<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include 'php/db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch floors for management
$floors_sql = "SELECT f.id, f.floor_number, f.name, f.description,
                      COUNT(r.id) as room_count,
                      COUNT(CASE WHEN r.status = 'enabled' THEN 1 END) as active_rooms
               FROM floors f
               LEFT JOIN rooms r ON f.id = r.floor_id
               GROUP BY f.id, f.floor_number, f.name, f.description
               ORDER BY f.floor_number ASC";
$floors_result = $conn->query($floors_sql);
if (!$floors_result) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pisos - Panel de Administración - INDET</title>

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
                <h1 class="text-3xl font-bold">Pisos</h1>
            </div>
            <div>
                <a href="admin.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-4">
                    <i class="fas fa-arrow-left mr-2"></i>Volver al Menú
                </a>
                <a href="php/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg">Cerrar Sesión</a>
            </div>
        </div>

        <div class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl mt-8">
            <h2 class="text-2xl font-bold mb-6">Gestionar Pisos</h2>

            <!-- Add Floor Form -->
            <form action="php/inventory_handler.php" method="POST" class="mb-8 p-4 bg-gray-700 rounded-lg">
                <h3 class="text-xl font-semibold mb-4">Agregar Nuevo Piso</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="number" name="floor_number" placeholder="Número del Piso" required class="p-2 border rounded bg-gray-600 text-white">
                    <input type="text" name="name" placeholder="Nombre del Piso" required class="p-2 border rounded bg-gray-600 text-white">
                    <input type="text" name="description" placeholder="Descripción" class="p-2 border rounded bg-gray-600 text-white">
                </div>
                <button type="submit" name="add_floor" class="mt-4 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg">Agregar Piso</button>
            </form>

            <!-- Search Floor -->
            <div class="mb-6">
                <input type="text" id="floor_search" placeholder="Buscar piso por nombre o número..." class="p-2 border rounded bg-gray-700 text-white w-full md:w-1/3">
            </div>

            <!-- Floors Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-gray-800" id="floors_table">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Número</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Nombre</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Descripción</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Habitaciones</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Activas</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-300">
                        <?php if ($floors_result->num_rows > 0): ?>
                            <?php while($floor = $floors_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-700 border-b border-gray-700 floor-row" data-floor-name="<?php echo strtolower($floor['name']); ?>" data-floor-number="<?php echo $floor['floor_number']; ?>">
                                    <td class="py-3 px-4 text-center"><?php echo $floor['floor_number']; ?></td>
                                    <td class="py-3 px-4 text-center"><?php echo htmlspecialchars($floor['name']); ?></td>
                                    <td class="py-3 px-4 text-center"><?php echo htmlspecialchars($floor['description']); ?></td>
                                    <td class="py-3 px-4 text-center"><?php echo $floor['room_count']; ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-1 rounded-full text-xs <?php echo $floor['active_rooms'] > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                            <?php echo $floor['active_rooms']; ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <button onclick="openEditFloorModal(<?php echo htmlspecialchars(json_encode($floor)); ?>)" class="text-blue-500 hover:text-blue-700 mr-2">Editar</button>
                                        <a href="admin_inventory.php?floor_id=<?php echo $floor['id']; ?>" class="text-purple-500 hover:text-purple-700 mr-2">Ver Inventario</a>
                                        <a href="php/inventory_handler.php?delete_floor=<?php echo $floor['id']; ?>" onclick="return confirm('¿Estás seguro? Esto eliminará todas las habitaciones e inventario del piso.')" class="text-red-500 hover:text-red-700">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4">No hay pisos registrados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Floor Modal -->
    <div id="editFloorModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-gray-800 text-white p-8 rounded-lg shadow-2xl w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6">Editar Piso</h2>
            <form action="php/inventory_handler.php" method="POST">
                <input type="hidden" id="editFloorId" name="id">
                <div class="mb-4">
                    <label class="block font-semibold">Número del Piso</label>
                    <input type="number" id="editFloorNumber" name="floor_number" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                </div>
                <div class="mb-4">
                    <label class="block font-semibold">Nombre del Piso</label>
                    <input type="text" id="editFloorName" name="name" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                </div>
                <div class="mb-4">
                    <label class="block font-semibold">Descripción</label>
                    <textarea id="editFloorDescription" name="description" rows="3" class="w-full p-3 border rounded-lg bg-gray-700 text-white"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeEditFloorModal()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-2">Cancelar</button>
                    <button type="submit" name="update_floor" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">Actualizar Piso</button>
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

        function openEditFloorModal(floor) {
            document.getElementById('editFloorId').value = floor.id;
            document.getElementById('editFloorNumber').value = floor.floor_number;
            document.getElementById('editFloorName').value = floor.name;
            document.getElementById('editFloorDescription').value = floor.description;
            document.getElementById('editFloorModal').classList.remove('hidden');
        }

        function closeEditFloorModal() {
            document.getElementById('editFloorModal').classList.add('hidden');
        }

        // Search functionality
        document.getElementById('floor_search').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.floor-row');

            rows.forEach(row => {
                const floorName = row.getAttribute('data-floor-name');
                const floorNumber = row.getAttribute('data-floor-number');
                if (floorName.includes(searchTerm) || floorNumber.includes(searchTerm)) {
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