<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id            = $_POST['id'] ?? '';
    $profesor_id   = $_POST['profesor_id'] ?? '';
    $fecha         = $_POST['fecha'] ?? '';
    $hora_inicio   = $_POST['hora_inicio'] ?? '';
    $hora_fin      = $_POST['hora_fin'] ?? '';
    $alumno        = $_POST['alumno'] ?? '';
    $email         = $_POST['email'] ?? '';
    $telefono      = $_POST['telefono'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';
    $tarifa_hora   = isset($_POST['tarifa_hora']) ? floatval(str_replace(',', '.', $_POST['tarifa_hora'])) : null;
    $pago_efectivo = isset($_POST['pago_efectivo']) ? floatval(str_replace(',', '.', $_POST['pago_efectivo'])) : 0;
    $pago_tarjeta  = isset($_POST['pago_tarjeta']) ? floatval(str_replace(',', '.', $_POST['pago_tarjeta'])) : 0;
    $importe_pagado = $pago_efectivo + $pago_tarjeta;

    if (empty($id) || empty($profesor_id) || empty($fecha) || empty($hora_inicio) || empty($hora_fin) || empty($alumno)) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be completed']);
        exit;
    }

    // Retrieve original data
    $stmt = $pdo->prepare("SELECT * FROM clases WHERE id = ?");
    $stmt->execute([$id]);
    $clase_original = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$clase_original) {
        echo json_encode(['success' => false, 'message' => 'Class not found']);
        exit;
    }

    $profesor_anterior_id = $clase_original['profesor_id'];
    $cambios_alumno = (
        $profesor_id != $clase_original['profesor_id'] ||
        $fecha != $clase_original['fecha'] ||
        $hora_inicio != $clase_original['hora_inicio'] ||
        $hora_fin != $clase_original['hora_fin']
    );

    $cambios_profesor_mismo = (
        $fecha != $clase_original['fecha'] ||
        $hora_inicio != $clase_original['hora_inicio'] ||
        $hora_fin != $clase_original['hora_fin'] ||
        $tarifa_hora != $clase_original['tarifa_hora'] ||
        $observaciones != $clase_original['observaciones']
    );

    // Update the class
    try {
        $stmt = $pdo->prepare("
            UPDATE clases SET 
                profesor_id = ?, 
                tarifa_hora = ?, 
                fecha = ?, 
                hora_inicio = ?, 
                hora_fin = ?, 
                alumno_nombre = ?, 
                email = ?, 
                telefono = ?, 
                observaciones = ?, 
                pago_efectivo = ?, 
                pago_tarjeta = ?, 
                importe_pagado = ?
            WHERE id = ?
        ");
        $stmt->execute([
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
            $importe_pagado,
            $id
        ]);

        if ($stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'No changes were made.']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }

    // === Corrección: SOLO el alumno recibe 'editar' (nunca 'eliminar' ni 'crear') ===

    // 💬 Email to student if relevant changes occurred
    if ($cambios_alumno) {
        $GLOBALS['datos_correo'] = [
            'nombre_profesor' => obtenerNombreProfesor($pdo, $profesor_id),
            'correo_profesor' => '',
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
            'tipo'            => 'editar'
        ];
        include __DIR__ . '/enviar_correo_clase.php';
    }

    // 💬 If the instructor changed
    if ($profesor_id != $profesor_anterior_id) {
        // Notify the previous one (cancelled)
        $anterior = obtenerDatosProfesor($pdo, $profesor_anterior_id);
        if ($anterior) {
            $GLOBALS['datos_correo'] = [
                'nombre_profesor' => $anterior['nombre'],
                'correo_profesor' => $anterior['email'],
                'nombre_alumno'   => $alumno,
                'correo_alumno'   => '', // <-- El alumno NO recibe este email
                'fecha'           => $clase_original['fecha'],
                'hora_inicio'     => $clase_original['hora_inicio'],
                'hora_fin'        => $clase_original['hora_fin'],
                'tipo'            => 'eliminar'
            ];
            include __DIR__ . '/enviar_correo_clase.php';
        }

        // Notify the new one (created)
        $nuevo = obtenerDatosProfesor($pdo, $profesor_id);
        $stmt_clase = $pdo->prepare("SELECT * FROM clases WHERE id = ?");
        $stmt_clase->execute([$id]);
        $clase_actualizada = $stmt_clase->fetch(PDO::FETCH_ASSOC);

        if ($nuevo && $clase_actualizada) {
            $GLOBALS['datos_correo'] = [
                'nombre_profesor' => $nuevo['nombre'],
                'correo_profesor' => $nuevo['email'],
                'nombre_alumno'   => $clase_actualizada['alumno_nombre'],
                'correo_alumno'   => '', // <-- El alumno NO recibe este email
                'fecha'           => $clase_actualizada['fecha'],
                'hora_inicio'     => $clase_actualizada['hora_inicio'],
                'hora_fin'        => $clase_actualizada['hora_fin'],
                'tarifa_hora'     => $clase_actualizada['tarifa_hora'],
                'observaciones'   => $clase_actualizada['observaciones'],
                'pago_efectivo'   => $clase_actualizada['pago_efectivo'],
                'pago_tarjeta'    => $clase_actualizada['pago_tarjeta'],
                'importe_pagado'  => $clase_actualizada['importe_pagado'],
                'tipo'            => 'crear'
            ];
            include __DIR__ . '/enviar_correo_clase.php';
        }

    } else if ($cambios_profesor_mismo) {
        // Same instructor, but relevant changes
        $profesor = obtenerDatosProfesor($pdo, $profesor_id);
        if ($profesor) {
            $GLOBALS['datos_correo'] = [
                'nombre_profesor' => $profesor['nombre'],
                'correo_profesor' => $profesor['email'],
                'nombre_alumno'   => $alumno,
                'correo_alumno'   => '',
                'fecha'           => $fecha,
                'hora_inicio'     => $hora_inicio,
                'hora_fin'        => $hora_fin,
                'tarifa_hora'     => $tarifa_hora,
                'observaciones'   => $observaciones,
                'pago_efectivo'   => $pago_efectivo,
                'pago_tarjeta'    => $pago_tarjeta,
                'importe_pagado'  => $importe_pagado,
                'tipo'            => 'editar'
            ];
            include __DIR__ . '/enviar_correo_clase.php';
        }
    }

    echo json_encode(['success' => true]);
}

function obtenerDatosProfesor($pdo, $id) {
    $stmt = $pdo->prepare("SELECT nombre, email FROM usuarios WHERE id = ? AND rol = 'profesor'");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function obtenerNombreProfesor($pdo, $id) {
    $stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ? AND rol = 'profesor'");
    $stmt->execute([$id]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    return $res['nombre'] ?? '';
}
?>
