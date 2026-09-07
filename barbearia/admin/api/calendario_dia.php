<?php
require_once __DIR__ . '/_bootstrap.php';

$data = $_GET['data'] ?? '';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
    jsonResponse(['erro' => 'Data inválida.'], 400);
}

$timestamp = strtotime($data);
$diaSemana = (int)date('w', $timestamp);
$dias = getDiasFuncionamento();
$diaConfig = $dias[$diaSemana] ?? null;

if (!$diaConfig || (int)$diaConfig['ativo'] === 0) {
    jsonResponse(['fechado' => true, 'slots' => []]);
}

$duracao = (int)getConfig('duracao_atendimento', 40);
$almocoIni = strtotime(getConfig('almoco_inicio', '13:00'));
$almocoFim = strtotime(getConfig('almoco_fim', '15:00'));
$abertura = strtotime($diaConfig['hora_abertura']);
$fechamento = strtotime($diaConfig['hora_fechamento']);

$pdo = conectarBanco();
$stmt = $pdo->prepare("SELECT a.*, GROUP_CONCAT(s.nome_servico SEPARATOR ', ') AS servicos_nomes
    FROM agendamentos a
    LEFT JOIN agendamento_servicos s ON s.agendamento_id = a.id
    WHERE a.data = :data
    GROUP BY a.id
    ORDER BY a.id DESC");
$stmt->execute(['data' => $data]);

// mantém apenas o registro mais recente por horário (última palavra sobre aquele slot)
$porHorario = [];
foreach ($stmt->fetchAll() as $row) {
    $h = substr($row['hora_inicio'], 0, 5);
    if (!isset($porHorario[$h])) $porHorario[$h] = $row;
}

$slots = [];
for ($t = $abertura; $t + ($duracao * 60) <= $fechamento; $t += $duracao * 60) {
    $hora = date('H:i', $t);
    $tFim = $t + ($duracao * 60);
    $ehAlmoco = ($t < $almocoFim && $tFim > $almocoIni);

    if ($ehAlmoco) {
        $slots[] = ['hora' => $hora, 'status' => 'almoco', 'cliente' => null, 'servicos' => null, 'id' => null];
        continue;
    }

    if (isset($porHorario[$hora])) {
        $reg = $porHorario[$hora];
        $slots[] = [
            'hora' => $hora,
            'status' => $reg['status'],
            'id' => (int)$reg['id'],
            'cliente' => $reg['cliente_nome'],
            'telefone' => $reg['cliente_telefone'] ? formatarTelefone($reg['cliente_telefone']) : null,
            'servicos' => $reg['servicos_nomes'],
            'valor' => $reg['valor_total'] !== null ? formatarMoeda((float)$reg['valor_total']) : null,
        ];
    } else {
        $slots[] = ['hora' => $hora, 'status' => 'livre', 'cliente' => null, 'servicos' => null, 'id' => null];
    }
}

jsonResponse(['fechado' => false, 'ordem_chegada' => (int)$diaConfig['ordem_chegada'] === 1, 'slots' => $slots]);
