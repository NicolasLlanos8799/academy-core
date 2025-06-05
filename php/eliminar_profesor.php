<?php
// ===========================================================
// File: eliminar_profesor.php
// Purpose: Allows deleting an instructor from the database
// only if they have NO FUTURE OR ONGOING classes. The request must be POST.
// ===========================================================

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id = $_POST['id'] ?? '';

    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Instructor ID not provided']);
        exit;
    }

    try {
        // Check if the instructor has any future or ongoing classes
        $stmtCheck = $pdo->prepare(
            "SELECT COUNT(*) FROM clases WHERE profesor_id = ? AND CONCAT(fecha, ' ', hora_fin) >= NOW()"
        );
        $stmtCheck->execute([$id]);
        $count = $stmtCheck->fetchColumn();

        if ($count > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete an instructor with upcoming or ongoing classes.']);
            exit;
        }

        // Proceed to delete if only has past classes or none at all
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ? AND rol = 'profesor'");
        if ($stmt->execute([$id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting instructor.']);
        }

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
