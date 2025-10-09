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
// Adjusted to use actual DB fields: rooms.capacity as adults (assuming capacity = number of people), placeholders for missing fields
$sql = "SELECT r.id, ro.type as room_type, ro.capacity as adults, r.checkin_date, r.checkout_date, r.status, u.name, u.email FROM reservations r JOIN users u ON r.user_id = u.id JOIN rooms ro ON r.room_id = ro.id WHERE r.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die('Reserva no encontrada.');
}

$reservation = $result->fetch_assoc();

// Calculate additional details
$checkin = new DateTime($reservation['checkin_date']);
$checkout = new DateTime($reservation['checkout_date']);
$nights = $checkin->diff($checkout)->days;
$price_per_night = 0; // Price removed
$subtotal = $nights * $price_per_night;
$tax_rate = 0.10; // Assuming 10% tax
$taxes = $subtotal * $tax_rate;
$total = $subtotal + $taxes;

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
$pdf->Cell(0, 10, utf8_decode('INDET Hotel '), 0, 1, 'L');
$pdf->SetXY(60, 25);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0, 0, 0);

// Confirmation number (right aligned)
$pdf->SetXY(140, 15);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(0, 51, 102);
$pdf->Cell(0, 10, utf8_decode('Confirmación: INDET-' . $reservation['id']), 0, 1, 'R');

// Line separator
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line(20, 35, 190, 35); // Moved up for compact header
$pdf->Ln(10);

// Client Details Section
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(0, 51, 102);
$pdf->Cell(0, 10, utf8_decode('Detalles del Cliente'), 0, 1, 'L');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0);
// Use table-like structure for alignment
$pdf->Cell(50, 8, utf8_decode('Nombre Completo:'), 0, 0);
$pdf->Cell(0, 8, utf8_decode($reservation['name']), 0, 1);
$pdf->Cell(50, 8, utf8_decode('Correo Electrónico:'), 0, 0);
$pdf->Cell(0, 8, utf8_decode($reservation['email']), 0, 1);
$pdf->Cell(50, 8, utf8_decode('Teléfono:'), 0, 0);
$pdf->Cell(0, 8, utf8_decode('No proporcionado'), 0, 1); // Phone not in DB, placeholder

$pdf->Ln(5);

// Reservation Details Section
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(0, 51, 102);
$pdf->Cell(0, 10, utf8_decode('Detalles de la Reserva'), 0, 1, 'L');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(50, 8, utf8_decode('Fecha de Check-in:'), 0, 0);
$pdf->Cell(0, 8, utf8_decode($reservation['checkin_date']), 0, 1);
$pdf->Cell(50, 8, utf8_decode('Fecha de Check-out:'), 0, 0);
$pdf->Cell(0, 8, utf8_decode($reservation['checkout_date']), 0, 1);
$pdf->Cell(50, 8, utf8_decode('Tipo de Habitación:'), 0, 0);
$pdf->Cell(0, 8, utf8_decode($reservation['room_type']), 0, 1);
$pdf->Cell(50, 8, utf8_decode('Número de Personas:'), 0, 0);
$pdf->Cell(0, 8, utf8_decode($reservation['adults']), 0, 1);
$pdf->Cell(50, 8, utf8_decode('Número de Noches:'), 0, 0);
$pdf->Cell(0, 8, utf8_decode($nights), 0, 1);

$pdf->Ln(5);

// Payment Summary Section
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(0, 51, 102);
$pdf->Cell(0, 10, utf8_decode('Resumen de Pago'), 0, 1, 'L');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(50, 8, utf8_decode('Subtotal:'), 0, 0);
$pdf->Cell(0, 8, utf8_decode('$' . number_format($subtotal, 2)), 0, 1);
$pdf->Cell(50, 8, utf8_decode('Impuestos:'), 0, 0);
$pdf->Cell(0, 8, utf8_decode('$' . number_format($taxes, 2)), 0, 1);
$pdf->Cell(50, 8, utf8_decode('Costo Total:'), 0, 0, 'B'); // Bold for total
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, utf8_decode('$' . number_format($total, 2)), 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 8, utf8_decode('Método de Pago:'), 0, 0);
$pdf->Cell(0, 8, utf8_decode('No especificado'), 0, 1); // Payment method not in DB, placeholder

$pdf->Ln(10);

// Footer Section
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(10);

$pdf->SetFont('Arial', 'I', 12);
$pdf->SetTextColor(0, 51, 102);
$pdf->MultiCell(0, 8, utf8_decode('¡Gracias por elegir INDET Hotel! Esperamos brindarle una experiencia deportiva inolvidable. Para más información, visite nuestro sitio web: www.indet-hotel.com o contáctenos al +123-456-7890.'), 0, 'C');

$pdf->Ln(5);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(128, 128, 128);
$pdf->Cell(0, 8, utf8_decode('Generado el ' . date('d/m/Y H:i')), 0, 0, 'L');
$pdf->Cell(0, 8, utf8_decode('INDET Hotel'), 0, 0, 'R');

$pdf->Output('D', 'reserva_INDET_' . $reservation['id'] . '.pdf');
?>