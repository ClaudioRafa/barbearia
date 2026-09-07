<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['erro' => 'Método não permitido.'], 405);
}

$id = isset($input['id']) && $input['id'] !== '' ? (int)$input['id'] : null;
$nome = trim($input['nome'] ?? '');
$telefone = trim($input['telefone'] ?? '');
$data = trim($input['data'] ?? '');
$hora = trim($input['hora'] ?? '');
$servicoIds = $input['servicos'] ?? [];

// AJUSTE DE COMPATIBILIDADE: Se a data vier com barras (11/09/2026),
// ajusta temporariamente para passar na validação original por traços (2026-09-11)
if (strpos($data, '/') !== false) {
    $partesData = explode('/', $data);
    if (count($partesData) === 3) {
        $data = $partesData[2] . '-' . $partesData[1] . '-' . $partesData[0];
    }
}

$erros = [];
if (mb_strlen($nome) < 2) $erros[] = 'Informe o nome do cliente.';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) $erros[] = 'Data inválida.';
if (!preg_match('/^\d{2}:\d{2}$/', $hora)) $erros[] = 'Horário inválido (use HH:MM).';
if (!is_array($servicoIds) || count($servicoIds) === 0) $erros[] = 'Selecione ao menos um serviço.';

if (!empty($erros)) jsonResponse(['erro' => implode(' ', $erros)], 422);

$pdo = conectarBanco();

$servicoIds = array_map('intval', $servicoIds);
$placeholders = implode(',', array_fill(0, count($servicoIds), '?'));
$stmt = $pdo->prepare("SELECT * FROM servicos WHERE id IN ($placeholders)");
$stmt->execute($servicoIds);
$servicos = $stmt->fetchAll();

if (count($servicos) !== count(array_unique($servicoIds))) {
    jsonResponse(['erro' => 'Um ou mais serviços são inválidos.'], 422);
}

$valorTotal = array_sum(array_column($servicos, 'preco'));
$duracao = (int)getConfig('duracao_atendimento', 40);
$horaFim = date('H:i:s', strtotime($hora) + $duracao * 60);
$telNormalizado = normalizarTelefone($telefone);

try {
    $pdo->beginTransaction();

    // verifica conflito de horário (ignorando o próprio registro em caso de edição)
    $sqlCheck = "SELECT id FROM agendamentos WHERE data = :data AND hora_inicio = :hora
        AND status IN ('reservado','concluido','bloqueado')";
    $paramsCheck = ['data' => $data, 'hora' => $hora];
    if ($id) {
        $sqlCheck .= ' AND id != :id';
        $paramsCheck['id'] = $id;
    }
    $sqlCheck .= ' FOR UPDATE';
    $check = $pdo->prepare($sqlCheck);
    $check->execute($paramsCheck);

    if ($check->fetch()) {
        $pdo->rollBack();
        jsonResponse(['erro' => 'Já existe um agendamento nesse horário.'], 409);
    }

    if ($id) {
        $upd = $pdo->prepare('UPDATE agendamentos SET cliente_nome=:nome, cliente_telefone=:tel, data=:data,
            hora_inicio=:hora, hora_fim=:hora_fim, valor_total=:valor WHERE id=:id');
        $upd->execute([
            'nome' => $nome, 'tel' => $telNormalizado, 'data' => $data,
            'hora' => $hora, 'hora_fim' => $horaFim, 'valor' => $valorTotal, 'id' => $id,
        ]);
        $pdo->prepare('DELETE FROM agendamento_servicos WHERE agendamento_id = :id')->execute(['id' => $id]);
        $agendamentoId = $id;
    } else {
        $ins = $pdo->prepare('INSERT INTO agendamentos (cliente_nome, cliente_telefone, data, hora_inicio, hora_fim, valor_total, status)
            VALUES (:nome, :tel, :data, :hora, :hora_fim, :valor, "reservado")');
        $ins->execute([
            'nome' => $nome, 'tel' => $telNormalizado, 'data' => $data,
            'hora' => $hora, 'hora_fim' => $horaFim, 'valor' => $valorTotal,
        ]);
        $agendamentoId = $pdo->lastInsertId();
    }

    $insServico = $pdo->prepare('INSERT INTO agendamento_servicos (agendamento_id, servico_id, nome_servico, preco)
        VALUES (:aid, :sid, :nome, :preco)');
    foreach ($servicos as $s) {
        $insServico->execute(['aid' => $agendamentoId, 'sid' => $s['id'], 'nome' => $s['nome'], 'preco' => $s['preco']]);
    }

    $pdo->commit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('salvar_agendamento: ' . $e->getMessage());
    jsonResponse(['erro' => 'Erro interno ao salvar o agendamento. Tente novamente.'], 500);
}

jsonResponse(['sucesso' => true, 'id' => $agendamentoId]);
