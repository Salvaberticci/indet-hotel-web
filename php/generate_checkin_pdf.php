<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Incluir el autoload de Composer

use FPDF; // Usar la clase FPDF directamente

function generateCheckinPDF($reservation_id) {
    ob_start(); // Start output buffering
    global $conn;

    // Get reservation details
    $sql = "SELECT r.*, rm.type as room_type, rm.capacity, f.name as floor_name,
                   u.name as user_name, u.cedula
            FROM reservations r
            JOIN rooms rm ON r.room_id = rm.id
            JOIN floors f ON rm.floor_id = f.id
            JOIN users u ON r.user_id = u.id
            WHERE r.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reservation = $result->fetch_assoc();

    if (!$reservation) {
        ob_end_clean(); // Clean (delete) the output buffer and disable output buffering
        return false;
    }

    // Create PDF
    $pdf = new FPDF();
    $pdf->SetFont('Helvetica', '', 10); // Set default font
    $pdf->AddPage();

    // Header
    $pdf->SetFont('Helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'INDET - Recibo de Check-in', 0, 1, 'C');
    $pdf->Ln(10);

    // Hotel Info
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Hotel INDET', 0, 1);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->Cell(0, 6, 'Valera Edo Trujillo', 0, 1);
    $pdf->Cell(0, 6, 'Instagram: @indetrujillo', 0, 1);
    $pdf->Cell(0, 6, 'Telefono: 0412-897643', 0, 1);
    $pdf->Ln(10);

    // Reservation Details
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Detalles de la Reserva', 0, 1);
    $pdf->SetFont('Helvetica', '', 10);

    $pdf->Cell(50, 6, 'ID de Reserva:', 0, 0);
    $pdf->Cell(0, 6, $reservation['id'], 0, 1);

    $pdf->Cell(50, 6, 'Huesped:', 0, 0);
    $pdf->Cell(0, 6, $reservation['guest_name'] . ' ' . $reservation['guest_lastname'], 0, 1);

    $pdf->Cell(50, 6, 'Cedula:', 0, 0);
    $pdf->Cell(0, 6, $reservation['cedula'], 0, 1);

    $pdf->Cell(50, 6, 'Email:', 0, 0);
    $pdf->Cell(0, 6, $reservation['guest_email'], 0, 1);

    $pdf->Cell(50, 6, 'Habitacion:', 0, 0);
    $pdf->Cell(0, 6, $reservation['room_type'] . ' (' . $reservation['room_id'] . ')', 0, 1);

    $pdf->Cell(50, 6, 'Piso:', 0, 0);
    $pdf->Cell(0, 6, $reservation['floor_name'], 0, 1);

    $pdf->Cell(50, 6, 'Capacidad:', 0, 0);
    $pdf->Cell(0, 6, $reservation['capacity'] . ' personas', 0, 1);

    $pdf->Cell(50, 6, 'Fecha de Llegada:', 0, 0);
    $pdf->Cell(0, 6, date('d/m/Y', strtotime($reservation['checkin_date'])), 0, 1);

    $pdf->Cell(50, 6, 'Fecha de Salida:', 0, 0);
    $pdf->Cell(0, 6, date('d/m/Y', strtotime($reservation['checkout_date'])), 0, 1);

    $pdf->Cell(50, 6, 'Adultos:', 0, 0);
    $pdf->Cell(0, 6, $reservation['adultos'], 0, 1);

    $pdf->Cell(50, 6, 'Ninos:', 0, 0);
    $pdf->Cell(0, 6, $reservation['ninos'], 0, 1);

    $pdf->Cell(50, 6, 'Discapacitados:', 0, 0);
    $pdf->Cell(0, 6, $reservation['discapacitados'], 0, 1);

    $pdf->Ln(10);

    // Terms and conditions
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'Terminos y Condiciones:', 0, 1);
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->MultiCell(0, 4, '1. El huesped se compromete a respetar las normas del hotel.
2. Cualquier dano causado sera cobrado al huesped.
3. El check-out debe realizarse antes de las 12:00 PM.
4. Se requiere identificacion valida para el check-in.
5. No se permiten mascotas sin autorizacion previa.', 0, 1);

    $pdf->Ln(10);

    // Signature
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->Cell(0, 6, 'Fecha de Check-in: ' . date('d/m/Y H:i'), 0, 1);
    $pdf->Ln(20);

    $pdf->Cell(80, 6, '_______________________________', 0, 0);
    $pdf->Cell(80, 6, '_______________________________', 0, 1);
    $pdf->Cell(80, 6, 'Firma del Huesped', 0, 0);
    $pdf->Cell(80, 6, 'Firma del Recepcionista', 0, 1);

    // Generate filename and save
    $filename = 'checkin_receipt_' . $reservation_id . '_' . date('Ymd_His') . '.pdf';
    $filepath = '../receipts/' . $filename;

    // Create receipts directory if it doesn't exist
    if (!file_exists('../receipts/')) {
        mkdir('../receipts/', 0777, true);
    }

    $pdf->Output($filepath, 'F');

    ob_end_clean(); // Clean (delete) the output buffer and disable output buffering
    return 'receipts/' . $filename;
}
?>
