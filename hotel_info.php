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
    <title>INDET - Nuestro Hotel</title>

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
                    <a href="index.php#booking" class="nav-button">Disponibilidad</a>
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
        <!-- Description Section -->
        <section id="description" class="py-24 bg-white text-gray-800">
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-5xl md:text-6xl font-montserrat font-black" data-aos="fade-up">Nuestro Hotel</h2>
                <p class="max-w-3xl mx-auto mt-6 text-lg text-gray-600" data-aos="fade-up" data-aos-delay="100">
                    Ubicado en el corazón de la ciudad, el Hotel Indet ofrece una experiencia de lujo y confort. Nuestras instalaciones están diseñadas para satisfacer tanto a viajeros de negocios como a turistas. Disfrute de nuestras modernas habitaciones, gastronomía de clase mundial y un servicio impecable.
                </p>
            </div>
        </section>

        <!-- Gallery Section -->
        <section id="gallery" class="py-24 bg-gray-100 text-gray-800">
            <div class="container mx-auto px-6">
                <h2 class="text-5xl md:text-6xl font-montserrat font-black text-center" data-aos="fade-up">Nuestras Instalaciones</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mt-12">
                    <div class="gallery-item" data-aos="fade-up">
                        <img src="images/hero-bg.jpg" alt="Instalación 1" class="rounded-lg shadow-lg w-full h-64 object-cover">
                    </div>
                    <div class="gallery-item" data-aos="fade-up" data-aos-delay="100">
                        <img src="images/equipo-futbol.jpg" alt="Instalación 2" class="rounded-lg shadow-lg w-full h-64 object-cover">
                    </div>
                    <div class="gallery-item" data-aos="fade-up" data-aos-delay="200">
                        <img src="images/equipo-natacion.jpg" alt="Instalación 3" class="rounded-lg shadow-lg w-full h-64 object-cover">
                    </div>
                    <div class="gallery-item" data-aos="fade-up" data-aos-delay="300">
                        <img src="images/equipo-voleibol.jpg" alt="Instalación 4" class="rounded-lg shadow-lg w-full h-64 object-cover">
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section id="services" class="py-24 bg-white text-gray-800">
            <div class="container mx-auto px-6">
                <h2 class="text-5xl md:text-6xl font-montserrat font-black text-center" data-aos="fade-up">Servicios Disponibles</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mt-12 text-center">
                    <div class="service-item" data-aos="fade-up">
                        <i class="fas fa-wifi text-4xl text-green-600 mb-4"></i>
                        <h3 class="text-xl font-bold">Wi-Fi de alta velocidad</h3>
                    </div>
                    <div class="service-item" data-aos="fade-up" data-aos-delay="100">
                        <i class="fas fa-utensils text-4xl text-green-600 mb-4"></i>
                        <h3 class="text-xl font-bold">Restaurante Gourmet</h3>
                    </div>
                    <div class="service-item" data-aos="fade-up" data-aos-delay="200">
                        <i class="fas fa-swimmer text-4xl text-green-600 mb-4"></i>
                        <h3 class="text-xl font-bold">Piscina y Spa</h3>
                    </div>
                    <div class="service-item" data-aos="fade-up" data-aos-delay="300">
                        <i class="fas fa-dumbbell text-4xl text-green-600 mb-4"></i>
                        <h3 class="text-xl font-bold">Gimnasio Equipado</h3>
                    </div>
                    <div class="service-item" data-aos="fade-up" data-aos-delay="400">
                        <i class="fas fa-concierge-bell text-4xl text-green-600 mb-4"></i>
                        <h3 class="text-xl font-bold">Servicio a la habitación 24/7</h3>
                    </div>
                    <div class="service-item" data-aos="fade-up" data-aos-delay="500">
                        <i class="fas fa-briefcase text-4xl text-green-600 mb-4"></i>
                        <h3 class="text-xl font-bold">Centro de Negocios</h3>
                    </div>
                </div>
            </div>
        </section>

        <!-- Schedule Section -->
        <section id="schedule" class="py-24 bg-gray-100 text-gray-800">
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-5xl md:text-6xl font-montserrat font-black" data-aos="fade-up">Horarios</h2>
                <div class="flex justify-center space-x-8 mt-8">
                    <div data-aos="fade-up" data-aos-delay="100">
                        <p class="text-2xl font-bold">Check-in</p>
                        <p class="text-lg">A partir de las 15:00</p>
                    </div>
                    <div data-aos="fade-up" data-aos-delay="200">
                        <p class="text-2xl font-bold">Check-out</p>
                        <p class="text-lg">Hasta las 12:00</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer id="footer" class="footer-bg text-white py-20 relative z-30">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 Hotel Indet. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/main.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true,
        });
    </script>
</body>
</html>
