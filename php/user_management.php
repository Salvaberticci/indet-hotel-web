<?php
session_start();
include 'db.php';

// Admin access check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle form submissions for CRUD operations
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$user_id = $_POST['user_id'] ?? $_GET['user_id'] ?? null;

// Create or Update User
if ($action === 'save') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = $_POST['password'];

    if ($user_id) { // Update
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $email, $role, $hashed_password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $email, $role, $user_id);
        }
    } else { // Create
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, role, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $role, $hashed_password);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: user_management.php");
    exit();
}

// Delete User
if ($action === 'delete' && $user_id) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: user_management.php");
    exit();
}

// Fetch all users for display
$users_result = $conn->query("SELECT id, name, email, role FROM users ORDER BY name ASC");

// Fetch user data for editing
$edit_user = null;
if ($action === 'edit' && $user_id) {
    $stmt = $conn->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_user = $result->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - INDET</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800 font-poppins">
    <div class="container mx-auto p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Gestión de Usuarios</h1>
            <a href="../admin.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg">Volver al Panel</a>
        </div>

        <!-- Add/Edit User Form -->
        <div class="bg-white p-6 rounded-xl shadow-2xl mb-8">
            <h2 class="text-2xl font-bold mb-6"><?php echo $edit_user ? 'Editar Usuario' : 'Añadir Nuevo Usuario'; ?></h2>
            <form action="user_management.php" method="POST">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="user_id" value="<?php echo $edit_user['id'] ?? ''; ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block font-semibold mb-2">Nombre</label>
                        <input type="text" name="name" value="<?php echo $edit_user['name'] ?? ''; ?>" required class="w-full p-3 border rounded-lg">
                    </div>
                    <div>
                        <label for="email" class="block font-semibold mb-2">Email</label>
                        <input type="email" name="email" value="<?php echo $edit_user['email'] ?? ''; ?>" required class="w-full p-3 border rounded-lg">
                    </div>
                    <div>
                        <label for="password" class="block font-semibold mb-2">Contraseña <?php echo $edit_user ? '(dejar en blanco para no cambiar)' : ''; ?></label>
                        <input type="password" name="password" <?php echo !$edit_user ? 'required' : ''; ?> class="w-full p-3 border rounded-lg">
                    </div>
                    <div>
                        <label for="role" class="block font-semibold mb-2">Rol</label>
                        <select name="role" required class="w-full p-3 border rounded-lg">
                            <option value="client" <?php echo (isset($edit_user['role']) && $edit_user['role'] == 'client') ? 'selected' : ''; ?>>Cliente</option>
                            <option value="admin" <?php echo (isset($edit_user['role']) && $edit_user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-6 rounded-lg">Guardar Usuario</button>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white p-6 rounded-xl shadow-2xl">
            <h2 class="text-2xl font-bold mb-6">Lista de Usuarios</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Nombre</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Email</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Rol</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php while($row = $users_result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-100">
                                <td class="py-3 px-4"><?php echo $row['name']; ?></td>
                                <td class="py-3 px-4"><?php echo $row['email']; ?></td>
                                <td class="py-3 px-4"><?php echo ucfirst($row['role']); ?></td>
                                <td class="py-3 px-4">
                                    <a href="user_management.php?action=edit&user_id=<?php echo $row['id']; ?>" class="text-blue-500 hover:text-blue-700 mr-4">Editar</a>
                                    <a href="user_management.php?action=delete&user_id=<?php echo $row['id']; ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar a este usuario?');" class="text-red-500 hover:text-red-700">Eliminar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
