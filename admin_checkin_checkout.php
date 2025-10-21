<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include 'php/db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get today's date
$today = date('Y-m-d');

// Fetch today's check-ins
$checkin_sql = "SELECT r.id, r.guest_name, r.guest_lastname, r.cedula, r.checkin_date, r.checkout_date, r.status,
                       rm.type as room_type, f.name as floor_name
                FROM reservations r
                JOIN rooms rm ON r.room_id = rm.id
                JOIN floors f ON rm.floor_id = f.id
                WHERE r.checkin_date = ? AND r.status IN ('confirmed', 'pending')
                ORDER BY r.checkin_date ASC";
$checkin_stmt = $conn->prepare($checkin_sql);
$checkin_stmt->bind_param("s", $today);
$checkin_stmt->execute();
$checkin_result = $checkin_stmt->get_result();

// Fetch today's check-outs
$checkout_sql = "SELECT r.id, r.guest_name, r.guest_lastname, r.cedula, r.checkin_date, r.checkout_date, r.status,
                        rm.type as room_type, f.name as floor_name
                 FROM reservations r
                 JOIN rooms rm ON r.room_id = rm.id
                 JOIN floors f ON rm.floor_id = f.id
                 WHERE r.checkout_date = ? AND r.status = 'confirmed'
                 ORDER BY r.checkout_date ASC";
