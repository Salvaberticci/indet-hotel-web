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

// Card background
$pdf->SetFillColor(255, 255, 255); // White
$pdf->Rect(15, 20, 180, 250, 'F'); // Card rectangle
$pdf->SetDrawColor(200, 200, 200);
$pdf->Rect(15, 20, 180, 250, 'D'); // Border

// Logo
$pdf->Image('images/logo.png', 75, 30, 60); // Centered logo
$pdf->Ln(80);

// Title
$pdf->SetFont('Arial', 'B', 20);
$pdf->SetTextColor(0, 128, 0); // Green
$pdf->Cell(0, 12, utf8_decode('¡Reserva Realizada con Éxito!'), 0, 1, 'C');
$pdf->Ln(8);

// Message
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(0, 0, 0);
$pdf->MultiCell(0, 6, utf8_decode('Gracias por tu reserva. Hemos recibido tu solicitud y está pendiente de confirmación por parte de nuestro equipo.'), 0, 'C');
$pdf->Ln(10);

// Details box
$pdf->SetFillColor(249, 249, 249); // Light gray
$pdf->Rect(25, $pdf->GetY(), 160, 100, 'F');
$pdf->SetDrawColor(220, 220, 220);
$pdf->Rect(25, $pdf->GetY(), 160, 100, 'D');

$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 12, utf8_decode('Detalles de tu Reserva'), 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 11);
$details = [
    'Número de Confirmación:' => 'INDET-' . $reservation['id'],
    'Tipo de Habitación:' => htmlspecialchars($reservation['room_type']),
    'Fecha de Llegada:' => htmlspecialchars($reservation['checkin_date']),
    'Fecha de Salida:' => htmlspecialchars($reservation['checkout_date'])
];

foreach ($details as $label => $value) {
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(60, 8, utf8_decode($label), 0, 0);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 8, utf8_decode($value), 0, 1);
    $pdf->Ln(3);
}

$pdf->Ln(20);

// Footer
$pdf->SetY(-40);
$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor(128, 128, 128);
$pdf->Cell(0, 8, 'Generado el ' . date('d/m/Y H:i'), 0, 0, 'L');
$pdf->Cell(0, 8, 'INDET - Experiencia Deportiva Inmersiva', 0, 0, 'R');

$pdf->Output('D', 'reserva_INDET_' . $reservation['id'] . '.pdf');
?>