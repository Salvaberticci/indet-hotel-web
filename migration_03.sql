CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date` date NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `events` (`name`, `description`, `date`, `image`) VALUES
('Torneo Nacional de Natación', 'Competencia de natación con atletas de todo el país.', '2025-10-15', 'evento-natacion.jpg'),
('Campeonato de Fútbol Sub-17', 'Los mejores equipos juveniles compiten por el título nacional.', '2025-11-05', 'evento-futbol.jpg'),
('Clínica de Voleibol con Expertos', 'Jornada de entrenamiento intensivo con jugadores profesionales.', '2025-11-20', 'evento-voleibol.jpg');
