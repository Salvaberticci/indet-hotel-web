<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include 'php/db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$user_sql = "SELECT name, email, cedula FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch user's reservations
$reservations_sql = "SELECT r.id, r.checkin_date, r.checkout_date, r.guest_name, r.guest_lastname, r.status,
                           rm.type as room_type, f.name as floor_name
                    FROM reservations r
                    JOIN rooms rm ON r.room_id = rm.id
                    JOIN floors f ON rm.floor_id = f.id
                    WHERE r.user_id = ?
                    ORDER BY r.checkin_date DESC";
$reservations_stmt = $conn->prepare($reservations_sql);
$reservations_stmt->bind_param("i", $user_id);
$reservations_stmt->execute();
$reservations_result = $reservations_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - INDET</title>

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
                    <a href="reservar.php" class="nav-button">Disponibilidad</a>
                    <a href="events.php" class="nav-button">Eventos</a>
                    <a href="faq.php" class="nav-button">FAQ</a>
                    <a href="#footer" class="nav-button">Contactos</a>
                </div>
                <div class="flex flex-col items-center space-y-2 justify-self-end">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="user_profile.php" class="login-button">
                            <span>Mi Perfil</span>
                            <i class="fas fa-user"></i>
                        </a>
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
    </header>

    <!-- Main Content -->
    <main class="relative z-30 bg-transparent flex items-center justify-center min-h-screen pt-24">
        <section class="bg-white text-gray-800 py-12 relative z-30 mx-4 md:mx-auto max-w-6xl rounded-2xl shadow-2xl w-full">
            <div class="container mx-auto px-6">
                <h2 class="text-4xl font-bold text-center mb-8">Mi Perfil</h2>

                <!-- User Information -->
                <div class="bg-gray-50 p-6 rounded-lg mb-8">
                    <h3 class="text-2xl font-bold mb-4">Información Personal</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-semibold text-gray-600">Nombre</label>
                            <p class="text-lg"><?php echo htmlspecialchars($user['name']); ?></p>
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-600">Cédula</label>
                            <p class="text-lg"><?php echo htmlspecialchars($user['cedula']); ?></p>
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-600">Correo Electrónico</label>
                            <p class="text-lg"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Reservations -->
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-2xl font-bold mb-4">Mis Reservas</h3>
                    <?php if ($reservations_result->num_rows > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-200 text-gray-800">
                                    <tr>
                                        <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Habitación</th>
                                        <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Check-in</th>
                                        <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Check-out</th>
                                        <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Estado</th>
                                        <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-700">
                                    <?php while($reservation = $reservations_result->fetch_assoc()): ?>
                                        <tr class="hover:bg-gray-100 border-b border-gray-200">
                                            <td class="py-3 px-4 text-center">
                                                <?php echo htmlspecialchars($reservation['room_type']); ?><br>
                                                <small class="text-gray-500"><?php echo htmlspecialchars($reservation['floor_name']); ?></small>
                                            </td>
                                            <td class="py-3 px-4 text-center"><?php echo date('d/m/Y', strtotime($reservation['checkin_date'])); ?></td>
                                            <td class="py-3 px-4 text-center"><?php echo date('d/m/Y', strtotime($reservation['checkout_date'])); ?></td>
                                            <td class="py-3 px-4 text-center">
                                                <span class="px-2 py-1 rounded-full text-xs <?php
                                                    echo $reservation['status'] === 'confirmed' ? 'bg-green-100 text-green-800' :
                                                         ($reservation['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                                          'bg-gray-100 text-gray-800');
                                                ?>">
                                                    <?php
                                                    echo $reservation['status'] === 'confirmed' ? 'Confirmada' :
                                                         ($reservation['status'] === 'pending' ? 'Pendiente' :
                                                          'Completada');
                                                    ?>
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <?php if ($reservation['status'] === 'pending'): ?>
                                                    <button onclick="cancelReservation(<?php echo $reservation['id']; ?>)" class="text-red-500 hover:text-red-700">
                                                        <i class="fas fa-times"></i> Cancelar
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-gray-500">No disponible</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-gray-500">No tienes reservas registradas.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

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

    <script>
        function cancelReservation(reservationId) {
            if (confirm('¿Estás seguro de que quieres cancelar esta reserva? Esta acción no se puede deshacer.')) {
                fetch('php/cancel_reservation.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'reservation_id=' + reservationId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Reserva cancelada exitosamente.');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>