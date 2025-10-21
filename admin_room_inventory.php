<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include 'php/db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get selected room from GET or default to first room
$selected_room_id = isset($_GET['room_id']) ? $_GET['room_id'] : null;

// Fetch all rooms for dropdown
$rooms_sql = "SELECT id, type, capacity FROM rooms WHERE status = 'enabled' ORDER BY id ASC";
$rooms_result = $conn->query($rooms_sql);

// If no room selected, select the first one
if (!$selected_room_id && $rooms_result->num_rows > 0) {
    $first_room = $rooms_result->fetch_assoc();
    $selected_room_id = $first_room['id'];
    $rooms_result->data_seek(0); // Reset pointer
}

// Fetch inventory for the selected room
$inventory_sql = "SELECT * FROM room_inventory WHERE room_id = ? ORDER BY item_name ASC";
$inventory_stmt = $conn->prepare($inventory_sql);
$inventory_stmt->bind_param("s", $selected_room_id);
$inventory_stmt->execute();
$inventory_result = $inventory_stmt->get_result();

// Get selected room info
$room_info_sql = "SELECT type, capacity FROM rooms WHERE id = ?";
$room_info_stmt = $conn->prepare($room_info_sql);
$room_info_stmt->bind_param("s", $selected_room_id);
$room_info_stmt->execute();
$room_info_result = $room_info_stmt->get_result();
$room_info = $room_info_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario de Habitaciones - Panel de Administración - INDET</title>

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
                <h1 class="text-3xl font-bold">Inventario de Habitación <?php echo $selected_room_id; ?> - <?php echo htmlspecialchars($room_info['type']); ?></h1>
            </div>
            <div>
                <a href="admin.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-4">
                    <i class="fas fa-arrow-left mr-2"></i>Volver al Menú
                </a>
                <a href="php/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg">Cerrar Sesión</a>
            </div>
        </div>

        <!-- Room Filter -->
        <div class="mb-6">
            <label for="room_select" class="block text-sm font-medium mb-2">Seleccionar Habitación:</label>
            <select id="room_select" onchange="changeRoom(this.value)" class="p-2 border rounded bg-gray-700 text-white">
                <?php while($room = $rooms_result->fetch_assoc()): ?>
                    <option value="<?php echo $room['id']; ?>" <?php echo $room['id'] == $selected_room_id ? 'selected' : ''; ?>>
                        Habitación <?php echo $room['id']; ?> - <?php echo htmlspecialchars($room['type']); ?> (Capacidad: <?php echo $room['capacity']; ?>)
                    </option>
                <?php endwhile; ?>
            </select>
            <button onclick="printInventory()" class="ml-4 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">
                <i class="fas fa-print mr-2"></i>Imprimir Reporte
            </button>
        </div>

        <div class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl mb-8">
            <h2 class="text-2xl font-bold mb-6">Gestionar Inventario</h2>

            <!-- Add Item Form -->
            <form action="php/room_inventory_handler.php" method="POST" class="mb-8 p-4 bg-gray-700 rounded-lg">
                <h3 class="text-xl font-semibold mb-4">Agregar Nuevo Item</h3>
                <input type="hidden" name="room_id" value="<?php echo $selected_room_id; ?>">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="text" name="item_name" placeholder="Nombre del Item" required class="p-2 border rounded bg-gray-600 text-white">
                    <input type="number" name="quantity" placeholder="Cantidad" min="0" required class="p-2 border rounded bg-gray-600 text-white">
                    <input type="text" name="description" placeholder="Descripción" required class="p-2 border rounded bg-gray-600 text-white">
                </div>
                <button type="submit" name="add_item" class="mt-4 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg">Agregar Item</button>
            </form>

            <!-- Inventory Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-gray-800" id="inventory-table">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Nombre del Item</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Cantidad</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Descripción</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Fecha de Creación</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-300">
                        <?php if ($inventory_result->num_rows > 0): ?>
                            <?php while($item = $inventory_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-700 border-b border-gray-700">
                                    <td class="py-3 px-4 text-center"><?php echo htmlspecialchars($item['item_name']); ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center">
                                            <button type="button" onclick="updateQuantity('<?php echo $item['id']; ?>', -1)" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded">-</button>
                                            <span id="quantity-<?php echo $item['id']; ?>" class="mx-2"><?php echo $item['quantity']; ?></span>
                                            <button type="button" onclick="updateQuantity('<?php echo $item['id']; ?>', 1)" class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded">+</button>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-center"><?php echo htmlspecialchars($item['description']); ?></td>
                                    <td class="py-3 px-4 text-center"><?php echo date('d/m/Y', strtotime($item['created_at'])); ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <button onclick="openEditItemModal(<?php echo htmlspecialchars(json_encode($item)); ?>)" class="text-blue-500 hover:text-blue-700 mr-2">Editar</button>
                                        <a href="php/room_inventory_handler.php?delete_item=<?php echo $item['id']; ?>&room_id=<?php echo $selected_room_id; ?>" onclick="return confirm('¿Estás seguro?')" class="text-red-500 hover:text-red-700">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4">No hay items en el inventario de esta habitación.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div id="editItemModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-gray-800 text-white p-8 rounded-lg shadow-2xl w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6">Editar Item</h2>
            <form action="php/room_inventory_handler.php" method="POST">
                <input type="hidden" id="editItemId" name="id">
                <input type="hidden" name="room_id" value="<?php echo $selected_room_id; ?>">
                <div class="mb-4">
                    <label class="block font-semibold">Nombre del Item</label>
                    <input type="text" id="editItemName" name="item_name" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                </div>
                <div class="mb-4">
                    <label class="block font-semibold">Cantidad</label>
                    <input type="number" id="editItemQuantity" name="quantity" min="0" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                </div>
                <div class="mb-4">
                    <label class="block font-semibold">Descripción</label>
                    <textarea id="editItemDescription" name="description" rows="3" required class="w-full p-3 border rounded-lg bg-gray-700 text-white"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeEditItemModal()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-2">Cancelar</button>
                    <button type="submit" name="update_item" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">Actualizar Item</button>
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

        function changeRoom(roomId) {
            window.location.href = 'admin_room_inventory.php?room_id=' + roomId;
        }

        function updateQuantity(itemId, delta) {
            const quantityElement = document.getElementById('quantity-' + itemId);
            let quantity = parseInt(quantityElement.textContent) + delta;
            if (quantity < 0) quantity = 0;

            // Update via AJAX
            fetch('php/room_inventory_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'update_quantity=1&item_id=' + itemId + '&quantity=' + quantity
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    quantityElement.textContent = quantity;
                } else {
                    alert('Error al actualizar cantidad');
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function openEditItemModal(item) {
            document.getElementById('editItemId').value = item.id;
            document.getElementById('editItemName').value = item.item_name;
            document.getElementById('editItemQuantity').value = item.quantity;
            document.getElementById('editItemDescription').value = item.description;
            document.getElementById('editItemModal').classList.remove('hidden');
        }

        function closeEditItemModal() {
            document.getElementById('editItemModal').classList.add('hidden');
        }

        function printInventory() {
            const printWindow = window.open('', '_blank');
            const roomId = '<?php echo $selected_room_id; ?>';
            const roomType = '<?php echo htmlspecialchars($room_info['type']); ?>';

            let content = `
                <html>
                <head>
                    <title>Reporte de Inventario - Habitación ${roomId}</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        h1 { color: #333; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                        .header { text-align: center; margin-bottom: 30px; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Reporte de Inventario</h1>
                        <h2>Habitación ${roomId} - ${roomType}</h2>
                        <p>Fecha: ${new Date().toLocaleDateString()}</p>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre del Item</th>
                                <th>Cantidad</th>
                                <th>Descripción</th>
                                <th>Fecha de Creación</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            // Add table rows
            const rows = document.querySelectorAll('#inventory-table tbody tr');
            rows.forEach(row => {
                if (row.cells.length >= 5) {
                    content += `
                        <tr>
                            <td>${row.cells[0].textContent}</td>
                            <td>${row.cells[1].querySelector('span').textContent}</td>
                            <td>${row.cells[2].textContent}</td>
                            <td>${row.cells[3].textContent}</td>
                        </tr>
                    `;
                }
            });

            content += `
                        </tbody>
                    </table>
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