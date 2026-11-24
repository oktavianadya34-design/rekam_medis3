<?php
// Koneksi database & fungsi bantu
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $conn = new mysqli('localhost', 'root', '', 'db_rekam_medis');
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $ex) {
    die('Koneksi database gagal. Pastikan MySQL di XAMPP aktif dan database db_rekam_medis sudah diimpor (database.sql).');
}

// Escape output HTML
function e($teks)
{
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}

// Pesan berhasil / gagal (disimpan di session sampai ditampilkan)
function flash($tipe, $pesan)
{
    $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
}

function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

// Validasi tanggal format YYYY-MM-DD
function tgl_valid($tgl)
{
    $d = DateTime::createFromFormat('Y-m-d', $tgl);
    return $d && $d->format('Y-m-d') === $tgl;
}

function tgl_indo($tgl)
{
    return $tgl ? date('d-m-Y', strtotime($tgl)) : '-';
}

// Hapus data; jika masih dipakai relasi (FK), tampilkan pesan gagal
function hapus_data($conn, $tabel, $pk, $id, $kembali, $nama)
{
    try {
        $st = $conn->prepare("DELETE FROM $tabel WHERE $pk = ?");
        $st->bind_param('i', $id);
        $st->execute();
        if ($st->affected_rows > 0) {
            flash('sukses', "Data $nama berhasil dihapus.");
        } else {
            flash('gagal', "Data $nama tidak ditemukan.");
        }
    } catch (mysqli_sql_exception $ex) {
        if ((int)$ex->getCode() === 1451) {
            flash('gagal', "Data $nama tidak dapat dihapus karena masih digunakan pada rekam medis.");
        } else {
            flash('gagal', "Gagal menghapus data $nama.");
        }
    }
    redirect($kembali);
}
