(function () {
    'use strict';

    function post(url, payload) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(Object.assign({}, payload, { csrf_token: window.CSRF_TOKEN })),
        }).then(r => r.json().then(dados => ({ ok: r.ok, dados })));
    }

    /* =====================================================
       AGENDAMENTOS (admin/agendamentos.php)
       ===================================================== */
    const modalAgendamento = document.getElementById('modalAgendamento');

    window.abrirNovoAgendamento = function () {
        if (!modalAgendamento) return;
        document.getElementById('tituloModalAgendamento').textContent = 'Novo agendamento';
        document.getElementById('agendamentoId').value = '';
        document.getElementById('campoNome').value = '';
        document.getElementById('campoTelefone').value = '';
        document.getElementById('campoData').value = '';
        document.getElementById('campoHora').value = '';
        document.querySelectorAll('input[name="servicoModal"]').forEach(cb => cb.checked = false);
        esconderErro('erroModalAgendamento');
        modalAgendamento.classList.add('open');
    };

    window.abrirEdicao = function (agendamento) {
        if (!modalAgendamento) return;
        document.getElementById('tituloModalAgendamento').textContent = 'Editar agendamento';
        document.getElementById('agendamentoId').value = agendamento.id;
        document.getElementById('campoNome').value = agendamento.cliente_nome || '';
        document.getElementById('campoTelefone').value = agendamento.cliente_telefone || '';
        document.getElementById('campoData').value = agendamento.data;
        document.getElementById('campoHora').value = agendamento.hora_inicio.substring(0, 5);
        document.querySelectorAll('input[name="servicoModal"]').forEach(cb => cb.checked = false);
        esconderErro('erroModalAgendamento');
        modalAgendamento.classList.add('open');
    };

    window.fecharModalAgendamento = function () {
        modalAgendamento.classList.remove('open');
    };

    window.salvarAgendamento = function () {
        const id = document.getElementById('agendamentoId').value;
        const nome = document.getElementById('campoNome').value.trim();
        const telefone = document.getElementById('campoTelefone').value.trim();
        const data = document.getElementById('campoData').value;
        const hora = document.getElementById('campoHora').value.trim();
        const servicos = Array.from(document.querySelectorAll('input[name="servicoModal"]:checked')).map(cb => parseInt(cb.value, 10));

        const dadosPayload = { id, nome, telefone, data, hora, servicos };

        post('api/salvar_agendamento.php', dadosPayload)
            .then(({ ok, dados: resposta }) => {
                if (!ok) {
                    const erroVerdadeiro = resposta && resposta.erro ? resposta.erro : 'Erro ao salvar o agendamento. Tente novamente.';
                    const caixinhaErro = document.getElementById('erroModalAgendamento') || document.querySelector('.error-msg');
                    if (caixinhaErro) {
                        caixinhaErro.innerText = erroVerdadeiro;
                        caixinhaErro.style.display = 'block';
                    }
                    return;
                }
                alert('Agendamento realizado com sucesso!');
                location.reload();
            });
    };

    window.mudarStatus = function (id, status) {
        const rotulos = { concluido: 'marcar como concluído', cancelado: 'cancelar' };
        if (!confirm('Deseja ' + (rotulos[status] || 'alterar') + ' este agendamento?')) return;
        post('api/atualizar_status.php', { id, status }).then(({ ok, dados }) => {
            if (!ok) { alert(dados.erro || 'Erro ao atualizar.'); return; }
            location.reload();
        });
    };

    window.excluirAgendamento = function (id) {
        if (!confirm('Excluir este agendamento definitivamente? Essa ação não pode ser desfeita.')) return;
        post('api/excluir_agendamento.php', { id }).then(({ ok, dados }) => {
            if (!ok) { alert(dados.erro || 'Erro ao excluir.'); return; }
            location.reload();
        });
    };

    /* =====================================================
       SERVIÇOS (admin/servicos.php)
       ===================================================== */
    const modalServico = document.getElementById('modalServico');

    window.abrirNovoServico = function () {
        if (!modalServico) return;
        document.getElementById('tituloModalServico').textContent = 'Novo serviço';
        document.getElementById('servicoId').value = '';
        document.getElementById('servicoNome').value = '';
        document.getElementById('servicoPreco').value = '';
        document.getElementById('servicoOrdem').value = '';
        esconderErro('erroModalServico');
        modalServico.classList.add('open');
    };

    window.abrirEdicaoServico = function (servico) {
        if (!modalServico) return;
        document.getElementById('tituloModalServico').textContent = 'Editar serviço';
        document.getElementById('servicoId').value = servico.id;
        document.getElementById('servicoNome').value = servico.nome;
        document.getElementById('servicoPreco').value = String(servico.preco).replace('.', ',');
        document.getElementById('servicoOrdem').value = servico.ordem;
        esconderErro('erroModalServico');
        modalServico.classList.add('open');
    };

    window.fecharModalServico = function () {
        modalServico.classList.remove('open');
    };

    window.salvarServico = function () {
        const id = document.getElementById('servicoId').value;
        const nome = document.getElementById('servicoNome').value.trim();
        const preco = document.getElementById('servicoPreco').value.trim();
        const ordem = document.getElementById('servicoOrdem').value.trim() || '0';

        post('api/salvar_servico.php', { id, nome, preco, ordem }).then(({ ok, dados }) => {
            if (!ok) { mostrarErro('erroModalServico', dados.erro); return; }
            location.reload();
        });
    };

    window.excluirServico = function (id) {
        if (!confirm('Remover este serviço da lista de serviços ativos?')) return;
        post('api/excluir_servico.php', { id }).then(({ ok, dados }) => {
            if (!ok) { alert(dados.erro || 'Erro ao excluir.'); return; }
            location.reload();
        });
    };

    /* =====================================================
       CONFIGURAÇÕES (admin/configuracoes.php)
       ===================================================== */
    window.salvarConfiguracoes = function () {
        const el = id => document.getElementById(id);
        if (!el('cfgNome')) return;

        const geral = {
            nome_barbearia: el('cfgNome').value.trim(),
            whatsapp: el('cfgWhatsapp').value.trim(),
            duracao_atendimento: el('cfgDuracao').value.trim(),
            almoco_inicio: el('cfgAlmocoInicio').value.trim(),
            almoco_fim: el('cfgAlmocoFim').value.trim(),
            texto_hero: el('cfgTextoHero').value.trim(),
            texto_sobre: el('cfgTextoSobre').value.trim(),
        };

        const dias = Array.from(document.querySelectorAll('#tabelaDias tr')).map(tr => ({
            dia_semana: tr.dataset.dia,
            ativo: tr.querySelector('.dia-ativo').checked,
            hora_abertura: tr.querySelector('.dia-abertura').value.trim(),
            hora_fechamento: tr.querySelector('.dia-fechamento').value.trim(),
            ordem_chegada: tr.querySelector('.dia-ordem').checked,
        }));

        post('api/salvar_configuracoes.php', { geral, dias }).then(({ ok, dados }) => {
            const alerta = document.getElementById('alertaConfig');
            if (!ok) {
                alerta.innerHTML = '<div class="alert alert-error">' + (dados.erro || 'Erro ao salvar.') + '</div>';
                return;
            }
            alerta.innerHTML = '<div class="alert alert-success">Configurações salvas com sucesso!</div>';
        });
    };

    function mostrarErro(id, mensagem) {
        const el = document.getElementById(id);
        if (el) {
            el.innerText = mensagem || 'Ocorreu um erro.';
            el.style.display = 'block';
        }
    }

    function esconderErro(id) {
        const el = document.getElementById(id);
        if (el) {
            el.style.display = 'none';
        }
    }
})();
