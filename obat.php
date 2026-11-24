<?php
require_once 'koneksi.php';

$aksi = $_GET['aksi'] ?? 'list';
$errors = [];
$data = ['id_obat' => 0, 'nama_obat' => '', 'satuan' => '', 'stok' => 0];
$satuan_umum = ['Tablet', 'Kapsul', 'Sirup', 'Salep', 'Tetes', 'Botol', 'Strip'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['aksi'] ?? '') === 'hapus') {
        hapus_data($conn, 'obat', 'id_obat', (int)($_POST['id'] ?? 0), 'obat.php', 'obat');
    }

    $stok_input = trim($_POST['stok'] ?? '');
    $data = [
        'id_obat'   => (int)($_POST['id_obat'] ?? 0),
        'nama_obat' => trim($_POST['nama_obat'] ?? ''),
        'satuan'    => trim($_POST['satuan'] ?? ''),
        'stok'      => $stok_input,
    ];

    if ($data['nama_obat'] === '' || mb_strlen($data['nama_obat']) > 100) $errors[] = 'Nama obat wajib diisi (maksimal 100 karakter).';
    if ($data['satuan'] === '' || mb_strlen($data['satuan']) > 30) $errors[] = 'Satuan wajib diisi (maksimal 30 karakter).';
    if (!ctype_digit($stok_input)) $errors[] = 'Stok harus berupa angka 0 atau lebih.';

    if (!$errors) {
        $stok = (int)$stok_input;
        try {
            if ($data['id_obat'] > 0) {
                $st = $conn->prepare('UPDATE obat SET nama_obat=?, satuan=?, stok=? WHERE id_obat=?');
                $st->bind_param('ssii', $data['nama_obat'], $data['satuan'], $stok, $data['id_obat']);
                $st->execute();
                flash('sukses', 'Data obat berhasil diperbarui.');
            } else {
                $st = $conn->prepare('INSERT INTO obat (nama_obat, satuan, stok) VALUES (?,?,?)');
                $st->bind_param('ssi', $data['nama_obat'], $data['satuan'], $stok);
                $st->execute();
                flash('sukses', 'Data obat berhasil ditambahkan.');
            }
            redirect('obat.php');
        } catch (mysqli_sql_exception $ex) {
            $errors[] = 'Gagal menyimpan data obat.';
        }
    }
    $aksi = $data['id_obat'] > 0 ? 'edit' : 'tambah';
} elseif ($aksi === 'edit') {
    $st = $conn->prepare('SELECT * FROM obat WHERE id_obat = ?');
    $id = (int)($_GET['id'] ?? 0);
    $st->bind_param('i', $id);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    if (!$row) {
        flash('gagal', 'Data obat tidak ditemukan.');
        redirect('obat.php');
    }
    $data = $row;
}

$judul = 'Data Obat';
require 'header.php';

if ($aksi === 'tambah' || $aksi === 'edit'):
?>
<h1><?= $aksi === 'edit' ? 'Edit' : 'Tambah' ?> Obat</h1>
<div class="kartu">
  <?php if ($errors): ?>
    <div class="alert err"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <form method="post" action="obat.php?aksi=<?= e($aksi) ?>">
    <input type="hidden" name="id_obat" value="<?= (int)$data['id_obat'] ?>">
    <div class="grid2">
      <div><label for="nama_obat">Nama Obat</label>
        <input type="text" id="nama_obat" name="nama_obat" maxlength="100" required value="<?= e($data['nama_obat']) ?>"></div>
      <div><label for="satuan">Satuan</label>
        <input type="text" id="satuan" name="satuan" maxlength="30" list="daftar-satuan" required value="<?= e($data['satuan']) ?>">
        <datalist id="daftar-satuan"><?php foreach ($satuan_umum as $s): ?><option value="<?= e($s) ?>"><?php endforeach; ?></datalist></div>
      <div><label for="stok">Stok</label>
        <input type="number" id="stok" name="stok" min="0" step="1" required value="<?= e($data['stok']) ?>"></div>
    </div>
    <div class="form-aksi">
      <button class="btn" type="submit">Simpan</button>
      <a class="btn abu" href="obat.php">Batal</a>
    </div>
  </form>
</div>
<?php
else:
    $rows = $conn->query('SELECT * FROM obat ORDER BY id_obat DESC')->fetch_all(MYSQLI_ASSOC);
?>
<h1>Data Obat</h1>
<div class="toolbar">
  <span></span>
  <a class="btn" href="obat.php?aksi=tambah">+ Tambah Obat</a>
</div>
<div class="tabel-wrap">
<table>
  <thead><tr><th>No</th><th>Nama Obat</th><th>Satuan</th><th>Stok</th><th>Aksi</th></tr></thead>
  <tbody>
  <?php if (!$rows): ?><tr><td colspan="5" class="kosong">Data obat belum ada.</td></tr><?php endif; ?>
  <?php foreach ($rows as $i => $r): ?>
    <tr>
      <td><?= $i + 1 ?></td>
      <td><?= e($r['nama_obat']) ?></td>
      <td><?= e($r['satuan']) ?></td>
      <td><?= (int)$r['stok'] ?></td>
      <td class="aksi">
        <a class="btn kuning kecil" href="obat.php?aksi=edit&id=<?= (int)$r['id_obat'] ?>">Edit</a>
        <form method="post" onsubmit="return confirm('Hapus obat ini?')">
          <input type="hidden" name="aksi" value="hapus">
          <input type="hidden" name="id" value="<?= (int)$r['id_obat'] ?>">
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
