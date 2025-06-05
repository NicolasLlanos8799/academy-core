<?php
require '../db.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$id_profesor = $_POST['id_profesor'] ?? null;

if (!$id_profesor) {
    echo json_encode(['error' => 'Teacher ID not provided']);
    exit;
}

try {
    // Query to get completed classes that haven't been paid yet
    $sql = "SELECT 
    c.id,
    c.fecha,
    c.hora_inicio,
    c.hora_fin,
    c.alumno_nombre,
    TIME_FORMAT(TIMEDIFF(c.hora_fin, c.hora_inicio), '%H:%i') AS duracion,
    c.importe_pagado * 0.3 AS importe_profesor
FROM clases c
LEFT JOIN clases_pagadas cp ON c.id = cp.clase_id
WHERE c.estado = 'completada'
  AND c.profesor_id = ?
  AND cp.clase_id IS NULL
ORDER BY c.fecha DESC, c.hora_inicio DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_profesor]);
    $clases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($clases);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error fetching classes: ' . $e->getMessage()]);
}
