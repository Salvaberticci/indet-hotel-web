<?php
session_start();
include 'php/db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch reservations from the database
$sql = "SELECT reservations.id, users.name as user_name, rooms.type as room_type, reservations.checkin_date, reservations.checkout_date, reservations.status 
        FROM reservations 
        JOIN users ON reservations.user_id = users.id 
        JOIN rooms ON reservations.room_id = rooms.id 
        ORDER BY reservations.checkin_date ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - INDET</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-gray-100 text-gray-800 font-poppins">
    <div class="container mx-auto p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Panel de Administración</h1>
            <a href="php/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition-transform hover:scale-105">Cerrar Sesión</a>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-2xl">
            <h2 class="text-2xl font-bold mb-6">Reservas</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="w-1/6 py-3 px-4 uppercase font-semibold text-sm">ID</th>
                            <th class="w-1/6 py-3 px-4 uppercase font-semibold text-sm">Cliente</th>
                            <th class="w-1/6 py-3 px-4 uppercase font-semibold text-sm">Habitación</th>
                            <th class="w-1/6 py-3 px-4 uppercase font-semibold text-sm">Llegada</th>
                            <th class="w-1/6 py-3 px-4 uppercase font-semibold text-sm">Salida</th>
                            <th class="w-1/6 py-3 px-4 uppercase font-semibold text-sm">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="w-1/6 py-3 px-4"><?php echo $row['id']; ?></td>
                                    <td class="w-1/6 py-3 px-4"><?php echo $row['user_name']; ?></td>
                                    <td class="w-1/6 py-3 px-4"><?php echo $row['room_type']; ?></td>
                                    <td class="w-1/6 py-3 px-4"><?php echo $row['checkin_date']; ?></td>
                                    <td class="w-1/6 py-3 px-4"><?php echo $row['checkout_date']; ?></td>
                                    <td class="w-1/6 py-3 px-4">
                                        <span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">No hay reservas encontradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>
