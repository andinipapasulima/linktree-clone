# ⚡ Neo-Linktree

<div align="center">

![Neo-Linktree Banner](https://img.shields.io/badge/Neo--Linktree-Biodata%20Link%20Modern-b460f3?style=for-the-badge&labelColor=1a1a1a)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=for-the-badge&logo=php&logoColor=white&labelColor=1a1a1a)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white&labelColor=1a1a1a)
![XAMPP](https://img.shields.io/badge/XAMPP-Compatible-FB7A24?style=for-the-badge&logo=xampp&logoColor=white&labelColor=1a1a1a)
![Chart.js](https://img.shields.io/badge/Chart.js-4.4-FF6384?style=for-the-badge&logo=chartdotjs&logoColor=white&labelColor=1a1a1a)
![License](https://img.shields.io/badge/License-MIT-94ffd8?style=for-the-badge&labelColor=1a1a1a)

**Platform biodata link modern bergaya Neo-Brutalisme & Pastel Pop.**  
Buat halaman profil dengan semua tautanmu dalam satu tempat — stylish, cepat, dan gratis.

[✨ Demo](#demo) · [🚀 Instalasi](#instalasi) · [📖 Fitur](#fitur) · [🗄️ Database](#database) · [🤝 Kontribusi](#kontribusi)

</div>

---

## 📸 Tampilan Antarmuka

| Halaman Publik | Dashboard Admin | Analytics |
|:---:|:---:|:---:|
| Profil pengguna dengan tombol tautan bergaya retro | Kelola semua tautan dari satu panel | Statistik klik per tautan |

---

## ✨ Fitur

### 👤 Manajemen Pengguna
- **Registrasi & Login** — autentikasi aman dengan `password_hash()`
- **Edit Profil** — nama, bio, foto profil, warna tema, dan Custom CSS
- **Background Kustom** — upload gambar background untuk halaman publik
- **Remember Me** — sesi otomatis 7 hari dengan cookie

### 🔗 Manajemen Tautan
- **Tambah / Edit / Hapus** tautan dengan mudah
- **Aktif / Nonaktif** toggle tautan secara instan
- **Atur Urutan** — naik/turun dengan satu klik
- **Icon Kustom** — pilih dari 20+ ikon Font Awesome (Instagram, TikTok, YouTube, dll.)
- **Jadwal Otomatis** — tautan tampil dan hilang pada waktu tertentu
- **Short URL** — buat link pendek custom seperti `website.com/s/igku`

### 📊 Analitik
- **Jumlah Klik** — tracking per tautan secara real-time
- **Visitor Unik** — pencatatan IP, perangkat (Mobile/Desktop/Tablet), dan browser
- **Dashboard Statistik** — total tautan, total klik, tautan aktif, visitor unik
- **Grafik Visitor Harian** — line chart 7 hari terakhir (Chart.js)
- **Bar Chart Klik** — perbandingan klik antar tautan secara visual
- **Doughnut Chart Perangkat** — breakdown Mobile / Desktop / Tablet
- **Peringkat Tautan** — tabel dengan progress bar persentase klik

### ⚙️ Fitur Teknis
- **Export JSON** — backup semua tautan ke file `.json`
- **Import JSON** — restore tautan dari file export
- **Search/Filter** — pencarian tautan real-time di dashboard & halaman publik
- **Dark Mode** — toggle tema gelap di halaman publik
- **Responsive** — optimal di semua ukuran layar
- **Anti Auto-Zoom** — font 16px pada input untuk iOS/Android
- **Open Graph** — meta tag untuk pratinjau saat dibagikan di media sosial

---

## 🗂️ Struktur Proyek

```
neo-linktree/
│
├── 📄 index / Routing
│   ├── .htaccess              # URL rewrite: /username → u.php?user=username
│   ├── u.php                  # Halaman publik profil pengguna
│   ├── short.php              # Redirect short URL (/s/kode)
│   ├── klik.php               # Tracker klik & redirect ke URL tujuan
│   └── 404.php                # Halaman not found kustom
│
├── 🔐 Autentikasi
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   └── cek_username.php       # Cek ketersediaan username (AJAX)
│
├── 🎛️ Panel Admin
│   ├── dashboard.php          # Halaman utama admin
│   ├── tambah_link.php        # Form tambah tautan baru
│   ├── edit_link.php          # Form edit tautan
│   ├── hapus_link.php         # Hapus tautan (redirect)
│   ├── profil.php             # Edit profil & pengaturan tampilan
│   ├── analytics.php          # Dashboard analitik
│   └── ubah_urutan.php        # Ubah urutan tautan (redirect)
│
├── 🔌 API Endpoints
│   ├── api_hapus.php          # Hapus tautan (AJAX/JSON)
│   ├── api_toggle.php         # Toggle status aktif (AJAX/JSON)
│   ├── api_urutan.php         # Ubah urutan (AJAX/JSON)
│   ├── api_export.php         # Export data ke JSON
│   ├── api_import.php         # Import data dari JSON
│   └── api_hapus_background.php # Hapus background (AJAX/JSON)
│
├── 🎨 Aset
│   ├── style.css              # Stylesheet global (Neo-Brutalisme)
│   ├── koneksi.php            # Koneksi database (ada di .gitignore)
│   └── uploads/               # Foto profil & background (ada di .gitignore)
│
└── 📋 Konfigurasi
    ├── .htaccess
    └── .gitignore
```

---

## 🗄️ Database

Buat database dengan nama `db_linktree`, lalu jalankan SQL berikut:

```sql
-- Tabel pengguna
CREATE TABLE users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nama            VARCHAR(100) NOT NULL,
    username        VARCHAR(50)  NOT NULL UNIQUE,
    email           VARCHAR(100) NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL,
    bio             TEXT,
    foto            VARCHAR(255) DEFAULT NULL,
    warna_tema      VARCHAR(20)  DEFAULT '#6366f1',
    background_image VARCHAR(255) DEFAULT NULL,
    custom_css      TEXT         DEFAULT NULL,
    last_active     DATETIME     DEFAULT NULL,
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- Tabel tautan
CREATE TABLE links (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT          NOT NULL,
    judul           VARCHAR(100) NOT NULL,
    url             TEXT         NOT NULL,
    icon            VARCHAR(50)  DEFAULT 'fas fa-link',
    urutan          INT          DEFAULT 0,
    aktif           TINYINT(1)   DEFAULT 1,
    jumlah_klik     INT          DEFAULT 0,
    scheduled_start DATETIME     DEFAULT NULL,
    scheduled_end   DATETIME     DEFAULT NULL,
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel short URL
CREATE TABLE short_urls (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    link_id     INT          NOT NULL,
    short_code  VARCHAR(50)  NOT NULL UNIQUE,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (link_id) REFERENCES links(id) ON DELETE CASCADE
);

-- Tabel visitor
CREATE TABLE visitors (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT          NOT NULL,
    visitor_ip       VARCHAR(50),
    visitor_device   VARCHAR(50),
    visitor_browser  VARCHAR(50),
    visited_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## 🚀 Instalasi

### Prasyarat
- **XAMPP** (Apache + MySQL + PHP 7.4+)
- Browser modern

### Langkah-langkah

**1. Clone repositori**
```bash
git clone https://github.com/username/neo-linktree.git
```
Atau download ZIP dan ekstrak ke folder `htdocs` XAMPP.

**2. Letakkan di direktori XAMPP**
```
C:/xampp/htdocs/linktree/
```

**3. Buat database**

Buka `http://localhost/phpmyadmin`, buat database `db_linktree`, lalu import SQL di atas.

**4. Konfigurasi koneksi**

Buat file `koneksi.php` (tidak termasuk di repo karena ada di `.gitignore`):
```php
<?php
$host = "localhost";
$user = "root";
$pass = "";       // sesuaikan password MySQL kamu
$db   = "db_linktree";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

define('SITE_NAME', 'Neo-Linktree');
define('SITE_URL', 'http://localhost/linktree/');
?>
```

**5. Buat folder upload**
```
htdocs/linktree/uploads/
htdocs/linktree/uploads/backgrounds/
```
Pastikan folder ini memiliki izin tulis (`chmod 775` di Linux/Mac).

**6. Jalankan**

Buka browser dan akses:
```
http://localhost/linktree/register.php
```

---

## 🌐 URL & Routing

| URL | Keterangan |
|-----|-----------|
| `/login.php` | Halaman login |
| `/register.php` | Halaman registrasi |
| `/dashboard.php` | Panel admin |
| `/profil.php` | Edit profil |
| `/analytics.php` | Dashboard analitik |
| `/u.php?user=username` | Halaman publik profil |
| `/username` | Alias halaman publik (via `.htaccess`) |
| `/s/kode` | Redirect short URL |
| `/404.php` | Halaman not found kustom |

---

## 🔒 Keamanan

- Password di-hash menggunakan `password_hash()` (bcrypt)
- Query database menggunakan **Prepared Statements** secara konsisten untuk mencegah SQL Injection
- Validasi input di sisi server sebelum disimpan ke database
- Session-based authentication untuk semua halaman admin
- `htmlspecialchars()` pada semua output untuk mencegah XSS
- **Sanitasi Custom CSS** — `url()`, `@import`, `expression()`, dan `javascript:` distrip sebelum dirender ke halaman publik
- Validasi ekstensi file saat upload (hanya JPG, PNG, GIF, WEBP)
- `rel="noopener"` pada semua tautan eksternal di halaman publik

> ⚠️ **Catatan:** File `koneksi.php` dan folder `uploads/` sudah dimasukkan ke `.gitignore` agar tidak ikut ter-commit ke repositori.

---

## 🛠️ Teknologi

| Teknologi | Kegunaan |
|-----------|---------|
| PHP (Native) | Backend & logika server |
| MySQL + MySQLi | Database & query |
| HTML5 + CSS3 | Tampilan antarmuka |
| Vanilla JavaScript | Interaksi & AJAX |
| Chart.js 4.4 | Grafik analitik (line, bar, doughnut) |
| Font Awesome 6 | Ikon tautan |
| XAMPP | Environment pengembangan lokal |

---

## 🤝 Kontribusi

Kontribusi sangat diterima! Silakan:

1. Fork repositori ini
2. Buat branch baru: `git checkout -b fitur/nama-fitur`
3. Commit perubahan: `git commit -m 'Tambah fitur X'`
4. Push ke branch: `git push origin fitur/nama-fitur`
5. Buka Pull Request

---

## 📄 Lisensi

Proyek ini menggunakan lisensi **MIT** — bebas digunakan, dimodifikasi, dan didistribusikan.

---

<div align="center">

Dibuat dengan ☕ dan semangat belajar

**⚡ Neo-Linktree** — *Biodata Link Modern & Keren*

</div>
