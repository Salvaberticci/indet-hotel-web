<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include 'php/db.php';

// Fetch rooms for the new section
$rooms_sql = "SELECT `type`, `capacity`, `description`, `photos` FROM `rooms`";
$rooms_result = $conn->query($rooms_sql);

// Fetch approved comments
$comments_sql = "SELECT name, comment, created_at FROM comments WHERE approved = 1 ORDER BY created_at DESC";
$comments_result = $conn->query($comments_sql);

?>
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
                    <a href="index.php" class="nav-button">Inicio</a>
                    <a href="hotel_info.php" class="nav-button">Nuestro Hotel</a>
                    <a href="#rooms" class="nav-button">Habitaciones</a>
                    <a href="reservar.php" class="nav-button">Disponibilidad</a>
                    <a href="events.php" class="nav-button">Eventos</a>
                    <a href="faq.php" class="nav-button">FAQ</a>
                    <a href="#footer" class="nav-button">Contactos</a>
                </div>
                <div class="flex items-center space-x-4 justify-self-end">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="flex flex-col items-center">
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
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-3xl font-bold mb-6">Reserva tu Estancia</h2>
                <p class="text-lg mb-6">Verifica la disponibilidad y reserva tu habitación perfecta.</p>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="login.php" class="action-button inline-block">Iniciar Sesión para Reservar <i class="fas fa-arrow-right ml-2"></i></a>
                <?php else: ?>
                    <a href="reservar.php" class="action-button inline-block">Ver Disponibilidad <i class="fas fa-arrow-right ml-2"></i></a>
                <?php endif; ?>
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

        <!-- Room Types Section -->
        <section id="rooms" class="py-24 bg-white text-gray-800">
            <div class="container mx-auto px-6">
                <h2 class="text-5xl md:text-6xl font-montserrat font-black text-center" data-aos="fade-up">Nuestras Habitaciones</h2>
                <p class="max-w-3xl mx-auto mt-6 text-lg text-gray-600 text-center" data-aos="fade-up" data-aos-delay="100">
                    Ofrecemos una variedad de habitaciones para adaptarnos a las necesidades de nuestros atletas y visitantes. Cada una diseñada para el máximo confort y recuperación.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12 mt-12">
                    <?php if ($rooms_result && $rooms_result->num_rows > 0): ?>
                        <?php while($room = $rooms_result->fetch_assoc()): ?>
                            <div class="room-card bg-gray-50 rounded-lg overflow-hidden shadow-lg transform hover:scale-105 transition-transform duration-300" data-aos="fade-up">
                                <?php 
                                    $photos = json_decode($room['photos'], true);
                                    $image = (!empty($photos) && isset($photos[0])) ? "images/{$photos[0]}" : 'images/hero-bg.jpg';
                                ?>
                                <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($room['type']); ?>" class="w-full h-64 object-cover">
                                <div class="p-6">
                                    <h3 class="text-2xl font-bold mb-2 capitalize"><?php echo htmlspecialchars($room['type']); ?></h3>
                                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($room['description']); ?></p>
                                    <ul class="text-left mb-4 space-y-2">
                                        <li><i class="fas fa-users mr-2 text-green-600"></i> Capacidad: <?php echo htmlspecialchars($room['capacity']); ?> personas</li>
                                    </ul>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-center col-span-full">No hay información de habitaciones para mostrar en este momento.</p>
                    <?php endif; ?>
                </div>
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

        <!-- Comments Section -->
        <section id="comments" class="py-24 bg-white text-gray-800">
            <div class="container mx-auto px-6">
                <h2 class="text-5xl md:text-6xl font-montserrat font-black text-center" data-aos="fade-up">Comentarios</h2>
                <p class="max-w-3xl mx-auto mt-6 text-lg text-gray-600 text-center" data-aos="fade-up" data-aos-delay="100">Deja tu comentario sobre nuestra experiencia deportiva.</p>
                <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-12">
                    <!-- Comment Form -->
                    <div class="bg-gray-50 p-6 rounded-lg shadow-lg">
                        <h3 class="text-2xl font-bold mb-4">Deja un Comentario</h3>
                        <form action="php/comment_handler.php" method="POST">
                            <div class="mb-4">
                                <label for="name" class="block font-semibold mb-2">Nombre</label>
                                <input type="text" id="name" name="name" required class="w-full p-3 border rounded bg-white">
                            </div>
                            <div class="mb-4">
                                <label for="email" class="block font-semibold mb-2">Correo Electrónico</label>
                                <input type="email" id="email" name="email" required class="w-full p-3 border rounded bg-white">
                            </div>
                            <div class="mb-4">
                                <label for="comment" class="block font-semibold mb-2">Comentario</label>
                                <textarea id="comment" name="comment" rows="4" required class="w-full p-3 border rounded bg-white"></textarea>
                            </div>
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Enviar Comentario</button>
                        </form>
                    </div>
                    <!-- Display Comments -->
                    <div class="bg-gray-50 p-6 rounded-lg shadow-lg">
                        <h3 class="text-2xl font-bold mb-4">Comentarios Recientes</h3>
                        <div class="space-y-4">
                            <?php if ($comments_result && $comments_result->num_rows > 0): ?>
                                <?php while($comment = $comments_result->fetch_assoc()): ?>
                                    <div class="bg-white p-4 rounded shadow">
                                        <h4 class="font-bold"><?php echo htmlspecialchars($comment['name']); ?></h4>
                                        <p class="text-gray-600 text-sm"><?php echo date('d/m/Y', strtotime($comment['created_at'])); ?></p>
                                        <p class="mt-2"><?php echo htmlspecialchars($comment['comment']); ?></p>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p>No hay comentarios aprobados aún.</p>
                            <?php endif; ?>
                        </div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
