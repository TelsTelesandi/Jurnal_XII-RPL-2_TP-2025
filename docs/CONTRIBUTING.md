# 🤝 Panduan Kontribusi

Terima kasih atas minat Anda untuk berkontribusi pada project RollMate! Dokumen ini berisi panduan untuk membantu Anda berkontribusi dengan efektif.

## 📋 Cara Berkontribusi

Ada beberapa cara untuk berkontribusi:

### 1. 🐛 Melaporkan Bug

Jika menemukan bug, buat issue dengan informasi:
- Deskripsi bug yang jelas
- Langkah-langkah untuk reproduce bug
- Expected behavior vs actual behavior
- Screenshot (jika perlu)
- Environment (PHP version, MySQL version, OS, Browser)

### 2. 💡 Mengusulkan Fitur Baru

Untuk fitur baru:
- Jelaskan fitur yang diusulkan
- Mengapa fitur ini berguna
- Bagaimana cara kerjanya
- Mockup atau wireframe (jika ada)

### 3. 📝 Memperbaiki Dokumentasi

Dokumentasi yang baik sangat penting:
- Perbaiki typo atau kesalahan
- Tambahkan penjelasan yang lebih jelas
- Tambahkan contoh penggunaan
- Terjemahkan ke bahasa lain

### 4. 💻 Menulis Kode

Untuk kontribusi kode:
- Fork repository
- Buat branch baru untuk fitur/fix
- Tulis kode dengan style guide yang konsisten
- Test perubahan Anda
- Buat pull request

## 🎨 Style Guide

### PHP Code Style

```php
<?php
/**
 * File: nama_file.php
 * Fungsi: Deskripsi singkat fungsi file
 */

// Gunakan camelCase untuk variabel
$userName = 'John Doe';
$orderNumber = 'ORD-2024-001';

// Gunakan PascalCase untuk class
class OrderManager {
    // Class code here
}

// Gunakan snake_case untuk nama file
// Contoh: order_baru.php, config/database.php

// Indentasi: 4 spaces
if ($condition) {
    // Code here
}

// Brace di baris baru untuk function
function processOrder($orderId) 
{
    // Code here
}

// Komentar dalam Bahasa Indonesia untuk siswa SMK
// Gunakan komentar yang jelas dan deskriptif
```

### HTML/CSS Style

```html
<!-- Gunakan indentasi 4 spaces -->
<div class="container">
    <div class="card">
        <h1 class="title">Judul</h1>
        <p class="description">Deskripsi</p>
    </div>
</div>

<!-- TailwindCSS classes: urut dari layout → spacing → typography → colors -->
<button class="flex items-center px-6 py-3 text-lg font-semibold text-white bg-blue-600 rounded-lg">
    Button
</button>
```

### SQL Style

```sql
-- Gunakan UPPERCASE untuk SQL keywords
SELECT * FROM orders WHERE status = 'pending';

-- Gunakan snake_case untuk nama tabel dan kolom
CREATE TABLE order_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indentasi untuk query panjang
SELECT 
    o.order_number,
    o.customer_name,
    u.full_name as created_by
FROM orders o
LEFT JOIN users u ON o.created_by = u.id
WHERE o.status = 'pending'
ORDER BY o.created_at DESC;
```

## 🔍 Testing

Sebelum submit pull request, pastikan:

### Manual Testing Checklist

- [ ] Login sebagai admin berfungsi
- [ ] Login sebagai user berfungsi
- [ ] Buat order baru berhasil
- [ ] Form validasi berjalan dengan benar
- [ ] Data tersimpan ke database
- [ ] Toast notification muncul
- [ ] Responsive di mobile dan desktop
- [ ] Tidak ada error di browser console
- [ ] Tidak ada PHP error/warning

### Browser Testing

Test di minimal 2 browser:
- [ ] Google Chrome
- [ ] Mozilla Firefox
- [ ] Microsoft Edge
- [ ] Safari (jika ada Mac)

### Database Testing

- [ ] Query berjalan dengan efisien
- [ ] Tidak ada N+1 query problem
- [ ] Foreign key constraints berfungsi
- [ ] Data integrity terjaga

## 📦 Pull Request Process

### 1. Fork & Clone

```bash
# Fork repository di GitHub
# Clone fork Anda
git clone https://github.com/YOUR_USERNAME/rollmate.git
cd rollmate
```

### 2. Buat Branch Baru

```bash
# Buat branch dengan nama deskriptif
git checkout -b feature/add-export-pdf
# atau
git checkout -b fix/login-session-bug
```

### 3. Commit Changes

