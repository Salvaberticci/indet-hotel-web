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

// 1. Fetch group info from the provided reservation ID
$group_sql = "SELECT user_id, checkin_date, checkout_date FROM reservations WHERE id = ?";
$group_stmt = $conn->prepare($group_sql);
$group_stmt->bind_param("i", $id);
$group_stmt->execute();
$group_info = $group_stmt->get_result()->fetch_assoc();

if (!$group_info) {
    die('Reserva no encontrada.');
}

// 2. Fetch all reservations in the same group
$sql = "SELECT r.id, ro.type as room_type, f.name as floor_name, r.adultos as adults, r.ninos, r.discapacitados, r.checkin_date, r.checkout_date, r.status, u.name, u.email, u.cedula, r.guest_name, r.guest_lastname, r.cedula as guest_cedula 
        FROM reservations r 
        JOIN users u ON r.user_id = u.id 
        JOIN rooms ro ON r.room_id = ro.id 
        LEFT JOIN floors f ON ro.floor_id = f.id
        WHERE r.user_id = ? AND r.checkin_date = ? AND r.checkout_date = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $group_info['user_id'], $group_info['checkin_date'], $group_info['checkout_date']);
$stmt->execute();
$group_result = $stmt->get_result();

$reservations = [];
$all_ids = [];
while ($row = $group_result->fetch_assoc()) {
    $reservations[] = $row;
    $all_ids[] = $row['id'];
}

if (empty($reservations)) {
    die('Reserva no encontrada.');
}

$reservation = $reservations[0];

// 3. Fetch ALL guests for the whole group
$guests = [];
$placeholders = implode(',', array_fill(0, count($all_ids), '?'));
$guests_sql = "SELECT guest_name, guest_lastname, guest_phone FROM reservation_guests WHERE reservation_id IN ($placeholders)";
$guests_stmt = $conn->prepare($guests_sql);
$guests_stmt->bind_param(str_repeat('i', count($all_ids)), ...$all_ids);
$guests_stmt->execute();
$guests_result = $guests_stmt->get_result();
while ($guest = $guests_result->fetch_assoc()) {
    $guests[] = $guest;
}

// Calculate additional details
$checkin = new DateTime($reservation['checkin_date']);
$checkout = new DateTime($reservation['checkout_date']);
$nights = $checkin->diff($checkout)->days;

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
$pdf->Cell(50, 8, utf8_decode('Número de Noches:'), 0, 0);
$pdf->Cell(0, 8, utf8_decode($nights), 0, 1);
$pdf->Ln(5);

// Rooms Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, utf8_decode('Habitaciones en esta Reserva:'), 0, 1, 'L');
$pdf->SetFont('Arial', '', 12);

foreach ($reservations as $res) {
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 8, utf8_decode('Habitación: ' . $res['room_type'] . ' - ' . ($res['floor_name'] ?? 'N/A')), 0, 1);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(33, 8, utf8_decode('Adultos: ' . $res['adults']), 0, 0);
    $pdf->Cell(33, 8, utf8_decode('Niños: ' . $res['ninos']), 0, 0);
    $pdf->Cell(33, 8, utf8_decode('Discap.: ' . $res['discapacitados']), 0, 1);
    $pdf->Ln(2);
}

$pdf->Ln(5);

// Guest Details Section
if (!empty($guests)) {
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(0, 51, 102);
    $pdf->Cell(0, 10, utf8_decode('Detalles de los Huéspedes'), 0, 1, 'L');
    $pdf->Ln(5);

    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0, 0, 0);
    foreach ($guests as $index => $guest) {
        $pdf->Cell(50, 8, utf8_decode('Huésped ' . ($index + 1) . ':'), 0, 0);
        $pdf->Cell(0, 8, utf8_decode($guest['guest_name'] . ' ' . $guest['guest_lastname']), 0, 1);
        if (!empty($guest['guest_phone'])) {
            $pdf->Cell(50, 8, utf8_decode('Teléfono:'), 0, 0);
            $pdf->Cell(0, 8, utf8_decode($guest['guest_phone']), 0, 1);
        }
        $pdf->Ln(2);
    }

    $pdf->Ln(5);
}


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