<?php
session_start();
include 'db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Find the user with the given token
    $sql = "SELECT id FROM users WHERE verification_token = ? AND is_verified = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // User found, update the verification status
        $user = $result->fetch_assoc();
        $user_id = $user['id'];

        $update_sql = "UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $user_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['flash_message'] = [
                'status' => 'success',
                'text' => '¡Correo verificado exitosamente! Ahora puedes iniciar sesión.'
            ];
        } else {
            $_SESSION['flash_message'] = [
                'status' => 'error',
                'text' => 'Error al verificar la cuenta. Por favor, intenta de nuevo.'
            ];
        }
        $update_stmt->close();
    } else {
        // Token is invalid or already used
        $_SESSION['flash_message'] = [
            'status' => 'error',
            'text' => 'El enlace de verificación no es válido o ya ha sido utilizado.'
        ];
    }
    $stmt->close();
} else {
    $_SESSION['flash_message'] = [
        'status' => 'error',
        'text' => 'No se proporcionó un token de verificación.'
    ];
}

$conn->close();
// Redirect to the login page to show the message
header("Location: ../login.php");
exit();
?>
