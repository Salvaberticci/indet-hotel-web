<?php
session_start();
include 'db.php';

// Admin access check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Create Cleaning Station
if (isset($_POST['create_station'])) {
    $station_id = $_POST['station_id'];
    $station_name = $_POST['station_name'];
    $floor_id = $_POST['floor_id'];

    // Check if station_id already exists
    $check_sql = "SELECT id FROM cleaning_stations WHERE station_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $station_id);
    $check_stmt->execute();
    $existing = $check_stmt->get_result();

    if ($existing->num_rows > 0) {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'El ID del cuarto de faenas ya existe. Por favor, elige un ID diferente.'];
        header("Location: ../admin_cleaning_inventory.php");
        exit();
    }

    $sql = "INSERT INTO cleaning_stations (station_id, station_name, floor_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $station_id, $station_name, $floor_id);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Cuarto de faenas creado exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al crear el cuarto de faenas: ' . $stmt->error];
    }
    header("Location: ../admin_cleaning_inventory.php");
    exit();
}

// Add Item
if (isset($_POST['add_item'])) {
    if (isset($_POST['station_id'])) {
        // Adding to specific station
        $station_id = $_POST['station_id'];
        $item_name = $_POST['item_name'];
        $quantity = (int)$_POST['quantity'];
        $description = $_POST['description'];

        $sql = "INSERT INTO cleaning_inventory (station_id, item_name, quantity, description) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isis", $station_id, $item_name, $quantity, $description);

        if ($stmt->execute()) {
            $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Item de faena agregado exitosamente.'];
        } else {
            $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al agregar el item: ' . $stmt->error];
        }
        header("Location: ../admin_cleaning_inventory.php?station_id=" . $station_id);
    } else {
        // Legacy floor-based adding (for backward compatibility)
        $floor_id = $_POST['floor_id'];
        $item_name = $_POST['item_name'];
        $quantity = (int)$_POST['quantity'];
        $description = $_POST['description'];

        $sql = "INSERT INTO cleaning_inventory (floor_id, item_name, quantity, description) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isis", $floor_id, $item_name, $quantity, $description);

        if ($stmt->execute()) {
            $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Item de faena agregado exitosamente.'];
        } else {
            $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al agregar el item: ' . $stmt->error];
        }
        header("Location: ../admin_cleaning_inventory.php?floor_id=" . $floor_id);
    }
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

    // Get station_id for redirect
    $station_sql = "SELECT station_id FROM cleaning_inventory WHERE id = ?";
    $station_stmt = $conn->prepare($station_sql);
    $station_stmt->bind_param("i", $id);
    $station_stmt->execute();
    $station_result = $station_stmt->get_result();
    $station = $station_result->fetch_assoc();

    if ($station && $station['station_id']) {
        header("Location: ../admin_cleaning_inventory.php?station_id=" . $station['station_id']);
    } else {
        header("Location: ../admin_cleaning_inventory.php");
    }
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

    // Get station_id for redirect
    $station_sql = "SELECT station_id FROM cleaning_inventory WHERE id = ?";
    $station_stmt = $conn->prepare($station_sql);
    $station_stmt->bind_param("i", $id);
    $station_stmt->execute();
    $station_result = $station_stmt->get_result();
    $station = $station_result->fetch_assoc();

    $sql = "DELETE FROM cleaning_inventory WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Item de faena eliminado exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al eliminar el item: ' . $stmt->error];
    }

    if ($station && $station['station_id']) {
        header("Location: ../admin_cleaning_inventory.php?station_id=" . $station['station_id']);
    } else {
        header("Location: ../admin_cleaning_inventory.php");
    }
    exit();
}

$conn->close();
?>