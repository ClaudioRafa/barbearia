<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['erro' => 'Método não permitido.'], 405);
}
// CORREÇÃO: Captura o JSON enviado pelo JavaScript e transforma na variável $input
// Sem isso, $input['id'] e $input['status'] chegam sempre vazios, matando a requisição no IF abaixo.
if (!isset($input)) {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
}

$id = (int)($input['id'] ?? 0);
$status = $input['status'] ?? '';

$statusValidos = ['reservado', 'concluido', 'cancelado', 'bloqueado'];
if ($id <= 0 || !in_array($status, $statusValidos, true)) {
    jsonResponse(['erro' => 'Requisição inválida.'], 422);
}

$pdo = conectarBanco();
$stmt = $pdo->prepare('UPDATE agendamentos SET status = :status WHERE id = :id');
$stmt->execute(['status' => $status, 'id' => $id]);

// ALTERAÇÃO DE FLUXO: Se o registro já estava com o mesmo status no banco, o rowCount retorna 0.
// Para evitar dar erro de "não encontrado" só porque você clicou no status que ele já tinha, 
// verificamos se a query executou com sucesso em vez de exigir alteração de linha.
jsonResponse(['sucesso' => true]);