# 🏦 Sistem Informasi Nusamba

Sistem informasi manajemen kredit dan jaminan untuk lembaga keuangan Nusamba. Aplikasi web berbasis PHP yang menyediakan fitur lengkap untuk pengelolaan data nasabah, pengajuan kredit, jaminan, dan monitoring.

## 📋 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Teknologi yang Digunakan](#-teknologi-yang-digunakan)
- [Struktur Project](#-struktur-project)
- [Persyaratan Sistem](#-persyaratan-sistem)
- [Instalasi](#-instalasi)
- [Konfigurasi Database](#-konfigurasi-database)
- [Cara Penggunaan](#-cara-penggunaan)
- [Struktur Database](#-struktur-database)
- [API dan Integrasi](#-api-dan-integrasi)
- [Keamanan](#-keamanan)
- [Troubleshooting](#-troubleshooting)
- [Kontribusi](#-kontribusi)
- [Lisensi](#-lisensi)

## ✨ Fitur Utama

### 👥 Manajemen User & Role

- Sistem login dengan autentikasi multi-level
- Role-based access control (Admin, CS, AO, dll)
- Manajemen profil user dengan foto dan cover
- Tracking aktivitas user

### 👤 Data Nasabah

- Input dan edit data nasabah lengkap
- Validasi NIK 16 digit
- Pencarian dan filter data nasabah
- Export data ke Excel/PDF

### 💰 Pengajuan Kredit

- Form pengajuan kredit dengan validasi
- Perhitungan otomatis angsuran
- Tracking status pengajuan
- Monitoring progress

### 🏠 Jaminan Kredit

- **BPKB**: Jaminan kendaraan bermotor
- **Sertifikat**: Jaminan tanah/bangunan
- **Akta**: Jaminan properti
- **Bilyet**: Jaminan tabungan/deposito
- **Kios**: Jaminan usaha
- **BPIH**: Jaminan asuransi
- **SPPH**: Jaminan lainnya

### 📊 Monitoring & Reporting

- Dashboard real-time
- Monitoring nasabah
- Monitoring teller
- Laporan kunjungan
- Export laporan ke Excel/PDF

### 🏢 Administrasi

- Manajemen kantor cabang
- Virtual account management
- Backup database
- File management

## 🛠 Teknologi yang Digunakan

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework**: Bootstrap 5.3.2
- **Icons**: Bootstrap Icons 1.11.1
- **PDF**: mPDF 8.0
- **Charts**: Chart.js
- **Notifications**: SweetAlert2
- **Date Picker**: Custom JavaScript
- **Fonts**: Poppins, Nunito, Pogonia

## 📁 Struktur Project

```
nusamba/
├── administrasi/          # Modul administrasi
├── assets/               # Assets (CSS, JS, images)
│   ├── css/
│   ├── js/
│   ├── fonts/
│   └── media/
├── cpanel/               # Panel admin
├── cs/                   # Modul customer service
├── config/               # Konfigurasi aplikasi
├── includes/             # Komponen yang dapat digunakan ulang
├── ketentuanku/          # Manajemen ketentuan
├── komite/               # Modul komite
├── ksu/                  # Modul KSU
├── monitoring-nasabah/   # Monitoring nasabah
├── monitoring-teller/    # Monitoring teller
├── profile/              # Manajemen profil
├── tracking/             # Tracking pengajuan
├── virtual-account/      # Manajemen virtual account
├── vendor/               # Dependencies (Composer)
├── dashboard.php         # Dashboard utama
├── login.php            # Halaman login
├── index.php            # Entry point
└── composer.json        # Dependencies
```

## ⚙️ Persyaratan Sistem

- **PHP**: 7.4 atau lebih tinggi
- **MySQL**: 5.7 atau lebih tinggi
- **Web Server**: Apache/Nginx
- **Memory**: Minimal 512MB RAM
- **Storage**: Minimal 1GB free space
- **Browser**: Chrome, Firefox, Safari, Edge (versi terbaru)

## 🚀 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/username/nusamba.git
cd nusamba
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Konfigurasi Web Server

Pastikan web server mengarah ke direktori project dan mod_rewrite aktif.

### 4. Set Permissions

```bash
chmod 755 -R nusamba/
chmod 777 -R assets/media/
chmod 777 -R ketentuanku/uploads/
chmod 777 -R monitoring-teller/uploads/
```

## 🗄️ Konfigurasi Database

### 1. Buat Database

```sql
CREATE DATABASE nusamba_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Import Database

```bash
mysql -u username -p nusamba_db < database/nusamba.sql
```

### 3. Konfigurasi Koneksi

Edit file `config.php` di root directory:

```php
<?php
$host = 'localhost';
$username = 'your_db_username';
$password = 'your_db_password';
$database = 'nusamba_db';

$connect = mysqli_connect($host, $username, $password, $database);

if (!$connect) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>
```

### 4. Konfigurasi Base URL

Edit file `config/config.php`:

```php
<?php
$base_url = "https://yourdomain.com/nusamba/";
date_default_timezone_set('Asia/Jakarta');
?>
```

## 📖 Cara Penggunaan

### Login Sistem

1. Akses `https://yourdomain.com/nusamba/`
2. Masukkan username dan password
3. Pilih role sesuai akses

### Input Data Nasabah

1. Menu: **CS > Input Nasabah**
2. Isi form data lengkap
3. Validasi NIK 16 digit
4. Simpan data

### Pengajuan Kredit

1. Menu: **CS > Input Pengajuan**
2. Pilih nasabah
3. Isi data pengajuan
4. Upload dokumen pendukung
5. Submit pengajuan

### Input Jaminan

1. Menu: **CS > Input Jaminan**
2. Pilih jenis jaminan
3. Isi data jaminan
4. Upload dokumen jaminan
5. Simpan data

### Monitoring

1. Menu: **Monitoring**
2. Pilih jenis monitoring
3. Filter data sesuai kebutuhan
4. Export laporan jika diperlukan

## 🗃️ Struktur Database

### Tabel Utama

- `account` - Data user dan role
- `nasabah` - Data nasabah
- `pengajuan` - Data pengajuan kredit
- `bpkb` - Jaminan BPKB
- `sertifikat` - Jaminan sertifikat
- `akta` - Jaminan akta
- `bilyet` - Jaminan bilyet
- `kios` - Jaminan kios
- `bpih` - Jaminan BPIH
- `spph` - Jaminan SPPH
- `penjamin` - Data penjamin
- `pendamping` - Data pendamping

### Relasi Database

- Nasabah → Pengajuan (1:N)
- Nasabah → Jaminan (1:N)
- Nasabah → Penjamin (1:N)
- Pengajuan → Tracking (1:N)

## 🔌 API dan Integrasi

### API Daerah

- Integrasi dengan API provinsi/kota
- Auto-fill data alamat
- Validasi kode pos

### Export Data

- Export ke Excel (.xlsx)
- Export ke PDF
- Custom template laporan

### Notifikasi

- SweetAlert2 untuk notifikasi
- Email notification (opsional)
- SMS gateway (opsional)

## 🔒 Keamanan

### Autentikasi

- Session-based authentication
- Role-based access control
- Password hashing
- Session timeout

### Validasi Input

- SQL injection prevention
- XSS protection
- File upload validation
- Input sanitization

### Keamanan File

- File type validation
- File size limit
- Secure file storage
- Access control

## 🔧 Troubleshooting

### Error Session

Jika mengalami error session, tambahkan di file PHP:

```php
require_once __DIR__ . '/../config/init.php';
```

### Database Connection

Pastikan konfigurasi database benar dan service MySQL berjalan.

### File Upload

Periksa permission folder upload dan konfigurasi PHP upload settings.

### Performance

- Optimasi query database
- Enable caching
- Compress assets
- CDN untuk static files

## 🤝 Kontribusi

1. Fork repository
2. Buat branch fitur baru (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

### Guidelines

- Ikuti coding standards PHP PSR-12
- Tambahkan komentar untuk fungsi kompleks
- Test fitur sebelum submit
- Update dokumentasi jika diperlukan

## 📄 Lisensi

Project ini dilisensikan di bawah [MIT License](LICENSE).

## 📞 Support

Untuk pertanyaan dan dukungan:

- Email: support@nusamba.com
- WhatsApp: +62 812-3456-7890
- Dokumentasi: [docs.nusamba.com](https://docs.nusamba.com)

---

**Nusamba** - Sistem Informasi Manajemen Kredit & Jaminan
_Dibuat dengan ❤️ untuk kemudahan pengelolaan kredit_
