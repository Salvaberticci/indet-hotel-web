<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    // For now, we'll just display a success message.
    // In a real application, you would send an email or save the message to the database.
    echo "Gracias por tu mensaje, " . htmlspecialchars($name) . ". Nos pondremos en contacto contigo pronto.";
}
?>
