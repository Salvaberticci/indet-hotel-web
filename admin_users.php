<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include 'php/db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch users to display
$users_sql = "SELECT id, name, email, role FROM users ORDER BY name ASC";
$users_result = $conn->query($users_sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Panel de Administración - INDET</title>

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
                <h1 class="text-3xl font-bold">Usuarios</h1>
            </div>
            <div>
                <a href="admin.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-4">Volver al Menú</a>
                <a href="php/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg">Cerrar Sesión</a>
            </div>
        </div>

        <div class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl">
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
    </script>
</body>
</html>
<?php
$conn->close();
?>