# 🏪 RollMate Marketplace - Sistem Marketplace Laminasi

**Sistem Marketplace dengan 3 Level Akses: Pelanggan, Admin, dan Staff**

![RollMate](https://img.shields.io/badge/Version-1.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.0+-purple)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.0-cyan)

Sistem marketplace modern untuk produk laminasi dengan 3 level akses berbeda: Pelanggan, Admin, dan Staff.

## 🎯 Fitur Utama

### 🛒 Untuk Pelanggan
- **Dashboard Marketplace** - Lihat katalog produk dengan tampilan modern
- **Purchase Request** - Buat permintaan pembelian produk
- **Track Status** - Lacak status request (Pending → Approved → Process → Done)
- **Notifikasi** - Terima notifikasi approval/rejection dari admin
- **Registrasi Mandiri** - Daftar akun sendiri tanpa perlu admin

### 👨‍💼 Untuk Admin
- **Dashboard Manajemen** - Statistik lengkap sistem
- **Approve/Reject Request** - Kelola request masuk dari pelanggan
- **Buat Purchase Order** - Convert request menjadi PO untuk staff
- **Kelola Produk** - CRUD produk (Create, Read, Update, Delete)
- **Kelola User** - Manajemen pelanggan dan staff
- **Laporan** - View reports dan analytics

### 👷 Untuk Staff
- **Dashboard Pekerjaan** - Lihat PO yang ditugaskan
- **Update Status** - Ubah status (Pending → Process → Done)
- **Laporan Hasil** - Tambahkan catatan hasil pekerjaan
- **Timeline** - Track waktu mulai dan selesai

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP Native (PDO)
- **Database**: MySQL
- **Frontend**: HTML5, TailwindCSS 3.0
- **Font**: Inter (Google Fonts)
- **Icons**: Heroicons (SVG)

## 📋 Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web Server (Apache/Nginx)
- Browser modern (Chrome, Firefox, Edge, Safari)

## 🚀 Instalasi

### 1. Clone atau Download Project
```bash
# Letakkan di folder web server Anda
# Contoh: C:\laragon\www\rollmate
```

### 2. Import Database
```bash
# Buka phpMyAdmin atau MySQL client
# Import file: database.sql
# Database akan otomatis dibuat dengan nama: rollmate
```

### 3. Konfigurasi Database
Edit file `config/database.php` sesuai dengan pengaturan MySQL Anda:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'rollmate');
```

### 4. Akses Aplikasi
Buka browser dan akses:
```
http://localhost/rollmate/login.php
```

## 👤 Akun Default

| Role | Username | Password | Dashboard |
|------|----------|----------|----------|
| **Admin** | `admin` | `admin123` | Manajemen sistem |
| **Staff** | `staff1` | `admin123` | Daftar pekerjaan |
| **Pelanggan** | `customer1` | `admin123` | Marketplace |

> ⚠️ **Penting**: Segera ubah password default setelah login pertama kali!

### Registrasi Pelanggan Baru
Pelanggan dapat mendaftar sendiri di: `http://localhost/rollmate/register.php`

## 📁 Struktur Folder

```
rollmate/
├── config/               # Konfigurasi sistem
│   ├── database.php
│   └── session.php
├── pelanggan/            # Dashboard & fitur pelanggan
│   └── dashboard.php
├── admin/                # Dashboard & fitur admin
│   └── dashboard.php
├── staff/                # Dashboard & fitur staff
│   └── dashboard.php
├── docs/                 # Dokumentasi lengkap
│   ├── CARA_PAKAI.md
│   ├── INSTALL.md
│   └── ...
├── index.php             # Landing page
├── login.php             # Halaman login
├── register.php          # Registrasi pelanggan
├── logout.php            # Logout handler
├── database.sql          # Database schema
├── .htaccess             # Apache config
├── .gitignore            # Git ignore
└── README.md             # Dokumentasi ini
```

> 📝 **Catatan**: File lama (order.php, dashboard.php lama, dll) sudah dipindahkan ke folder `old_files/` untuk backup.

## 🎨 Tema Warna

Setiap role memiliki tema warna berbeda:

