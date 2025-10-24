-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-10-2025 a las 22:28:21
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `indet_hotel_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cleaning_inventory`
--

CREATE TABLE `cleaning_inventory` (
  `id` int(11) NOT NULL,
  `floor_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cleaning_inventory`
--

INSERT INTO `cleaning_inventory` (`id`, `floor_id`, `item_name`, `quantity`, `description`, `created_at`) VALUES
(1, 1, 'Escobas', 5, 'Escobas para limpieza', '2025-10-21 05:41:19'),
(2, 1, 'Desinfectante', 10, 'Botellas de desinfectante', '2025-10-21 05:41:19'),
(3, 1, 'Trapos', 20, 'Trapos de limpieza', '2025-10-21 05:41:19'),
(4, 2, 'Escobas', 5, 'Escobas para limpieza', '2025-10-21 05:41:19'),
(5, 2, 'Desinfectante', 10, 'Botellas de desinfectante', '2025-10-21 05:41:19'),
(6, 2, 'Trapos', 20, 'Trapos de limpieza', '2025-10-21 05:41:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `comments`
--

INSERT INTO `comments` (`id`, `name`, `email`, `comment`, `created_at`, `approved`) VALUES
(1, 'Salvatore Berticci', 'salvatoreberticci19@gmail.com', 'Me gustaron mucho las habitaciones, muy cómodas y limpias', '2025-10-08 22:51:09', 1),
(2, 'María López', 'maria.lopez@example.com', 'Excelente servicio y atención al cliente', '2025-10-15 14:30:00', 1),
(3, 'Carlos Rodríguez', 'carlos.rodriguez@example.com', 'Las instalaciones están muy bien mantenidas', '2025-10-18 09:15:00', 1),
(4, 'Ana Martínez', 'ana.martinez@example.com', 'Recomiendo este hotel para familias', '2025-10-20 16:45:00', 0),
(5, 'Luis Sánchez', 'luis.sanchez@example.com', 'Buena ubicación y precios accesibles', '2025-10-22 11:20:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date` date NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `events`
--

INSERT INTO `events` (`id`, `name`, `description`, `date`, `image`, `created_at`) VALUES
(1, 'Torneo Nacional de Natacion', 'Competencia de natación con atletas de todo el país.', '2025-10-15', 'Opera Captura de pantalla_2025-09-17_115448_www.instagram.com.png', '2025-09-21 16:59:24'),
(2, 'Campeonato de Futbol Sub-17', 'Los mejores equipos juveniles compiten por el titulo nacional.', '2025-11-05', 'Opera Captura de pantalla_2025-09-17_115927_www.instagram.com.png', '2025-09-21 16:59:24'),
(3, 'Clínica de Voleibol con Expertos', 'Jornada de entrenamiento intensivo con jugadores profesionales.', '2025-11-20', 'Opera Captura de pantalla_2025-09-17_120407_www.instagram.com.png', '2025-09-21 16:59:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `floors`
--

CREATE TABLE `floors` (
  `id` int(11) NOT NULL,
  `floor_number` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `floors`
--

INSERT INTO `floors` (`id`, `floor_number`, `name`, `description`) VALUES
(1, 1, 'Planta Baja', 'Piso principal del hotel'),
(2, 2, 'Piso 2', 'Segundo piso del hotel'),
(3, 3, 'Piso 3', 'Tercer piso del hotel'),
(4, 4, 'Piso 4', 'Cuarto piso del hotel');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `floor_inventory`
--

CREATE TABLE `floor_inventory` (
  `id` int(11) NOT NULL,
  `floor_id` int(11) NOT NULL,
  `floor` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `floor_inventory`
--

INSERT INTO `floor_inventory` (`id`, `floor_id`, `floor`, `item_name`, `quantity`, `description`, `created_at`) VALUES
(1, 1, 1, 'Cama individual', 10, 'Camas para habitaciones individuales', '2025-10-10 03:20:00'),
(2, 1, 1, 'Silla', 20, 'Sillas para habitaciones', '2025-10-10 03:20:00'),
(3, 1, 0, 'cama matrimonial', 2, 'cama para dos personas', '2025-10-09 23:33:42'),
(4, 2, 0, 'cama matrimonial', 2, 'cama para dos personas', '2025-10-09 23:34:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hotel_info`
--

CREATE TABLE `hotel_info` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `services` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `hotel_info`
--

INSERT INTO `hotel_info` (`id`, `name`, `location`, `contact`, `services`) VALUES
(1, 'Hotel INDET', 'Centro de Caracas, Venezuela', '+58 212 555-0123', 'WiFi gratuito, Piscina, Gimnasio, Restaurante, Servicio de habitaciones, Estacionamiento, Lavandería');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `maintenance_tasks`
--

CREATE TABLE `maintenance_tasks` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `assigned_to_user_id` int(11) NOT NULL,
  `task_description` text DEFAULT 'Limpieza estándar de la habitación.',
  `status` enum('pending','completed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `maintenance_tasks`
--

INSERT INTO `maintenance_tasks` (`id`, `room_id`, `assigned_to_user_id`, `task_description`, `status`, `created_at`, `completed_at`) VALUES
(5, 8, 3, 'Limpieza estándar de la habitación.', 'completed', '2025-09-21 22:06:48', '2025-09-21 23:30:00'),
(6, 8, 1, 'Limpieza estándar de la habitación.', 'completed', '2025-10-09 23:13:17', '2025-10-10 08:00:00'),
(7, 1, 3, 'Limpieza estándar de la habitación.', 'pending', '2025-10-21 01:58:39', NULL),
(8, 2, 7, 'Reparación de grifo en baño', 'completed', '2025-10-15 10:00:00', '2025-10-15 14:30:00'),
(9, 3, 7, 'Cambio de bombillas', 'pending', '2025-10-20 09:15:00', NULL),
(10, 4, 3, 'Limpieza profunda de alfombras', 'completed', '2025-10-18 11:00:00', '2025-10-18 16:45:00'),
(11, 5, 7, 'Revisión de aire acondicionado', 'pending', '2025-10-22 13:20:00', NULL),
(12, 6, 3, 'Limpieza estándar de la habitación.', 'completed', '2025-10-23 07:30:00', '2025-10-23 09:15:00'),
(13, 5, 3, 'Limpieza después del check-out', 'pending', '2025-09-04 04:30:00', NULL),
(14, 2, 3, 'Limpieza después del check-out', 'pending', '2025-09-22 04:30:00', NULL),
(15, 2, 7, 'Limpieza estándar - limpieza de habitación', 'pending', '2025-10-24 17:37:09', NULL),
(16, 2, 3, 'Limpieza estándar - Limpieza', 'pending', '2025-10-24 17:37:46', NULL),
(17, 2, 7, 'Limpieza estándar', 'pending', '2025-10-24 19:11:03', NULL),
(18, 1, 3, 'Limpieza profunda', 'pending', '2025-10-24 19:11:23', NULL),
(19, 2, 7, 'Limpieza estándar', 'completed', '2025-10-24 19:17:30', '2025-10-24 19:17:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `checkin_date` date NOT NULL,
  `checkout_date` date NOT NULL,
  `guest_name` varchar(255) NOT NULL,
  `guest_lastname` varchar(255) DEFAULT NULL,
  `guest_email` varchar(255) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `adultos` int(11) DEFAULT 0,
  `ninos` int(11) DEFAULT 0,
  `discapacitados` int(11) DEFAULT 0,
  `status` enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  `checkin_time` timestamp NULL DEFAULT NULL,
  `checkout_time` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `room_id`, `checkin_date`, `checkout_date`, `guest_name`, `guest_lastname`, `guest_email`, `cedula`, `adultos`, `ninos`, `discapacitados`, `status`, `checkin_time`, `checkout_time`) VALUES
(19, 1, 2, '2025-09-22', '2025-09-30', 'Salvatore', 'Berticci', 'salvatoreberticci19@gmail.com', '12345678', 2, 0, 0, 'completed', NULL, NULL),
(21, 1, 1, '2025-09-21', '2025-09-30', 'Salvatore', 'Berticci', 'salvatoreberticci19@gmail.com', '12345678', 1, 0, 0, 'completed', NULL, NULL),
(22, 1, 1, '2025-09-22', '2025-09-30', 'Salvatore', 'Berticci', 'salvatoreberticci19@gmail.com', '12345678', 1, 0, 0, 'confirmed', NULL, NULL),
(23, 1, 1, '2025-09-01', '2025-09-16', 'Salvatore', 'Berticci', 'salvatoreberticci19@gmail.com', '12345678', 1, 0, 0, 'completed', NULL, NULL),
(24, 1, 2, '2025-09-21', '2025-09-30', 'Salvatore', 'Berticci', 'salvatoreberticci19@gmail.com', '12345678', 2, 0, 0, 'completed', NULL, NULL),
(25, 1, 1, '2025-09-01', '2025-09-22', 'Salvatore', 'Berticci', 'salvatoreberticci19@gmail.com', '12345678', 1, 0, 0, 'cancelled', NULL, NULL),
(26, 1, 3, '2025-09-21', '2025-09-30', 'Salvatore', 'Berticci', 'salvatoreberticci19@gmail.com', '12345678', 3, 0, 0, 'confirmed', NULL, NULL),
(28, 1, 1, '2025-09-01', '2025-09-22', 'Salvatore', 'Berticci', 'salvatoreberticci19@gmail.com', '12345678', 1, 0, 0, 'completed', NULL, NULL),
(29, 1, 2, '2025-09-21', '2025-09-22', 'Salvatore', 'Berticci', 'salvatoreberticci19@gmail.com', '12345678', 2, 0, 0, 'completed', NULL, NULL),
(30, 2, 5, '2025-09-16', '2025-09-04', 'Admin', 'User', 'admin@indet.com', '12345', 1, 0, 0, 'completed', NULL, NULL),
(31, 4, 8, '2025-10-01', '2025-10-31', 'Pedro', 'García', 'pedro@gmail.com', '44332211', 2, 1, 0, 'confirmed', NULL, NULL),
(32, 5, 1, '2025-11-01', '2025-11-05', 'María', 'López', 'maria.lopez@example.com', '55667788', 2, 1, 0, 'confirmed', NULL, NULL),
(33, 6, 2, '2025-11-10', '2025-11-15', 'Carlos', 'Rodríguez', 'carlos.rodriguez@example.com', '99887766', 1, 0, 1, 'confirmed', NULL, NULL),
(34, 8, 3, '2025-12-01', '2025-12-07', 'Luis', 'Sánchez', 'luis.sanchez@example.com', '77889900', 3, 2, 0, 'confirmed', NULL, NULL),
(35, 9, 4, '2025-12-15', '2025-12-20', 'Carmen', 'Díaz', 'carmen.diaz@example.com', '11224455', 2, 0, 0, 'confirmed', NULL, NULL),
(36, 10, 5, '2025-10-25', '2025-10-28', 'José', 'Hernández', 'jose.hernandez@example.com', '66778899', 1, 0, 0, 'confirmed', NULL, NULL),
(37, 5, 6, '2025-11-20', '2025-11-25', 'María', 'López', 'maria.lopez@example.com', '55667788', 2, 1, 0, 'confirmed', NULL, NULL),
(38, 6, 7, '2025-12-10', '2025-12-15', 'Carlos', 'Rodríguez', 'carlos.rodriguez@example.com', '99887766', 3, 0, 0, 'confirmed', NULL, NULL),
(39, 8, 8, '2025-11-05', '2025-11-10', 'Luis', 'Sánchez', 'luis.sanchez@example.com', '77889900', 2, 2, 0, 'confirmed', NULL, NULL),
(40, 9, 9, '2025-10-30', '2025-11-02', 'Carmen', 'Díaz', 'carmen.diaz@example.com', '11224455', 1, 0, 1, 'confirmed', NULL, NULL),
(41, 10, 10, '2025-12-20', '2025-12-25', 'José', 'Hernández', 'jose.hernandez@example.com', '66778899', 4, 1, 0, 'confirmed', NULL, NULL),
(42, 11, 2, '2025-10-25', '2025-10-27', 'Axel', 'Axel', 'axel@gmail.com', '9313132', 2, 2, 0, 'confirmed', '2025-10-24 19:46:14', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` varchar(10) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `room_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, '001', 5, 'Excelente habitación, muy cómoda y limpia', '2025-10-10 14:30:00'),
(2, 4, '002', 4, 'Buena habitación, aunque el WiFi podría ser mejor', '2025-10-12 09:15:00'),
(3, 5, '003', 5, 'Perfecto para familias, mucho espacio', '2025-10-15 16:45:00'),
(4, 6, '004', 3, 'Habitación aceptable, pero necesita mantenimiento', '2025-10-18 11:20:00'),
(5, 8, '005', 5, 'Increíble experiencia, volveré seguro', '2025-10-20 13:10:00'),
(6, 9, '006', 4, 'Buena relación calidad-precio', '2025-10-22 10:30:00'),
(7, 10, '007', 5, 'El personal es muy amable y atento', '2025-10-23 15:45:00'),
(8, 1, '008', 4, 'Habitación espaciosa y bien equipada', '2025-10-24 08:20:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rooms`
--

CREATE TABLE `rooms` (
  `id` varchar(10) NOT NULL,
  `type` varchar(255) NOT NULL,
  `capacity` int(11) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `photos` text NOT NULL,
  `floor` int(11) NOT NULL,
  `floor_id` int(11) NOT NULL,
  `status` enum('enabled','disabled') NOT NULL DEFAULT 'enabled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rooms`
--

INSERT INTO `rooms` (`id`, `type`, `capacity`, `description`, `price`, `photos`, `floor`, `floor_id`, `status`) VALUES
('001', 'Habitación 3 literas', 3, 'Habitación con 3 literas', 45.00, '[\"default_room.jpg\"]', 1, 1, 'enabled'),
('002', 'Habitación 7 literas', 7, 'Habitación con 7 literas', 105.00, '[\"default_room.jpg\"]', 2, 2, 'enabled'),
('003', 'Habitación 8 literas', 8, 'Habitación con 8 literas', 120.00, '[\"default_room.jpg\"]', 3, 3, 'enabled'),
('004', 'Habitación 3 literas', 3, 'Habitación con 3 literas', 45.00, '[\"default_room.jpg\"]', 1, 1, 'enabled'),
('005', 'Habitación 7 literas', 7, 'Habitación con 7 literas', 105.00, '[\"default_room.jpg\"]', 2, 2, 'enabled'),
('006', 'Habitación 8 literas', 8, 'Habitación con 8 literas', 120.00, '[\"default_room.jpg\"]', 3, 3, 'enabled'),
('007', 'Habitación 3 literas', 3, 'Habitación con 3 literas', 45.00, '[\"default_room.jpg\"]', 1, 1, 'enabled'),
('008', 'Habitación 7 literas', 7, 'Habitación con 7 literas', 105.00, '[\"default_room.jpg\"]', 2, 2, 'enabled'),
('009', 'Habitación 8 literas', 8, 'Habitación con 8 literas', 120.00, '[\"default_room.jpg\"]', 3, 3, 'enabled'),
('010', 'Habitación 3 literas', 3, 'Habitación con 3 literas', 45.00, '[\"default_room.jpg\"]', 1, 1, 'enabled');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `room_inventory`
--

CREATE TABLE `room_inventory` (
  `id` int(11) NOT NULL,
  `room_id` varchar(10) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `room_inventory`
--

INSERT INTO `room_inventory` (`id`, `room_id`, `item_name`, `quantity`, `description`, `created_at`) VALUES
(1, '001', 'Almohadas', 3, 'Almohadas para habitación', '2025-10-21 05:41:19'),
(2, '001', 'Sábanas', 3, 'Sábanas para literas', '2025-10-21 05:41:19'),
(3, '001', 'Toallas', 6, 'Toallas de baño', '2025-10-21 05:41:19'),
(4, '002', 'Almohadas', 7, 'Almohadas para habitación', '2025-10-21 05:41:19'),
(5, '002', 'Sábanas', 7, 'Sábanas para literas', '2025-10-21 05:41:19'),
(6, '002', 'Toallas', 14, 'Toallas de baño', '2025-10-21 05:41:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `room_status`
--

CREATE TABLE `room_status` (
  `id` int(11) NOT NULL,
  `room_id` varchar(10) NOT NULL,
  `status` enum('available','occupied','cleaning') NOT NULL DEFAULT 'available',
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `room_status`
--

INSERT INTO `room_status` (`id`, `room_id`, `status`, `date`) VALUES
(1, '001', 'available', '2025-10-24'),
(2, '002', 'occupied', '2025-10-24'),
(3, '003', 'cleaning', '2025-10-24'),
(4, '004', 'available', '2025-10-24'),
(5, '005', 'occupied', '2025-10-24'),
(6, '006', 'available', '2025-10-24'),
(7, '007', 'cleaning', '2025-10-24'),
(8, '008', 'available', '2025-10-24'),
(9, '009', 'occupied', '2025-10-24'),
(10, '010', 'available', '2025-10-24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `cedula_type` varchar(10) DEFAULT 'V',
  `password` varchar(255) NOT NULL,
  `role` enum('client','admin','maintenance') NOT NULL DEFAULT 'client',
  `is_verified` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `cedula`, `cedula_type`, `password`, `role`, `is_verified`) VALUES
(1, 'Salvatore Berticci', 'salvatoreberticci19@gmail.com', '12345678', 'V', '$2y$10$8lxCyjzvMRtCx9.KoYl/SehkJRhAZ.0NE.6PxLNcMmjR29P8wgTGG', 'client', 1),
(2, 'Admin', 'admin@indet.com', '12345', 'V', '$2y$10$rp8eQhk7P/QiNqQWSDvzcOnJZn0oCrXukJuV2Bsx9Dq980VPpHPMW', 'admin', 1),
(3, 'Juan Perez', 'juanperez11@gmail.com', '11223344', 'V', '$2y$10$Qgsk4klfwiNj.NaARvWAAOmJeoTrUURmJ92tD5t4vGw/2QLem3.AG', 'maintenance', 1),
(4, 'Pedro García', 'pedro@gmail.com', '44332211', 'V', '$2y$10$ELs4NU4olvDyJ6QSpaUQpOk672qhmjWQJfT7bUY3bxo2lk.kd1j52', 'client', 1),
(5, 'María López', 'maria.lopez@example.com', '55667788', 'V', '$2y$10$sample.hash.for.maria', 'client', 1),
(6, 'Carlos Rodríguez', 'carlos.rodriguez@example.com', '99887766', 'V', '$2y$10$sample.hash.for.carlos', 'client', 1),
(7, 'Ana Martínez', 'ana.martinez@example.com', '33445566', 'V', '$2y$10$sample.hash.for.ana', 'maintenance', 1),
(8, 'Luis Sánchez', 'luis.sanchez@example.com', '77889900', 'V', '$2y$10$sample.hash.for.luis', 'client', 1),
(9, 'Carmen Díaz', 'carmen.diaz@example.com', '11224455', 'V', '$2y$10$sample.hash.for.carmen', 'client', 1),
(10, 'José Hernández', 'jose.hernandez@example.com', '66778899', 'V', '$2y$10$sample.hash.for.jose', 'client', 1),
(11, 'Axel', 'axel@gmail.com', '9313132', 'E', '$2y$10$ZJgto6EfLFRmR6YcgRpqKu0xLfBTm9rrkc9l0lrmD/BUdRj3x9eDu', 'client', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cleaning_inventory`
--
ALTER TABLE `cleaning_inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_floor_id` (`floor_id`);

--
-- Indices de la tabla `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `floors`
--
ALTER TABLE `floors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `floor_number` (`floor_number`);

--
-- Indices de la tabla `floor_inventory`
--
ALTER TABLE `floor_inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_floor_inventory_floor` (`floor_id`);

--
-- Indices de la tabla `hotel_info`
--
ALTER TABLE `hotel_info`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `maintenance_tasks`
--
ALTER TABLE `maintenance_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `assigned_to_user_id` (`assigned_to_user_id`);

--
-- Indices de la tabla `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indices de la tabla `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indices de la tabla `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_rooms_floor` (`floor_id`);

--
-- Indices de la tabla `room_inventory`
--
ALTER TABLE `room_inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_room_id` (`room_id`);

--
-- Indices de la tabla `room_status`
--
ALTER TABLE `room_status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cleaning_inventory`
--
ALTER TABLE `cleaning_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `floors`
--
ALTER TABLE `floors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `floor_inventory`
--
ALTER TABLE `floor_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `hotel_info`
--
ALTER TABLE `hotel_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `maintenance_tasks`
--
ALTER TABLE `maintenance_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `room_inventory`
--
ALTER TABLE `room_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `room_status`
--
ALTER TABLE `room_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cleaning_inventory`
--
ALTER TABLE `cleaning_inventory`
  ADD CONSTRAINT `cleaning_inventory_ibfk_1` FOREIGN KEY (`floor_id`) REFERENCES `floors` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `floor_inventory`
--
ALTER TABLE `floor_inventory`
  ADD CONSTRAINT `fk_floor_inventory_floor` FOREIGN KEY (`floor_id`) REFERENCES `floors` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `maintenance_tasks`
--
ALTER TABLE `maintenance_tasks`
  ADD CONSTRAINT `maintenance_tasks_ibfk_2` FOREIGN KEY (`assigned_to_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `fk_rooms_floor` FOREIGN KEY (`floor_id`) REFERENCES `floors` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
