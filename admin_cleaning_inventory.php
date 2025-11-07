<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include 'php/db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// No longer need floor-specific variables since we're using a single cleaning room
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuarto de Faenas - Panel de Administración - INDET</title>

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
                <h1 class="text-3xl font-bold">Cuarto de Faenas</h1>
            </div>
            <div>
                <a href="admin.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-4">
                    <i class="fas fa-arrow-left mr-2"></i>Volver al Menú
                </a>
                <a href="php/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg">Cerrar Sesión</a>
            </div>
        </div>


        <!-- Cleaning Inventory Management -->
        <div class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl mb-8">
            <h2 class="text-2xl font-bold mb-6">Cuarto de Faenas - Inventario de Productos de Limpieza</h2>

            <!-- Add Cleaning Item Form -->
            <form action="php/cleaning_inventory_handler.php" method="POST" class="mb-8 p-4 bg-gray-700 rounded-lg">
                <h3 class="text-xl font-semibold mb-4">Agregar Nuevo Producto de Limpieza</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="text" name="item_name" placeholder="Nombre del Producto" required class="p-2 border rounded bg-gray-600 text-white">
                    <input type="number" name="quantity" placeholder="Cantidad" min="0" required class="p-2 border rounded bg-gray-600 text-white">
                    <input type="text" name="description" placeholder="Descripción" required class="p-2 border rounded bg-gray-600 text-white">
                </div>
                <button type="submit" name="add_item" class="mt-4 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg">Agregar Producto</button>
            </form>

            <!-- Cleaning Inventory Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-gray-800" id="cleaning-inventory-table">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">ID</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Nombre del Producto</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Cantidad</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Descripción</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Fecha de Creación</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-300">
                        <?php
                        $cleaning_inventory_sql = "SELECT * FROM cleaning_inventory ORDER BY item_name ASC";
                        $cleaning_inventory_result = $conn->query($cleaning_inventory_sql);
                        if ($cleaning_inventory_result->num_rows > 0): ?>
                            <?php while($item = $cleaning_inventory_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-700 border-b border-gray-700">
                                    <td class="py-3 px-4 text-center"><?php echo $item['id']; ?></td>
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
                                        <a href="php/cleaning_inventory_handler.php?delete_item=<?php echo $item['id']; ?>" onclick="return confirm('¿Estás seguro?')" class="text-red-500 hover:text-red-700">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4">No hay productos de limpieza en el inventario.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div id="editItemModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-gray-800 text-white p-8 rounded-lg shadow-2xl w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6">Editar Producto de Limpieza</h2>
            <form action="php/cleaning_inventory_handler.php" method="POST">
                <input type="hidden" id="editItemId" name="id">
                <!-- No longer need floor_id since we use single cleaning room -->
                <div class="mb-4">
                    <label class="block font-semibold">Nombre del Producto</label>
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
                    <button type="submit" name="update_item" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">Actualizar Producto</button>
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

        function changeFloor(floorId) {
            if (floorId === "") {
                window.location.href = 'admin_cleaning_inventory.php';
            } else {
                window.location.href = 'admin_cleaning_inventory.php?floor_id=' + floorId;
            }
        }

        function updateQuantity(itemId, delta) {
            const quantityElement = document.getElementById('quantity-' + itemId);
            let quantity = parseInt(quantityElement.textContent) + delta;
            if (quantity < 0) quantity = 0;

            // Update via AJAX
            fetch('php/cleaning_inventory_handler.php', {
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
    </script>
</body>
</html>
<?php
$conn->close();
?>