<?php
require_once __DIR__ . '/../includes/functions.php';

$data = $_GET['data'] ?? '';

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
    jsonResponse(['erro' => 'Data inválida.'], 400);
}

// não permite consultar datas passadas
if (strtotime($data) < strtotime(date('Y-m-d'))) {
    jsonResponse(['horarios' => [], 'ordem_chegada' => false]);
}

$horarios = getHorariosDisponiveis($data);
$ordemChegada = diaEhOrdemChegada($data);

jsonResponse([
    'horarios' => $horarios,
    'ordem_chegada' => $ordemChegada,
]);
