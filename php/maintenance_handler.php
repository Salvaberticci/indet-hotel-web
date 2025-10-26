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
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Tarea asignada exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al crear la tarea: ' . $stmt->error];
    }
    header("Location: ../admin_maintenance_tasks.php");
    exit();
}

// Create Task from Checkout
if (isset($_POST['create_checkout_task'])) {
    $reservation_id = $_POST['reservation_id'];
    $room_id = $_POST['room_id'];
    $task_description = trim($_POST['task_description']);
    $assigned_to_user_id = $_POST['assigned_to_user_id'];

    $conn->begin_transaction();

    try {
        // Update reservation status to completed
        $update_sql = "UPDATE reservations SET status = 'completed' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $reservation_id);
        $update_stmt->execute();

        // Create maintenance task
        $task_sql = "INSERT INTO maintenance_tasks (room_id, assigned_to_user_id, task_description, status) VALUES (?, ?, ?, 'pending')";
        $task_stmt = $conn->prepare($task_sql);
        $task_stmt->bind_param("sis", $room_id, $assigned_to_user_id, $task_description);
        $task_stmt->execute();

        $conn->commit();
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Check-out procesado y tarea de mantenimiento creada exitosamente.'];

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al procesar check-out: ' . $e->getMessage()];
    }

    header("Location: ../admin_checkin_checkout.php");
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
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Tarea marcada como completada.'];
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
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Tarea eliminada.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al eliminar la faena.'];
    }
    header("Location: ../admin_maintenance_tasks.php");
    exit();
}

?>
