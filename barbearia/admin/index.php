<?php
require_once __DIR__ . '/includes/auth.php';
exigirLogin();

$pdo = conectarBanco();
$hoje = date('Y-m-d');

$stmtHoje = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE data = :d AND status IN ('reservado','concluido')");
$stmtHoje->execute(['d' => $hoje]);
$totalHoje = (int)$stmtHoje->fetchColumn();

$stmtSemana = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE data BETWEEN :ini AND :fim AND status IN ('reservado','concluido')");
$stmtSemana->execute(['ini' => $hoje, 'fim' => date('Y-m-d', strtotime('+6 days'))]);
$totalSemana = (int)$stmtSemana->fetchColumn();

$stmtFaturamento = $pdo->prepare("SELECT COALESCE(SUM(valor_total),0) FROM agendamentos WHERE data = :d AND status IN ('reservado','concluido')");
$stmtFaturamento->execute(['d' => $hoje]);
$faturamentoHoje = (float)$stmtFaturamento->fetchColumn();

$stmtPendentes = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE data < :d AND status = 'reservado'");
$stmtPendentes->execute(['d' => $hoje]);
$pendentesAtrasados = (int)$stmtPendentes->fetchColumn();

$stmtProximos = $pdo->prepare("SELECT a.*, GROUP_CONCAT(s.nome_servico SEPARATOR ', ') AS servicos_nomes
    FROM agendamentos a
    LEFT JOIN agendamento_servicos s ON s.agendamento_id = a.id
    WHERE a.data = :d AND a.status IN ('reservado','concluido')
    GROUP BY a.id
    ORDER BY a.hora_inicio ASC");
$stmtProximos->execute(['d' => $hoje]);
$agendamentosHoje = $stmtProximos->fetchAll();

$paginaAtiva = 'dashboard';
$tituloPagina = 'Painel';
require __DIR__ . '/includes/header.php';
?>

<div class="admin-header">
    <div>
        <h1>Olá, <?= htmlspecialchars($_SESSION['admin_usuario']) ?></h1>
        <p>Resumo de hoje, <?= formatarDataBR($hoje) ?></p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card"><div class="label">Agendamentos hoje</div><div class="value"><?= $totalHoje ?></div></div>
    <div class="stat-card"><div class="label">Agendamentos nos próximos 7 dias</div><div class="value"><?= $totalSemana ?></div></div>
    <div class="stat-card"><div class="label">Faturamento previsto hoje</div><div class="value"><?= formatarMoeda($faturamentoHoje) ?></div></div>
    <div class="stat-card"><div class="label">Pendentes em atraso</div><div class="value"><?= $pendentesAtrasados ?></div></div>
</div>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr><th>Horário</th><th>Cliente</th><th>Serviços</th><th>Valor</th><th>Status</th></tr>
        </thead>
        <tbody>
            <?php if (empty($agendamentosHoje)): ?>
                <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:24px;">Nenhum agendamento para hoje.</td></tr>
            <?php else: foreach ($agendamentosHoje as $a): ?>
                <tr>
                    <td><?= substr($a['hora_inicio'], 0, 5) ?></td>
                    <td><?= htmlspecialchars($a['cliente_nome']) ?><br><small style="color:var(--text-muted);"><?= formatarTelefone($a['cliente_telefone']) ?></small></td>
                    <td><?= htmlspecialchars($a['servicos_nomes']) ?></td>
                    <td><?= formatarMoeda($a['valor_total']) ?></td>
                    <td><span class="badge badge-<?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
