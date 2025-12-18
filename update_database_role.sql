-- Script untuk memperbarui database yang sudah ada agar mendukung role produksi
-- Jalankan script ini di phpMyAdmin atau MySQL command line

USE project;

-- Update kolom role untuk menambahkan opsi 'produksi'
ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'produksi') DEFAULT 'user';

-- Insert user produksi contoh (opsional)
-- Uncomment baris di bawah jika ingin menambahkan user produksi contoh
-- INSERT INTO users (username, password, role, status) VALUES 
-- ('produksi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'produksi', 'aktif');

-- Password default: password
-- Username: produksi
-- Password: password






