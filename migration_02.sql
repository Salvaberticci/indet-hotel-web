-- Migration 02: Add user verification fields
-- Add columns for email verification to the users table

ALTER TABLE `users`
ADD COLUMN `is_verified` BOOLEAN NOT NULL DEFAULT FALSE AFTER `role`,
ADD COLUMN `verification_token` VARCHAR(255) NULL DEFAULT NULL AFTER `is_verified`;
