<?php
header('Content-Type: application/json');
session_start();
include __DIR__ . '/db.php';

if (isset($_GET['reservation_id'])) {
    $reservation_id = (int) $_GET['reservation_id'];

    // Get the base reservation details to find the group
    $base_sql = "SELECT user_id, checkin_date, checkout_date FROM reservations WHERE id = ?";
    $stmt = $conn->prepare($base_sql);
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $base_result = $stmt->get_result();
    $base_data = $base_result->fetch_assoc();

    if ($base_data) {
        // Find all reservations in the same group
        $group_sql = "SELECT r.id, r.room_id, rooms.type, f.name as floor_name
                      FROM reservations r 
                      JOIN rooms ON r.room_id = rooms.id 
                      LEFT JOIN floors f ON rooms.floor_id = f.id
                      WHERE r.user_id = ? 
                      AND r.checkin_date = ? 
                      AND r.checkout_date = ?";
        $stmt = $conn->prepare($group_sql);
        $stmt->bind_param("iss", $base_data['user_id'], $base_data['checkin_date'], $base_data['checkout_date']);
        $stmt->execute();
        $group_result = $stmt->get_result();

        $group = [];
        while ($row = $group_result->fetch_assoc()) {
            $group[] = $row;
        }

        echo json_encode(['group' => $group]);
    } else {
        echo json_encode(['error' => 'Reserva no encontrada']);
    }
} else {
    echo json_encode(['error' => 'ID de reserva no proporcionado']);
}

$conn->close();
?>