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

// Logo at top
$pdf->Image('images/logo.png', 85, 10, 40); // Centered logo
$pdf->Ln(50);

// Title
$pdf->SetFont('Arial', 'B', 24);
$pdf->SetTextColor(0, 128, 0); // Green
$pdf->Cell(0, 15, utf8_decode('¡Reserva Realizada con Éxito!'), 0, 1, 'C');
$pdf->Ln(10);

// Message
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->MultiCell(0, 8, utf8_decode('Gracias por tu reserva. Hemos recibido tu solicitud y está pendiente de confirmación por parte de nuestro equipo.'), 0, 'C');
$pdf->Ln(10);

// Details box
$pdf->SetFillColor(245, 245, 245); // Light gray background
$pdf->Rect(20, $pdf->GetY(), 170, 80, 'F'); // Background rectangle
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 12, utf8_decode('Detalles de tu Reserva'), 0, 1, 'L');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 12);
$details = [
    'Número de Confirmación:' => 'INDET-' . $reservation['id'],
    'Tipo de Habitación:' => htmlspecialchars($reservation['room_type']),
    'Fecha de Llegada:' => htmlspecialchars($reservation['checkin_date']),
    'Fecha de Salida:' => htmlspecialchars($reservation['checkout_date'])
];

foreach ($details as $label => $value) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 8, utf8_decode($label), 0, 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 8, utf8_decode($value), 0, 1);
    $pdf->Ln(2);
}

$pdf->Ln(10);

// Footer
$pdf->SetY(-30);
$pdf->SetFont('Arial', 'I', 10);
$pdf->SetTextColor(128, 128, 128);
$pdf->Cell(0, 10, 'Generado el ' . date('d/m/Y H:i'), 0, 0, 'L');
$pdf->Cell(0, 10, 'INDET - Experiencia Deportiva Inmersiva', 0, 0, 'R');

$pdf->Output('D', 'reserva_INDET_' . $reservation['id'] . '.pdf');
?>