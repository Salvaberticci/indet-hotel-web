<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['checkin']) || empty($_POST['checkin']) || !isset($_POST['checkout']) || empty($_POST['checkout']) || !isset($_POST['room_type']) || empty($_POST['room_type'])) {
        die("Error: Por favor, complete todos los campos del formulario.");
    }
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $room_type = $_POST['room_type'];

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php?status=error&message=" . urlencode('Debes iniciar sesión para realizar una reserva.'));
        exit();
    }
    $user_id = $_SESSION['user_id'];

    // Get room_id based on room_type. This is a simplified approach.
    // In a real application, you would have a more robust way to get the room_id.
    $room_id_query = "SELECT id FROM rooms WHERE type = ? LIMIT 1";
    $stmt = $conn->prepare($room_id_query);
    $stmt->bind_param("s", $room_type);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $room = $result->fetch_assoc();
        $room_id = $room['id'];

        $sql = "INSERT INTO reservations (user_id, room_id, checkin_date, checkout_date, status) VALUES (?, ?, ?, ?, 'pending')";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $user_id, $room_id, $checkin, $checkout);

        if ($stmt->execute()) {
            header("Location: ../index.php?status=success&message=" . urlencode('Reserva realizada con éxito.'));
        } else {
            header("Location: ../index.php?status=error&message=" . urlencode('Error al realizar la reserva.'));
        }
    } else {
        // If room type does not exist, we need to add it first.
        // For now, let's add the room types to the database.
        // This is a temporary solution for demonstration purposes.
        $add_room_sql = "INSERT INTO rooms (type, capacity, description, price, photos) VALUES (?, ?, ?, ?, ?)";
        $stmt_add = $conn->prepare($add_room_sql);
        
        $rooms_to_add = [
            ['individual', 1, 'Habitación individual con cama sencilla.', 50.00, ''],
            ['doble', 2, 'Habitación doble con dos camas individuales.', 80.00, ''],
            ['suite', 4, 'Suite de lujo con cama king size y sala de estar.', 150.00, '']
        ];

        foreach ($rooms_to_add as $room_data) {
            $stmt_add->bind_param("sisds", $room_data[0], $room_data[1], $room_data[2], $room_data[3], $room_data[4]);
            $stmt_add->execute();
        }
        
        // Retry getting the room_id
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $room = $result->fetch_assoc();
            $room_id = $room['id'];

            $sql = "INSERT INTO reservations (user_id, room_id, checkin_date, checkout_date, status) VALUES (?, ?, ?, ?, 'pending')";

            $stmt_insert = $conn->prepare($sql);
            $stmt_insert->bind_param("iiss", $user_id, $room_id, $checkin, $checkout);

            if ($stmt_insert->execute()) {
                header("Location: ../index.php?status=success&message=" . urlencode('Reserva realizada con éxito.'));
            } else {
                header("Location: ../index.php?status=error&message=" . urlencode('Error al realizar la reserva.'));
            }
        } else {
            header("Location: ../index.php?status=error&message=" . urlencode('No se pudo encontrar el tipo de habitación.'));
        }
    }

    $stmt->close();
    $conn->close();
}
?>
