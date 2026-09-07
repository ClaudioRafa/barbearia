<?php
 
require_once __DIR__ . '/includes/functions.php';

$nomeBarbearia = getConfig('nome_barbearia', 'POR NÓS BARBEARIA');
$whatsapp = getConfig('whatsapp', '5521987674006');
$textoHero = getConfig('texto_hero', '');
$textoSobre = getConfig('texto_sobre', '');
$servicos = getServicosAtivos();
$dias = getDiasFuncionamento();
$hoje = date('Y-m-d');
$dataMax = date('Y-m-d', strtotime('+45 days'));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= htmlspecialchars($nomeBarbearia) ?> — Agendamento Online</title>
<meta name="description" content="Agende seu horário na <?= htmlspecialchars($nomeBarbearia) ?> em poucos cliques.">
<link rel="stylesheet" href="assets/css/style.css?v=2.0">
</head>
<body>

<header class="topbar">
    <div class="topbar-inner">
        <div class="brand" onclick="window.location.reload();">
            <img src="assets/img/log.png" alt="<?= htmlspecialchars($nomeBarbearia) ?>" class="brand-logo-img">
        </div>
        <button class="btn btn-primary" onclick="abrirAgendamento()">Agendar horário</button>
    </div>
</header>





<main>
    <section class="hero">
    <div class="container hero-center">
        <div class="eyebrow">AGENDAMENTO ONLINE</div>
        <h1><?= htmlspecialchars($nomeBarbearia) ?></h1>
        <p class="lead"><?= htmlspecialchars($textoHero) ?></p>
        
        <div class="hero-actions">
            <button class="btn btn-primary" onclick="abrirAgendamento()">Agendar horário</button>
            <a class="btn btn-outline" href="<?= htmlspecialchars(linkWhatsapp($whatsapp, 'Olá! Vim pelo site e gostaria de mais informações.')) ?>" target="_blank" rel="noopener">Falar no WhatsApp</a>
        </div>

        <div class="hero-visual">
            <div class="tag">
                <strong>Seg a Qui</strong>
                09:00 às 20:00 · agendamento por horário marcado
            </div>
        </div>
    </div>
