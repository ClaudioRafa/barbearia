<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['erro' => 'Método não permitido.'], 405);
}

$id = isset($input['id']) && $input['id'] !== '' ? (int)$input['id'] : null;
$nome = trim($input['nome'] ?? '');
$preco = (float)str_replace(',', '.', (string)($input['preco'] ?? '0'));
$ordem = (int)($input['ordem'] ?? 0);

if (mb_strlen($nome) < 2 || $preco <= 0) {
    jsonResponse(['erro' => 'Informe um nome válido e um preço maior que zero.'], 422);
}

$pdo = conectarBanco();

if ($id) {
    $stmt = $pdo->prepare('UPDATE servicos SET nome=:nome, preco=:preco, ordem=:ordem WHERE id=:id');
    $stmt->execute(['nome' => $nome, 'preco' => $preco, 'ordem' => $ordem, 'id' => $id]);
} else {
    $stmt = $pdo->prepare('INSERT INTO servicos (nome, preco, ordem, ativo) VALUES (:nome, :preco, :ordem, 1)');
    $stmt->execute(['nome' => $nome, 'preco' => $preco, 'ordem' => $ordem]);
    $id = $pdo->lastInsertId();
}

jsonResponse(['sucesso' => true, 'id' => $id]);
