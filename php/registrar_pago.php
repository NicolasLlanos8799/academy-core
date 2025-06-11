<?php
// ===========================================================
// File: registrar_pago.php
// Functionality: Registers a new payment and marks related classes as paid
// ===========================================================

require 'db.php'; // Database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';

// Cargar configuración de la escuela
$config = include __DIR__ . '/school_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profesor_id = $_POST['profesor_id'] ?? null;
    if (empty($profesor_id)) {
        echo json_encode(['success' => false, 'message' => 'Missing professor ID']);
        exit;
    }

    // Get the instructor name and email
    $stmt = $pdo->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
    $stmt->execute([$profesor_id]);
    $profesor = $stmt->fetch();

    if (!$profesor) {
        echo json_encode(['success' => false, 'message' => 'Instructor not found']);
        exit;
    }
    $profesor_nombre = $profesor['nombre'];
    $correo_profesor = $profesor['email'];

    // Get all completed classes not yet paid
    $stmt = $pdo->prepare("
        SELECT id, hora_inicio, hora_fin, tarifa_hora
        FROM clases 
        WHERE profesor_id = ? 
        AND estado = 'completada' 
        AND id NOT IN (SELECT clase_id FROM clases_pagadas)
    ");
    $stmt->execute([$profesor_id]);
    $clases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($clases) === 0) {
        echo json_encode(['success' => false, 'message' => 'No completed classes to register']);
        exit;
    }

    // Calculate total hours and total amount
    $total_horas = 0;
    $total = 0;
    foreach ($clases as $clase) {
        $inicio = new DateTime($clase['hora_inicio']);
        $fin = new DateTime($clase['hora_fin']);
        $duracion = ($fin->getTimestamp() - $inicio->getTimestamp()) / 3600;
        $total_horas += $duracion;
        $total += $duracion * $clase['tarifa_hora'];
    }
    $total_horas = round($total_horas, 2);
    $total = round($total, 2);

    // Insert the payment
    $stmt = $pdo->prepare("INSERT INTO pagos (profesor_id, total_horas, total, estado, fecha_pago) VALUES (?, ?, ?, 'pagado', NOW())");
    if (!$stmt->execute([$profesor_id, $total_horas, $total])) {
        echo json_encode(['success' => false, 'message' => 'Error registering the payment']);
        exit;
    }
    $pago_id = $pdo->lastInsertId();

    // Associate paid classes with the payment
    $stmt = $pdo->prepare("INSERT INTO clases_pagadas (clase_id, pago_id) VALUES (?, ?)");
    foreach ($clases as $clase) {
        $stmt->execute([$clase['id'], $pago_id]);
    }

    // ✅ Enviar email al profesor notificando el pago
    $mensajeHTML = "<div style='color: #000; font-family: sans-serif;'>";
    $mensajeHTML .= "<h3 style='color: #000;'>Payment Registered</h3>";
    $mensajeHTML .= "<p style='margin-top:20px; color:#000;'>Hi <strong>{$profesor_nombre}</strong>,</p>";
    $mensajeHTML .= "<p style='color:#000;'>We have registered a payment to your name on <strong>" . date('d/m/Y') . "</strong> for a total amount of <strong>€{$total}</strong>, corresponding to your completed classes.</p>";
    $mensajeHTML .= "<p style='color:#000;'>You can see the full details in the <strong>Payments</strong> section of your panel.</p>";
    $mensajeHTML .= "<p style='margin-top:20px; color:#000;'>Thank you for your work!</p>";
    $mensajeHTML .= "<p style='color:#000;'><em>{$config['nombre_escuela']}</em></p>";
    $mensajeHTML .= "<p style='font-size:12px; color:#999;'>Ref ID: " . uniqid('ref-') . "</p>";
    $mensajeHTML .= "</div>";

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $config['email_contacto'];
        $mail->Password = $config['email_password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($config['email_contacto'], $config['nombre_remitente']);
        $mail->addAddress($correo_profesor, $profesor_nombre);
        $mail->isHTML(true);
        $mail->Subject = '=?UTF-8?B?' . base64_encode('Payment Registered') . '?=';
        $mail->Body = $mensajeHTML;
        $mail->send();
    } catch (Exception $e) {
        error_log("Error sending payment email: " . $mail->ErrorInfo);
    }

    echo json_encode(['success' => true]);
}
?>
