<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['erro' => 'Método não permitido.'], 405);
}

$id = (int)($input['id'] ?? 0);
if ($id <= 0) jsonResponse(['erro' => 'ID inválido.'], 422);

$pdo = conectarBanco();

// Desativa em vez de excluir de verdade: preserva o histórico de agendamentos
// que já referenciam esse serviço (agendamento_servicos tem FK RESTRICT).
$stmt = $pdo->prepare('UPDATE servicos SET ativo = 0 WHERE id = :id');
$stmt->execute(['id' => $id]);

if ($stmt->rowCount() === 0) {
    jsonResponse(['erro' => 'Serviço não encontrado.'], 404);
}

jsonResponse(['sucesso' => true]);
