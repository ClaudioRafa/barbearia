<?php
require_once __DIR__ . '/includes/auth.php';
exigirLogin();

$csrfToken = gerarCsrfToken();
$hoje = date('Y-m-d');

$paginaAtiva = 'calendario';
$tituloPagina = 'Calendário';
require __DIR__ . '/includes/header.php';
?>

<div class="admin-header">
    <div>
        <h1>Calendário</h1>
        <p>Clique em um horário livre para bloqueá-lo, ou em um bloqueado para liberá-lo.</p>
    </div>
</div>

<div class="calendar-toolbar">
    <input type="date" id="dataCalendario" value="<?= $hoje ?>">
    <div class="legend">
        <span><i style="background:#4a4a4a;"></i> Disponível</span>
        <span><i style="background:var(--brass);"></i> Reservado</span>
        <span><i style="background:var(--success);"></i> Concluído</span>
        <span><i style="background:var(--danger);"></i> Cancelado</span>
        <span><i style="background:#6b6b6b;"></i> Bloqueado</span>
    </div>
</div>

<div id="avisoDia"></div>
<div class="slots-day-grid" id="slotsDayGrid">
    <div class="loading-msg">Carregando...</div>
</div>

<div class="admin-modal-overlay" id="modalSlot">
    <div class="admin-modal">
        <h3 id="tituloModalSlot">Horário</h3>
        <div id="detalhesSlot" style="font-size:14px;color:var(--text-muted);margin-bottom:16px;"></div>
        <div class="form-actions" id="acoesModalSlot"></div>
    </div>
</div>

<script>window.CSRF_TOKEN = <?= json_encode($csrfToken) ?>;</script>

<script src="../assets/js/admin.js"></script>

<?php require __DIR__ . '/includes/footer.php'; ?>
