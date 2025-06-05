<?php
// ===========================================================
// File: agregar_profesor.php
// Functionality: Adds a new instructor to the database
// ===========================================================

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre   = $_POST['nombre'] ?? '';
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $telefono = $_POST['telefono'] ?? '';

    // Verifica que todos los campos requeridos estén completos
    if (empty($nombre) || empty($email) || empty($password) || empty($telefono)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    try {
        // Encripta la contraseña de manera segura
        $passwordHashed = password_hash($password, PASSWORD_DEFAULT);

        // Inserta el nuevo profesor (rol = 'profesor')
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, telefono, rol)
                               VALUES (?, ?, ?, ?, 'profesor')");

        if ($stmt->execute([$nombre, $email, $passwordHashed, $telefono])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding instructor']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
