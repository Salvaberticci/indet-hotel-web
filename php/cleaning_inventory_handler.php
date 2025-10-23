<?php
session_start();
include 'db.php';

// Admin access check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// No longer need station creation - single cleaning room concept

// Add Item
if (isset($_POST['add_item'])) {
    $item_name = $_POST['item_name'];
    $quantity = (int)$_POST['quantity'];
    $description = $_POST['description'];

    $sql = "INSERT INTO cleaning_inventory (item_name, quantity, description) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sis", $item_name, $quantity, $description);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Producto de limpieza agregado exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al agregar el producto: ' . $stmt->error];
    }
    header("Location: ../admin_cleaning_inventory.php");
    exit();
}

// Update Item
if (isset($_POST['update_item'])) {
    $id = $_POST['id'];
    $item_name = $_POST['item_name'];
    $quantity = (int)$_POST['quantity'];
    $description = $_POST['description'];

    $sql = "UPDATE cleaning_inventory SET item_name = ?, quantity = ?, description = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisi", $item_name, $quantity, $description, $id);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Item de faena actualizado exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al actualizar el item: ' . $stmt->error];
    }

    header("Location: ../admin_cleaning_inventory.php");
    exit();
}

// Update Quantity (AJAX)
if (isset($_POST['update_quantity'])) {
    $item_id = $_POST['item_id'];
    $quantity = (int)$_POST['quantity'];

    $sql = "UPDATE cleaning_inventory SET quantity = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $quantity, $item_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    exit();
}

// Delete Item
if (isset($_GET['delete_item'])) {
    $id = $_GET['delete_item'];

    $sql = "DELETE FROM cleaning_inventory WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Producto de limpieza eliminado exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al eliminar el producto: ' . $stmt->error];
    }

    header("Location: ../admin_cleaning_inventory.php");
    exit();
}

$conn->close();
?>