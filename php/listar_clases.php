<?php
// ===========================================================
// File: listar_clases.php
// Purpose: Retrieves all registered classes from the database,
// including information about the assigned instructor.
// ===========================================================

require 'db.php';

try {
    $stmt = $pdo->query("
    SELECT 
        clases.id, 
        clases.fecha, 
        clases.hora_inicio, 
        clases.hora_fin, 
        clases.alumno_nombre,
        clases.email,
        clases.telefono,
        clases.tarifa_hora,
        clases.observaciones,
        clases.importe_pagado,
        clases.pago_efectivo,
        clases.pago_tarjeta,
        clases.profesor_id,
        clases.estado,
        usuarios.nombre AS profesor_nombre 
    FROM clases 
    JOIN usuarios ON clases.profesor_id = usuarios.id
");

    $clases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($clases);

} catch (Exception $e) {
    echo json_encode([
        "success" => false, 
        "message" => "Error retrieving classes"
    ]);
}
?>
