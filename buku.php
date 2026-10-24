<?php
// ============================================================
// KELOLA BUKU (ADMIN: CRUD) / KATALOG BUKU (SISWA)
// ============================================================
require_once __DIR__ . '/config/koneksi.php';
cek_login();

$role          = $_SESSION['level'];
$pesan_sukses  = '';
$pesan_error   = '';
$edit          = null;

if ($role === 'admin') {
    // Hapus buku
    if (($_GET['aksi'] ?? '') === 'hapus' && isset($_GET['id'])) {
        q("DELETE FROM buku WHERE id_buku=" . (int)$_GET['id']);
        $pesan_sukses = "Data buku berhasil dihapus!";
    }

    // Tambah buku
    if (isset($_POST['tambah_buku'])) {
        $kode      = esc($_POST['kode_buku'] ?? '');
        $judul     = esc($_POST['judul'] ?? '');
        $kategori  = (int)($_POST['id_kategori'] ?? 0);
        $pengarang = esc($_POST['pengarang'] ?? '');
        $penerbit  = esc($_POST['penerbit'] ?? '');
        $tahun     = (int)($_POST['tahun_terbit'] ?? 0);
        $isbn      = esc($_POST['isbn'] ?? '');
        $jumlah    = (int)($_POST['jumlah_buku'] ?? 0);
        $rak       = esc($_POST['lokasi_rak'] ?? '');
        $deskripsi = esc($_POST['deskripsi'] ?? '');

        if (nilai("SELECT COUNT(*) FROM buku WHERE kode_buku='$kode'") > 0) {
            $pesan_error = "Kode buku sudah digunakan! Gunakan kode lain.";
        } else {
            q("INSERT INTO buku (kode_buku, judul, id_kategori, pengarang, penerbit, tahun_terbit, isbn, jumlah_buku, sisa_buku, lokasi_rak, deskripsi)
               VALUES ('$kode', '$judul', $kategori, '$pengarang', '$penerbit', $tahun, '$isbn', $jumlah, $jumlah, '$rak', '$deskripsi')");
            $pesan_sukses = "Data buku baru berhasil ditambahkan!";
        }
    }

    // Edit buku
    if (isset($_POST['edit_buku'])) {
        $id        = (int)$_POST['id_buku'];
        $kode      = esc($_POST['kode_buku'] ?? '');
        $judul     = esc($_POST['judul'] ?? '');
        $kategori  = (int)($_POST['id_kategori'] ?? 0);
        $pengarang = esc($_POST['pengarang'] ?? '');
        $penerbit  = esc($_POST['penerbit'] ?? '');
        $tahun     = (int)($_POST['tahun_terbit'] ?? 0);
        $isbn      = esc($_POST['isbn'] ?? '');
        $jumlah    = (int)($_POST['jumlah_buku'] ?? 0);
        $sisa      = (int)($_POST['sisa_buku'] ?? 0);
        $rak       = esc($_POST['lokasi_rak'] ?? '');
        $deskripsi = esc($_POST['deskripsi'] ?? '');

        q("UPDATE buku SET kode_buku='$kode', judul='$judul', id_kategori=$kategori,
           pengarang='$pengarang', penerbit='$penerbit', tahun_terbit=$tahun,
           isbn='$isbn', jumlah_buku=$jumlah, sisa_buku=$sisa,
           lokasi_rak='$rak', deskripsi='$deskripsi'
           WHERE id_buku=$id");
        $pesan_sukses = "Data buku berhasil diperbarui!";
    }

    // Ambil data untuk form edit
    if (($_GET['aksi'] ?? '') === 'edit' && isset($_GET['id'])) {
        $edit = db("SELECT * FROM buku WHERE id_buku=" . (int)$_GET['id']);
    }
}

$kategori = semua("SELECT * FROM kategori ORDER BY nama_kategori ASC");
$daftar   = semua("SELECT b.*, k.nama_kategori
                   FROM buku b
                   LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
                   ORDER BY b.id_buku DESC");

$page_title = $role === 'admin' ? "Kelola Data Buku" : "Katalog Buku Perpustakaan";
require_once __DIR__ . '/includes/header.php';
?>

<div class="flora-hero" style="padding: 20px 25px;">
  <h2><?= bi('book', 24); ?> <?= $role === 'admin' ? 'Kelola Data Buku Perpustakaan' : 'Katalog Buku Perpustakaan'; ?></h2>
  <p>
    <?= $role === 'admin'
        ? 'Admin memiliki hak akses penuh untuk melakukan Create, Read, Update, dan Delete (CRUD) data buku.'
        : 'Temukan berbagai referensi buku menarik dan ajukan peminjaman secara digital.'; ?>
  </p>
</div>

<?php alert($pesan_sukses, $pesan_error); ?>

<?php if ($role === 'admin'): ?>
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <?= $edit ? bi('pencil', 16) . ' Edit Data Buku' : bi('plus', 16) . ' Tambah Koleksi Buku Baru'; ?>
      </div>
      <?php if ($edit): ?>
        <a href="buku.php" class="btn btn-outline btn-sm">Batal Edit</a>
      <?php endif; ?>
    </div>

    <form action="buku.php" method="POST">
      <?php if ($edit): ?>
        <input type="hidden" name="id_buku" value="<?= $edit['id_buku']; ?>">
      <?php endif; ?>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="kode_buku">Kode Buku *</label>
          <input type="text" name="kode_buku" id="kode_buku" class="form-control"
                 placeholder="Contoh: BK-RPL-007" required value="<?= e($edit['kode_buku'] ?? ''); ?>">
        </div>
        <div class="form-group">
          <label class="form-label" for="judul">Judul Buku *</label>
          <input type="text" name="judul" id="judul" class="form-control"
                 placeholder="Judul buku" required value="<?= e($edit['judul'] ?? ''); ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="id_kategori">Kategori Buku *</label>
          <select name="id_kategori" id="id_kategori" class="form-control" required>
            <option value="">-- Pilih Kategori --</option>
            <?php foreach ($kategori as $kat): ?>
              <option value="<?= $kat['id_kategori']; ?>"
                <?= (int)($edit['id_kategori'] ?? 0) === (int)$kat['id_kategori'] ? 'selected' : ''; ?>>
                <?= e($kat['nama_kategori']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="pengarang">Pengarang / Penulis *</label>
          <input type="text" name="pengarang" id="pengarang" class="form-control"
                 placeholder="Nama penulis" required value="<?= e($edit['pengarang'] ?? ''); ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="penerbit">Penerbit *</label>
          <input type="text" name="penerbit" id="penerbit" class="form-control"
                 placeholder="Penerbit" required value="<?= e($edit['penerbit'] ?? ''); ?>">
        </div>
        <div class="form-group">
          <label class="form-label" for="tahun_terbit">Tahun Terbit *</label>
          <input type="number" name="tahun_terbit" id="tahun_terbit" class="form-control"
                 min="1990" max="2099" required value="<?= e($edit['tahun_terbit'] ?? date('Y')); ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="isbn">Nomor ISBN</label>
          <input type="text" name="isbn" id="isbn" class="form-control"
                 placeholder="978-..." value="<?= e($edit['isbn'] ?? ''); ?>">
        </div>
        <div class="form-group">
          <label class="form-label" for="lokasi_rak">Lokasi Rak *</label>
          <input type="text" name="lokasi_rak" id="lokasi_rak" class="form-control"
                 placeholder="Contoh: Rak A1" required value="<?= e($edit['lokasi_rak'] ?? 'Rak A1'); ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="jumlah_buku">Jumlah Total Stok *</label>
          <input type="number" name="jumlah_buku" id="jumlah_buku" class="form-control"
                 min="1" required value="<?= e($edit['jumlah_buku'] ?? 5); ?>">
        </div>
        <?php if ($edit): ?>
          <div class="form-group">
            <label class="form-label" for="sisa_buku">Sisa Stok Tersedia *</label>
            <input type="number" name="sisa_buku" id="sisa_buku" class="form-control"
                   min="0" required value="<?= e($edit['sisa_buku'] ?? 5); ?>">
          </div>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label class="form-label" for="deskripsi">Sinopsis / Deskripsi Singkat</label>
        <textarea name="deskripsi" id="deskripsi" rows="2" class="form-control"
                  placeholder="Ringkasan isi buku..."><?= e($edit['deskripsi'] ?? ''); ?></textarea>
      </div>

      <div style="margin-top: 10px;">
        <?php if ($edit): ?>
          <button type="submit" name="edit_buku" class="btn btn-warning">
            <?= bi('pencil', 14); ?> Simpan Perubahan Buku
          </button>
        <?php else: ?>
          <button type="submit" name="tambah_buku" class="btn btn-primary">
            <?= bi('plus', 14); ?> Tambahkan Buku Baru
          </button>
        <?php endif; ?>
      </div>
    </form>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <div class="card-title"><?= bi('journal', 18); ?> Daftar Buku Perpustakaan</div>
    <span style="font-size: 0.85rem; color: var(--text-muted);">
      Total: <?= count($daftar); ?> Judul Buku
    </span>
  </div>

  <div class="filter-bar">
    <div class="search-input-wrapper">
      <input type="text" id="tableSearch" class="form-control"
             placeholder="Ketik untuk mencari judul, kode, pengarang, penerbit...">
    </div>
  </div>

  <div class="table-responsive">
    <table class="live-table">
      <thead>
        <tr>
          <th>No</th>
          <th>Kode</th>
          <th>Judul Buku</th>
          <th>Kategori</th>
          <th>Pengarang & Penerbit</th>
          <th>Tahun</th>
          <th>Rak</th>
          <th>Stok (Sisa)</th>
          <th style="text-align: center;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$daftar): ?>
          <tr>
            <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 25px;">
              Belum ada data buku yang tersimpan.
            </td>
          </tr>
        <?php else: $no = 1; foreach ($daftar as $b): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><code><?= e($b['kode_buku']); ?></code></td>
            <td>
              <strong><?= e($b['judul']); ?></strong>
              <?php if ($b['isbn']): ?>
                <br><small style="color: var(--text-muted);">ISBN: <?= e($b['isbn']); ?></small>
              <?php endif; ?>
            </td>
            <td><span class="badge badge-menunggu"><?= e($b['nama_kategori'] ?? 'Umum'); ?></span></td>
            <td>
              <?= e($b['pengarang']); ?><br>
              <small style="color: var(--text-muted);">Penerbit: <?= e($b['penerbit']); ?></small>
            </td>
            <td><?= $b['tahun_terbit']; ?></td>
            <td><strong><?= e($b['lokasi_rak']); ?></strong></td>
            <td>
              <span class="badge <?= $b['sisa_buku'] > 0 ? 'badge-dikembalikan' : 'badge-ditolak'; ?>">
                <?= $b['sisa_buku']; ?> / <?= $b['jumlah_buku']; ?>
              </span>
            </td>
            <td style="text-align: center; white-space: nowrap;">
              <?php if ($role === 'admin'): ?>
                <a href="buku.php?aksi=edit&id=<?= $b['id_buku']; ?>" class="btn btn-warning btn-sm">
                  <?= bi('pencil', 12); ?> Edit
                </a>
                <a href="buku.php?aksi=hapus&id=<?= $b['id_buku']; ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Hapus buku ini?');">
                  <?= bi('trash', 12); ?> Hapus
                </a>
              <?php elseif ($b['sisa_buku'] > 0): ?>
                <a href="pinjam.php?id_buku=<?= $b['id_buku']; ?>" class="btn btn-primary btn-sm">
                  <?= bi('download', 12); ?> Pinjam
                </a>
              <?php else: ?>
                <button class="btn btn-outline btn-sm" disabled>Habis</button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
