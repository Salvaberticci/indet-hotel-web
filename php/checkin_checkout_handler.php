<?php
session_start();
include 'db.php';

// Admin access check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// header('Content-Type: application/json'); // Ya no se devuelve JSON
error_reporting(E_ALL); // Activar todos los errores para depuración
ini_set('display_errors', 1); // Mostrar errores para depuración

if ($_SERVER['REQUEST_METHOD'] === 'GET') { // Cambiar a GET para la redirección simple
    $action = $_GET['action'] ?? '';
    $reservation_id = (int)($_GET['reservation_id'] ?? 0);
    $redirect_url = '../admin_checkin_checkout.php';

    if (!$reservation_id) {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'ID de reserva inválido.'];
        header("Location: $redirect_url");
        exit();
    }

    if ($action === 'checkin') {
        $sql = "UPDATE reservations SET status = 'confirmed' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $reservation_id);

        if ($stmt->execute()) {
            ob_start();
            include 'generate_checkin_pdf.php';
            $pdf_url = generateCheckinPDF($reservation_id);
            ob_end_clean();

            $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Check-in confirmado exitosamente. Se ha generado el recibo.'];
            if ($pdf_url) {
                $_SESSION['flash_message']['pdf_url'] = $pdf_url; // Guardar URL del PDF para abrirlo en el frontend
            }
        } else {
            $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al confirmar check-in.'];
        }
        header("Location: $redirect_url");
        exit();

    } elseif ($action === 'checkout') {
        $conn->begin_transaction();

        try {
            $update_sql = "UPDATE reservations SET status = 'completed' WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $reservation_id);
            $update_stmt->execute();

            $room_sql = "SELECT room_id FROM reservations WHERE id = ?";
            $room_stmt = $conn->prepare($room_sql);
            $room_stmt->bind_param("i", $reservation_id);
            $room_stmt->execute();
            $room_result = $room_stmt->get_result();
            $room_data = $room_result->fetch_assoc();

            if ($room_data) {
                $room_id = $room_data['room_id'];
                ob_start();
                include 'maintenance_scheduler.php';
                scheduleCleaningAfterCheckout($reservation_id);
                ob_end_clean();
            }

            $conn->commit();

            ob_start();
            include 'generate_checkout_pdf.php';
            $pdf_url = generateCheckoutPDF($reservation_id);
            ob_end_clean();

            $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Check-out procesado exitosamente. La habitación ha sido enviada a mantenimiento.'];
            if ($pdf_url) {
                $_SESSION['flash_message']['pdf_url'] = $pdf_url;
            }

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al procesar check-out: ' . $e->getMessage()];
        }
        header("Location: $redirect_url");
        exit();

    } elseif ($action === 'general_report') {
        ob_start();
        include 'generate_general_report.php';
        $pdf_url = generateGeneralReport();
        ob_end_clean();

        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Reporte general generado exitosamente.'];
        if ($pdf_url) {
            $_SESSION['flash_message']['pdf_url'] = $pdf_url;
        }
        header("Location: $redirect_url");
        exit();

    } elseif ($action === 'individual_report') {
        $type = $_GET['type'] ?? ''; // Cambiar a GET

        if ($type === 'checkin') {
            ob_start();
            include 'generate_checkin_pdf.php';
            $pdf_url = generateCheckinPDF($reservation_id);
            ob_end_clean();

            $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Reporte de check-in generado exitosamente.'];
            if ($pdf_url) {
                $_SESSION['flash_message']['pdf_url'] = $pdf_url;
            }
        } elseif ($type === 'checkout') {
            ob_start();
            include 'generate_checkout_pdf.php';
            $pdf_url = generateCheckoutPDF($reservation_id);
            ob_end_clean();

            $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Reporte de check-out generado exitosamente.'];
            if ($pdf_url) {
                $_SESSION['flash_message']['pdf_url'] = $pdf_url;
            }
        } else {
            $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Tipo de reporte inválido.'];
        }
        header("Location: $redirect_url");
        exit();

    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Acción inválida.'];
        header("Location: $redirect_url");
        exit();
    }
} else {
    $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Método no permitido.'];
    header("Location: $redirect_url");
    exit();
}

$conn->close();
?>
