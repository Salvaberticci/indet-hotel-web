<?php
session_start();
include 'db.php';

// Admin access check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $reservation_id = (int)($_POST['reservation_id'] ?? 0);

    if (!$reservation_id) {
        echo json_encode(['success' => false, 'message' => 'ID de reserva inválido.']);
        exit();
    }

    if ($action === 'checkin') {
        // Confirm check-in and generate PDF receipt
        $sql = "UPDATE reservations SET status = 'confirmed' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $reservation_id);

        if ($stmt->execute()) {
            // Generate PDF receipt for check-in
            include 'generate_checkin_pdf.php';
            $pdf_url = generateCheckinPDF($reservation_id);

            echo json_encode([
                'success' => true,
                'message' => 'Check-in confirmado exitosamente.',
                'pdf_url' => $pdf_url
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al confirmar check-in.']);
        }

    } elseif ($action === 'checkout') {
        // Process check-out and send room to maintenance
        $conn->begin_transaction();

        try {
            // Update reservation status to completed
            $update_sql = "UPDATE reservations SET status = 'completed' WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $reservation_id);
            $update_stmt->execute();

            // Get room information
            $room_sql = "SELECT room_id FROM reservations WHERE id = ?";
            $room_stmt = $conn->prepare($room_sql);
            $room_stmt->bind_param("i", $reservation_id);
            $room_stmt->execute();
            $room_result = $room_stmt->get_result();
            $room_data = $room_result->fetch_assoc();

            if ($room_data) {
                $room_id = $room_data['room_id'];

                // Schedule cleaning task 30 minutes after checkout
                include 'maintenance_scheduler.php';
                scheduleCleaningAfterCheckout($reservation_id);
            }

            $conn->commit();

            // Generate PDF receipt for check-out
            include 'generate_checkout_pdf.php';
            $pdf_url = generateCheckoutPDF($reservation_id);

            echo json_encode([
                'success' => true,
                'message' => 'Check-out procesado exitosamente.',
                'pdf_url' => $pdf_url
            ]);

        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Error al procesar check-out: ' . $e->getMessage()]);
        }

    } else {
        echo json_encode(['success' => false, 'message' => 'Acción inválida.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}

$conn->close();
?>