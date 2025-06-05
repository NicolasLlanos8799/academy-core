<?php
// ===========================================================
// Archivo: marcar_completada.php (versión unificada)
// Funcionalidad: Cambia el estado de una clase a "completada"
// Verifica si el usuario es admin o profesor y controla permisos
// ===========================================================

session_start();
header('Content-Type: application/json');
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id = $_POST['id'] ?? '';
$usuarioId = $_SESSION['user_id'] ?? null;
$rol = $_SESSION['rol'] ?? null;

if (empty($id) || !$usuarioId || !$rol) {
    echo json_encode(['success' => false, 'message' => 'Datos faltantes o no autorizado']);
    exit;
}

// Verificar clase
if ($rol === 'profesor') {
    // Solo puede marcar como completada si la clase le pertenece
    $stmt = $pdo->prepare("SELECT estado FROM clases WHERE id = ? AND profesor_id = ?");
    $stmt->execute([$id, $usuarioId]);
} else {
    // Admin puede marcar cualquier clase
    $stmt = $pdo->prepare("SELECT estado FROM clases WHERE id = ?");
    $stmt->execute([$id]);
}

$clase = $stmt->fetch();

if (!$clase) {
    echo json_encode(['success' => false, 'message' => 'Clase no encontrada o no autorizada']);
    exit;
}

if ($clase['estado'] === 'completada') {
    echo json_encode(['success' => false, 'message' => 'La clase ya está completada']);
    exit;
}

// Actualizar estado
$stmt = $pdo->prepare("UPDATE clases SET estado = 'completada' WHERE id = ?");
$stmt->execute([$id]);

echo json_encode(['success' => true]);
