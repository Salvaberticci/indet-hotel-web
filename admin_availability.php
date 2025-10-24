<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include 'php/db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservacion - Panel de Administración - INDET</title>

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
            <div class="flex items-center">
                <h1 class="text-3xl font-bold">Reservacion</h1>
            </div>
            <div>
                <a href="admin.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-4">Volver al Menú</a>
                <a href="php/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg">Cerrar Sesión</a>
            </div>
        </div>

        <div class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl mb-8">
            <h2 class="text-2xl font-bold mb-6">Ver Reservacion</h2>
            <form method="GET" class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="checkin" class="block font-semibold mb-2">Fecha de Llegada</label>
                        <input type="date" id="checkin" name="checkin" required class="w-full p-2 border rounded bg-gray-700 text-white">
                    </div>
                    <div>
                        <label for="checkout" class="block font-semibold mb-2">Fecha de Salida</label>
                        <input type="date" id="checkout" name="checkout" required class="w-full p-2 border rounded bg-gray-700 text-white">
                    </div>
                </div>
                <button type="submit" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">Ver Reservacion</button>
            </form>
            <?php
            if (isset($_GET['checkin']) && isset($_GET['checkout'])) {
                $checkin_date = $_GET['checkin'];
                $checkout_date = $_GET['checkout'];

                // Find rooms that are NOT booked during the selected dates
                $sql = "SELECT r.id, r.type, r.capacity, r.description, r.photos
                        FROM rooms r
                        WHERE r.id NOT IN (
                            SELECT res.room_id
                            FROM reservations res
                            WHERE (res.checkin_date < ? AND res.checkout_date > ?)
                            OR (res.checkin_date >= ? AND res.checkin_date < ?)
                        )
                        ORDER BY r.type";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $checkout_date, $checkin_date, $checkin_date, $checkout_date);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    echo '<h3 class="text-xl font-bold mb-4">Habitaciones Disponibles</h3>';
                    echo '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">';
                    while ($room = $result->fetch_assoc()) {
                        $photos = json_decode($room['photos'], true);
                        $image = (!empty($photos) && isset($photos[0])) ? "images/{$photos[0]}" : 'images/hero-bg.jpg';

                        echo '<div class="bg-gray-700 rounded-lg overflow-hidden shadow-lg">';
                        echo '<img src="' . htmlspecialchars($image) . '" alt="' . htmlspecialchars($room['type']) . '" class="w-full h-32 object-cover">';
                        echo '<div class="p-4">';
                        echo '<h4 class="text-lg font-bold capitalize">' . htmlspecialchars($room['type']) . '</h4>';
                        echo '<p class="text-gray-300 text-sm mb-2">' . htmlspecialchars($room['description']) . '</p>';
                        echo '<ul class="text-sm space-y-1">';
                        echo '<li><i class="fas fa-users mr-2 text-green-400"></i>Capacidad: ' . htmlspecialchars($room['capacity']) . '</li>';
                        echo '</ul>';
                        echo '</div>';
                        echo '</div>';
                    }
                    echo '</div>';
                } else {
                    echo '<p class="text-center text-red-400">No hay habitaciones disponibles para las fechas seleccionadas.</p>';
                }

                $stmt->close();
            }
            ?>
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