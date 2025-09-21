<?php
session_start();
include 'db.php';

// Admin access check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Add User
if (isset($_POST['add_user'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Check if email already exists
    $check_sql = "SELECT id FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'El email ya está registrado.'];
    } else {
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        if ($stmt->execute()) {
            $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Usuario agregado exitosamente.'];
        } else {
            $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al agregar el usuario.'];
        }
    }
    header("Location: ../admin.php#users-section");
    exit();
}

// Update User
if (isset($_POST['update_user'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Prevent admin from changing their own role to non-admin
    if ($id == $_SESSION['user_id'] && $role != 'admin') {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'No puedes cambiar tu propio rol a no-administrador.'];
        header("Location: ../admin.php#users-section");
        exit();
    }

    // Check if email is taken by another user
    $check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $email, $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'El email ya está registrado por otro usuario.'];
    } else {
        $sql = "UPDATE users SET name=?, email=?, role=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $email, $role, $id);
        if ($stmt->execute()) {
            $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Usuario actualizado exitosamente.'];
        } else {
            $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al actualizar el usuario.'];
        }
    }
    header("Location: ../admin.php#users-section");
    exit();
}

// Update User Role
if (isset($_POST['update_user_role'])) {
    $user_id = $_POST['user_id'];
    $role = $_POST['role'];

    // Prevent admin from changing their own role to non-admin
    if ($user_id == $_SESSION['user_id'] && $role != 'admin') {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'No puedes cambiar tu propio rol a no-administrador.'];
        header("Location: ../admin.php");
        exit();
    }

    $sql = "UPDATE users SET role = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $role, $user_id);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Rol de usuario actualizado exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al actualizar el rol del usuario.'];
    }
    header("Location: ../admin.php");
    exit();
}

// Delete User
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];

    // Prevent admin from deleting themselves
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'No puedes eliminar tu propia cuenta de administrador.'];
        header("Location: ../admin.php");
        exit();
    }

    // Check for associated reservations
    $check_sql = "SELECT id FROM reservations WHERE user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'No se puede eliminar el usuario porque tiene reservas asociadas.'];
    } else {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            $_SESSION['flash_message'] = ['status' => 'success', 'text' => 'Usuario eliminado exitosamente.'];
        } else {
            $_SESSION['flash_message'] = ['status' => 'error', 'text' => 'Error al eliminar el usuario.'];
        }
    }
    header("Location: ../admin.php");
    exit();
}
?>