$checkout_stmt = $conn->prepare($checkout_sql);
$checkout_stmt->bind_param("s", $today);
$checkout_stmt->execute();
$checkout_result = $checkout_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in/Check-out - Panel de Administración - INDET</title>

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
                <h1 class="text-3xl font-bold">Check-in/Check-out - <?php echo date('d/m/Y', strtotime($today)); ?></h1>
            </div>
            <div>
                <button onclick="printDailyReport()" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg mr-4">
                    <i class="fas fa-print mr-2"></i>Reporte Diario
                </button>
                <a href="admin.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-4">
                    <i class="fas fa-arrow-left mr-2"></i>Volver al Menú
                </a>
                <a href="php/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg">Cerrar Sesión</a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Check-in Section -->
            <div class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl">
                <h2 class="text-2xl font-bold mb-6 text-green-400">
                    <i class="fas fa-sign-in-alt mr-2"></i>Check-ins de Hoy
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-gray-800">
                        <thead class="bg-gray-700 text-white">
                            <tr>
                                <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Huésped</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Habitación</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Check-out</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Estado</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-300">
                            <?php if ($checkin_result->num_rows > 0): ?>
                                <?php while($reservation = $checkin_result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-700 border-b border-gray-700">
                                        <td class="py-3 px-4 text-center">
                                            <?php echo htmlspecialchars($reservation['guest_name'] . ' ' . $reservation['guest_lastname']); ?><br>
                                            <small class="text-gray-400">Cédula: <?php echo htmlspecialchars($reservation['cedula']); ?></small>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <?php echo htmlspecialchars($reservation['room_type']); ?><br>
                                            <small class="text-gray-400"><?php echo htmlspecialchars($reservation['floor_name']); ?></small>
                                        </td>
                                        <td class="py-3 px-4 text-center"><?php echo date('d/m/Y', strtotime($reservation['checkout_date'])); ?></td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="px-2 py-1 rounded-full text-xs <?php echo $reservation['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                <?php echo $reservation['status'] === 'confirmed' ? 'Confirmada' : 'Pendiente'; ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <?php if ($reservation['status'] === 'pending'): ?>
                                                <button onclick="confirmCheckin(<?php echo $reservation['id']; ?>)" class="text-green-500 hover:text-green-700 mr-2">
                                                    <i class="fas fa-check"></i> Confirmar
                                                </button>
                                            <?php else: ?>
                                                <span class="text-green-500">Check-in Completado</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-4">No hay check-ins programados para hoy.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Check-out Section -->
            <div class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl">
                <h2 class="text-2xl font-bold mb-6 text-red-400">
                    <i class="fas fa-sign-out-alt mr-2"></i>Check-outs de Hoy
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-gray-800">
                        <thead class="bg-gray-700 text-white">
                            <tr>
                                <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Huésped</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Habitación</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Check-in</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-300">
                            <?php if ($checkout_result->num_rows > 0): ?>
                                <?php while($reservation = $checkout_result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-700 border-b border-gray-700">
                                        <td class="py-3 px-4 text-center">
                                            <?php echo htmlspecialchars($reservation['guest_name'] . ' ' . $reservation['guest_lastname']); ?><br>
                                            <small class="text-gray-400">Cédula: <?php echo htmlspecialchars($reservation['cedula']); ?></small>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <?php echo htmlspecialchars($reservation['room_type']); ?><br>
                                            <small class="text-gray-400"><?php echo htmlspecialchars($reservation['floor_name']); ?></small>
                                        </td>
                                        <td class="py-3 px-4 text-center"><?php echo date('d/m/Y', strtotime($reservation['checkin_date'])); ?></td>
                                        <td class="py-3 px-4 text-center">
                                            <button onclick="processCheckout(<?php echo $reservation['id']; ?>)" class="text-red-500 hover:text-red-700">
                                                <i class="fas fa-sign-out-alt"></i> Procesar Check-out
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center py-4">No hay check-outs programados para hoy.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
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

        function confirmCheckin(reservationId) {
            if (confirm('¿Confirmar check-in para esta reserva?')) {
                fetch('php/checkin_checkout_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=checkin&reservation_id=' + reservationId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.pdf_url) {
                            // Open PDF in new window
                            window.open(data.pdf_url, '_blank');
                        }
                        alert('Check-in confirmado exitosamente. Se ha generado el recibo.');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }

        function processCheckout(reservationId) {
            if (confirm('¿Procesar check-out para esta reserva? La habitación pasará a mantenimiento.')) {
                fetch('php/checkin_checkout_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=checkout&reservation_id=' + reservationId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.pdf_url) {
                            // Open PDF in new window
                            window.open(data.pdf_url, '_blank');
                        }
                        alert('Check-out procesado exitosamente. La habitación ha sido enviada a mantenimiento.');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }

        function printDailyReport() {
            const printWindow = window.open('', '_blank');
            const today = '<?php echo date('d/m/Y', strtotime($today)); ?>';

            let checkinContent = '';
            let checkoutContent = '';

            // Get check-in data
            const checkinRows = document.querySelectorAll('#checkin-section tbody tr');
            checkinRows.forEach(row => {
                if (row.cells.length >= 4) {
                    checkinContent += `
                        <tr>
                            <td>${row.cells[0].textContent}</td>
                            <td>${row.cells[1].textContent}</td>
                            <td>${row.cells[2].textContent}</td>
                            <td>${row.cells[3].textContent}</td>
                        </tr>
                    `;
                }
            });

            // Get check-out data
            const checkoutRows = document.querySelectorAll('#checkout-section tbody tr');
            checkoutRows.forEach(row => {
                if (row.cells.length >= 3) {
                    checkoutContent += `
                        <tr>
                            <td>${row.cells[0].textContent}</td>
                            <td>${row.cells[1].textContent}</td>
                            <td>${row.cells[2].textContent}</td>
                        </tr>
                    `;
                }
            });

            const content = `
                <html>
                <head>
                    <title>Reporte Diario - ${today}</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        h1, h2 { color: #333; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; margin-bottom: 30px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                        .section { margin-bottom: 40px; }
                        .header { text-align: center; margin-bottom: 30px; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Reporte Diario de Check-in/Check-out</h1>
                        <h2>Fecha: ${today}</h2>
                    </div>

                    <div class="section">
                        <h2>Check-ins del Día</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Huésped</th>
                                    <th>Habitación</th>
                                    <th>Check-out</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${checkinContent || '<tr><td colspan="4">No hay check-ins registrados.</td></tr>'}
                            </tbody>
                        </table>
                    </div>

                    <div class="section">
                        <h2>Check-outs del Día</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Huésped</th>
                                    <th>Habitación</th>
                                    <th>Check-in</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${checkoutContent || '<tr><td colspan="3">No hay check-outs registrados.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </body>
                </html>
            `;

            printWindow.document.write(content);
            printWindow.document.close();
            printWindow.print();
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>