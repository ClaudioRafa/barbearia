<?php
/**
 * Inclua este arquivo no topo de cada página do admin, após definir $paginaAtiva e $tituloPagina.
 * Requer que admin/includes/auth.php já tenha sido carregado e exigirLogin() chamado.
 */
$nomeBarbearia = getConfig('nome_barbearia', 'POR NÓS BARBEARIA');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($tituloPagina ?? 'Painel') ?> — Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="brand"><span class="brand-mark" aria-hidden="true"></span> <?= htmlspecialchars($nomeBarbearia) ?></div>
        <nav class="admin-nav">
            <a href="index.php" class="<?= ($paginaAtiva ?? '') === 'dashboard' ? 'active' : '' ?>">Painel</a>
            <a href="calendario.php" class="<?= ($paginaAtiva ?? '') === 'calendario' ? 'active' : '' ?>">Calendário</a>
            <a href="agendamentos.php" class="<?= ($paginaAtiva ?? '') === 'agendamentos' ? 'active' : '' ?>">Agendamentos</a>
            <a href="servicos.php" class="<?= ($paginaAtiva ?? '') === 'servicos' ? 'active' : '' ?>">Serviços</a>
            <a href="configuracoes.php" class="<?= ($paginaAtiva ?? '') === 'configuracoes' ? 'active' : '' ?>">Configurações</a>
            <a href="logout.php" class="logout-link">Sair</a>
        </nav>
    </aside>
    <main class="admin-main">
