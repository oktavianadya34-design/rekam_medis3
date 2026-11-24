<?php
require_once 'koneksi.php';

$aksi = $_GET['aksi'] ?? 'list';
$errors = [];
$data = ['id_pasien' => 0, 'no_rm' => '', 'nama_pasien' => '', 'jenis_kelamin' => 'L', 'tanggal_lahir' => '', 'alamat' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['aksi'] ?? '') === 'hapus') {
        hapus_data($conn, 'pasien', 'id_pasien', (int)($_POST['id'] ?? 0), 'pasien.php', 'pasien');
    }

    $data = [
        'id_pasien'     => (int)($_POST['id_pasien'] ?? 0),
        'no_rm'         => trim($_POST['no_rm'] ?? ''),
        'nama_pasien'   => trim($_POST['nama_pasien'] ?? ''),
        'jenis_kelamin' => $_POST['jenis_kelamin'] ?? '',
        'tanggal_lahir' => trim($_POST['tanggal_lahir'] ?? ''),
        'alamat'        => trim($_POST['alamat'] ?? ''),
    ];

    if ($data['no_rm'] === '' || mb_strlen($data['no_rm']) > 20) $errors[] = 'No. RM wajib diisi (maksimal 20 karakter).';
    if ($data['nama_pasien'] === '' || mb_strlen($data['nama_pasien']) > 100) $errors[] = 'Nama pasien wajib diisi (maksimal 100 karakter).';
    if (!in_array($data['jenis_kelamin'], ['L', 'P'], true)) $errors[] = 'Jenis kelamin harus L atau P.';
    if (!tgl_valid($data['tanggal_lahir'])) $errors[] = 'Tanggal lahir tidak valid.';
    elseif ($data['tanggal_lahir'] > date('Y-m-d')) $errors[] = 'Tanggal lahir tidak boleh melebihi hari ini.';
    if ($data['alamat'] === '') $errors[] = 'Alamat wajib diisi.';

    if (!$errors) {
        try {
            if ($data['id_pasien'] > 0) {
                $st = $conn->prepare('UPDATE pasien SET no_rm=?, nama_pasien=?, jenis_kelamin=?, tanggal_lahir=?, alamat=? WHERE id_pasien=?');
                $st->bind_param('sssssi', $data['no_rm'], $data['nama_pasien'], $data['jenis_kelamin'], $data['tanggal_lahir'], $data['alamat'], $data['id_pasien']);
                $st->execute();
                flash('sukses', 'Data pasien berhasil diperbarui.');
            } else {
                $st = $conn->prepare('INSERT INTO pasien (no_rm, nama_pasien, jenis_kelamin, tanggal_lahir, alamat) VALUES (?,?,?,?,?)');
                $st->bind_param('sssss', $data['no_rm'], $data['nama_pasien'], $data['jenis_kelamin'], $data['tanggal_lahir'], $data['alamat']);
                $st->execute();
                flash('sukses', 'Data pasien berhasil ditambahkan.');
            }
            redirect('pasien.php');
        } catch (mysqli_sql_exception $ex) {
            $errors[] = ((int)$ex->getCode() === 1062) ? 'No. RM sudah digunakan pasien lain.' : 'Gagal menyimpan data pasien.';
        }
    }
    $aksi = $data['id_pasien'] > 0 ? 'edit' : 'tambah';
} elseif ($aksi === 'edit') {
    $st = $conn->prepare('SELECT * FROM pasien WHERE id_pasien = ?');
    $id = (int)($_GET['id'] ?? 0);
    $st->bind_param('i', $id);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    if (!$row) {
        flash('gagal', 'Data pasien tidak ditemukan.');
        redirect('pasien.php');
    }
    $data = $row;
}

$judul = 'Data Pasien';
require 'header.php';

