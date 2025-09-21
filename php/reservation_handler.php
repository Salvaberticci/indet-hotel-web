<?php
include 'db.php';
session_start();

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_reservation'])) {
        $user_id = $_POST['user_id'];
        $room_id = $_POST['room_id'];
        $checkin_date = $_POST['checkin_date'];
        $checkout_date = $_POST['checkout_date'];

        $sql = "INSERT INTO reservations (user_id, room_id, checkin_date, checkout_date, status) VALUES (?, ?, ?, ?, 'confirmed')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $user_id, $room_id, $checkin_date, $checkout_date);
        if ($stmt->execute()) {
            $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Reserva agregada exitosamente.'];
        } else {
            $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al agregar la reserva.'];
        }
        $stmt->close();
        header("Location: ../admin.php#reservations-section");
        exit();
    }

    if (isset($_POST['update_reservation'])) {
        $id = $_POST['id'];
        $user_id = $_POST['user_id'];
        $room_id = $_POST['room_id'];
        $checkin_date = $_POST['checkin_date'];
        $checkout_date = $_POST['checkout_date'];
        $status = $_POST['status'];

        $sql = "UPDATE reservations SET user_id=?, room_id=?, checkin_date=?, checkout_date=?, status=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssi", $user_id, $room_id, $checkin_date, $checkout_date, $status, $id);
        if ($stmt->execute()) {
            $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Reserva actualizada exitosamente.'];
        } else {
            $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al actualizar la reserva.'];
        }
        $stmt->close();
        header("Location: ../admin.php#reservations-section");
        exit();
    }
}

if (isset($_GET['delete_reservation'])) {
    $id = $_GET['delete_reservation'];
    $sql = "DELETE FROM reservations WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Reserva eliminada exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al eliminar la reserva.'];
    }
    $stmt->close();
    header("Location: ../admin.php#reservations-section");
    exit();
}

$conn->close();
?>