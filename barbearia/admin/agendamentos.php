<?php
require_once __DIR__ . '/includes/auth.php';
exigirLogin();

$pdo = conectarBanco();
$servicos = getServicosAtivos();
$csrfToken = gerarCsrfToken();

$filtroData = $_GET['data'] ?? '';
$filtroStatus = $_GET['status'] ?? '';

$sql = "SELECT a.*, GROUP_CONCAT(s.nome_servico SEPARATOR ', ') AS servicos_nomes
        FROM agendamentos a
        LEFT JOIN agendamento_servicos s ON s.agendamento_id = a.id
        WHERE 1=1";
$params = [];

if ($filtroData !== '') {
    $sql .= ' AND a.data = :data';
    $params['data'] = $filtroData;
}
if ($filtroStatus !== '') {
    $sql .= ' AND a.status = :status';
    $params['status'] = $filtroStatus;
}
$sql .= ' GROUP BY a.id ORDER BY a.data DESC, a.hora_inicio DESC LIMIT 200';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$agendamentos = $stmt->fetchAll();

$paginaAtiva = 'agendamentos';
$tituloPagina = 'Agendamentos';
require __DIR__ . '/includes/header.php';
?>

<div class="admin-header">
    <div>
        <h1>Agendamentos</h1>
        <p>Veja, crie, edite e gerencie todos os agendamentos.</p>
    </div>
    <button class="btn btn-primary btn-sm" onclick="abrirNovoAgendamento()">+ Novo agendamento</button>
</div>

<form class="filters-bar" method="get">
    <input type="date" name="data" value="<?= htmlspecialchars($filtroData) ?>">
    <select name="status">
        <option value="">Todos os status</option>
         <?php foreach (['reservado','concluido','cancelado','bloqueado'] as $st): ?>
            <option value="<?= $st ?>" <?= $filtroStatus === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-outline btn-sm" type="submit">Filtrar</button>
    <?php if ($filtroData || $filtroStatus): ?><a class="btn btn-outline btn-sm" href="agendamentos.php">Limpar</a><?php endif; ?>
</form>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr><th>Data</th><th>Horário</th><th>Cliente</th><th>Serviços</th><th>Valor</th><th>Status</th><th>Ações</th></tr>
        </thead>
        <tbody id="tabelaAgendamentos">
            <?php if (empty($agendamentos)): ?>
                <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:24px;">Nenhum agendamento encontrado.</td></tr>
            <?php else: foreach ($agendamentos as $a): ?>
                <tr data-id="<?= $a['id'] ?>">
                    <td><?= formatarDataBR($a['data']) ?></td>
                    <td><?= substr($a['hora_inicio'], 0, 5) ?></td>
                    <td><?= htmlspecialchars($a['cliente_nome'] ?? '—') ?><br><small style="color:var(--text-muted);"><?= $a['cliente_telefone'] ? formatarTelefone($a['cliente_telefone']) : '' ?></small></td>
                    <td><?= htmlspecialchars($a['servicos_nomes'] ?? '—') ?></td>
                    <td><?= formatarMoeda($a['valor_total']) ?></td>
                    <td><span class="badge badge-<?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span></td>
                    <td>
                        <div class="action-icons">
                            <?php if ($a['status'] === 'reservado'): ?>
                                <button onclick="mudarStatus(<?= $a['id'] ?>, 'concluido')">Concluir</button>
                                <button class="danger" onclick="mudarStatus(<?= $a['id'] ?>, 'cancelado')">Cancelar</button>
                            <?php endif; ?>
                            <button onclick='abrirEdicao(<?= json_encode($a, JSON_UNESCAPED_UNICODE) ?>)'>Editar</button>
                            <button class="danger" onclick="excluirAgendamento(<?= $a['id'] ?>)">Excluir</button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal criar/editar agendamento -->
<div class="admin-modal-overlay" id="modalAgendamento">
    <div class="admin-modal">
        <h3 id="tituloModalAgendamento">Novo agendamento</h3>
        <input type="hidden" id="agendamentoId" value="">
        <div class="form-field">
            <label>Nome do cliente</label>
            <input type="text" id="campoNome">
        </div>
        <div class="form-field">
            <label>Telefone / WhatsApp</label>
            <input type="text" id="campoTelefone">
        </div>
        <div class="form-field">
            <label>Data</label>
            <input type="date" id="campoData">
        </div>
        <div class="form-field">
            <label>Horário (HH:MM)</label>
            <input type="text" id="campoHora" placeholder="09:00">
        </div>
        <div class="form-field">
            <label>Serviços</label>
            <div id="campoServicos">
                <?php foreach ($servicos as $s): ?>
                    <label class="checkbox-inline" style="margin-bottom:6px;">
                        <input type="checkbox" name="servicoModal" value="<?= $s['id'] ?>" data-preco="<?= $s['preco'] ?>">
                        <?= htmlspecialchars($s['nome']) ?> — <?= formatarMoeda($s['preco']) ?>
                    </label><br>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- CONSERTADO: Removido o display:none que ocultava o aviso de erro -->
        <div class="error-msg" id="erroModalAgendamento"></div>
        <div class="form-actions">
            <button class="btn btn-outline" onclick="fecharModalAgendamento()">Cancelar</button>
            <button class="btn btn-primary" onclick="salvarAgendamento()">Salvar</button>
        </div>
    </div>
</div>

<script>window.CSRF_TOKEN = <?= json_encode($csrfToken) ?>;</script>

<script src="../assets/js/admin.js"></script>

<?php require __DIR__ . '/includes/footer.php'; ?>
