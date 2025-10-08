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

// Fetch reservations from the database
$sql = "SELECT reservations.id, users.name as user_name, rooms.type as room_type, reservations.checkin_date, reservations.checkout_date, reservations.status 
        FROM reservations 
        JOIN users ON reservations.user_id = users.id 
        JOIN rooms ON reservations.room_id = rooms.id 
        ORDER BY reservations.checkin_date ASC";
$result = $conn->query($sql);

// Fetch events
$events_sql = "SELECT id, name, description, date, image FROM events ORDER BY date DESC";
$events_result = $conn->query($events_sql);

// Fetch rooms for management
$rooms_sql = "SELECT id, type, capacity, description, price, photos FROM rooms ORDER BY type ASC";
$rooms_result = $conn->query($rooms_sql);
if (!$rooms_result) {
    die("Query failed: " . $conn->error);
}

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
    <div class="flex min-h-screen">
        <aside class="w-64 bg-gray-800 p-6 overflow-y-auto sticky top-0 h-screen">
            <img src="images/logo.png" alt="Logo" class="w-16 h-16 mb-4 mx-auto">
            <h2 class="text-xl font-bold mb-6">Menú de Administración</h2>
            <ul class="space-y-4">
                <?php if ($is_admin): ?>
                <li><a href="#reservations-section" class="block py-3 px-4 rounded-full hover:bg-gray-700 transition text-center">Reservas</a></li>
                <li><a href="#availability-section" class="block py-3 px-4 rounded-full hover:bg-gray-700 transition text-center">Disponibilidad</a></li>
                <li><a href="#users-section" class="block py-3 px-4 rounded-full hover:bg-gray-700 transition text-center">Usuarios</a></li>
                <li><a href="#rooms-section" class="block py-3 px-4 rounded-full hover:bg-gray-700 transition text-center">Habitaciones</a></li>
                <li><a href="#reports-section" class="block py-3 px-4 rounded-full hover:bg-gray-700 transition text-center">Reportes</a></li>
                <li><a href="#events-section" class="block py-3 px-4 rounded-full hover:bg-gray-700 transition text-center">Eventos</a></li>
                <?php endif; ?>
                <li><a href="#assign-maintenance-section" class="block py-3 px-4 rounded-full hover:bg-gray-700 transition text-center">Asignar Mantenimiento</a></li>
                <li><a href="#maintenance-tasks-section" class="block py-3 px-4 rounded-full hover:bg-gray-700 transition text-center">Tareas de Mantenimiento</a></li>
            </ul>
        </aside>
        <main class="flex-1 p-8 overflow-y-auto">
            <?php
            $flash_message = null;
            if (isset($_SESSION['flash_message'])) {
                $flash_message = $_SESSION['flash_message'];
                unset($_SESSION['flash_message']);
            }
            ?>
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold"><?php echo $is_admin ? 'Panel de Administración' : 'Panel de Mantenimiento'; ?></h1>
                <a href="php/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition-transform hover:scale-105">Cerrar Sesión</a>
            </div>

        <?php if ($is_admin): ?>
        <div id="reservations-section" class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Reservas</h2>
            </div>

            <!-- Add Reservation Form -->
            <form action="php/reservation_handler.php" method="POST" class="mb-8 p-4 bg-gray-700 rounded-lg">
                <h3 class="text-xl font-semibold mb-4">Agregar Nueva Reserva</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="user_id" class="block font-semibold mb-2">Cliente</label>
                        <select name="user_id" required class="w-full p-2 border rounded bg-gray-600 text-white">
                            <option value="">Seleccionar cliente...</option>
                            <?php
                            $users_sql = "SELECT id, name FROM users ORDER BY name ASC";
                            $users_result = $conn->query($users_sql);
                            while($user = $users_result->fetch_assoc()): ?>
                                <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?></option>
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
                                <tr class="hover:bg-gray-700 border-b border-gray-700">
                                    <td class="py-3 px-4 text-center"><?php echo $row['id']; ?></td>
                                    <td class="py-3 px-4 text-center"><?php echo $row['user_name']; ?></td>
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

        <div id="availability-section" class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl mb-8">
            <h2 class="text-2xl font-bold mb-6">Ver Disponibilidad</h2>
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
                <button type="submit" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">Ver Disponibilidad</button>
            </form>
            <?php
            if (isset($_GET['checkin']) && isset($_GET['checkout'])) {
                $checkin_date = $_GET['checkin'];
                $checkout_date = $_GET['checkout'];

                // Find rooms that are NOT booked during the selected dates
                $sql = "SELECT r.id, r.type, r.capacity, r.description, r.price, r.photos
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

        <div id="users-section" class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl">
            <h2 class="text-2xl font-bold mb-6">Gestionar Usuarios</h2>

            <!-- Add User Form -->
            <form action="php/user_handler.php" method="POST" class="mb-8 p-4 bg-gray-700 rounded-lg">
                <h3 class="text-xl font-semibold mb-4">Agregar Nuevo Usuario</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <input type="text" name="name" placeholder="Nombre" required class="p-2 border rounded bg-gray-600 text-white">
                    <input type="email" name="email" placeholder="Email" required class="p-2 border rounded bg-gray-600 text-white">
                    <input type="password" name="password" placeholder="Contraseña" required class="p-2 border rounded bg-gray-600 text-white">
                    <select name="role" required class="p-2 border rounded bg-gray-600 text-white">
                        <option value="user">Usuario</option>
                        <option value="admin">Admin</option>
                        <option value="maintenance">Mantenimiento</option>
                    </select>
                </div>
                <button type="submit" name="add_user" class="mt-4 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg">Agregar Usuario</button>
            </form>

            <?php
            // Fetch users to display
            $users_sql = "SELECT id, name, email, role FROM users ORDER BY name ASC";
            $users_result = $conn->query($users_sql);
            ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-gray-800">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Nombre</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Email</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Rol</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Editar</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Eliminar</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-300">
                        <?php if ($users_result->num_rows > 0): ?>
                            <?php while($user_row = $users_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-700 border-b border-gray-700">
                                    <td class="py-3 px-4 text-center"><?php echo $user_row['name']; ?></td>
                                    <td class="py-3 px-4 text-center"><?php echo $user_row['email']; ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <form action="php/user_handler.php" method="POST" class="flex items-center justify-center">
                                            <input type="hidden" name="user_id" value="<?php echo $user_row['id']; ?>">
                                            <select name="role" class="p-1 border rounded-lg text-sm bg-gray-700 text-white">
                                                <option value="user" <?php if($user_row['role'] == 'user') echo 'selected'; ?>>Usuario</option>
                                                <option value="admin" <?php if($user_row['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                                                <option value="maintenance" <?php if($user_row['role'] == 'maintenance') echo 'selected'; ?>>Mantenimiento</option>
                                            </select>
                                            <button type="submit" name="update_user_role" class="ml-2 bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-1 px-3 rounded-lg text-sm">Guardar</button>
                                        </form>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <button onclick="openEditUserModal(<?php echo htmlspecialchars(json_encode($user_row)); ?>)" class="text-blue-500 hover:text-blue-700">Editar</button>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <?php if ($_SESSION['user_id'] != $user_row['id']): ?>
                                            <a href="php/user_handler.php?delete_user=<?php echo $user_row['id']; ?>" onclick="return confirm('¿Estás seguro? Esta acción no se puede deshacer.')" class="text-red-500 hover:text-red-700">Eliminar</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">No hay usuarios encontrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="rooms-section" class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl mt-8">
            <h2 class="text-2xl font-bold mb-6">Gestionar Habitaciones</h2>

            <!-- Add Room Form -->
            <form action="php/room_handler.php" method="POST" enctype="multipart/form-data" class="mb-8 p-4 bg-gray-700 rounded-lg">
                <h3 class="text-xl font-semibold mb-4">Agregar Nueva Habitación</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <input type="text" name="type" placeholder="Tipo (ej. Individual)" required class="p-2 border rounded bg-gray-600 text-white">
                    <input type="number" name="capacity" placeholder="Capacidad" required class="p-2 border rounded bg-gray-600 text-white">
                    <input type="text" name="description" placeholder="Descripción" required class="p-2 border rounded bg-gray-600 text-white">
                </div>
                <div class="mt-4">
                    <label for="room_image" class="block font-semibold mb-2">Imagen de la Habitación</label>
                    <input type="file" name="image" id="room_image" accept="image/*" class="p-2 border rounded bg-gray-600 text-white">
                </div>
                <button type="submit" name="add_room" class="mt-4 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg">Agregar Habitación</button>
            </form>

            <!-- Rooms Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-gray-800">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Imagen</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Tipo</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Descripción</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Capacidad</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-300">
                        <?php if ($rooms_result->num_rows > 0): ?>
                            <?php while($room = $rooms_result->fetch_assoc()): ?>
                                <?php $photos = json_decode($room['photos'], true); ?>
                                <tr class="hover:bg-gray-700 border-b border-gray-700">
                                    <td class="py-3 px-4 text-center"><img src="images/<?php echo htmlspecialchars($photos[0] ?? 'default_room.jpg'); ?>" alt="Room Image" class="w-16 h-16 object-cover rounded"></td>
                                    <td class="py-3 px-4 text-center capitalize"><?php echo $room['type']; ?></td>
                                    <td class="py-3 px-4 text-center"><?php echo $room['description']; ?></td>
                                    <td class="py-3 px-4 text-center"><?php echo $room['capacity']; ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <button onclick="openEditRoomModal(<?php echo htmlspecialchars(json_encode($room)); ?>)" class="text-blue-500 hover:text-blue-700 mr-2">Editar</button>
                                        <a href="php/room_handler.php?delete_room=<?php echo $room['id']; ?>" onclick="return confirm('¿Estás seguro? Esto no se puede hacer si la habitación tiene reservas.')" class="text-red-500 hover:text-red-700">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4">No hay habitaciones para mostrar.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="reports-section" class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl mt-8">
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
                <canvas id="reservationsChart" width="400" height="200"></canvas>
            </div>
        </div>

        <div id="events-section" class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl mt-8">
            <h2 class="text-2xl font-bold mb-6">Gestionar Eventos</h2>
            
            <!-- Add Event Form -->
            <form action="php/event_handler.php" method="POST" enctype="multipart/form-data" class="mb-8 p-4 bg-gray-700 rounded-lg">
                <h3 class="text-xl font-semibold mb-4">Agregar Nuevo Evento</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <input type="text" name="name" placeholder="Nombre del Evento" required class="p-2 border rounded bg-gray-600 text-white">
                    <input type="text" name="description" placeholder="Descripción" required class="p-2 border rounded bg-gray-600 text-white">
                    <input type="date" name="date" required class="p-2 border rounded bg-gray-600 text-white">
                    <input type="file" name="image" accept="image/*" class="p-2 border rounded bg-gray-600 text-white">
                </div>
                <button type="submit" name="add_event" class="mt-4 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg">Agregar Evento</button>
            </form>

            <!-- Events Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-gray-800">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Imagen</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Nombre</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Descripción</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Fecha</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-300">
                        <?php if ($events_result->num_rows > 0): ?>
                            <?php while($event = $events_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-700 border-b border-gray-700">
                                    <td class="py-3 px-4 text-center"><img src="images/<?php echo htmlspecialchars($event['image'] ?? 'default_event.jpg'); ?>" alt="Event Image" class="w-16 h-16 object-cover rounded"></td>
                                    <td class="py-3 px-4 text-center"><?php echo $event['name']; ?></td>
                                    <td class="py-3 px-4 text-center"><?php echo $event['description']; ?></td>
                                    <td class="py-3 px-4 text-center"><?php echo $event['date']; ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($event)); ?>)" class="text-blue-500 hover:text-blue-700 mr-2">Editar</button>
                                        <a href="php/event_handler.php?delete_event=<?php echo $event['id']; ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar este evento?');" class="text-red-500 hover:text-red-700">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4">No hay eventos para mostrar.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <div id="assign-maintenance-section" class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl mt-8">
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
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Habitación</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Asignado a</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Estado</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Fecha de Creación</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-300">
                        <?php if ($maintenance_result->num_rows > 0): ?>
                            <?php while($task = $maintenance_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-700 border-b border-gray-700">
                                    <td class="py-3 px-4 text-center"><?php echo $task['room_type']; ?></td>
                                    <td class="py-3 px-4 text-center"><?php echo $task['staff_name']; ?></td>
                                    <td class="py-3 px-4 text-center">
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
                                    <td class="py-3 px-4 text-center"><?php echo $task['created_at']; ?></td>
                                    <td class="py-3 px-4 text-center">
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
        </main>
    </div>

    <!-- Edit Room Modal -->
    <div id="editRoomModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-gray-800 text-white p-8 rounded-lg shadow-2xl w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6">Editar Habitación</h2>
            <form action="php/room_handler.php" method="POST" enctype="multipart/form-data">
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
                    <label class="block font-semibold">Imagen (opcional)</label>
                    <input type="file" name="image" accept="image/*" class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                    <div id="currentImage" class="mt-2"></div>
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
            <form action="php/event_handler.php" method="POST" enctype="multipart/form-data">
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
                <div class="mb-4">
                    <label for="editEventImage" class="block font-semibold mb-2">Imagen</label>
                    <input type="file" id="editEventImage" name="image" accept="image/*" class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                    <div id="currentEventImage" class="mt-2"></div>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeEditModal()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-2">Cancelar</button>
                    <button type="submit" name="update_event" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">Actualizar Evento</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Flash Message Modal -->
    <div id="flashModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center <?php echo $flash_message ? '' : 'hidden'; ?>">
        <div class="bg-gray-800 text-white p-8 rounded-lg shadow-2xl w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6"><?php echo $flash_message ? ($flash_message['status'] == 'success' ? 'Éxito' : 'Error') : ''; ?></h2>
            <p class="mb-6"><?php echo $flash_message ? $flash_message['text'] : ''; ?></p>
            <div class="flex justify-end">
                <button onclick="closeFlashModal()" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">Aceptar</button>
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
                    <label for="editReservationUser" class="block font-semibold mb-2">Cliente</label>
                    <select id="editReservationUser" name="user_id" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                        <option value="">Seleccionar cliente...</option>
                        <?php
                        $users_sql = "SELECT id, name FROM users ORDER BY name ASC";
                        $users_result = $conn->query($users_sql);
                        while($user = $users_result->fetch_assoc()): ?>
                            <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?></option>
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

    <!-- Edit User Modal -->
    <div id="editUserModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-gray-800 text-white p-8 rounded-lg shadow-2xl w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6">Editar Usuario</h2>
            <form action="php/user_handler.php" method="POST">
                <input type="hidden" id="editUserId" name="id">
                <div class="mb-4">
                    <label for="editUserName" class="block font-semibold mb-2">Nombre</label>
                    <input type="text" id="editUserName" name="name" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                </div>
                <div class="mb-4">
                    <label for="editUserEmail" class="block font-semibold mb-2">Email</label>
                    <input type="email" id="editUserEmail" name="email" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                </div>
                <div class="mb-4">
                    <label for="editUserRole" class="block font-semibold mb-2">Rol</label>
                    <select id="editUserRole" name="role" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                        <option value="user">Usuario</option>
                        <option value="admin">Admin</option>
                        <option value="maintenance">Mantenimiento</option>
                    </select>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeEditUserModal()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-2">Cancelar</button>
                    <button type="submit" name="update_user" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">Actualizar Usuario</button>
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

        const chartCtx = document.getElementById('reservationsChart').getContext('2d');
        console.log(<?php echo json_encode($room_types); ?>, <?php echo json_encode($reservation_counts); ?>);
        const reservationsChart = new Chart(chartCtx, {
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
            document.getElementById('currentEventImage').innerHTML = '<img src="images/' + (event.image || 'default_event.jpg') + '" alt="Current Image" class="w-16 h-16 object-cover rounded">';
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
            const photos = room.photos ? JSON.parse(room.photos) : ['default_room.jpg'];
            document.getElementById('currentImage').innerHTML = '<img src="images/' + photos[0] + '" alt="Current Image" class="w-16 h-16 object-cover rounded">';
            document.getElementById('editRoomModal').classList.remove('hidden');
        }

        function closeEditRoomModal() {
            document.getElementById('editRoomModal').classList.add('hidden');
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

        function openEditUserModal(user) {
            document.getElementById('editUserId').value = user.id;
            document.getElementById('editUserName').value = user.name;
            document.getElementById('editUserEmail').value = user.email;
            document.getElementById('editUserRole').value = user.role;
            document.getElementById('editUserModal').classList.remove('hidden');
        }

        function closeEditUserModal() {
            document.getElementById('editUserModal').classList.add('hidden');
        }

        function closeFlashModal() {
            document.getElementById('flashModal').classList.add('hidden');
        }

        // Auto-close flash modal after 3 seconds
        if (document.getElementById('flashModal') && !document.getElementById('flashModal').classList.contains('hidden')) {
            setTimeout(() => {
                closeFlashModal();
            }, 3000);
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>
