<?php
// ===========================================================
// File: editar_clase_profesor.php
// Purpose: Allows a teacher to edit their own class.
// Also sends email notifications to teacher and student.
// Cannot modify paid amount, assigned teacher, or status.
// ===========================================================

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $fecha = $_POST['fecha'] ?? '';
    $hora_inicio = $_POST['hora_inicio'] ?? '';
    $hora_fin = $_POST['hora_fin'] ?? '';
    $alumno = trim($_POST['alumno'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $observaciones = trim($_POST['observaciones'] ?? '');

    if (empty($id) || empty($fecha) || empty($hora_inicio) || empty($hora_fin) || empty($alumno)) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be completed']);
        exit;
    }

    if (strtotime($hora_inicio) >= strtotime($hora_fin)) {
        echo json_encode(['success' => false, 'message' => 'Start time must be earlier than end time.']);
        exit;
    }

    $profesorId = $_SESSION['user_id'] ?? null;
    if (!$profesorId) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM clases WHERE id = ? AND profesor_id = ?");
    $stmt->execute([$id, $profesorId]);
    $clase = $stmt->fetch();

    if (!$clase) {
        echo json_encode(['success' => false, 'message' => 'Class not found or does not belong to the teacher']);
        exit;
    }

    // Update only allowed fields
    $stmt = $pdo->prepare("
        UPDATE clases 
        SET fecha = ?, hora_inicio = ?, hora_fin = ?, alumno_nombre = ?, email = ?, telefono = ?, observaciones = ?
        WHERE id = ? AND profesor_id = ?
    ");
    $stmt->execute([$fecha, $hora_inicio, $hora_fin, $alumno, $email, $telefono, $observaciones, $id, $profesorId]);

    // 🔔 Notify teacher and student by email
    $stmt = $pdo->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
    $stmt->execute([$profesorId]);
    $profesor = $stmt->fetch();

    if ($profesor) {
        $GLOBALS['datos_correo'] = [
            'nombre_profesor' => $profesor['nombre'],
            'correo_profesor' => $profesor['email'],
            'nombre_alumno'   => $alumno,
            'correo_alumno'   => $email,
            'fecha'           => $fecha,
            'hora_inicio'     => $hora_inicio,
            'hora_fin'        => $hora_fin,
            'tarifa_hora'     => $tarifa_hora,        // ✅ added
            'observaciones'   => $observaciones,
            'pago_efectivo'   => $pago_efectivo,
            'pago_tarjeta'    => $pago_tarjeta,
            'importe_pagado'  => $importe_pagado,      // ✅ added
            'tipo'            => 'editar'
        ];        

        ob_start();
        include __DIR__ . '/../enviar_correo_clase.php';  // 👈 same file used by admin
        ob_end_clean();
    }

    echo json_encode(['success' => true]);
}
