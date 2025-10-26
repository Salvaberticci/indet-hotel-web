<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include 'php/db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get selected date from GET parameter or default to show all
$selected_date = isset($_GET['date']) ? $_GET['date'] : null;
$selected_cedula = isset($_GET['cedula']) ? $_GET['cedula'] : null;

// Build WHERE conditions
$where_conditions = [];
$params = [];
$param_types = '';

if ($selected_date) {
    $where_conditions[] = "(r.checkin_date = ? OR r.checkout_date = ?)";
    $params[] = $selected_date;
    $params[] = $selected_date;
    $param_types .= 'ss';
}

if ($selected_cedula) {
    $where_conditions[] = "r.cedula LIKE ?";
    $params[] = '%' . $selected_cedula . '%';
    $param_types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Fetch all reservations for check-ins (pending or confirmed)
$checkin_sql = "SELECT r.id, r.guest_name, r.guest_lastname, r.cedula, r.checkin_date, r.checkout_date, r.checkin_time, r.status,
                        rm.type as room_type, f.name as floor_name, u.name as user_name
                  FROM reservations r
                  JOIN rooms rm ON r.room_id = rm.id
                  JOIN floors f ON rm.floor_id = f.id
                  JOIN users u ON r.user_id = u.id
                  $where_clause AND r.status IN ('confirmed', 'pending')
                  ORDER BY CASE WHEN r.status = 'pending' THEN 1 ELSE 2 END, r.checkin_date ASC";
$checkin_stmt = $conn->prepare($checkin_sql);
if (!empty($params)) {
    $checkin_stmt->bind_param($param_types, ...$params);
}
$checkin_stmt->execute();
$checkin_result = $checkin_stmt->get_result();

// Fetch all reservations for check-outs (confirmed or completed)
$checkout_sql = "SELECT r.id, r.room_id, r.guest_name, r.guest_lastname, r.cedula, r.checkin_date, r.checkout_date, r.checkout_time, r.status,
                          rm.type as room_type, f.name as floor_name, u.name as user_name
                   FROM reservations r
                   JOIN rooms rm ON r.room_id = rm.id
                   JOIN floors f ON rm.floor_id = f.id
                   JOIN users u ON r.user_id = u.id
                   $where_clause AND r.status IN ('confirmed', 'completed')
                   ORDER BY CASE WHEN r.status = 'confirmed' THEN 1 ELSE 2 END, r.checkout_date ASC";
$checkout_stmt = $conn->prepare($checkout_sql);
if (!empty($params)) {
    $checkout_stmt->bind_param($param_types, ...$params);
}
$checkout_stmt->execute();
$checkout_result = $checkout_stmt->get_result();

// Fetch maintenance users for modal
$maintenance_users_sql = "SELECT id, name FROM users WHERE role = 'maintenance' ORDER BY name ASC";
$maintenance_users_result = $conn->query($maintenance_users_sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in/Check-out - Panel de Administración - INDET</title>

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
    /* Estilos para las notificaciones */
    .notification {
        background-color: #333;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInOut 5s forwards;
        pointer-events: none; /* Prevent interaction during animation */
    }

    .notification.success {
        background-color: #28a745; /* Verde para éxito */
    }

    .notification.error {
        background-color: #dc3545; /* Rojo para error */
    }

    .notification i {
        margin-right: 10px;
    }

    @keyframes fadeInOut {
        0% {
            opacity: 0;
            transform: translateY(20px);
            pointer-events: none;
        }
        10% {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }
        90% {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }
        100% {
            opacity: 0;
            transform: translateY(-20px);
            pointer-events: none;
        }
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
                <h1 class="text-3xl font-bold">Check-in/Check-out</h1>
            </div>
            <div>
                <button onclick="printDailyReport()" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg mr-4">
                    <i class="fas fa-print mr-2"></i>Reporte Diario
                </button>
                <a href="admin.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-4">
                    <i class="fas fa-arrow-left mr-2"></i>Volver al Menú
                </a>
                <a href="php/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg">Cerrar Sesión</a>
            </div>
        </div>

        <!-- Search Section -->
        <div class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Search by Cédula -->
                <div>
                    <label for="cedula_search" class="block text-sm font-medium mb-2">Buscar por Cédula:</label>
                    <input type="text" id="cedula_search" placeholder="Ingresa la cédula..." class="p-2 border rounded bg-gray-700 text-white w-full">
                    <button onclick="searchByCedula()" class="mt-2 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg w-full">
                        <i class="fas fa-search mr-2"></i>Buscar por Cédula
                    </button>
                </div>

                <!-- Search by Date -->
                <div>
                    <label for="date_search" class="block text-sm font-medium mb-2">Buscar por Fecha:</label>
                    <input type="date" id="date_search" value="<?php echo $selected_date ?: ''; ?>" class="p-2 border rounded bg-gray-700 text-white w-full">
                    <button onclick="searchByDate()" class="mt-2 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg w-full">
                        <i class="fas fa-calendar mr-2"></i>Buscar por Fecha
                    </button>
                    <button onclick="clearFilters()" class="mt-2 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg w-full">
                        <i class="fas fa-times mr-2"></i>Limpiar Filtros
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8" id="checkin-checkout-sections">
            <!-- Check-in Section -->
            <div class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl">
                <h2 class="text-2xl font-bold mb-6 text-green-400">
                    <i class="fas fa-sign-in-alt mr-2"></i>Check-ins
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-gray-800" id="checkin-table">
                        <thead class="bg-gray-700 text-white">
                            <tr>
                                <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Huésped</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Habitación</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Check-out</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Hora Check-in</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Estado</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-300">
                            <?php if ($checkin_result->num_rows > 0): ?>
                                <?php while($reservation = $checkin_result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-700 border-b border-gray-700 reservation-row" data-cedula="<?php echo htmlspecialchars($reservation['cedula']); ?>">
                                        <td class="py-3 px-4 text-center">
                                            <?php echo htmlspecialchars($reservation['guest_name'] . ' ' . $reservation['guest_lastname']); ?><br>
                                            <small class="text-gray-400">Cédula: <?php echo htmlspecialchars($reservation['cedula']); ?></small><br>
                                            <small class="text-gray-500">Reservado por: <?php echo htmlspecialchars($reservation['user_name']); ?></small>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <?php echo htmlspecialchars($reservation['room_type']); ?><br>
                                            <small class="text-gray-400"><?php echo htmlspecialchars($reservation['floor_name']); ?></small>
                                        </td>
                                        <td class="py-3 px-4 text-center"><?php echo date('d/m/Y', strtotime($reservation['checkout_date'])); ?></td>
                                        <td class="py-3 px-4 text-center">
                                            <?php echo $reservation['checkin_time'] ? date('d/m/Y H:i', strtotime($reservation['checkin_time'])) : '-'; ?>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="px-2 py-1 rounded-full text-xs <?php echo $reservation['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                <?php echo $reservation['status'] === 'confirmed' ? 'Confirmada' : 'Pendiente'; ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <?php if ($reservation['status'] === 'pending'): ?>
                                                <button onclick="confirmCheckin(<?php echo $reservation['id']; ?>)" class="text-green-500 hover:text-green-700 mr-2">
                                                    <i class="fas fa-sign-in-alt"></i> Procesar Check-in
                                                </button>
                                            <?php elseif ($reservation['status'] === 'confirmed'): ?>
                                                <span class="text-gray-500">Check-in procesado</span>
                                            <?php endif; ?>
                                            <button onclick="generateIndividualReport(<?php echo $reservation['id']; ?>, 'checkin')" class="text-blue-500 hover:text-blue-700">
                                                <i class="fas fa-file-pdf"></i> Reporte
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center py-4">No hay check-ins programados.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Check-out Section -->
            <div class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl">
                <h2 class="text-2xl font-bold mb-6 text-red-400">
                    <i class="fas fa-sign-out-alt mr-2"></i>Check-outs
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-gray-800" id="checkout-table">
                        <thead class="bg-gray-700 text-white">
                            <tr>
                                <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Huésped</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Habitación</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Check-in</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Hora Check-out</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-300">
                            <?php if ($checkout_result->num_rows > 0): ?>
                                <?php while($reservation = $checkout_result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-700 border-b border-gray-700 reservation-row" data-cedula="<?php echo htmlspecialchars($reservation['cedula']); ?>">
                                        <td class="py-3 px-4 text-center">
                                            <?php echo htmlspecialchars($reservation['guest_name'] . ' ' . $reservation['guest_lastname']); ?><br>
                                            <small class="text-gray-400">Cédula: <?php echo htmlspecialchars($reservation['cedula']); ?></small><br>
                                            <small class="text-gray-500">Reservado por: <?php echo htmlspecialchars($reservation['user_name']); ?></small>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <?php echo htmlspecialchars($reservation['room_type']); ?><br>
                                            <small class="text-gray-400"><?php echo htmlspecialchars($reservation['floor_name']); ?></small>
                                        </td>
                                        <td class="py-3 px-4 text-center"><?php echo date('d/m/Y', strtotime($reservation['checkin_date'])); ?></td>
                                        <td class="py-3 px-4 text-center">
                                            <?php echo $reservation['checkout_time'] ? date('d/m/Y H:i', strtotime($reservation['checkout_time'])) : '-'; ?>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <?php if ($reservation['status'] === 'confirmed'): ?>
                                                <button onclick="processCheckout(<?php echo $reservation['id']; ?>, <?php echo $reservation['room_id']; ?>, '<?php echo htmlspecialchars($reservation['room_type']); ?>')" class="text-red-500 hover:text-red-700 mr-2">
                                                    <i class="fas fa-sign-out-alt"></i> Procesar Check-out
                                                </button>
                                            <?php elseif ($reservation['status'] === 'completed'): ?>
                                                <span class="text-gray-500">Check-out procesado</span>
                                            <?php endif; ?>
                                            <button onclick="generateIndividualReport(<?php echo $reservation['id']; ?>, 'checkout')" class="text-blue-500 hover:text-blue-700">
                                                <i class="fas fa-file-pdf"></i> Reporte
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-4">No hay check-outs programados.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Task Modal -->
    <div id="maintenanceModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border border-gray-700 w-96 shadow-2xl rounded-xl bg-gray-800">
            <div class="mt-3">
                <h3 class="text-xl font-bold text-white mb-4">Crear Tarea de Mantenimiento</h3>
                <form id="maintenanceForm" action="php/maintenance_handler.php" method="POST">
                    <input type="hidden" name="reservation_id" id="modal_reservation_id">
                    <input type="hidden" name="room_id" id="modal_room_id">

                    <div class="mb-4">
                        <p id="modal_room_info" class="text-sm text-gray-300"></p>
                    </div>

                    <div class="mb-4">
                        <label for="task_description" class="block text-sm font-medium text-gray-200 mb-2">Descripción de la Tarea:</label>
                        <textarea name="task_description" id="task_description" rows="4" required class="w-full p-2 border border-gray-600 rounded-md bg-gray-700 text-white focus:ring-blue-500 focus:border-blue-500" placeholder="Describe la tarea de mantenimiento..."></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="assigned_to_user_id" class="block text-sm font-medium text-gray-200 mb-2">Asignar a:</label>
                        <select name="assigned_to_user_id" id="assigned_to_user_id" required class="w-full p-2 border border-gray-600 rounded-md bg-gray-700 text-white focus:ring-blue-500 focus:border-blue-500">
                            <option value="" class="bg-gray-700">Seleccionar personal</option>
                            <?php while($user = $maintenance_users_result->fetch_assoc()): ?>
                                <option value="<?php echo $user['id']; ?>" class="bg-gray-700"><?php echo htmlspecialchars($user['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">Cancelar</button>
                        <button type="submit" name="create_checkout_task" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">Crear Tarea</button>
                    </div>
                </form>
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

        function confirmCheckin(reservationId) {
            if (confirm('¿Confirmar check-in para esta reserva?')) {
                window.location.href = 'php/checkin_checkout_handler.php?action=checkin&reservation_id=' + reservationId;
            }
        }

        function processCheckout(reservationId, roomId, roomType) {
            if (confirm('¿Procesar check-out para esta reserva?')) {
                // Set modal data
                document.getElementById('modal_reservation_id').value = reservationId;
                document.getElementById('modal_room_id').value = roomId;
                document.getElementById('modal_room_info').textContent = 'Habitación: ' + roomType;

                // Show modal
                document.getElementById('maintenanceModal').classList.remove('hidden');
            }
        }

        function searchByCedula() {
            const cedula = document.getElementById('cedula_search').value.trim();
            const rows = document.querySelectorAll('.reservation-row');

            if (cedula === '') {
                // Show all rows if search is empty
                rows.forEach(row => {
                    row.style.display = '';
                });
                return;
            }

            rows.forEach(row => {
                const rowCedula = row.getAttribute('data-cedula');
                if (rowCedula && rowCedula.includes(cedula)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function searchByDate() {
            const selectedDate = document.getElementById('date_search').value;
            const cedula = document.getElementById('cedula_search').value.trim();

            let url = 'admin_checkin_checkout.php?';
            let params = [];

            if (selectedDate) {
                params.push('date=' + selectedDate);
            }
            if (cedula) {
                params.push('cedula=' + encodeURIComponent(cedula));
            }

            if (params.length === 0) {
                alert('Por favor selecciona una fecha o ingresa una cédula.');
                return;
            }

            window.location.href = url + params.join('&');
        }

        function clearFilters() {
            window.location.href = 'admin_checkin_checkout.php';
        }

        function printDailyReport() {
            const printWindow = window.open('', '_blank');
            const today = '<?php echo date('d/m/Y', strtotime($selected_date ?: date('Y-m-d'))); ?>';

            let checkinContent = '';
            let checkoutContent = '';

            // Get check-in data
            const checkinRows = document.querySelectorAll('#checkin-table tbody tr:not([style*="display: none"])');
            checkinRows.forEach(row => {
                if (row.cells.length >= 5 && !row.querySelector('td[colspan]')) {
                    checkinContent += `
                        <tr>
                            <td>${row.cells[0].textContent}</td>
                            <td>${row.cells[1].textContent}</td>
                            <td>${row.cells[2].textContent}</td>
                            <td>${row.cells[3].textContent}</td>
                        </tr>
                    `;
                }
            });

            // Get check-out data
            const checkoutRows = document.querySelectorAll('#checkout-table tbody tr:not([style*="display: none"])');
            checkoutRows.forEach(row => {
                if (row.cells.length >= 4 && !row.querySelector('td[colspan]')) {
                    checkoutContent += `
                        <tr>
                            <td>${row.cells[0].textContent}</td>
                            <td>${row.cells[1].textContent}</td>
                            <td>${row.cells[2].textContent}</td>
                        </tr>
                    `;
                }
            });

            const content = `
                <html>
                <head>
                    <title>Reporte Diario - ${today}</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        h1, h2 { color: #333; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; margin-bottom: 30px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                        .section { margin-bottom: 40px; }
                        .header { text-align: center; margin-bottom: 30px; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Reporte Diario de Check-in/Check-out</h1>
                        <h2>Fecha: ${today}</h2>
                    </div>

                    <div class="section">
                        <h2>Check-ins</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Huésped</th>
                                    <th>Habitación</th>
                                    <th>Check-out</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${checkinContent || '<tr><td colspan="4">No hay check-ins registrados.</td></tr>'}
                            </tbody>
                        </table>
                    </div>

                    <div class="section">
                        <h2>Check-outs</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Huésped</th>
                                    <th>Habitación</th>
                                    <th>Check-in</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${checkoutContent || '<tr><td colspan="3">No hay check-outs registrados.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </body>
                </html>
            `;

            printWindow.document.write(content);
            printWindow.document.close();
            printWindow.print();
        }

        function generateGeneralReport() {
            fetch('php/checkin_checkout_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=general_report'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.open(data.pdf_url, '_blank');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function generateIndividualReport(reservationId, type) {
            fetch('php/checkin_checkout_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=individual_report&reservation_id=' + reservationId + '&type=' + type
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.open(data.pdf_url, '_blank');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function closeModal() {
            document.getElementById('maintenanceModal').classList.add('hidden');
            document.getElementById('maintenanceForm').reset();
        }

        // Close modal when clicking outside
        document.getElementById('maintenanceModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>
