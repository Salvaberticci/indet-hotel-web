<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include 'php/db.php';

// Check if the user is logged in and is an admin or maintenance
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'maintenance'])) {
    header("Location: login.php");
    exit();
}

$is_admin = $_SESSION['user_role'] == 'admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - INDET</title>

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

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">

    <style>
    body {
      background: #111;
      overflow-x: hidden;
    }

    #networkCanvas {
      position: fixed;
      top: 0;
      left: 0;
      z-index: -1;
    }
    </style>
</head>
<body class="bg-gray-900 text-white font-poppins">
    <canvas id="networkCanvas"></canvas>
    <div class="min-h-screen p-8">
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
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold"><?php echo $is_admin ? 'Panel de Administración' : 'Panel de Mantenimiento'; ?></h1>
            <div>
                <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-4">
                    <i class="fas fa-arrow-left mr-2"></i>Volver al Inicio
                </a>
                <a href="php/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg">Cerrar Sesión</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($is_admin): ?>
            <a href="admin_reservations.php" class="bg-gray-800 hover:bg-gray-700 text-white p-6 rounded-xl shadow-2xl transition text-center">
                <i class="fas fa-calendar-check fa-3x mb-4"></i>
                <h3 class="text-xl font-bold">Reservas</h3>
                <p>Gestionar reservas de habitaciones</p>
            </a>
            <a href="admin_availability.php" class="bg-gray-800 hover:bg-gray-700 text-white p-6 rounded-xl shadow-2xl transition text-center">
                <i class="fas fa-search fa-3x mb-4"></i>
                <h3 class="text-xl font-bold">Reservacion</h3>
                <p>Ver habitaciones disponibles</p>
            </a>
            <a href="admin_users.php" class="bg-gray-800 hover:bg-gray-700 text-white p-6 rounded-xl shadow-2xl transition text-center">
                <i class="fas fa-users fa-3x mb-4"></i>
                <h3 class="text-xl font-bold">Usuarios</h3>
                <p>Gestionar usuarios del sistema</p>
            </a>
            <a href="admin_floors.php" class="bg-gray-800 hover:bg-gray-700 text-white p-6 rounded-xl shadow-2xl transition text-center">
                <i class="fas fa-building fa-3x mb-4"></i>
                <h3 class="text-xl font-bold">Pisos</h3>
                <p>Gestionar pisos del hotel</p>
            </a>
            <a href="admin_rooms.php" class="bg-gray-800 hover:bg-gray-700 text-white p-6 rounded-xl shadow-2xl transition text-center">
                <i class="fas fa-bed fa-3x mb-4"></i>
                <h3 class="text-xl font-bold">Habitaciones</h3>
                <p>Gestionar habitaciones</p>
            </a>
            <a href="admin_inventory.php" class="bg-gray-800 hover:bg-gray-700 text-white p-6 rounded-xl shadow-2xl transition text-center">
                <i class="fas fa-boxes fa-3x mb-4"></i>
                <h3 class="text-xl font-bold">Inventario</h3>
                <p>Gestionar inventario por habitación</p>
            </a>
            <a href="admin_cleaning_inventory.php" class="bg-gray-800 hover:bg-gray-700 text-white p-6 rounded-xl shadow-2xl transition text-center">
                <i class="fas fa-broom fa-3x mb-4"></i>
                <h3 class="text-xl font-bold">Faenas</h3>
                <p>Gestionar cuartos de faenas e inventario</p>
            </a>
            <a href="admin_reports.php" class="bg-gray-800 hover:bg-gray-700 text-white p-6 rounded-xl shadow-2xl transition text-center">
                <i class="fas fa-chart-bar fa-3x mb-4"></i>
                <h3 class="text-xl font-bold">Reportes</h3>
                <p>Ver reportes de desempeño</p>
            </a>
            <a href="admin_events.php" class="bg-gray-800 hover:bg-gray-700 text-white p-6 rounded-xl shadow-2xl transition text-center">
                <i class="fas fa-calendar-alt fa-3x mb-4"></i>
                <h3 class="text-xl font-bold">Eventos</h3>
                <p>Gestionar eventos</p>
            </a>
            <?php endif; ?>
            <a href="admin_maintenance_tasks.php" class="bg-gray-800 hover:bg-gray-700 text-white p-6 rounded-xl shadow-2xl transition text-center">
                <i class="fas fa-tasks fa-3x mb-4"></i>
                <h3 class="text-xl font-bold">Tareas de Mantenimiento</h3>
                <p>Ver tareas de mantenimiento</p>
            </a>
            <?php if ($is_admin): ?>
            <a href="admin_checkin_checkout.php" class="bg-gray-800 hover:bg-gray-700 text-white p-6 rounded-xl shadow-2xl transition text-center">
                <i class="fas fa-concierge-bell fa-3x mb-4"></i>
                <h3 class="text-xl font-bold">Check-in/Check-out</h3>
                <p>Gestionar check-ins y check-outs diarios</p>
            </a>
            <?php endif; ?>
            <?php if ($is_admin): ?>
            <a href="admin_comments.php" class="bg-gray-800 hover:bg-gray-700 text-white p-6 rounded-xl shadow-2xl transition text-center">
                <i class="fas fa-comments fa-3x mb-4"></i>
                <h3 class="text-xl font-bold">Comentarios</h3>
                <p>Gestionar comentarios</p>
            </a>
            <?php endif; ?>
        </div>
    </div>


    <script>
        const canvas = document.getElementById('networkCanvas');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        let nodes = [];
        for (let i = 0; i < 50; i++) {
            nodes.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                vx: (Math.random() - 0.5) * 1,
                vy: (Math.random() - 0.5) * 1
            });
        }

        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            nodes.forEach(node => {
                node.x += node.vx;
                node.y += node.vy;
                if (node.x < 0 || node.x > canvas.width) node.vx *= -1;
                if (node.y < 0 || node.y > canvas.height) node.vy *= -1;
                ctx.beginPath();
                ctx.arc(node.x, node.y, 2, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(255,255,255,0.7)';
                ctx.fill();
            });

            nodes.forEach((node, i) => {
                nodes.slice(i + 1).forEach(other => {
                    let dist = Math.hypot(node.x - other.x, node.y - other.y);
                    if (dist < 120) {
                        ctx.beginPath();
                        ctx.moveTo(node.x, node.y);
                        ctx.lineTo(other.x, other.y);
                        ctx.strokeStyle = `rgba(255,255,255,${(1 - dist / 120) * 0.3})`;
                        ctx.stroke();
                    }
                });
            });
            requestAnimationFrame(animate);
        }
        animate();
    </script>
</body>
</html>
<?php
$conn->close();
?>
