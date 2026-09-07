<?php
require_once __DIR__ . '/../includes/auth.php';

if (!adminLogado()) {
    jsonResponse(['erro' => 'Sessão expirada. Faça login novamente.'], 401);
}

// Endpoints que alteram dados (POST) exigem token CSRF válido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) $input = $_POST;

    $token = $input['csrf_token'] ?? ($_POST['csrf_token'] ?? null);
    if (!validarCsrfToken($token)) {
        jsonResponse(['erro' => 'Token de segurança inválido. Recarregue a página e tente novamente.'], 403);
    }
}
