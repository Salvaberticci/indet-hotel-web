<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cedula_type = $_POST['cedula_type'];
    $cedula = trim($_POST['cedula']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate cedula: only numbers
    if (!preg_match("/^[0-9]+$/", $cedula)) {
        $_SESSION['flash_message'] = [
            'status' => 'error',
            'text' => 'El número de cédula solo puede contener números.'
        ];
        header("Location: ../login.php");
        exit();
    }

    $sql = "SELECT id, name, password, role, is_verified FROM users WHERE email = ? AND cedula = ? AND cedula_type = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $email, $cedula, $cedula_type);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Password is correct, start session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            $message_text = ($user['role'] == 'maintenance') ? '¡Inicio de sesión exitoso como mantenimiento!' : '¡Inicio de sesión exitoso!';
            $_SESSION['flash_message'] = [
                'status' => 'success',
                'text' => $message_text
            ];
            if ($user['role'] == 'admin' || $user['role'] == 'maintenance') {
                header("Location: ../admin.php");
            } else {
                header("Location: ../reservar.php");
            }
            exit();
        } else {
            // Incorrect password
            $_SESSION['flash_message'] = [
                'status' => 'error',
                'text' => 'Credenciales inválidas. Por favor, inténtalo de nuevo.'
            ];
            header("Location: ../login.php");
            exit();
        }
    } else {
        // User not found
        $_SESSION['flash_message'] = [
            'status' => 'error',
            'text' => 'Credenciales inválidas. Por favor, inténtalo de nuevo.'
        ];
        header("Location: ../login.php");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
