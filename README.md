# 🕌 Sistem Rekomendasi Pesantren

Aplikasi web berbasis **PHP & MySQL** untuk merekomendasikan pondok pesantren kepada calon santri menggunakan algoritma **K-Nearest Neighbor (KNN)** dengan Euclidean Distance berbobot.

---

## 📋 Daftar Isi

- [Fitur](#-fitur)
- [Teknologi](#-teknologi)
- [Struktur Proyek](#-struktur-proyek)
- [Persyaratan Sistem](#-persyaratan-sistem)
- [Instalasi](#-instalasi)
- [Konfigurasi Database](#-konfigurasi-database)
- [Cara Penggunaan](#-cara-penggunaan)
- [Algoritma KNN](#-algoritma-knn)
- [Role Pengguna](#-role-pengguna)
- [Screenshot](#-screenshot)

---

## ✨ Fitur

- 🔐 **Autentikasi** — Login multi-role (Admin & Santri)
- 🤖 **Rekomendasi KNN** — Rekomendasi pesantren berbasis preferensi santri dengan bobot kriteria yang dapat dikonfigurasi
- 🏫 **Kelola Data Pondok** — Admin dapat menambah, mengedit, dan menghapus data pesantren beserta foto
- 👥 **Kelola Data User** — Manajemen akun santri oleh admin
- ⚖️ **Kelola Kriteria** — Bobot kriteria penilaian dapat disesuaikan oleh admin
- 📊 **Detail Perhitungan** — Transparansi perhitungan jarak KNN ditampilkan lengkap ke santri
- 📱 **Responsif** — Tampilan menyesuaikan berbagai ukuran layar

---

## 🛠 Teknologi

| Komponen | Teknologi |
|----------|-----------|
| Backend | PHP 7.4+ |
| Database | MySQL / MariaDB |
| Frontend | HTML5, CSS3 (Custom Framework) |
| Icon | Font Awesome 6 |
| Server | Apache (XAMPP) |
| Algoritma | K-Nearest Neighbor (KNN) |

---

## 📁 Struktur Proyek

```
Rekom_Pesantren/
│
├── index.php                        # Halaman utama / redirect
├── login.php                        # Halaman login
├── logout.php                       # Proses logout
├── koneksi.php                      # Konfigurasi koneksi database
├── main.js                          # JavaScript global
│
├── admin/                           # Panel Admin
│   ├── dashboard.php                # Dashboard admin
│   ├── kelola_data_kriteria.php     # Manajemen bobot kriteria
│   ├── kelola_data_pondok.php       # Manajemen data pesantren
│   └── kelola_data_user.php         # Manajemen data user
│
├── CSantri/                         # Panel Santri
│   ├── dashboard.php                # Dashboard santri
│   ├── informasi_pesantren.php      # Informasi daftar pesantren
│   └── rekomendasi_pesantren.php    # Halaman rekomendasi KNN
│
├── css/                             # Stylesheet
│   ├── framework.css                # CSS framework kustom
│   ├── master.css                   # CSS utama
│   └── all.min.css                  # Font Awesome icons
│
├── imgs/                            # Aset gambar
│   ├── course-01.jpg ~ 05.jpg       # Foto pesantren default
│   ├── team-01.png ~ 05.png         # Avatar default
│   └── pondok_*.png                 # Foto pesantren dari upload
│
├── layout/
│   └── sidebar.php                  # Komponen sidebar navigasi
│
└── webfonts/                        # Font Awesome webfonts
    ├── fa-solid-900.*
    ├── fa-regular-400.*
    └── fa-brands-400.*
```

---

## ⚙️ Persyaratan Sistem

- **XAMPP** (atau server lokal setara) dengan:
  - PHP >= 7.4
  - Apache
  - MySQL / MariaDB
- Browser modern (Chrome, Firefox, Edge)

---

## 🚀 Instalasi

**1. Clone atau salin proyek**

```bash
# Salin folder proyek ke direktori XAMPP
C:\xampp\htdocs\Rekom_Pesantren\
```

**2. Jalankan XAMPP**

Aktifkan modul **Apache** dan **MySQL** dari XAMPP Control Panel.

**3. Import database**

Buka **phpMyAdmin** di `http://localhost/phpmyadmin`, lalu:
- Buat database baru bernama `rekom_pesantren`
- Import file SQL (lihat bagian [Konfigurasi Database](#-konfigurasi-database))

**4. Konfigurasi koneksi**

Edit file `koneksi.php` sesuai pengaturan lokal Anda:

```php
$host   = "localhost";
$dbname = "rekom_pesantren";
$user   = "root";
$pass   = "";          // Sesuaikan jika ada password MySQL
```

**5. Akses aplikasi**

Buka browser dan kunjungi:
```
http://localhost/Rekom_Pesantren/
```

---

## 🗄️ Konfigurasi Database

Buat tabel-tabel berikut pada database `rekom_pesantren`:

```sql
-- Tabel Users
CREATE TABLE users (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    nama     VARCHAR(100) NOT NULL,
    username VARCHAR(50)  NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role     ENUM('admin', 'santri') NOT NULL DEFAULT 'santri'
);

-- Tabel Pondok
CREATE TABLE pondok (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nama_pondok     VARCHAR(150) NOT NULL,
    lokasi          VARCHAR(200),
    deskripsi       TEXT,
    foto            VARCHAR(255),
    nilai_biaya     TINYINT NOT NULL COMMENT '1=Sangat Murah, 5=Sangat Mahal',
    nilai_jarak     TINYINT NOT NULL COMMENT '1=Sangat Dekat, 5=Sangat Jauh',
    nilai_fasilitas TINYINT NOT NULL COMMENT '1=Sangat Kurang, 5=Sangat Lengkap',
    nilai_program   TINYINT NOT NULL COMMENT '1=Salaf, 2=Modern, 3=Tahfidz',
    nilai_santri    TINYINT NOT NULL COMMENT '1=<100, 2=100-300, 3=300-500, 4=500-700, 5=>700'
);

-- Tabel Kriteria
CREATE TABLE kriteria (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    kode_kriteria  VARCHAR(5)  NOT NULL UNIQUE,
    nama_kriteria  VARCHAR(100),
    jenis          ENUM('cost', 'benefit') NOT NULL,
    bobot          DECIMAL(5,2) NOT NULL DEFAULT 1.00
);

-- Data awal kriteria
INSERT INTO kriteria (kode_kriteria, nama_kriteria, jenis, bobot) VALUES
('C1', 'Biaya Pendidikan',   'cost',    1.00),
('C2', 'Jarak / Lokasi',     'cost',    1.00),
('C3', 'Fasilitas',          'benefit', 1.00),
('C4', 'Program Pendidikan', 'benefit', 1.00),
('C5', 'Jumlah Santri',      'benefit', 1.00);

-- Akun admin default
INSERT INTO users (nama, username, password, role)
VALUES ('Administrator', 'admin', 'admin123', 'admin');
```

> ⚠️ **Catatan Keamanan:** Password disimpan sebagai plain text pada versi ini. Disarankan menggunakan `password_hash()` untuk lingkungan produksi.

---

## 📖 Cara Penggunaan

### Sebagai Admin

1. Login dengan akun role `admin`
2. Tambahkan data pesantren melalui **Kelola Data Pondok** (isi semua nilai kriteria C1–C5)
3. Atur bobot kriteria sesuai kebutuhan melalui **Kelola Data Kriteria**
4. Tambahkan akun santri melalui **Kelola Data User**

### Sebagai Santri

1. Login dengan akun role `santri`
2. Buka menu **Rekomendasi Pesantren**
3. Pilih nilai preferensi untuk setiap kriteria (C1–C5)
4. Klik **Dapatkan Rekomendasi**
5. Sistem menampilkan **3 pesantren terbaik** beserta detail perhitungan KNN

---

## 🧮 Algoritma KNN

Sistem menggunakan **K-Nearest Neighbor** dengan **Euclidean Distance Berbobot**:

```
d(q, p) = √ Σ wᵢ × (qᵢ − pᵢ)²
```

| Simbol | Keterangan |
|--------|-----------|
| `q`    | Vektor preferensi input santri |
| `p`    | Vektor nilai pesantren |
| `wᵢ`  | Bobot kriteria ke-i (dari tabel `kriteria`) |
| `K`    | Jumlah tetangga terdekat = **3** |

### Kriteria Penilaian

| Kode | Kriteria          | Jenis   | Skala |
|------|-------------------|---------|-------|
| C1   | Biaya Pendidikan  | Cost    | 1–5   |
| C2   | Jarak / Lokasi    | Cost    | 1–5   |
| C3   | Fasilitas         | Benefit | 1–5   |
| C4   | Program Pendidikan| Benefit | 1–3   |
| C5   | Jumlah Santri     | Benefit | 1–5   |

> Semakin **kecil nilai jarak KNN**, semakin **cocok** pesantren tersebut dengan preferensi santri.

---

## 👤 Role Pengguna

| Role  | Akses |
|-------|-------|
| **Admin** | Dashboard, kelola pondok, kelola kriteria, kelola user |
| **Santri** | Dashboard, informasi pesantren, rekomendasi KNN |

---

## 📸 Screenshot

> Letakkan screenshot aplikasi di folder `imgs/` dan referensikan di sini.

| Halaman | Deskripsi |
|---------|-----------|
| `login.php` | Halaman login dengan judul Sistem Rekomendasi Pesantren |
| `CSantri/rekomendasi_pesantren.php` | Form preferensi + hasil rekomendasi KNN |
| `admin/kelola_data_pondok.php` | CRUD data pesantren |

---

## 📄 Lisensi

Proyek ini dibuat untuk keperluan akademik / tugas akhir.  
Bebas digunakan dan dimodifikasi dengan tetap mencantumkan sumber.

---

> Dibuat dengan ❤️ menggunakan PHP, MySQL, dan algoritma KNN.