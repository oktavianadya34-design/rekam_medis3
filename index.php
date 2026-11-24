<?php
$judul = 'Dashboard';
require 'header.php';

// Fungsi agregat COUNT()
$jml = [];
foreach (['pasien' => 'Pasien', 'dokter' => 'Dokter', 'obat' => 'Obat', 'rekam_medis' => 'Rekam Medis'] as $tabel => $label) {
    $jml[$label] = (int)$conn->query("SELECT COUNT(*) AS total FROM $tabel")->fetch_assoc()['total'];
}

// 5 pemeriksaan terakhir (JOIN)
$terakhir = $conn->query(
    "SELECT r.tanggal_periksa, p.nama_pasien, d.nama_dokter, r.diagnosa, o.nama_obat
     FROM rekam_medis r
     JOIN pasien p ON p.id_pasien = r.id_pasien
     JOIN dokter d ON d.id_dokter = r.id_dokter
     JOIN obat o   ON o.id_obat   = r.id_obat
     ORDER BY r.tanggal_periksa DESC, r.id_rekam DESC LIMIT 5"
)->fetch_all(MYSQLI_ASSOC);
?>
<h1>Dashboard</h1>
<div class="statistik">
  <?php foreach ($jml as $label => $angka): ?>
    <div class="stat"><div class="angka"><?= $angka ?></div><div class="label">Jumlah <?= e($label) ?></div></div>
  <?php endforeach; ?>
</div>

<div class="toolbar">
  <h2 style="margin:0">Pemeriksaan Terakhir</h2>
  <a class="btn" href="rekam.php?aksi=tambah">+ Tambah Rekam Medis</a>
</div>
<div class="tabel-wrap">
<table>
  <thead><tr><th>Tanggal</th><th>Pasien</th><th>Dokter</th><th>Diagnosa</th><th>Obat</th></tr></thead>
  <tbody>
  <?php if (!$terakhir): ?>
    <tr><td colspan="5" class="kosong">Belum ada data pemeriksaan.</td></tr>
  <?php endif; ?>
  <?php foreach ($terakhir as $r): ?>
    <tr>
      <td><?= e(tgl_indo($r['tanggal_periksa'])) ?></td>
      <td><?= e($r['nama_pasien']) ?></td>
      <td><?= e($r['nama_dokter']) ?></td>
      <td><?= e($r['diagnosa']) ?></td>
      <td><?= e($r['nama_obat']) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php require 'footer.php'; ?>
