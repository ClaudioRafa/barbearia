<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['erro' => 'Método não permitido.'], 405);
}

$pdo = conectarBanco();

/* -------- configurações gerais -------- */
$geral = $input['geral'] ?? [];
$camposPermitidos = ['nome_barbearia', 'whatsapp', 'texto_hero', 'texto_sobre', 'duracao_atendimento', 'almoco_inicio', 'almoco_fim'];

foreach ($camposPermitidos as $campo) {
    if (isset($geral[$campo])) {
        $valor = trim((string)$geral[$campo]);
        if ($campo === 'whatsapp') $valor = normalizarTelefone($valor);
        if ($campo === 'duracao_atendimento') $valor = (string)max(10, (int)$valor);
        setConfig($campo, $valor);
    }
}

/* -------- dias de funcionamento -------- */
$dias = $input['dias'] ?? [];
if (is_array($dias)) {
    $stmt = $pdo->prepare('UPDATE dias_funcionamento SET ativo=:ativo, hora_abertura=:abertura, hora_fechamento=:fechamento, ordem_chegada=:ordem
        WHERE dia_semana = :dia');
    foreach ($dias as $d) {
        if (!isset($d['dia_semana'])) continue;
        $stmt->execute([
            'ativo' => !empty($d['ativo']) ? 1 : 0,
            'abertura' => $d['hora_abertura'] ?? '09:00',
            'fechamento' => $d['hora_fechamento'] ?? '20:00',
            'ordem' => !empty($d['ordem_chegada']) ? 1 : 0,
            'dia' => (int)$d['dia_semana'],
        ]);
    }
}

jsonResponse(['sucesso' => true]);
