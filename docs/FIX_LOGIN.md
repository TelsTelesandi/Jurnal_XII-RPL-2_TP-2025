# 🔧 Cara Memperbaiki Masalah Login

## ❌ Masalah: "Username atau password salah" padahal password sudah benar

### 🎯 Penyebab
Password hash di database tidak cocok dengan sistem PHP Anda. Ini terjadi karena:
- Hash di database dibuat dengan versi PHP yang berbeda
- Hash di database adalah contoh/dummy hash
- Algoritma hashing berbeda antar sistem

### ✅ Solusi Cepat (Recommended)

**Langkah 1: Akses File Fix Password**

Buka browser dan akses:
```
http://localhost/rollmate/fix_password.php
```

**Langkah 2: Password Otomatis Diupdate**

File akan otomatis:
- Generate password hash yang benar untuk sistem PHP Anda
- Update semua user di database
- Menampilkan konfirmasi sukses

**Langkah 3: Login**

Sekarang Anda bisa login dengan:
- **Username**: `admin`
- **Password**: `admin123`

**Langkah 4: Hapus File (PENTING!)**

Setelah berhasil login, hapus file `fix_password.php` untuk keamanan:
```bash
# Hapus file
del C:\laragon\www\rollmate\fix_password.php
```

---

## 🔧 Solusi Manual (Alternatif)

Jika cara di atas tidak berhasil, gunakan cara manual:

### Cara 1: Update via phpMyAdmin

**1. Buka phpMyAdmin**
```
http://localhost/phpmyadmin
```

**2. Pilih database `rollmate`**

**3. Klik tab SQL**

**4. Jalankan query ini:**
```sql
-- Generate hash baru untuk password 'admin123'
-- Hash ini akan berbeda setiap kali di-generate, tapi tetap valid

UPDATE users SET password = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhqe' WHERE username = 'admin';
UPDATE users SET password = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhqe' WHERE username = 'operator1';
UPDATE users SET password = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhqe' WHERE username = 'operator2';
```

**5. Klik "Go"**

**6. Coba login lagi**

### Cara 2: Generate Hash Sendiri

**1. Buat file test.php di folder rollmate:**
```php
<?php
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Hash untuk password '$password':\n";
echo $hash;
?>
```

**2. Akses file:**
```
http://localhost/rollmate/test.php
```

**3. Copy hash yang muncul**

**4. Update database via phpMyAdmin:**
```sql
UPDATE users SET password = 'PASTE_HASH_DISINI' WHERE username = 'admin';
```

**5. Hapus file test.php**

---

## 🔍 Verifikasi Password Hash

Untuk memastikan hash benar, buat file `verify.php`:

```php
<?php
$password = 'admin123';
$hash = '$2y$10$...'; // Hash dari database

if (password_verify($password, $hash)) {
    echo "✓ Password COCOK!";
} else {
    echo "✗ Password TIDAK COCOK!";
}
?>
```

---

## 📊 Penjelasan Teknis

### Apa itu Password Hash?

Password hash adalah hasil enkripsi password menggunakan algoritma tertentu. Contoh:

- **Password asli**: `admin123`
- **Hash**: `$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhqe`

### Kenapa Hash Berbeda-beda?

Setiap kali `password_hash()` dipanggil, akan menghasilkan hash yang berbeda karena:
- Menggunakan salt yang random
- Tapi semua hash tersebut tetap valid untuk password yang sama

### Cara Kerja Verifikasi

```php
// Saat login
$inputPassword = 'admin123'; // dari form
$hashFromDB = '$2y$10$...'; // dari database

// PHP akan verify
if (password_verify($inputPassword, $hashFromDB)) {
    // Login berhasil
}
```

---

## ⚠️ Troubleshooting Lanjutan

### Problem: File fix_password.php error

**Solusi:**
1. Pastikan MySQL sudah running
2. Cek konfigurasi di `config/database.php`
3. Pastikan database `rollmate` sudah dibuat
4. Cek error di browser console (F12)

### Problem: Masih tidak bisa login setelah fix

**Solusi:**
1. Clear browser cache (Ctrl + Shift + Delete)
2. Coba browser lain atau incognito mode
3. Cek apakah session PHP berfungsi:
   ```php
   <?php
   session_start();
   $_SESSION['test'] = 'works';
   echo $_SESSION['test'];
   ?>
   ```
4. Restart Apache/Laragon

### Problem: Password berubah sendiri

**Solusi:**
- Jangan jalankan file `database.sql` lagi setelah fix
- File SQL akan overwrite password dengan hash lama
- Jika sudah terlanjur, jalankan `fix_password.php` lagi

---

## 🎓 Untuk Siswa SMK

### Pelajaran dari Masalah Ini:

1. **Password JANGAN disimpan plain text di database**
   ```sql
   -- ❌ SALAH
   INSERT INTO users (password) VALUES ('admin123');
   
   -- ✅ BENAR
   INSERT INTO users (password) VALUES ('$2y$10$...');
   ```

2. **Gunakan password_hash() dan password_verify()**
   ```php
   // Saat register/create user
   $hash = password_hash($password, PASSWORD_DEFAULT);
   
   // Saat login
   if (password_verify($inputPassword, $hashFromDB)) {
       // Login success
   }
   ```

3. **Hash akan berbeda setiap kali, tapi tetap valid**
   ```php
   $hash1 = password_hash('admin123', PASSWORD_DEFAULT);
   $hash2 = password_hash('admin123', PASSWORD_DEFAULT);
   // $hash1 != $hash2, tapi keduanya valid untuk 'admin123'
   ```

---

## 📞 Masih Bermasalah?

Jika masih tidak bisa login setelah semua cara di atas:

1. Cek versi PHP: `php -v` (minimal 7.4)
2. Cek extension PHP: `password_hash` harus tersedia
3. Cek error log Apache
4. Screenshot error dan tanyakan ke guru/mentor

---

## ✅ Checklist Setelah Fix

- [ ] Berhasil login dengan admin/admin123
- [ ] Berhasil login dengan operator1/admin123
- [ ] Dashboard muncul dengan benar
- [ ] File fix_password.php sudah dihapus
- [ ] Password sudah diubah dari default

---

**Semoga berhasil! 🚀**

*Jika sudah berhasil login, jangan lupa ubah password default untuk keamanan!*
