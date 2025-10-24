<?php
require_once 'fpdf/fpdf.php';
include 'db.php';

function generateGeneralReport() {
    global $conn;

    // Create PDF
    class PDF extends FPDF {
        function AddFont($family, $style='', $file='', $dir='') {
            return;
        }
    
        function SetFont($family, $style='', $size=0) {
            if ($family !== 'Helvetica' && $family !== 'Courier' && $family !== 'Times') {
                $family = 'Helvetica';
            }
            parent::SetFont($family, $style, $size);
        }
    }
    
    $pdf = new PDF('P', 'mm', 'A4');
    $pdf->SetFont('Helvetica', '', 10); // Set default font
    $pdf->AddPage();
    $pdf->SetFont('Helvetica', 'B', 16);

    // Title
    $pdf->Cell(0, 10, 'Reporte General de Check-in/Check-out', 0, 1, 'C');
    $pdf->Ln(10);

    // Check-ins Section
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Check-ins', 0, 1);
    $pdf->SetFont('Arial', '', 10);

    $checkin_sql = "SELECT r.id, r.guest_name, r.guest_lastname, r.cedula, r.checkin_date, r.checkout_date, r.status,
                           rm.type as room_type, f.name as floor_name, u.name as user_name
                    FROM reservations r
                    JOIN rooms rm ON r.room_id = rm.id
                    JOIN floors f ON rm.floor_id = f.id
                    JOIN users u ON r.user_id = u.id
                    WHERE r.status IN ('confirmed', 'pending')
                    ORDER BY r.checkin_date ASC";

    $checkin_result = $conn->query($checkin_sql);

    if ($checkin_result->num_rows > 0) {
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->Cell(40, 8, 'Huesped', 1);
        $pdf->Cell(25, 8, 'Cedula', 1);
        $pdf->Cell(30, 8, 'Habitacion', 1);
        $pdf->Cell(25, 8, 'Check-in', 1);
        $pdf->Cell(25, 8, 'Check-out', 1);
        $pdf->Cell(20, 8, 'Estado', 1);
        $pdf->Ln();

        while ($row = $checkin_result->fetch_assoc()) {
            $pdf->Cell(40, 6, $row['guest_name'] . ' ' . $row['guest_lastname'], 1);
            $pdf->Cell(25, 6, $row['cedula'], 1);
            $pdf->Cell(30, 6, $row['room_type'] . ' - ' . $row['floor_name'], 1);
            $pdf->Cell(25, 6, date('d/m/Y', strtotime($row['checkin_date'])), 1);
            $pdf->Cell(25, 6, date('d/m/Y', strtotime($row['checkout_date'])), 1);
            $pdf->Cell(20, 6, $row['status'] == 'confirmed' ? 'Confirmado' : 'Pendiente', 1);
            $pdf->Ln();
        }
    } else {
        $pdf->Cell(0, 8, 'No hay check-ins registrados.', 1, 1, 'C');
    }

    $pdf->Ln(10);

    // Check-outs Section
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Check-outs', 0, 1);
    $pdf->SetFont('Arial', '', 10);

    $checkout_sql = "SELECT r.id, r.guest_name, r.guest_lastname, r.cedula, r.checkin_date, r.checkout_date, r.status,
                            rm.type as room_type, f.name as floor_name, u.name as user_name
                     FROM reservations r
                     JOIN rooms rm ON r.room_id = rm.id
                     JOIN floors f ON rm.floor_id = f.id
                     JOIN users u ON r.user_id = u.id
                     WHERE r.status IN ('confirmed', 'completed')
                     ORDER BY r.checkout_date ASC";

    $checkout_result = $conn->query($checkout_sql);

    if ($checkout_result->num_rows > 0) {
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->Cell(40, 8, 'Huesped', 1);
        $pdf->Cell(25, 8, 'Cedula', 1);
        $pdf->Cell(30, 8, 'Habitacion', 1);
        $pdf->Cell(25, 8, 'Check-in', 1);
        $pdf->Cell(25, 8, 'Check-out', 1);
        $pdf->Cell(20, 8, 'Estado', 1);
        $pdf->Ln();

        while ($row = $checkout_result->fetch_assoc()) {
            $pdf->Cell(40, 6, $row['guest_name'] . ' ' . $row['guest_lastname'], 1);
            $pdf->Cell(25, 6, $row['cedula'], 1);
            $pdf->Cell(30, 6, $row['room_type'] . ' - ' . $row['floor_name'], 1);
            $pdf->Cell(25, 6, date('d/m/Y', strtotime($row['checkin_date'])), 1);
            $pdf->Cell(25, 6, date('d/m/Y', strtotime($row['checkout_date'])), 1);
            $pdf->Cell(20, 6, $row['status'] == 'completed' ? 'Completado' : 'Confirmado', 1);
            $pdf->Ln();
        }
    } else {
        $pdf->Cell(0, 8, 'No hay check-outs registrados.', 1, 1, 'C');
    }

    // Generate unique filename
    $filename = 'reporte_general_' . date('Y-m-d_H-i-s') . '.pdf';
    $filepath = 'receipts/' . $filename;

    // Save PDF
    $pdf->Output($filepath, 'F');

    return $filepath;
}
?>