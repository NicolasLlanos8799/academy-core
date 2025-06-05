<?php
require '../db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$id_profesor = $_POST['id_profesor'] ?? null;

if (!$id_profesor) {
    echo json_encode(['error' => 'Teacher ID not provided']);
    exit;
}

try {
    $sql = "SELECT 
                c.id,
                c.fecha,
                c.hora_inicio,
                c.hora_fin,
                c.alumno_nombre,
                TIME_FORMAT(TIMEDIFF(c.hora_fin, c.hora_inicio), '%H:%i') AS duracion,
                cp.importe_profesor
            FROM clases c
            INNER JOIN clases_pagadas cp ON c.id = cp.id_clase
            WHERE cp.id_profesor = ?
            ORDER BY c.fecha DESC, c.hora_inicio DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_profesor]);
    $clases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($clases);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error fetching paid classes: ' . $e->getMessage()]);
}
