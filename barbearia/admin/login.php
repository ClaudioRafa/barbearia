<?php
require_once __DIR__ . '/includes/auth.php';

if (adminLogado()) {
    header('Location: index.php');
    exit;
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (tentarLogin($usuario, $senha)) {
        header('Location: index.php');
        exit;
    }
    $erro = 'Usuário ou senha incorretos.';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login administrativo — POR NÓS BARBEARIA</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-login-body">
    <div class="login-box">
        <div class="brand" style="justify-content:center;margin-bottom:22px;">
            <span class="brand-mark" aria-hidden="true"></span> POR NÓS BARBEARIA
        </div>
        <h1 class="login-title">Painel administrativo</h1>
        <?php if ($erro): ?>
            <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-field">
                <label for="usuario">Usuário</label>
                <input type="text" id="usuario" name="usuario" required autofocus>
            </div>
            <div class="form-field">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
        </form>
        <p class="login-back"><a href="../index.php">&larr; Voltar ao site</a></p>
    </div>
</body>
</html>
