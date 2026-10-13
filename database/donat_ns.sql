-- MySQL dump for Donat NS database schema
-- Do NOT include sample/dummy transactions. Import to create schema only.

CREATE DATABASE IF NOT EXISTS `donat_ns` DEFAULT CHARACTER SET = 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';
USE `donat_ns`;

-- users
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- productions: catat produksi harian (bisa beberapa batch per hari)
CREATE TABLE `productions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `production_date` DATE NOT NULL,
  `produced` INT NOT NULL CHECK (produced >= 0),
  `distributed` INT DEFAULT 0 CHECK (distributed >= 0),
  `note` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_prod_date_batch` (`production_date`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- distributions: catat pengiriman/didistribusikan (batch), tidak perlu data warung
CREATE TABLE `distributions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `production_id` INT NULL,
  `distribution_date` DATE NOT NULL,
  `distributed` INT NOT NULL CHECK (distributed >= 0),
  `note` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`production_id`) REFERENCES `productions`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- sales_reports: ketika sales melakukan pengecekan dan setoran
CREATE TABLE `sales_reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `distribution_id` INT NOT NULL,
  `report_date` DATE NOT NULL,
  `total_distributed` INT NOT NULL CHECK (total_distributed >= 0),
  `total_bs` INT NOT NULL DEFAULT 0 CHECK (total_bs >= 0),
  `total_sold` INT AS (GREATEST(total_distributed - total_bs, 0)) STORED,
  `price_per` DECIMAL(12,2) NOT NULL,
  `total_value` DECIMAL(14,2) NOT NULL,
  `note` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`distribution_id`) REFERENCES `distributions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- payments: pencatatan setoran (bisa bertahap)
CREATE TABLE `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sales_report_id` INT NOT NULL,
  `payment_date` DATE NOT NULL,
  `amount` DECIMAL(14,2) NOT NULL CHECK (amount >= 0),
  `note` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`sales_report_id`) REFERENCES `sales_reports`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- expense_categories: kategori pengeluaran yang dapat dikelola
CREATE TABLE `expense_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- expenses: pengeluaran bahan baku dan operasional
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
  FOREIGN KEY (`category_id`) REFERENCES `expense_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- cash_openings: saldo awal kas
CREATE TABLE `cash_openings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `opening_date` DATE NOT NULL,
  `opening_balance` DECIMAL(14,2) NOT NULL,
  `note` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- simple audit log
CREATE TABLE `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `action` VARCHAR(255) NOT NULL,
  `detail` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Indexes untuk pencarian berdasarkan tanggal
CREATE INDEX idx_production_date ON productions (production_date);
CREATE INDEX idx_distribution_date ON distributions (distribution_date);
CREATE INDEX idx_report_date ON sales_reports (report_date);
CREATE INDEX idx_payment_date ON payments (payment_date);
CREATE INDEX idx_expense_date ON expenses (expense_date);
CREATE INDEX idx_opening_date ON cash_openings (opening_date);

COMMIT;
