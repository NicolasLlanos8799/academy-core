<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profesor_id = $_POST['profesor_id'] ?? '';
    $fecha = $_POST['fecha'] ?? '';
    $hora_inicio = $_POST['hora_inicio'] ?? '';
    $hora_fin = $_POST['hora_fin'] ?? '';
    $alumno = $_POST['alumno'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';
    $tarifa_hora = isset($_POST['tarifa_hora']) ? floatval($_POST['tarifa_hora']) : null;

    // New fields
    $pago_efectivo = isset($_POST['pago_efectivo']) ? floatval($_POST['pago_efectivo']) : 0;
    $pago_tarjeta = isset($_POST['pago_tarjeta']) ? floatval($_POST['pago_tarjeta']) : 0;
    $importe_pagado = $pago_efectivo + $pago_tarjeta;

    // Basic validation
    if (empty($profesor_id) || empty($fecha) || empty($hora_inicio) || empty($hora_fin) || empty($alumno)) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be completed']);
        exit;
    }

    // Insert with new fields
    $stmt = $pdo->prepare("
        INSERT INTO clases (
            profesor_id,
            tarifa_hora,
            fecha,
            hora_inicio,
            hora_fin,
            alumno_nombre,
            email,
            telefono,
            observaciones,
            pago_efectivo,
            pago_tarjeta,
            importe_pagado,
            estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')
    ");

    $insercion = $stmt->execute([
        $profesor_id,
        $tarifa_hora,
        $fecha,
        $hora_inicio,
        $hora_fin,
        $alumno,
        $email,
        $telefono,
        $observaciones,
        $pago_efectivo,
        $pago_tarjeta,
        $importe_pagado
    ]);

    // Email notification (reused logic)
    if ($insercion) {
        $stmt_prof = $pdo->prepare("SELECT nombre, email FROM usuarios WHERE id = ? AND rol = 'profesor'");
        $stmt_prof->execute([$profesor_id]);
        $profesor = $stmt_prof->fetch(PDO::FETCH_ASSOC);

        if ($profesor) {
            $GLOBALS['datos_correo'] = [
                'nombre_profesor' => $profesor['nombre'],
                'correo_profesor' => $profesor['email'],
                'nombre_alumno'   => $alumno,
                'correo_alumno'   => $email,
                'fecha'           => $fecha,
                'hora_inicio'     => $hora_inicio,
                'hora_fin'        => $hora_fin,
                'tarifa_hora'     => $tarifa_hora,
                'observaciones'   => $observaciones,
                'pago_efectivo'   => $pago_efectivo,
                'pago_tarjeta'    => $pago_tarjeta,
                'importe_pagado'  => $importe_pagado,
                'tipo'            => 'crear'
            ];
        
            ob_start();
            include __DIR__ . '/enviar_correo_clase.php';
            ob_end_clean();
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error assigning class']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
