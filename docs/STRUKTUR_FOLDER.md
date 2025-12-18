# 📁 Struktur Folder RollMate Marketplace

Struktur folder yang terorganisir berdasarkan role dan fungsi.

## 🗂️ Struktur Lengkap

```
rollmate/
│
├── 📁 config/                      # Konfigurasi sistem
│   ├── database.php                # Koneksi database
│   └── session.php                 # Manajemen session & auth
│
├── 📁 pelanggan/                   # Dashboard & fitur pelanggan
│   ├── dashboard.php               # Dashboard marketplace
│   ├── products.php                # Katalog produk (coming soon)
│   ├── my_requests.php             # Daftar request saya (coming soon)
│   └── request_form.php            # Form purchase request (coming soon)
│
├── 📁 admin/                       # Dashboard & fitur admin
│   ├── dashboard.php               # Dashboard manajemen
│   ├── requests.php                # Kelola request masuk (coming soon)
│   ├── purchase_orders.php         # Kelola PO (coming soon)
│   ├── products.php                # CRUD produk (coming soon)
│   ├── users.php                   # Kelola user (coming soon)
│   └── reports.php                 # Laporan (coming soon)
│
├── 📁 staff/                       # Dashboard & fitur staff
│   ├── dashboard.php               # Dashboard pekerjaan
│   └── po_detail.php               # Detail PO (coming soon)
│
├── 📄 index.php                    # Landing page (redirect)
├── 📄 login.php                    # Halaman login
├── 📄 register.php                 # Registrasi pelanggan
├── 📄 logout.php                   # Logout handler
├── 📄 database.sql                 # Database schema
│
├── 📚 README.md                    # Dokumentasi utama
├── 📚 CARA_PAKAI.md                # Panduan penggunaan
├── 📚 STRUKTUR_FOLDER.md           # File ini
├── 📚 FIX_LOGIN.md                 # Troubleshooting login
├── 📚 INSTALL.md                   # Panduan instalasi
├── 📚 CHANGELOG.md                 # Catatan perubahan
├── 📚 CONTRIBUTING.md              # Panduan kontribusi
├── 📚 QUICK_START.md               # Quick start guide
│
├── 📄 .htaccess                    # Apache configuration
├── 📄 .gitignore                   # Git ignore rules
└── 📄 LICENSE                      # MIT License
```

## 🎯 Penjelasan Struktur

### 📁 `/config/`
**Fungsi:** Menyimpan file konfigurasi sistem

- `database.php` - Koneksi database dengan PDO
- `session.php` - Fungsi session, auth, dan notifikasi

**Akses:** Digunakan oleh semua file PHP

---

### 📁 `/pelanggan/`
**Fungsi:** Dashboard dan fitur untuk pelanggan

**File Utama:**
- `dashboard.php` - Tampilan marketplace dengan katalog produk
- `products.php` - Halaman katalog produk lengkap
- `my_requests.php` - Daftar request yang dibuat pelanggan
- `request_form.php` - Form untuk membuat purchase request

**Akses:** Hanya user dengan role `pelanggan`

**Fitur:**
- ✅ Lihat katalog produk
- ✅ Buat purchase request
- ✅ Track status request
- ✅ Lihat notifikasi approval/rejection

---

### 📁 `/admin/`
**Fungsi:** Dashboard dan fitur untuk admin

**File Utama:**
- `dashboard.php` - Dashboard manajemen dengan statistik
- `requests.php` - Kelola request masuk (approve/reject)
- `purchase_orders.php` - Kelola dan buat PO
- `products.php` - CRUD produk (Create, Read, Update, Delete)
- `users.php` - Kelola user (pelanggan, staff, admin)
- `reports.php` - Laporan dan statistik

**Akses:** Hanya user dengan role `admin`

**Fitur:**
- ✅ Approve/reject request pelanggan
- ✅ Buat Purchase Order
- ✅ Assign PO ke staff
- ✅ Kelola produk
- ✅ Kelola user
- ✅ Lihat laporan

---

### 📁 `/staff/`
**Fungsi:** Dashboard dan fitur untuk staff produksi

**File Utama:**
- `dashboard.php` - Dashboard pekerjaan dengan daftar PO
- `po_detail.php` - Detail PO dan update status

**Akses:** Hanya user dengan role `staff`

**Fitur:**
- ✅ Lihat PO yang ditugaskan
- ✅ Update status (Pending → Process → Done)
- ✅ Tambah laporan hasil pekerjaan

---

### 📄 File Root

**File Autentikasi:**
- `index.php` - Landing page, redirect ke login/dashboard
- `login.php` - Halaman login untuk 3 role
- `register.php` - Registrasi akun pelanggan baru
- `logout.php` - Handler logout

