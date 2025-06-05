<?php
// ===========================================================
// File: clases_por_pago.php
// Functionality: Returns all classes associated with a payment
// ===========================================================

require 'db.php';
header('Content-Type: application/json');

$pago_id = $_GET['pago_id'] ?? '';

if (empty($pago_id)) {
    echo json_encode(['success' => false, 'message' => 'Payment ID not provided']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            c.fecha,
            c.hora_inicio,
            c.hora_fin,
            c.alumno_nombre,
            c.tarifa_hora
        FROM clases_pagadas cp
        JOIN clases c ON cp.clase_id = c.id
        WHERE cp.pago_id = ?
        ORDER BY c.fecha, c.hora_inicio
    ");

    $stmt->execute([$pago_id]);
    $clases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'clases' => $clases]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error retrieving paid classes']);
}
?>
