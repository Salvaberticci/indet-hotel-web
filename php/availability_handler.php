<?php
include 'db.php';

header('Content-Type: text/html; charset=utf-8');

if (isset($_GET['checkin']) && isset($_GET['checkout'])) {
    $checkin_date = $_GET['checkin'];
    $checkout_date = $_GET['checkout'];
    $room_type = isset($_GET['room_type']) ? $_GET['room_type'] : null;

    // Find rooms that are NOT booked during the selected dates
    $sql = "SELECT r.id, r.type, r.capacity, r.description, r.price, r.photos
            FROM rooms r
            WHERE r.id NOT IN (
                SELECT res.room_id
                FROM reservations res
                WHERE (res.checkin_date < ? AND res.checkout_date > ?)
                OR (res.checkin_date >= ? AND res.checkin_date < ?)
            )";
    $params = [$checkout_date, $checkin_date, $checkin_date, $checkout_date];
    $types = "ssss";

    if ($room_type) {
        $sql .= " AND r.type = ?";
        $params[] = $room_type;
        $types .= "s";
    }

    $sql .= " ORDER BY r.type";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<h3 class="text-2xl font-bold text-center mb-6">Habitaciones Disponibles</h3>';
        echo '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 px-6">';
        while ($room = $result->fetch_assoc()) {
            $photos = json_decode($room['photos'], true);
            $image = (!empty($photos) && isset($photos[0])) ? "images/{$photos[0]}" : 'images/hero-bg.jpg';
            
            echo '<div class="room-card bg-gray-50 rounded-lg overflow-hidden shadow-lg">';
            echo '<img src="' . htmlspecialchars($image) . '" alt="' . htmlspecialchars($room['type']) . '" class="w-full h-48 object-cover">';
            echo '<div class="p-4">';
            echo '<h4 class="text-xl font-bold capitalize">' . htmlspecialchars($room['type']) . '</h4>';
            echo '<p class="text-gray-600 text-sm mb-2">' . htmlspecialchars($room['description']) . '</p>';
            echo '<ul class="text-sm space-y-1">';
            echo '<li><i class="fas fa-users mr-2 text-green-600"></i>Capacidad: ' . htmlspecialchars($room['capacity']) . '</li>';
            echo '<li><i class="fas fa-dollar-sign mr-2 text-green-600"></i>Precio: $' . htmlspecialchars(number_format($room['price'], 2)) . ' / noche</li>';
            echo '</ul>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<p class="text-center text-red-500">No hay habitaciones disponibles para las fechas seleccionadas.</p>';
    }

    $stmt->close();
    $conn->close();
}
?>
