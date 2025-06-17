<?php
session_start(); // 🔑 Siempre primero

// Control de inactividad
$tiempoInactividad = 129600; // 36 horas

if (isset($_SESSION['ULTIMA_ACTIVIDAD']) && (time() - $_SESSION['ULTIMA_ACTIVIDAD']) > $tiempoInactividad) {
    session_unset();     // Limpia variables de sesión
    session_destroy();   // Elimina la sesión
    header("Location: login.php?expirada=1");
    exit;
}

$_SESSION['ULTIMA_ACTIVIDAD'] = time(); // Renueva el tiempo de actividad

// 🔒 Bloquear caché del navegador
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// 🔐 Validar sesión y rol
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>
