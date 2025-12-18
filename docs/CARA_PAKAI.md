# 🚀 Cara Pakai RollMate Marketplace

## 📥 Langkah 1: Import Database

1. Buka phpMyAdmin: `http://localhost/phpmyadmin`
2. Klik tab "SQL"
3. Copy-paste seluruh isi file `database.sql`
4. Klik "Go"
5. Database `rollmate` akan otomatis dibuat

## 🔧 Langkah 2: Fix Password

**PENTING: Lakukan ini setelah import database!**

1. Buka browser: `http://localhost/rollmate/fix_password.php`
2. Tunggu hingga muncul pesan "Password Berhasil Diupdate"
3. Lihat tabel user yang sudah diupdate
4. **HAPUS file `fix_password.php` setelah selesai!**

```bash
# Hapus file untuk keamanan
del C:\laragon\www\rollmate\fix_password.php
```

## 👥 Langkah 3: Login

Akses: `http://localhost/rollmate/login.php`

### Akun Default:

| Role | Username | Password | Dashboard |
|------|----------|----------|-----------|
| **Admin** | admin | admin123 | Kelola request, buat PO, kelola produk |
| **Staff** | staff1 | admin123 | Terima PO, update status pekerjaan |
| **Pelanggan** | customer1 | admin123 | Lihat produk, buat request |

## 📝 Langkah 4: Daftar Akun Pelanggan Baru

Jika ingin membuat akun pelanggan baru:

1. Di halaman login, klik "**Daftar sebagai Pelanggan**"
2. Atau akses langsung: `http://localhost/rollmate/register.php`
3. Isi form registrasi:
   - Username (minimal 4 karakter)
   - Email (format valid)
   - Password (minimal 6 karakter)
   - Nama lengkap
   - No. telepon (opsional)
   - Nama perusahaan (opsional)
   - Alamat (opsional)
4. Klik "**Daftar Sekarang**"
5. Setelah berhasil, Anda akan otomatis diarahkan ke halaman login
6. Login dengan username dan password yang baru dibuat

## 🎯 Alur Kerja Sistem

### Untuk Pelanggan:
1. **Login** → Dashboard Marketplace
2. **Lihat Produk** → Katalog produk laminasi
3. **Buat Request** → Isi form purchase request
4. **Tunggu Approval** → Admin akan approve/reject
5. **Upload Pembayaran** → Jika di-approve
6. **Track Status** → Lihat progress order (Pending → Process → Done)

### Untuk Admin:
1. **Login** → Dashboard Admin
2. **Lihat Request Masuk** → Daftar request dari pelanggan
3. **Approve/Reject** → Tentukan status request
4. **Buat Purchase Order** → Jika request di-approve
5. **Assign ke Staff** → PO dikirim ke staff
6. **Monitor Progress** → Lihat status pekerjaan

### Untuk Staff:
1. **Login** → Dashboard Staff
2. **Lihat PO** → Daftar purchase order
3. **Mulai Kerja** → Update status ke "Process"
4. **Selesai** → Update status ke "Done"
5. **Laporan** → Tambah catatan hasil pekerjaan

## ⚠️ Troubleshooting

### Login Gagal - "Username atau password salah"

**Solusi:**
```
1. Akses: http://localhost/rollmate/fix_password.php
2. Password akan otomatis diupdate
3. Login lagi dengan: admin / admin123
4. Hapus file fix_password.php
```

### Database Error

**Solusi:**
```
1. Pastikan MySQL sudah running
2. Pastikan database 'rollmate' sudah dibuat
3. Import ulang database.sql jika perlu
4. Cek config/database.php (username: root, password: kosong)
```

### Registrasi Gagal - "Username sudah digunakan"

**Solusi:**
```
Gunakan username yang berbeda atau login dengan akun yang sudah ada
```

### Registrasi Gagal - "Email sudah terdaftar"

**Solusi:**
```
Gunakan email yang berbeda atau login dengan akun yang sudah ada
```

## 🔐 Keamanan

### Setelah Instalasi:

1. ✅ **Hapus fix_password.php** setelah digunakan
2. ✅ **Ubah password default** setelah login pertama kali
3. ✅ **Jangan gunakan di production** tanpa review keamanan
4. ✅ **Backup database** secara berkala

### Password Requirements:

- Minimal 6 karakter
- Kombinasi huruf dan angka (recommended)
- Jangan gunakan password yang mudah ditebak

## 📱 Akses dari Perangkat Lain

Jika ingin akses dari HP/laptop lain dalam jaringan yang sama:

1. Cek IP komputer server:
   ```bash
   ipconfig
   # Cari "IPv4 Address"
   ```

2. Akses dari perangkat lain:
   ```
   http://[IP-KOMPUTER]/rollmate/login.php
   # Contoh: http://192.168.1.100/rollmate/login.php
   ```

## 🎨 Fitur yang Tersedia

### ✅ Sudah Dibuat:
- Login system dengan 3 role
- Registrasi pelanggan
- Fix password utility
- Database lengkap dengan sample data

### 🔄 Sedang Dikembangkan:
- Dashboard Pelanggan (Marketplace)
- Dashboard Admin (Manajemen)
- Dashboard Staff (Pekerjaan)
- Form Purchase Request
- Sistem Approval
- Upload Pembayaran
- Notifikasi

## 📞 Bantuan

Jika masih ada masalah:

1. Cek file `README.md` untuk dokumentasi lengkap
2. Cek file `FIX_LOGIN.md` untuk troubleshooting login
3. Pastikan semua langkah instalasi sudah diikuti

---

**Selamat menggunakan RollMate Marketplace! 🎉**

*Sistem Marketplace Laminasi dengan 3 Level Akses*
