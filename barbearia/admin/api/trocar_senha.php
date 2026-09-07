<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['erro' => 'Método não permitido.'], 405);
}

$senhaAtual = $input['senha_atual'] ?? '';
$novaSenha = $input['nova_senha'] ?? '';

if (strlen($novaSenha) < 6) {
    jsonResponse(['erro' => 'A nova senha deve ter pelo menos 6 caracteres.'], 422);
}

$pdo = conectarBanco();
$stmt = $pdo->prepare('SELECT * FROM admin_usuarios WHERE id = :id');
$stmt->execute(['id' => $_SESSION['admin_id']]);
$admin = $stmt->fetch();

if (!$admin || !password_verify($senhaAtual, $admin['senha_hash'])) {
    jsonResponse(['erro' => 'Senha atual incorreta.'], 403);
}

$novoHash = password_hash($novaSenha, PASSWORD_DEFAULT);
$pdo->prepare('UPDATE admin_usuarios SET senha_hash = :h WHERE id = :id')
    ->execute(['h' => $novoHash, 'id' => $admin['id']]);

jsonResponse(['sucesso' => true]);
