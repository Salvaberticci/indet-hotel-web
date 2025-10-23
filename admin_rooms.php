<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include 'php/db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch rooms for management
$rooms_sql = "SELECT r.id, r.type, r.capacity, r.description, r.photos, r.floor_id, r.status, f.name as floor_name FROM rooms r JOIN floors f ON r.floor_id = f.id ORDER BY f.floor_number ASC, r.type ASC";
$rooms_result = $conn->query($rooms_sql);
if (!$rooms_result) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Habitaciones - Panel de Administración - INDET</title>

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
                <h1 class="text-3xl font-bold">Habitaciones</h1>
            </div>
            <div>
                <a href="admin.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-4">
                    <i class="fas fa-arrow-left mr-2"></i>Volver al Menú
                </a>
                <a href="php/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg">Cerrar Sesión</a>
            </div>
        </div>

        <div class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl mt-8">
            <h2 class="text-2xl font-bold mb-6">Gestionar Habitaciones</h2>

            <!-- Add Room Form -->
            <form action="php/room_handler.php" method="POST" enctype="multipart/form-data" class="mb-8 p-4 bg-gray-700 rounded-lg">
                <h3 class="text-xl font-semibold mb-4">Agregar Nueva Habitación</h3>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <input type="text" name="room_id" placeholder="ID (ej. 001)" required class="p-2 border rounded bg-gray-600 text-white">
                    <input type="text" name="type" placeholder="Tipo (ej. Individual)" required class="p-2 border rounded bg-gray-600 text-white">
                    <input type="number" name="capacity" placeholder="Capacidad" required class="p-2 border rounded bg-gray-600 text-white">
                    <select name="floor_id" required class="p-2 border rounded bg-gray-600 text-white">
                        <option value="">Seleccionar Piso</option>
                        <?php
                        $floors_sql = "SELECT id, name FROM floors ORDER BY floor_number ASC";
                        $floors_result = $conn->query($floors_sql);
                        while($floor = $floors_result->fetch_assoc()) {
                            echo "<option value='" . $floor['id'] . "'>" . htmlspecialchars($floor['name']) . "</option>";
                        }
                        ?>
                    </select>
                    <input type="text" name="description" placeholder="Descripción" required class="p-2 border rounded bg-gray-600 text-white">
                </div>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="room_images" class="block font-semibold mb-2">Imágenes de la Habitación (múltiples)</label>
                        <input type="file" name="images[]" id="room_images" accept="image/*" multiple class="p-2 border rounded bg-gray-600 text-white">
                    </div>
                    <div>
                        <label for="room_videos" class="block font-semibold mb-2">Vídeos de la Habitación (múltiples)</label>
                        <input type="file" name="videos[]" id="room_videos" accept="video/*" multiple class="p-2 border rounded bg-gray-600 text-white">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block font-semibold mb-2">Estado</label>
                    <select name="status" required class="p-2 border rounded bg-gray-600 text-white">
                        <option value="enabled">Habilitada</option>
                        <option value="disabled">No Habilitada</option>
                    </select>
                </div>
                <button type="submit" name="add_room" class="mt-4 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg">Agregar Habitación</button>
            </form>

            <!-- Search Room -->
            <div class="mb-6">
                <input type="text" id="room_search" placeholder="Buscar habitación por ID..." class="p-2 border rounded bg-gray-700 text-white w-full md:w-1/3">
            </div>

            <!-- Rooms Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-gray-800" id="rooms_table">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">ID</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Imagen</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Tipo</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Piso</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Descripción</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Capacidad</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Estado</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-300">
                        <?php if ($rooms_result->num_rows > 0): ?>
                            <?php while($room = $rooms_result->fetch_assoc()): ?>
                                <?php $photos = json_decode($room['photos'], true); ?>
                                <tr class="hover:bg-gray-700 border-b border-gray-700 room-row" data-room-id="<?php echo $room['id']; ?>">
                                    <td class="py-3 px-4 text-center"><?php echo $room['id']; ?></td>
                                    <td class="py-3 px-4 text-center"><img src="images/<?php echo htmlspecialchars($photos[0] ?? 'default_room.jpg'); ?>" alt="Room Image" class="w-16 h-16 object-cover rounded"></td>
                                    <td class="py-3 px-4 text-center capitalize"><?php echo $room['type']; ?></td>
                                    <td class="py-3 px-4 text-center"><?php echo htmlspecialchars($room['floor_name']); ?></td>
                                    <td class="py-3 px-4 text-center"><?php echo $room['description']; ?></td>
                                    <td class="py-3 px-4 text-center"><?php echo $room['capacity']; ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-1 rounded-full text-xs <?php echo ($room['status'] ?? 'enabled') === 'enabled' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo ($room['status'] ?? 'enabled') === 'enabled' ? 'Habilitada' : 'No Habilitada'; ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <button onclick="openEditRoomModal(<?php echo htmlspecialchars(json_encode($room)); ?>)" class="text-blue-500 hover:text-blue-700 mr-2">Editar</button>
                                        <a href="admin_room_inventory.php?room_id=<?php echo $room['id']; ?>" class="text-green-500 hover:text-green-700 mr-2">Crear Inventario</a>
                                        <a href="php/room_handler.php?delete_room=<?php echo $room['id']; ?>" onclick="return confirm('¿Estás seguro? Esto no se puede hacer si la habitación tiene reservas.')" class="text-red-500 hover:text-red-700">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center py-4">No hay habitaciones para mostrar.</td></tr>
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
                    <label class="block font-semibold">Piso</label>
                    <select id="editRoomFloor" name="floor_id" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                        <option value="">Seleccionar Piso</option>
                        <?php
                        $edit_floors_sql = "SELECT id, name FROM floors ORDER BY floor_number ASC";
                        $edit_floors_result = $conn->query($edit_floors_sql);
                        while($floor = $edit_floors_result->fetch_assoc()) {
                            echo "<option value='" . $floor['id'] . "'>" . htmlspecialchars($floor['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block font-semibold">Descripción</label>
                    <textarea id="editRoomDescription" name="description" rows="3" required class="w-full p-3 border rounded-lg bg-gray-700 text-white"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block font-semibold">Estado</label>
                    <select id="editRoomStatus" name="status" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                        <option value="enabled">Habilitada</option>
                        <option value="disabled">No Habilitada</option>
                    </select>
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

        function openEditRoomModal(room) {
            document.getElementById('editRoomId').value = room.id;
            document.getElementById('editRoomType').value = room.type;
            document.getElementById('editRoomCapacity').value = room.capacity;
            // Note: room.floor_id should be passed from the query result
            document.getElementById('editRoomFloor').value = room.floor_id || room.floor;
            document.getElementById('editRoomDescription').value = room.description;
            document.getElementById('editRoomStatus').value = room.status || 'enabled';
            const photos = room.photos ? JSON.parse(room.photos) : ['default_room.jpg'];
            document.getElementById('currentImage').innerHTML = '<img src="images/' + photos[0] + '" alt="Current Image" class="w-16 h-16 object-cover rounded">';
            document.getElementById('editRoomModal').classList.remove('hidden');
        }

        function closeEditRoomModal() {
            document.getElementById('editRoomModal').classList.add('hidden');
        }

        // Search functionality
        document.getElementById('room_search').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.room-row');

            rows.forEach(row => {
                const roomId = row.getAttribute('data-room-id').toLowerCase();
                if (roomId.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>