-- Buat database jika belum ada
CREATE DATABASE IF NOT EXISTS `purchase_order_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE `purchase_order_db`;

-- Tabel users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` enum('admin','production') NOT NULL DEFAULT 'production',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel items
CREATE TABLE IF NOT EXISTS `items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_code` varchar(50) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `unit` varchar(20) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `min_stock` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_code` (`item_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel outgoing_transactions
CREATE TABLE IF NOT EXISTS `outgoing_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_code` varchar(50) NOT NULL,
  `requested_by` int(11) NOT NULL,
  `request_date` datetime NOT NULL,
  `status` enum('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `production_notes` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_code` (`request_code`),
  KEY `requested_by` (`requested_by`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `outgoing_transactions_ibfk_1` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`),
  CONSTRAINT `outgoing_transactions_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel outgoing_transaction_items
CREATE TABLE IF NOT EXISTS `outgoing_transaction_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `transaction_id` (`transaction_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `outgoing_transaction_items_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `outgoing_transactions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `outgoing_transaction_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tambahkan user admin default (password: admin123)
INSERT INTO `users` (`username`, `password`, `email`, `name`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'Administrator', 'admin');

-- Tambahkan beberapa contoh item
INSERT INTO `items` (`item_code`, `item_name`, `description`, `unit`, `unit_price`, `stock`, `min_stock`) VALUES
('ITM001', 'Kertas A4', 'Kertas HVS A4 80gr', 'rim', 50000.00, 100, 10),
('ITM002', 'Tinta Printer Hitam', 'Tinta printer hitam original', 'pcs', 150000.00, 50, 5),
('ITM003', 'Ballpoint', 'Pulpen hitam', 'pcs', 5000.00, 200, 20);
