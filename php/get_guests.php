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
    $reservation_id = (int)$_GET['reservation_id'];

    $sql = "SELECT guest_name, guest_lastname, guest_phone FROM reservation_guests WHERE reservation_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $reservation_id);
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