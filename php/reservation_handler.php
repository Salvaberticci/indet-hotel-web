<?php
include 'db.php';
session_start();

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Prevent any HTML output for AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    // Disable error display for AJAX
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_reservation'])) {
        // Debug logging
        error_log("Starting reservation processing");
        error_log("POST data: " . print_r($_POST, true));

        $cedula = $_POST['cedula'];
        $guest_name = $_POST['guest_name'];
        $guest_lastname = $_POST['guest_lastname'];
        $guest_email = $_POST['guest_email'];
        $checkin_date = $_POST['checkin_date'];
        $checkout_date = $_POST['checkout_date'];
        $adultos = (int)$_POST['adultos'];
        $ninos = (int)$_POST['ninos'];
        $discapacitados = (int)$_POST['discapacitados'];
        $selected_rooms = json_decode($_POST['selected_rooms'], true);
        $user_id = isset($_POST['user_id']) && !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;

        error_log("Selected rooms decoded: " . print_r($selected_rooms, true));

        // Validate that at least one room is selected
        if (empty($selected_rooms)) {
            error_log("No rooms selected");
            $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Debe seleccionar al menos una habitación.'];
            header("Location: ../admin_reservations.php");
            exit();
        }

        // Use selected user_id if provided, otherwise use admin user ID
        $reservation_user_id = $user_id ?? $_SESSION['user_id'];

        $success_count = 0;
        $errors = [];

        foreach ($selected_rooms as $room) {
            $room_id = $room['id'];
            error_log("Processing room: " . $room_id);

            // Get floor_id from the room - room IDs in database are strings like '001', '002', etc.
            $floor_sql = "SELECT floor_id FROM rooms WHERE id = ?";
            $floor_stmt = $conn->prepare($floor_sql);
            $floor_stmt->bind_param("s", $room_id);
            $floor_stmt->execute();
            $floor_result = $floor_stmt->get_result();
            $floor_row = $floor_result->fetch_assoc();

            $floor_id = null; // Inicializar floor_id
            if ($floor_row) { // Si se encuentra con el ID original
                $floor_id = $floor_row['floor_id'];
            } else { // Si no se encuentra con el ID original, intentar con alternativos
                error_log("Room $room_id not found with original ID. Trying alternative IDs.");
                $alt_room_sql = "SELECT id, floor_id FROM rooms WHERE id = ? OR id = ?";
                $alt_stmt = $conn->prepare($alt_room_sql);
                $alt_room_id = str_pad($room_id, 3, '0', STR_PAD_LEFT);
                $alt_stmt->bind_param("ss", $room_id, $alt_room_id);
                $alt_stmt->execute();
                $alt_result = $alt_stmt->get_result();
                $alt_row = $alt_result->fetch_assoc();
                $alt_stmt->close();

                if ($alt_row) {
                    $room_id = $alt_row['id'];
                    $floor_id = $alt_row['floor_id'];
                    error_log("Found room with alternative ID: $room_id, floor_id: $floor_id");
                } else {
                    $errors[] = "Habitación $room_id no encontrada.";
                    error_log("Room $room_id not found even with alternative IDs.");
                    $floor_stmt->close(); // Cerrar el stmt original antes de continuar
                    continue;
                }
            }
            $floor_stmt->close(); // Cerrar el stmt original

            // La verificación de !isset($floor_id) ya no es necesaria aquí,
            // ya que se maneja dentro del bloque if/else anterior.
            // Si se llega a este punto y $floor_id es null, significa que hubo un error
            // y ya se añadió un mensaje a $errors y se hizo 'continue'.

            error_log("Floor ID for room $room_id: " . $floor_id);

            $sql = "INSERT INTO reservations (user_id, room_id, checkin_date, checkout_date, guest_name, guest_lastname, guest_email, cedula, adultos, ninos, discapacitados, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issssssiiiis", $reservation_user_id, $room_id, $checkin_date, $checkout_date, $guest_name, $guest_lastname, $guest_email, $cedula, $adultos, $ninos, $discapacitados);

            if ($stmt->execute()) {
                $reservation_id = $stmt->insert_id;
                error_log("Reservation inserted with ID: " . $reservation_id);
                // Schedule cleaning task for this reservation
                // include 'maintenance_scheduler.php'; // Comentado temporalmente para depuración
                // scheduleCleaningBeforeReservation($reservation_id); // Comentado temporalmente para depuración
                $success_count++;
            } else {
                $error_msg = "Error al agregar reserva para habitación $room_id: " . $stmt->error;
                error_log($error_msg);
                $errors[] = $error_msg;
                // Capturar el error de la base de datos para devolverlo en la respuesta AJAX
                // Se eliminó la lógica de respuesta AJAX detallada aquí, ya que el formulario de adición de reservas fue eliminado.
            }
            $stmt->close();
        }

        // Handle response based on request type
        // Se eliminó la lógica de respuesta AJAX para la adición de reservas, ya que el formulario fue eliminado.
        // Solo se mantiene la lógica para redirección en caso de que se intente acceder directamente.
        if ($success_count > 0) {
            $_SESSION['flash_message'] = ['status' => 'success', 'text' => "Se agregaron $success_count reserva(s) exitosamente."];
        } else {
            $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al agregar las reservas: ' . implode(', ', $errors)];
        }
        header("Location: ../admin_reservations.php");
        exit();
    }

    if (isset($_POST['update_reservation'])) {
        $id = $_POST['id'];
        $user_id = $_POST['user_id'];
        $room_id = $_POST['room_id'];
        $checkin_date = $_POST['checkin_date'];
        $checkout_date = $_POST['checkout_date'];
        $status = $_POST['status'];

        $sql = "UPDATE reservations SET user_id=?, room_id=?, checkin_date=?, checkout_date=?, status=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssi", $user_id, $room_id, $checkin_date, $checkout_date, $status, $id);
        if ($stmt->execute()) {
            $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Reserva actualizada exitosamente.'];
        } else {
            $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al actualizar la reserva.'];
        }
        $stmt->close();
        header("Location: ../admin_reservations.php");
        exit();
    }
}

if (isset($_GET['delete_reservation'])) {
    $id = $_GET['delete_reservation'];
    $sql = "DELETE FROM reservations WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Reserva eliminada exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al eliminar la reserva.'];
    }
    $stmt->close();
    header("Location: ../admin_reservations.php");
    exit();
}

$conn->close();
?>
