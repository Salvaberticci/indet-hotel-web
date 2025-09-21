<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - INDET</title>

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
    <div id="three-canvas" class="fixed top-0 left-0 w-full h-full z-20"></div>

    <!-- Navigation -->
    <nav id="navbar" class="fixed top-0 left-0 w-full p-6 z-40 transition-all duration-300">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php"><img src="images/logo.png" alt="INDET Logo" class="w-24 logo"></a>
            <div class="hidden md:flex items-center space-x-4 nav-link-container">
                <a href="index.php#about" class="nav-button">Sobre La Institución</a>
                <a href="index.php#gallery" class="nav-button">Instalaciones</a>
                <a href="index.php#footer" class="nav-button">Contactos</a>
            </div>
             <div class="flex items-center space-x-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="text-white font-semibold"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="php/logout.php" class="login-button">
                        <span>Logout</span>
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                <?php else: ?>
                    <a href="index.php" class="login-button">
                        <span>Inicio</span>
                        <i class="fas fa-home"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="relative z-30 flex items-center justify-center min-h-screen">
        <div class="bg-white text-gray-800 p-8 rounded-xl shadow-2xl w-full max-w-md" data-aos="fade-up">
            <h2 class="text-3xl font-bold mb-6 text-center">Iniciar Sesión</h2>
            <form action="php/login_handler.php" method="POST">
                <div class="mb-4">
                    <label for="email" class="block font-semibold mb-2">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div class="mb-6">
                    <label for="password" class="block font-semibold mb-2">Contraseña</label>
                    <input type="password" id="password" name="password" required class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg w-full transition-transform hover:scale-105 shadow-lg">Entrar</button>
            </form>
            <p class="text-center mt-4">¿No tienes una cuenta? <a href="register.php" class="text-green-600 hover:underline">Regístrate aquí</a></p>
        </div>
    </main>

    <!-- Footer -->
    <footer id="footer" class="footer-bg text-white py-12 relative z-30">
        <div class="container mx-auto text-center relative z-10">
            <p class="text-lg"><i class="fab fa-instagram mr-2"></i> @indetrujillo</p>
            <p class="text-lg my-2"><i class="fas fa-phone-alt mr-2"></i> 0412-897643</p>
            <p class="text-lg">Valera Edo Trujillo</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
