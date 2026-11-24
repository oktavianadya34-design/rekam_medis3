<?php
require_once 'koneksi.php';

$aksi = $_GET['aksi'] ?? 'list';
$errors = [];
$data = [
    'id_rekam' => 0, 'id_pasien' => 0, 'id_dokter' => 0, 'id_obat' => 0,
    'tanggal_periksa' => date('Y-m-d'), 'keluhan' => '', 'diagnosa' => '', 'tindakan' => '', 'resep' => '',
];

// Data untuk dropdown (diambil dari database)
$list_pasien = $conn->query('SELECT id_pasien, no_rm, nama_pasien FROM pasien ORDER BY nama_pasien')->fetch_all(MYSQLI_ASSOC);
$list_dokter = $conn->query('SELECT id_dokter, nama_dokter, spesialisasi FROM dokter ORDER BY nama_dokter')->fetch_all(MYSQLI_ASSOC);
$list_obat   = $conn->query('SELECT id_obat, nama_obat, satuan, stok FROM obat ORDER BY nama_obat')->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['aksi'] ?? '') === 'hapus') {
        hapus_data($conn, 'rekam_medis', 'id_rekam', (int)($_POST['id'] ?? 0), 'rekam.php', 'rekam medis');
    }

    $data = [
        'id_rekam'        => (int)($_POST['id_rekam'] ?? 0),
        'id_pasien'       => (int)($_POST['id_pasien'] ?? 0),
        'id_dokter'       => (int)($_POST['id_dokter'] ?? 0),
        'id_obat'         => (int)($_POST['id_obat'] ?? 0),
        'tanggal_periksa' => trim($_POST['tanggal_periksa'] ?? ''),
        'keluhan'         => trim($_POST['keluhan'] ?? ''),
        'diagnosa'        => trim($_POST['diagnosa'] ?? ''),
        'tindakan'        => trim($_POST['tindakan'] ?? ''),
        'resep'           => trim($_POST['resep'] ?? ''),
    ];

    if ($data['id_pasien'] <= 0) $errors[] = 'Pilih pasien.';
    if ($data['id_dokter'] <= 0) $errors[] = 'Pilih dokter.';
    if ($data['id_obat'] <= 0)   $errors[] = 'Pilih obat.';
    if (!tgl_valid($data['tanggal_periksa'])) $errors[] = 'Tanggal periksa tidak valid.';
    if ($data['keluhan'] === '')  $errors[] = 'Keluhan wajib diisi.';
    if ($data['diagnosa'] === '') $errors[] = 'Diagnosa wajib diisi.';
    if ($data['tindakan'] === '') $errors[] = 'Tindakan wajib diisi.';
    if ($data['resep'] === '' || mb_strlen($data['resep']) > 100) $errors[] = 'Resep / aturan pakai wajib diisi (maksimal 100 karakter).';

    if (!$errors) {
        try {
            if ($data['id_rekam'] > 0) {
                $st = $conn->prepare('UPDATE rekam_medis SET id_pasien=?, id_dokter=?, id_obat=?, tanggal_periksa=?, keluhan=?, diagnosa=?, tindakan=?, resep=? WHERE id_rekam=?');
                $st->bind_param('iiisssssi', $data['id_pasien'], $data['id_dokter'], $data['id_obat'], $data['tanggal_periksa'], $data['keluhan'], $data['diagnosa'], $data['tindakan'], $data['resep'], $data['id_rekam']);
                $st->execute();
                flash('sukses', 'Rekam medis berhasil diperbarui.');
            } else {
                $st = $conn->prepare('INSERT INTO rekam_medis (id_pasien, id_dokter, id_obat, tanggal_periksa, keluhan, diagnosa, tindakan, resep) VALUES (?,?,?,?,?,?,?,?)');
                $st->bind_param('iiisssss', $data['id_pasien'], $data['id_dokter'], $data['id_obat'], $data['tanggal_periksa'], $data['keluhan'], $data['diagnosa'], $data['tindakan'], $data['resep']);
                $st->execute();
                flash('sukses', 'Rekam medis berhasil ditambahkan.');
            }
            redirect('rekam.php');
        } catch (mysqli_sql_exception $ex) {
            $errors[] = ((int)$ex->getCode() === 1452)
                ? 'Pasien, dokter, atau obat yang dipilih tidak tersedia di database.'
                : 'Gagal menyimpan rekam medis.';
        }
    }
    $aksi = $data['id_rekam'] > 0 ? 'edit' : 'tambah';
} elseif ($aksi === 'edit') {
    $st = $conn->prepare('SELECT * FROM rekam_medis WHERE id_rekam = ?');
    $id = (int)($_GET['id'] ?? 0);
    $st->bind_param('i', $id);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    if (!$row) {
        flash('gagal', 'Rekam medis tidak ditemukan.');
        redirect('rekam.php');
    }
    $data = $row;
}

