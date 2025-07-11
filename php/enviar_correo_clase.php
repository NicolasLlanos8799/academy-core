<?php
ob_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';

// Cargar configuración de la escuela
$config = include __DIR__ . '/school_config.php';

if (!isset($GLOBALS['datos_correo'])) {
    ob_end_clean();
    return;
}

$datos = $GLOBALS['datos_correo'];

$nombre_profesor  = $datos['nombre_profesor'] ?? '';
$correo_profesor  = $datos['correo_profesor'] ?? '';
$nombre_alumno    = $datos['nombre_alumno'] ?? '';
$correo_alumno    = $datos['correo_alumno'] ?? '';
$fecha_clase      = $datos['fecha'] ?? '';
$hora_inicio      = $datos['hora_inicio'] ?? '';
$hora_fin         = $datos['hora_fin'] ?? '';
$tarifa_hora      = $datos['tarifa_hora'] ?? '';
$observaciones    = $datos['observaciones'] ?? '';
$tipo             = $datos['tipo'] ?? 'crear';
$pago_efectivo    = $datos['pago_efectivo'] ?? 0;
$pago_tarjeta     = $datos['pago_tarjeta'] ?? 0;
$importe_pagado   = $datos['importe_pagado'] ?? 0;

// ICS CALENDAR
$uid = 'class-' . uniqid() . '@surfschool';
$method = $tipo === 'eliminar' ? 'CANCEL' : 'REQUEST';
$sequence = match ($tipo) {
    'crear' => 0,
    'editar' => 1,
    'eliminar' => 2,
    default => 0
};

$dtstamp = gmdate('Ymd\THis\Z');
$dtstart = gmdate('Ymd\THis\Z', strtotime("$fecha_clase $hora_inicio"));
$dtend   = gmdate('Ymd\THis\Z', strtotime("$fecha_clase $hora_fin"));

// -- Adaptar textos con config
$SUMMARY = match ($tipo) {
    'editar' => $config['nombre_escuela'] . ' Class Updated',
    'eliminar' => $config['nombre_escuela'] . ' Class CANCELLED',
    default => $config['nombre_escuela'] . ' Class'
};

$contenido_ics = "BEGIN:VCALENDAR\r\n";
$contenido_ics .= "VERSION:2.0\r\n";
$contenido_ics .= "PRODID:-//{$config['nombre_escuela']}//EN\r\n";
$contenido_ics .= "METHOD:$method\r\n";
$contenido_ics .= "BEGIN:VEVENT\r\n";
$contenido_ics .= "UID:$uid\r\n";
$contenido_ics .= "SEQUENCE:$sequence\r\n";
$contenido_ics .= "DTSTAMP:$dtstamp\r\n";
$contenido_ics .= "DTSTART:$dtstart\r\n";
$contenido_ics .= "DTEND:$dtend\r\n";
$contenido_ics .= "SUMMARY:$SUMMARY\r\n";
if ($tipo === 'eliminar') {
    $contenido_ics .= "STATUS:CANCELLED\r\n";
}
$contenido_ics .= "DESCRIPTION:Class with $nombre_alumno and $nombre_profesor.\r\n";
$contenido_ics .= "LOCATION:{$config['direccion']}\r\n";
$contenido_ics .= "ORGANIZER;CN={$config['nombre_escuela']}:mailto:{$config['email_contacto']} \r\n";
$contenido_ics .= "ATTENDEE;CN=$nombre_profesor;RSVP=TRUE;PARTSTAT=NEEDS-ACTION:mailto:$correo_profesor\r\n";
$contenido_ics .= "ATTENDEE;CN=$nombre_alumno;RSVP=TRUE;PARTSTAT=NEEDS-ACTION:mailto:$correo_alumno\r\n";
$contenido_ics .= "END:VEVENT\r\n";
$contenido_ics .= "END:VCALENDAR\r\n";

$archivo_ics = tempnam(sys_get_temp_dir(), 'class_') . '.ics';
file_put_contents($archivo_ics, $contenido_ics);

