<?php
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['erro' => 'Método não permitido.'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

$data = trim($input['data'] ?? '');
$hora = trim($input['hora'] ?? '');
$nome = trim($input['nome'] ?? '');
$telefone = trim($input['telefone'] ?? '');
$servicoIds = $input['servicos'] ?? [];

/* -------- validações -------- */
$erros = [];

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) $erros[] = 'Data inválida.';
if (!preg_match('/^\d{2}:\d{2}$/', $hora)) $erros[] = 'Horário inválido.';
if (mb_strlen($nome) < 3) $erros[] = 'Informe o nome completo.';

$telNormalizado = normalizarTelefone($telefone);
if (strlen($telNormalizado) < 10) $erros[] = 'Informe um telefone/WhatsApp válido com DDD.';

if (!is_array($servicoIds) || count($servicoIds) === 0) $erros[] = 'Selecione ao menos um serviço.';

if (!empty($erros)) {
    jsonResponse(['erro' => implode(' ', $erros)], 422);
}

$pdo = conectarBanco();

// valida serviços e calcula total no servidor (nunca confiar em preço vindo do cliente)
$servicoIds = array_map('intval', $servicoIds);
$placeholders = implode(',', array_fill(0, count($servicoIds), '?'));
$stmt = $pdo->prepare("SELECT * FROM servicos WHERE id IN ($placeholders) AND ativo = 1");
$stmt->execute($servicoIds);
$servicos = $stmt->fetchAll();

if (count($servicos) !== count(array_unique($servicoIds))) {
    jsonResponse(['erro' => 'Um ou mais serviços selecionados são inválidos.'], 422);
}

$valorTotal = array_sum(array_column($servicos, 'preco'));
$duracao = (int)getConfig('duracao_atendimento', 40);
$horaFim = date('H:i:s', strtotime($hora) + $duracao * 60);

// não permite agendar em data/hora passada
if (strtotime("$data $hora") <= time()) {
    jsonResponse(['erro' => 'Não é possível agendar em um horário que já passou.'], 422);
}

// confirma que o horário realmente está dentro do funcionamento do dia
$disponiveis = getHorariosDisponiveis($data);
if (!in_array($hora, $disponiveis, true)) {
    jsonResponse(['erro' => 'Esse horário não está mais disponível. Escolha outro.'], 409);
}

/* -------- transação com lock para impedir duplicidade (condição de corrida) -------- */
try {
    $pdo->beginTransaction();

    // trava as linhas dessa data/hora para evitar que dois clientes reservem ao mesmo tempo
    $check = $pdo->prepare("SELECT id FROM agendamentos
        WHERE data = :data AND hora_inicio = :hora
        AND status IN ('reservado','concluido','bloqueado')
        FOR UPDATE");
    $check->execute(['data' => $data, 'hora' => $hora]);

    if ($check->fetch()) {
        $pdo->rollBack();
        jsonResponse(['erro' => 'Esse horário acabou de ser reservado por outro cliente. Escolha outro horário.'], 409);
    }

    $insert = $pdo->prepare('INSERT INTO agendamentos
        (cliente_nome, cliente_telefone, data, hora_inicio, hora_fim, valor_total, status)
        VALUES (:nome, :tel, :data, :hora, :hora_fim, :valor, "reservado")');
    $insert->execute([
        'nome' => $nome,
        'tel' => $telNormalizado,
        'data' => $data,
        'hora' => $hora,
        'hora_fim' => $horaFim,
        'valor' => $valorTotal,
    ]);
    $agendamentoId = $pdo->lastInsertId();

    $insertServico = $pdo->prepare('INSERT INTO agendamento_servicos (agendamento_id, servico_id, nome_servico, preco)
        VALUES (:aid, :sid, :nome, :preco)');
    foreach ($servicos as $s) {
        $insertServico->execute([
            'aid' => $agendamentoId,
            'sid' => $s['id'],
            'nome' => $s['nome'],
            'preco' => $s['preco'],
        ]);
    }

    $pdo->commit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    jsonResponse(['erro' => 'Erro ao salvar o agendamento. Tente novamente.'], 500);
}

jsonResponse([
    'sucesso' => true,
    'agendamento' => [
        'id' => $agendamentoId,
        'nome' => $nome,
        'data' => $data,
        'data_formatada' => formatarDataBR($data),
        'hora' => $hora,
        'valor_total' => $valorTotal,
        'valor_formatado' => formatarMoeda($valorTotal),
        'servicos' => array_column($servicos, 'nome'),
    ],
]);
