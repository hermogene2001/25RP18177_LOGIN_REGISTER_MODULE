-- Create database
CREATE DATABASE IF NOT EXISTS `25rp18177_shareride_db`;
USE `25rp18177_shareride_db`;

-- Create tbl_users table
CREATE TABLE IF NOT EXISTS `tbl_users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_firstname` VARCHAR(50) NOT NULL,
  `user_lastname` VARCHAR(50) NOT NULL,
  `user_gender` VARCHAR(20),
  `user_email` VARCHAR(100) NOT NULL UNIQUE,
  `user_password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create indexes
CREATE INDEX idx_user_email ON `tbl_users`(`user_email`);
CREATE INDEX idx_created_at ON `tbl_users`(`created_at`);
