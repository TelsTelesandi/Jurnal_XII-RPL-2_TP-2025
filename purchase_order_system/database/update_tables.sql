-- Ensure the notifications table exists
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add any missing columns to outgoing_transactions
ALTER TABLE `outgoing_transactions` 
  MODIFY COLUMN `status` ENUM('pending','approved','rejected','completed','partially_approved') NOT NULL DEFAULT 'pending',
  ADD COLUMN IF NOT EXISTS `admin_notes` TEXT NULL AFTER `production_notes`,
  ADD COLUMN IF NOT EXISTS `updated_by` INT(11) NULL AFTER `approved_by`,
  ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP() AFTER `created_at`;

-- Add foreign key for updated_by
ALTER TABLE `outgoing_transactions`
  ADD CONSTRAINT `fk_outgoing_transactions_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- Ensure outgoing_transaction_items has all necessary columns
ALTER TABLE `outgoing_transaction_items`
  ADD COLUMN IF NOT EXISTS `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending' AFTER `total_price`,
  ADD COLUMN IF NOT EXISTS `admin_notes` TEXT NULL AFTER `status`,
  ADD COLUMN IF NOT EXISTS `approved_quantity` INT(11) NULL AFTER `admin_notes`,
  ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP() AFTER `created_at`;
