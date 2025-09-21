<?php
session_start();
include 'db.php';

// Admin access check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Add Event
if (isset($_POST['add_event'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $image = 'default_event.jpg'; // Default image, can be expanded with file upload

    $sql = "INSERT INTO events (name, description, date, image) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $description, $date, $image);
    
    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Evento agregado exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al agregar el evento.'];
    }
    header("Location: ../admin.php");
    exit();
}

// Update Event
if (isset($_POST['update_event'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $date = $_POST['date'];

    $sql = "UPDATE events SET name = ?, description = ?, date = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $name, $description, $date, $id);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Evento actualizado exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al actualizar el evento.'];
    }
    header("Location: ../admin.php");
    exit();
}

// Delete Event
if (isset($_GET['delete_event'])) {
    $id = $_GET['delete_event'];

    $sql = "DELETE FROM events WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Evento eliminado exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al eliminar el evento.'];
    }
    header("Location: ../admin.php");
    exit();
}
?>