$judul = 'Rekam Medis';
require 'header.php';

if ($aksi === 'tambah' || $aksi === 'edit'):
    $master_kosong = !$list_pasien || !$list_dokter || !$list_obat;
?>
<h1><?= $aksi === 'edit' ? 'Edit' : 'Tambah' ?> Rekam Medis</h1>
<div class="kartu">
  <?php if ($master_kosong): ?>
    <div class="alert err">Dropdown belum lengkap. Isi dulu data
      <?php if (!$list_pasien): ?><a href="pasien.php?aksi=tambah">pasien</a> <?php endif; ?>
      <?php if (!$list_dokter): ?><a href="dokter.php?aksi=tambah">dokter</a> <?php endif; ?>
      <?php if (!$list_obat): ?><a href="obat.php?aksi=tambah">obat</a><?php endif; ?>.
    </div>
  <?php endif; ?>
  <?php if ($errors): ?>
    <div class="alert err"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <form method="post" action="rekam.php?aksi=<?= e($aksi) ?>">
    <input type="hidden" name="id_rekam" value="<?= (int)$data['id_rekam'] ?>">
    <div class="grid2">
      <div><label for="id_pasien">Pasien</label>
        <select id="id_pasien" name="id_pasien" required>
          <option value="">-- Pilih pasien --</option>
          <?php foreach ($list_pasien as $p): ?>
            <option value="<?= (int)$p['id_pasien'] ?>" <?= (int)$data['id_pasien'] === (int)$p['id_pasien'] ? 'selected' : '' ?>><?= e($p['no_rm'] . ' - ' . $p['nama_pasien']) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div><label for="id_dokter">Dokter</label>
        <select id="id_dokter" name="id_dokter" required>
          <option value="">-- Pilih dokter --</option>
          <?php foreach ($list_dokter as $d): ?>
            <option value="<?= (int)$d['id_dokter'] ?>" <?= (int)$data['id_dokter'] === (int)$d['id_dokter'] ? 'selected' : '' ?>><?= e($d['nama_dokter'] . ' (' . $d['spesialisasi'] . ')') ?></option>
          <?php endforeach; ?>
        </select></div>
      <div><label for="id_obat">Obat</label>
        <select id="id_obat" name="id_obat" required>
          <option value="">-- Pilih obat --</option>
          <?php foreach ($list_obat as $o): ?>
            <option value="<?= (int)$o['id_obat'] ?>" <?= (int)$data['id_obat'] === (int)$o['id_obat'] ? 'selected' : '' ?>><?= e($o['nama_obat'] . ' - ' . $o['satuan'] . ' (stok ' . $o['stok'] . ')') ?></option>
          <?php endforeach; ?>
        </select></div>
      <div><label for="tanggal_periksa">Tanggal Periksa</label>
        <input type="date" id="tanggal_periksa" name="tanggal_periksa" required value="<?= e($data['tanggal_periksa']) ?>"></div>
    </div>
    <label for="keluhan">Keluhan</label>
    <textarea id="keluhan" name="keluhan" required><?= e($data['keluhan']) ?></textarea>
    <label for="diagnosa">Diagnosa</label>
    <textarea id="diagnosa" name="diagnosa" required><?= e($data['diagnosa']) ?></textarea>
    <label for="tindakan">Tindakan</label>
    <textarea id="tindakan" name="tindakan" required><?= e($data['tindakan']) ?></textarea>
    <label for="resep">Resep / Aturan Pakai Obat</label>
    <input type="text" id="resep" name="resep" maxlength="100" placeholder="contoh: 3 x 1 tablet sesudah makan" required value="<?= e($data['resep']) ?>">
    <div class="form-aksi">
      <button class="btn" type="submit" <?= $master_kosong ? 'disabled' : '' ?>>Simpan</button>
      <a class="btn abu" href="rekam.php">Batal</a>
    </div>
  </form>
