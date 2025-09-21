<?php
session_start();
include 'php/db.php';

// Admin access check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// --- Data Fetching ---

// 1. Daily Room Status Report (for today)
$today = date('Y-m-d');
$daily_status_sql = "SELECT r.type, rs.status, rs.date 
                     FROM room_status rs
                     JOIN rooms r ON rs.room_id = r.id
                     WHERE rs.date = ?
                     ORDER BY r.type ASC";
$stmt_daily = $conn->prepare($daily_status_sql);
$stmt_daily->bind_param("s", $today);
$stmt_daily->execute();
$daily_status_result = $stmt_daily->get_result();

// 2. Client Profile Analysis (reservations per user)
$client_analysis_sql = "SELECT u.name, u.email, COUNT(res.id) as reservation_count
                        FROM users u
                        JOIN reservations res ON u.id = res.user_id
                        GROUP BY u.id
                        ORDER BY reservation_count DESC";
$client_analysis_result = $conn->query($client_analysis_sql);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes Avanzados - INDET</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800 font-poppins">
    <div class="container mx-auto p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Reportes Avanzados</h1>
            <a href="admin.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg">Volver al Panel</a>
        </div>

        <!-- Daily Room Status Report -->
        <div class="bg-white p-6 rounded-xl shadow-2xl mb-8">
            <h2 class="text-2xl font-bold mb-6">Reporte Diario de Estado de Habitaciones (<?php echo $today; ?>)</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Habitación</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php if ($daily_status_result->num_rows > 0): ?>
                            <?php while($row = $daily_status_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-100">
                                    <td class="py-3 px-4 capitalize"><?php echo $row['type']; ?></td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 font-semibold leading-tight rounded-full <?php 
                                            $status_classes = ['available' => 'text-green-700 bg-green-100', 'occupied' => 'text-red-700 bg-red-100', 'cleaning' => 'text-blue-700 bg-blue-100'];
                                            echo $status_classes[$row['status']] ?? 'text-gray-700 bg-gray-100';
                                        ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="text-center py-4">No hay datos de estado para la fecha de hoy.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Client Profile Analysis -->
        <div class="bg-white p-6 rounded-xl shadow-2xl">
            <h2 class="text-2xl font-bold mb-6">Análisis de Perfil de Clientes</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Cliente</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Email</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Total de Reservas</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php if ($client_analysis_result->num_rows > 0): ?>
                            <?php while($row = $client_analysis_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-100">
                                    <td class="py-3 px-4"><?php echo $row['name']; ?></td>
                                    <td class="py-3 px-4"><?php echo $row['email']; ?></td>
                                    <td class="py-3 px-4 text-center font-bold"><?php echo $row['reservation_count']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center py-4">No hay datos de reservas para analizar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
