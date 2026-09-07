<?php
/**
 * SETUP INICIAL — execute uma única vez pelo navegador:
 *   http://localhost/barbearia/setup.php
 * Cria o usuário administrador padrão. APAGUE ESTE ARQUIVO depois de usar.
 */
require_once __DIR__ . '/config/conexao.php';

$pdo = conectarBanco();
$mensagem = '';
$erro = '';

// Se já existe algum admin, bloqueia o setup por segurança
$existe = $pdo->query('SELECT COUNT(*) FROM admin_usuarios')->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $existe == 0) {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($usuario === '' || strlen($senha) < 6) {
        $erro = 'Informe um usuário e uma senha com pelo menos 6 caracteres.';
    } else {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO admin_usuarios (usuario, senha_hash) VALUES (:u, :h)');
        $stmt->execute(['u' => $usuario, 'h' => $hash]);
        $mensagem = 'Administrador criado com sucesso! Já pode acessar /admin/login.php. Por segurança, apague o arquivo setup.php agora.';
        $existe = 1;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Setup - POR NÓS BARBEARIA</title>
<style>
    body { font-family: system-ui, sans-serif; background:#121212; color:#EDE7DD; display:flex; align-items:center; justify-content:center; height:100vh; margin:0; }
    .box { background:#1C1C1C; padding:32px; border-radius:8px; width:360px; border:1px solid #2E2A24; }
    h1 { font-size:20px; margin-top:0; }
    label { display:block; margin:12px 0 4px; font-size:14px; }
    input { width:100%; padding:10px; border-radius:4px; border:1px solid #3a352e; background:#121212; color:#EDE7DD; box-sizing:border-box; }
    button { margin-top:20px; width:100%; padding:10px; background:#B08D57; border:none; border-radius:4px; color:#121212; font-weight:bold; cursor:pointer; }
    .msg-ok { background:#1f3a24; border:1px solid #2f5b38; padding:12px; border-radius:4px; margin-bottom:12px; font-size:14px; }
    .msg-err { background:#3a1f1f; border:1px solid #5b2f2f; padding:12px; border-radius:4px; margin-bottom:12px; font-size:14px; }
</style>
</head>
<body>
<div class="box">
    <h1>Configuração inicial</h1>
    <?php if ($mensagem): ?>
        <div class="msg-ok"><?= htmlspecialchars($mensagem) ?></div>
        <p><a href="admin/login.php" style="color:#B08D57;">Ir para o login →</a></p>
    <?php elseif ($existe > 0): ?>
        <div class="msg-err">Já existe um administrador cadastrado. Por segurança, este setup está desativado. Apague o arquivo setup.php.</div>
    <?php else: ?>
        <?php if ($erro): ?><div class="msg-err"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
        <form method="post">
            <label>Usuário</label>
            <input type="text" name="usuario" required>
            <label>Senha (mín. 6 caracteres)</label>
            <input type="password" name="senha" required minlength="6">
            <button type="submit">Criar administrador</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
