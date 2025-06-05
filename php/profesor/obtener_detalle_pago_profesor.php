<?php
require '../db.php';
header('Content-Type: application/json');

$pago_id = $_GET['id'] ?? '';
$profesor_id = $_GET['profesor_id'] ?? '';

if (empty($pago_id) || empty($profesor_id)) {
    echo json_encode(['success' => false, 'message' => 'Payment ID or teacher ID not provided']);
    exit;
}

try {
    $stmt = $pdo->prepare("
    SELECT 
        c.fecha,
        c.alumno_nombre,
        TIME_FORMAT(TIMEDIFF(c.hora_fin, c.hora_inicio), '%H:%i') AS duracion,
        c.tarifa_hora,
        ROUND(TIMESTAMPDIFF(MINUTE, c.hora_inicio, c.hora_fin) / 60 * c.tarifa_hora, 2) AS importe
    FROM clases_pagadas cp
    JOIN clases c ON cp.clase_id = c.id
    WHERE cp.pago_id = ? AND c.profesor_id = ?
    ORDER BY c.fecha, c.hora_inicio
");

    $stmt->execute([$pago_id, $profesor_id]);
    $clases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($clases);

} catch (Exception $e) {
    echo json_encode(['error' => 'Error fetching paid classes']);
}
