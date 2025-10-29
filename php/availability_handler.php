<?php
include 'db.php';

header('Content-Type: application/json; charset=utf-8');

$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';
$floor_id = $_GET['floor_id'] ?? '';
$capacity = $_GET['capacity'] ?? '';
$total_people = $_GET['total_people'] ?? 0;

if (empty($checkin) || empty($checkout) || empty($floor_id)) {
    echo json_encode([]);
    exit();
}

// For groups > 16, capacity parameter is optional
if ($total_people <= 16 && empty($capacity)) {
    echo json_encode([]);
    exit();
}

// Validate that total_people can fit in the selected capacity, but allow all rooms if total_people > 16
if ($total_people <= 16 && $total_people > $capacity) {
    echo json_encode([]);
    exit();
}

// Build the SQL query based on total_people
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
        AND r.status = 'enabled'";

$params = [$checkin, $checkin, $checkout, $checkout, $checkin, $checkout, $floor_id];
$types = "ssssssi";

// Add capacity filter only for groups <= 16
if ($total_people <= 16) {
    $sql .= " AND r.capacity = ?";
    $params[] = $capacity;
    $types .= "i";
}


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
