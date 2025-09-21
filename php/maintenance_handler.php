<?php
session_start();
include 'db.php';

// Admin access check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Mark Task as Complete (from admin panel link)
if (isset($_GET['action']) && $_GET['action'] == 'complete' && isset($_GET['id'])) {
    $task_id = $_GET['id'];
    $status = 'completed';

    $sql = "UPDATE maintenance_tasks SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $task_id);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Tarea marcada como completada.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al actualizar la tarea.'];
    }
    header("Location: ../admin.php#maintenance-tasks-section");
    exit();
}

// Update Task Status
if (isset($_POST['update_task_status'])) {
    $task_id = $_POST['task_id'];
    $status = $_POST['status'];

    $sql = "UPDATE maintenance_tasks SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $task_id);

    if ($stmt->execute()) {
        // Also update the room status if the task is completed
        if ($status == 'completed') {
            $room_id_sql = "SELECT room_id FROM maintenance_tasks WHERE id = ?";
            $room_stmt = $conn->prepare($room_id_sql);
            $room_stmt->bind_param("i", $task_id);
            $room_stmt->execute();
            $result = $room_stmt->get_result();
            if ($result->num_rows > 0) {
                $room = $result->fetch_assoc();
                $room_id = $room['room_id'];
                
                $update_room_sql = "UPDATE room_status SET status = 'available' WHERE room_id = ?";
                $update_stmt = $conn->prepare($update_room_sql);
                $update_stmt->bind_param("i", $room_id);
                $update_stmt->execute();
            }
        }
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Estado de la tarea actualizado.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al actualizar la tarea.'];
    }
    header("Location: ../admin.php");
    exit();
}

// Delete Task
if (isset($_GET['delete_task'])) {
    $task_id = $_GET['delete_task'];

    $sql = "DELETE FROM maintenance_tasks WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $task_id);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Tarea de mantenimiento eliminada.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al eliminar la tarea.'];
    }
    header("Location: ../admin.php");
    exit();
}
?>
