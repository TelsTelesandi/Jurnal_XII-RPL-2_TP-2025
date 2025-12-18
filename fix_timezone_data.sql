-- Script untuk memperbaiki timezone data yang sudah ada
-- Jalankan script ini di phpMyAdmin atau MySQL command line

USE project;

-- Set timezone untuk session MySQL
SET time_zone = '+07:00';

-- Update jam_masuk untuk data yang sudah ada (jika perlu)
-- Hanya jalankan jika jam_masuk tidak sesuai dengan WIB
-- UPDATE reports SET jam_masuk = DATE_ADD(jam_masuk, INTERVAL 7 HOUR) WHERE jam_masuk IS NOT NULL;

-- Tampilkan data untuk verifikasi
SELECT 
    id,
    nomor_registrasi,
    nomor_mesin,
    aset,
    tgl_masuk,
    jam_masuk,
    status,
    NOW() as current_time_wib
FROM reports 
ORDER BY tgl_masuk DESC 
LIMIT 5;

-- Tampilkan timezone info
SELECT 
    @@global.time_zone as global_timezone,
    @@session.time_zone as session_timezone,
    NOW() as current_time;

