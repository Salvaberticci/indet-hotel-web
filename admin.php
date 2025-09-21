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
$sql = "SELECT reservations.id, users.name as user_name, rooms.type as room_type, reservations.checkin_date, reservations.checkout_date, reservations.status 
        FROM reservations 
        JOIN users ON reservations.user_id = users.id 
        JOIN rooms ON reservations.room_id = rooms.id 
        ORDER BY reservations.checkin_date ASC";
$result = $conn->query($sql);

// Fetch events
$events_sql = "SELECT id, name, description, date FROM events ORDER BY date DESC";
$events_result = $conn->query($events_sql);

// Fetch rooms for management
$rooms_sql = "SELECT id, type, capacity, description, price FROM rooms ORDER BY type ASC";
$rooms_result = $conn->query($rooms_sql);

// Fetch maintenance tasks
$maintenance_sql = "SELECT mt.id, r.type as room_type, u.name as staff_name, mt.status, mt.created_at 
                    FROM maintenance_tasks mt
                    JOIN rooms r ON mt.room_id = r.id
                    JOIN users u ON mt.assigned_to_user_id = u.id
                    ORDER BY mt.created_at DESC";
$maintenance_result = $conn->query($maintenance_sql);
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
</head>
<body class="bg-gray-900 text-white font-poppins">
    <div class="container mx-auto p-8">
        <?php
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            $status_class = $message['status'] == 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
            echo '<div class="border px-4 py-3 rounded relative mb-4 ' . $status_class . '" role="alert">';
            echo '<span class="block sm:inline">' . $message['text'] . '</span>';
            echo '</div>';
            unset($_SESSION['flash_message']);
        }
        ?>
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Panel de Administración</h1>
            <a href="php/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition-transform hover:scale-105">Cerrar Sesión</a>
        </div>

        <div class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Reservas</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-gray-800">
                    <thead class="bg-gray-700 text-white">
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
                    <tbody class="text-gray-300">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-700 border-b border-gray-700">
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

        <div id="users-section" class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl">
            <h2 class="text-2xl font-bold mb-6">Gestionar Usuarios</h2>
            <?php
            // Fetch users to display
            $users_sql = "SELECT id, name, email, role FROM users ORDER BY name ASC";
            $users_result = $conn->query($users_sql);
            ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-gray-800">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Nombre</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Email</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Rol</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-300">
                        <?php if ($users_result->num_rows > 0): ?>
                            <?php while($user_row = $users_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-700 border-b border-gray-700">
                                    <td class="py-3 px-4"><?php echo $user_row['name']; ?></td>
                                    <td class="py-3 px-4"><?php echo $user_row['email']; ?></td>
                                    <td class="py-3 px-4">
                                        <form action="php/user_handler.php" method="POST" class="flex items-center">
                                            <input type="hidden" name="user_id" value="<?php echo $user_row['id']; ?>">
                                            <select name="role" class="p-1 border rounded-lg text-sm bg-gray-700 text-white">
                                                <option value="user" <?php if($user_row['role'] == 'user') echo 'selected'; ?>>Usuario</option>
                                                <option value="admin" <?php if($user_row['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                                                <option value="maintenance" <?php if($user_row['role'] == 'maintenance') echo 'selected'; ?>>Mantenimiento</option>
                                            </select>
                                            <button type="submit" name="update_user_role" class="ml-2 bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-1 px-3 rounded-lg text-sm">Guardar</button>
                                        </form>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if ($_SESSION['user_id'] != $user_row['id']): ?>
                                            <a href="php/user_handler.php?delete_user=<?php echo $user_row['id']; ?>" onclick="return confirm('¿Estás seguro? Esta acción no se puede deshacer.')" class="text-red-500 hover:text-red-700">Eliminar</a>
                                        <?php endif; ?>
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

        <div id="rooms-section" class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl mt-8">
            <h2 class="text-2xl font-bold mb-6">Gestionar Habitaciones</h2>

            <!-- Add Room Form -->
            <form action="php/room_handler.php" method="POST" class="mb-8 p-4 bg-gray-700 rounded-lg">
                <h3 class="text-xl font-semibold mb-4">Agregar Nueva Habitación</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <input type="text" name="type" placeholder="Tipo (ej. Individual)" required class="p-2 border rounded bg-gray-600 text-white">
                    <input type="number" name="capacity" placeholder="Capacidad" required class="p-2 border rounded bg-gray-600 text-white">
                    <input type="text" name="description" placeholder="Descripción" required class="p-2 border rounded bg-gray-600 text-white">
                    <input type="number" step="0.01" name="price" placeholder="Precio por noche" required class="p-2 border rounded bg-gray-600 text-white">
                </div>
                <button type="submit" name="add_room" class="mt-4 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg">Agregar Habitación</button>
            </form>

            <!-- Rooms Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-gray-800">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Tipo</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Capacidad</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Precio</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-300">
                        <?php if ($rooms_result->num_rows > 0): ?>
                            <?php while($room = $rooms_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-700 border-b border-gray-700">
                                    <td class="py-3 px-4 capitalize"><?php echo $room['type']; ?></td>
                                    <td class="py-3 px-4"><?php echo $room['capacity']; ?></td>
                                    <td class="py-3 px-4">$<?php echo number_format($room['price'], 2); ?></td>
                                    <td class="py-3 px-4">
                                        <button onclick="openEditRoomModal(<?php echo htmlspecialchars(json_encode($room)); ?>)" class="text-blue-500 hover:text-blue-700 mr-2">Editar</button>
                                        <a href="php/room_handler.php?delete_room=<?php echo $room['id']; ?>" onclick="return confirm('¿Estás seguro? Esto no se puede hacer si la habitación tiene reservas.')" class="text-red-500 hover:text-red-700">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center py-4">No hay habitaciones para mostrar.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl mt-8">
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

        <div id="events-section" class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl mt-8">
            <h2 class="text-2xl font-bold mb-6">Gestionar Eventos</h2>
            
            <!-- Add Event Form -->
            <form action="php/event_handler.php" method="POST" class="mb-8 p-4 bg-gray-700 rounded-lg">
                <h3 class="text-xl font-semibold mb-4">Agregar Nuevo Evento</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="text" name="name" placeholder="Nombre del Evento" required class="p-2 border rounded bg-gray-600 text-white">
                    <input type="text" name="description" placeholder="Descripción" required class="p-2 border rounded bg-gray-600 text-white">
                    <input type="date" name="date" required class="p-2 border rounded bg-gray-600 text-white">
                </div>
                <button type="submit" name="add_event" class="mt-4 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg">Agregar Evento</button>
            </form>

            <!-- Events Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-gray-800">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Nombre</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Descripción</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Fecha</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-300">
                        <?php if ($events_result->num_rows > 0): ?>
                            <?php while($event = $events_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-700 border-b border-gray-700">
                                    <td class="py-3 px-4"><?php echo $event['name']; ?></td>
                                    <td class="py-3 px-4"><?php echo $event['description']; ?></td>
                                    <td class="py-3 px-4"><?php echo $event['date']; ?></td>
                                    <td class="py-3 px-4">
                                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($event)); ?>)" class="text-blue-500 hover:text-blue-700 mr-2">Editar</button>
                                        <a href="php/event_handler.php?delete_event=<?php echo $event['id']; ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar este evento?');" class="text-red-500 hover:text-red-700">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center py-4">No hay eventos para mostrar.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl mt-8">
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
                        <select name="room_id" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                            <option value="">Seleccionar habitación...</option>
                            <?php while($room = $rooms_to_clean_result->fetch_assoc()): ?>
                                <option value="<?php echo $room['id']; ?>"><?php echo ucfirst($room['type']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label for="user_id" class="block font-semibold mb-2">Asignar a</label>
                        <select name="user_id" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
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

        <div id="maintenance-tasks-section" class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl mt-8">
            <h2 class="text-2xl font-bold mb-6">Tareas de Mantenimiento</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-gray-800">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Habitación</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Asignado a</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Estado</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Fecha de Creación</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-300">
                        <?php if ($maintenance_result->num_rows > 0): ?>
                            <?php while($task = $maintenance_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-700 border-b border-gray-700">
                                    <td class="py-3 px-4"><?php echo $task['room_type']; ?></td>
                                    <td class="py-3 px-4"><?php echo $task['staff_name']; ?></td>
                                    <td class="py-3 px-4">
                                        <?php
                                            $status_classes = [
                                                'pending' => 'text-yellow-700 bg-yellow-100',
                                                'completed' => 'text-green-700 bg-green-100'
                                            ];
                                            $status_class = $status_classes[$task['status']] ?? 'text-gray-700 bg-gray-100';
                                            $status_translations = [
                                                'pending' => 'Pendiente',
                                                'completed' => 'Completada'
                                            ];
                                            $translated_status = $status_translations[$task['status']] ?? ucfirst($task['status']);
                                        ?>
                                        <span class="px-2 py-1 font-semibold leading-tight rounded-full <?php echo $status_class; ?>">
                                            <?php echo $translated_status; ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4"><?php echo $task['created_at']; ?></td>
                                    <td class="py-3 px-4">
                                        <?php if ($task['status'] == 'pending'): ?>
                                            <a href="php/maintenance_handler.php?action=complete&id=<?php echo $task['id']; ?>" class="text-green-500 hover:text-green-700">Marcar como Completada</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">No hay tareas de mantenimiento pendientes.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Room Modal -->
    <div id="editRoomModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-gray-800 text-white p-8 rounded-lg shadow-2xl w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6">Editar Habitación</h2>
            <form action="php/room_handler.php" method="POST">
                <input type="hidden" id="editRoomId" name="id">
                <div class="mb-4">
                    <label class="block font-semibold">Tipo</label>
                    <input type="text" id="editRoomType" name="type" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                </div>
                <div class="mb-4">
                    <label class="block font-semibold">Capacidad</label>
                    <input type="number" id="editRoomCapacity" name="capacity" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                </div>
                <div class="mb-4">
                    <label class="block font-semibold">Descripción</label>
                    <textarea id="editRoomDescription" name="description" rows="3" required class="w-full p-3 border rounded-lg bg-gray-700 text-white"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block font-semibold">Precio</label>
                    <input type="number" step="0.01" id="editRoomPrice" name="price" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeEditRoomModal()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-2">Cancelar</button>
                    <button type="submit" name="update_room" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">Actualizar Habitación</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Event Modal -->
    <div id="editEventModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-gray-800 text-white p-8 rounded-lg shadow-2xl w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6">Editar Evento</h2>
            <form action="php/event_handler.php" method="POST">
                <input type="hidden" id="editEventId" name="id">
                <div class="mb-4">
                    <label for="editEventName" class="block font-semibold mb-2">Nombre</label>
                    <input type="text" id="editEventName" name="name" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                </div>
                <div class="mb-4">
                    <label for="editEventDescription" class="block font-semibold mb-2">Descripción</label>
                    <textarea id="editEventDescription" name="description" rows="3" required class="w-full p-3 border rounded-lg bg-gray-700 text-white"></textarea>
                </div>
                <div class="mb-4">
                    <label for="editEventDate" class="block font-semibold mb-2">Fecha</label>
                    <input type="date" id="editEventDate" name="date" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeEditModal()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-2">Cancelar</button>
                    <button type="submit" name="update_event" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">Actualizar Evento</button>
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

        function openEditModal(event) {
            document.getElementById('editEventId').value = event.id;
            document.getElementById('editEventName').value = event.name;
            document.getElementById('editEventDescription').value = event.description;
            document.getElementById('editEventDate').value = event.date;
            document.getElementById('editEventModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editEventModal').classList.add('hidden');
        }

        function openEditRoomModal(room) {
            document.getElementById('editRoomId').value = room.id;
            document.getElementById('editRoomType').value = room.type;
            document.getElementById('editRoomCapacity').value = room.capacity;
            document.getElementById('editRoomDescription').value = room.description;
            document.getElementById('editRoomPrice').value = room.price;
            document.getElementById('editRoomModal').classList.remove('hidden');
        }

        function closeEditRoomModal() {
            document.getElementById('editRoomModal').classList.add('hidden');
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>
