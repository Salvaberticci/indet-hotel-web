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
    $room_id = $_POST['room_id'];
    $type = $_POST['type'];
    $capacity = $_POST['capacity'];
    $floor_id = $_POST['floor_id'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    $photos = [];
    $videos = [];

    // Handle multiple images
    if (isset($_FILES['images'])) {
        $upload_dir = '../images/';
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] == UPLOAD_ERR_OK) {
                $file_name = basename($_FILES['images']['name'][$key]);
                $target_file = $upload_dir . $file_name;
                if (move_uploaded_file($tmp_name, $target_file)) {
                    $photos[] = $file_name;
                }
            }
        }
    }

    // Handle multiple videos
    if (isset($_FILES['videos'])) {
        $upload_dir = '../images/'; // Using same directory for simplicity
        foreach ($_FILES['videos']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['videos']['error'][$key] == UPLOAD_ERR_OK) {
                $file_name = basename($_FILES['videos']['name'][$key]);
                $target_file = $upload_dir . $file_name;
                if (move_uploaded_file($tmp_name, $target_file)) {
                    $videos[] = $file_name;
                }
            }
        }
    }

    if (empty($photos)) {
        $photos[] = 'default_room.jpg';
    }

    $photos_json = json_encode($photos);
    $videos_json = json_encode($videos);

    $sql = "INSERT INTO rooms (id, type, capacity, floor_id, description, photos, videos, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiissss", $room_id, $type, $capacity, $floor_id, $description, $photos_json, $videos_json, $status);

    if ($stmt->execute()) {
        // Create default inventory items for the new room based on capacity
        $default_items = [];
        if ($capacity >= 1) {
            $default_items[] = ['item_name' => 'Almohadas', 'quantity' => $capacity, 'description' => 'Almohadas para habitación'];
            $default_items[] = ['item_name' => 'Sábanas', 'quantity' => $capacity, 'description' => 'Sábanas para literas'];
            $default_items[] = ['item_name' => 'Toallas', 'quantity' => $capacity * 2, 'description' => 'Toallas de baño'];
        }

        // Insert default inventory items
        foreach ($default_items as $item) {
            $inventory_sql = "INSERT INTO room_inventory (room_id, item_name, quantity, description) VALUES (?, ?, ?, ?)";
            $inventory_stmt = $conn->prepare($inventory_sql);
            $inventory_stmt->bind_param("ssis", $room_id, $item['item_name'], $item['quantity'], $item['description']);
            $inventory_stmt->execute();
        }

        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Habitación agregada exitosamente con inventario básico.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al agregar la habitación: ' . $stmt->error];
    }
    header("Location: ../admin_rooms.php");
    exit();
}

// Update Room
if (isset($_POST['update_room'])) {
    $id = $_POST['id'];
    $type = $_POST['type'];
    $capacity = $_POST['capacity'];
    $floor_id = $_POST['floor_id'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    // Fetch current photos and videos
    $current_sql = "SELECT photos FROM rooms WHERE id = ?";
    $current_stmt = $conn->prepare($current_sql);
    $current_stmt->bind_param("s", $id);
    $current_stmt->execute();
    $current_result = $current_stmt->get_result();
    $current_room = $current_result->fetch_assoc();
    $photos = json_decode($current_room['photos'], true) ?? ['default_room.jpg'];
    $videos = [];

    // Handle multiple images (append to existing)
    if (isset($_FILES['images'])) {
        $upload_dir = '../images/';
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] == UPLOAD_ERR_OK) {
                $file_name = basename($_FILES['images']['name'][$key]);
                $target_file = $upload_dir . $file_name;
                if (move_uploaded_file($tmp_name, $target_file)) {
                    $photos[] = $file_name;
                }
            }
        }
    }

    // Handle multiple videos (append to existing)
    if (isset($_FILES['videos'])) {
        $upload_dir = '../images/';
        foreach ($_FILES['videos']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['videos']['error'][$key] == UPLOAD_ERR_OK) {
                $file_name = basename($_FILES['videos']['name'][$key]);
                $target_file = $upload_dir . $file_name;
                if (move_uploaded_file($tmp_name, $target_file)) {
                    $videos[] = $file_name;
                }
            }
        }
    }

    $photos_json = json_encode($photos);
    $videos_json = json_encode($videos);

    $sql = "UPDATE rooms SET type = ?, capacity = ?, floor_id = ?, description = ?, photos = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siissss", $type, $capacity, $floor_id, $description, $photos_json, $status, $id);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Habitación actualizada exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al actualizar la habitación: ' . $stmt->error];
    }
    header("Location: ../admin_rooms.php");
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
    header("Location: ../admin_rooms.php");
    exit();
}
?>
