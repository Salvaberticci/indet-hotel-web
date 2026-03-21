<?php
include 'php/db.php';
$reservation_id = 74; // Using an ID from the SQL dump

$base_sql = "SELECT user_id, checkin_date, checkout_date FROM reservations WHERE id = ?";
$stmt = $conn->prepare($base_sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$base_result = $stmt->get_result();
$base_data = $base_result->fetch_assoc();

print_r($base_data);

if ($base_data) {
    $group_sql = "SELECT r.id, r.room_id, rooms.type 
                  FROM reservations r 
                  JOIN rooms ON r.room_id = rooms.id 
                  WHERE r.user_id = ? 
                  AND r.checkin_date = ? 
                  AND r.checkout_date = ?";
    $stmt2 = $conn->prepare($group_sql);
    if (!$stmt2) {
        die("Prepare 2 failed: " . $conn->error);
    }
    $stmt2->bind_param("iss", $base_data['user_id'], $base_data['checkin_date'], $base_data['checkout_date']);
    $stmt2->execute();
    $group_result = $stmt2->get_result();

    $group = [];
    while ($row = $group_result->fetch_assoc()) {
        $group[] = $row;
    }
    print_r($group);
}
?>