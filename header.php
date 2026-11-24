<?php
require_once 'koneksi.php';
$hal = basename($_SERVER['PHP_SELF'], '.php');
$menu = [
    'index'  => 'Dashboard',
    'pasien' => 'Pasien',
    'dokter' => 'Dokter',
    'obat'   => 'Obat',
    'rekam'  => 'Rekam Medis',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul ?? 'Rekam Medis') ?> - Rekam Medis Sederhana</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
  <div class="wrap bar">
    <a class="brand" href="index.php">&#10010; Rekam Medis</a>
    <nav>
      <?php foreach ($menu as $file => $label): ?>
        <a href="<?= $file ?>.php" class="<?= $hal === $file ? 'aktif' : '' ?>"><?= $label ?></a>
      <?php endforeach; ?>
    </nav>
  </div>
</header>
<main class="wrap">
<?php if (!empty($_SESSION['flash'])): $f = $_SESSION['flash']; unset($_SESSION['flash']); ?>
  <div class="alert <?= $f['tipe'] === 'sukses' ? 'ok' : 'err' ?>"><?= e($f['pesan']) ?></div>
<?php endif; ?>
