# 📦 Panduan Instalasi RollMate

Panduan lengkap untuk menginstall aplikasi RollMate di komputer lokal Anda.

## 🔧 Persiapan

### Software yang Dibutuhkan

1. **Web Server dengan PHP dan MySQL**
   - **Laragon** (Recommended untuk Windows) - [Download](https://laragon.org/download/)
   - **XAMPP** - [Download](https://www.apachefriends.org/)
   - **WAMP** - [Download](https://www.wampserver.com/)

2. **Browser Modern**
   - Google Chrome (Recommended)
   - Mozilla Firefox
   - Microsoft Edge

3. **Text Editor** (Opsional, untuk edit kode)
   - Visual Studio Code
   - Sublime Text
   - Notepad++

## 📥 Langkah Instalasi

### Step 1: Download/Clone Project

**Opsi A: Download ZIP**
1. Download project sebagai ZIP
2. Extract ke folder web server:
   - Laragon: `C:\laragon\www\rollmate`
   - XAMPP: `C:\xampp\htdocs\rollmate`
   - WAMP: `C:\wamp64\www\rollmate`

**Opsi B: Git Clone**
```bash
cd C:\laragon\www
git clone [repository-url] rollmate
```

### Step 2: Start Web Server

**Untuk Laragon:**
1. Buka aplikasi Laragon
2. Klik tombol "Start All"
3. Tunggu hingga Apache dan MySQL berwarna hijau

**Untuk XAMPP:**
1. Buka XAMPP Control Panel
2. Start Apache
3. Start MySQL

**Untuk WAMP:**
1. Buka WAMP
2. Klik icon WAMP di system tray
3. Pastikan status "Online"

### Step 3: Buat Database

**Cara 1: Menggunakan phpMyAdmin**

1. Buka browser, akses phpMyAdmin:
   - Laragon: `http://localhost/phpmyadmin`
   - XAMPP: `http://localhost/phpmyadmin`
   - WAMP: `http://localhost/phpmyadmin`

2. Login phpMyAdmin:
   - Username: `root`
   - Password: (kosongkan atau sesuai pengaturan)

3. Klik tab "SQL" di menu atas

4. Copy seluruh isi file `database.sql` dan paste ke SQL editor

5. Klik tombol "Go" atau "Kirim"

6. Database `rollmate` akan otomatis dibuat beserta tabel dan data sample

**Cara 2: Menggunakan MySQL Command Line**

```bash
# Login ke MySQL
mysql -u root -p

# Buat database
CREATE DATABASE rollmate;

# Gunakan database
USE rollmate;

# Import file SQL
SOURCE C:/laragon/www/rollmate/database.sql;

# Keluar
EXIT;
```

### Step 4: Konfigurasi Database (Jika Perlu)

Jika pengaturan MySQL Anda berbeda, edit file `config/database.php`:

```php
define('DB_HOST', 'localhost');      // Host database
define('DB_USER', 'root');           // Username MySQL
define('DB_PASS', '');               // Password MySQL (isi jika ada)
define('DB_NAME', 'rollmate');       // Nama database
```

### Step 5: Akses Aplikasi

1. Buka browser
2. Akses URL:
   - Laragon: `http://localhost/rollmate`
   - XAMPP: `http://localhost/rollmate`
   - WAMP: `http://localhost/rollmate`

3. Anda akan otomatis diarahkan ke halaman login

### Step 6: Login Pertama Kali

Gunakan salah satu akun default:

**Admin:**
- Username: `admin`
- Password: `admin123`

**User/Operator:**
- Username: `operator1`
- Password: `admin123`

## ✅ Verifikasi Instalasi

Setelah login, pastikan:

- ✅ Dashboard muncul dengan benar
- ✅ Statistik order ditampilkan
- ✅ Tombol "Buat Order Baru" berfungsi
- ✅ Halaman order multi-step dapat diakses
- ✅ Form order dapat diisi dan disimpan

## 🔍 Troubleshooting

### Problem: "Koneksi database gagal"

**Solusi:**
1. Pastikan MySQL sudah running
2. Cek username dan password di `config/database.php`
3. Pastikan database `rollmate` sudah dibuat
4. Coba restart MySQL service

### Problem: "Page not found" atau Error 404

**Solusi:**
1. Pastikan folder `rollmate` ada di direktori web server yang benar
2. Cek apakah Apache sudah running
3. Pastikan URL yang diakses benar
4. Coba akses: `http://localhost/rollmate/login.php`

### Problem: Tampilan tidak muncul dengan benar

**Solusi:**
1. Pastikan koneksi internet aktif (untuk load TailwindCSS dari CDN)
2. Clear browser cache (Ctrl + Shift + Delete)
3. Coba browser lain
4. Cek console browser (F12) untuk melihat error

### Problem: Session tidak berfungsi / Selalu logout

**Solusi:**
1. Pastikan PHP session enabled
2. Cek permission folder temporary PHP
3. Edit `php.ini`:
   ```ini
   session.save_path = "C:/laragon/tmp"
   session.auto_start = 0
   ```
4. Restart Apache

### Problem: Error "Call to undefined function password_hash()"

**Solusi:**
1. Upgrade PHP ke versi 7.4 atau lebih tinggi
2. Cek versi PHP: buat file `info.php` dengan isi `<?php phpinfo(); ?>`
3. Akses `http://localhost/rollmate/info.php`

### Problem: Import database gagal

**Solusi:**
1. Buka file `database.sql` dengan text editor
2. Copy paste manual ke phpMyAdmin SQL tab
3. Jalankan per section (CREATE TABLE, INSERT, dll)
4. Atau gunakan MySQL Workbench untuk import

## 🔐 Keamanan Setelah Instalasi

### 1. Ubah Password Default

Setelah login pertama kali:
1. Login sebagai admin
2. Ubah password default
3. Ubah password semua user default

### 2. Hapus File Info (Jika Ada)

```bash
# Hapus file info.php jika dibuat untuk testing
rm info.php
```

### 3. Backup Database Secara Berkala

```bash
# Export database
mysqldump -u root -p rollmate > backup_rollmate.sql
```

## 📱 Testing Fitur

### Test 1: Buat Order Baru
1. Login sebagai user
2. Klik "Buat Order Baru"
3. Pilih jenis laminasi
4. Pilih tipe order
5. Isi form dan submit
6. Cek apakah order muncul di dashboard

### Test 2: Quality Check
1. Buat order baru atau gunakan order sample
2. Update status order menjadi "in_progress" di database
3. Akses menu QC
4. Pilih order dan lakukan QC
5. Cek apakah status berubah

### Test 3: Filter dan Search
1. Buka halaman "Lihat Semua Order"
2. Coba filter berdasarkan status
3. Coba search berdasarkan nama pelanggan
4. Cek pagination

## 🚀 Deploy ke Production

### Persiapan Production

1. **Ubah Konfigurasi Database**
   ```php
   define('DB_HOST', 'your-production-host');
   define('DB_USER', 'your-production-user');
   define('DB_PASS', 'your-strong-password');
   define('DB_NAME', 'rollmate');
   ```

2. **Disable Error Display**
   Edit `php.ini` atau tambahkan di awal file PHP:
   ```php
   ini_set('display_errors', 0);
   error_reporting(0);
   ```

3. **Enable HTTPS**
   - Install SSL Certificate
   - Force HTTPS di `.htaccess`

4. **Backup Rutin**
   - Setup automated database backup
   - Backup file aplikasi

## 📞 Bantuan Lebih Lanjut

Jika masih mengalami masalah:

1. Cek file `README.md` untuk informasi lebih lanjut
2. Baca dokumentasi PHP dan MySQL
3. Cek error log:
   - Apache error log: `C:\laragon\bin\apache\logs\error.log`
   - PHP error log: sesuai konfigurasi `php.ini`

## 🎓 Tips untuk Siswa SMK

1. **Pelajari Struktur Folder**
   - Pahami fungsi setiap file
   - Baca komentar di dalam kode

2. **Eksperimen dengan Kode**
   - Coba ubah warna tema
   - Tambah field baru di form
   - Buat fitur tambahan

3. **Gunakan Developer Tools**
   - Tekan F12 di browser
   - Pelajari Network tab
   - Cek Console untuk error

4. **Version Control**
   - Gunakan Git untuk tracking perubahan
   - Commit setiap perubahan penting
   - Buat branch untuk fitur baru

---

**Selamat! Aplikasi RollMate sudah siap digunakan! 🎉**

*Jika instalasi berhasil, jangan lupa untuk explore semua fitur dan pelajari kodenya!*
