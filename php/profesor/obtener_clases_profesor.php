<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

// 🛑 Check if logged in as a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$idProfesor = $_SESSION['user_id'];

try {
    // ✅ Corregido: usamos clases.tarifa_hora en vez de usuarios
    $stmt = $pdo->prepare("SELECT clases.id, clases.alumno_nombre, clases.email, clases.telefono, clases.fecha, clases.hora_inicio, clases.hora_fin, clases.estado, clases.observaciones, clases.importe_pagado, clases.tarifa_hora
                           FROM clases
                           WHERE clases.profesor_id = ?");
    $stmt->execute([$idProfesor]);

    $clases = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $clases[] = [
            'id' => $row['id'],
            'title' => $row['alumno_nombre'],
            'start' => $row['fecha'] . 'T' . $row['hora_inicio'],
            'end' => $row['fecha'] . 'T' . $row['hora_fin'],
            'alumno' => $row['alumno_nombre'],
            'email' => $row['email'],
            'telefono' => $row['telefono'],
            'observaciones' => $row['observaciones'],
            'estado' => $row['estado'],
            'tarifa_hora' => $row['tarifa_hora']
        ];
    }

    echo json_encode($clases);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Query error: ' . $e->getMessage()]);
    exit;
}
?>
