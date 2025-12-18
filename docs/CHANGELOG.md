# Changelog

Semua perubahan penting pada project RollMate akan didokumentasikan di file ini.

Format berdasarkan [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
dan project ini mengikuti [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-11-11

### ✨ Added - Fitur Baru

#### Sistem Autentikasi
- Login system dengan role-based access (Admin & User)
- Session management yang aman
- Password hashing menggunakan PHP `password_hash()`
- Auto-redirect berdasarkan status login
- Logout functionality

#### Multi-Step Order Creation
- **Step 1**: Pilihan jenis laminasi (Glossy, Doff, Transparan, Kertas Tebal)
- **Step 2**: Pilihan tipe order (Order Baru, Revisi, Pengulangan, QC)
- **Step 3**: Form detail sesuai tipe order
- Animasi transisi antar step (fade & slide)
- Session storage untuk menyimpan pilihan user
- Toast notification setelah pilihan disimpan

#### Form Order
- **Order Baru**: Form lengkap untuk order dari pelanggan baru
- **Order Revisi**: Form dengan referensi ke order sebelumnya
- **Order Pengulangan**: Copy order lama dengan modifikasi jumlah
- **Quality Check**: Form QC dengan checklist standar dan hasil LULUS/GAGAL

#### Dashboard
- Statistik real-time (Total, Pending, In Progress, Completed)
- Tabel order terbaru (5 terakhir)
- Quick access buttons ke fitur utama
- Responsive cards dengan hover effects

#### Manajemen Order
- Daftar semua order dengan pagination
- Filter berdasarkan status
- Search berdasarkan nomor order atau nama pelanggan
- Auto-generate nomor order (ORD-YYYY-XXX)
- Tracking order history

#### Database
- Struktur database lengkap dengan relasi
- Sample data untuk testing
- Order history tracking
- Production logs support
- Quality check records

#### UI/UX
- Design modern mengikuti style Tailwind CSS official
- Fully responsive (Mobile, Tablet, Desktop)
- Gradient backgrounds dan shadows
- Smooth transitions dan hover effects
- Card-based layout
- Icon SVG dari Heroicons
- Font Inter dari Google Fonts

#### Keamanan
- Prepared statements untuk mencegah SQL Injection
- Input validation dan sanitization
- Session-based authentication
- Password hashing
- Protected config files via .htaccess
- CSRF protection ready

#### Dokumentasi
- README.md lengkap dengan fitur dan cara penggunaan
- INSTALL.md dengan panduan instalasi detail
- CHANGELOG.md untuk tracking perubahan
- Komentar kode dalam Bahasa Indonesia
- Troubleshooting guide

### 🎨 Design System

#### Color Palette
- **Primary Blue**: `#2563EB` - Header, buttons, links
- **Secondary Yellow**: `#FBBF24` - CTA buttons
- **Background**: `#F8FAFC` - Neutral light gray
- **Purple**: `#7C3AED` - Revision orders
- **Green**: `#059669` - Repeat orders, success states
- **Orange**: `#EA580C` - Quality check
- **Red**: `#DC2626` - Errors, failed states

#### Typography
- **Font Family**: Inter (Google Fonts)
- **Weights**: 300, 400, 500, 600, 700, 800

#### Components
- Cards dengan rounded corners dan shadows
- Gradient buttons dengan hover effects
- Form inputs dengan focus states
- Status badges dengan color coding
- Progress indicators
- Toast notifications

### 📁 File Structure

```
rollmate/
├── config/
│   ├── database.php       # Database configuration
│   └── session.php        # Session management
├── database.sql           # Database schema & sample data
├── login.php              # Login page
├── logout.php             # Logout handler
├── index.php              # Landing page (auto-redirect)
├── dashboard.php          # Main dashboard
├── order.php              # Multi-step order selection
├── order_baru.php         # New order form
├── order_revisi.php       # Revision order form
├── order_ulangan.php      # Repeat order form
├── order_qc.php           # Quality check form
├── orders_list.php        # All orders list
├── .htaccess              # Apache configuration
├── .gitignore             # Git ignore rules
├── README.md              # Main documentation
├── INSTALL.md             # Installation guide
└── CHANGELOG.md           # This file
```

### 🔧 Technical Stack

- **Backend**: PHP 7.4+ (Native, no framework)
- **Database**: MySQL 5.7+ with PDO
- **Frontend**: HTML5, TailwindCSS 3.0 (CDN)
- **Icons**: Heroicons (SVG)
- **Fonts**: Inter (Google Fonts)
- **Server**: Apache with mod_rewrite

### 📊 Database Tables

1. **users** - User accounts (admin & operators)
2. **orders** - Production orders
3. **order_history** - Order status changes tracking
4. **production_logs** - Production process logs

### 🎯 Target Users

- Siswa SMK jurusan RPL/TKJ
- Operator produksi laminating
- Admin/Manager produksi
- Developer yang ingin belajar PHP native

### 📝 Notes

- Semua password default: `admin123`
- Database sample sudah include 3 users dan 3 orders
- Kode diberi komentar lengkap dalam Bahasa Indonesia
- Siap untuk development dan customization

### 🚀 Future Enhancements (Roadmap)

Fitur yang direncanakan untuk versi berikutnya:

- [ ] Export laporan ke PDF/Excel
- [ ] Upload foto produk
- [ ] Real-time notifications
- [ ] Email notifications
- [ ] Advanced reporting & analytics
- [ ] Multi-language support
- [ ] Dark mode
- [ ] Mobile app (PWA)
- [ ] API endpoints
- [ ] Barcode/QR code generation
- [ ] Inventory management
- [ ] Customer portal
- [ ] Payment tracking

---

## Version Format

- **MAJOR**: Breaking changes yang tidak backward compatible
- **MINOR**: Fitur baru yang backward compatible
- **PATCH**: Bug fixes yang backward compatible

Contoh: `1.0.0` → `1.1.0` → `1.1.1`

---

**Maintained by**: RollMate Development Team  
**Last Updated**: November 11, 2024
