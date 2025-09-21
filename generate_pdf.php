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
$pdf->SetFont('Arial', '', 16);
$pdf->Cell(0, 10, 'Confirmacion de Reserva - INDET', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Numero de Confirmacion: INDET-' . $reservation['id'], 0, 1);
$pdf->Cell(0, 10, 'Nombre del Huesped: ' . htmlspecialchars($reservation['name']), 0, 1);
$pdf->Cell(0, 10, 'Email: ' . htmlspecialchars($reservation['email']), 0, 1);
$pdf->Cell(0, 10, 'Tipo de Habitacion: ' . htmlspecialchars($reservation['room_type']), 0, 1);
$pdf->Cell(0, 10, 'Fecha de Llegada: ' . htmlspecialchars($reservation['checkin_date']), 0, 1);
$pdf->Cell(0, 10, 'Fecha de Salida: ' . htmlspecialchars($reservation['checkout_date']), 0, 1);
$pdf->Cell(0, 10, 'Estado: ' . htmlspecialchars($reservation['status']), 0, 1);

$pdf->Output('D', 'reserva_INDET_' . $reservation['id'] . '.pdf');
?>