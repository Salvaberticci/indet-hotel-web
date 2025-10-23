<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include 'php/db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch reservations from the database
$sql = "SELECT reservations.id, users.name as user_name, users.cedula_type as user_cedula_type, users.cedula as user_cedula, rooms.type as room_type, reservations.checkin_date, reservations.checkout_date, reservations.status, reservations.user_id
        FROM reservations
        JOIN users ON reservations.user_id = users.id
        JOIN rooms ON reservations.room_id = rooms.id
        ORDER BY reservations.checkin_date ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas - Panel de Administración - INDET</title>

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
                <h1 class="text-3xl font-bold">Reservas</h1>
            </div>
            <div>
                <a href="admin.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-4">Volver al Menú</a>
                <a href="php/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg">Cerrar Sesión</a>
            </div>
        </div>

        <!-- Search by Cédula -->
        <div class="mb-6">
            <label for="cedula_search" class="block text-sm font-medium mb-2">Buscar por Cédula:</label>
            <input type="text" id="cedula_search" placeholder="Ingresa la cédula..." class="p-2 border rounded bg-gray-700 text-white w-full md:w-1/3">
            <button onclick="searchByCedula()" class="ml-2 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">
                <i class="fas fa-search mr-2"></i>Buscar
            </button>
        </div>

        <div class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Reservas</h2>
            </div>

            <!-- Add Reservation Form -->
            <form action="php/reservation_handler.php" method="POST" class="mb-8 p-4 bg-gray-700 rounded-lg">
                <h3 class="text-xl font-semibold mb-4">Agregar Nueva Reserva</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="user_id" class="block font-semibold mb-2">Cliente (Buscar por Cédula)</label>
                        <select name="user_id" id="user_select" required class="w-full p-2 border rounded bg-gray-600 text-white">
                            <option value="">Seleccionar cliente...</option>
                            <?php
                            $users_sql = "SELECT id, name, cedula_type, cedula FROM users WHERE cedula IS NOT NULL AND cedula != '' ORDER BY cedula ASC";
                            $users_result = $conn->query($users_sql);
                            while($user = $users_result->fetch_assoc()): ?>
                                <option value="<?php echo $user['id']; ?>" data-cedula="<?php echo htmlspecialchars($user['cedula']); ?>">
                                    <?php echo htmlspecialchars($user['cedula_type'] . '-' . $user['cedula'] . ' - ' . $user['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label for="room_id" class="block font-semibold mb-2">Habitación</label>
                        <select name="room_id" required class="w-full p-2 border rounded bg-gray-600 text-white">
                            <option value="">Seleccionar habitación...</option>
                            <?php
                            $rooms_for_select_sql = "SELECT id, type FROM rooms ORDER BY type ASC";
                            $rooms_for_select_result = $conn->query($rooms_for_select_sql);
                            while($room_for_select = $rooms_for_select_result->fetch_assoc()): ?>
                                <option value="<?php echo $room_for_select['id']; ?>"><?php echo ucfirst($room_for_select['type']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label for="checkin_date" class="block font-semibold mb-2">Fecha de Llegada</label>
                        <input type="date" name="checkin_date" required class="w-full p-2 border rounded bg-gray-600 text-white">
                    </div>
                    <div>
                        <label for="checkout_date" class="block font-semibold mb-2">Fecha de Salida</label>
                        <input type="date" name="checkout_date" required class="w-full p-2 border rounded bg-gray-600 text-white">
                    </div>
                </div>
                <button type="submit" name="add_reservation" class="mt-4 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg">Agregar Reserva</button>
            </form>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-gray-800">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">ID</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Cliente</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Habitación</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Llegada</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Salida</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Estado</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Acciones</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Editar</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Eliminar</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-300">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-700 border-b border-gray-700 reservation-row" data-cedula="<?php echo htmlspecialchars($row['user_cedula']); ?>">
                                    <td class="py-3 px-4 text-center"><?php echo $row['id']; ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <?php echo $row['user_name']; ?><br>
                                        <small class="text-gray-400">Cédula: <?php echo htmlspecialchars($row['user_cedula_type'] . '-' . $row['user_cedula']); ?></small>
                                    </td>
                                    <td class="py-3 px-4 text-center"><?php echo $row['room_type']; ?></td>
                                    <td class="py-3 px-4 text-center"><?php echo $row['checkin_date']; ?></td>
                                    <td class="py-3 px-4 text-center"><?php echo $row['checkout_date']; ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <?php
                                            $status_classes = [
                                                'pending' => 'text-yellow-700 bg-yellow-100',
                                                'confirmed' => 'text-green-700 bg-green-100',
                                                'cancelled' => 'text-red-700 bg-red-100'
                                            ];
                                            $status_class = $status_classes[$row['status']] ?? 'text-gray-700 bg-gray-100';
                                            $status_translations = [
                                                'pending' => 'Pendiente',
                                                'confirmed' => 'Confirmada',
                                                'cancelled' => 'Cancelada'
                                            ];
                                            $translated_status = $status_translations[$row['status']] ?? ucfirst($row['status']);
                                        ?>
                                        <span class="px-2 py-1 font-semibold leading-tight rounded-full <?php echo $status_class; ?>">
                                            <?php echo $translated_status; ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <?php if ($row['status'] == 'pending'): ?>
                                            <a href="php/update_reservation_status.php?id=<?php echo $row['id']; ?>&status=confirmed" class="text-green-500 hover:text-green-700 mr-2">Confirmar</a>
                                            <a href="php/update_reservation_status.php?id=<?php echo $row['id']; ?>&status=cancelled" class="text-red-500 hover:text-red-700">Cancelar</a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <button onclick="openEditReservationModal(<?php echo htmlspecialchars(json_encode($row)); ?>)" class="text-blue-500 hover:text-blue-700">Editar</button>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <a href="php/reservation_handler.php?delete_reservation=<?php echo $row['id']; ?>" onclick="return confirm('¿Estás seguro de eliminar esta reserva?')" class="text-red-500 hover:text-red-700">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">No hay reservas encontradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Reservation Modal -->
    <div id="editReservationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-gray-800 text-white p-8 rounded-lg shadow-2xl w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6">Editar Reserva</h2>
            <form action="php/reservation_handler.php" method="POST">
                <input type="hidden" id="editReservationId" name="id">
                <div class="mb-4">
                    <label for="editReservationUser" class="block font-semibold mb-2">Cliente (Buscar por Cédula)</label>
                    <select id="editReservationUser" name="user_id" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                        <option value="">Seleccionar cliente...</option>
                        <?php
                        $users_sql = "SELECT id, name, cedula_type, cedula FROM users WHERE cedula IS NOT NULL AND cedula != '' ORDER BY cedula ASC";
                        $users_result = $conn->query($users_sql);
                        while($user = $users_result->fetch_assoc()): ?>
                            <option value="<?php echo $user['id']; ?>" data-cedula="<?php echo htmlspecialchars($user['cedula']); ?>">
                                <?php echo htmlspecialchars($user['cedula_type'] . '-' . $user['cedula'] . ' - ' . $user['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="editReservationRoom" class="block font-semibold mb-2">Habitación</label>
                    <select id="editReservationRoom" name="room_id" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                        <option value="">Seleccionar habitación...</option>
                        <?php
                        $rooms_sql = "SELECT id, type FROM rooms ORDER BY type ASC";
                        $rooms_result = $conn->query($rooms_sql);
                        while($room = $rooms_result->fetch_assoc()): ?>
                            <option value="<?php echo $room['id']; ?>"><?php echo ucfirst($room['type']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="editReservationCheckin" class="block font-semibold mb-2">Fecha de Llegada</label>
                    <input type="date" id="editReservationCheckin" name="checkin_date" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                </div>
                <div class="mb-4">
                    <label for="editReservationCheckout" class="block font-semibold mb-2">Fecha de Salida</label>
                    <input type="date" id="editReservationCheckout" name="checkout_date" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                </div>
                <div class="mb-4">
                    <label for="editReservationStatus" class="block font-semibold mb-2">Estado</label>
                    <select id="editReservationStatus" name="status" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                        <option value="pending">Pendiente</option>
                        <option value="confirmed">Confirmada</option>
                        <option value="cancelled">Cancelada</option>
                    </select>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeEditReservationModal()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-2">Cancelar</button>
                    <button type="submit" name="update_reservation" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">Actualizar Reserva</button>
                </div>
            </form>
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

        function searchByCedula() {
            const cedula = document.getElementById('cedula_search').value.trim();
            const rows = document.querySelectorAll('.reservation-row');

            if (cedula === '') {
                // Show all rows if search is empty
                rows.forEach(row => {
                    row.style.display = '';
                });
                return;
            }

            rows.forEach(row => {
                const rowCedula = row.getAttribute('data-cedula');
                if (rowCedula && rowCedula.includes(cedula)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function filterUsersByCedula() {
            const cedula = document.getElementById('user_cedula_search').value.trim();
            const options = document.querySelectorAll('#user_select option');

            options.forEach(option => {
                if (option.value === '') return; // Skip the "Seleccionar cliente..." option

                const optionCedula = option.getAttribute('data-cedula');
                if (cedula === '' || (optionCedula && optionCedula.includes(cedula))) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            });
        }

        function openEditReservationModal(reservation) {
            document.getElementById('editReservationId').value = reservation.id;
            document.getElementById('editReservationUser').value = reservation.user_id;
            document.getElementById('editReservationRoom').value = reservation.room_id;
            document.getElementById('editReservationCheckin').value = reservation.checkin_date;
            document.getElementById('editReservationCheckout').value = reservation.checkout_date;
            document.getElementById('editReservationStatus').value = reservation.status;
            document.getElementById('editReservationModal').classList.remove('hidden');
        }

        function closeEditReservationModal() {
            document.getElementById('editReservationModal').classList.add('hidden');
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>