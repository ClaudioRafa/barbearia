<?php
require_once __DIR__ . '/../config/conexao.php';

/* =====================================================
 * Configurações (tabela chave/valor)
 * ===================================================== */

function getConfiguracoes(): array
{
    static $cache = null;
    if ($cache !== null) return $cache;

    $pdo = conectarBanco();
    $stmt = $pdo->query('SELECT chave, valor FROM configuracoes');
    $cache = [];
    foreach ($stmt->fetchAll() as $row) {
        $cache[$row['chave']] = $row['valor'];
    }
    return $cache;
}

function getConfig(string $chave, $padrao = null)
{
    $config = getConfiguracoes();
    return $config[$chave] ?? $padrao;
}

function setConfig(string $chave, string $valor): void
{
    $pdo = conectarBanco();
    $stmt = $pdo->prepare('INSERT INTO configuracoes (chave, valor) VALUES (:c, :v)
        ON DUPLICATE KEY UPDATE valor = :v2');
    $stmt->execute(['c' => $chave, 'v' => $valor, 'v2' => $valor]);
}

/* =====================================================
 * Dias de funcionamento
 * ===================================================== */

function getDiasFuncionamento(): array
{
    $pdo = conectarBanco();
    $stmt = $pdo->query('SELECT * FROM dias_funcionamento ORDER BY dia_semana');
    $dias = [];
    foreach ($stmt->fetchAll() as $row) {
        $dias[(int)$row['dia_semana']] = $row;
    }
    return $dias;
}

function nomeDiaSemana(int $dia): string
{
    $nomes = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
    return $nomes[$dia] ?? '';
}

/* =====================================================
 * Geração de horários disponíveis para uma data
 * ===================================================== */

/**
 * Retorna lista de horários (HH:MM) disponíveis para uma data,
 * já excluindo almoço e horários ocupados/bloqueados.
 */
function getHorariosDisponiveis(string $data): array
{
    $pdo = conectarBanco();

    $timestamp = strtotime($data);
    if ($timestamp === false) return [];

    $diaSemana = (int)date('w', $timestamp);
    $dias = getDiasFuncionamento();
    $diaConfig = $dias[$diaSemana] ?? null;

    if (!$diaConfig || (int)$diaConfig['ativo'] === 0) {
        return []; // fechado nesse dia
    }

    $duracao = (int)getConfig('duracao_atendimento', 40);
    $almocoInicio = getConfig('almoco_inicio', '13:00');
    $almocoFim = getConfig('almoco_fim', '15:00');

    $abertura = strtotime($diaConfig['hora_abertura']);
    $fechamento = strtotime($diaConfig['hora_fechamento']);
    $almocoIni = strtotime($almocoInicio);
    $almocoF = strtotime($almocoFim);

    // Não permite gerar horários para datas passadas
    $hoje = strtotime(date('Y-m-d'));
    $agora = time();
    $ehHoje = ($timestamp === $hoje);

    $slots = [];
    for ($t = $abertura; $t + ($duracao * 60) <= $fechamento; $t += $duracao * 60) {
        $horaSlot = date('H:i', $t);

        // pula horários cujo atendimento (início ao fim) sobreponha o intervalo de almoço
        $tFim = $t + ($duracao * 60);
        if ($t < $almocoF && $tFim > $almocoIni) continue;

        // se for hoje, pula horários que já passaram
        if ($ehHoje && strtotime($data . ' ' . $horaSlot) <= $agora) continue;

        $slots[] = $horaSlot;
    }

    if (empty($slots)) return [];

    // busca horários já ocupados (reservado, concluido ou bloqueado) nessa data
    $stmt = $pdo->prepare("SELECT hora_inicio FROM agendamentos
        WHERE data = :data AND status IN ('reservado','concluido','bloqueado')");
    $stmt->execute(['data' => $data]);
    $ocupados = array_map(fn($h) => substr($h, 0, 5), $stmt->fetchAll(PDO::FETCH_COLUMN));

    return array_values(array_diff($slots, $ocupados));
}

function diaEhOrdemChegada(string $data): bool
{
    $timestamp = strtotime($data);
    $diaSemana = (int)date('w', $timestamp);
    $dias = getDiasFuncionamento();
    return isset($dias[$diaSemana]) && (int)$dias[$diaSemana]['ordem_chegada'] === 1;
}

/* =====================================================
 * Serviços
 * ===================================================== */

function getServicosAtivos(): array
{
    $pdo = conectarBanco();
    return $pdo->query('SELECT * FROM servicos WHERE ativo = 1 ORDER BY ordem, id')->fetchAll();
}

/* =====================================================
 * Formatação
 * ===================================================== */

function formatarMoeda(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function formatarDataBR(string $data): string
{
    $t = strtotime($data);
    return $t ? date('d/m/Y', $t) : $data;
}

function formatarTelefone(string $telefone): string
{
    $digitos = preg_replace('/\D/', '', $telefone);
    if (strlen($digitos) === 11) {
        return sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 5), substr($digitos, 7));
    }
    if (strlen($digitos) === 10) {
        return sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 4), substr($digitos, 6));
    }
    return $telefone;
}

/** Normaliza telefone para dígitos apenas (armazenamento) */
function normalizarTelefone(string $telefone): string
{
    return preg_replace('/\D/', '', $telefone);
}

function linkWhatsapp(string $numero, string $mensagem = ''): string
{
    $numero = preg_replace('/\D/', '', $numero);
    $url = 'https://wa.me/' . $numero;
    if ($mensagem !== '') {
        $url .= '?text=' . rawurlencode($mensagem);
    }
    return $url;
}

function jsonResponse($dados, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}
