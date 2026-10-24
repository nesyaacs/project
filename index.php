<?php
// ============================================================
// BERANDA + KATALOG BUKU (PUBLIK)
// ============================================================
$page_title = "Beranda & Katalog Koleksi";
require_once __DIR__ . '/config/koneksi.php';

$stat = [
    ['book', nilai("SELECT COUNT(*) FROM buku"), 'Judul Buku'],
    ['journal', nilai("SELECT SUM(sisa_buku) FROM buku"), 'Buku Siap Pinjam'],
    ['tag', nilai("SELECT COUNT(*) FROM kategori"), 'Kategori Koleksi'],
    ['people', nilai("SELECT COUNT(*) FROM users WHERE level='siswa'"), 'Siswa Terdaftar'],
];

$daftar_buku = semua("SELECT b.*, k.nama_kategori
                      FROM buku b
                      LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
                      ORDER BY b.id_buku DESC");

require_once __DIR__ . '/includes/header.php';
?>

<div class="flora-hero">
  <h2><?= bi('book', 24); ?> Selamat Datang di Perpustakaan Digital Sekolah</h2>
  <p>
    Mewujudkan kemudahan literasi digital bagi seluruh warga sekolah. Temukan referensi buku pelajaran,
    rekayasa perangkat lunak, sains, sastra, dan ilmu pengetahuan terkini dengan mudah, cepat, dan terorganisir.
  </p>
  <div style="margin-top: 18px; display: flex; gap: 10px; flex-wrap: wrap;">
    <?php if (!$login): ?>
      <a href="login.php" class="btn btn-primary"><?= bi('box-arrow-in-right', 15); ?> Masuk / Login</a>
      <a href="daftar.php" class="btn btn-outline"><?= bi('card-checklist', 15); ?> Daftar Anggota Siswa</a>
    <?php else: ?>
      <a href="dashboard.php" class="btn btn-primary"><?= bi('speedometer', 15); ?> Buka Dashboard Saya</a>
      <?php if ($level === 'siswa'): ?>
        <a href="pinjam.php" class="btn btn-outline"><?= bi('download', 15); ?> Ajukan Pinjam Buku</a>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<div class="stats-grid">
  <?php foreach ($stat as [$ikon, $jumlah, $label]): ?>
    <div class="stat-card">
      <div class="stat-icon"><?= bi($ikon, 24); ?></div>
      <div class="stat-info">
        <h3><?= $jumlah; ?></h3>
        <p><?= $label; ?></p>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title"><?= bi('journal', 18); ?> Koleksi Buku Perpustakaan</div>
    <span style="font-size: 0.85rem; color: var(--text-muted);">Tersedia untuk seluruh warga sekolah</span>
  </div>

  <div class="filter-bar">
    <div class="search-input-wrapper">
      <input type="text" id="bookCatalogSearch" class="form-control"
             placeholder="Ketik kata kunci untuk mencari judul, pengarang, penerbit, atau kategori...">
    </div>
  </div>

  <div class="book-grid">
    <?php if (!$daftar_buku): ?>
      <p style="text-align: center; color: var(--text-muted); grid-column: 1/-1; padding: 30px;">
        Belum ada koleksi buku yang ditambahkan.
      </p>
    <?php else: foreach ($daftar_buku as $buku): ?>
      <div class="book-card">
        <span class="book-badge"><?= e($buku['lokasi_rak']); ?></span>
        <div class="book-cover"><?= bi('book', 44); ?></div>
        <div class="book-body">
          <span class="book-category"><?= e($buku['nama_kategori'] ?? 'Umum'); ?></span>
          <h4 class="book-title"><?= e($buku['judul']); ?></h4>
          <div class="book-meta">
            Penulis: <?= e($buku['pengarang']); ?><br>
            Penerbit: <?= e($buku['penerbit']); ?> (<?= $buku['tahun_terbit']; ?>)
          </div>
          <div class="book-footer">
            <span class="book-stock">
              <?= $buku['sisa_buku'] > 0 ? 'Tersedia: ' . $buku['sisa_buku'] : 'Stok Habis'; ?>
            </span>
            <?php if ($login && $level === 'siswa'): ?>
              <?php if ($buku['sisa_buku'] > 0): ?>
                <a href="pinjam.php?id_buku=<?= $buku['id_buku']; ?>" class="btn btn-primary btn-sm">
                  <?= bi('download', 13); ?> Pinjam
                </a>
              <?php else: ?>
                <button class="btn btn-outline btn-sm" disabled>Habis</button>
              <?php endif; ?>
            <?php elseif (!$login): ?>
              <a href="login.php" class="btn btn-outline btn-sm">
                <?= bi('box-arrow-in-right', 13); ?> Login
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
