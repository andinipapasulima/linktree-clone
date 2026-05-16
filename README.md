# 🌐 Neo-Linktree Clone (Premium Neo-Brutalisme Edition)

Aplikasi web personal tautan (*Linktree Clone*) yang dirancang dengan estetika **Ultra-Premium Neo-Brutalisme** (karakteristik garis batas tebal, warna kontras berani, dan bayangan pekat). Proyek ini menggabungkan fondasi PHP native yang solid pada sisi *back-end* dengan interaktivitas mutakhir berbasis data di sisi *front-end*.

---

## ✨ Fitur Utama (God Tier Features)

### 🖥️ Admin Dashboard Control
* **Single Column Responsive Layout:** Manajemen terpusat untuk memantau data secara lega tanpa tabel kaku konvensional.
* **Asynchronous Live Re-ordering (Fetch API / AJAX):** Mengubah urutan prioritas tautan (`🔼` / `🔽`) secara instan di latar belakang tanpa memicu *refresh/reload* halaman browser.
* **Asynchronous CRUD:** Proses penghapusan data tautan secara *real-time* dilengkapi dengan mikro-animasi transisi *fade-out*.
* **Smart Session Identification:** Sistem deteksi otomatis berlapis untuk membaca kunci *session user ID* demi keamanan data admin.

### 🌐 Public Profile Page (`u.php`)
* **3D Mouse Move Parallax:** Elemen-elemen geometri (*shapes*) dekoratif yang melayang secara dinamis merespon pergerakan kursor dengan nilai kedalaman (*depth*) yang bervariasi.
* **Instant Live Search:** Penyaringan tautan secara langsung (*real-time filtering*) ketika pengguna mengetik kata kunci.
* **Pop-Bounce Micro-interactions:** Animasi memantul ala kartun retro menggunakan `@keyframes` CSS ketika tautan yang dicari tidak ditemukan.
* **Dual Theme Switcher:** Perpindahan tema instan antara **Mode Pop** (Retro-Pastel) dan **Mode Matrix** (Neo-Dark Theme).
* **Profile Avatar Light Shimmer & Tilting:** Efek kilauan gradasi yang mengikuti koordinat kursor saat melewati area profil, lengkap dengan efek kemiringan (*tilt*) 3D.

---

## 🛠️ Teknologi yang Digunakan

* **Back-End:** PHP Native (Versi 7.4 / 8.x+)
* **Database:** MySQL / MariaDB (Eksekusi query via `mysqli`)
* **Front-End Interactivity:** Vanilla JavaScript (ES6+ Features, Fetch API, DOM Manipulation)
* **Styling:** CSS3 Native *(No external CSS variables or frameworks like Bootstrap/Tailwind used to maintain light footprint and pure customized brutalist style)*

---

## 📂 Struktur Repositori Penting

```text
├── api_hapus.php       # API internal untuk menghapus link secara asynchronous
├── api_urutan.php      # API internal untuk menukar posisi urutan link di database
├── dashboard.php       # Panel kendali admin (Kelola semua tautan)
├── u.php               # Halaman publik profil user (Linktree view)
├── koneksi.php         # Konfigurasi koneksi ke database MySQL
├── uploads/            # Direktori penyimpanan foto profil user
└── README.md           # Dokumentasi proyek
