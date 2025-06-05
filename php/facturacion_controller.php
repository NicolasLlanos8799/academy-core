<?php
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require 'db.php';

try {
    $stmt = $pdo->prepare("
        SELECT 
            YEAR(clases.fecha) AS anio,
            MONTH(clases.fecha) AS mes,
            SUM(clases.importe_pagado) AS total_facturado
        FROM clases
        WHERE clases.estado = 'completada'
        GROUP BY anio, mes
        ORDER BY anio DESC, mes DESC
    ");
    $stmt->execute();
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($resultado);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error',
        'details' => $e->getMessage()
    ]);
}
