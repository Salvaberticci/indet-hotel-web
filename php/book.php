<?php
session_start();
include 'db.php';

// Check if this is an AJAX request
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $is_admin_reservation = isset($_POST['add_reservation_admin']) && $_POST['add_reservation_admin'] === 'true';

    // Validaciones básicas
    $required_fields = ['checkin', 'checkout', 'floor_id', 'selected_rooms'];
    if ($is_admin_reservation) {
        $required_fields[] = 'user_id';
    } else {
        $required_fields = array_merge($required_fields, ['cedula', 'guest_name', 'guest_lastname', 'guest_email']);
    }

    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $error_msg = 'Por favor, complete todos los campos del formulario.';
            if ($is_ajax) {
                echo json_encode(['success' => false, 'message' => $error_msg]);
                exit();
            } else {
                $_SESSION['flash_message'] = [
                    'status' => 'error',
                    'text' => $error_msg
                ];
                header("Location: ../" . ($is_admin_reservation ? "admin_reservations.php" : "reservar.php"));
                exit();
            }
        }
    }

    // Validar fechas
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $today = date('Y-m-d');

    if ($checkin < $today) {
        $error_msg = 'La fecha de llegada no puede ser anterior a hoy.';
        if ($is_ajax) {
            echo json_encode(['success' => false, 'message' => $error_msg]);
            exit();
        } else {
            $_SESSION['flash_message'] = [
                'status' => 'error',
                'text' => $error_msg
            ];
            header("Location: ../" . ($is_admin_reservation ? "admin_reservations.php" : "reservar.php"));
            exit();
        }
    }

    if ($checkout <= $checkin) {
        $error_msg = 'La fecha de salida debe ser posterior a la fecha de llegada.';
        if ($is_ajax) {
            echo json_encode(['success' => false, 'message' => $error_msg]);
            exit();
        } else {
            $_SESSION['flash_message'] = [
                'status' => 'error',
                'text' => $error_msg
            ];
            header("Location: ../" . ($is_admin_reservation ? "admin_reservations.php" : "reservar.php"));
            exit();
        }
    }

    $floor_id = $_POST['floor_id'];
    $selected_rooms = json_decode($_POST['selected_rooms'], true);
    $adultos = (int)$_POST['adultos'];
    $ninos = (int)$_POST['ninos'];
    $discapacitados = (int)$_POST['discapacitados'];

    $user_id = null;
    $cedula = null;
    $guest_name = null;
    $guest_lastname = null;
    $guest_email = null;

    if ($is_admin_reservation) {
        $user_id = $_POST['user_id'];
        // Fetch user details from DB
        $user_sql = "SELECT cedula, name, lastname, email FROM users WHERE id = ?";
        $stmt_user = $conn->prepare($user_sql);
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $user_result = $stmt_user->get_result();
        if ($user_result->num_rows > 0) {
            $user_data = $user_result->fetch_assoc();
            $cedula = $user_data['cedula'];
            $guest_name = $user_data['name'];
            $guest_lastname = $user_data['lastname'];
            $guest_email = $user_data['email'];
        } else {
            $error_msg = 'Usuario seleccionado no encontrado.';
            if ($is_ajax) {
                echo json_encode(['success' => false, 'message' => $error_msg]);
                exit();
            } else {
                $_SESSION['flash_message'] = [
                    'status' => 'error',
                    'text' => $error_msg
                ];
                header("Location: ../admin_reservations.php");
                exit();
            }
        }
    } else {
        if (!isset($_SESSION['user_id'])) {
            $error_msg = 'Debes iniciar sesión para realizar una reserva.';
            if ($is_ajax) {
                echo json_encode(['success' => false, 'message' => $error_msg]);
                exit();
            } else {
                $_SESSION['flash_message'] = [
                    'status' => 'error',
                    'text' => $error_msg
                ];
                header("Location: ../login.php");
                exit();
            }
        }
        $user_id = $_SESSION['user_id'];
        $cedula = $_POST['cedula'];
        $guest_name = $_POST['guest_name'];
        $guest_lastname = $_POST['guest_lastname'];
        $guest_email = $_POST['guest_email'];
    }

    // Verificar que las habitaciones seleccionadas estén disponibles
    $conn->begin_transaction();
    try {
        foreach ($selected_rooms as $room) {
            $room_id = $room['id'];

            // Verificar reservacion
            $availability_query = "SELECT COUNT(*) as count FROM reservations
                                   WHERE room_id = ? AND status IN ('confirmed', 'pending')
                                   AND ((checkin_date <= ? AND checkout_date > ?) OR
                                        (checkin_date < ? AND checkout_date >= ?) OR
                                        (checkin_date >= ? AND checkout_date <= ?))";
            $stmt = $conn->prepare($availability_query);
            $stmt->bind_param("issssss", $room_id, $checkin, $checkin, $checkout, $checkout, $checkin, $checkout);
            $stmt->execute();
            $result = $stmt->get_result();
            $count = $result->fetch_assoc()['count'];

            if ($count > 0) {
                throw new Exception("La habitación {$room_id} no está disponible en las fechas seleccionadas.");
            }

            // Insertar reserva
            $sql = "INSERT INTO reservations (user_id, room_id, checkin_date, checkout_date, guest_name, guest_email, cedula, guest_lastname, adultos, ninos, discapacitados, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iissssssiii", $user_id, $room_id, $checkin, $checkout, $guest_name, $guest_email, $cedula, $guest_lastname, $adultos, $ninos, $discapacitados);

            if (!$stmt->execute()) {
                throw new Exception("Error al crear la reserva para la habitación {$room_id}.");
            }
        }

        $conn->commit();

        // Schedule maintenance tasks for each reservation
        include 'maintenance_scheduler.php';
        foreach ($selected_rooms as $room) {
            // Get the reservation ID for this room (assuming we can get it from the last insert)
            $last_id_sql = "SELECT LAST_INSERT_ID() as id";
            $last_id_result = $conn->query($last_id_sql);
            $reservation_id = $last_id_result->fetch_assoc()['id'];

            scheduleCleaningBeforeReservation($reservation_id);
        }

        // Store success message in session
        $_SESSION['flash_message'] = [
            'status' => 'success',
            'text' => '¡Reserva realizada exitosamente! Puedes ver los detalles en tu perfil.'
        ];

        if ($is_ajax) {
            echo json_encode(['success' => true, 'message' => 'Reserva realizada exitosamente']);
            exit();
        } else {
            // Redirect to user profile instead of confirmation page
            header("Location: ../" . ($is_admin_reservation ? "admin_reservations.php" : "user_profile.php"));
            exit();
        }

    } catch (Exception $e) {
        $conn->rollback();
        $error_msg = $e->getMessage();
        if ($is_ajax) {
            echo json_encode(['success' => false, 'message' => $error_msg]);
            exit();
        } else {
            $_SESSION['flash_message'] = [
                'status' => 'error',
                'text' => $error_msg
            ];
            header("Location: ../" . ($is_admin_reservation ? "admin_reservations.php" : "reservar.php"));
            exit();
        }
    }

    $conn->close();
}
?>
