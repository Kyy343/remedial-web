
# Manajemen Tugas Mahasiswa

Aplikasi web sederhana untuk mengelola data mahasiswa dan tugas menggunakan PHP dan MySQL.

## Fitur

- Dashboard ringkasan mahasiswa dan tugas
- CRUD data mahasiswa (Tambah, Tampil, Edit, Hapus)
- CRUD data tugas
- Menghubungkan tugas dengan mahasiswa
- Status tugas: Belum Dikerjakan, Sedang Dikerjakan, dan Selesai
- Tampilan responsif menggunakan CSS

## Teknologi

- PHP
- MySQL
- HTML
- CSS
- PDO

## Struktur Proyek

```text
remedial-web/
├── config/
│   ├── database.php
│   └── database.example.php
├── index.php
├── mahasiswa.php
├── tugas.php
├── style.css
├── db_manajemen_tugas.sql
└── README.md
```

## Cara Menjalankan

1. Install dan jalankan XAMPP.
2. Aktifkan Apache dan MySQL.
3. Salin folder proyek ke `C:\xampp\htdocs\remedial-web`.
4. Buka phpMyAdmin melalui `http://localhost/phpmyadmin`.
5. Buat database `db_manajemen_tugas`.
6. Import file `db_manajemen_tugas.sql`.
7. Pastikan konfigurasi database di `config/database.php` sesuai dengan MySQL lokal.
8. Buka aplikasi melalui:

   http://localhost/remedial-web/

## Database

Aplikasi menggunakan database `db_manajemen_tugas` dengan tabel:

- `mahasiswa`
- `tugas`

Tabel tugas berelasi dengan tabel mahasiswa melalui `mahasiswa_id`.

## Pengembang

I Nyoman Riski Sanjaya Putra
240040066