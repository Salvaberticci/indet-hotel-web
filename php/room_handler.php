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

    $photos = [];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../images/';
        $file_name = basename($_FILES['image']['name']);
        $target_file = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $photos[] = $file_name;
        } else {
            $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al subir la imagen.'];
            header("Location: ../admin.php#rooms-section");
            exit();
        }
    } else {
        $photos[] = 'default_room.jpg';
    }
    $photos_json = json_encode($photos);

    $sql = "INSERT INTO rooms (type, capacity, description, price, photos) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisds", $type, $capacity, $description, $price, $photos_json);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Habitación agregada exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al agregar la habitación: ' . $stmt->error];
    }
    header("Location: ../admin.php#rooms-section");
    exit();
}

// Update Room
if (isset($_POST['update_room'])) {
    $id = $_POST['id'];
    $type = $_POST['type'];
    $capacity = $_POST['capacity'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    // Fetch current photos
    $current_sql = "SELECT photos FROM rooms WHERE id = ?";
    $current_stmt = $conn->prepare($current_sql);
    $current_stmt->bind_param("i", $id);
    $current_stmt->execute();
    $current_result = $current_stmt->get_result();
    $current_room = $current_result->fetch_assoc();
    $photos = json_decode($current_room['photos'], true) ?? ['default_room.jpg'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../images/';
        $file_name = basename($_FILES['image']['name']);
        $target_file = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $photos = [$file_name]; // Replace with new image
        } else {
            $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al subir la imagen.'];
            header("Location: ../admin.php#rooms-section");
            exit();
        }
    }
    $photos_json = json_encode($photos);

    $sql = "UPDATE rooms SET type = ?, capacity = ?, description = ?, price = ?, photos = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisdsi", $type, $capacity, $description, $price, $photos_json, $id);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Habitación actualizada exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al actualizar la habitación: ' . $stmt->error];
    }
    header("Location: ../admin.php#rooms-section");
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
            $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al eliminar la habitación: ' . $stmt->error];
        }
    }
    header("Location: ../admin.php#rooms-section");
    exit();
}
?>