</div>
<?php
else:
    // Riwayat pemeriksaan memakai SQL JOIN (nama tampil, bukan ID)
    $filter = (int)($_GET['pasien'] ?? 0);
    $sql = 'SELECT r.id_rekam, r.tanggal_periksa, r.keluhan, r.diagnosa, r.tindakan, r.resep,
                   p.no_rm, p.nama_pasien, d.nama_dokter, o.nama_obat
            FROM rekam_medis r
            JOIN pasien p ON p.id_pasien = r.id_pasien
            JOIN dokter d ON d.id_dokter = r.id_dokter
            JOIN obat o   ON o.id_obat   = r.id_obat';
    if ($filter > 0) {
        $st = $conn->prepare($sql . ' WHERE r.id_pasien = ? ORDER BY r.tanggal_periksa DESC, r.id_rekam DESC');
        $st->bind_param('i', $filter);
        $st->execute();
        $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $rows = $conn->query($sql . ' ORDER BY r.tanggal_periksa DESC, r.id_rekam DESC')->fetch_all(MYSQLI_ASSOC);
    }
?>
<h1>Riwayat Pemeriksaan (Rekam Medis)</h1>
<div class="toolbar">
  <form method="get">
    <select name="pasien" onchange="this.form.submit()">
      <option value="0">Semua pasien</option>
      <?php foreach ($list_pasien as $p): ?>
        <option value="<?= (int)$p['id_pasien'] ?>" <?= $filter === (int)$p['id_pasien'] ? 'selected' : '' ?>><?= e($p['nama_pasien']) ?></option>
      <?php endforeach; ?>
    </select>
    <noscript><button class="btn" type="submit">Filter</button></noscript>
  </form>
  <a class="btn" href="rekam.php?aksi=tambah">+ Tambah Rekam Medis</a>
</div>
<div class="tabel-wrap">
<table>
  <thead><tr><th>No</th><th>Tanggal</th><th>Pasien</th><th>Dokter</th><th>Keluhan</th><th>Diagnosa</th><th>Tindakan</th><th>Obat</th><th>Resep</th><th>Aksi</th></tr></thead>
  <tbody>
  <?php if (!$rows): ?><tr><td colspan="10" class="kosong">Belum ada catatan pemeriksaan.</td></tr><?php endif; ?>
  <?php foreach ($rows as $i => $r): ?>
    <tr>
      <td><?= $i + 1 ?></td>
      <td><?= e(tgl_indo($r['tanggal_periksa'])) ?></td>
      <td><?= e($r['nama_pasien']) ?><br><small><?= e($r['no_rm']) ?></small></td>
      <td><?= e($r['nama_dokter']) ?></td>
      <td><?= e($r['keluhan']) ?></td>
      <td><?= e($r['diagnosa']) ?></td>
      <td><?= e($r['tindakan']) ?></td>
      <td><?= e($r['nama_obat']) ?></td>
      <td><?= e($r['resep']) ?></td>
      <td class="aksi">
        <a class="btn kuning kecil" href="rekam.php?aksi=edit&id=<?= (int)$r['id_rekam'] ?>">Edit</a>
        <form method="post" onsubmit="return confirm('Hapus rekam medis ini?')">
          <input type="hidden" name="aksi" value="hapus">
          <input type="hidden" name="id" value="<?= (int)$r['id_rekam'] ?>">
          <button class="btn merah kecil" type="submit">Hapus</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>
<?php require 'footer.php'; ?>
