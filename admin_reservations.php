<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include 'php/db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch reservations from the database
$sql = "SELECT reservations.id, users.name as user_name, users.cedula_type as user_cedula_type, users.cedula as user_cedula, rooms.type as room_type, floors.name as floor_name, reservations.checkin_date, reservations.checkout_date, reservations.status, reservations.user_id
        FROM reservations
        JOIN users ON reservations.user_id = users.id
        JOIN rooms ON reservations.room_id = rooms.id
        LEFT JOIN floors ON rooms.floor_id = floors.id
        ORDER BY reservations.id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas - Panel de Administración - INDET</title>

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
                <h1 class="text-3xl font-bold">Reservas</h1>
            </div>
            <div>
                <a href="admin.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-4">Volver al Menú</a>
                <a href="php/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg">Cerrar Sesión</a>
            </div>
        </div>

        <!-- Formulario para Agregar Reserva -->
        <div class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl mb-8">
            <h2 class="text-2xl font-bold mb-6">Agregar Nueva Reserva</h2>
            <form action="php/admin_book_reservation.php" method="POST" class="px-6" id="reservationForm">
                <!-- Datos del Cliente -->
                <div class="mb-4">
                    <label for="user_id" class="block font-bold text-sm mb-2 text-gray-500">CLIENTE EXISTENTE (Buscar por Cédula)</label>
                    <select id="user_id" name="user_id" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                        <option value="">Seleccionar cliente...</option>
                        <?php
                        $users_sql_form = "SELECT id, name, cedula_type, cedula FROM users WHERE cedula IS NOT NULL AND cedula != '' AND role = 'client' ORDER BY cedula ASC";
                        $users_result_form = $conn->query($users_sql_form);
                        while($user_form = $users_result_form->fetch_assoc()): ?>
                            <option value="<?php echo $user_form['id']; ?>" data-cedula="<?php echo htmlspecialchars($user_form['cedula']); ?>">
                                <?php echo htmlspecialchars($user_form['cedula_type'] . '-' . $user_form['cedula'] . ' - ' . $user_form['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="form-group text-left">
                        <label for="checkin" class="font-bold text-sm mb-2 block text-gray-500">FECHA DE LLEGADA*</label>
                        <input type="date" name="checkin" placeholder="SELECCIONA" required class="booking-input bg-gray-700 text-white">
                    </div>
                    <div class="form-group text-left">
                        <label for="checkout" class="font-bold text-sm mb-2 block text-gray-500">FECHA DE SALIDA*</label>
                        <input type="date" name="checkout" placeholder="SELECCIONA" required class="booking-input bg-gray-700 text-white">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="form-group text-left">
                        <label class="font-bold text-sm mb-2 block text-gray-500">PERSONAS*</label>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="text-center">
                                <label class="block text-sm">Adultos</label>
                                <div class="flex items-center justify-center">
                                    <button type="button" class="bg-gray-600 px-2 py-1 rounded" onclick="changeCount('adultos', -1)">-</button>
                                    <span id="adultos-count" class="mx-2">0</span>
                                    <button type="button" class="bg-gray-600 px-2 py-1 rounded" onclick="changeCount('adultos', 1)">+</button>
                                </div>
                                <input type="hidden" name="adultos" id="adultos" value="0">
                            </div>
                            <div class="text-center">
                                <label class="block text-sm">Niños</label>
                                <div class="flex items-center justify-center">
                                    <button type="button" class="bg-gray-600 px-2 py-1 rounded" onclick="changeCount('ninos', -1)">-</button>
                                    <span id="ninos-count" class="mx-2">0</span>
                                    <button type="button" class="bg-gray-600 px-2 py-1 rounded" onclick="changeCount('ninos', 1)">+</button>
                                </div>
                                <input type="hidden" name="ninos" id="ninos" value="0">
                            </div>
                            <div class="text-center">
                                <label class="block text-sm">Discapacitados</label>
                                <div class="flex items-center justify-center">
                                    <button type="button" class="bg-gray-600 px-2 py-1 rounded" onclick="changeCount('discapacitados', -1)">-</button>
                                    <span id="discapacitados-count" class="mx-2">0</span>
                                    <button type="button" class="bg-gray-600 px-2 py-1 rounded" onclick="changeCount('discapacitados', 1)">+</button>
                                </div>
                                <input type="hidden" name="discapacitados" id="discapacitados" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="form-group text-left">
                        <label for="floor_id" class="font-bold text-sm mb-2 block text-gray-500">PISO*</label>
                        <select name="floor_id" id="floor_id" required class="booking-input bg-gray-700 text-white">
                            <option value="">SELECCIONA</option>
                            <?php
                            $floors_sql_form = "SELECT id, name FROM floors ORDER BY floor_number ASC";
                            $floors_result_form = $conn->query($floors_sql_form);
                            while($floor_form = $floors_result_form->fetch_assoc()) {
                                echo "<option value='" . $floor_form['id'] . "'>" . htmlspecialchars($floor_form['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="form-group text-left">
                        <label for="room_capacity" class="font-bold text-sm mb-2 block text-gray-500">CAPACIDAD DE HABITACIÓN*</label>
                        <select name="room_capacity" id="room_capacity" required class="booking-input bg-gray-700 text-white">
                            <option value="">SELECCIONA</option>
                            <option value="3">3 Literas</option>
                            <option value="7">7 Literas</option>
                            <option value="8">8 Literas</option>
                        </select>
                    </div>
                </div>
                <div id="room-selection" class="mb-6 hidden">
                    <h3 class="text-lg font-bold mb-4">Seleccionar Habitaciones</h3>
                    <div id="available-rooms" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
                    <div id="selected-rooms" class="mt-4">
                        <h4 class="font-bold">Habitaciones Seleccionadas:</h4>
                        <ul id="selected-list" class="list-disc pl-5"></ul>
                    </div>
                </div>
                <button type="button" id="reserve-btn" class="action-button w-full hidden bg-green-500 hover:bg-green-600">Agregar Reserva <i class="fas fa-plus"></i></button>
            </form>
            <div id="availability-results" class="mt-8"></div>
        </div>

        <!-- Search by Cédula -->
        <div class="mb-6">
            <label for="cedula_search" class="block text-sm font-medium mb-2">Buscar por Cédula:</label>
            <input type="text" id="cedula_search" placeholder="Ingresa la cédula..." class="p-2 border rounded bg-gray-700 text-white w-full md:w-1/3">
            <button onclick="searchByCedula()" class="ml-2 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">
                <i class="fas fa-search mr-2"></i>Buscar
            </button>
        </div>

        <div class="bg-gray-800 text-white p-6 rounded-xl shadow-2xl mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Reservas</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-gray-800">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">ID</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Cliente</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Habitación</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Piso</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Llegada</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Salida</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Estado</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Editar</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Eliminar</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-300">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-700 border-b border-gray-700 reservation-row" data-cedula="<?php echo htmlspecialchars($row['user_cedula']); ?>">
                                    <td class="py-3 px-4 text-center"><?php echo $row['id']; ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <?php echo $row['user_name']; ?><br>
                                        <small class="text-gray-400">Cédula: <?php echo htmlspecialchars($row['user_cedula_type'] . '-' . $row['user_cedula']); ?></small>
                                    </td>
                                    <td class="py-3 px-4 text-center"><?php echo $row['room_type']; ?></td>
                                    <td class="py-3 px-4 text-center"><?php echo htmlspecialchars($row['floor_name'] ?? 'N/A'); ?></td>
                                    <td class="py-3 px-4 text-center"><?php echo $row['checkin_date']; ?></td>
                                    <td class="py-3 px-4 text-center"><?php echo $row['checkout_date']; ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <?php
                                            $status_classes = [
                                                'pending' => 'text-yellow-700 bg-yellow-100',
                                                'confirmed' => 'text-green-700 bg-green-100',
                                                'cancelled' => 'text-red-700 bg-red-100'
                                            ];
                                            $status_class = $status_classes[$row['status']] ?? 'text-gray-700 bg-gray-100';
                                            $status_translations = [
                                                'pending' => 'Pendiente',
                                                'confirmed' => 'Confirmada',
                                                'cancelled' => 'Cancelada'
                                            ];
                                            $translated_status = $status_translations[$row['status']] ?? ucfirst($row['status']);
                                        ?>
                                        <span class="px-2 py-1 font-semibold leading-tight rounded-full <?php echo $status_class; ?>">
                                            <?php echo $translated_status; ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <button onclick="openEditReservationModal(<?php echo htmlspecialchars(json_encode($row)); ?>)" class="text-blue-500 hover:text-blue-700">Editar</button>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <a href="php/reservation_handler.php?delete_reservation=<?php echo $row['id']; ?>" onclick="return confirm('¿Estás seguro de eliminar esta reserva?')" class="text-red-500 hover:text-red-700">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">No hay reservas encontradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Reservation Modal -->
    <div id="editReservationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-gray-800 text-white p-8 rounded-lg shadow-2xl w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6">Editar Reserva</h2>
            <form action="php/reservation_handler.php" method="POST">
                <input type="hidden" id="editReservationId" name="id">
                <div class="mb-4">
                    <label for="editReservationUser" class="block font-semibold mb-2">Cliente (Buscar por Cédula)</label>
                    <select id="editReservationUser" name="user_id" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                        <option value="">Seleccionar cliente...</option>
                        <?php
                        $users_sql = "SELECT id, name, cedula_type, cedula FROM users WHERE cedula IS NOT NULL AND cedula != '' ORDER BY cedula ASC";
                        $users_result = $conn->query($users_sql);
                        while($user = $users_result->fetch_assoc()): ?>
                            <option value="<?php echo $user['id']; ?>" data-cedula="<?php echo htmlspecialchars($user['cedula']); ?>">
                                <?php echo htmlspecialchars($user['cedula_type'] . '-' . $user['cedula'] . ' - ' . $user['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="editReservationRoom" class="block font-semibold mb-2">Habitación</label>
                    <select id="editReservationRoom" name="room_id" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                        <option value="">Seleccionar habitación...</option>
                        <?php
                        $rooms_sql = "SELECT id, type FROM rooms ORDER BY type ASC";
                        $rooms_result = $conn->query($rooms_sql);
                        while($room = $rooms_result->fetch_assoc()): ?>
                            <option value="<?php echo $room['id']; ?>"><?php echo ucfirst($room['type']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="editReservationCheckin" class="block font-semibold mb-2">Fecha de Llegada</label>
                    <input type="date" id="editReservationCheckin" name="checkin_date" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                </div>
                <div class="mb-4">
                    <label for="editReservationCheckout" class="block font-semibold mb-2">Fecha de Salida</label>
                    <input type="date" id="editReservationCheckout" name="checkout_date" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                </div>
                <div class="mb-4">
                    <label for="editReservationStatus" class="block font-semibold mb-2">Estado</label>
                    <select id="editReservationStatus" name="status" required class="w-full p-3 border rounded-lg bg-gray-700 text-white">
                        <option value="pending">Pendiente</option>
                        <option value="confirmed">Confirmada</option>
                        <option value="cancelled">Cancelada</option>
                    </select>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeEditReservationModal()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-2">Cancelar</button>
                    <button type="submit" name="update_reservation" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">Actualizar Reserva</button>
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

        function filterUsersByCedula() {
            const cedula = document.getElementById('user_cedula_search').value.trim();
            const options = document.querySelectorAll('#user_select option');

            options.forEach(option => {
                if (option.value === '') return; // Skip the "Seleccionar cliente..." option

                const optionCedula = option.getAttribute('data-cedula');
                if (cedula === '' || (optionCedula && optionCedula.includes(cedula))) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            });
        }

        function openEditReservationModal(reservation) {
            document.getElementById('editReservationId').value = reservation.id;
            document.getElementById('editReservationUser').value = reservation.user_id;
            document.getElementById('editReservationRoom').value = reservation.room_id;
            document.getElementById('editReservationCheckin').value = reservation.checkin_date;
            document.getElementById('editReservationCheckout').value = reservation.checkout_date;
            document.getElementById('editReservationStatus').value = reservation.status;
            document.getElementById('editReservationModal').classList.remove('hidden');
        }

        function closeEditReservationModal() {
            document.getElementById('editReservationModal').classList.add('hidden');
        }

        let selectedRooms = [];

        function changeCount(type, delta) {
            const countElement = document.getElementById(type + '-count');
            const hiddenInput = document.getElementById(type);
            let count = parseInt(countElement.textContent) + delta;
            if (count < 0) count = 0;
            countElement.textContent = count;
            hiddenInput.value = count;
            updateFloorOptions();
            checkRoomSelection();
        }

        function updateFloorOptions() {
            const discapacitados = parseInt(document.getElementById('discapacitados').value);
            const floorSelect = document.getElementById('floor_id');
            const options = floorSelect.querySelectorAll('option');

            options.forEach(option => {
                if (option.value !== '') {
                    if (discapacitados === 0 && option.textContent === 'Planta Baja') {
                        option.disabled = true;
                        option.style.display = 'none';
                    } else {
                        option.disabled = false;
                        option.style.display = 'block';
                    }
                }
            });

            // Reset selection if current floor is disabled
            if (floorSelect.selectedOptions[0] && floorSelect.selectedOptions[0].disabled) {
                floorSelect.selectedIndex = 0;
            }
        }

        function checkRoomSelection() {
            const checkin = document.querySelector('form#reservationForm input[name="checkin"]').value;
            const checkout = document.querySelector('form#reservationForm input[name="checkout"]').value;
            const floorId = document.getElementById('floor_id').value;
            const capacity = document.getElementById('room_capacity').value;
            const adultos = parseInt(document.getElementById('adultos').value);
            const ninos = parseInt(document.getElementById('ninos').value);
            const discapacitados = parseInt(document.getElementById('discapacitados').value);
            const totalPeople = adultos + ninos + discapacitados;

            if (checkin && checkout && floorId && capacity && totalPeople > 0) {
                loadAvailableRooms(checkin, checkout, floorId, capacity, totalPeople);
            } else {
                document.getElementById('room-selection').classList.add('hidden');
                document.getElementById('reserve-btn').classList.add('hidden');
            }
        }

        function loadAvailableRooms(checkin, checkout, floorId, capacity, totalPeople) {
            fetch(`php/availability_handler.php?checkin=${checkin}&checkout=${checkout}&floor_id=${floorId}&capacity=${capacity}&total_people=${totalPeople}`)
                .then(response => response.json())
                .then(data => {
                    displayAvailableRooms(data);
                })
                .catch(error => console.error('Error:', error));
        }

        function displayAvailableRooms(rooms) {
            const container = document.getElementById('available-rooms');
            container.innerHTML = '';

            if (rooms.length === 0) {
                container.innerHTML = '<p>No hay habitaciones disponibles para los criterios seleccionados.</p>';
                return;
            }

            rooms.forEach(room => {
                const roomDiv = document.createElement('div');
                roomDiv.className = 'border p-4 rounded bg-gray-50 text-gray-800';
                roomDiv.innerHTML = `
                    <h4 class="font-bold">Habitación ${room.id}</h4>
                    <p>Tipo: ${room.type}</p>
                    <p>Capacidad: ${room.capacity}</p>
                    <p>Piso: ${room.floor_name}</p>
                    <p>Descripción: ${room.description}</p>
                    <button type="button" onclick="selectRoom(${room.id}, '${room.type}', ${room.capacity})" class="bg-blue-500 text-white px-4 py-2 rounded mt-2">Seleccionar</button>
                `;
                container.appendChild(roomDiv);
            });

            document.getElementById('room-selection').classList.remove('hidden');
            document.getElementById('reserve-btn').classList.remove('hidden');
        }

        function selectRoom(id, type, capacity) {
            if (selectedRooms.find(room => room.id === id)) {
                alert('Esta habitación ya está seleccionada.');
                return;
            }
            selectedRooms.push({ id, type, capacity });
            updateSelectedRoomsDisplay();
        }

        function updateSelectedRoomsDisplay() {
            const list = document.getElementById('selected-list');
            list.innerHTML = '';
            selectedRooms.forEach(room => {
                const li = document.createElement('li');
                li.textContent = `Habitación ${room.id} - ${room.type} (Capacidad: ${room.capacity})`;
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'ml-2 text-red-500';
                removeBtn.textContent = 'Remover';
                removeBtn.onclick = () => removeRoom(room.id);
                li.appendChild(removeBtn);
                list.appendChild(li);
            });
        }

        function removeRoom(id) {
            selectedRooms = selectedRooms.filter(room => room.id !== id);
            updateSelectedRoomsDisplay();
        }

        document.addEventListener('DOMContentLoaded', function () {
            const checkinInput = document.querySelector('form#reservationForm input[name="checkin"]');
            const checkoutInput = document.querySelector('form#reservationForm input[name="checkout"]');
            const floorSelect = document.getElementById('floor_id');
            const capacitySelect = document.getElementById('room_capacity');
            const userIdSelect = document.getElementById('user_id');

            checkinInput.addEventListener('change', checkRoomSelection);
            checkoutInput.addEventListener('change', checkRoomSelection);
            floorSelect.addEventListener('change', checkRoomSelection);
            capacitySelect.addEventListener('change', checkRoomSelection);
            userIdSelect.addEventListener('change', checkRoomSelection); // Add listener for user selection

            // Add listeners for person counters
            ['adultos', 'ninos', 'discapacitados'].forEach(type => {
                document.getElementById(type + '-count').addEventListener('DOMSubtreeModified', checkRoomSelection);
            });

            document.getElementById('reserve-btn').addEventListener('click', function() {
                if (selectedRooms.length === 0) {
                    alert('Por favor selecciona al menos una habitación.');
                    return;
                }
                showConfirmation();
            });

            // Filter users by cedula in the add reservation form
            const userSelect = document.getElementById('user_id');
            const userOptions = Array.from(userSelect.options);
            let userSearchInput = document.createElement('input');
            userSearchInput.type = 'text';
            userSearchInput.placeholder = 'Buscar cliente por cédula...';
            userSearchInput.className = 'p-2 border rounded bg-gray-700 text-white w-full mb-2';
            userSearchInput.id = 'user_cedula_search_form';
            userSelect.parentNode.insertBefore(userSearchInput, userSelect);

            userSearchInput.addEventListener('keyup', function() {
                const searchText = this.value.toLowerCase();
                userOptions.forEach(option => {
                    if (option.value === '') return;
                    const cedula = option.getAttribute('data-cedula').toLowerCase();
                    const name = option.textContent.toLowerCase();
                    if (cedula.includes(searchText) || name.includes(searchText)) {
                        option.style.display = '';
                    } else {
                        option.style.display = 'none';
                    }
                });
            });
        });

        function showConfirmation() {
            const form = document.getElementById('reservationForm');
            const confirmationDiv = document.createElement('div');
            confirmationDiv.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            confirmationDiv.innerHTML = `
                <div class="bg-gray-800 text-white p-8 rounded-lg max-w-md w-full mx-4">
                    <h3 class="text-xl font-bold mb-4">Confirmar Reserva</h3>
                    <p class="mb-4">¿Estás seguro de que quieres proceder con esta reserva?</p>
                    <div class="mb-4">
                        <h4 class="font-bold">Detalles de la reserva:</h4>
                        <p>Cliente: ${document.getElementById('user_id').selectedOptions[0].textContent}</p>
                        <p>Check-in: ${document.querySelector('form#reservationForm input[name="checkin"]').value}</p>
                        <p>Check-out: ${document.querySelector('form#reservationForm input[name="checkout"]').value}</p>
                        <p>Habitaciones seleccionadas: ${selectedRooms.length}</p>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="this.closest('.fixed').remove()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Volver</button>
                        <button type="button" onclick="submitReservation()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">Confirmar</button>
                    </div>
                </div>
            `;
            document.body.appendChild(confirmationDiv);
        }

        function submitReservation() {
            const form = document.getElementById('reservationForm');
            const formData = new FormData(form);
            formData.append('selected_rooms', JSON.stringify(selectedRooms));
            formData.append('add_reservation_admin', 'true'); // Indicate this is an admin reservation

            fetch('php/admin_book_reservation.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Instead of alert, rely on PHP flash message and reload
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la reserva. Inténtalo de nuevo.');
            });
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>
