# ⚡ Quick Start Guide - RollMate

Panduan singkat untuk memulai aplikasi RollMate dalam 5 menit!

## 🚀 Instalasi Cepat

### Step 1: Persiapan (1 menit)
```bash
# Pastikan Laragon/XAMPP sudah terinstall
# Start Apache dan MySQL
```

### Step 2: Setup Database (2 menit)
1. Buka phpMyAdmin: `http://localhost/phpmyadmin`
2. Klik tab "SQL"
3. Copy-paste seluruh isi file `database.sql`
4. Klik "Go"
5. Database `rollmate` akan otomatis dibuat

### Step 3: Fix Password (1 menit)
1. Buka browser: `http://localhost/rollmate/fix_password.php`
2. Tunggu hingga muncul pesan "Password Berhasil Diupdate"
3. Klik tombol "Login Sekarang"

### Step 4: Login (30 detik)
- **Username**: `admin`
- **Password**: `admin123`
- Klik "Login"

### Step 5: Hapus File Fix (30 detik)
```bash
# Hapus file untuk keamanan
del C:\laragon\www\rollmate\fix_password.php
```

## ✅ Selesai!

Anda sekarang bisa:
- ✅ Melihat dashboard
- ✅ Membuat order baru
- ✅ Mengelola order
- ✅ Melakukan quality check

---

## 🎯 Test Fitur Utama

### Test 1: Buat Order Baru (2 menit)
1. Klik "Buat Order Baru" di dashboard
2. Pilih jenis laminasi (contoh: Laminating Glossy)
3. Klik "Lanjutkan"
4. Pilih tipe order (contoh: Order Baru dari Pelanggan)
5. Klik "Lanjutkan ke Form"
6. Isi form:
   - Nama Pelanggan: `Test Customer`
   - Lebar: `100`
   - Panjang: `50`
   - Jumlah: `5`
7. Klik "Simpan Order"
8. Order akan muncul di dashboard

### Test 2: Lihat Daftar Order (1 menit)
1. Klik "Lihat Semua Order"
2. Coba filter berdasarkan status
3. Coba search nama pelanggan
4. Lihat detail order

### Test 3: Quality Check (2 menit)
1. Klik "Buat Order Baru"
2. Pilih jenis laminasi
3. Pilih "Pengecekan Kualitas (Quality Check)"
4. Pilih order yang akan di-QC
5. Pilih hasil QC (LULUS/GAGAL)
6. Isi catatan QC
7. Simpan

---

## 🔑 Akun Default

| Username | Password | Role | Akses |
|----------|----------|------|-------|
| admin | admin123 | Admin | Full access |
| operator1 | admin123 | User | Standard |
| operator2 | admin123 | User | Standard |

⚠️ **PENTING**: Ubah password default setelah login!

---

## 📱 Akses Aplikasi

### Desktop
```
http://localhost/rollmate
```

### Mobile (dalam jaringan yang sama)
```
http://[IP-KOMPUTER]/rollmate
# Contoh: http://192.168.1.100/rollmate
```

Cara cek IP komputer:
```bash
# Windows
ipconfig

# Cari "IPv4 Address"
```

---

## 🎨 Fitur Utama

### 1. Multi-Step Order
- **Step 1**: Pilih jenis laminasi (4 pilihan)
- **Step 2**: Pilih tipe order (4 pilihan)
- **Step 3**: Isi form detail

### 2. Dashboard
- Statistik real-time
- Order terbaru
- Quick actions

### 3. Manajemen Order
- Daftar semua order
- Filter & search
- Pagination

### 4. Quality Check
- Checklist standar
- Status LULUS/GAGAL
- Catatan detail

---

## 🐛 Troubleshooting Cepat

### Login Gagal?
```
Akses: http://localhost/rollmate/fix_password.php
```

### Database Error?
```
1. Cek MySQL running
2. Cek database 'rollmate' sudah dibuat
3. Import ulang database.sql
```

### Tampilan Rusak?
```
1. Cek koneksi internet (untuk TailwindCSS CDN)
2. Clear browser cache (Ctrl + Shift + Delete)
3. Coba browser lain
```

---

## 📚 Dokumentasi Lengkap

- **README.md** - Dokumentasi utama
- **INSTALL.md** - Panduan instalasi detail
- **FIX_LOGIN.md** - Solusi masalah login
- **CONTRIBUTING.md** - Panduan kontribusi

---

## 🎓 Tips untuk Siswa

### Pelajari Kode
```php
// Baca file-file ini untuk belajar:
login.php          // Autentikasi
order.php          // Multi-step form
config/session.php // Session management
config/database.php // Database connection
```

### Eksperimen
- Ubah warna tema
- Tambah field di form
- Buat fitur baru
- Customize tampilan

### Best Practices
- Baca komentar di kode
- Pahami struktur folder
- Test setiap perubahan
- Gunakan Git untuk version control

---

## ✅ Checklist Instalasi

- [ ] Laragon/XAMPP terinstall
- [ ] Apache & MySQL running
- [ ] Database `rollmate` dibuat
- [ ] Password sudah di-fix
- [ ] Berhasil login
- [ ] File `fix_password.php` sudah dihapus
- [ ] Test buat order baru
- [ ] Test semua fitur

---

## 🚀 Next Steps

Setelah instalasi berhasil:

1. **Ubah Password Default**
   - Login sebagai admin
   - Ubah password dari `admin123`

2. **Explore Fitur**
   - Coba semua jenis order
   - Test quality check
   - Lihat laporan

3. **Customize**
   - Ubah logo/branding
   - Sesuaikan warna
   - Tambah fitur baru

4. **Deploy** (Opsional)
   - Setup di hosting
   - Konfigurasi production
   - Backup database

---

## 📞 Butuh Bantuan?

- Baca **FIX_LOGIN.md** untuk masalah login
- Baca **INSTALL.md** untuk instalasi detail
- Baca **README.md** untuk dokumentasi lengkap
- Cek **CONTRIBUTING.md** untuk kontribusi

---

**Selamat menggunakan RollMate! 🎉**

*Aplikasi siap digunakan dalam 5 menit!*
