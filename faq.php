<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include 'php/db.php';
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INDET - Preguntas Frecuentes</title>

    <!-- Tailwind CSS -->
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
<body class="bg-gray-900 text-white">

    <!-- Background Elements -->
    <div class="fixed top-0 left-0 w-full h-full bg-cover bg-center z-0" style="background-image: url('images/hero-bg.jpg');"></div>
    <div class="fixed top-0 left-0 w-full h-full bg-black/60 z-10"></div>

    <!-- Header -->
    <header id="header" class="relative h-screen/2 overflow-hidden">
        <!-- Navigation -->
        <nav id="navbar" class="fixed top-0 left-0 w-full p-6 z-40 transition-all duration-300">
            <div class="container mx-auto grid grid-cols-3 items-center">
                <div class="justify-self-start">
                    <a href="index.php"><img src="images/logo.png" alt="INDET Logo" class="w-24 logo"></a>
                </div>
                <div class="hidden md:flex items-center space-x-4 nav-link-container justify-self-center">
                    <a href="index.php" class="nav-button">Inicio</a>
                    <a href="hotel_info.php" class="nav-button">Nuestro Hotel</a>
                    <a href="index.php#rooms" class="nav-button">Habitaciones</a>
                    <a href="index.php#booking" class="nav-button">Reservacion</a>
                    <a href="events.php" class="nav-button">Eventos</a>
                    <a href="faq.php" class="nav-button">FAQ</a>
                    <a href="index.php#footer" class="nav-button">Contactos</a>
                </div>
                <div class="flex items-center space-x-4 justify-self-end">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span class="text-white font-semibold"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <a href="php/logout.php" class="login-button">
                            <span>Logout</span>
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="login-button">
                            <span>Login</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <!-- Hero Content -->
        <div class="relative z-30 flex flex-col items-center justify-center h-full text-center px-4 py-32">
        </div>
    </header>

    <!-- Main Content -->
    <main class="relative z-30 bg-gray-900">
        <!-- FAQ Section -->
        <section id="faq" class="py-24 bg-white text-gray-800">
            <div class="container mx-auto px-6">
                <div class="max-w-4xl mx-auto">
                    <div class="faq-item bg-gray-100 p-6 rounded-lg mb-6" data-aos="fade-up">
                        <h3 class="text-xl font-bold">¿Cuáles son los horarios de check-in y check-out?</h3>
                        <p class="mt-2 text-gray-600">Nuestro check-in es a partir de las 3:00 PM y el check-out es hasta las 12:00 PM.</p>
                    </div>
                    <div class="faq-item bg-gray-100 p-6 rounded-lg mb-6" data-aos="fade-up" data-aos-delay="100">
                        <h3 class="text-xl font-bold">¿Las instalaciones están abiertas para todo público?</h3>
                        <p class="mt-2 text-gray-600">El acceso a las villas es principalmente para atletas y delegaciones. Sin embargo, algunas áreas comunes pueden tener acceso público en horarios específicos. Recomendamos contactarnos para más detalles.</p>
                    </div>
                    <div class="faq-item bg-gray-100 p-6 rounded-lg mb-6" data-aos="fade-up" data-aos-delay="200">
                        <h3 class="text-xl font-bold">¿Cómo puedo realizar una reserva para un grupo grande?</h3>
                        <p class="mt-2 text-gray-600">Para reservas de grupos o delegaciones deportivas, por favor contáctenos directamente a través de nuestro formulario de contacto o número de teléfono para una atención personalizada.</p>
                    </div>
                    <div class="faq-item bg-gray-100 p-6 rounded-lg mb-6" data-aos="fade-up" data-aos-delay="300">
                        <h3 class="text-xl font-bold">¿Se admiten mascotas?</h3>
                        <p class="mt-2 text-gray-600">Actualmente no se admiten mascotas en nuestras instalaciones para garantizar la comodidad y seguridad de todos nuestros huéspedes.</p>
                    </div>
                    <div class="faq-item bg-gray-100 p-6 rounded-lg" data-aos="fade-up" data-aos-delay="400">
                        <h3 class="text-xl font-bold">¿Hay estacionamiento disponible?</h3>
                        <p class="mt-2 text-gray-600">Sí, contamos con un amplio estacionamiento gratuito para nuestros huéspedes durante su estadía.</p>
                    </div>
                </div>
            </div>
        </section>
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
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
