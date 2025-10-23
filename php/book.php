<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validaciones básicas
    $required_fields = ['cedula', 'guest_name', 'guest_lastname', 'guest_email', 'checkin', 'checkout', 'floor_id', 'room_capacity', 'selected_rooms'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $_SESSION['flash_message'] = [
                'status' => 'error',
                'text' => 'Por favor, complete todos los campos del formulario.'
            ];
            header("Location: ../reservar.php");
            exit();
        }
    }

    // Validar fechas
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $today = date('Y-m-d');

    if ($checkin < $today) {
        $_SESSION['flash_message'] = [
            'status' => 'error',
            'text' => 'La fecha de llegada no puede ser anterior a hoy.'
        ];
        header("Location: ../reservar.php");
        exit();
    }

    if ($checkout <= $checkin) {
        $_SESSION['flash_message'] = [
            'status' => 'error',
            'text' => 'La fecha de salida debe ser posterior a la fecha de llegada.'
        ];
        header("Location: ../reservar.php");
        exit();
    }

    $cedula = $_POST['cedula'];
    $guest_name = $_POST['guest_name'];
    $guest_lastname = $_POST['guest_lastname'];
    $guest_email = $_POST['guest_email'];
    $floor_id = $_POST['floor_id'];
    $room_capacity = $_POST['room_capacity'];
    $selected_rooms = json_decode($_POST['selected_rooms'], true);
    $adultos = (int)$_POST['adultos'];
    $ninos = (int)$_POST['ninos'];
    $discapacitados = (int)$_POST['discapacitados'];

    if (!isset($_SESSION['user_id'])) {
        $_SESSION['flash_message'] = [
            'status' => 'error',
            'text' => 'Debes iniciar sesión para realizar una reserva.'
        ];
        header("Location: ../login.php");
        exit();
    }
    $user_id = $_SESSION['user_id'];

    // Verificar que las habitaciones seleccionadas estén disponibles
    $conn->begin_transaction();
    try {
        foreach ($selected_rooms as $room) {
            $room_id = $room['id'];

            // Verificar disponibilidad
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

        // Redirect to user profile instead of confirmation page
        header("Location: ../user_profile.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['flash_message'] = [
            'status' => 'error',
            'text' => $e->getMessage()
        ];
        header("Location: ../reservar.php");
        exit();
    }

    $conn->close();
}
?>
