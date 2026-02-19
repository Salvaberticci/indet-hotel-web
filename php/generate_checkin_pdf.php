<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Incluir el autoload de Composer

use FPDF; // Usar la clase FPDF directamente

function generateCheckinPDF($reservation_id)
{
    ob_start(); // Start output buffering
    global $conn;

    // 1. Fetch group info from the provided reservation ID
    $group_sql = "SELECT user_id, checkin_date, checkout_date FROM reservations WHERE id = ?";
    $group_stmt = $conn->prepare($group_sql);
    $group_stmt->bind_param("i", $reservation_id);
    $group_stmt->execute();
    $group_info = $group_stmt->get_result()->fetch_assoc();

    if (!$group_info) {
        ob_end_clean();
        return false;
    }

    // 2. Fetch all reservations in the same group
    $sql = "SELECT r.*, rm.type as room_type, f.name as floor_name,
                   u.name as user_name, u.cedula
            FROM reservations r
            JOIN rooms rm ON r.room_id = rm.id
            JOIN floors f ON rm.floor_id = f.id
            JOIN users u ON r.user_id = u.id
            WHERE r.user_id = ? AND r.checkin_date = ? AND r.checkout_date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $group_info['user_id'], $group_info['checkin_date'], $group_info['checkout_date']);
    $stmt->execute();
    $result = $stmt->get_result();

    $reservations = [];
    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }

    if (empty($reservations)) {
        ob_end_clean();
        return false;
    }

    $reservation = $reservations[0];

    // Create PDF
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetFont('Helvetica', '', 10); // Set default font

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
    $pdf->Cell(0, 6, utf8_decode($reservation['guest_name'] . ' ' . $reservation['guest_lastname']), 0, 1);

    $pdf->Cell(50, 6, 'Cedula:', 0, 0);
    $pdf->Cell(0, 6, $reservation['cedula'], 0, 1);

    $pdf->Cell(50, 6, 'Email:', 0, 0);
    $pdf->Cell(0, 6, $reservation['guest_email'], 0, 1);

    $pdf->SetFont('Helvetica', 'B', 11);
    $pdf->Cell(0, 8, utf8_decode('Habitaciones en esta Reserva:'), 0, 1);
    $pdf->SetFont('Helvetica', '', 10);

    $total_adults = 0;
    $total_ninos = 0;
    $total_discapacitados = 0;

    foreach ($reservations as $res) {
        $pdf->Cell(0, 6, utf8_decode('• ' . $res['room_type'] . ' (' . $res['room_id'] . ') - Piso: ' . $res['floor_name']), 0, 1);
        $total_adults += $res['adultos'];
        $total_ninos += $res['ninos'];
        $total_discapacitados += $res['discapacitados'];
    }
    $pdf->Ln(4);

    $pdf->Cell(50, 6, 'Fecha de Llegada:', 0, 0);
    $pdf->Cell(0, 6, date('d/m/Y', strtotime($reservation['checkin_date'])), 0, 1);

    $pdf->Cell(50, 6, 'Fecha de Salida:', 0, 0);
    $pdf->Cell(0, 6, date('d/m/Y', strtotime($reservation['checkout_date'])), 0, 1);

    $pdf->Cell(50, 6, 'Total Adultos:', 0, 0);
    $pdf->Cell(0, 6, $total_adults, 0, 1);

    $pdf->Cell(50, 6, 'Total Ninos:', 0, 0);
    $pdf->Cell(0, 6, $total_ninos, 0, 1);

    $pdf->Cell(50, 6, 'Total Discapacitados:', 0, 0);
    $pdf->Cell(0, 6, $total_discapacitados, 0, 1);

    $pdf->Ln(10);

    // Terms and conditions
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'Terminos y Condiciones:', 0, 1);
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->MultiCell(0, 4, utf8_decode('1. El huésped se compromete a respetar las normas del hotel.
2. Cualquier daño causado será cobrado al huésped.
3. El check-out debe realizarse antes de las 12:00 PM.
4. Se requiere identificación válida para el check-in.
5. No se permiten mascotas sin autorización previa.'), 0, 1);

    $pdf->Ln(10);

    // Signature
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->Cell(0, 6, 'Fecha de Check-in: ' . date('d/m/Y H:i'), 0, 1);
    $pdf->Ln(20);

    $pdf->Cell(80, 6, '_______________________________', 0, 0);
    $pdf->Cell(80, 6, '_______________________________', 0, 1);
    $pdf->Cell(80, 6, utf8_decode('Firma del Huésped'), 0, 0);
    $pdf->Cell(80, 6, utf8_decode('Firma del Recepcionista'), 0, 1);

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