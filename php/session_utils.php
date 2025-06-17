<?php
function require_role(string $role): void {
    session_start();
    // Allow up to 36 hours of inactivity
    $tiempoInactividad = 129600;

    if (isset($_SESSION['ULTIMA_ACTIVIDAD']) && (time() - $_SESSION['ULTIMA_ACTIVIDAD']) > $tiempoInactividad) {
        session_unset();
        session_destroy();
        header('Location: login.php?expirada=1');
        exit;
    }

    $_SESSION['ULTIMA_ACTIVIDAD'] = time();

    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');

    if (!isset($_SESSION['user_id']) || ($_SESSION['rol'] ?? '') !== $role) {
        header('Location: login.php');
        exit;
    }
}
