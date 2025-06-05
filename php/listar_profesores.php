<?php
// ===========================================================
// File: listar_profesores.php
// Purpose: Returns a JSON array of instructors.
// ===========================================================

require 'db.php';
header('Content-Type: application/json');

try {
    // Fetch instructors (without tarifa_hora)
    $stmt = $pdo->query("SELECT id, nombre, email, telefono FROM usuarios WHERE rol = 'profesor'");
    $profesores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($profesores); // ✅ Returns only the array

} catch (Exception $e) {
    // In case of error, return an object (not array) with message
    echo json_encode([
        "success" => false,
        "message" => "Error retrieving the instructor list"
    ]);
}
?>
