<?php
// ===========================================================
// File: editar_profesor.php
// Purpose: Allows editing and updating instructor information 
// in the database without hourly rate
// ===========================================================

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id = $_POST['id'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';

    if (empty($id) || empty($nombre) || empty($email) || empty($telefono)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE usuarios 
                               SET nombre = ?, email = ?, telefono = ? 
                               WHERE id = ?");

        if ($stmt->execute([$nombre, $email, $telefono, $id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating instructor']);
        }

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
