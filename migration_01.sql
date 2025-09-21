-- Migración para añadir funcionalidades de mantenimiento

-- Paso 1: Añadir el rol 'maintenance' a la tabla de usuarios
ALTER TABLE `users` MODIFY `role` ENUM('client','admin','maintenance') NOT NULL DEFAULT 'client';

-- Paso 2: Crear la tabla para las tareas de mantenimiento
CREATE TABLE `maintenance_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `assigned_to_user_id` int(11) NOT NULL,
  `task_description` text DEFAULT 'Limpieza estándar de la habitación.',
  `status` enum('pending','completed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `room_id` (`room_id`),
  KEY `assigned_to_user_id` (`assigned_to_user_id`),
  CONSTRAINT `maintenance_tasks_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `maintenance_tasks_ibfk_2` FOREIGN KEY (`assigned_to_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
