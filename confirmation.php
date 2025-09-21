<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

// Check if reservation details are in session
if (!isset($_SESSION['last_reservation'])) {
    // If not, redirect to home
    header("Location: index.php");
    exit();
}

$reservation = $_SESSION['last_reservation'];
// Clear the session variable so it's not shown again on refresh
unset($_SESSION['last_reservation']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Reserva - INDET</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-gray-100 text-gray-800 font-poppins">
    <div class="container mx-auto p-8 flex items-center justify-center min-h-screen">
        <div class="bg-white p-10 rounded-xl shadow-2xl max-w-lg w-full text-center">
            <h1 class="text-3xl font-bold text-green-600 mb-4">¡Reserva Realizada con Éxito!</h1>
            <p class="text-gray-600 mb-6">Gracias por tu reserva. Hemos recibido tu solicitud y está pendiente de confirmación por parte de nuestro equipo.</p>
            
            <div class="bg-gray-50 p-6 rounded-lg text-left space-y-4">
                <h2 class="text-xl font-bold border-b pb-2 mb-4">Detalles de tu Reserva</h2>
                <div>
                    <p class="font-semibold">Número de Confirmación:</p>
                    <p class="text-lg font-mono bg-gray-200 px-2 py-1 rounded inline-block">INDET-<?php echo htmlspecialchars($reservation['id']); ?></p>
                </div>
                <div>
                    <p class="font-semibold">Tipo de Habitación:</p>
                    <p class="capitalize"><?php echo htmlspecialchars($reservation['room_type']); ?></p>
                </div>
                <div>
                    <p class="font-semibold">Fecha de Llegada:</p>
                    <p><?php echo htmlspecialchars($reservation['checkin']); ?></p>
                </div>
                <div>
                    <p class="font-semibold">Fecha de Salida:</p>
                    <p><?php echo htmlspecialchars($reservation['checkout']); ?></p>
                </div>
            </div>

            <a href="index.php" class="mt-8 inline-block bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg transition-transform hover:scale-105">Volver a la Página Principal</a>
        </div>
    </div>
</body>
</html>
