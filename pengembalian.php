<?php
// ============================================================
// PENGEMBALIAN BUKU + DENDA
// ============================================================
require_once __DIR__ . '/config/koneksi.php';
cek_login();

$role         = $_SESSION['level'];
$user_id      = $_SESSION['id_user'];
$pesan_sukses = '';
$pesan_error  = '';

// Proses pengembalian
if (isset($_GET['id'])) {
    $id  = (int)$_GET['id'];
    $trx = db("SELECT t.*, b.judul, b.id_buku
               FROM transaksi t
               JOIN buku b ON t.id_buku = b.id_buku
               WHERE t.id_transaksi=$id AND t.status='dipinjam'");

    if ($trx) {
        if ($role !== 'admin' && $trx['id_user'] != $user_id) {
            $pesan_error = "Anda tidak berhak memproses transaksi milik siswa lain!";
        } else {
            [$hari, $denda] = hitung_denda($trx['tgl_jatuh_tempo']);
            $tgl_kembali = date('Y-m-d');

            mysqli_begin_transaction($koneksi);

            $q1 = q("UPDATE transaksi SET tgl_kembali='$tgl_kembali', status='dikembalikan', denda=$denda
                     WHERE id_transaksi=$id");
            $q2 = q("UPDATE buku SET sisa_buku = sisa_buku + 1 WHERE id_buku={$trx['id_buku']}");

            if ($q1 && $q2) {
                mysqli_commit($koneksi);
                $info = $denda > 0 ? " Terlambat $hari hari, denda " . rp($denda) . "." : " Tepat waktu, bebas denda.";
                $pesan_sukses = "Buku \"{$trx['judul']}\" berhasil dikembalikan.$info";
            } else {
                mysqli_rollback($koneksi);
                $pesan_error = "Gagal memproses pengembalian: " . mysqli_error($koneksi);
            }
        }
    }
}

$where = $role === 'admin' ? "" : "AND t.id_user = $user_id";

$pinjam = semua("SELECT t.*, u.nama_lengkap, u.nis_nip, u.kelas, b.judul, b.kode_buku, b.lokasi_rak
                 FROM transaksi t
                 JOIN users u ON t.id_user = u.id_user
                 JOIN buku b ON t.id_buku = b.id_buku
                 WHERE t.status = 'dipinjam' $where
                 ORDER BY t.tgl_jatuh_tempo ASC");

$riwayat = semua("SELECT t.*, u.nama_lengkap, b.judul
                  FROM transaksi t
                  JOIN users u ON t.id_user = u.id_user
                  JOIN buku b ON t.id_buku = b.id_buku
                  WHERE t.status = 'dikembalikan' $where
                  ORDER BY t.tgl_kembali DESC LIMIT 10");

$page_title = "Pengembalian Buku";
require_once __DIR__ . '/includes/header.php';
?>

<div class="flora-hero" style="padding: 20px 25px;">
  <h2><?= bi('upload', 24); ?> Pengembalian Buku Perpustakaan</h2>
  <p>
    Pastikan buku dikembalikan dalam kondisi baik sebelum tanggal jatuh tempo.
    Tarif denda keterlambatan adalah <strong>Rp 1.000 / hari</strong>.
  </p>
</div>

<?php alert($pesan_sukses, $pesan_error); ?>

<div class="card">
  <div class="card-header">
    <div class="card-title"><?= bi('book', 18); ?> Buku yang Sedang Dipinjam</div>
    <span style="font-size: 0.85rem; color: var(--text-muted);">
      Total: <?= count($pinjam); ?> Buku Aktif
    </span>
  </div>

  <div class="filter-bar">
    <div class="search-input-wrapper">
      <input type="text" id="tableSearch" class="form-control"
             placeholder="Cari judul buku, nama siswa, kode transaksi...">
    </div>
  </div>

  <div class="table-responsive">
    <table class="live-table">
      <thead>
        <tr>
          <th>No</th>
          <th>Kode Transaksi</th>
          <?php if ($role === 'admin'): ?><th>Nama Siswa (Kelas)</th><?php endif; ?>
          <th>Judul Buku</th>
          <th>Tgl Pinjam</th>
          <th>Jatuh Tempo</th>
          <th>Status Keterlambatan</th>
          <th style="text-align: center;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$pinjam): ?>
          <tr>
            <td colspan="<?= $role === 'admin' ? 8 : 7; ?>" style="text-align: center; color: var(--text-muted); padding: 25px;">
              Tidak ada buku yang sedang dipinjam saat ini.
            </td>
          </tr>
        <?php else: $no = 1; foreach ($pinjam as $row): ?>
          <?php [$hari, $denda] = hitung_denda($row['tgl_jatuh_tempo']); ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><code><?= e($row['kode_transaksi']); ?></code></td>
            <?php if ($role === 'admin'): ?>
              <td>
                <strong><?= e($row['nama_lengkap']); ?></strong><br>
                <small style="color: var(--text-muted);">
                  <?= e($row['nis_nip']); ?> - <?= e($row['kelas'] ?? '-'); ?>
                </small>
              </td>
            <?php endif; ?>
            <td>
              <strong><?= e($row['judul']); ?></strong><br>
              <small style="color: var(--text-muted);">
                Kode: <?= e($row['kode_buku']); ?> | Rak: <?= e($row['lokasi_rak']); ?>
              </small>
            </td>
            <td><?= tgl($row['tgl_pinjam']); ?></td>
            <td><strong><?= tgl($row['tgl_jatuh_tempo']); ?></strong></td>
            <td>
              <?php if ($hari > 0): ?>
                <span class="badge badge-ditolak">
                  <?= bi('exclamation', 12); ?> Telat <?= $hari; ?> Hari (<?= rp($denda); ?>)
                </span>
              <?php else: ?>
                <span class="badge badge-dikembalikan">Tepat Waktu</span>
              <?php endif; ?>
            </td>
            <td style="text-align: center; white-space: nowrap;">
              <a href="pengembalian.php?id=<?= $row['id_transaksi']; ?>" class="btn btn-warning btn-sm"
                 onclick="return confirm('Proses pengembalian buku ini?');">
                <?= bi('upload', 12); ?> Kembalikan Buku
              </a>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title"><?= bi('journal', 18); ?> Riwayat Pengembalian Terakhir</div>
    <span style="font-size: 0.85rem; color: var(--text-muted);">10 Riwayat Terakhir</span>
  </div>

  <div class="table-responsive">
    <table>
      <thead>
        <tr>
          <th>Kode Transaksi</th>
          <?php if ($role === 'admin'): ?><th>Nama Siswa</th><?php endif; ?>
          <th>Judul Buku</th>
          <th>Tgl Pinjam</th>
          <th>Tgl Dikembalikan</th>
          <th>Denda</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$riwayat): ?>
          <tr>
            <td colspan="<?= $role === 'admin' ? 7 : 6; ?>" style="text-align: center; color: var(--text-muted); padding: 20px;">
              Belum ada riwayat pengembalian buku.
            </td>
          </tr>
        <?php else: foreach ($riwayat as $r): ?>
          <tr>
            <td><code><?= e($r['kode_transaksi']); ?></code></td>
            <?php if ($role === 'admin'): ?><td><?= e($r['nama_lengkap']); ?></td><?php endif; ?>
            <td><?= e($r['judul']); ?></td>
            <td><?= tgl($r['tgl_pinjam']); ?></td>
            <td><?= tgl($r['tgl_kembali']); ?></td>
            <td>
              <?= $r['denda'] > 0
                  ? '<strong style="color:var(--danger);">' . rp($r['denda']) . '</strong>'
                  : 'Rp 0'; ?>
            </td>
            <td><span class="badge badge-dikembalikan">Dikembalikan</span></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
