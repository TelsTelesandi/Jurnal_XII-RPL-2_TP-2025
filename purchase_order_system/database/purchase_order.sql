-- Create database
CREATE DATABASE IF NOT EXISTS purchase_order_db;
USE purchase_order_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('admin', 'production') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Suppliers table
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    address TEXT,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Items table
CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_code VARCHAR(20) NOT NULL UNIQUE,
    item_name VARCHAR(100) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    unit_price DECIMAL(10, 2) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Incoming transactions (items received from suppliers)
CREATE TABLE IF NOT EXISTS incoming_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_code VARCHAR(20) NOT NULL UNIQUE,
    supplier_id INT NOT NULL,
    transaction_date DATE NOT NULL,
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Incoming transaction items
CREATE TABLE IF NOT EXISTS incoming_transaction_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(12, 2) NOT NULL,
    FOREIGN KEY (transaction_id) REFERENCES incoming_transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Outgoing transactions (requests from production)
CREATE TABLE IF NOT EXISTS outgoing_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_code VARCHAR(20) NOT NULL UNIQUE,
    requested_by INT NOT NULL,
    request_date DATE NOT NULL,
    status ENUM('pending', 'approved', 'partially_approved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT,
    production_notes TEXT,
    processed_by INT,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Outgoing transaction items
CREATE TABLE IF NOT EXISTS outgoing_transaction_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    item_id INT NOT NULL,
    requested_quantity INT NOT NULL,
    approved_quantity INT,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(12, 2) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    notes TEXT,
    FOREIGN KEY (transaction_id) REFERENCES outgoing_transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample admin user (password: admin123)
INSERT INTO users (username, password, name, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@example.com', 'admin'),
('production', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Production User', 'production@example.com', 'production');

-- Insert sample suppliers
INSERT INTO suppliers (supplier_id, name, address, contact_person, phone, email) VALUES
('SUP001', 'ABC Electronics', '123 Tech Street, Silicon Valley', 'John Doe', '+1234567890', 'john@abc.com'),
('SUP002', 'Global Parts Inc.', '456 Industry Road, New York', 'Jane Smith', '+1987654321', 'jane@globalparts.com'),
('SUP003', 'Tech Supplies Ltd', '789 Gadget Ave, Boston', 'Mike Johnson', '+1122334455', 'mike@techsupplies.com');

-- Insert sample items
INSERT INTO items (item_code, item_name, unit, stock, unit_price, description) VALUES
('ITM001', 'Laptop', 'unit', 50, 1200.00, '15.6" Laptop, 16GB RAM, 512GB SSD'),
('ITM002', 'Wireless Mouse', 'unit', 200, 25.99, 'Wireless Optical Mouse, 2.4GHz'),
('ITM003', 'Mechanical Keyboard', 'unit', 150, 89.99, 'RGB Mechanical Keyboard, Blue Switches'),
('ITM004', 'Monitor 24"', 'unit', 75, 199.99, '24" Full HD LED Monitor'),
('ITM005', 'Webcam HD', 'unit', 120, 49.99, 'HD 1080p Webcam with Microphone');
