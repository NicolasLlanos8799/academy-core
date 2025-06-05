<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php';

$anio = $_GET['anio'] ?? null;
$mes = $_GET['mes'] ?? null;

if (!$anio || !$mes) {
    echo json_encode(["error" => "Missing parameters"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
    SELECT 
        clases.fecha AS fecha_clase,
        usuarios.nombre AS profesor,
        clases.alumno_nombre AS alumno,
        clases.pago_efectivo,
        clases.pago_tarjeta,
        clases.importe_pagado,
        clases.tarifa_hora,
        TIMESTAMPDIFF(MINUTE, clases.hora_inicio, clases.hora_fin) / 60 AS duracion_horas,
        ROUND(clases.tarifa_hora * (TIMESTAMPDIFF(MINUTE, clases.hora_inicio, clases.hora_fin) / 60), 2) AS importe_profesor
    FROM clases
    JOIN usuarios ON clases.profesor_id = usuarios.id
    WHERE clases.estado = 'completada'
      AND YEAR(clases.fecha) = ?
      AND MONTH(clases.fecha) = ?
    ORDER BY clases.fecha ASC
");

    $stmt->execute([$anio, $mes]);
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($resultado);
} catch (PDOException $e) {
    echo json_encode([
        "error" => "Database error", 
        "details" => $e->getMessage()
    ]);
}
