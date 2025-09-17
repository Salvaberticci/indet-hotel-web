<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - INDET</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-gray-900 text-white font-poppins">

    <?php
    if (isset($_GET['status']) && isset($_GET['message'])) {
        $status = $_GET['status'] === 'success' ? 'success' : 'error';
        $message = htmlspecialchars($_GET['message']);
        $icon = $status === 'success' ? 'fa-check-circle' : 'fa-times-circle';
        echo "<div class='notification $status'><i class='fas $icon'></i> $message</div>";
    }
    ?>
    <div class="container mx-auto flex items-center justify-center min-h-screen">
        <div class="bg-white text-gray-800 p-8 rounded-xl shadow-2xl w-full max-w-md">
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
    </div>
</body>
</html>
