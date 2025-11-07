<?php
header('Content-Type: application/json');
session_start();
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $guest_id = (int)$_POST['guest_id'];
    $guest_name = trim($_POST['guest_name']);
    $guest_lastname = trim($_POST['guest_lastname']);
    $guest_phone = trim($_POST['guest_phone']);

    // Validate required fields
    if (empty($guest_name)) {
        echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
        exit();
    }

    // Validate phone format (optional, but if provided should be reasonable)
    if (!empty($guest_phone) && !preg_match('/^[0-9+\-\s()]{7,20}$/', $guest_phone)) {
        echo json_encode(['success' => false, 'message' => 'Formato de teléfono inválido']);
        exit();
    }

    // Update guest in database
    $sql = "UPDATE reservation_guests SET guest_name = ?, guest_lastname = ?, guest_phone = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $guest_name, $guest_lastname, $guest_phone, $guest_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Huésped actualizado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar huésped: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}

$conn->close();
?>