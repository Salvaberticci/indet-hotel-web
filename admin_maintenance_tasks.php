<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include 'php/db.php';

// Check if the user is logged in and is an admin or maintenance
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'maintenance'])) {
    header("Location: login.php");
    exit();
}

// Fetch maintenance tasks
$maintenance_sql = "SELECT mt.id, r.type as room_type, u.name as staff_name, mt.task_description, mt.status, mt.created_at
                    FROM maintenance_tasks mt
                    JOIN rooms r ON mt.room_id = r.id
                    JOIN users u ON mt.assigned_to_user_id = u.id
                    ORDER BY mt.created_at ASC";
$maintenance_result = $conn->query($maintenance_sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tareas de Mantenimiento - Panel de Administración - INDET</title>

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
                <h1 class="text-3xl font-bold">Tareas de Mantenimiento</h1>
            </div>
            <div>
                <a href="admin.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-4">
                    <i class="fas fa-arrow-left mr-2"></i>Volver al Menú
                </a>
                <a href="php/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg">Cerrar Sesión</a>
            </div>
        </div>

        <!-- Combined Task Management Section -->
        <div class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl mb-8">
            <h2 class="text-2xl font-bold mb-6">Gestión de Tareas de Mantenimiento</h2>

            <!-- Create New Task Form -->
            <form action="php/maintenance_handler.php" method="POST" class="mb-8 p-4 bg-gray-700 rounded-lg">
                <h3 class="text-xl font-semibold mb-4">Asignar Nueva Tarea</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <select name="room_id" required class="p-2 border rounded bg-gray-600 text-white">
                        <option value="">Seleccionar Habitación</option>
                        <?php
                        $rooms_sql = "SELECT id, type FROM rooms WHERE status = 'enabled' ORDER BY id ASC";
                        $rooms_result = $conn->query($rooms_sql);
                        while($room = $rooms_result->fetch_assoc()): ?>
                            <option value="<?php echo $room['id']; ?>"><?php echo $room['id']; ?> - <?php echo htmlspecialchars($room['type']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <select name="assigned_to_user_id" required class="p-2 border rounded bg-gray-600 text-white">
                        <option value="">Seleccionar Personal</option>
                        <?php
                        $staff_sql = "SELECT id, name FROM users WHERE role IN ('maintenance') ORDER BY name ASC";
                        $staff_result = $conn->query($staff_sql);
                        while($staff = $staff_result->fetch_assoc()): ?>
                            <option value="<?php echo $staff['id']; ?>"><?php echo htmlspecialchars($staff['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <input type="text" name="task_description" placeholder="Descripción de la tarea" required class="p-2 border rounded bg-gray-600 text-white">
                </div>
                <button type="submit" name="create_task" class="mt-4 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg">Asignar Tarea</button>
            </form>

            <!-- Tasks List -->
            <h3 class="text-xl font-semibold mb-4">Tareas Asignadas</h3>
            <div class="mb-4 flex justify-between items-center">
                <div>
                    <button onclick="filterTasks('all')" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg mr-2">Todas</button>
                    <button onclick="filterTasks('pending')" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded-lg mr-2">Pendientes</button>
                    <button onclick="filterTasks('completed')" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">Completadas</button>
                </div>
                <button onclick="generateReport()" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">
                    <i class="fas fa-file-pdf mr-2"></i>Generar Reporte
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-gray-800" id="tasks-table">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Habitación</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Descripción</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Asignado a</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Estado</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Fecha de Creación</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-300">
                        <?php if ($maintenance_result->num_rows > 0): ?>
                            <?php while($task = $maintenance_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-700 border-b border-gray-700 task-row" data-status="<?php echo $task['status']; ?>">
                                    <td class="py-3 px-4 text-center"><?php echo $task['room_type']; ?></td>
                                    <td class="py-3 px-4 text-center"><?php echo htmlspecialchars($task['task_description']); ?></td>
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
                                    <td class="py-3 px-4 text-center"><?php echo date('d/m/Y H:i', strtotime($task['created_at'])); ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <?php if ($task['status'] == 'pending'): ?>
                                            <a href="php/maintenance_handler.php?action=complete&id=<?php echo $task['id']; ?>" class="text-green-500 hover:text-green-700 mr-2">Marcar como Completada</a>
                                        <?php endif; ?>
                                        <a href="php/maintenance_handler.php?action=delete&id=<?php echo $task['id']; ?>" onclick="return confirm('¿Estás seguro de eliminar esta tarea?')" class="text-red-500 hover:text-red-700">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">No hay tareas asignadas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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

        function filterTasks(status) {
            const rows = document.querySelectorAll('.task-row');
            rows.forEach(row => {
                if (status === 'all' || row.getAttribute('data-status') === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function generateReport() {
            const printWindow = window.open('', '_blank');
            const currentDate = new Date().toLocaleDateString();

            let content = `
                <html>
                <head>
                    <title>Reporte de Tareas de Mantenimiento</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        h1 { color: #333; text-align: center; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .status-pending { background-color: #fff3cd; }
                        .status-completed { background-color: #d4edda; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Reporte de Tareas de Mantenimiento</h1>
                        <p>Fecha: ${currentDate}</p>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Habitación</th>
                                <th>Descripción</th>
                                <th>Asignado a</th>
                                <th>Estado</th>
                                <th>Fecha de Creación</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            // Add table rows
            const rows = document.querySelectorAll('#tasks-table tbody tr');
            rows.forEach(row => {
                if (row.cells.length >= 6) {
                    const status = row.getAttribute('data-status');
                    const statusClass = status === 'completed' ? 'status-completed' : 'status-pending';
                    content += `
                        <tr class="${statusClass}">
                            <td>${row.cells[0].textContent}</td>
                            <td>${row.cells[1].textContent}</td>
                            <td>${row.cells[2].textContent}</td>
                            <td>${row.cells[3].textContent}</td>
                            <td>${row.cells[4].textContent}</td>
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