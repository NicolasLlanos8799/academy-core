<?php
// ===========================================================
// File: generar_pagos.php
// Purpose: Registers payments for instructors based on 
// the hours worked in COMPLETED classes.
// ===========================================================

require 'db.php'; // Database connection

try {
    // 🔹 1. Get the total hours worked for each instructor with COMPLETED classes
    $stmt = $pdo->query("
        SELECT profesor_id, SUM(TIMESTAMPDIFF(HOUR, hora_inicio, hora_fin)) AS total_horas 
        FROM clases 
        WHERE estado = 'completada' 
        GROUP BY profesor_id
    ");

    $clases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 🔹 2. Process each instructor and calculate the payment
    foreach ($clases as $clase) {
        $profesor_id = $clase['profesor_id'];
        $total_horas = $clase['total_horas'];

        // Get the hourly rate for the instructor
        $stmt = $pdo->prepare("SELECT tarifa_hora FROM usuarios WHERE id = ?");
        $stmt->execute([$profesor_id]);
        $profesor = $stmt->fetch();

        // If no hourly rate is registered, skip
        if (!$profesor || empty($profesor['tarifa_hora'])) {
            continue;
        }

        // Calculate the total amount to pay
        $total_pagar = $total_horas * $profesor['tarifa_hora'];

        // 🔹 3. Check if a pending payment already exists for this instructor
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pagos WHERE profesor_id = ? AND estado = 'pendiente'");
        $stmt->execute([$profesor_id]);
        $existe_pago = $stmt->fetchColumn();

        if ($existe_pago > 0) {
            continue; // Skip if there’s already a pending payment
        }

        // 🔹 4. Register the payment in the database
        $stmt = $pdo->prepare("INSERT INTO pagos (profesor_id, total_horas, total, estado) VALUES (?, ?, ?, 'pendiente')");
        $stmt->execute([$profesor_id, $total_horas, $total_pagar]);
    }

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    error_log("Error generating payments: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
