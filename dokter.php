<?php
require_once 'koneksi.php';

$aksi = $_GET['aksi'] ?? 'list';
$errors = [];
$data = ['id_dokter' => 0, 'nama_dokter' => '', 'spesialisasi' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['aksi'] ?? '') === 'hapus') {
        hapus_data($conn, 'dokter', 'id_dokter', (int)($_POST['id'] ?? 0), 'dokter.php', 'dokter');
    }

    $data = [
        'id_dokter'    => (int)($_POST['id_dokter'] ?? 0),
        'nama_dokter'  => trim($_POST['nama_dokter'] ?? ''),
        'spesialisasi' => trim($_POST['spesialisasi'] ?? ''),
    ];

    if ($data['nama_dokter'] === '' || mb_strlen($data['nama_dokter']) > 100) $errors[] = 'Nama dokter wajib diisi (maksimal 100 karakter).';
    if ($data['spesialisasi'] === '' || mb_strlen($data['spesialisasi']) > 50) $errors[] = 'Spesialisasi wajib diisi (maksimal 50 karakter).';

    if (!$errors) {
        try {
            if ($data['id_dokter'] > 0) {
                $st = $conn->prepare('UPDATE dokter SET nama_dokter=?, spesialisasi=? WHERE id_dokter=?');
                $st->bind_param('ssi', $data['nama_dokter'], $data['spesialisasi'], $data['id_dokter']);
                $st->execute();
                flash('sukses', 'Data dokter berhasil diperbarui.');
            } else {
                $st = $conn->prepare('INSERT INTO dokter (nama_dokter, spesialisasi) VALUES (?,?)');
                $st->bind_param('ss', $data['nama_dokter'], $data['spesialisasi']);
                $st->execute();
                flash('sukses', 'Data dokter berhasil ditambahkan.');
            }
            redirect('dokter.php');
        } catch (mysqli_sql_exception $ex) {
            $errors[] = 'Gagal menyimpan data dokter.';
        }
    }
    $aksi = $data['id_dokter'] > 0 ? 'edit' : 'tambah';
} elseif ($aksi === 'edit') {
    $st = $conn->prepare('SELECT * FROM dokter WHERE id_dokter = ?');
    $id = (int)($_GET['id'] ?? 0);
    $st->bind_param('i', $id);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    if (!$row) {
        flash('gagal', 'Data dokter tidak ditemukan.');
        redirect('dokter.php');
    }
    $data = $row;
}

$judul = 'Data Dokter';
require 'header.php';

if ($aksi === 'tambah' || $aksi === 'edit'):
?>
<h1><?= $aksi === 'edit' ? 'Edit' : 'Tambah' ?> Dokter</h1>
<div class="kartu">
  <?php if ($errors): ?>
    <div class="alert err"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <form method="post" action="dokter.php?aksi=<?= e($aksi) ?>">
    <input type="hidden" name="id_dokter" value="<?= (int)$data['id_dokter'] ?>">
    <div class="grid2">
      <div><label for="nama_dokter">Nama Dokter</label>
        <input type="text" id="nama_dokter" name="nama_dokter" maxlength="100" required value="<?= e($data['nama_dokter']) ?>"></div>
      <div><label for="spesialisasi">Spesialisasi</label>
        <input type="text" id="spesialisasi" name="spesialisasi" maxlength="50" required value="<?= e($data['spesialisasi']) ?>"></div>
    </div>
    <div class="form-aksi">
      <button class="btn" type="submit">Simpan</button>
      <a class="btn abu" href="dokter.php">Batal</a>
    </div>
  </form>
</div>
<?php
else:
    $rows = $conn->query('SELECT * FROM dokter ORDER BY id_dokter DESC')->fetch_all(MYSQLI_ASSOC);
?>
<h1>Data Dokter</h1>
<div class="toolbar">
  <span></span>
  <a class="btn" href="dokter.php?aksi=tambah">+ Tambah Dokter</a>
</div>
<div class="tabel-wrap">
<table>
  <thead><tr><th>No</th><th>Nama Dokter</th><th>Spesialisasi</th><th>Aksi</th></tr></thead>
  <tbody>
  <?php if (!$rows): ?><tr><td colspan="4" class="kosong">Data dokter belum ada.</td></tr><?php endif; ?>
  <?php foreach ($rows as $i => $r): ?>
    <tr>
      <td><?= $i + 1 ?></td>
      <td><?= e($r['nama_dokter']) ?></td>
      <td><?= e($r['spesialisasi']) ?></td>
      <td class="aksi">
        <a class="btn kuning kecil" href="dokter.php?aksi=edit&id=<?= (int)$r['id_dokter'] ?>">Edit</a>
        <form method="post" onsubmit="return confirm('Hapus dokter ini?')">
          <input type="hidden" name="aksi" value="hapus">
          <input type="hidden" name="id" value="<?= (int)$r['id_dokter'] ?>">
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
