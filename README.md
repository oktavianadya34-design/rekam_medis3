# Aplikasi Rekam Medis Sederhana

PHP + MySQL/MariaDB. Seluruh data fiktif (simulasi pembelajaran).

## Cara Menjalankan (Laragon)

1. Buat atau letakkan folder `rekam_medis` di dalam folder `www` Laragon.
2. Nyalakan **Apache** dan **MySQL** di Laragon.
3. Buka `http://localhost/phpmyadmin`.
4. Pilih menu **Import**, lalu pilih file `database.sql` dan klik **Go**.
5. Buka `http://localhost/rekam_medis/`.

Jika user/password MySQL berbeda, sesuaikan di `koneksi.php`.

## Struktur File

| File | Fungsi |
|---|---|
| `database.sql` | Struktur tabel, PK, FK, dan data uji |
| `koneksi.php` | Koneksi database dan fungsi bantu |
| `header.php` / `footer.php` / `style.css` | Tampilan bersama |
| `index.php` | Dashboard |
| `pasien.php`, `dokter.php`, `obat.php` | CRUD data master |
| `rekam.php` | CRUD rekam medis dan relasi data |

## Aturan Relasi

Foreign Key pada tabel `rekam_medis` menggunakan `ON DELETE RESTRICT`. Data pasien, dokter, dan obat yang sudah digunakan dalam rekam medis tidak dapat dihapus.

## Catatan Keamanan

Menggunakan prepared statement untuk mencegah SQL Injection dan `htmlspecialchars` untuk membantu mencegah XSS.