<?php
// ===========================================================
// File: login.php
// Functionality: Handles user authentication in the system.
// ===========================================================

session_start();
require 'db.php';

// Show errors during development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Look up user in the 'usuarios' table
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {       
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['nombre'] = $user['nombre'];

        if ($user['rol'] === 'admin') {
            echo json_encode([
                'success' => true,
                'redirect' => 'admin.php'
            ]);
        } else {
            // Instructor: also return the ID
            echo json_encode([
                'success' => true,
                'redirect' => 'profesor.php',
                'id_profesor' => $user['id']
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Incorrect credentials.'
        ]);
    }
}
