<?php
session_start();
include 'php/db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch reservations from the database
$sql = "SELECT reservations.id, users.name as user_name, rooms.type as room_type, reservations.checkin_date, reservations.checkout_date, reservations.status 
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
    <title>Admin Panel - INDET</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-gray-100 text-gray-800 font-poppins">
    <div class="container mx-auto p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Panel de Administración</h1>
            <a href="php/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition-transform hover:scale-105">Cerrar Sesión</a>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-2xl mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Reservas</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">ID</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Cliente</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Habitación</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Llegada</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Salida</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Estado</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-100">
                                    <td class="py-3 px-4"><?php echo $row['id']; ?></td>
                                    <td class="py-3 px-4"><?php echo $row['user_name']; ?></td>
                                    <td class="py-3 px-4"><?php echo $row['room_type']; ?></td>
                                    <td class="py-3 px-4"><?php echo $row['checkin_date']; ?></td>
                                    <td class="py-3 px-4"><?php echo $row['checkout_date']; ?></td>
                                    <td class="py-3 px-4">
                                        <?php
                                            $status_classes = [
                                                'pending' => 'text-yellow-700 bg-yellow-100',
                                                'confirmed' => 'text-green-700 bg-green-100',
                                                'cancelled' => 'text-red-700 bg-red-100'
                                            ];
                                            $status_class = $status_classes[$row['status']] ?? 'text-gray-700 bg-gray-100';
                                        ?>
                                        <span class="px-2 py-1 font-semibold leading-tight rounded-full <?php echo $status_class; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if ($row['status'] == 'pending'): ?>
                                            <a href="php/update_reservation_status.php?id=<?php echo $row['id']; ?>&status=confirmed" class="text-green-500 hover:text-green-700 mr-2">Confirmar</a>
                                            <a href="php/update_reservation_status.php?id=<?php echo $row['id']; ?>&status=cancelled" class="text-red-500 hover:text-red-700">Cancelar</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">No hay reservas encontradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Usuarios</h2>
                <a href="php/user_management.php" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition-transform hover:scale-105">Gestionar Usuarios</a>
            </div>
            <?php
            // Fetch users to display
            $users_sql = "SELECT id, name, email, role FROM users ORDER BY name ASC";
            $users_result = $conn->query($users_sql);
            ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">ID</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Nombre</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Email</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Rol</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php if ($users_result->num_rows > 0): ?>
                            <?php while($user_row = $users_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-100">
                                    <td class="py-3 px-4"><?php echo $user_row['id']; ?></td>
                                    <td class="py-3 px-4"><?php echo $user_row['name']; ?></td>
                                    <td class="py-3 px-4"><?php echo $user_row['email']; ?></td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 font-semibold leading-tight rounded-full <?php echo $user_row['role'] == 'admin' ? 'text-purple-700 bg-purple-100' : 'text-gray-700 bg-gray-100'; ?>">
                                            <?php echo ucfirst($user_row['role']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">No hay usuarios encontrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-2xl mt-8">
            <h2 class="text-2xl font-bold mb-6">Estado de las Habitaciones</h2>
            <?php
            // Fetch room statuses
            $room_status_sql = "SELECT r.id, r.type, r.capacity, rs.status, rs.date 
                                FROM rooms r 
                                JOIN room_status rs ON r.id = rs.room_id 
                                ORDER BY r.type ASC";
            $room_status_result = $conn->query($room_status_sql);
            ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Habitación</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Capacidad</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Estado</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Fecha de Actualización</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php if ($room_status_result->num_rows > 0): ?>
                            <?php while($status_row = $room_status_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-100">
                                    <td class="py-3 px-4 capitalize"><?php echo $status_row['type']; ?></td>
                                    <td class="py-3 px-4"><?php echo $status_row['capacity']; ?></td>
                                    <td class="py-3 px-4">
                                        <?php
                                            $room_status_classes = [
                                                'available' => 'text-green-700 bg-green-100',
                                                'occupied' => 'text-red-700 bg-red-100',
                                                'cleaning' => 'text-blue-700 bg-blue-100'
                                            ];
                                            $status_class = $room_status_classes[$status_row['status']] ?? 'text-gray-700 bg-gray-100';
                                        ?>
                                        <span class="px-2 py-1 font-semibold leading-tight rounded-full <?php echo $status_class; ?>">
                                            <?php echo ucfirst($status_row['status']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4"><?php echo $status_row['date']; ?></td>
                                    <td class="py-3 px-4">
                                        <form action="php/update_room_status.php" method="POST" class="flex items-center">
                                            <input type="hidden" name="room_id" value="<?php echo $status_row['id']; ?>">
                                            <select name="status" class="p-1 border rounded-lg text-sm">
                                                <option value="available" <?php if($status_row['status'] == 'available') echo 'selected'; ?>>Disponible</option>
                                                <option value="occupied" <?php if($status_row['status'] == 'occupied') echo 'selected'; ?>>Ocupada</option>
                                                <option value="cleaning" <?php if($status_row['status'] == 'cleaning') echo 'selected'; ?>>En Limpieza</option>
                                            </select>
                                            <button type="submit" class="ml-2 bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-1 px-3 rounded-lg text-sm">Actualizar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">No se encontró información sobre el estado de las habitaciones.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-2xl mt-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Reportes de Desempeño</h2>
                <a href="reports.php" class="bg-purple-500 hover:bg-purple-600 text-white font-bold py-2 px-4 rounded-lg">Ver Reportes Avanzados</a>
            </div>
            <?php
            // Fetch data for chart: Reservations per room type
            $chart_sql = "SELECT r.type, COUNT(res.id) as reservation_count 
                          FROM rooms r 
                          LEFT JOIN reservations res ON r.id = res.room_id 
                          GROUP BY r.type";
            $chart_result = $conn->query($chart_sql);
            
            $room_types = [];
            $reservation_counts = [];
            if ($chart_result->num_rows > 0) {
                while($row = $chart_result->fetch_assoc()) {
                    $room_types[] = ucfirst($row['type']);
                    $reservation_counts[] = $row['reservation_count'];
                }
            }
            ?>
            <div>
                <canvas id="reservationsChart"></canvas>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-2xl mt-8">
            <h2 class="text-2xl font-bold mb-6">Asignar Tarea de Mantenimiento</h2>
            <?php
            // Fetch rooms that are not already pending cleaning
            $rooms_to_clean_sql = "SELECT r.id, r.type FROM rooms r 
                                   LEFT JOIN maintenance_tasks mt ON r.id = mt.room_id AND mt.status = 'pending'
                                   WHERE mt.id IS NULL";
            $rooms_to_clean_result = $conn->query($rooms_to_clean_sql);

            // Fetch maintenance staff
            $maintenance_staff_sql = "SELECT id, name FROM users WHERE role = 'maintenance'";
            $maintenance_staff_result = $conn->query($maintenance_staff_sql);
            ?>
            <form action="php/assign_task.php" method="POST">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="room_id" class="block font-semibold mb-2">Habitación</label>
                        <select name="room_id" required class="w-full p-3 border rounded-lg">
                            <option value="">Seleccionar habitación...</option>
                            <?php while($room = $rooms_to_clean_result->fetch_assoc()): ?>
                                <option value="<?php echo $room['id']; ?>"><?php echo ucfirst($room['type']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label for="user_id" class="block font-semibold mb-2">Asignar a</label>
                        <select name="user_id" required class="w-full p-3 border rounded-lg">
                            <option value="">Seleccionar personal...</option>
                            <?php while($staff = $maintenance_staff_result->fetch_assoc()): ?>
                                <option value="<?php echo $staff['id']; ?>"><?php echo $staff['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="self-end">
                        <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg">Asignar Tarea</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('reservationsChart').getContext('2d');
        const reservationsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($room_types); ?>,
                datasets: [{
                    label: '# de Reservas por Tipo de Habitación',
                    data: <?php echo json_encode($reservation_counts); ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>
