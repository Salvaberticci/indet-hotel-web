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
    $user_id = intval($_POST['user_id']);

    if (empty($room_id) || empty($user_id)) {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Por favor, selecciona una habitación y un miembro del personal.'];
        header("Location: ../admin.php");
        exit();
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert the new maintenance task
        $stmt_task = $conn->prepare("INSERT INTO maintenance_tasks (room_id, assigned_to_user_id) VALUES (?, ?)");
        $stmt_task->bind_param("ii", $room_id, $user_id);
        $stmt_task->execute();
        $stmt_task->close();

        // Update the room status to 'cleaning'
        $current_date = date('Y-m-d');
        $stmt_room = $conn->prepare("UPDATE room_status SET status = 'cleaning', date = ? WHERE room_id = ?");
        $stmt_room->bind_param("si", $current_date, $room_id);
        $stmt_room->execute();
        $stmt_room->close();

        // Commit transaction
        $conn->commit();

        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Tarea de mantenimiento asignada correctamente.'];

    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al asignar la tarea.'];
    }

    $conn->close();
    header("Location: ../admin.php");
    exit();

} else {
    header("Location: ../admin.php");
    exit();
}
?>
