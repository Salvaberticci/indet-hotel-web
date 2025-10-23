<?php
session_start();
include 'db.php';

// Admin access check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'maintenance'])) {
    header("Location: ../login.php");
    exit();
}

// Create New Task
if (isset($_POST['create_task'])) {
    $room_id = $_POST['room_id'];
    $assigned_to_user_id = $_POST['assigned_to_user_id'];
    $task_description = $_POST['task_description'];

    $sql = "INSERT INTO maintenance_tasks (room_id, assigned_to_user_id, task_description, status) VALUES (?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sis", $room_id, $assigned_to_user_id, $task_description);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Faena asignada exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al crear la tarea: ' . $stmt->error];
    }
    header("Location: ../admin_maintenance_tasks.php");
    exit();
}

// Mark Task as Complete (from admin panel link)
if (isset($_GET['action']) && $_GET['action'] == 'complete' && isset($_GET['id'])) {
    $task_id = $_GET['id'];
    $status = 'completed';

    $sql = "UPDATE maintenance_tasks SET status = ?, completed_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $task_id);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Faena marcada como completada.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al actualizar la faena.'];
    }
    header("Location: ../admin_maintenance_tasks.php");
    exit();
}

// Delete Task
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $task_id = $_GET['id'];

    $sql = "DELETE FROM maintenance_tasks WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $task_id);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Faena eliminada.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al eliminar la faena.'];
    }
    header("Location: ../admin_maintenance_tasks.php");
    exit();
}

?>
