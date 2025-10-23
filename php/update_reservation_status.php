<?php
session_start();
include 'db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $reservation_id = intval($_GET['id']);
    $new_status = $_GET['status'];

    // Validate status
    $allowed_statuses = ['confirmed', 'cancelled'];
    if (!in_array($new_status, $allowed_statuses)) {
        // Invalid status, redirect back
        header("Location: ../admin_reservations.php");
        exit();
    }

    // Prepare and execute the update statement
    $stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $reservation_id);

    if ($stmt->execute()) {
        // Success
        $_SESSION['flash_message'] = [
            'status' => 'success',
            'text' => 'El estado de la reserva ha sido actualizado.'
        ];
    } else {
        // Error
        $_SESSION['flash_message'] = [
            'status' => 'error',
            'text' => 'Error al actualizar el estado de la reserva.'
        ];
    }

    $stmt->close();
    $conn->close();

    header("Location: ../admin_reservations.php");
    exit();

} else {
    // Redirect if parameters are not set
    header("Location: ../admin.php");
    exit();
}
?>
