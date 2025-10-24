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
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- AOS (Animate on Scroll) -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-gray-900 text-white font-poppins">

    <?php
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        $status = $message['status'];
        $text = $message['text'];
        $icon = $status === 'success' ? 'fa-check-circle' : 'fa-times-circle';
        echo "<div class='notification $status'><i class='fas $icon'></i> $text</div>";
    }
    ?>

    <!-- Background Elements -->
    <div class="fixed top-0 left-0 w-full h-full bg-cover bg-center z-0" style="background-image: url('images/hero-bg.jpg');"></div>
    <div class="fixed top-0 left-0 w-full h-full bg-black/60 z-10"></div>

    <!-- Header -->
    <header class="relative">
        <!-- Navigation -->
        <nav id="navbar" class="fixed top-0 left-0 w-full p-6 z-40 transition-all duration-300">
            <div class="container mx-auto grid grid-cols-3 items-center">
                <div class="justify-self-start">
                    <img src="images/logo.png" alt="INDET Logo" class="w-24 logo">
                </div>
                <div class="hidden md:flex items-center space-x-4 nav-link-container justify-self-center">
                    <a href="index.php" class="nav-button">Inicio</a>
                    <a href="hotel_info.php" class="nav-button">Nuestro Hotel</a>
                    <a href="#rooms" class="nav-button">Habitaciones</a>
                    <a href="reservar.php" class="nav-button">Reservacion</a>
                    <a href="events.php" class="nav-button">Eventos</a>
                    <a href="faq.php" class="nav-button">FAQ</a>
                    <a href="#footer" class="nav-button">Contactos</a>
                </div>
                <div class="flex items-center space-x-4 justify-self-end">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="flex flex-col items-center">
                            <?php if ($_SESSION['user_role'] == 'admin'): ?>
                                <a href="admin.php" class="login-button">
                                    <span>Panel Admin</span>
                                    <i class="fas fa-cog"></i>
                                </a>
                            <?php endif; ?>
                            <a href="php/logout.php" class="login-button">
                                <span>Logout</span>
                                <i class="fas fa-sign-out-alt"></i>
                            </a>
                            <span class="text-white font-semibold text-sm mt-1"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="login-button">
                            <span>Login</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="relative z-30 bg-transparent pt-24">
        <div class="container mx-auto p-8 flex items-center justify-center min-h-screen">
        <div class="bg-gray-800/90 backdrop-blur-sm p-10 rounded-xl shadow-2xl max-w-lg w-full text-center" data-aos="fade-up">
            <h1 class="text-3xl font-bold text-green-400 mb-4">¡Reserva Realizada con Éxito!</h1>
            <p class="text-gray-300 mb-6">Gracias por tu reserva. Hemos recibido tu solicitud y está pendiente de confirmación por parte de nuestro equipo.</p>

            <div class="bg-gray-700/50 p-6 rounded-lg text-left space-y-4">
                <h2 class="text-xl font-bold border-b border-gray-600 pb-2 mb-4 text-white">Detalles de tu Reserva</h2>
                <div>
                    <p class="font-semibold text-white">Número de Confirmación:</p>
                    <p class="text-lg font-mono bg-gray-600 px-2 py-1 rounded inline-block text-white">INDET-<?php echo htmlspecialchars($reservation['id']); ?></p>
                </div>
                <div>
                    <p class="font-semibold text-white">Tipo de Habitación:</p>
                    <p class="capitalize text-gray-300"><?php echo htmlspecialchars($reservation['room_type']); ?></p>
                </div>
                <div>
                    <p class="font-semibold text-white">Fecha de Llegada:</p>
                    <p class="text-gray-300"><?php echo htmlspecialchars($reservation['checkin']); ?></p>
                </div>
                <div>
                    <p class="font-semibold text-white">Fecha de Salida:</p>
                    <p class="text-gray-300"><?php echo htmlspecialchars($reservation['checkout']); ?></p>
                </div>
            </div>

            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="reservar.php" class="inline-block action-button">Volver a Reservar</a>
                <a href="generate_pdf.php?id=<?php echo htmlspecialchars($reservation['id']); ?>" class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition-transform hover:scale-105">Generar PDF</a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer id="footer" class="footer-bg text-white py-20 relative z-30">
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-2 gap-12 items-center px-6">
            <div class="text-center md:text-left">
                <h3 class="text-3xl font-bold mb-4">Contacto</h3>
                <p class="text-lg mb-2"><i class="fab fa-instagram mr-2"></i> @indetrujillo</p>
                <p class="text-lg mb-2"><i class="fas fa-phone-alt mr-2"></i> 0412-897643</p>
                <p class="text-lg">Valera Edo Trujillo</p>
            </div>
            <div>
                <h3 class="text-3xl font-bold mb-4 text-center md:text-left">Envíanos un Mensaje</h3>
                <form action="php/contact_handler.php" method="POST" class="space-y-4">
                    <input type="text" name="name" placeholder="Tu Nombre" required class="w-full p-3 rounded-lg bg-gray-800 border border-gray-700 focus:outline-none focus:border-green-500">
                    <input type="email" name="email" placeholder="Tu Correo Electrónico" required class="w-full p-3 rounded-lg bg-gray-800 border border-gray-700 focus:outline-none focus:border-green-500">
                    <textarea name="message" placeholder="Tu Mensaje" rows="4" required class="w-full p-3 rounded-lg bg-gray-800 border border-gray-700 focus:outline-none focus:border-green-500"></textarea>
                    <button type="submit" class="w-full action-button bg-green-600 hover:bg-green-700">Enviar Mensaje <i class="fas fa-paper-plane ml-2"></i></button>
                </form>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
