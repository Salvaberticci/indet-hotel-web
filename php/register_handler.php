<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $cedula_type = $_POST['cedula_type'];
    $cedula = trim($_POST['cedula']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate name: only letters and spaces
    if (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $_SESSION['flash_message'] = [
            'status' => 'error',
            'text' => 'El nombre solo puede contener letras y espacios.'
        ];
        header("Location: ../register.php");
        exit();
    }

    // Validate cedula: only numbers
    if (!preg_match("/^[0-9]+$/", $cedula)) {
        $_SESSION['flash_message'] = [
            'status' => 'error',
            'text' => 'La cédula solo puede contener números.'
        ];
        header("Location: ../register.php");
        exit();
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO users (name, cedula_type, cedula, email, password, role, is_verified) VALUES (?, ?, ?, ?, ?, 'client', 1)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $name, $cedula_type, $cedula, $email, $hashed_password);

        if ($stmt->execute()) {
            $_SESSION['flash_message'] = [
                'status' => 'success',
                'text' => '¡Registro exitoso! Ahora puedes iniciar sesión.'
            ];

            // Redirect to the login page to show the message without logging in
            header("Location: ../login.php");
            exit();
        }
    } catch (mysqli_sql_exception $e) {
        // Handle specific database errors
        if ($e->getCode() == 1062) { // Duplicate entry error
            $_SESSION['flash_message'] = [
                'status' => 'error',
                'text' => 'El correo electrónico ya está registrado. Por favor, utiliza otro correo electrónico.'
            ];
        } else {
            $_SESSION['flash_message'] = [
                'status' => 'error',
                'text' => 'Error en el registro. Por favor, inténtalo de nuevo.'
            ];
        }
        header("Location: ../register.php");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
