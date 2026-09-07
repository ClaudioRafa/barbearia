<?php
require_once __DIR__ . '/includes/auth.php';
exigirLogin();

$pdo = conectarBanco();
$servicos = $pdo->query('SELECT * FROM servicos WHERE ativo = 1 ORDER BY ordem, id')->fetchAll();
$csrfToken = gerarCsrfToken();

$paginaAtiva = 'servicos';
$tituloPagina = 'Serviços';
require __DIR__ . '/includes/header.php';
?>

<div class="admin-header">
    <div>
        <h1>Serviços</h1>
        <p>Gerencie os serviços e preços exibidos para os clientes.</p>
    </div>
    <button class="btn btn-primary btn-sm" onclick="abrirNovoServico()">+ Novo serviço</button>
</div>

<div class="table-card">
    <table class="data-table">
        <thead><tr><th>Ordem</th><th>Nome</th><th>Preço</th><th>Ações</th></tr></thead>
        <tbody>
            <?php foreach ($servicos as $s): ?>
                <tr>
                    <td><?= $s['ordem'] ?></td>
                    <td><?= htmlspecialchars($s['nome']) ?></td>
                    <td><?= formatarMoeda($s['preco']) ?></td>
                    <td>
                        <div class="action-icons">
                            <button onclick='abrirEdicaoServico(<?= json_encode($s, JSON_UNESCAPED_UNICODE) ?>)'>Editar</button>
                            <button class="danger" onclick="excluirServico(<?= $s['id'] ?>)">Excluir</button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="admin-modal-overlay" id="modalServico">
    <div class="admin-modal">
        <h3 id="tituloModalServico">Novo serviço</h3>
        <input type="hidden" id="servicoId">
        <div class="form-field"><label>Nome do serviço</label><input type="text" id="servicoNome"></div>
        <div class="form-field"><label>Preço (R$)</label><input type="text" id="servicoPreco" placeholder="Ex: 25,00"></div>
        <div class="form-field"><label>Ordem de exibição</label><input type="text" id="servicoOrdem" placeholder="Ex: 1"></div>
        <div class="error-msg" id="erroModalServico"></div>
        <div class="form-actions">
            <button class="btn btn-outline" onclick="fecharModalServico()">Cancelar</button>
            <button class="btn btn-primary" onclick="salvarServico()">Salvar</button>
        </div>
    </div>
</div>

<script>window.CSRF_TOKEN = <?= json_encode($csrfToken) ?>;</script>

<script src="../assets/js/admin.js"></script>

<?php require __DIR__ . '/includes/footer.php'; ?>
