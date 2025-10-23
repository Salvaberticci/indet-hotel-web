<?php
session_start();
include 'db.php';

// Admin access check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Add Item
if (isset($_POST['add_item'])) {
    $floor_id = $_POST['floor_id'];
    $item_name = $_POST['item_name'];
    $quantity = $_POST['quantity'];
    $description = $_POST['description'];

    $sql = "INSERT INTO floor_inventory (floor_id, item_name, quantity, description) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isis", $floor_id, $item_name, $quantity, $description);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Item agregado exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al agregar el item: ' . $stmt->error];
    }
    header("Location: ../admin_inventory.php?floor_id=$floor_id");
    exit();
}

// Update Item
if (isset($_POST['update_item'])) {
    $id = $_POST['id'];
    $floor_id = $_POST['floor_id'];
    $item_name = $_POST['item_name'];
    $quantity = $_POST['quantity'];
    $description = $_POST['description'];

    $sql = "UPDATE floor_inventory SET item_name = ?, quantity = ?, description = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisi", $item_name, $quantity, $description, $id);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Item actualizado exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al actualizar el item: ' . $stmt->error];
    }
    header("Location: ../admin_inventory.php?floor_id=$floor_id");
    exit();
}

// Delete Item
if (isset($_GET['delete_item'])) {
    $id = $_GET['delete_item'];
    $floor_id = $_GET['floor_id'];

    $sql = "DELETE FROM floor_inventory WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Item eliminado exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al eliminar el item: ' . $stmt->error];
    }
    header("Location: ../admin_inventory.php?floor_id=$floor_id");
    exit();
}

// Add Floor
if (isset($_POST['add_floor'])) {
    $floor_number = $_POST['floor_number'];
    $name = $_POST['name'];
    $description = $_POST['description'];

    $sql = "INSERT INTO floors (floor_number, name, description) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $floor_number, $name, $description);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Piso agregado exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al agregar el piso: ' . $stmt->error];
    }
    header("Location: ../admin_inventory.php");
    exit();
}

// Update Floor
if (isset($_POST['update_floor'])) {
    $id = $_POST['id'];
    $floor_number = $_POST['floor_number'];
    $name = $_POST['name'];
    $description = $_POST['description'];

    // Check if floor_number already exists for another floor
    $check_sql = "SELECT id FROM floors WHERE floor_number = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $floor_number, $id);
    $check_stmt->execute();
    $existing = $check_stmt->get_result();

    if ($existing->num_rows > 0) {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'El número de piso ya existe. Por favor, elige un número diferente.'];
        header("Location: ../admin_inventory.php");
        exit();
    }

    $sql = "UPDATE floors SET floor_number = ?, name = ?, description = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $floor_number, $name, $description, $id);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Piso actualizado exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al actualizar el piso: ' . $stmt->error];
    }
    header("Location: ../admin_inventory.php");
    exit();
}

// Delete Floor
if (isset($_GET['delete_floor'])) {
    $id = $_GET['delete_floor'];

    // Check if floor has rooms or inventory
    $check_rooms = $conn->prepare("SELECT COUNT(*) as count FROM rooms WHERE floor_id = ?");
    $check_rooms->bind_param("i", $id);
    $check_rooms->execute();
    $rooms_count = $check_rooms->get_result()->fetch_assoc()['count'];

    $check_inventory = $conn->prepare("SELECT COUNT(*) as count FROM floor_inventory WHERE floor_id = ?");
    $check_inventory->bind_param("i", $id);
    $check_inventory->execute();
    $inventory_count = $check_inventory->get_result()->fetch_assoc()['count'];

    if ($rooms_count > 0 || $inventory_count > 0) {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'No se puede eliminar el piso porque tiene habitaciones o inventario asociado.'];
    } else {
        $sql = "DELETE FROM floors WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Piso eliminado exitosamente.'];
        } else {
            $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al eliminar el piso: ' . $stmt->error];
        }
    }
    header("Location: ../admin_inventory.php");
    exit();
}
?>