if ($aksi === 'tambah' || $aksi === 'edit'):
?>
<h1><?= $aksi === 'edit' ? 'Edit' : 'Tambah' ?> Pasien</h1>
<div class="kartu">
  <?php if ($errors): ?>
    <div class="alert err"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <form method="post" action="pasien.php?aksi=<?= e($aksi) ?>">
    <input type="hidden" name="id_pasien" value="<?= (int)$data['id_pasien'] ?>">
    <div class="grid2">
      <div><label for="no_rm">No. Rekam Medis</label>
        <input type="text" id="no_rm" name="no_rm" maxlength="20" required value="<?= e($data['no_rm']) ?>"></div>
      <div><label for="nama_pasien">Nama Pasien</label>
        <input type="text" id="nama_pasien" name="nama_pasien" maxlength="100" required value="<?= e($data['nama_pasien']) ?>"></div>
      <div><label for="jenis_kelamin">Jenis Kelamin</label>
        <select id="jenis_kelamin" name="jenis_kelamin" required>
          <option value="L" <?= $data['jenis_kelamin'] === 'L' ? 'selected' : '' ?>>Laki-laki (L)</option>
          <option value="P" <?= $data['jenis_kelamin'] === 'P' ? 'selected' : '' ?>>Perempuan (P)</option>
        </select></div>
      <div><label for="tanggal_lahir">Tanggal Lahir</label>
        <input type="date" id="tanggal_lahir" name="tanggal_lahir" max="<?= date('Y-m-d') ?>" required value="<?= e($data['tanggal_lahir']) ?>"></div>
    </div>
    <label for="alamat">Alamat</label>
    <textarea id="alamat" name="alamat" required><?= e($data['alamat']) ?></textarea>
    <div class="form-aksi">
      <button class="btn" type="submit">Simpan</button>
      <a class="btn abu" href="pasien.php">Batal</a>
    </div>
  </form>
</div>
<?php
else:
    $q = trim($_GET['q'] ?? '');
    if ($q !== '') {
        $like = '%' . $q . '%';
        $st = $conn->prepare('SELECT * FROM pasien WHERE nama_pasien LIKE ? OR no_rm LIKE ? ORDER BY id_pasien DESC');
        $st->bind_param('ss', $like, $like);
        $st->execute();
        $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $rows = $conn->query('SELECT * FROM pasien ORDER BY id_pasien DESC')->fetch_all(MYSQLI_ASSOC);
    }
?>
<h1>Data Pasien</h1>
<div class="toolbar">
  <form method="get">
    <input type="text" name="q" placeholder="Cari nama / No. RM" value="<?= e($q) ?>">
    <button class="btn" type="submit">Cari</button>
    <?php if ($q !== ''): ?><a class="btn abu" href="pasien.php">Reset</a><?php endif; ?>
  </form>
  <a class="btn" href="pasien.php?aksi=tambah">+ Tambah Pasien</a>
</div>
<div class="tabel-wrap">
<table>
  <thead><tr><th>No</th><th>No. RM</th><th>Nama Pasien</th><th>JK</th><th>Tanggal Lahir</th><th>Alamat</th><th>Aksi</th></tr></thead>
  <tbody>
  <?php if (!$rows): ?><tr><td colspan="7" class="kosong">Data pasien belum ada.</td></tr><?php endif; ?>
  <?php foreach ($rows as $i => $r): ?>
    <tr>
      <td><?= $i + 1 ?></td>
      <td><?= e($r['no_rm']) ?></td>
      <td><?= e($r['nama_pasien']) ?></td>
      <td><span class="badge"><?= e($r['jenis_kelamin']) ?></span></td>
      <td><?= e(tgl_indo($r['tanggal_lahir'])) ?></td>
      <td><?= e($r['alamat']) ?></td>
      <td class="aksi">
        <a class="btn kuning kecil" href="pasien.php?aksi=edit&id=<?= (int)$r['id_pasien'] ?>">Edit</a>
        <form method="post" onsubmit="return confirm('Hapus pasien ini?')">
          <input type="hidden" name="aksi" value="hapus">
          <input type="hidden" name="id" value="<?= (int)$r['id_pasien'] ?>">
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
