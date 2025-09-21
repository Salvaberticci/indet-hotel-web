<?php
session_start();
include 'php/db.php';

// Access control: only maintenance and admin users
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'maintenance'])) {
    header("Location: login.php");
    exit();
}

// Handle task completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'])) {
    $task_id = intval($_POST['task_id']);
    $completed_at = date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare("UPDATE maintenance_tasks SET status = 'completed', completed_at = ? WHERE id = ?");
    $stmt->bind_param("si", $completed_at, $task_id);
    $stmt->execute();
    $stmt->close();

    // Also update room status to 'available'
    $room_id_query = $conn->prepare("SELECT room_id FROM maintenance_tasks WHERE id = ?");
    $room_id_query->bind_param("i", $task_id);
    $room_id_query->execute();
    $result = $room_id_query->get_result();
    if($result->num_rows > 0) {
        $room_id = $result->fetch_assoc()['room_id'];
        $current_date = date('Y-m-d');
        $update_room_stmt = $conn->prepare("UPDATE room_status SET status = 'available', date = ? WHERE room_id = ?");
        $update_room_stmt->bind_param("si", $current_date, $room_id);
        $update_room_stmt->execute();
        $update_room_stmt->close();
    }
    $room_id_query->close();

    header("Location: maintenance.php");
    exit();
}

// Fetch assigned tasks for the logged-in user or all tasks for admin
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

if ($user_role === 'admin') {
    $tasks_sql = "SELECT mt.id, r.type as room_type, u.name as assigned_to, mt.status, mt.created_at 
                  FROM maintenance_tasks mt
                  JOIN rooms r ON mt.room_id = r.id
                  JOIN users u ON mt.assigned_to_user_id = u.id
                  WHERE mt.status = 'pending'
                  ORDER BY mt.created_at ASC";
    $stmt = $conn->prepare($tasks_sql);
} else {
    $tasks_sql = "SELECT mt.id, r.type as room_type, mt.status, mt.created_at 
                  FROM maintenance_tasks mt
                  JOIN rooms r ON mt.room_id = r.id
                  WHERE mt.assigned_to_user_id = ? AND mt.status = 'pending'
                  ORDER BY mt.created_at ASC";
    $stmt = $conn->prepare($tasks_sql);
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$tasks_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Mantenimiento - INDET</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800 font-poppins">
    <div class="container mx-auto p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Tareas de Mantenimiento Pendientes</h1>
            <a href="php/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg">Cerrar Sesión</a>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-2xl">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Habitación</th>
                            <?php if ($user_role === 'admin'): ?>
                                <th class="py-3 px-4 uppercase font-semibold text-sm">Asignado a</th>
                            <?php endif; ?>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Fecha de Asignación</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php if ($tasks_result->num_rows > 0): ?>
                            <?php while($task = $tasks_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-100">
                                    <td class="py-3 px-4 capitalize"><?php echo $task['room_type']; ?></td>
                                    <?php if ($user_role === 'admin'): ?>
                                        <td class="py-3 px-4"><?php echo $task['assigned_to']; ?></td>
                                    <?php endif; ?>
                                    <td class="py-3 px-4"><?php echo $task['created_at']; ?></td>
                                    <td class="py-3 px-4">
                                        <form action="maintenance.php" method="POST">
                                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg">Marcar como Completada</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo $user_role === 'admin' ? '4' : '3'; ?>" class="text-center py-4">No hay tareas pendientes.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
<?php $stmt->close(); $conn->close(); ?>
