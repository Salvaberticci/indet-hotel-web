<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Generate a unique verification token
    $verification_token = bin2hex(random_bytes(50));

    $sql = "INSERT INTO users (name, email, password, role, verification_token) VALUES (?, ?, ?, 'client', ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $email, $hashed_password, $verification_token);

    if ($stmt->execute()) {
        // SIMULATE SENDING EMAIL: In a real application, you would send an email here.
        // For this project, we will show a flash message with the verification link.
        $verification_link = "http://localhost/indet-hotel-web/php/verify.php?token=" . $verification_token;

        $_SESSION['flash_message'] = [
            'status' => 'success',
            'text' => '¡Registro casi completo! Por favor, verifica tu correo electrónico. <a href="' . $verification_link . '" class="font-bold underline">Verificar ahora</a>'
        ];

        // Redirect to the login page to show the message
        header("Location: ../login.php");
        exit();
    } else {
        // Handle errors, e.g., duplicate email
        $_SESSION['flash_message'] = [
            'status' => 'error',
            'text' => 'Error en el registro. Es posible que el correo electrónico ya esté en uso.'
        ];
        header("Location: ../register.php");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
