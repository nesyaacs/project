<?php
// ============================================================
// PEMINJAMAN BUKU
// ============================================================
require_once __DIR__ . '/config/koneksi.php';
cek_login();

$role         = $_SESSION['level'];
$user_id      = $_SESSION['id_user'];
$pesan_sukses = '';
$pesan_error  = '';
$pilih        = (int)($_GET['id_buku'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses_pinjam'])) {
    $id_buku = (int)($_POST['id_buku'] ?? 0);
    $id_user = $role === 'admin' ? (int)($_POST['id_user'] ?? 0) : $user_id;
    $tanggal = esc($_POST['tgl_pinjam'] ?? '');
    $tempo   = esc($_POST['tgl_jatuh_tempo'] ?? '');
    $catatan = esc($_POST['catatan'] ?? '');

    $buku = db("SELECT * FROM buku WHERE id_buku=$id_buku");

    if (!$id_buku || !$id_user || !$tanggal || !$tempo) {
        $pesan_error = "Harap isi semua kolom formulir peminjaman!";
    } elseif (!$buku) {
        $pesan_error = "Data buku tidak ditemukan!";
    } elseif ($buku['sisa_buku'] < 1) {
        $pesan_error = "Maaf, stok buku \"" . e($buku['judul']) . "\" sedang habis!";
    } elseif (nilai("SELECT COUNT(*) FROM transaksi WHERE id_user=$id_user AND id_buku=$id_buku AND status='dipinjam'") > 0) {
        $pesan_error = "Siswa ini masih meminjam buku tersebut dan belum mengembalikannya!";
    } else {
        // Kode transaksi: TRX-20261023-001
        $prefix = "TRX-" . date('Ymd') . "-";
        $urut   = nilai("SELECT COUNT(*) FROM transaksi WHERE kode_transaksi LIKE '$prefix%'") + 1;
        $kode   = $prefix . str_pad($urut, 3, "0", STR_PAD_LEFT);

        mysqli_begin_transaction($koneksi);

        $q1 = q("INSERT INTO transaksi (kode_transaksi, id_user, id_buku, tgl_pinjam, tgl_jatuh_tempo, status, denda, catatan)
                 VALUES ('$kode', $id_user, $id_buku, '$tanggal', '$tempo', 'dipinjam', 0, '$catatan')");
        $q2 = q("UPDATE buku SET sisa_buku = sisa_buku - 1 WHERE id_buku=$id_buku");

        if ($q1 && $q2) {
            mysqli_commit($koneksi);
            $pesan_sukses = "Peminjaman berhasil dicatat dengan kode $kode. Harap kembalikan sebelum jatuh tempo!";
        } else {
            mysqli_rollback($koneksi);
            $pesan_error = "Gagal memproses peminjaman: " . mysqli_error($koneksi);
        }
    }
}

$buku_tersedia = semua("SELECT * FROM buku WHERE sisa_buku > 0 ORDER BY judul ASC");
$siswa_list    = $role === 'admin'
    ? semua("SELECT * FROM users WHERE level='siswa' AND status_aktif='aktif' ORDER BY nama_lengkap ASC")
    : [];

$page_title = "Peminjaman Buku";
require_once __DIR__ . '/includes/header.php';
?>

<div class="flora-hero" style="padding: 20px 25px;">
  <h2><?= bi('download', 24); ?> Form Peminjaman Buku Digital</h2>
  <p>
    Lakukan peminjaman buku perpustakaan secara mandiri. Batas peminjaman standar adalah 7 hari.
    Stok buku akan otomatis diperbarui oleh sistem.
  </p>
</div>

<?php alert($pesan_sukses, $pesan_error); ?>

<div class="card" style="max-width: 750px; margin: 0 auto 30px auto;">
  <div class="card-header">
    <div class="card-title"><?= bi('journal', 18); ?> Formulir Peminjaman Buku</div>
    <a href="buku.php" class="btn btn-outline btn-sm"><?= bi('book', 14); ?> Lihat Katalog Buku</a>
  </div>

  <form action="pinjam.php" method="POST">
    <?php if ($role === 'admin'): ?>
      <div class="form-group">
        <label class="form-label" for="id_user">Pilih Siswa Peminjam *</label>
        <select name="id_user" id="id_user" class="form-control" required>
          <option value="">-- Pilih Nama Siswa --</option>
          <?php foreach ($siswa_list as $s): ?>
            <option value="<?= $s['id_user']; ?>">
              <?= e($s['nama_lengkap']); ?> (NIS: <?= e($s['nis_nip']); ?> - <?= e($s['kelas']); ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php else: ?>
      <div class="form-group">
        <label class="form-label">Nama Peminjam</label>
        <input type="text" class="form-control" readonly style="background: var(--primary-subtle);"
               value="<?= e($_SESSION['nama_lengkap']); ?> (NIS: <?= e($_SESSION['nis_nip']); ?>)">
      </div>
    <?php endif; ?>

    <div class="form-group">
      <label class="form-label" for="id_buku">Pilih Buku yang Ingin Dipinjam *</label>
      <select name="id_buku" id="id_buku" class="form-control" required>
        <option value="">-- Pilih Buku yang Tersedia --</option>
        <?php foreach ($buku_tersedia as $b): ?>
          <option value="<?= $b['id_buku']; ?>" <?= $pilih === (int)$b['id_buku'] ? 'selected' : ''; ?>>
            <?= e($b['judul']); ?> | [Rak: <?= e($b['lokasi_rak']); ?>] | (Sisa: <?= $b['sisa_buku']; ?> unit)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label class="form-label" for="tgl_pinjam">Tanggal Peminjaman *</label>
        <input type="date" name="tgl_pinjam" id="tgl_pinjam" class="form-control"
               value="<?= date('Y-m-d'); ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="tgl_jatuh_tempo">Tanggal Jatuh Tempo *</label>
        <input type="date" name="tgl_jatuh_tempo" id="tgl_jatuh_tempo" class="form-control"
               value="<?= date('Y-m-d', strtotime('+7 days')); ?>" required>
        <small style="color: var(--text-muted);">* Standar waktu peminjaman adalah 7 hari</small>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label" for="catatan">Catatan / Keperluan (Opsional)</label>
      <textarea name="catatan" id="catatan" rows="2" class="form-control"
                placeholder="Contoh: Referensi tugas praktikum..."></textarea>
    </div>

    <div style="margin-top: 20px; display: flex; gap: 10px;">
      <button type="submit" name="proses_pinjam" class="btn btn-primary" style="flex: 1;">
        <?= bi('download', 14); ?> Ajukan Peminjaman Sekarang
      </button>
      <?php if ($role === 'siswa'): ?>
        <a href="pengembalian.php" class="btn btn-outline"><?= bi('upload', 13); ?> Lihat Buku Dipinjam</a>
      <?php else: ?>
        <a href="transaksi.php" class="btn btn-outline"><?= bi('arrow-repeat', 13); ?> Buka Data Transaksi</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
