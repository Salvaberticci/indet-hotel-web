<?php
error_reporting(E_ALL); // Report all errors
ini_set('display_errors', 1); // Display errors for debugging

session_start();
include 'db.php';

// Start output buffering to catch any unexpected output
ob_start();

// Set content type to JSON for AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    $is_ajax = true;
} else {
    $is_ajax = false;
}

// Function to send JSON response and exit
function sendJsonResponse($success, $message, $is_ajax, $redirect_url = '../admin_reservations.php') {
    $output = ob_get_clean(); // Get any buffered output
    if (!empty($output)) {
        error_log("Unexpected output before JSON response: " . $output);
        // Append unexpected output to the message for AJAX requests
        if ($is_ajax) {
            $message .= "\nOutput inesperado del servidor: " . $output;
        }
    }

    if ($is_ajax) {
        // Set flash message for AJAX success cases so it shows after page reload
        if ($success) {
            $_SESSION['flash_message'] = ['status' => 'success', 'text' => $message];
        }
        echo json_encode(['success' => $success, 'message' => $message]);
    } else {
        $_SESSION['flash_message'] = ['status' => ($success ? 'success' : 'error'), 'text' => $message];
        header("Location: " . $redirect_url);
    }
    exit();
}

// Custom error handler to convert errors to exceptions
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Custom exception handler to catch uncaught exceptions
set_exception_handler(function ($exception) use ($is_ajax) {
    error_log("Uncaught exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    sendJsonResponse(false, 'Error interno del servidor: ' . $exception->getMessage(), $is_ajax);
});


// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    error_log("Access denied: User not logged in or not admin.");
    sendJsonResponse(false, 'Acceso denegado. Solo administradores pueden agregar reservas de esta manera.', $is_ajax, '../login.php');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("POST request received in admin_book_reservation.php");

    // Ensure database connection is still valid
    if ($conn->connect_error) {
        error_log("Database connection error: " . $conn->connect_error);
        sendJsonResponse(false, 'Error de conexión a la base de datos: ' . $conn->connect_error, $is_ajax);
    }
    error_log("Database connection successful.");

    // Validaciones básicas para el formulario de administración
    $required_fields = ['user_id', 'checkin', 'checkout', 'floor_id', 'selected_rooms'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            error_log("Missing required field: " . $field);
            sendJsonResponse(false, 'Por favor, complete todos los campos del formulario.', $is_ajax);
        }
    }
    error_log("All required fields are present.");

    // Validar fechas
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $today = date('Y-m-d');

    if ($checkin < $today) {
        error_log("Check-in date is in the past: " . $checkin);
        sendJsonResponse(false, 'La fecha de llegada no puede ser anterior a hoy.', $is_ajax);
    }
    error_log("Check-in date is valid.");

    if ($checkout <= $checkin) {
        error_log("Check-out date is not after check-in date: " . $checkout . " <= " . $checkin);
        sendJsonResponse(false, 'La fecha de salida debe ser posterior a la fecha de llegada.', $is_ajax);
    }
    error_log("Check-out date is valid.");

    $floor_id = $_POST['floor_id'];
    $selected_rooms = json_decode($_POST['selected_rooms'], true);
    $adultos = (int)$_POST['adultos'];
    $ninos = (int)$_POST['ninos'];
    $discapacitados = (int)$_POST['discapacitados'];
    $user_id = $_POST['user_id'];
    $total_people = $adultos + $ninos + $discapacitados;

    error_log("Parsed form data: user_id=" . $user_id . ", checkin=" . $checkin . ", checkout=" . $checkout . ", floor_id=" . $floor_id . ", adultos=" . $adultos . ", ninos=" . $ninos . ", discapacitados=" . $discapacitados . ", selected_rooms=" . json_encode($selected_rooms));

    // Fetch user details from DB for guest_name, guest_email, cedula, guest_lastname
    $user_sql = "SELECT cedula, name, email FROM users WHERE id = ?"; // Removed 'lastname'
    $stmt_user = $conn->prepare($user_sql);
    if (!$stmt_user) {
        error_log('Error al preparar la consulta de usuario: ' . $conn->error);
        sendJsonResponse(false, 'Error al preparar la consulta de usuario: ' . $conn->error, $is_ajax);
    }
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $user_result = $stmt_user->get_result();
    if ($user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();
        $cedula = $user_data['cedula'];
        $guest_name = $user_data['name'];
        $guest_lastname = ''; // Set to empty or handle as needed if 'lastname' is not in DB
        $guest_email = $user_data['email'];
        error_log("User data fetched: cedula=" . $cedula . ", name=" . $guest_name);
    } else {
        error_log("User not found for ID: " . $user_id);
        sendJsonResponse(false, 'Usuario seleccionado no encontrado.', $is_ajax);
    }


    // Verificar que las habitaciones seleccionadas estén disponibles
    $conn->begin_transaction();
    error_log("Transaction started.");
    try {
        foreach ($selected_rooms as $room) {
            $room_id = $room['id'];
            error_log("Processing room_id: " . $room_id);

            // Verificar reservacion
            $availability_query = "SELECT COUNT(*) as count FROM reservations
                                   WHERE room_id = ? AND status IN ('confirmed', 'pending')
                                   AND ((checkin_date <= ? AND checkout_date > ?) OR
                                        (checkin_date < ? AND checkout_date >= ?) OR
                                        (checkin_date >= ? AND checkout_date <= ?))";
            $stmt = $conn->prepare($availability_query);
            if (!$stmt) {
                error_log('Error al preparar la consulta de disponibilidad: ' . $conn->error);
                throw new Exception('Error al preparar la consulta de disponibilidad: ' . $conn->error);
            }
            $stmt->bind_param("issssss", $room_id, $checkin, $checkin, $checkout, $checkout, $checkin, $checkout);
            $stmt->execute();
            $result = $stmt->get_result();
            $count = $result->fetch_assoc()['count'];
            error_log("Availability check for room " . $room_id . ": " . $count . " existing reservations.");

            if ($count > 0) {
                throw new Exception("La habitación {$room_id} no está disponible en las fechas seleccionadas.");
            }

            // Insertar reserva
            $sql = "INSERT INTO reservations (user_id, room_id, checkin_date, checkout_date, guest_name, guest_email, cedula, guest_lastname, adultos, ninos, discapacitados, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log('Error al preparar la consulta de inserción de reserva: ' . $conn->error);
                throw new Exception('Error al preparar la consulta de inserción de reserva: ' . $conn->error);
            }
            $stmt->bind_param("iissssssiii", $user_id, $room_id, $checkin, $checkout, $guest_name, $guest_email, $cedula, $guest_lastname, $adultos, $ninos, $discapacitados);

            if (!$stmt->execute()) {
                error_log("Error al crear la reserva para la habitación {$room_id}: " . $stmt->error);
                throw new Exception("Error al crear la reserva para la habitación {$room_id}: " . $stmt->error);
            }
            error_log("Reservation inserted for room_id: " . $room_id);
        }

        $conn->commit();
        error_log("Transaction committed successfully.");

        // Schedule maintenance tasks for each reservation
        error_log("Including maintenance_scheduler.php");
        include 'maintenance_scheduler.php';
        foreach ($selected_rooms as $room) {
            // Get the reservation ID for this room (assuming we can get it from the last insert)
            $last_id_sql = "SELECT LAST_INSERT_ID() as id";
            $last_id_result = $conn->query($last_id_sql);
            $reservation_id = $last_id_result->fetch_assoc()['id'];
            error_log("Scheduling cleaning for reservation ID: " . $reservation_id);
            scheduleCleaningBeforeReservation($reservation_id);
        }

        // Save guest details if provided - AFTER scheduling maintenance
        if (isset($_POST['guests'])) {
            $guests_json = $_POST['guests'];
            error_log("Guests JSON received: " . $guests_json);
            $guests = json_decode($guests_json, true);
            error_log("Guests decoded: " . print_r($guests, true));

            if (is_array($guests) && !empty($guests)) {
                // For multiple rooms, we need to get all reservation IDs that were created
                // Since LAST_INSERT_ID() only gives us the last one, we need to query for recent reservations
                $total_rooms = count($selected_rooms);
                $user_id_for_query = $user_id;
                $checkin_for_query = $checkin;
                $checkout_for_query = $checkout;

                // Get all reservation IDs for this user and time period
                $get_reservation_ids_sql = "SELECT id FROM reservations
                                           WHERE user_id = ? AND checkin_date = ? AND checkout_date = ?
                                           ORDER BY id DESC LIMIT ?";
                $stmt_ids = $conn->prepare($get_reservation_ids_sql);
                $stmt_ids->bind_param("issi", $user_id_for_query, $checkin_for_query, $checkout_for_query, $total_rooms);
                $stmt_ids->execute();
                $result_ids = $stmt_ids->get_result();

                $reservation_ids = [];
                while ($row = $result_ids->fetch_assoc()) {
                    $reservation_ids[] = $row['id'];
                }
                $stmt_ids->close();

                // Reverse the array so it matches the order of selected_rooms
                $reservation_ids = array_reverse($reservation_ids);

                error_log("Found reservation IDs: " . implode(', ', $reservation_ids));

                // Save guests for each reservation - group by room_id
                $guests_by_room = [];
                foreach ($guests as $guest) {
                    $room_id = $guest['room_id'] ?? '';
                    if (!isset($guests_by_room[$room_id])) {
                        $guests_by_room[$room_id] = [];
                    }
                    $guests_by_room[$room_id][] = $guest;
                }

                // Assign guests to reservations based on room_id
                foreach ($reservation_ids as $res_index => $res_id) {
                    $room_id = $selected_rooms[$res_index]['id'];
                    if (isset($guests_by_room[$room_id])) {
                        foreach ($guests_by_room[$room_id] as $guest) {
                            if (!empty($guest['name'])) {
                                error_log("Saving guest for reservation ID: $res_id, room: $room_id");

                                $guest_sql = "INSERT INTO reservation_guests (reservation_id, guest_name, guest_lastname, guest_phone) VALUES (?, ?, ?, ?)";
                                $stmt_guest = $conn->prepare($guest_sql);
                                if (!$stmt_guest) {
                                    error_log('Error al preparar la consulta de inserción de huésped: ' . $conn->error);
                                    throw new Exception('Error al preparar la consulta de inserción de huésped: ' . $conn->error);
                                }
                                $stmt_guest->bind_param("isss", $res_id, $guest['name'], $guest['lastname'], $guest['phone']);
                                if (!$stmt_guest->execute()) {
                                    error_log("Error al guardar huésped: " . $stmt_guest->error);
                                    throw new Exception("Error al guardar huésped: " . $stmt_guest->error);
                                }
                                $stmt_guest->close();
                                error_log("Guest saved for reservation $res_id: " . $guest['name']);
                            }
                        }
                    }
                }
            }
        }

        sendJsonResponse(true, '¡Reserva agregada exitosamente!', $is_ajax);

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Transaction rolled back. Error: " . $e->getMessage());
        sendJsonResponse(false, $e->getMessage(), $is_ajax);
    }

    $conn->close();
    error_log("Database connection closed.");
}
?>