| Role | Warna Utama | Hex Code | Penggunaan |
|------|-------------|----------|------------|
| **Pelanggan** | Hijau | `#059669` | Dashboard marketplace |
| **Admin** | Biru | `#2563EB` | Dashboard manajemen |
| **Staff** | Ungu | `#7C3AED` | Dashboard pekerjaan |
| **Accent** | Kuning | `#FBBF24` | Tombol CTA |
| **Background** | Abu Muda | `#F8FAFC` | Background netral |

## 📱 Responsive Design

Aplikasi fully responsive dengan breakpoint:
- **Mobile**: < 768px
- **Tablet**: 768px - 1024px
- **Desktop**: > 1024px

## 🔐 Keamanan

- Password di-hash menggunakan `password_hash()` PHP
- Prepared statements untuk mencegah SQL Injection
- Session-based authentication
- CSRF protection ready
- Input validation dan sanitization

## 📊 Database Schema

### Tabel: users
Menyimpan data pengguna (admin dan user)

### Tabel: orders
Menyimpan data order produksi dengan field:
- Informasi pelanggan
- Jenis laminasi
- Tipe order
- Spesifikasi material
- Status order
- Quality check results

### Tabel: order_history
Tracking perubahan status order

### Tabel: production_logs
Log proses produksi

## 🎓 Untuk Siswa SMK

Aplikasi ini dibuat dengan kode yang mudah dipahami:
- ✅ Komentar lengkap dalam Bahasa Indonesia
- ✅ Struktur kode yang rapi dan terorganisir
- ✅ Naming convention yang jelas
- ✅ Best practices PHP dan MySQL
- ✅ Modern UI/UX dengan TailwindCSS

## 🐛 Troubleshooting

### Error: "Username atau password salah" padahal password benar ⚠️

**Penyebab**: Password hash di database tidak cocok dengan sistem PHP Anda.

**Solusi Cepat**:
1. Akses: `http://localhost/rollmate/fix_password.php`
2. Password akan otomatis diupdate
3. Login dengan: `admin` / `admin123`
4. Hapus file `fix_password.php` setelah selesai

**Dokumentasi Lengkap**: Baca file `docs/FIX_LOGIN.md`

## 📚 Dokumentasi Lengkap

Dokumentasi lengkap tersedia di folder `docs/`:

- **[CARA_PAKAI.md](docs/CARA_PAKAI.md)** - Panduan lengkap cara menggunakan sistem
- **[QUICK_START.md](docs/QUICK_START.md)** - Quick start 5 menit
- **[INSTALL.md](docs/INSTALL.md)** - Panduan instalasi detail
- **[STRUKTUR_FOLDER.md](docs/STRUKTUR_FOLDER.md)** - Struktur folder dan file
- **[FIX_LOGIN.md](docs/FIX_LOGIN.md)** - Troubleshooting login
- **[CHANGELOG.md](docs/CHANGELOG.md)** - Catatan perubahan versi
- **[CONTRIBUTING.md](docs/CONTRIBUTING.md)** - Panduan kontribusi

### 🚀 Cara Merapihkan Struktur Folder

Jika Anda baru clone/download project, jalankan script untuk merapihkan:

```bash
# Windows
rapihkan.bat

# Atau manual, lihat file STRUKTUR_BARU.md
```

### Error: "Koneksi database gagal"
- Pastikan MySQL sudah running
- Cek konfigurasi di `config/database.php`
- Pastikan database `rollmate` sudah dibuat

### Error: "Session tidak berfungsi"
- Pastikan PHP session sudah enabled
- Cek permission folder temporary PHP

### Tampilan tidak muncul dengan benar
- Pastikan koneksi internet aktif (untuk load TailwindCSS CDN)
- Clear browser cache
- Coba browser lain

## 📝 Lisensi

Project ini dibuat untuk keperluan edukasi dan pembelajaran.

## 👨‍💻 Pengembangan Lebih Lanjut

Fitur yang bisa ditambahkan:
- [ ] Export laporan ke PDF/Excel
- [ ] Notifikasi real-time
- [ ] Upload foto hasil produksi
- [ ] Grafik statistik produksi
- [ ] API untuk integrasi dengan sistem lain
- [ ] Multi-language support
- [ ] Dark mode

## 📞 Support

Jika ada pertanyaan atau masalah, silakan buat issue atau hubungi developer.

---

**Dibuat dengan ❤️ untuk pembelajaran siswa SMK**

*Happy Coding! 🚀*
