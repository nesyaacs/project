<?php
// ============================================================
// KELOLA SELURUH TRANSAKSI (KHUSUS ADMIN)
// ============================================================
require_once __DIR__ . '/config/koneksi.php';
cek_login('admin');

$pesan_sukses = '';
$pesan_error  = '';

// Hapus transaksi
if (($_GET['aksi'] ?? '') === 'hapus' && isset($_GET['id'])) {
    $id  = (int)$_GET['id'];
    $trx = db("SELECT * FROM transaksi WHERE id_transaksi=$id");

    if ($trx) {
        if ($trx['status'] === 'dipinjam') {
            q("UPDATE buku SET sisa_buku = sisa_buku + 1 WHERE id_buku={$trx['id_buku']}");
        }
        q("DELETE FROM transaksi WHERE id_transaksi=$id");
        $pesan_sukses = "Data transaksi berhasil dihapus!";
    }
}

// Perpanjang 7 hari
if (($_GET['aksi'] ?? '') === 'perpanjang' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    q("UPDATE transaksi SET
         tgl_jatuh_tempo = DATE_ADD(tgl_jatuh_tempo, INTERVAL 7 DAY),
         catatan = CONCAT(IFNULL(catatan, ''), ' [Diperpanjang 7 hari]')
       WHERE id_transaksi=$id AND status='dipinjam'");
    $pesan_sukses = "Masa peminjaman berhasil diperpanjang 7 hari!";
}

// Filter status
$status = $_GET['status'] ?? '';
$where  = in_array($status, ['dipinjam', 'dikembalikan', 'menunggu'])
    ? "WHERE t.status = '$status'"
    : '';

$daftar = semua("SELECT t.*, u.nama_lengkap, u.nis_nip, u.kelas, b.judul, b.kode_buku
                 FROM transaksi t
                 JOIN users u ON t.id_user = u.id_user
                 JOIN buku b ON t.id_buku = b.id_buku
                 $where
                 ORDER BY t.id_transaksi DESC");

$page_title = "Kelola Seluruh Transaksi";
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-hero" style="padding: 20px 25px;">
  <h2><?= bi('arrow-repeat'); ?> Kelola Sirkulasi Transaksi Peminjaman & Pengembalian</h2>
  <p>
    Admin dapat memantau seluruh transaksi peminjaman, perpanjangan masa pinjam, konfirmasi pengembalian,
    pengecekan denda keterlambatan, dan pencetakan rekap.
  </p>
  <div style="margin-top: 15px; display: flex; gap: 10px; flex-wrap: wrap;">
    <a href="pinjam.php" class="btn btn-primary"><?= bi('plus'); ?> Catat Peminjaman Baru</a>
    <a href="pengembalian.php" class="btn btn-outline"><?= bi('upload'); ?> Menu Pengembalian Cepat</a>
    <a href="laporan.php" class="btn btn-outline"><?= bi('printer'); ?> Cetak Laporan Peminjaman</a>
  </div>
</div>

<?php alert($pesan_sukses, $pesan_error); ?>

<div class="card">
  <div class="card-header">
    <div class="card-title"><?= bi('journal'); ?> Seluruh Data Transaksi Perpustakaan</div>
    <span style="font-size: 0.85rem; color: var(--text-muted);">
      Total: <?= count($daftar); ?> Transaksi
    </span>
  </div>

  <div class="filter-bar">
    <div class="search-input-wrapper">
      <input type="text" id="tableSearch" class="form-control"
             placeholder="Cari kode transaksi, nama siswa, NIS, judul buku...">
    </div>
    <div style="display: flex; gap: 8px;">
      <a href="transaksi.php" class="btn btn-sm <?= !$status ? 'btn-primary' : 'btn-outline'; ?>">Semua</a>
      <a href="transaksi.php?status=dipinjam" class="btn btn-sm <?= $status === 'dipinjam' ? 'btn-primary' : 'btn-outline'; ?>">Sedang Dipinjam</a>
      <a href="transaksi.php?status=dikembalikan" class="btn btn-sm <?= $status === 'dikembalikan' ? 'btn-primary' : 'btn-outline'; ?>">Sudah Kembali</a>
    </div>
  </div>

  <div class="table-responsive">
    <table class="live-table">
      <thead>
        <tr>
          <th>No</th>
          <th>Kode Transaksi</th>
          <th>Siswa Peminjam</th>
          <th>Buku</th>
          <th>Tgl Pinjam</th>
          <th>Jatuh Tempo</th>
          <th>Tgl Kembali</th>
          <th>Denda</th>
          <th>Status</th>
          <th style="text-align: center;">Aksi Admin</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$daftar): ?>
          <tr>
            <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 25px;">
              Belum ada data transaksi yang sesuai filter.
            </td>
          </tr>
        <?php else: $no = 1; foreach ($daftar as $trx): ?>
          <?php $telat = $trx['status'] === 'dipinjam' && date('Y-m-d') > $trx['tgl_jatuh_tempo']; ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><code><?= e($trx['kode_transaksi']); ?></code></td>
            <td>
              <strong><?= e($trx['nama_lengkap']); ?></strong><br>
              <small style="color: var(--text-muted);">
                <?= e($trx['nis_nip']); ?> (<?= e($trx['kelas'] ?? '-'); ?>)
              </small>
            </td>
            <td>
              <strong><?= e($trx['judul']); ?></strong><br>
              <small style="color: var(--text-muted);"><?= e($trx['kode_buku']); ?></small>
            </td>
            <td><?= tgl($trx['tgl_pinjam']); ?></td>
            <td>
              <?= tgl($trx['tgl_jatuh_tempo']); ?>
              <?php if ($telat): ?>
                <br><span style="color: var(--danger); font-size: 0.72rem; font-weight: bold;">
                  <?= bi('exclamation', 12); ?> Lewat Waktu!
                </span>
              <?php endif; ?>
            </td>
            <td><?= tgl($trx['tgl_kembali']); ?></td>
            <td>
              <?= $trx['denda'] > 0
                  ? '<strong style="color:var(--danger);">' . rp($trx['denda']) . '</strong>'
                  : 'Rp 0'; ?>
            </td>
            <td><span class="badge badge-<?= $trx['status']; ?>"><?= $trx['status']; ?></span></td>
            <td style="text-align: center; white-space: nowrap;">
              <?php if ($trx['status'] === 'dipinjam'): ?>
                <a href="pengembalian.php?id=<?= $trx['id_transaksi']; ?>" class="btn btn-warning btn-sm"
                   onclick="return confirm('Proses pengembalian buku ini?');">
                  <?= bi('upload'); ?> Kembali
                </a>
                <a href="transaksi.php?aksi=perpanjang&id=<?= $trx['id_transaksi']; ?>" class="btn btn-outline btn-sm"
                   onclick="return confirm('Perpanjang masa pinjam 7 hari?');">
                  <?= bi('plus'); ?> 7 Hari
                </a>
              <?php endif; ?>
              <a href="transaksi.php?aksi=hapus&id=<?= $trx['id_transaksi']; ?>" class="btn btn-danger btn-sm"
                 onclick="return confirm('Hapus transaksi ini?');">
                <?= bi('trash'); ?>
              </a>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
