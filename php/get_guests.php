<?php
header('Content-Type: application/json');
session_start();
include 'db.php';

// For debugging, temporarily allow access without session check
// TODO: Remove this in production
/*
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Sesión no iniciada']);
    exit();
}

if ($_SESSION['user_role'] != 'admin') {
    echo json_encode(['error' => 'Acceso denegado. Solo administradores pueden ver huéspedes.']);
    exit();
}
*/

if (isset($_GET['reservation_id'])) {
    $reservation_id = (int) $_GET['reservation_id'];

    $sql = "SELECT rg.id, rg.guest_name, rg.guest_lastname, rg.guest_phone, r.room_id 
            FROM reservation_guests rg
            JOIN reservations r ON rg.reservation_id = r.id
            WHERE r.user_id = (SELECT user_id FROM reservations WHERE id = ?)
            AND r.checkin_date = (SELECT checkin_date FROM reservations WHERE id = ?)
            AND r.checkout_date = (SELECT checkout_date FROM reservations WHERE id = ?)
            ORDER BY r.room_id ASC, rg.id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $reservation_id, $reservation_id, $reservation_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $guests = [];
    while ($row = $result->fetch_assoc()) {
        $guests[] = $row;
    }

    echo json_encode($guests);
} else {
    echo json_encode(['error' => 'ID de reserva no proporcionado']);
}

$conn->close();
?>