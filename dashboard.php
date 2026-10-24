<?php
// ============================================================
// DASHBOARD ADMIN & SISWA
// ============================================================
require_once __DIR__ . '/config/koneksi.php';
cek_login();

$level   = $_SESSION['level'];
$user_id = $_SESSION['id_user'];

$page_title = $level === 'admin' ? "Dashboard Admin Perpustakaan" : "Dashboard Siswa";
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($level === 'admin'): ?>
  <?php
  $stat = [
      ['book', nilai("SELECT COUNT(*) FROM buku"), 'Judul Buku (' . nilai("SELECT SUM(jumlah_buku) FROM buku") . ' Unit)'],
      ['people', nilai("SELECT COUNT(*) FROM users WHERE level='siswa'"), 'Siswa Terdaftar'],
      ['arrow-repeat', nilai("SELECT COUNT(*) FROM transaksi WHERE status='dipinjam'"), 'Sedang Dipinjam'],
      ['check', nilai("SELECT COUNT(*) FROM transaksi WHERE status='dikembalikan'"), 'Selesai Dikembalikan'],
  ];

  $terbaru = semua("SELECT t.*, u.nama_lengkap, u.kelas, b.judul
                    FROM transaksi t
                    JOIN users u ON t.id_user = u.id_user
                    JOIN buku b ON t.id_buku = b.id_buku
                    ORDER BY t.id_transaksi DESC LIMIT 5");
  ?>

  <div class="flora-hero">
    <h2><?= bi('speedometer', 24); ?> Selamat Datang, <?= e($_SESSION['nama_lengkap']); ?> (Admin)</h2>
    <p>
      Sistem Informasi Perpustakaan Digital Sekolah siap mengelola katalog buku, data keanggotaan siswa,
      serta pemantauan sirkulasi peminjaman dan pengembalian buku secara real-time.
    </p>
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
      <div class="card-title"><?= bi('journal', 18); ?> Menu Kelola Cepat Admin</div>
    </div>
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
      <a href="buku.php" class="btn btn-primary"><?= bi('book', 15); ?> Kelola Data Buku (CRUD)</a>
      <a href="anggota.php" class="btn btn-outline"><?= bi('people', 15); ?> Kelola Anggota Siswa (CRUD)</a>
      <a href="transaksi.php" class="btn btn-primary"><?= bi('arrow-repeat', 15); ?> Kelola Transaksi (CRUD)</a>
      <a href="laporan.php" class="btn btn-outline"><?= bi('printer', 15); ?> Cetak Rekap Laporan</a>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-title"><?= bi('arrow-repeat', 18); ?> 5 Transaksi Peminjaman Terkini</div>
      <a href="transaksi.php" class="btn btn-outline btn-sm">Lihat Semua &rarr;</a>
    </div>

    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Kode Transaksi</th>
            <th>Nama Siswa</th>
            <th>Kelas</th>
            <th>Judul Buku</th>
            <th>Tgl Pinjam</th>
            <th>Jatuh Tempo</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$terbaru): ?>
            <tr>
              <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 20px;">
                Belum ada transaksi peminjaman.
              </td>
            </tr>
          <?php else: foreach ($terbaru as $row): ?>
            <tr>
              <td><strong><?= e($row['kode_transaksi']); ?></strong></td>
              <td><?= e($row['nama_lengkap']); ?></td>
              <td><?= e($row['kelas'] ?? '-'); ?></td>
              <td><?= e($row['judul']); ?></td>
              <td><?= tgl($row['tgl_pinjam']); ?></td>
              <td><?= tgl($row['tgl_jatuh_tempo']); ?></td>
              <td><span class="badge badge-<?= $row['status']; ?>"><?= $row['status']; ?></span></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

<?php else: ?>
  <?php
  $stat = [
      ['book', nilai("SELECT COUNT(*) FROM transaksi WHERE id_user=$user_id AND status='dipinjam'"), 'Buku Sedang Dipinjam'],
      ['journal', nilai("SELECT COUNT(*) FROM transaksi WHERE id_user=$user_id"), 'Total Riwayat Pinjam'],
      ['check', nilai("SELECT COUNT(*) FROM transaksi WHERE id_user=$user_id AND status='dikembalikan'"), 'Buku Telah Kembali'],
  ];

  $dipinjam = semua("SELECT t.*, b.judul, b.pengarang, b.kode_buku, b.lokasi_rak
                     FROM transaksi t
                     JOIN buku b ON t.id_buku = b.id_buku
                     WHERE t.id_user = $user_id AND t.status = 'dipinjam'
                     ORDER BY t.tgl_jatuh_tempo ASC");
  ?>

  <div class="flora-hero">
    <h2><?= bi('person', 24); ?> Halo, <?= e($_SESSION['nama_lengkap']); ?></h2>
    <p>
      Selamat datang di portal perpustakaan digital sekolah. Anda login sebagai siswa kelas
      <strong><?= e($_SESSION['kelas'] ?? '-'); ?></strong>.
      Silakan cari buku favoritmu atau kelola buku yang sedang kamu pinjam.
    </p>
    <div style="margin-top: 15px; display: flex; gap: 10px; flex-wrap: wrap;">
      <a href="pinjam.php" class="btn btn-primary"><?= bi('download', 14); ?> Ajukan Pinjam Buku</a>
      <a href="buku.php" class="btn btn-outline"><?= bi('journal', 14); ?> Lihat Katalog Buku</a>
      <a href="pengembalian.php" class="btn btn-outline"><?= bi('upload', 14); ?> Kembalikan Buku</a>
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
      <div class="card-title"><?= bi('book', 18); ?> Buku yang Sedang Anda Pinjam</div>
      <a href="pengembalian.php" class="btn btn-outline btn-sm">Menu Pengembalian &rarr;</a>
    </div>

    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Kode Pinjam</th>
            <th>Kode Buku</th>
            <th>Judul Buku</th>
            <th>Tgl Pinjam</th>
            <th>Jatuh Tempo</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$dipinjam): ?>
            <tr>
              <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 25px;">
                Tidak ada buku yang sedang Anda pinjam. Silakan kunjungi
                <a href="pinjam.php" style="color: var(--primary-dark); font-weight: bold;">Katalog Buku</a>
                untuk meminjam buku.
              </td>
            </tr>
          <?php else: foreach ($dipinjam as $row): ?>
            <?php [$hari, $denda] = hitung_denda($row['tgl_jatuh_tempo']); ?>
            <tr>
              <td><strong><?= e($row['kode_transaksi']); ?></strong></td>
              <td><?= e($row['kode_buku']); ?></td>
              <td>
                <strong><?= e($row['judul']); ?></strong><br>
                <small style="color: var(--text-muted);">
                  Penulis: <?= e($row['pengarang']); ?> | Rak: <?= e($row['lokasi_rak']); ?>
                </small>
              </td>
              <td><?= tgl($row['tgl_pinjam']); ?></td>
              <td>
                <?= tgl($row['tgl_jatuh_tempo']); ?>
                <?php if ($hari > 0): ?>
                  <br><span style="color: var(--danger); font-size: 0.75rem; font-weight: bold;">
                    <?= bi('exclamation', 12); ?> Terlambat
                  </span>
                <?php endif; ?>
              </td>
              <td><span class="badge badge-<?= $row['status']; ?>"><?= $row['status']; ?></span></td>
              <td>
                <a href="pengembalian.php?id=<?= $row['id_transaksi']; ?>" class="btn btn-warning btn-sm"
                   onclick="return confirm('Kembalikan buku ini sekarang?');">
                  <?= bi('upload', 12); ?> Kembalikan
                </a>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