**File Database:**
- `database.sql` - Schema database lengkap dengan sample data

**File Dokumentasi:**
- `README.md` - Dokumentasi utama aplikasi
- `CARA_PAKAI.md` - Panduan penggunaan step-by-step
- `STRUKTUR_FOLDER.md` - Dokumentasi struktur folder (file ini)
- `FIX_LOGIN.md` - Troubleshooting masalah login
- `INSTALL.md` - Panduan instalasi detail
- `CHANGELOG.md` - Catatan perubahan versi
- `CONTRIBUTING.md` - Panduan kontribusi
- `QUICK_START.md` - Quick start 5 menit

**File Konfigurasi:**
- `.htaccess` - Konfigurasi Apache (security, routing)
- `.gitignore` - File yang diabaikan Git
- `LICENSE` - MIT License

---

## 🔐 Akses Berdasarkan Role

### Pelanggan
```
✅ /pelanggan/dashboard.php
✅ /pelanggan/products.php
✅ /pelanggan/my_requests.php
✅ /pelanggan/request_form.php
❌ /admin/* (tidak bisa akses)
❌ /staff/* (tidak bisa akses)
```

### Admin
```
✅ /admin/dashboard.php
✅ /admin/requests.php
✅ /admin/purchase_orders.php
✅ /admin/products.php
✅ /admin/users.php
✅ /admin/reports.php
❌ /pelanggan/* (redirect ke admin)
❌ /staff/* (redirect ke admin)
```

### Staff
```
✅ /staff/dashboard.php
✅ /staff/po_detail.php
❌ /pelanggan/* (redirect ke staff)
❌ /admin/* (tidak bisa akses)
```

---

## 🚀 Alur Akses

### 1. Login
```
User → login.php → Cek role → Redirect ke dashboard sesuai role
```

### 2. Pelanggan
```
Login → pelanggan/dashboard.php → Lihat produk → Buat request → Track status
```

### 3. Admin
```
Login → admin/dashboard.php → Lihat request → Approve → Buat PO → Assign staff
```

### 4. Staff
```
Login → staff/dashboard.php → Lihat PO → Mulai kerja → Selesai
```

---

## 📊 Database Tables

Tabel yang digunakan dalam sistem:

1. **users** - Data user (3 role)
2. **products** - Katalog produk
3. **requests** - Purchase request dari pelanggan
4. **payments** - Data pembayaran
5. **purchase_orders** - PO dari admin ke staff
6. **notifications** - Notifikasi user

---

## 🎨 Desain Dashboard

### Pelanggan (Marketplace)
- **Warna:** Hijau (`green-600`)
- **Style:** Card produk, grid layout
- **Fokus:** User-friendly, mudah pesan

### Admin (Manajemen)
- **Warna:** Biru (`blue-600`)
- **Style:** Sidebar, tabel data
- **Fokus:** Efisiensi, banyak informasi

### Staff (Pekerjaan)
- **Warna:** Ungu (`purple-600`)
- **Style:** List PO, progress bar
- **Fokus:** Sederhana, fokus ke pekerjaan

---

## 📝 Naming Convention

### File PHP
- Lowercase dengan underscore: `my_requests.php`
- Deskriptif: `purchase_orders.php` bukan `po.php`

### Folder
- Lowercase: `pelanggan/`, `admin/`, `staff/`
- Sesuai role: jelas dan mudah dipahami

### Database
- Table: lowercase, plural: `users`, `products`
- Column: snake_case: `customer_id`, `created_at`

---

## ✅ Checklist Struktur

- [x] Folder config untuk konfigurasi
- [x] Folder terpisah untuk setiap role
- [x] File dokumentasi lengkap
- [x] Naming convention konsisten
- [x] Akses control berdasarkan role
- [x] Dashboard berbeda untuk setiap role

---

## 🔄 Update Selanjutnya

File yang akan ditambahkan:

### Pelanggan
- [ ] `pelanggan/products.php` - Katalog lengkap
- [ ] `pelanggan/my_requests.php` - Daftar request
- [ ] `pelanggan/request_form.php` - Form request
- [ ] `pelanggan/payment.php` - Upload bukti bayar

### Admin
- [ ] `admin/requests.php` - Kelola request
- [ ] `admin/purchase_orders.php` - Kelola PO
- [ ] `admin/products.php` - CRUD produk
- [ ] `admin/users.php` - Kelola user
- [ ] `admin/reports.php` - Laporan

### Staff
- [ ] `staff/po_detail.php` - Detail PO
- [ ] `staff/update_status.php` - Update status

---

**Struktur folder yang rapi = Kode yang mudah di-maintain! 🎯**
