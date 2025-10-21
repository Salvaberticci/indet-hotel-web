<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservation_id = (int)($_POST['reservation_id'] ?? 0);
    $user_id = $_SESSION['user_id'];

    if (!$reservation_id) {
        echo json_encode(['success' => false, 'message' => 'ID de reserva inválido.']);
        exit();
    }

    // Check if the reservation belongs to the user and is pending
    $check_sql = "SELECT id FROM reservations WHERE id = ? AND user_id = ? AND status = 'pending'";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $reservation_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Reserva no encontrada o no puede ser cancelada.']);
        exit();
    }

    // Delete the reservation
    $delete_sql = "DELETE FROM reservations WHERE id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $reservation_id, $user_id);

    if ($delete_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Reserva cancelada exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al cancelar la reserva.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}

$conn->close();
?>