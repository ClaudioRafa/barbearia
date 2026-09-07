(function () {
    'use strict';

    const overlay = document.getElementById('modalOverlay');
    const inputData = document.getElementById('inputData');
    const slotsContainer = document.getElementById('slotsContainer');

    let horarioSelecionado = null;
    let ultimoAgendamento = null;

    /* ---------- abrir / fechar modal ---------- */
    window.abrirAgendamento = function () {
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
        irParaEtapa(1);
        carregarHorarios();
    };

    window.fecharAgendamento = function () {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    };

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) fecharAgendamento();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay.classList.contains('open')) fecharAgendamento();
    });

    /* ---------- navegação entre etapas ---------- */
    window.irParaEtapa = function (n) {
        if (n === 2) {
            if (!validarServicos()) return;
            carregarHorarios();
        }
        if (n === 3) {
            if (!horarioSelecionado) {
                document.getElementById('erroHorario').classList.add('show');
                return;
            }
            montarResumo('resumoEtapa3');
        }

        document.querySelectorAll('.step').forEach(el => el.classList.remove('active'));
        document.querySelector('.step[data-step="' + n + '"]').classList.add('active');

        document.querySelectorAll('.step-indicator span').forEach(el => {
            el.classList.toggle('active', parseInt(el.dataset.step, 10) <= n);
        });
    };

    function validarServicos() {
        const marcados = document.querySelectorAll('input[name="servico"]:checked');
        const erro = document.getElementById('erroServicos');
        if (marcados.length === 0) {
            erro.classList.add('show');
            return false;
        }
        erro.classList.remove('show');
        return true;
    }

    document.querySelectorAll('input[name="servico"]').forEach(cb => {
        cb.addEventListener('change', () => document.getElementById('erroServicos').classList.remove('show'));
    });

    function servicosSelecionados() {
        return Array.from(document.querySelectorAll('input[name="servico"]:checked')).map(cb => ({
            id: parseInt(cb.value, 10),
            nome: cb.dataset.nome,
            preco: parseFloat(cb.dataset.preco),
        }));
    }

    /* ---------- carregar horários disponíveis ---------- */
    inputData.addEventListener('change', carregarHorarios);

    function carregarHorarios() {
        horarioSelecionado = null;
        document.getElementById('erroHorario').classList.remove('show');
        slotsContainer.innerHTML = '<div class="loading-msg">Carregando horários...</div>';

        fetch('api/horarios_disponiveis.php?data=' + encodeURIComponent(inputData.value))
            .then(r => r.json())
            .then(dados => {
                if (!dados.horarios || dados.horarios.length === 0) {
                    slotsContainer.innerHTML = '<div class="empty-msg">Nenhum horário disponível nessa data. Tente outro dia.</div>';
                    return;
                }
                let html = '';
                if (dados.ordem_chegada) {
                    html += '<div class="note-box" style="margin:0 0 12px;">Neste dia o atendimento é por ordem de chegada, mas você ainda pode garantir um horário reservando abaixo.</div>';
                }
                html += '<div class="slots-grid">';
                dados.horarios.forEach(h => {
                    html += '<button type="button" class="slot-btn" data-hora="' + h + '">' + h + '</button>';
                });
                html += '</div>';
                slotsContainer.innerHTML = html;

                slotsContainer.querySelectorAll('.slot-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        slotsContainer.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
                        btn.classList.add('selected');
                        horarioSelecionado = btn.dataset.hora;
                        document.getElementById('erroHorario').classList.remove('show');
                    });
                });
            })
            .catch(() => {
                slotsContainer.innerHTML = '<div class="empty-msg">Não foi possível carregar os horários. Tente novamente.</div>';
            });
    }

    /* ---------- resumo ---------- */
    function montarResumo(elementId) {
        const servicos = servicosSelecionados();
        const total = servicos.reduce((s, x) => s + x.preco, 0);
        const dataFormatada = formatarDataBR(inputData.value);

        let html = '';
        servicos.forEach(s => {
            html += '<div class="row"><span>' + escapeHtml(s.nome) + '</span><span>' + formatarMoeda(s.preco) + '</span></div>';
        });
        html += '<div class="row"><span>Data</span><span>' + dataFormatada + '</span></div>';
        html += '<div class="row"><span>Horário</span><span>' + horarioSelecionado + '</span></div>';
        html += '<div class="row total"><span>Total</span><span>' + formatarMoeda(total) + '</span></div>';

        document.getElementById(elementId).innerHTML = html;
    }

    /* ---------- confirmar agendamento ---------- */
    window.confirmarAgendamento = function () {
        const nome = document.getElementById('inputNome').value.trim();
        const telefone = document.getElementById('inputTelefone').value.trim();
        const erroDados = document.getElementById('erroDados');
        const btn = document.getElementById('btnConfirmar');

        if (nome.length < 3) {
            erroDados.textContent = 'Informe seu nome completo.';
            erroDados.classList.add('show');
            return;
        }
        const digitos = telefone.replace(/\D/g, '');
        if (digitos.length < 10) {
            erroDados.textContent = 'Informe um telefone válido com DDD.';
            erroDados.classList.add('show');
            return;
        }
        erroDados.classList.remove('show');

        const payload = {
            data: inputData.value,
            hora: horarioSelecionado,
            nome: nome,
            telefone: telefone,
            servicos: servicosSelecionados().map(s => s.id),
        };

        btn.disabled = true;
        btn.textContent = 'Confirmando...';

        fetch('api/criar_agendamento.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        })
            .then(r => r.json().then(dados => ({ ok: r.ok, dados })))
            .then(({ ok, dados }) => {
                btn.disabled = false;
                btn.textContent = 'Confirmar agendamento';

                if (!ok) {
                    erroDados.textContent = dados.erro || 'Não foi possível confirmar. Tente novamente.';
                    erroDados.classList.add('show');
                    if (ehConflitoDeHorario(dados)) carregarHorarios();
                    return;
                }

                ultimoAgendamento = dados.agendamento;
                montarResumoFinal();
                irParaEtapa(4);
            })
            .catch(() => {
                btn.disabled = false;
                btn.textContent = 'Confirmar agendamento';
                erroDados.textContent = 'Erro de conexão. Verifique sua internet e tente novamente.';
                erroDados.classList.add('show');
            });
    };

    function ehConflitoDeHorario(dados) {
        return dados && dados.erro && dados.erro.indexOf('reservado') !== -1;
    }

    function montarResumoFinal() {
        const a = ultimoAgendamento;
        let html = '';
        (a.servicos || []).forEach(nome => {
            html += '<div class="row"><span>' + escapeHtml(nome) + '</span></div>';
        });
        html += '<div class="row"><span>Cliente</span><span>' + escapeHtml(a.nome) + '</span></div>';
        html += '<div class="row"><span>Data</span><span>' + a.data_formatada + '</span></div>';
        html += '<div class="row"><span>Horário</span><span>' + a.hora + '</span></div>';
        html += '<div class="row total"><span>Total</span><span>' + a.valor_formatado + '</span></div>';
        document.getElementById('resumoFinal').innerHTML = html;

        const msg = 'Olá! Confirmando meu agendamento na ' + window.NOME_BARBEARIA + ': ' +
            (a.servicos || []).join(', ') + ' no dia ' + a.data_formatada + ' às ' + a.hora + '.';
        document.getElementById('linkWhatsappConfirmacao').href =
            'https://wa.me/' + window.WHATSAPP_NUMERO + '?text=' + encodeURIComponent(msg);
    }

    /* ---------- utilitários ---------- */
    function formatarMoeda(v) {
        return 'R$ ' + v.toFixed(2).replace('.', ',');
    }

    function formatarDataBR(dataIso) {
        const [ano, mes, dia] = dataIso.split('-');
        return dia + '/' + mes + '/' + ano;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
})();
