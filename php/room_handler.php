<?php
session_start();
include 'db.php';

// Admin access check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Add Room
if (isset($_POST['add_room'])) {
    $type = $_POST['type'];
    $capacity = $_POST['capacity'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    // For simplicity, photos will be handled as a JSON string of filenames.
    // A real implementation would involve file uploads.
    $photos = json_encode(['default_room.jpg']);

    $sql = "INSERT INTO rooms (type, capacity, description, price, photos) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisds", $type, $capacity, $description, $price, $photos);
    
    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Habitación agregada exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al agregar la habitación.'];
    }
    header("Location: ../admin.php");
    exit();
}

// Update Room
if (isset($_POST['update_room'])) {
    $id = $_POST['id'];
    $type = $_POST['type'];
    $capacity = $_POST['capacity'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    $sql = "UPDATE rooms SET type = ?, capacity = ?, description = ?, price = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisdi", $type, $capacity, $description, $price, $id);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Habitación actualizada exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al actualizar la habitación.'];
    }
    header("Location: ../admin.php");
    exit();
}

// Delete Room
if (isset($_GET['delete_room'])) {
    $id = $_GET['delete_room'];

    // Before deleting, check for related reservations to avoid foreign key constraint errors
    $check_sql = "SELECT id FROM reservations WHERE room_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'No se puede eliminar la habitación porque tiene reservas asociadas.'];
    } else {
        $sql = "DELETE FROM rooms WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Habitación eliminada exitosamente.'];
        } else {
            $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al eliminar la habitación.'];
        }
    }
    header("Location: ../admin.php");
    exit();
}
?>
