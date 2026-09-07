<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['erro' => 'Método não permitido.'], 405);
}

$data = trim($input['data'] ?? '');
$hora = trim($input['hora'] ?? '');
$motivo = trim($input['motivo'] ?? 'Bloqueado pelo administrador');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data) || !preg_match('/^\d{2}:\d{2}$/', $hora)) {
    jsonResponse(['erro' => 'Data ou horário inválido.'], 422);
}

$pdo = conectarBanco();
$duracao = (int)getConfig('duracao_atendimento', 40);
$horaFim = date('H:i:s', strtotime($hora) + $duracao * 60);

try {
    $pdo->beginTransaction();
    $check = $pdo->prepare("SELECT id FROM agendamentos WHERE data=:data AND hora_inicio=:hora
        AND status IN ('reservado','concluido','bloqueado') FOR UPDATE");
    $check->execute(['data' => $data, 'hora' => $hora]);
    if ($check->fetch()) {
        $pdo->rollBack();
        jsonResponse(['erro' => 'Já existe um agendamento ou bloqueio nesse horário.'], 409);
    }

    $ins = $pdo->prepare('INSERT INTO agendamentos (data, hora_inicio, hora_fim, valor_total, status, observacao)
        VALUES (:data, :hora, :hora_fim, 0, "bloqueado", :motivo)');
    $ins->execute(['data' => $data, 'hora' => $hora, 'hora_fim' => $horaFim, 'motivo' => $motivo]);
    $pdo->commit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    jsonResponse(['erro' => 'Erro ao bloquear horário.'], 500);
}

jsonResponse(['sucesso' => true]);
