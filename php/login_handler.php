<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, name, password, role, is_verified FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Check if the user is verified (admins don't need verification)
            if ($user['is_verified'] == 1 || $user['role'] == 'admin') {
                // Password is correct, start session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                $_SESSION['flash_message'] = [
                    'status' => 'success',
                    'text' => '¡Inicio de sesión exitoso!'
                ];
                if ($user['role'] == 'admin') {
                    header("Location: ../admin.php");
                } else {
                    header("Location: ../index.php");
                }
                exit();
            } else {
                // User is not verified
                $_SESSION['flash_message'] = [
                    'status' => 'error',
                    'text' => 'Tu cuenta no ha sido verificada. Por favor, revisa tu correo electrónico.'
                ];
                header("Location: ../login.php");
                exit();
            }
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
