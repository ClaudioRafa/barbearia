<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ]);
}

require_once __DIR__ . '/../../includes/functions.php';

function adminLogado(): bool
{
    return !empty($_SESSION['admin_id']);
}

function exigirLogin(): void
{
    if (!adminLogado()) {
        header('Location: login.php');
        exit;
    }
}

function tentarLogin(string $usuario, string $senha): bool
{
    $pdo = conectarBanco();
    $stmt = $pdo->prepare('SELECT * FROM admin_usuarios WHERE usuario = :u LIMIT 1');
    $stmt->execute(['u' => $usuario]);
    $admin = $stmt->fetch();

     // ALTERAÇÃO: Removido o password_verify. Agora compara o texto puro direto do banco.
    if ($admin && $senha === $admin['senha']) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_usuario'] = $admin['usuario'];
        return true;
    }
    return false;
}

/** Token simples anti-CSRF para os formulários do painel */
function gerarCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validarCsrfToken(?string $token): bool
{
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}
