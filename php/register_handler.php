<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $cedula_type = $_POST['cedula_type'];
    $cedula = $_POST['cedula'];
    $email = $_POST['email'];
    $password = $_POST['password'];

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

            // Redirect to the login page to show the message
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
