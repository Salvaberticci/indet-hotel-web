<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INDET - Experiencia Deportiva Inmersiva</title>

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
    <div id="three-canvas" class="fixed top-0 left-0 w-full h-full z-20"></div>

    <!-- Header -->
    <header id="header" class="relative h-screen overflow-hidden">
        <!-- Navigation -->
        <nav id="navbar" class="fixed top-0 left-0 w-full p-6 z-40 transition-all duration-300">
            <div class="container mx-auto grid grid-cols-3 items-center">
                <div class="justify-self-start">
                    <img src="images/logo.png" alt="INDET Logo" class="w-24 logo">
                </div>
                <div class="hidden md:flex items-center space-x-4 nav-link-container justify-self-center">
                    <a href="#about" class="nav-button">Sobre La Institución</a>
                    <a href="#booking" class="nav-button">Disponibilidad</a>
                    <a href="#footer" class="nav-button">Contactos</a>
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
        <div class="relative z-30 flex flex-col items-center justify-center h-full text-center px-4">
            <h1 class="text-8xl md:text-9xl font-montserrat font-black text-white tracking-widest indet-title" data-aos="zoom-in">INDET</h1>
            <p class="text-xl md:text-2xl font-poppins font-semibold mt-2" data-aos="fade-up" data-aos-delay="200">#EnTrujilloContinúaElProgresoDeportivoYRecreativo</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="relative z-30 bg-transparent">
        <!-- Booking Section -->
        <section id="booking" class="bg-white text-gray-800 py-12 -mt-24 relative z-30 mx-4 md:mx-auto max-w-5xl rounded-2xl shadow-2xl" data-aos="fade-up">
            <div class="container mx-auto">
                <form action="php/book.php" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end px-6">
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
                            <option value="doble">Doble</option>
                            <option value="suite">Suite</option>
                        </select>
                    </div>
                    <button type="submit" class="action-button">Ver Disponibilidad <i class="fas fa-arrow-right"></i></button>
                </form>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="py-24 text-center bg-white text-gray-800">
            <div class="container mx-auto px-6">
                <h2 class="text-5xl md:text-6xl font-montserrat font-black somos-title" data-aos="fade-up">
                    <span class="text-green-600">SOMOS LA CASA</span><br>
                    <span class="text-red-600">DE LOS JÓVENES</span>
                </h2>
                <p class="max-w-3xl mx-auto mt-6 text-lg text-gray-600" data-aos="fade-up" data-aos-delay="100">
                    Proveemos el deporte a nivel nacional con nuestros jóvenes, construyendo un futuro mejor hasta conseguir los objetivos.
                </p>
            </div>
        </section>

        <!-- Gallery Section -->
        <section id="gallery" class="py-24 bg-transparent">
            <div class="container mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                    <div class="gallery-card" data-aos="fade-up">
                        <img src="images/equipo-natacion.jpg" alt="Natación">
                        <h3>Natación</h3>
                    </div>
                    <div class="gallery-card" data-aos="fade-up" data-aos-delay="100">
                        <img src="images/equipo-futbol.jpg" alt="Fútbol">
                        <h3>Fútbol</h3>
                    </div>
                    <div class="gallery-card" data-aos="fade-up" data-aos-delay="200">
                        <img src="images/equipo-voleibol.jpg" alt="Voleibol">
                        <h3>Voleibol</h3>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer id="footer" class="footer-bg text-white py-12 relative z-30">
        <div class="container mx-auto text-center relative z-10">
            <p class="text-lg"><i class="fab fa-instagram mr-2"></i> @indetrujillo</p>
            <p class="text-lg my-2"><i class="fas fa-phone-alt mr-2"></i> 0412-897643</p>
            <p class="text-lg">Valera Edo Trujillo, Loremp Ipsum</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
