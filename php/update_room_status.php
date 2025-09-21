<?php
session_start();
include 'db.php';

// Admin access check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_id = intval($_POST['room_id']);
    $new_status = $_POST['status'];
    $current_date = date('Y-m-d');

    // Validate status
    $allowed_statuses = ['available', 'occupied', 'cleaning'];
    if (!in_array($new_status, $allowed_statuses)) {
        // Invalid status, redirect back
        header("Location: ../admin.php");
        exit();
    }

    // Prepare and execute the update statement
    $stmt = $conn->prepare("UPDATE room_status SET status = ?, date = ? WHERE room_id = ?");
    $stmt->bind_param("ssi", $new_status, $current_date, $room_id);

    if ($stmt->execute()) {
        // Success
        $_SESSION['flash_message'] = [
            'status' => 'success',
            'text' => 'El estado de la habitación ha sido actualizado.'
        ];
    } else {
        // Error
        $_SESSION['flash_message'] = [
            'status' => 'error',
            'text' => 'Error al actualizar el estado de la habitación.'
        ];
    }

    $stmt->close();
    $conn->close();

    header("Location: ../admin.php");
    exit();

} else {
    // Redirect if accessed directly
    header("Location: ../admin.php");
    exit();
}
?>
