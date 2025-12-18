-- ============================================
-- Database: RollMate Marketplace
-- Sistem Marketplace Laminasi dengan 3 Level Akses
-- Pelanggan, Admin, dan Staff
-- ============================================

-- Buat database baru
CREATE DATABASE IF NOT EXISTS rollmate;
USE rollmate;

-- ============================================
-- Tabel: users
-- Menyimpan data pengguna dengan 3 role: pelanggan, admin, staff
-- ============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    company_name VARCHAR(100), -- Untuk pelanggan
    address TEXT,
    role ENUM('pelanggan', 'admin', 'staff') DEFAULT 'pelanggan',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Tabel: products
-- Menyimpan data produk laminasi yang dijual
-- ============================================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50), -- Contoh: Bahan Laminasi, Jasa Laminasi, Barang Jadi
    description TEXT,
    price DECIMAL(12,2) NOT NULL,
    stock INT DEFAULT 0,
    unit VARCHAR(20), -- Contoh: roll, meter, pcs
    image VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Tabel: requests
-- Menyimpan permintaan pembelian dari pelanggan
-- ============================================
CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_number VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(100) NOT NULL, -- Simpan nama produk untuk history
    quantity INT NOT NULL,
    note TEXT,
    status ENUM('pending', 'approved', 'rejected', 'paid', 'process', 'done') DEFAULT 'pending',
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Tabel: payments
-- Menyimpan data pembayaran dari pelanggan
-- ============================================
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    proof_image VARCHAR(255), -- Upload bukti transfer
    payment_method VARCHAR(50), -- Transfer Bank, Cash, dll
    date_paid TIMESTAMP NULL,
    status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    verified_by INT NULL,
    verified_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Tabel: purchase_orders
-- Menyimpan Purchase Order yang dibuat admin dari request
-- ============================================
CREATE TABLE purchase_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_number VARCHAR(50) NOT NULL UNIQUE,
    request_id INT NOT NULL,
    admin_id INT NOT NULL,
    staff_id INT NULL,
    total_amount DECIMAL(12,2) NOT NULL,
    status ENUM('pending', 'process', 'done', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Tabel: notifications
-- Menyimpan notifikasi untuk user
-- ============================================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(20), -- info, success, warning, error
    is_read TINYINT(1) DEFAULT 0,
    link VARCHAR(255), -- Link ke halaman terkait
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Insert Data Default
-- ============================================

-- Insert user default untuk 3 role
-- Password semua user: admin123
-- CATATAN: Jalankan fix_password.php setelah import untuk generate hash yang benar
INSERT INTO users (username, password, full_name, email, phone, company_name, address, role) VALUES
('admin', '$2y$10$placeholder', 'Administrator System', 'admin@rollmate.com', '081234567890', 'RollMate HQ', 'Jl. Industri No. 123', 'admin'),
('staff1', '$2y$10$placeholder', 'Staff Produksi 1', 'staff1@rollmate.com', '081234567891', NULL, 'Jl. Pekerja No. 45', 'staff'),
('staff2', '$2y$10$placeholder', 'Staff Produksi 2', 'staff2@rollmate.com', '081234567892', NULL, 'Jl. Pekerja No. 46', 'staff'),
('customer1', '$2y$10$placeholder', 'Budi Santoso', 'budi@customer.com', '081234567893', 'PT Maju Jaya', 'Jl. Bisnis No. 1', 'pelanggan'),
('customer2', '$2y$10$placeholder', 'Siti Aminah', 'siti@customer.com', '081234567894', 'CV Sukses Makmur', 'Jl. Dagang No. 2', 'pelanggan');

-- Insert sample products
INSERT INTO products (name, category, description, price, stock, unit, is_active) VALUES
('Laminasi Glossy Premium', 'Jasa Laminasi', 'Laminasi glossy dengan hasil mengkilap dan tahan lama', 50000.00, 999, 'meter', 1),
('Laminasi Doff/Matte', 'Jasa Laminasi', 'Laminasi doff dengan hasil tidak mengkilap, elegan', 45000.00, 999, 'meter', 1),
('Laminasi Transparan', 'Jasa Laminasi', 'Laminasi transparan untuk melindungi dokumen', 40000.00, 999, 'meter', 1),
('Roll Laminasi Glossy 100cm', 'Bahan Laminasi', 'Roll laminasi glossy lebar 100cm, panjang 50 meter', 850000.00, 50, 'roll', 1),
('Roll Laminasi Doff 80cm', 'Bahan Laminasi', 'Roll laminasi doff lebar 80cm, panjang 50 meter', 750000.00, 30, 'roll', 1);

-- Insert sample requests
INSERT INTO requests (request_number, customer_id, company_name, product_id, product_name, quantity, note, status) VALUES
('REQ-2024-001', 4, 'PT Maju Jaya', 1, 'Laminasi Glossy Premium', 100, 'Untuk banner promosi perusahaan', 'approved'),
('REQ-2024-002', 5, 'CV Sukses Makmur', 4, 'Roll Laminasi Glossy 100cm', 5, 'Butuh segera untuk produksi', 'pending');

-- ============================================
-- Index untuk performa query
-- ============================================
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_products_category ON products(category);
CREATE INDEX idx_products_is_active ON products(is_active);
CREATE INDEX idx_requests_status ON requests(status);
CREATE INDEX idx_requests_customer_id ON requests(customer_id);
CREATE INDEX idx_requests_created_at ON requests(created_at);
CREATE INDEX idx_po_status ON purchase_orders(status);
CREATE INDEX idx_po_staff_id ON purchase_orders(staff_id);
CREATE INDEX idx_notifications_user_id ON notifications(user_id);
CREATE INDEX idx_notifications_is_read ON notifications(is_read);

-- ============================================
-- Catatan Password Default:
-- Semua user default menggunakan password: admin123
-- 
-- PENTING: Setelah import database, jalankan:
-- http://localhost/rollmate/fix_password.php
-- untuk generate password hash yang benar
-- 
-- Login Credentials:
-- Admin    : admin / admin123
-- Staff    : staff1 / admin123
-- Pelanggan: customer1 / admin123
-- ============================================