```bash
# Add files
git add .

# Commit dengan message yang jelas
git commit -m "Add: Export order to PDF feature"
# atau
git commit -m "Fix: Session timeout issue on login"
```

**Commit Message Format:**
- `Add:` untuk fitur baru
- `Fix:` untuk bug fix
- `Update:` untuk update fitur existing
- `Refactor:` untuk refactoring code
- `Docs:` untuk perubahan dokumentasi
- `Style:` untuk perubahan formatting

### 4. Push & Create PR

```bash
# Push ke fork Anda
git push origin feature/add-export-pdf

# Buat Pull Request di GitHub
# Isi deskripsi PR dengan detail perubahan
```

### 5. PR Description Template

```markdown
## Deskripsi
Jelaskan perubahan yang dilakukan

## Tipe Perubahan
- [ ] Bug fix
- [ ] Fitur baru
- [ ] Breaking change
- [ ] Dokumentasi

## Testing
Jelaskan bagaimana Anda test perubahan ini

## Screenshots (jika ada)
Tambahkan screenshot untuk perubahan UI

## Checklist
- [ ] Code mengikuti style guide
- [ ] Sudah di-test secara manual
- [ ] Dokumentasi sudah diupdate
- [ ] Tidak ada breaking changes
```

## 🎓 Untuk Siswa SMK

### Tips Kontribusi Pertama

1. **Mulai dari yang Kecil**
   - Perbaiki typo di dokumentasi
   - Tambahkan komentar yang lebih jelas
   - Perbaiki formatting code

2. **Pelajari Kode yang Ada**
   - Baca file-file yang ada
   - Pahami struktur folder
   - Lihat bagaimana fitur diimplementasi

3. **Bertanya Jika Bingung**
   - Jangan ragu untuk bertanya
   - Buat issue untuk diskusi
   - Minta review dari kontributor lain

4. **Dokumentasikan Perubahan**
   - Tulis komentar yang jelas
   - Update README jika perlu
   - Buat dokumentasi untuk fitur baru

### Ide Kontribusi untuk Pemula

**Level 1 - Easy:**
- Perbaiki typo di dokumentasi
- Tambahkan komentar di kode
- Ubah warna tema
- Tambahkan validasi form

**Level 2 - Medium:**
- Tambahkan field baru di form
- Buat halaman baru
- Tambahkan filter/search
- Buat export ke CSV

**Level 3 - Advanced:**
- Implementasi fitur upload foto
- Buat sistem notifikasi
- Tambahkan API endpoints
- Implementasi real-time updates

## 🏆 Recognition

Kontributor yang aktif akan:
- Dicantumkan di README.md
- Mendapat badge kontributor
- Diakui dalam release notes

## 📞 Komunikasi

- **Issues**: Untuk bug reports dan feature requests
- **Pull Requests**: Untuk kontribusi kode
- **Discussions**: Untuk diskusi umum

## ⚖️ Code of Conduct

### Kami Berkomitmen Untuk:

- Menciptakan lingkungan yang ramah dan inklusif
- Menghormati sudut pandang dan pengalaman yang berbeda
- Menerima kritik konstruktif dengan baik
- Fokus pada apa yang terbaik untuk komunitas

### Tidak Diperbolehkan:

- Bahasa atau gambar yang tidak pantas
- Trolling atau komentar yang menghina
- Harassment dalam bentuk apapun
- Mempublikasikan informasi pribadi orang lain

## 📚 Resources

### Belajar Git & GitHub
- [Git Handbook](https://guides.github.com/introduction/git-handbook/)
- [GitHub Flow](https://guides.github.com/introduction/flow/)
- [How to Contribute to Open Source](https://opensource.guide/how-to-contribute/)

### Belajar PHP
- [PHP Manual](https://www.php.net/manual/en/)
- [PHP The Right Way](https://phptherightway.com/)
- [W3Schools PHP Tutorial](https://www.w3schools.com/php/)

### Belajar MySQL
- [MySQL Tutorial](https://www.mysqltutorial.org/)
- [SQL for Beginners](https://www.w3schools.com/sql/)

### Belajar TailwindCSS
- [Tailwind Documentation](https://tailwindcss.com/docs)
- [Tailwind UI Components](https://tailwindui.com/)

## 🙏 Terima Kasih!

Terima kasih telah meluangkan waktu untuk berkontribusi pada RollMate. Setiap kontribusi, sekecil apapun, sangat berarti untuk project ini!

---

**Happy Contributing! 🚀**

*Dibuat dengan ❤️ untuk komunitas developer Indonesia*
