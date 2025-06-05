<?php
require 'db.php'; // Database connection

header('Content-Type: application/json');

$profesor_id = $_POST['profesor_id'] ?? null;

if (!$profesor_id) {
    echo json_encode(['success' => false, 'message' => 'Missing professor ID']);
    exit;
}

// Traer datos del profesor
$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->execute([$profesor_id]);
$profesor = $stmt->fetch();
if (!$profesor) {
    echo json_encode(['success' => false, 'message' => 'Professor not found']);
    exit;
}

// Traer clases completadas y no pagadas de este profesor
// Ajusta el campo pagada=0 si tienes ese campo, o usa una tabla intermedia si lo prefieres
$stmt = $pdo->prepare("
    SELECT
        id,
        fecha,
        alumno_nombre,
        hora_inicio,
        hora_fin,
        tarifa_hora,
        observaciones
    FROM clases
    WHERE profesor_id = ?
      AND estado = 'completada'
      AND id NOT IN (SELECT clase_id FROM clases_pagadas)
    ORDER BY fecha ASC
");
$stmt->execute([$profesor_id]);
$clases = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_horas = 0;
$total_pagar = 0;
$detalle_clases = [];

foreach ($clases as $clase) {
    // Calcula duración en horas
    $inicio = new DateTime($clase['hora_inicio']);
    $fin = new DateTime($clase['hora_fin']);
    $duracion = ($fin->getTimestamp() - $inicio->getTimestamp()) / 3600;
    $importe = round($duracion * $clase['tarifa_hora'], 2);

    $detalle_clases[] = [
        'fecha' => $clase['fecha'],
        'alumno_nombre' => $clase['alumno_nombre'],
        'duracion' => number_format($duracion, 2),
        'tarifa_hora' => number_format($clase['tarifa_hora'], 2),
        'importe' => number_format($importe, 2),
        'observaciones' => $clase['observaciones'],
    ];

    $total_horas += $duracion;
    $total_pagar += $importe;
}

echo json_encode([
    'success' => true,
    'profesor_nombre' => $profesor['nombre'],
    'total_horas' => number_format($total_horas, 2),
    'total_pagar' => number_format($total_pagar, 2),
    'clases' => $detalle_clases,
]);
