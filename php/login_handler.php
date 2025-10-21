<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cedula = $_POST['cedula'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, name, password, role, is_verified FROM users WHERE email = ? AND cedula = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $cedula);
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
