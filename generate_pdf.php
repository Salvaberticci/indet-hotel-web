<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include 'php/db.php';

// Include FPDF library via composer
require('vendor/autoload.php');

if (!isset($_GET['id'])) {
    die('ID de reserva no proporcionado.');
}

$id = intval($_GET['id']);

// Fetch reservation details
$sql = "SELECT r.id, ro.type as room_type, r.checkin_date, r.checkout_date, r.status, u.name, u.email FROM reservations r JOIN users u ON r.user_id = u.id JOIN rooms ro ON r.room_id = ro.id WHERE r.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die('Reserva no encontrada.');
}

$reservation = $result->fetch_assoc();

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();

// Set margins
$pdf->SetMargins(20, 20, 20);

// Header
$pdf->Image('images/logo.png', 20, 10, 30); // Logo
$pdf->SetFont('Arial', 'B', 20);
$pdf->Cell(0, 10, 'INDET - Confirmacion de Reserva', 0, 1, 'C');
$pdf->Ln(10);

// Reservation ID
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Numero de Confirmacion: INDET-' . $reservation['id'], 0, 1, 'C');
$pdf->Ln(5);

// Details table
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(0, 100, 0); // Green header
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(60, 10, 'Campo', 1, 0, 'C', true);
$pdf->Cell(120, 10, 'Detalle', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFillColor(240, 240, 240); // Light gray rows

$fields = [
    'Nombre del Huesped' => $reservation['name'],
    'Email' => $reservation['email'],
    'Tipo de Habitacion' => $reservation['room_type'],
    'Fecha de Llegada' => $reservation['checkin_date'],
    'Fecha de Salida' => $reservation['checkout_date'],
    'Estado' => $reservation['status']
];

$fill = false;
foreach ($fields as $field => $value) {
    $pdf->Cell(60, 10, $field, 1, 0, 'L', $fill);
    $pdf->Cell(120, 10, htmlspecialchars($value), 1, 1, 'L', $fill);
    $fill = !$fill;
}

$pdf->Ln(10);

// Footer
$pdf->SetY(-30);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, 'Generado el ' . date('d/m/Y H:i'), 0, 0, 'L');
$pdf->Cell(0, 10, 'Pagina ' . $pdf->PageNo(), 0, 0, 'R');

$pdf->Output('D', 'reserva_INDET_' . $reservation['id'] . '.pdf');
?>