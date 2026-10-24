<?php
// ============================================================
// LAPORAN SIRKULASI (KHUSUS ADMIN) + CETAK
// ============================================================
require_once __DIR__ . '/config/koneksi.php';
cek_login('admin');

$awal   = esc($_GET['tgl_awal'] ?? date('Y-m-01'));
$akhir  = esc($_GET['tgl_akhir'] ?? date('Y-m-d'));
$status = $_GET['status'] ?? '';

$where = "WHERE t.tgl_pinjam BETWEEN '$awal' AND '$akhir'";
if (in_array($status, ['dipinjam', 'dikembalikan'])) {
    $where .= " AND t.status = '$status'";
}

$data = semua("SELECT t.*, u.nama_lengkap, u.nis_nip, u.kelas, b.judul
               FROM transaksi t
               JOIN users u ON t.id_user = u.id_user
               JOIN buku b ON t.id_buku = b.id_buku
               $where
               ORDER BY t.tgl_pinjam ASC");

$total_denda = $total_kembali = $total_pinjam = 0;
foreach ($data as $row) {
    $total_denda += (int)$row['denda'];
    if ($row['status'] === 'dikembalikan') $total_kembali++;
    if ($row['status'] === 'dipinjam') $total_pinjam++;
}
$total_trx = count($data);

$page_title = "Laporan Transaksi Perpustakaan";
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-hero no-print" style="padding: 20px 25px;">
  <h2><?= bi('printer'); ?> Laporan Sirkulasi Buku Perpustakaan</h2>
  <p>
    Gunakan filter tanggal dan status di bawah ini untuk melihat dan mencetak rekapitulasi
    data peminjaman buku perpustakaan sekolah.
  </p>
</div>

<div class="card no-print">
  <div class="card-header">
    <div class="card-title"><?= bi('search'); ?> Filter Laporan Peminjaman</div>
    <button type="button" class="btn btn-primary btn-sm" onclick="window.print();">
      <?= bi('printer'); ?> Cetak / Print Laporan
    </button>
  </div>

  <form action="laporan.php" method="GET">
    <div class="form-row">
      <div class="form-group">
        <label class="form-label" for="tgl_awal">Tanggal Awal</label>
        <input type="date" name="tgl_awal" id="tgl_awal" class="form-control" value="<?= e($awal); ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="tgl_akhir">Tanggal Akhir</label>
        <input type="date" name="tgl_akhir" id="tgl_akhir" class="form-control" value="<?= e($akhir); ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="status">Status Peminjaman</label>
        <select name="status" id="status" class="form-control">
          <option value="">-- Semua Status --</option>
          <option value="dipinjam" <?= $status === 'dipinjam' ? 'selected' : ''; ?>>Sedang Dipinjam</option>
          <option value="dikembalikan" <?= $status === 'dikembalikan' ? 'selected' : ''; ?>>Sudah Dikembalikan</option>
        </select>
      </div>
    </div>
    <div style="display: flex; gap: 10px;">
      <button type="submit" class="btn btn-primary"><?= bi('search'); ?> Terapkan Filter</button>
      <a href="laporan.php" class="btn btn-outline">Reset Filter</a>
    </div>
  </form>
</div>

<div class="card">
  <div style="text-align: center; border-bottom: 2px solid var(--primary); padding-bottom: 15px; margin-bottom: 20px;">
    <h2 style="color: var(--primary-dark); font-size: 1.4rem; text-transform: uppercase; margin-bottom: 4px;">
      LAPORAN SIRKULASI PEMINJAMAN BUKU PERPUSTAKAAN DIGITAL
    </h2>
    <p style="font-size: 0.9rem; color: var(--text-muted); margin: 0;">
      SMK Tunas Bangsa • Program Keahlian Rekayasa Perangkat Lunak (RPL)
    </p>
    <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">
      Periode: <strong><?= tgl($awal); ?></strong> s/d <strong><?= tgl($akhir); ?></strong>
    </p>
  </div>

  <div class="stats-grid" style="margin-bottom: 20px;">
    <div class="stat-card" style="padding: 12px 16px;">
      <div class="stat-info"><h3><?= $total_trx; ?></h3><p>Total Transaksi</p></div>
    </div>
    <div class="stat-card" style="padding: 12px 16px;">
      <div class="stat-info"><h3><?= $total_pinjam; ?></h3><p>Masih Dipinjam</p></div>
    </div>
    <div class="stat-card" style="padding: 12px 16px;">
      <div class="stat-info"><h3><?= $total_kembali; ?></h3><p>Telah Dikembalikan</p></div>
    </div>
    <div class="stat-card" style="padding: 12px 16px;">
      <div class="stat-info"><h3><?= rp($total_denda); ?></h3><p>Total Denda Masuk</p></div>
    </div>
  </div>

  <div class="table-responsive">
    <table>
      <thead>
        <tr>
          <th>No</th>
          <th>Kode</th>
          <th>Nama Siswa (NIS)</th>
          <th>Kelas</th>
          <th>Judul Buku</th>
          <th>Tgl Pinjam</th>
          <th>Jatuh Tempo</th>
          <th>Tgl Kembali</th>
          <th>Denda</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$data): ?>
          <tr>
            <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 25px;">
              Tidak ada data transaksi pada rentang periode ini.
            </td>
          </tr>
        <?php else: $no = 1; foreach ($data as $trx): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><code><?= e($trx['kode_transaksi']); ?></code></td>
            <td>
              <strong><?= e($trx['nama_lengkap']); ?></strong><br>
              <small style="color: var(--text-muted);"><?= e($trx['nis_nip']); ?></small>
            </td>
            <td><?= e($trx['kelas'] ?? '-'); ?></td>
            <td><?= e($trx['judul']); ?></td>
            <td><?= tgl($trx['tgl_pinjam']); ?></td>
            <td><?= tgl($trx['tgl_jatuh_tempo']); ?></td>
            <td><?= tgl($trx['tgl_kembali']); ?></td>
            <td><?= $trx['denda'] > 0 ? rp($trx['denda']) : 'Rp 0'; ?></td>
            <td><span class="badge badge-<?= $trx['status']; ?>"><?= $trx['status']; ?></span></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <div style="margin-top: 40px; display: flex; justify-content: flex-end; text-align: center;">
    <div style="width: 220px;">
      <p style="font-size: 0.85rem; margin-bottom: 60px;">
        Kota Sekolah, <?= date('d F Y'); ?><br>
        Kepala Perpustakaan / Petugas,
      </p>
      <p style="font-weight: bold; text-decoration: underline; margin-bottom: 2px;">
        <?= e($_SESSION['nama_lengkap']); ?>
      </p>
      <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">
        NIP. <?= e($_SESSION['nis_nip']); ?>
      </p>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