</section>




    <section class="section" id="servicos">
        <div class="container">
            <div class="section-head">
                <h2>Serviços</h2>
                <p><?= htmlspecialchars($textoSobre) ?></p>
            </div>
            <div class="service-list">
                <?php foreach ($servicos as $s): ?>
                <div class="service-row">
                    <span class="name"><?= htmlspecialchars($s['nome']) ?></span>
                    <span class="dots"></span>
                    <span class="price"><?= formatarMoeda($s['preco']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section" id="horarios">
        <div class="container">
            <div class="section-head">
                <h2>Horário de funcionamento</h2>
                <p>Atendimentos de 40 minutos. Chegue com alguns minutos de antecedência.</p>
            </div>
            <table class="hours-table">
                <?php foreach ($dias as $d):
                    $inativo = (int)$d['ativo'] === 0;
                ?>
                <tr class="<?= $inativo ? 'closed' : '' ?>">
                    <td><?= nomeDiaSemana((int)$d['dia_semana']) ?></td>
                    <td>
                        <?php if ($inativo): ?>
                            Fechado
                        <?php else: ?>
                            <?= substr($d['hora_abertura'], 0, 5) ?> às <?= substr($d['hora_fechamento'], 0, 5) ?>
                            <?= (int)$d['ordem_chegada'] === 1 ? ' · por ordem de chegada' : '' ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <div class="note-box">Horário de almoço (<?= substr(getConfig('almoco_inicio','13:00'),0,5) ?> às <?= substr(getConfig('almoco_fim','15:00'),0,5) ?>) não disponível para agendamento.</div>
        </div>
    </section>
</main>

<footer class="container">
    <span>&copy; <?= date('Y') ?> <?= htmlspecialchars($nomeBarbearia) ?></span>
    <span><?= formatarTelefone($whatsapp) ?></span>
</footer>

<a class="whatsapp-float" href="<?= htmlspecialchars(linkWhatsapp($whatsapp, 'Olá! Vim pelo site e gostaria de mais informações.')) ?>" target="_blank" rel="noopener" aria-label="Falar no WhatsApp">
    <svg viewBox="0 0 32 32" fill="#0b0b0b"><path d="M16.02 2.67C8.66 2.67 2.7 8.63 2.7 16c0 2.5.68 4.85 1.87 6.87L2.67 29.3l6.6-1.87A13.2 13.2 0 0 0 16.02 29.3c7.35 0 13.32-5.96 13.32-13.3 0-3.56-1.38-6.9-3.9-9.42a13.24 13.24 0 0 0-9.42-3.9Zm0 24.28a10.9 10.9 0 0 1-5.58-1.53l-.4-.24-3.9 1.1 1.05-3.82-.26-.4a10.96 10.96 0 0 1-1.68-5.84c0-6.06 4.93-10.98 11-10.98 2.94 0 5.7 1.14 7.77 3.22a10.9 10.9 0 0 1 3.22 7.76c0 6.06-4.93 10.98-11 10.98Zm6.03-8.22c-.33-.17-1.95-.96-2.25-1.07-.3-.11-.52-.17-.74.17-.22.33-.85 1.07-1.04 1.29-.19.22-.38.24-.71.08-.33-.17-1.4-.51-2.66-1.63-.98-.87-1.65-1.95-1.84-2.28-.19-.33-.02-.5.15-.67.15-.15.33-.38.5-.58.16-.19.22-.33.33-.55.11-.22.06-.41-.03-.58-.08-.17-.74-1.77-1.01-2.43-.27-.64-.54-.55-.74-.56h-.63c-.22 0-.58.08-.88.41-.3.33-1.15 1.13-1.15 2.75s1.18 3.19 1.34 3.41c.17.22 2.32 3.55 5.63 4.98.79.34 1.4.55 1.88.7.79.25 1.51.21 2.08.13.63-.1 1.95-.8 2.23-1.57.28-.77.28-1.44.19-1.57-.08-.14-.3-.22-.63-.38Z"/></svg>
</a>

<!-- Modal de agendamento -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal">
        <button class="modal-close" onclick="fecharAgendamento()" aria-label="Fechar">&times;</button>
        <h3>Agendar horário</h3>
        <div class="step-indicator">
            <span data-step="1" class="active"></span><span data-step="2"></span><span data-step="3"></span><span data-step="4"></span>
        </div>

        <!-- Etapa 1: serviços -->
        <div class="step active" data-step="1">
            <p style="color:var(--text-muted);font-size:14px;margin-top:0;">Escolha um ou mais serviços.</p>
            <div id="listaServicos">
                <?php foreach ($servicos as $s): ?>
                <label class="service-option">
                    <input type="checkbox" name="servico" value="<?= $s['id'] ?>" data-preco="<?= $s['preco'] ?>" data-nome="<?= htmlspecialchars($s['nome']) ?>">
                    <span class="name"><?= htmlspecialchars($s['nome']) ?></span>
                    <span class="price"><?= formatarMoeda($s['preco']) ?></span>
                </label>
                <?php endforeach; ?>
            </div>
            <div class="error-msg" id="erroServicos">Selecione ao menos um serviço.</div>
            <div class="step-actions">
                <button class="btn btn-primary" onclick="irParaEtapa(2)">Continuar</button>
            </div>
        </div>

        <!-- Etapa 2: data e horário -->
        <div class="step" data-step="2">
            <div class="form-field">
                <label for="inputData">Escolha a data</label>
                <input type="date" id="inputData" min="<?= $hoje ?>" max="<?= $dataMax ?>" value="<?= $hoje ?>">
            </div>
            <div class="form-field">
                <label>Horários disponíveis</label>
                <div id="slotsContainer"><div class="loading-msg">Carregando horários...</div></div>
                <div class="error-msg" id="erroHorario">Escolha um horário.</div>
            </div>
            <div class="step-actions">
                <button class="btn btn-outline" onclick="irParaEtapa(1)">Voltar</button>
                <button class="btn btn-primary" onclick="irParaEtapa(3)">Continuar</button>
            </div>
        </div>

        <!-- Etapa 3: dados do cliente -->
        <div class="step" data-step="3">
            <div class="summary-box" id="resumoEtapa3"></div>
            <div class="form-field">
                <label for="inputNome">Nome completo</label>
                <input type="text" id="inputNome" placeholder="Seu nome completo">
            </div>
            <div class="form-field">
                <label for="inputTelefone">Telefone / WhatsApp</label>
                <input type="tel" id="inputTelefone" placeholder="(21) 90000-0000">
            </div>
            <div class="error-msg" id="erroDados"></div>
            <div class="step-actions">
                <button class="btn btn-outline" onclick="irParaEtapa(2)">Voltar</button>
                <button class="btn btn-primary" id="btnConfirmar" onclick="confirmarAgendamento()">Confirmar agendamento</button>
            </div>
        </div>

        <!-- Etapa 4: confirmação -->
        <div class="step" data-step="4">
            <div style="text-align:center;">
                <div class="confirm-icon">&#10003;</div>
                <h3 style="margin-bottom:4px;">AGENDAMENTO CONFIRMADO!</h3>
                <p style="color:var(--text-muted);font-size:14px;margin-top:0;">Te esperamos na <?= htmlspecialchars($nomeBarbearia) ?>.</p>
            </div>
            <div class="summary-box" id="resumoFinal"></div>
            <div class="step-actions">
                <a class="btn btn-primary" id="linkWhatsappConfirmacao" target="_blank" rel="noopener">Confirmar pelo WhatsApp</a>
            </div>
            <div class="step-actions">
                <button class="btn btn-outline btn-block" onclick="fecharAgendamento(); location.reload();">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.WHATSAPP_NUMERO = <?= json_encode($whatsapp) ?>;
    window.NOME_BARBEARIA = <?= json_encode($nomeBarbearia) ?>;
// Procure por post('api/salvar_agendamento.php', ...) no seu index.php e mude o final para isto:
post('api/salvar_agendamento.php', dados)
    .then(({ ok, dados: resposta }) => {
        if (!ok) {
            // FORÇADO: Se o PHP mandou um erro real (ex: coluna faltando), mostra ele.
            // Se não, mostra o texto padrão.
            const erroReal = resposta && resposta.erro ? resposta.erro : 'Erro ao salvar o agendamento. Tente novamente.';
            
            // Procura a caixinha cinza/vermelha de erro da tela
            const caixinha = document.getElementById('erroSalvar') || document.querySelector('.error-msg');
            if (caixinha) {
                caixinha.innerText = erroReal; // Injeta o texto real aqui
                caixinha.style.display = 'block';
            }
            return;
        }
        
        alert('Agendamento realizado com sucesso!');
        location.reload();
    })
    .catch(err => {
        console.error(err);
        const caixinha = document.getElementById('erroSalvar') || document.querySelector('.error-msg');
        if (caixinha) {
            caixinha.innerText = 'Erro de conexão com o servidor.';
            caixinha.style.display = 'block';
        }
    });

</script>
<script src="assets/js/main.js"></script>
</body>
</html>
