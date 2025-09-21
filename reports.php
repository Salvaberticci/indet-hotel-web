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
$daily_status_sql = "SELECT rs.status, COUNT(rs.id) as count
                     FROM room_status rs
                     WHERE rs.date = ?
                     GROUP BY rs.status";
$stmt_daily = $conn->prepare($daily_status_sql);
$stmt_daily->bind_param("s", $today);
$stmt_daily->execute();
$daily_status_result = $stmt_daily->get_result();

$status_counts = ['available' => 0, 'occupied' => 0, 'cleaning' => 0];
while ($row = $daily_status_result->fetch_assoc()) {
    $status_counts[$row['status']] = $row['count'];
}

// 2. Client Profile Analysis (reservations per user)
$client_analysis_sql = "SELECT u.name, u.email, COUNT(res.id) as reservation_count
                        FROM users u
                        JOIN reservations res ON u.id = res.user_id
                        GROUP BY u.id
                        ORDER BY reservation_count DESC";
$client_analysis_result = $conn->query($client_analysis_sql);

// 3. Reservation Trends (last 30 days)
$reservation_trend_sql = "SELECT DATE(checkin_date) as date, COUNT(id) as count
                          FROM reservations
                          WHERE checkin_date >= CURDATE() - INTERVAL 30 DAY
                          GROUP BY DATE(checkin_date)
                          ORDER BY date ASC";
$reservation_trend_result = $conn->query($reservation_trend_sql);

$trend_dates = [];
$trend_counts = [];
while ($row = $reservation_trend_result->fetch_assoc()) {
    $trend_dates[] = $row['date'];
    $trend_counts[] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes Avanzados - INDET</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <h2 class="text-2xl font-bold mb-6">Distribución de Estado de Habitaciones (<?php echo $today; ?>)</h2>
            <div class="max-w-md mx-auto">
                <canvas id="roomStatusChart"></canvas>
            </div>
        </div>

        <!-- Client Profile Analysis -->
        <div class="bg-white p-6 rounded-xl shadow-2xl">
            <h2 class="text-2xl font-bold mb-6">Análisis de Perfil de Clientes</h2>

            <!-- Chart Container -->
            <div class="mb-8">
                <canvas id="clientReservationsChart"></canvas>
            </div>

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
                        <?php 
                        $client_names = [];
                        $reservation_counts = [];
                        if ($client_analysis_result->num_rows > 0):
                            // Reset pointer to re-iterate for the table
                            $client_analysis_result->data_seek(0); 
                            while($row = $client_analysis_result->fetch_assoc()): 
                                $client_names[] = $row['name'];
                                $reservation_counts[] = $row['reservation_count'];
                        ?>
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

        <!-- Reservation Trends -->
        <div class="bg-white p-6 rounded-xl shadow-2xl mt-8">
            <h2 class="text-2xl font-bold mb-6">Tendencia de Reservas (Últimos 30 Días)</h2>
            <div>
                <canvas id="reservationTrendChart"></canvas>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Room Status Chart
        const roomStatusCtx = document.getElementById('roomStatusChart').getContext('2d');
        const roomStatusChart = new Chart(roomStatusCtx, {
            type: 'pie',
            data: {
                labels: ['Disponibles', 'Ocupadas', 'En Limpieza'],
                datasets: [{
                    data: [<?php echo $status_counts['available']; ?>, <?php echo $status_counts['occupied']; ?>, <?php echo $status_counts['cleaning']; ?>],
                    backgroundColor: ['#10B981', '#EF4444', '#3B82F6'],
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Estado Actual de las Habitaciones'
                    }
                }
            }
        });

        // Client Reservations Chart
        const clientCtx = document.getElementById('clientReservationsChart').getContext('2d');
        const clientReservationsChart = new Chart(clientCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($client_names); ?>,
                datasets: [{
                    label: '# de Reservas',
                    data: <?php echo json_encode($reservation_counts); ?>,
                    backgroundColor: 'rgba(0, 100, 0, 0.6)',
                    borderColor: 'rgba(0, 100, 0, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Número de Reservas por Cliente'
                    }
                }
            }
        });

        // Reservation Trend Chart
        const trendCtx = document.getElementById('reservationTrendChart').getContext('2d');
        const reservationTrendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($trend_dates); ?>,
                datasets: [{
                    label: 'Reservas por Día',
                    data: <?php echo json_encode($trend_counts); ?>,
                    backgroundColor: 'rgba(220, 38, 38, 0.2)',
                    borderColor: 'rgba(220, 38, 38, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    });
    </script>
</body>
</html>
<?php $conn->close(); ?>
