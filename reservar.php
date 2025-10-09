<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include 'php/db.php';

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INDET - Realizar Reserva</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-gray-900 text-white">

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
    <header id="header" class="relative">
        <!-- Navigation -->
        <nav id="navbar" class="fixed top-0 left-0 w-full p-6 z-40 transition-all duration-300">
            <div class="container mx-auto grid grid-cols-3 items-center">
                <div class="justify-self-start">
                    <img src="images/logo.png" alt="INDET Logo" class="w-24 logo">
                </div>
                <div class="hidden md:flex items-center space-x-4 nav-link-container justify-self-center">
                    <a href="index.php" class="nav-button">Inicio</a>
                    <a href="hotel_info.php" class="nav-button">Nuestro Hotel</a>
                    <a href="index.php#rooms" class="nav-button">Habitaciones</a>
                    <a href="reservar.php" class="nav-button">Disponibilidad</a>
                    <a href="events.php" class="nav-button">Eventos</a>
                    <a href="faq.php" class="nav-button">FAQ</a>
                    <a href="#footer" class="nav-button">Contactos</a>
                </div>
                <div class="flex flex-col items-center space-y-2 justify-self-end">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="php/logout.php" class="login-button">
                            <span>Logout</span>
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                        <span class="text-white font-semibold"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
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
    <main class="relative z-30 bg-transparent flex items-center justify-center min-h-screen">
        <!-- Booking Section -->
        <section id="booking" class="bg-white text-gray-800 py-12 relative z-30 mx-4 md:mx-auto max-w-5xl rounded-2xl shadow-2xl w-full">
            <div class="container mx-auto">
                <h2 class="text-4xl font-bold text-center mb-8">Realizar una Reserva</h2>
                <form action="php/book.php" method="POST" class="px-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end mb-6">
                        <div class="form-group text-left">
                            <label for="checkin" class="font-bold text-sm mb-2 block text-gray-500">FECHA DE LLEGADA*</label>
                            <input type="text" name="checkin" placeholder="SELECCIONA" onfocus="(this.type='date')" onblur="(this.type='text')" required class="booking-input">
                        </div>
                        <div class="form-group text-left">
                            <label for="checkout" class="font-bold text-sm mb-2 block text-gray-500">FECHA DE SALIDA*</label>
                            <input type="text" name="checkout" placeholder="SELECCIONA" onfocus="(this.type='date')" onblur="(this.type='text')" required class="booking-input">
                        </div>
                        <div class="form-group text-left">
                            <label for="room_type" class="font-bold text-sm mb-2 block text-gray-500">HABITACIÓN*</label>
                            <select name="room_type" required class="booking-input">
                                <option value="">SELECCIONA</option>
                                <option value="individual">Individual</option>
                                <option value="dual">Doble</option>
                                <option value="suite">Suite</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="form-group text-left">
                            <label for="guest_name" class="font-bold text-sm mb-2 block text-gray-500">NOMBRE COMPLETO*</label>
                            <input type="text" name="guest_name" placeholder="Ingresa tu nombre" required class="booking-input">
                        </div>
                        <div class="form-group text-left">
                            <label for="guest_email" class="font-bold text-sm mb-2 block text-gray-500">CORREO ELECTRÓNICO*</label>
                            <input type="email" name="guest_email" placeholder="Ingresa tu correo" required class="booking-input">
                        </div>
                    </div>
                    <button type="submit" class="action-button w-full">Confirmar Reserva <i class="fas fa-arrow-right"></i></button>
                </form>
                <div id="availability-results" class="mt-8"></div>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkinInput = document.querySelector('input[name="checkin"]');
            const checkoutInput = document.querySelector('input[name="checkout"]');
            const roomTypeSelect = document.querySelector('select[name="room_type"]');
            const resultsContainer = document.getElementById('availability-results');

            function checkAvailability() {
                const checkin = checkinInput.value;
                const checkout = checkoutInput.value;
                const roomType = roomTypeSelect.value;

                if (checkin && checkout) {
                    let url = `php/availability_handler.php?checkin=${checkin}&checkout=${checkout}`;
                    if (roomType) {
                        url += `&room_type=${roomType}`;
                    }
                    fetch(url)
                        .then(response => response.text())
                        .then(data => {
                            resultsContainer.innerHTML = data;
                        })
                        .catch(error => console.error('Error:', error));
                }
            }

            checkinInput.addEventListener('change', checkAvailability);
            checkoutInput.addEventListener('change', checkAvailability);
            roomTypeSelect.addEventListener('change', checkAvailability);
        });
    </script>

    <!-- Footer -->
    <footer id="footer" class="footer-bg text-white py-20 relative z-30 mt-16">
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

</body>
</html>
