<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include 'php/db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch events
$events_sql = "SELECT id, name, description, date, image FROM events ORDER BY date DESC";
$events_result = $conn->query($events_sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos - Panel de Administración - INDET</title>

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
                <h1 class="text-3xl font-bold">Eventos</h1>
            </div>
            <div>
                <a href="admin.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-4">Volver al Menú</a>
                <a href="php/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg">Cerrar Sesión</a>
            </div>
        </div>

        <div class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl mt-8">
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
    </script>
</body>
</html>
<?php
$conn->close();
?>