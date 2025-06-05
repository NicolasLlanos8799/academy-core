<?php
// ===========================================================
// File: reset_password.php
// Functionality: Allows a user to reset their password.
// ===========================================================

require 'db.php';
header('Content-Type: application/json');

// Show errors during development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $nueva_contrasena = trim($_POST['nueva_contrasena'] ?? '');

    if (!$email || !$nueva_contrasena) {
        echo json_encode(['success' => false, 'message' => 'Missing data.']);
        exit;
    }

    // Check if the user exists
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'This email is not registered.']);
        exit;
    }

    // Hash the new password
    $passwordHash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);

    // Update the password
    $update = $pdo->prepare("UPDATE usuarios SET password = ? WHERE email = ?");
    $success = $update->execute([$passwordHash, $email]);
    $rowCount = $update->rowCount();

    if ($success && $rowCount > 0) {
        echo json_encode(['success' => true, 'message' => 'Password successfully updated.']);
    } else if ($success && $rowCount === 0) {
        echo json_encode(['success' => false, 'message' => 'No changes made. Please verify the email.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating the password.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
}
