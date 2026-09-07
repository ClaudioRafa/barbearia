<?php
require_once __DIR__ . '/includes/auth.php';
exigirLogin();

$config = getConfiguracoes();
$dias = getDiasFuncionamento();
$csrfToken = gerarCsrfToken();

$paginaAtiva = 'configuracoes';
$tituloPagina = 'Configurações';
require __DIR__ . '/includes/header.php';
?>

<div class="admin-header">
    <div>
        <h1>Configurações</h1>
        <p>Dados da barbearia, horários de funcionamento e segurança.</p>
    </div>
</div>

<div id="alertaConfig"></div>

<div class="form-card">
    <h2>Informações da barbearia</h2>
    <div class="form-grid">
        <div class="form-field"><label>Nome da barbearia</label><input type="text" id="cfgNome" value="<?= htmlspecialchars($config['nome_barbearia'] ?? '') ?>"></div>
        <div class="form-field"><label>WhatsApp (com DDD)</label><input type="text" id="cfgWhatsapp" value="<?= htmlspecialchars(formatarTelefone($config['whatsapp'] ?? '')) ?>"></div>
        <div class="form-field"><label>Duração de cada atendimento (min)</label><input type="text" id="cfgDuracao" value="<?= htmlspecialchars($config['duracao_atendimento'] ?? '40') ?>"></div>
    </div>
    <div class="form-grid" style="margin-top:16px;">
        <div class="form-field"><label>Início do almoço</label><input type="text" id="cfgAlmocoInicio" value="<?= htmlspecialchars(substr($config['almoco_inicio'] ?? '13:00', 0, 5)) ?>" placeholder="13:00"></div>
        <div class="form-field"><label>Fim do almoço</label><input type="text" id="cfgAlmocoFim" value="<?= htmlspecialchars(substr($config['almoco_fim'] ?? '15:00', 0, 5)) ?>" placeholder="15:00"></div>
    </div>
    <div class="form-field" style="margin-top:16px;">
        <label>Texto de destaque da página inicial</label>
        <textarea id="cfgTextoHero" rows="2"><?= htmlspecialchars($config['texto_hero'] ?? '') ?></textarea>
    </div>
    <div class="form-field">
        <label>Texto sobre a barbearia (seção de serviços)</label>
        <textarea id="cfgTextoSobre" rows="2"><?= htmlspecialchars($config['texto_sobre'] ?? '') ?></textarea>
    </div>
</div>

<div class="form-card">
    <h2>Dias e horários de funcionamento</h2>
    <div class="table-card" style="margin-bottom:0;">
        <table class="data-table">
            <thead><tr><th>Dia</th><th>Aberto</th><th>Abertura</th><th>Fechamento</th><th>Ordem de chegada</th></tr></thead>
            <tbody id="tabelaDias">
                <?php foreach ($dias as $dia => $d): ?>
                <tr data-dia="<?= $dia ?>">
                    <td><?= nomeDiaSemana((int)$dia) ?></td>
                    <td><label class="checkbox-inline"><input type="checkbox" class="dia-ativo" <?= (int)$d['ativo'] === 1 ? 'checked' : '' ?>></label></td>
                    <td><input type="text" class="dia-abertura" value="<?= substr($d['hora_abertura'],0,5) ?>" style="width:80px;"></td>
                    <td><input type="text" class="dia-fechamento" value="<?= substr($d['hora_fechamento'],0,5) ?>" style="width:80px;"></td>
                    <td><label class="checkbox-inline"><input type="checkbox" class="dia-ordem" <?= (int)$d['ordem_chegada'] === 1 ? 'checked' : '' ?>></label></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="form-actions">
        <button class="btn btn-primary" onclick="salvarConfiguracoes()">Salvar configurações</button>
    </div>
</div>

<div class="form-card">
    <h2>Segurança — trocar senha</h2>
    <div class="form-grid">
        <div class="form-field"><label>Senha atual</label><input type="password" id="senhaAtual"></div>
        <div class="form-field"><label>Nova senha</label><input type="password" id="novaSenha"></div>
    </div>
    <div class="error-msg" id="erroSenha"></div>
    <div class="form-actions">
        <button class="btn btn-outline" onclick="trocarSenha()">Atualizar senha</button>
    </div>
</div>

<script>window.CSRF_TOKEN = <?= json_encode($csrfToken) ?>;</script>

<script src="../assets/js/admin.js"></script>

<?php require __DIR__ . '/includes/footer.php'; ?>
