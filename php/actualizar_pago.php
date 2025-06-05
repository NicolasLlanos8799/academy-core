<?php
// ===========================================================
// File: actualizar_pago.php
// Functionality: Updates the status of a payment in the database
// and sends an email notification when the payment is marked as approved.
// ===========================================================

require 'db.php'; // Import database connection

// Ensure the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the data sent via POST
    $id_pago = $_POST['id_pago'] ?? '';
    $estado = $_POST['estado'] ?? '';

    // Validation: Check that both fields are present
    if (empty($id_pago) || empty($estado)) {
        echo json_encode(['success' => false, 'message' => 'Incomplete data']);
        exit;
    }

    // Prepare the SQL query to update the payment status
    $stmt = $pdo->prepare("UPDATE pagos SET estado = ? WHERE id = ?");
    
    if ($stmt->execute([$estado, $id_pago])) {
        // If the payment is marked as "paid", send an email to the teacher
        if ($estado === 'pagado') {
            // Get the teacher's email associated with the payment
            $stmt = $pdo->prepare("SELECT u.email FROM pagos p 
                                   JOIN usuarios u ON p.profesor_id = u.id 
                                   WHERE p.id = ?");
            $stmt->execute([$id_pago]);
            $profesor = $stmt->fetch();

            // Send email notification
            $to = $profesor['email'];
            $subject = "Payment Received";
            $message = "Hello, your payment has been processed and marked as 'Paid'.";
            $headers = "From: admin@surfschool.com";

            // Send the email
            mail($to, $subject, $message, $headers);
        }

        // JSON response indicating success
        echo json_encode(['success' => true]);
    } else {
        // If an error occurs, return an error message
        echo json_encode(['success' => false, 'message' => 'Error updating payment']);
    }
}
?>
