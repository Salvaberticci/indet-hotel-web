<?php
// Only include db.php if not already included
if (!isset($conn)) {
    include 'db.php';
}

// Function to schedule cleaning tasks 1 day before reservation
function scheduleCleaningBeforeReservation($reservation_id) {
    global $conn;

    // Get reservation details
    $reservation_sql = "SELECT room_id, checkin_date FROM reservations WHERE id = ?";
    $stmt = $conn->prepare($reservation_sql);
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $reservation = $stmt->get_result()->fetch_assoc();

    if (!$reservation) return;

    $room_id = $reservation['room_id'];
    $checkin_date = $reservation['checkin_date'];

    // Calculate cleaning date (1 day before check-in)
    $cleaning_date = date('Y-m-d', strtotime($checkin_date . ' -1 day'));

    // Check if cleaning task already exists
    $check_sql = "SELECT id FROM maintenance_tasks WHERE room_id = ? AND DATE(created_at) = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $room_id, $cleaning_date);
    $check_stmt->execute();

    if ($check_stmt->get_result()->num_rows == 0) {
        // Assign to first available maintenance staff
        $staff_sql = "SELECT id FROM users WHERE role = 'maintenance' LIMIT 1";
        $staff_result = $conn->query($staff_sql);

        if ($staff_result->num_rows > 0) {
            $staff = $staff_result->fetch_assoc();

            // Create cleaning task
            $task_description = 'Limpieza programada antes del check-in';
            $created_at = $cleaning_date . ' 08:00:00';
            $insert_sql = "INSERT INTO maintenance_tasks (room_id, assigned_to_user_id, task_description, status, created_at)
                          VALUES (?, ?, ?, 'pending', ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iiss", $room_id, $staff['id'], $task_description, $created_at);
            $insert_stmt->execute();
        }
    }
}

// Function to schedule cleaning tasks 30 minutes after checkout
function scheduleCleaningAfterCheckout($reservation_id) {
    global $conn;

    // Get reservation details
    $reservation_sql = "SELECT room_id, checkout_date FROM reservations WHERE id = ?";
    $stmt = $conn->prepare($reservation_sql);
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $reservation = $stmt->get_result()->fetch_assoc();

    if (!$reservation) return;

    $room_id = $reservation['room_id'];
    $checkout_date = $reservation['checkout_date'];

    // Calculate cleaning time (30 minutes after checkout)
    $cleaning_datetime = date('Y-m-d H:i:s', strtotime($checkout_date . ' +30 minutes'));

    // Check if cleaning task already exists
    $check_sql = "SELECT id FROM maintenance_tasks WHERE room_id = ? AND created_at = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $room_id, $cleaning_datetime);
    $check_stmt->execute();

    if ($check_stmt->get_result()->num_rows == 0) {
        // Assign to first available maintenance staff
        $staff_sql = "SELECT id FROM users WHERE role = 'maintenance' LIMIT 1";
        $staff_result = $conn->query($staff_sql);

        if ($staff_result->num_rows > 0) {
            $staff = $staff_result->fetch_assoc();

            // Create cleaning task
            $task_description = 'Limpieza después del check-out';
            $insert_sql = "INSERT INTO maintenance_tasks (room_id, assigned_to_user_id, task_description, status, created_at)
                          VALUES (?, ?, ?, 'pending', ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iiss", $room_id, $staff['id'], $task_description, $cleaning_datetime);
            $insert_stmt->execute();
        }
    }
}

// Function to check and schedule maintenance tasks for upcoming reservations
function checkAndScheduleMaintenanceTasks() {
    global $conn;

    // Get reservations for tomorrow
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $reservations_sql = "SELECT id FROM reservations WHERE checkin_date = ? AND status IN ('confirmed', 'pending')";
    $stmt = $conn->prepare($reservations_sql);
    $stmt->bind_param("s", $tomorrow);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($reservation = $result->fetch_assoc()) {
        scheduleCleaningBeforeReservation($reservation['id']);
    }

    // Get reservations checking out today
    $today = date('Y-m-d');
    $checkout_sql = "SELECT id FROM reservations WHERE checkout_date = ? AND status = 'confirmed'";
    $checkout_stmt = $conn->prepare($checkout_sql);
    $checkout_stmt->bind_param("s", $today);
    $checkout_stmt->execute();
    $checkout_result = $checkout_stmt->get_result();

    while ($reservation = $checkout_result->fetch_assoc()) {
        // Schedule cleaning 30 minutes after checkout
        scheduleCleaningAfterCheckout($reservation['id']);
    }
}

// This script can be called via cron job or included in other scripts
// For now, we'll run it when this file is accessed directly
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    checkAndScheduleMaintenanceTasks();
    echo "Maintenance tasks checked and scheduled.";
    $conn->close();
}
?>