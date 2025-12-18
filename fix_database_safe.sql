-- Script AMAN untuk memperbaiki database tanpa menghapus data
-- Jalankan script ini di phpMyAdmin atau MySQL command line

USE project;

-- Cek apakah tabel reports ada
SHOW TABLES LIKE 'reports';

-- Jika tabel reports ada, backup data dulu
CREATE TABLE IF NOT EXISTS reports_backup AS SELECT * FROM reports;

-- Hapus field lokasi jika ada (jika tabel sudah ada)
ALTER TABLE reports DROP COLUMN IF EXISTS lokasi;

-- Tambahkan field yang diperlukan jika belum ada
ALTER TABLE reports 
ADD COLUMN IF NOT EXISTS nomor_registrasi VARCHAR(50) AFTER id,
ADD COLUMN IF NOT EXISTS nomor_mesin VARCHAR(50) AFTER nomor_registrasi;

-- Update data lama jika ada
UPDATE reports SET nomor_registrasi = CONCAT(id, '/mtn/', DATE_FORMAT(NOW(), '%m/%y')) WHERE nomor_registrasi IS NULL OR nomor_registrasi = '';

-- Tampilkan struktur tabel untuk verifikasi
DESCRIBE reports;

-- Tampilkan data untuk verifikasi
SELECT * FROM reports LIMIT 5;

