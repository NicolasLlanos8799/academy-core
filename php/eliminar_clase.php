<?php
// ===========================================================
// File: eliminar_clase.php
// Purpose: Deletes a class, recalculates payments, and sends email notification.
// ===========================================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = $_POST['id'] ?? '';

        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Class ID not provided']);
            exit;
        }

        // Retrieve class data before deleting
        $stmt = $pdo->prepare("SELECT * FROM clases WHERE id = ?");
        $stmt->execute([$id]);
        $clase = $stmt->fetch();

        if (!$clase) {
            echo json_encode(['success' => false, 'message' => 'Class not found']);
            exit;
        }

        $profesor_id = $clase['profesor_id'];
        $tarifa_hora = $clase['tarifa_hora'] ?? 0;

        // Get instructor's name and email
        $stmt = $pdo->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
        $stmt->execute([$profesor_id]);
        $profesor = $stmt->fetch();

        // Delete the class
        $stmt = $pdo->prepare("DELETE FROM clases WHERE id = ?");
        $stmt->execute([$id]);

        // Recalculate teacher's pending payments
        $stmt = $pdo->prepare("
            SELECT SUM(TIMESTAMPDIFF(HOUR, hora_inicio, hora_fin)) AS total_horas 
            FROM clases 
            WHERE profesor_id = ? AND estado = 'pendiente'
        ");
        $stmt->execute([$profesor_id]);
        $resultado = $stmt->fetch();
        $total_horas = $resultado['total_horas'] ?? 0;

        $total_pagar = $total_horas * $tarifa_hora;

        $stmt = $pdo->prepare("UPDATE pagos SET total_horas = ?, total = ? WHERE profesor_id = ? AND estado = 'pendiente'");
        $stmt->execute([$total_horas, $total_pagar, $profesor_id]);

        // Send cancellation email (if enough data is available)
        if ($profesor && !empty($clase['email'])) {
            $GLOBALS['datos_correo'] = [
                'nombre_profesor' => $profesor['nombre'],
                'correo_profesor' => $profesor['email'],
                'nombre_alumno'   => $clase['alumno_nombre'],
                'correo_alumno'   => $clase['email'],
                'fecha'           => $clase['fecha'],
                'hora_inicio'     => $clase['hora_inicio'],
                'hora_fin'        => $clase['hora_fin'],
                'tarifa_hora'     => $clase['tarifa_hora'] ?? 0,
                'observaciones'   => $clase['observaciones'] ?? '',
                'pago_efectivo'   => $clase['pago_efectivo'] ?? 0,
                'pago_tarjeta'    => $clase['pago_tarjeta'] ?? 0,
                'importe_pagado'  => $clase['importe_pagado'] ?? 0,
                'tipo'            => 'eliminar'
            ];            

            ob_start();
            include __DIR__ . '/enviar_correo_clase.php';
            ob_end_clean();
        }

        echo json_encode(['success' => true]);
        exit;

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error deleting class: ' . $e->getMessage()]);
        exit;
    }
}

// If not POST, return method error
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
exit;
