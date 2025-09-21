-- Set the database character set and collation
ALTER DATABASE `indet_hotel_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Convert individual tables and their columns to the new character set
ALTER TABLE `users` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `rooms` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `reservations` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `reviews` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `room_status` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `maintenance_tasks` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `events` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
