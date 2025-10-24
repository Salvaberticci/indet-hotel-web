<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include 'php/db.php';

// Fetch events (assuming an 'events' table exists)
// This is a placeholder. We will create the table and add data later.
$events_sql = "SELECT `name`, `description`, `date`, `image` FROM `events` ORDER BY `date` DESC";
$events_result = $conn->query($events_sql);
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INDET - Eventos</title>

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
        <!-- Events Section -->
        <section id="events" class="py-24 bg-white text-gray-800">
            <div class="container mx-auto px-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
                    <?php if ($events_result && $events_result->num_rows > 0): ?>
                        <?php while($event = $events_result->fetch_assoc()): ?>
                            <div class="event-card bg-gray-50 rounded-lg overflow-hidden shadow-lg" data-aos="fade-up">
                                <img src="images/<?php echo htmlspecialchars($event['image']); ?>" alt="<?php echo htmlspecialchars($event['name']); ?>" class="w-full h-64 object-cover">
                                <div class="p-6">
                                    <h3 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($event['name']); ?></h3>
                                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($event['description']); ?></p>
                                    <p class="font-semibold">Fecha: <?php echo htmlspecialchars(date('d/m/Y', strtotime($event['date']))); ?></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="col-span-full text-center">No hay eventos programados en este momento.</p>
                    <?php endif; ?>
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
