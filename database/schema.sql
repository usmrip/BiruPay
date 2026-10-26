-- Biru Payment Hub Database Schema
-- Multi-Platform Payment Gateway Hub for Biru Indonesia Creative

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'operator') NOT NULL DEFAULT 'admin',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `applications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `app_code` VARCHAR(50) NOT NULL UNIQUE,
  `app_name` VARCHAR(100) NOT NULL,
  `api_key` VARCHAR(64) NOT NULL UNIQUE,
  `webhook_url` VARCHAR(255) DEFAULT NULL,
  `return_url` VARCHAR(255) DEFAULT NULL,
  `default_gateway` VARCHAR(50) DEFAULT 'default',
  `mode` ENUM('sandbox', 'live') NOT NULL DEFAULT 'sandbox',
  `gateway_live` VARCHAR(50) NOT NULL DEFAULT 'default',
  `gateway_sandbox` VARCHAR(50) NOT NULL DEFAULT 'default',
  `timezone` VARCHAR(50) NOT NULL DEFAULT 'UTC',
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gateway_configs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `gateway_code` VARCHAR(50) NOT NULL UNIQUE,
  `gateway_name` VARCHAR(100) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 0,
  `is_production` TINYINT(1) NOT NULL DEFAULT 0,
  `sandbox_merchant_id` VARCHAR(100) DEFAULT NULL,
  `sandbox_client_key` VARCHAR(255) DEFAULT NULL,
  `sandbox_server_key` VARCHAR(255) DEFAULT NULL,
  `production_merchant_id` VARCHAR(100) DEFAULT NULL,
  `production_client_key` VARCHAR(255) DEFAULT NULL,
  `production_server_key` VARCHAR(255) DEFAULT NULL,
  `merchant_id` VARCHAR(100) DEFAULT NULL,
  `client_key` VARCHAR(255) DEFAULT NULL,
  `server_key` VARCHAR(255) DEFAULT NULL,
  `public_key` TEXT DEFAULT NULL,
  `private_key` TEXT DEFAULT NULL,
  `additional_config` JSON DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `app_id` INT NOT NULL,
  `order_id` VARCHAR(100) NOT NULL,
  `trx_reference` VARCHAR(100) NOT NULL UNIQUE,
  `gateway` ENUM('midtrans', 'duitku', 'doku') NOT NULL,
  `mode` ENUM('sandbox', 'live') NOT NULL DEFAULT 'sandbox',
  `amount` DECIMAL(15,2) NOT NULL,
  `fee` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `return_url` VARCHAR(255) DEFAULT NULL,
  `webhook_url` VARCHAR(255) DEFAULT NULL,
  `customer_name` VARCHAR(100) DEFAULT NULL,
  `customer_email` VARCHAR(100) DEFAULT NULL,
  `customer_phone` VARCHAR(30) DEFAULT NULL,
  `item_details` JSON DEFAULT NULL,
  `gateway_reference` VARCHAR(255) DEFAULT NULL,
  `snap_token` VARCHAR(255) DEFAULT NULL,
  `payment_method` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('pending', 'success', 'failed', 'expired', 'cancelled') NOT NULL DEFAULT 'pending',
  `webhook_response_raw` LONGTEXT DEFAULT NULL,
  `is_app_notified` TINYINT(1) NOT NULL DEFAULT 0,
  `app_notify_status` VARCHAR(100) DEFAULT NULL,
  `app_notify_attempts` INT NOT NULL DEFAULT 0,
  `paid_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_app_id` (`app_id`),
  INDEX `idx_order_id` (`order_id`),
  INDEX `idx_trx_reference` (`trx_reference`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(50) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `channel` VARCHAR(50) NOT NULL,
  `level` VARCHAR(20) NOT NULL DEFAULT 'info',
  `message` TEXT NOT NULL,
  `context` JSON DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_channel` (`channel`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

