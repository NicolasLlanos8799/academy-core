<?php
session_start();
header('Content-Type: application/json');

// ⏱ Tiempo de expiración en segundos (36 horas)
$tiempoInactividad = 129600;

// 🚫 Si no hay sesión iniciada, marcar como inactiva
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['activa' => false, 'razon' => 'sin_user_id']);
    exit;
}

// 🚫 Si no hay registro de actividad o pasó demasiado tiempo, destruir sesión
if (!isset($_SESSION['ULTIMA_ACTIVIDAD']) || (time() - $_SESSION['ULTIMA_ACTIVIDAD']) > $tiempoInactividad) {
    session_unset();
    session_destroy();
    echo json_encode(['activa' => false, 'razon' => 'expirada']);
    exit;
}

// ✅ Sesión válida, renovar tiempo
$_SESSION['ULTIMA_ACTIVIDAD'] = time();
echo json_encode(['activa' => true]);
exit;
