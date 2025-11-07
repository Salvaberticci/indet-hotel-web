<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include 'php/db.php';

// Include FPDF library via composer
require('vendor/autoload.php');

// Get current month
$current_month = date('m');
$current_year = date('Y');
$first_day = date('Y-m-01');
$last_day = date('Y-m-t');

// Fetch reservations for current month
$sql = "SELECT r.id, ro.type as room_type, r.adultos as adults, r.ninos, r.discapacitados, r.checkin_date, r.checkout_date, r.status, u.name, u.email, u.cedula, r.guest_name, r.guest_lastname, r.cedula as guest_cedula
        FROM reservations r
        JOIN users u ON r.user_id = u.id
        JOIN rooms ro ON r.room_id = ro.id
        WHERE (DATE(r.checkin_date) BETWEEN ? AND ?) OR (DATE(r.checkout_date) BETWEEN ? AND ?)
        ORDER BY r.checkin_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $first_day, $last_day, $first_day, $last_day);
$stmt->execute();
$result = $stmt->get_result();

$reservations = [];
while ($row = $result->fetch_assoc()) {
    $reservations[] = $row;
}

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();

// Set margins
$pdf->SetMargins(20, 20, 20);

// Set default font
$pdf->SetFont('Arial', '', 12);

// Header Section
// Logo (left aligned)
$pdf->Image('images/logo.png', 20, 10, 10); // Smaller logo

// Hotel name and address (center/right)
$pdf->SetXY(60, 15);
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(0, 51, 102); // Dark blue
$pdf->Cell(0, 10, utf8_decode('INDET Hotel - Reporte Mensual'), 0, 1, 'L');
$pdf->SetXY(60, 25);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0, 0, 0);

// Month (right aligned)
$month_names = [
    '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
    '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
    '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
];
$month_name = $month_names[$current_month] ?? $current_month;

$pdf->SetXY(140, 15);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(0, 51, 102);
$pdf->Cell(0, 10, utf8_decode('Mes: ' . $month_name . ' ' . $current_year), 0, 1, 'R');

// Line separator
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line(20, 35, 190, 35);
$pdf->Ln(10);

// Title
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(0, 51, 102);
$pdf->Cell(0, 10, utf8_decode('Reservas del Mes'), 0, 1, 'L');
$pdf->Ln(5);

// Check if there are reservations
if (empty($reservations)) {
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 10, utf8_decode('No hay reservas para este mes.'), 0, 1, 'L');
} else {
    // Table headers
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(20, 8, utf8_decode('ID'), 1, 0, 'C', true);
    $pdf->Cell(40, 8, utf8_decode('Cliente'), 1, 0, 'C', true);
    $pdf->Cell(30, 8, utf8_decode('Habitación'), 1, 0, 'C', true);
    $pdf->Cell(30, 8, utf8_decode('Check-in'), 1, 0, 'C', true);
    $pdf->Cell(30, 8, utf8_decode('Check-out'), 1, 0, 'C', true);
    $pdf->Cell(25, 8, utf8_decode('Estado'), 1, 1, 'C', true);

    // Table data
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetFillColor(255, 255, 255);
    foreach ($reservations as $reservation) {
        $status_classes = [
            'pending' => 'Pendiente',
            'confirmed' => 'Confirmada',
            'cancelled' => 'Cancelada'
        ];
        $status_text = $status_classes[$reservation['status']] ?? ucfirst($reservation['status']);

        $pdf->Cell(20, 6, $reservation['id'], 1, 0, 'C');
        $pdf->Cell(40, 6, utf8_decode(substr($reservation['name'], 0, 20)), 1, 0, 'L');
        $pdf->Cell(30, 6, utf8_decode($reservation['room_type']), 1, 0, 'C');
        $pdf->Cell(30, 6, $reservation['checkin_date'], 1, 0, 'C');
        $pdf->Cell(30, 6, $reservation['checkout_date'], 1, 0, 'C');
        $pdf->Cell(25, 6, utf8_decode($status_text), 1, 1, 'C');
    }

    // Summary
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, utf8_decode('Total de Reservas: ' . count($reservations)), 0, 1, 'L');
}

$pdf->Ln(10);

// Footer Section
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(10);

$pdf->SetFont('Arial', 'I', 12);
$pdf->SetTextColor(0, 51, 102);
$pdf->MultiCell(0, 8, utf8_decode('INDET Hotel - Sistema de Administración de Reservas'), 0, 'C');

$pdf->Ln(5);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(128, 128, 128);
$pdf->Cell(0, 8, utf8_decode('Generado el ' . date('d/m/Y H:i')), 0, 0, 'L');
$pdf->Cell(0, 8, utf8_decode('INDET Hotel'), 0, 0, 'R');

$pdf->Output('D', 'reporte_mensual_INDET_' . $current_year . '-' . $current_month . '.pdf');
?>