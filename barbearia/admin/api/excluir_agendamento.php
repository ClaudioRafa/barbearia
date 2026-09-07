<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['erro' => 'Método não permitido.'], 405);
}

/// CORREÇÃO: Captura o JSON enviado pelo JavaScript e injeta na variável $input
// Evita que o fluxo do código morra no IF abaixo retornando sempre "ID inválido".
if (!isset($input)) {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
}

$id = (int)($input['id'] ?? 0);
if ($id <= 0) jsonResponse(['erro' => 'ID inválido.'], 422);

$pdo = conectarBanco();

try {
    $pdo->beginTransaction();

    $pdo->prepare('DELETE FROM agendamento_servicos WHERE agendamento_id = :id')->execute(['id' => $id]);

    $stmt = $pdo->prepare('DELETE FROM agendamentos WHERE id = :id');
    $stmt->execute(['id' => $id]);

    if ($stmt->rowCount() === 0) {
        $pdo->rollBack();
        jsonResponse(['erro' => 'Agendamento não encontrado.'], 404);
    }

    $pdo->commit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('excluir_agendamento: ' . $e->getMessage());
    jsonResponse(['erro' => 'Erro interno ao excluir o agendamento.'], 500);
}

jsonResponse(['sucesso' => true]);