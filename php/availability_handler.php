<?php
include 'db.php';

header('Content-Type: application/json; charset=utf-8');

$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';
$floor_id = $_GET['floor_id'] ?? '';
$capacity = $_GET['capacity'] ?? '';
$total_people = $_GET['total_people'] ?? 0;

if (empty($checkin) || empty($checkout) || empty($floor_id) || empty($capacity)) {
    echo json_encode([]);
    exit();
}

// Query to find available rooms
$sql = "SELECT r.id, r.type, r.capacity, r.description, r.photos, r.price, f.name as floor_name
        FROM rooms r
        JOIN floors f ON r.floor_id = f.id
        WHERE r.id NOT IN (
            SELECT room_id FROM reservations
            WHERE status IN ('confirmed', 'pending')
            AND ((checkin_date <= ? AND checkout_date > ?) OR
                 (checkin_date < ? AND checkout_date >= ?) OR
                 (checkin_date >= ? AND checkout_date <= ?))
        )
        AND r.floor_id = ?
        AND r.capacity = ?
        AND r.status = 'enabled'"; // Solo habitaciones habilitadas

$params = [$checkin, $checkin, $checkout, $checkout, $checkin, $checkout, $floor_id, $capacity];
$types = "ssssssii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$rooms = [];
while ($room = $result->fetch_assoc()) {
    $rooms[] = $room;
}

echo json_encode($rooms);

$stmt->close();
$conn->close();
?>
