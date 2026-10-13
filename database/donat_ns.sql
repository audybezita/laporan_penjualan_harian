-- MySQL dump for Donat NS database schema
CREATE DATABASE IF NOT EXISTS `donat_ns` DEFAULT CHARACTER SET = 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';
USE `donat_ns`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `productions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `production_date` DATE NOT NULL,
  `batch_name` VARCHAR(100) NULL,
  `produced` INT NOT NULL CHECK (produced >= 0),
  `distributed` INT NOT NULL DEFAULT 0 CHECK (distributed >= 0),
  `note` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_production_date_batch` (`production_date`, `batch_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `distributions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `production_id` INT NULL,
  `distribution_date` DATE NOT NULL,
  `distributed` INT NOT NULL CHECK (distributed >= 0),
  `note` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_distribution_date` (`distribution_date`),
  CONSTRAINT `fk_distributions_production` FOREIGN KEY (`production_id`) REFERENCES `productions`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `sales_reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `distribution_id` INT NOT NULL,
  `report_date` DATE NOT NULL,
  `total_distributed` INT NOT NULL CHECK (total_distributed >= 0),
  `total_bs` INT NOT NULL DEFAULT 0 CHECK (total_bs >= 0),
  `total_sold` INT GENERATED ALWAYS AS (GREATEST(total_distributed - total_bs, 0)) STORED,
  `price_per` DECIMAL(12,2) NOT NULL,
  `total_value` DECIMAL(14,2) NOT NULL,
  `note` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_report_date` (`report_date`),
  CONSTRAINT `fk_sales_reports_distribution` FOREIGN KEY (`distribution_id`) REFERENCES `distributions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sales_report_id` INT NOT NULL,
  `payment_date` DATE NOT NULL,
  `amount` DECIMAL(14,2) NOT NULL CHECK (amount >= 0),
  `note` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_payment_date` (`payment_date`),
  CONSTRAINT `fk_payments_report` FOREIGN KEY (`sales_report_id`) REFERENCES `sales_reports`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `expense_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `expense_categories` (`name`) VALUES
('Tepung'),
('Gula'),
('Telur'),
('Minyak'),
('Margarin'),
('Ragi'),
('Topping'),
('Kemasan'),
('Gas'),
('Transportasi'),
('Pengeluaran Lainnya');

CREATE TABLE `expenses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `expense_date` DATE NOT NULL,
  `category_id` INT NULL,
  `item` VARCHAR(255) NULL,
  `quantity` INT NULL,
  `unit_price` DECIMAL(14,2) NULL,
  `total` DECIMAL(14,2) NOT NULL,
  `note` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_expense_date` (`expense_date`),
  CONSTRAINT `fk_expenses_category` FOREIGN KEY (`category_id`) REFERENCES `expense_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cash_openings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `opening_date` DATE NOT NULL,
  `opening_balance` DECIMAL(14,2) NOT NULL,
  `note` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_opening_date` (`opening_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `action` VARCHAR(255) NOT NULL,
  `detail` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
