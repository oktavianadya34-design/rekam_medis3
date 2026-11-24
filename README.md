# Aplikasi Rekam Medis Sederhana

PHP + MySQL/MariaDB. Seluruh data fiktif (simulasi pembelajaran).

## Cara menjalankan (XAMPP)
1. Buat folder `rekam_medis` di dalam `htdocs`, lalu salin semua file ke dalamnya.
2. Nyalakan **Apache** dan **MySQL** di XAMPP Control Panel.
3. Buka `http://localhost/phpmyadmin` > tab **Import** > pilih `database.sql` > **Go**
   (otomatis membuat database `db_rekam_medis`, 4 tabel, relasi FK, dan data uji).
4. Buka `http://localhost/rekam_medis/`.

Jika user/password MySQL berbeda, ubah di `koneksi.php`.

## Struktur file
| File | Fungsi |
|---|---|
| `database.sql` | Struktur 4 tabel, PK, FK, data uji |
| `koneksi.php` | Koneksi database dan fungsi bantu |
| `header.php` / `footer.php` / `style.css` | Tampilan bersama |
| `index.php` | Dashboard (COUNT) |
| `pasien.php`, `dokter.php`, `obat.php` | CRUD master |
| `rekam.php` | CRUD rekam medis, dropdown relasi, riwayat dengan JOIN |

## Aturan relasi
FK `rekam_medis` memakai `ON DELETE RESTRICT`: pasien/dokter/obat yang sudah dipakai di rekam medis
tidak dapat dihapus, dan aplikasi menampilkan pesan gagal yang jelas.

## Catatan keamanan
Memakai prepared statement (anti SQL injection) dan `htmlspecialchars` (anti XSS).
