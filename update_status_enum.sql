-- Script untuk update ENUM status di tabel reports
USE project;

-- Ubah kolom status untuk mendukung nilai 'proses'
ALTER TABLE reports MODIFY COLUMN status ENUM('open', 'pending', 'proses', 'close') DEFAULT 'pending';

-- Update data yang sudah ada jika perlu
UPDATE reports SET status = 'pending' WHERE status = 'open';