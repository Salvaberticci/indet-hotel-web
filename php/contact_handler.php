<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $name = filter_var(trim($_POST["name"]), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $message = filter_var(trim($_POST["message"]), FILTER_SANITIZE_STRING);

    if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($message)) {
        // Set error flash message
        $_SESSION['flash_message'] = [
            'status' => 'error',
            'text' => 'Por favor, completa todos los campos correctamente.'
        ];
        header("Location: ../index.php#footer");
        exit();
    }

    // Simulate email sending
    $to = "admin@indet.com"; // Admin Email
    $subject = "Nuevo Mensaje de Contacto de: $name";
    $headers = "From: " . $email . "\r\n";
    $headers .= "Reply-To: ". $email . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    $email_body = "
    <html>
    <body>
        <h2>Nuevo Mensaje desde el Formulario de Contacto</h2>
        <p><strong>Nombre:</strong> {$name}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Mensaje:</strong></p>
        <p>{$message}</p>
    </body>
    </html>
    ";

    // In a real application, you would use a library like PHPMailer
    // For this example, we'll just pretend it was sent successfully.
    // mail($to, $subject, $email_body, $headers);

    // Set success flash message
    $_SESSION['flash_message'] = [
        'status' => 'success',
        'text' => '¡Gracias por tu mensaje! Nos pondremos en contacto contigo pronto.'
    ];

    header("Location: ../index.php#footer");
    exit();

} else {
    // Redirect if accessed directly
    header("Location: ../index.php");
    exit();
}
?>
