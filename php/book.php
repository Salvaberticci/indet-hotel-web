<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['checkin']) || empty($_POST['checkin']) || !isset($_POST['checkout']) || empty($_POST['checkout']) || !isset($_POST['room_type']) || empty($_POST['room_type']) || !isset($_POST['guest_name']) || empty($_POST['guest_name']) || !isset($_POST['guest_email']) || empty($_POST['guest_email'])) {
        die("Error: Por favor, complete todos los campos del formulario.");
    }
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $room_type = $_POST['room_type'];
    $guest_name = $_POST['guest_name'];
    $guest_email = $_POST['guest_email'];

    if (!isset($_SESSION['user_id'])) {
        $_SESSION['flash_message'] = [
            'status' => 'error',
            'text' => 'Debes iniciar sesión para realizar una reserva.'
        ];
        header("Location: ../login.php");
        exit();
    }
    $user_id = $_SESSION['user_id'];

    // Get room_id based on room_type.
    $room_id_query = "SELECT id FROM rooms WHERE type = ? LIMIT 1";
    $stmt = $conn->prepare($room_id_query);
    $stmt->bind_param("s", $room_type);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $room = $result->fetch_assoc();
        $room_id = $room['id'];

        $sql = "INSERT INTO reservations (user_id, room_id, checkin_date, checkout_date, guest_name, guest_email, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissss", $user_id, $room_id, $checkin, $checkout, $guest_name, $guest_email);

        if ($stmt->execute()) {
            $reservation_id = $stmt->insert_id;
            // Store reservation details in session to display on confirmation page
            $_SESSION['last_reservation'] = [
                'id' => $reservation_id,
                'room_type' => $room_type,
                'checkin' => $checkin,
                'checkout' => $checkout,
                'guest_name' => $guest_name
            ];
            header("Location: ../confirmation.php");
            exit();
        } else {
            $_SESSION['flash_message'] = [
                'status' => 'error',
                'text' => 'Error al realizar la reserva.'
            ];
        }
    } else {
        $_SESSION['flash_message'] = [
            'status' => 'error',
            'text' => 'No se pudo encontrar el tipo de habitación.'
        ];
    }
    header("Location: ../reservar.php");
    exit();

    $stmt->close();
    $conn->close();
}
?>