$destinatarios = [
    [$correo_profesor, $nombre_profesor],
    [$correo_alumno, $nombre_alumno]
];

foreach ($destinatarios as [$email, $nombre]) {
    if (empty($email)) continue;

    $esProfesor = ($email === $correo_profesor);

    $tituloCorreo = match ($tipo) {
        'editar'   => $esProfesor ? 'Class You Will Teach Was Updated' : 'Your Surf Kite Class Was Updated',
        'eliminar' => $esProfesor ? 'Class Cancelled' : 'Your Surf Kite Class Was Cancelled',
        default    => $esProfesor ? 'New Class Assigned to You' : 'You Have a New Surf Kite Class'
    };

    $mensajeIntro = match ($tipo) {
        'editar'   => $esProfesor ?
            'A class you were scheduled to teach has been updated.' :
            'Your scheduled class has been updated.',
        'eliminar' => $esProfesor ?
            'The class you were scheduled to teach has been cancelled.' :
            'Your upcoming class has been cancelled.',
        default    => $esProfesor ?
            'A new class has been assigned.' :
            'You have been enrolled in a new Surf Kite class.'
    };

    // ----------- INICIO MENSAJE HTML (caso CANCELACIÓN personalizado) -------------
    if ($tipo === 'eliminar') {
        $mensajeHTML = "<div style='color: #000; font-family: Arial, sans-serif;'>";
        $mensajeHTML .= "<h3 style='color: #000;'>$tituloCorreo</h3>";
        $mensajeHTML .= "<p style='margin-top:20px; color:#000;'>$mensajeIntro</p>";
        $mensajeHTML .= "<ul style='font-size:15px; padding-left:18px;'>";
        if ($esProfesor) {
            $mensajeHTML .= "
                <li style='color:#000;'><strong style='color:#000;'>Date:</strong> " . date('d-m-Y', strtotime($fecha_clase)) . "</li>
                <li style='color:#000;'><strong style='color:#000;'>Time:</strong> $hora_inicio to $hora_fin</li>
                <li style='color:#000;'><strong style='color:#000;'>Participant:</strong> $nombre_alumno</li>
            ";
        } else {
            $mensajeHTML .= "
                <li style='color:#000;'><strong style='color:#000;'>Date:</strong> " . date('d-m-Y', strtotime($fecha_clase)) . "</li>
                <li style='color:#000;'><strong style='color:#000;'>Time:</strong> $hora_inicio to $hora_fin</li>
                <li style='color:#000;'><strong style='color:#000;'>Instructor:</strong> $nombre_profesor</li>
            ";
        }
        $mensajeHTML .= "</ul>";
        $mensajeHTML .= "<p style='margin-top:18px;'>If you need more information, please contact us at <a href='mailto:{$config['email_contacto']}'>{$config['email_contacto']}</a> or WhatsApp <a href='tel:{$config['telefono']}'>{$config['telefono']}</a>.</p>";
        $mensajeHTML .= "<p>Best regards,<br>Rebels Tarifa KiteSchool</p>";
        $mensajeHTML .= "<p style='font-size:12px; color:#999; margin-top:22px;'>Ref ID: " . uniqid('ref-') . "</p>";
        $mensajeHTML .= "</div>";

    } else {
        // ----------- MENSAJE HTML para crear/editar: igual que antes -------------
        $mensajeHTML = "<div style='color: #000; font-family: Arial, sans-serif;'>";
        $mensajeHTML .= "<h3 style='color: #000;'>$tituloCorreo</h3>";

        if (!$esProfesor) {
            $mensajeHTML .= "<p style='
                margin-top: 15px;
                padding: 15px;
                background-color: #fff3cd;
                border: 1px solid #ffeeba;
                border-radius: 6px;
                color: #856404;
                font-weight: bold;
                font-size: 15px;'>
                <strong>IMPORTANT:</strong> To cancel or modify this class, please contact the {$config['nombre_escuela']} via email at
                <a href='mailto:{$config['email_contacto']}' style='color: #0d6efd;'>{$config['email_contacto']}</a> or WhatsApp at
                <a href='tel:{$config['telefono']}' style='color: #0d6efd;'>{$config['telefono']}</a>.
            </p>";
        }

        $mensajeHTML .= "<p style='margin-top:20px; color:#000;'>$mensajeIntro</p>";
        $mensajeHTML .= "<p style='color:#000;'><strong>" . ($esProfesor ? "Participant" : "Instructor") . ":</strong> " . ($esProfesor ? $nombre_alumno : $nombre_profesor) . "</p>";
        $mensajeHTML .= "<p style='color:#000;'><strong>" . ($esProfesor ? "Instructor" : "Participant") . ":</strong> " . ($esProfesor ? $nombre_profesor : $nombre_alumno) . "</p>";
        $mensajeHTML .= "<p style='color:#000;'><strong>Date:</strong> " . date('d-m-Y', strtotime($fecha_clase)) . "</p>";
        $mensajeHTML .= "<p style='color:#000;'><strong>Time:</strong> $hora_inicio to $hora_fin</p>";
        $mensajeHTML .= "<p style='color:#000;'><strong>Location:</strong> {$config['direccion']} <a href='{$config['maps_url']}' style='color:#0d6efd;text-decoration:underline;' target='_blank'>View on map</a></p>";

        if ($esProfesor) {
            if (!empty($tarifa_hora)) {
                $mensajeHTML .= "<p style='color:#000;'><strong>Instructor Hourly Rate (€):</strong> €" . number_format($tarifa_hora, 2) . "</p>";
            }
            if (!empty($observaciones)) {
                $mensajeHTML .= "<p style='color:#000;'><strong>Additional Info:</strong> $observaciones</p>";
            }
        } else {
            if ($importe_pagado > 0) {
                $mensajeHTML .= "<p style='color:#000;'><strong>Total Amount Paid (€):</strong> €" . number_format($importe_pagado, 2) . "</p>";
            }
            if ($pago_efectivo > 0 && $pago_tarjeta > 0) {
                $mensajeHTML .= "<p style='color:#000;'><strong>Cash Payment (€):</strong> €" . number_format($pago_efectivo, 2) . "</p>";
                $mensajeHTML .= "<p style='color:#000;'><strong>Card Payment (€):</strong> €" . number_format($pago_tarjeta, 2) . "</p>";
            }
        }

        $mensajeHTML .= "<p style='margin-top:20px; color:#000;'><em>To add this class to your calendar, open the attached file named <strong>class.ics</strong>.</em></p>";
        $mensajeHTML .= "<p style='font-size:12px; color:#999;'>Ref ID: " . uniqid('ref-') . "</p>";
        $mensajeHTML .= "</div>";
    }

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
        $mail->addAddress($email, $nombre);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->ContentType = 'text/html; charset=UTF-8';

        // CAMBIO CLAVE: Asunto con el UID de la clase/booking para diferenciar
       // Antes del foreach
        $id_unico = uniqid();
        $uid = 'class-' . $id_unico . '@surfschool';

        // ...dentro del foreach (donde se arma el asunto)
        $id_corto = substr($id_unico, -6); // Solo los últimos 6 caracteres del ID único
        $mail->Subject = $tituloCorreo . ' (#' . $id_corto . ')';


        $mail->Body = $mensajeHTML;
        $mail->addStringAttachment($contenido_ics, 'class.ics', 'base64', 'text/calendar');

        // Limpiar headers personalizados (extra seguro)
        $mail->clearCustomHeaders();

        // Forzar Message-ID único
        $mail->MessageID = sprintf('<%s-%s@%s>', uniqid(), time(), 'surfschool.com');

        $mail->send();
    } catch (Exception $e) {
        // Puedes loguear el error si quieres
    }
}

unlink($archivo_ics);
ob_end_clean();