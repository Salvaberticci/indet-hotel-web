<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include 'php/db.php';

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INDET - Realizar Reserva</title>

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
</head>
<body class="bg-gray-900 text-white">

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

    <!-- Background Elements -->
    <div class="fixed top-0 left-0 w-full h-full bg-cover bg-center z-0" style="background-image: url('images/hero-bg.jpg');"></div>
    <div class="fixed top-0 left-0 w-full h-full bg-black/60 z-10"></div>

    <!-- Header -->
    <header id="header" class="relative">
        <!-- Navigation -->
        <nav id="navbar" class="fixed top-0 left-0 w-full p-6 z-40 transition-all duration-300">
            <div class="container mx-auto grid grid-cols-3 items-center">
                <div class="justify-self-start">
                    <img src="images/logo.png" alt="INDET Logo" class="w-24 logo">
                </div>
                <div class="hidden md:flex items-center space-x-4 nav-link-container justify-self-center">
                    <a href="index.php" class="nav-button">Inicio</a>
                    <a href="hotel_info.php" class="nav-button">Nuestro Hotel</a>
                    <a href="index.php#rooms" class="nav-button">Habitaciones</a>
                    <a href="reservar.php" class="nav-button">Disponibilidad</a>
                    <a href="events.php" class="nav-button">Eventos</a>
                    <a href="faq.php" class="nav-button">FAQ</a>
                    <a href="#footer" class="nav-button">Contactos</a>
                </div>
                <div class="flex flex-col items-center space-y-2 justify-self-end">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="user_profile.php" class="login-button">
                            <span>Mi Perfil</span>
                            <i class="fas fa-user"></i>
                        </a>
                        <span class="text-white font-semibold"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <a href="php/logout.php" class="login-button">
                            <span>Logout</span>
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="login-button">
                            <span>Login</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="relative z-30 bg-transparent flex items-center justify-center min-h-screen">
        <!-- Booking Section -->
        <section id="booking" class="bg-white text-gray-800 py-12 relative z-30 mx-4 md:mx-auto max-w-5xl rounded-2xl shadow-2xl w-full">
            <div class="container mx-auto">
                <h2 class="text-4xl font-bold text-center mb-8">Realizar una Reserva</h2>
                <form action="php/book.php" method="POST" class="px-6" id="reservationForm">
                    <!-- Datos Personales -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="form-group text-left">
                            <label for="cedula" class="font-bold text-sm mb-2 block text-gray-500">CÉDULA*</label>
                            <input type="text" name="cedula" placeholder="Ingresa tu cédula" required class="booking-input">
                        </div>
                        <div class="form-group text-left">
                            <label for="guest_name" class="font-bold text-sm mb-2 block text-gray-500">NOMBRE*</label>
                            <input type="text" name="guest_name" placeholder="Ingresa tu nombre" required class="booking-input">
                        </div>
                        <div class="form-group text-left">
                            <label for="guest_lastname" class="font-bold text-sm mb-2 block text-gray-500">APELLIDO*</label>
                            <input type="text" name="guest_lastname" placeholder="Ingresa tu apellido" required class="booking-input">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="form-group text-left">
                            <label for="guest_email" class="font-bold text-sm mb-2 block text-gray-500">CORREO ELECTRÓNICO*</label>
                            <input type="email" name="guest_email" placeholder="Ingresa tu correo" required class="booking-input">
                        </div>
                        <div class="form-group text-left">
                            <label for="checkin" class="font-bold text-sm mb-2 block text-gray-500">FECHA DE LLEGADA*</label>
                            <input type="text" name="checkin" placeholder="SELECCIONA" onfocus="(this.type='date')" onblur="(this.type='text')" required class="booking-input">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="form-group text-left">
                            <label for="checkout" class="font-bold text-sm mb-2 block text-gray-500">FECHA DE SALIDA*</label>
                            <input type="text" name="checkout" placeholder="SELECCIONA" onfocus="(this.type='date')" onblur="(this.type='text')" required class="booking-input">
                        </div>
                        <div class="form-group text-left">
                            <label class="font-bold text-sm mb-2 block text-gray-500">PERSONAS*</label>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="text-center">
                                    <label class="block text-sm">Adultos</label>
                                    <div class="flex items-center justify-center">
                                        <button type="button" class="bg-gray-300 px-2 py-1 rounded" onclick="changeCount('adultos', -1)">-</button>
                                        <span id="adultos-count" class="mx-2">0</span>
                                        <button type="button" class="bg-gray-300 px-2 py-1 rounded" onclick="changeCount('adultos', 1)">+</button>
                                    </div>
                                    <input type="hidden" name="adultos" id="adultos" value="0">
                                </div>
                                <div class="text-center">
                                    <label class="block text-sm">Niños</label>
                                    <div class="flex items-center justify-center">
                                        <button type="button" class="bg-gray-300 px-2 py-1 rounded" onclick="changeCount('ninos', -1)">-</button>
                                        <span id="ninos-count" class="mx-2">0</span>
                                        <button type="button" class="bg-gray-300 px-2 py-1 rounded" onclick="changeCount('ninos', 1)">+</button>
                                    </div>
                                    <input type="hidden" name="ninos" id="ninos" value="0">
                                </div>
                                <div class="text-center">
                                    <label class="block text-sm">Discapacitados</label>
                                    <div class="flex items-center justify-center">
                                        <button type="button" class="bg-gray-300 px-2 py-1 rounded" onclick="changeCount('discapacitados', -1)">-</button>
                                        <span id="discapacitados-count" class="mx-2">0</span>
                                        <button type="button" class="bg-gray-300 px-2 py-1 rounded" onclick="changeCount('discapacitados', 1)">+</button>
                                    </div>
                                    <input type="hidden" name="discapacitados" id="discapacitados" value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="form-group text-left">
                            <label for="floor_id" class="font-bold text-sm mb-2 block text-gray-500">PISO*</label>
                            <select name="floor_id" id="floor_id" required class="booking-input">
                                <option value="">SELECCIONA</option>
                                <?php
                                include 'php/db.php';
                                $floors_sql = "SELECT id, name FROM floors ORDER BY floor_number ASC";
                                $floors_result = $conn->query($floors_sql);
                                while($floor = $floors_result->fetch_assoc()) {
                                    echo "<option value='" . $floor['id'] . "'>" . htmlspecialchars($floor['name']) . "</option>";
                                }
                                $conn->close();
                                ?>
                            </select>
                        </div>
                        <div class="form-group text-left">
                            <label for="room_capacity" class="font-bold text-sm mb-2 block text-gray-500">CAPACIDAD DE HABITACIÓN*</label>
                            <select name="room_capacity" id="room_capacity" required class="booking-input">
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
                    <button type="button" id="reserve-btn" class="action-button w-full hidden">Reservar <i class="fas fa-arrow-right"></i></button>
                </form>
                <div id="availability-results" class="mt-8"></div>
            </div>
        </section>
    </main>

    <!-- Botón de volver -->
    <div class="fixed bottom-4 left-4 z-50">
        <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>

    <script>
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
                    if (discapacitados > 0 && option.textContent !== 'Planta Baja') {
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
            const checkin = document.querySelector('input[name="checkin"]').value;
            const checkout = document.querySelector('input[name="checkout"]').value;
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
                roomDiv.className = 'border p-4 rounded bg-gray-50';
                roomDiv.innerHTML = `
                    <h4 class="font-bold">Habitación ${room.id}</h4>
                    <p>Tipo: ${room.type}</p>
                    <p>Capacidad: ${room.capacity}</p>
                    <p>Piso: ${room.floor_name}</p>
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
            const checkinInput = document.querySelector('input[name="checkin"]');
            const checkoutInput = document.querySelector('input[name="checkout"]');
            const floorSelect = document.getElementById('floor_id');
            const capacitySelect = document.getElementById('room_capacity');

            checkinInput.addEventListener('change', checkRoomSelection);
            checkoutInput.addEventListener('change', checkRoomSelection);
            floorSelect.addEventListener('change', checkRoomSelection);
            capacitySelect.addEventListener('change', checkRoomSelection);

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
        });

        function showConfirmation() {
            const form = document.getElementById('reservationForm');
            const confirmationDiv = document.createElement('div');
            confirmationDiv.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            confirmationDiv.innerHTML = `
                <div class="bg-white text-gray-800 p-8 rounded-lg max-w-md w-full mx-4">
                    <h3 class="text-xl font-bold mb-4 text-gray-800">Confirmar Reserva</h3>
                    <p class="mb-4 text-gray-700">¿Estás seguro de que quieres proceder con esta reserva?</p>
                    <div class="mb-4">
                        <h4 class="font-bold text-gray-800">Detalles de la reserva:</h4>
                        <p class="text-gray-700">Check-in: ${document.querySelector('input[name="checkin"]').value}</p>
                        <p class="text-gray-700">Check-out: ${document.querySelector('input[name="checkout"]').value}</p>
                        <p class="text-gray-700">Habitaciones seleccionadas: ${selectedRooms.length}</p>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button onclick="this.closest('.fixed').remove()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Volver</button>
                        <button onclick="submitReservation()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">Confirmar</button>
                    </div>
                </div>
            `;
            document.body.appendChild(confirmationDiv);
        }

        function submitReservation() {
            const form = document.getElementById('reservationForm');
            const formData = new FormData(form);
            formData.append('selected_rooms', JSON.stringify(selectedRooms));

            fetch('php/book.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                window.location.href = 'confirmation.php';
            })
            .catch(error => console.error('Error:', error));
        }
    </script>

    <!-- Footer -->
    <footer id="footer" class="footer-bg text-white py-20 relative z-30 mt-16">
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-2 gap-12 items-center px-6">
            <div class="text-center md:text-left">
                <h3 class="text-3xl font-bold mb-4">Contacto</h3>
                <p class="text-lg mb-2"><i class="fab fa-instagram mr-2"></i> @indetrujillo</p>
                <p class="text-lg mb-2"><i class="fas fa-phone-alt mr-2"></i> 0412-897643</p>
                <p class="text-lg">Valera Edo Trujillo</p>
            </div>
            <div>
                <h3 class="text-3xl font-bold mb-4 text-center md:text-left">Envíanos un Mensaje</h3>
                <form action="php/contact_handler.php" method="POST" class="space-y-4">
                    <input type="text" name="name" placeholder="Tu Nombre" required class="w-full p-3 rounded-lg bg-gray-800 border border-gray-700 focus:outline-none focus:border-green-500">
                    <input type="email" name="email" placeholder="Tu Correo Electrónico" required class="w-full p-3 rounded-lg bg-gray-800 border border-gray-700 focus:outline-none focus:border-green-500">
                    <textarea name="message" placeholder="Tu Mensaje" rows="4" required class="w-full p-3 rounded-lg bg-gray-800 border border-gray-700 focus:outline-none focus:border-green-500"></textarea>
                    <button type="submit" class="w-full action-button bg-green-600 hover:bg-green-700">Enviar Mensaje <i class="fas fa-paper-plane ml-2"></i></button>
                </form>
            </div>
        </div>
    </footer>

</body>
</html>
