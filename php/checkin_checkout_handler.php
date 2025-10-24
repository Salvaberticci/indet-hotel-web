<?php
session_start();
include 'db.php';

// Admin access check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

error_reporting(0); // Reactivar supresión de errores
ini_set('display_errors', 0); // Reactivar supresión de errores

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    $reservation_id = (int)($_GET['reservation_id'] ?? 0);
    $redirect_url = '../admin_checkin_checkout.php';

    if (!$reservation_id && $action !== 'general_report') {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'ID de reserva inválido.'];
        header("Location: $redirect_url");
        exit();
    }

    if ($action === 'checkin') {
        $sql = "UPDATE reservations SET status = 'confirmed', checkin_time = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $reservation_id);

        if ($stmt->execute()) {
            $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Check-in confirmado exitosamente.'];
        } else {
            $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al confirmar check-in.'];
        }
        header("Location: $redirect_url");
        exit();

    } elseif ($action === 'checkout') {
        $conn->begin_transaction();

        try {
            $update_sql = "UPDATE reservations SET status = 'completed', checkout_time = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $reservation_id);
            $update_stmt->execute();

            $conn->commit();
            $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Check-out procesado exitosamente.'];

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al procesar check-out: ' . $e->getMessage()];
        }
        header("Location: $redirect_url");
        exit();

    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Acción inválida.'];
        header("Location: $redirect_url");
        exit();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') { // Manejar reportes con POST
    $action = $_POST['action'] ?? '';
    $reservation_id = (int)($_POST['reservation_id'] ?? 0);

    if (!$reservation_id && $action !== 'general_report') {
        echo json_encode(['success' => false, 'message' => 'ID de reserva inválido.']);
        exit();
    }

    if ($action === 'general_report') {
        header('Content-Type: application/json');
        ob_start();
        include 'generate_general_report.php';
        $pdf_url = generateGeneralReport();
        ob_end_clean();

        echo json_encode([
            'success' => true,
            'message' => 'Reporte general generado exitosamente.',
            'pdf_url' => $pdf_url
        ]);
        exit();

    } elseif ($action === 'individual_report') {
        header('Content-Type: application/json');
        $type = $_POST['type'] ?? '';

        if ($type === 'checkin') {
            ob_start();
            include 'generate_checkin_pdf.php';
            $pdf_url = generateCheckinPDF($reservation_id);
            ob_end_clean();

            echo json_encode([
                'success' => true,
                'message' => 'Reporte de check-in generado exitosamente.',
                'pdf_url' => $pdf_url
            ]);
        } elseif ($type === 'checkout') {
            ob_start();
            include 'generate_checkout_pdf.php';
            $pdf_url = generateCheckoutPDF($reservation_id);
            ob_end_clean();

            echo json_encode([
                'success' => true,
                'message' => 'Reporte de check-out generado exitosamente.',
                'pdf_url' => $pdf_url
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Tipo de reporte inválido.']);
        }
        exit();

    } else {
        echo json_encode(['success' => false, 'message' => 'Acción inválida.']);
        exit();
    }
} else {
    $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Método no permitido.'];
    header("Location: $redirect_url");
    exit();
}

$conn->close();
?>
