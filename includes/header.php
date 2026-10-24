<?php
// ============================================================
// HEADER + MENU OTOMATIS SESUAI ROLE
// ============================================================
require_once __DIR__ . '/../config/koneksi.php';

$halaman = basename($_SERVER['PHP_SELF']);
$login   = isset($_SESSION['id_user']);
$level   = $_SESSION['level'] ?? '';
$nama    = $_SESSION['nama_lengkap'] ?? '';

if (!$login) {
    $menu = [
        ['index.php', 'house', 'Beranda'],
        ['login.php', 'box-arrow-in-right', 'Masuk (Login)'],
        ['daftar.php', 'card-checklist', 'Daftar Anggota'],
    ];
} elseif ($level === 'admin') {
    $menu = [
        ['dashboard.php', 'speedometer', 'Dashboard'],
        ['buku.php', 'book', 'Kelola Data Buku'],
        ['anggota.php', 'people', 'Kelola Anggota'],
        ['transaksi.php', 'arrow-repeat', 'Transaksi'],
        ['laporan.php', 'printer', 'Laporan'],
    ];
} else {
    $menu = [
        ['dashboard.php', 'speedometer', 'Dashboard'],
        ['buku.php', 'journal', 'Katalog Buku'],
        ['pinjam.php', 'download', 'Peminjaman Buku'],
        ['pengembalian.php', 'upload', 'Pengembalian'],
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($page_title) ? e($page_title) . ' - Perpustakaan Digital' : 'Perpustakaan Digital Sekolah'; ?></title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="navbar">
  <a href="index.php" class="brand">
    <div class="brand-icon"><?= bi('book', 20); ?></div>
    <div class="brand-text">
      <h1>Perpustakaan Digital</h1>
      <p>SMK Rekayasa Perangkat Lunak</p>
    </div>
  </a>

  <button class="nav-toggle" id="navToggle" aria-label="Toggle Menu">&#9776;</button>

  <nav>
    <ul class="nav-menu" id="navMenu">
      <?php foreach ($menu as [$url, $ikon, $label]): ?>
        <li>
          <a href="<?= $url; ?>" class="nav-link <?= $halaman === $url ? 'active' : ''; ?>">
            <?= bi($ikon, 16); ?> <?= $label; ?>
          </a>
        </li>
      <?php endforeach; ?>

      <?php if ($login): ?>
        <li class="nav-user">
          <span><?= bi('person', 15); ?> <?= $level === 'admin' ? 'Admin' : 'Siswa'; ?>:
            <strong><?= e($nama); ?></strong></span>
        </li>
        <li>
          <a href="logout.php" class="nav-link btn-danger btn-sm" style="color:#fff;"
             onclick="return confirm('Keluar dari sistem?');">
            <?= bi('box-arrow-right', 14); ?> Logout
          </a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>
</header>

<main class="main-wrapper">
