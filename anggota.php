<?php
// ============================================================
// KELOLA ANGGOTA (KHUSUS ADMIN)
// ============================================================
require_once __DIR__ . '/config/koneksi.php';
cek_login('admin');

$pesan_sukses = '';
$pesan_error  = '';
$edit         = null;

// Hapus anggota
if (($_GET['aksi'] ?? '') === 'hapus' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id === (int)$_SESSION['id_user']) {
        $pesan_error = "Anda tidak dapat menghapus akun sendiri!";
    } else {
        q("DELETE FROM users WHERE id_user=$id");
        $pesan_sukses = "Data anggota berhasil dihapus!";
    }
}

// Tambah anggota
if (isset($_POST['tambah_anggota'])) {
    $nis      = esc($_POST['nis_nip'] ?? '');
    $nama     = esc($_POST['nama_lengkap'] ?? '');
    $username = esc($_POST['username'] ?? '');
    $pass     = md5(trim($_POST['password'] ?? ''));
    $level    = esc($_POST['level'] ?? 'siswa');
    $kelas    = esc($_POST['kelas'] ?? '');
    $jurusan  = esc($_POST['jurusan'] ?? '');
    $telepon  = esc($_POST['telepon'] ?? '');
    $alamat   = esc($_POST['alamat'] ?? '');
    $status   = esc($_POST['status_aktif'] ?? 'aktif');

    if (nilai("SELECT COUNT(*) FROM users WHERE username='$username' OR nis_nip='$nis'") > 0) {
        $pesan_error = "Username atau NIS/NIP sudah terdaftar!";
    } else {
        q("INSERT INTO users (nis_nip, nama_lengkap, username, password, level, kelas, jurusan, telepon, alamat, status_aktif)
           VALUES ('$nis', '$nama', '$username', '$pass', '$level', '$kelas', '$jurusan', '$telepon', '$alamat', '$status')");
        $pesan_sukses = "Data anggota baru berhasil ditambahkan!";
    }
}

// Edit anggota
if (isset($_POST['edit_anggota'])) {
    $id        = (int)$_POST['id_user'];
    $nis       = esc($_POST['nis_nip'] ?? '');
    $nama      = esc($_POST['nama_lengkap'] ?? '');
    $username  = esc($_POST['username'] ?? '');
    $level     = esc($_POST['level'] ?? 'siswa');
    $kelas     = esc($_POST['kelas'] ?? '');
    $jurusan   = esc($_POST['jurusan'] ?? '');
    $telepon   = esc($_POST['telepon'] ?? '');
    $alamat    = esc($_POST['alamat'] ?? '');
    $status    = esc($_POST['status_aktif'] ?? 'aktif');
    $pass_baru = trim($_POST['password_baru'] ?? '');

    if (nilai("SELECT COUNT(*) FROM users WHERE username='$username' AND id_user != $id") > 0) {
        $pesan_error = "Username sudah digunakan oleh akun lain!";
    } else {
        $ganti_pass = $pass_baru ? ", password='" . md5($pass_baru) . "'" : "";
        q("UPDATE users SET nis_nip='$nis', nama_lengkap='$nama', username='$username',
           level='$level', kelas='$kelas', jurusan='$jurusan', telepon='$telepon',
           alamat='$alamat', status_aktif='$status' $ganti_pass
           WHERE id_user=$id");
        $pesan_sukses = "Data anggota berhasil diperbarui!";
    }
}

// Ambil data untuk form edit
if (($_GET['aksi'] ?? '') === 'edit' && isset($_GET['id'])) {
    $edit = db("SELECT * FROM users WHERE id_user=" . (int)$_GET['id']);
}

$daftar     = semua("SELECT * FROM users ORDER BY level ASC, nama_lengkap ASC");
$page_title = "Kelola Data Anggota";
require_once __DIR__ . '/includes/header.php';
?>

<div class="flora-hero" style="padding: 20px 25px;">
  <h2><?= bi('people', 24); ?> Kelola Data Anggota Perpustakaan</h2>
  <p>
    Menu manajemen data anggota perpustakaan digital: pendaftaran siswa, pengelolaan akun petugas/admin,
    serta kontrol status keanggotaan aktif/non-aktif.
  </p>
</div>

<?php alert($pesan_sukses, $pesan_error); ?>

<div class="card">
  <div class="card-header">
    <div class="card-title">
      <?= $edit ? bi('pencil', 16) . ' Edit Data Anggota' : bi('plus', 16) . ' Tambah Anggota / Petugas Baru'; ?>
    </div>
    <?php if ($edit): ?>
      <a href="anggota.php" class="btn btn-outline btn-sm">Batal Edit</a>
    <?php endif; ?>
  </div>

  <form action="anggota.php" method="POST">
    <?php if ($edit): ?>
      <input type="hidden" name="id_user" value="<?= $edit['id_user']; ?>">
    <?php endif; ?>

    <div class="form-row">
      <div class="form-group">
        <label class="form-label" for="nis_nip">NIS / NIP *</label>
        <input type="text" name="nis_nip" id="nis_nip" class="form-control"
               placeholder="Nomor induk siswa / petugas" required value="<?= e($edit['nis_nip'] ?? ''); ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="nama_lengkap">Nama Lengkap *</label>
        <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control"
               placeholder="Nama lengkap" required value="<?= e($edit['nama_lengkap'] ?? ''); ?>">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label class="form-label" for="username">Username *</label>
        <input type="text" name="username" id="username" class="form-control"
               placeholder="Username login" required value="<?= e($edit['username'] ?? ''); ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="level">Role / Hak Akses *</label>
        <select name="level" id="level" class="form-control" required>
          <option value="siswa" <?= ($edit['level'] ?? '') === 'siswa' ? 'selected' : ''; ?>>Siswa (User Biasa)</option>
          <option value="admin" <?= ($edit['level'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin (Petugas Perpustakaan)</option>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label class="form-label" for="kelas">Kelas</label>
        <input type="text" name="kelas" id="kelas" class="form-control"
               placeholder="Contoh: XII RPL 1" value="<?= e($edit['kelas'] ?? ''); ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="jurusan">Jurusan</label>
        <input type="text" name="jurusan" id="jurusan" class="form-control"
               placeholder="Contoh: Rekayasa Perangkat Lunak" value="<?= e($edit['jurusan'] ?? ''); ?>">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label class="form-label" for="telepon">No. Telepon / WA</label>
        <input type="text" name="telepon" id="telepon" class="form-control"
               placeholder="08xxxxxxxxxx" value="<?= e($edit['telepon'] ?? ''); ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="status_aktif">Status Keaktifan *</label>
        <select name="status_aktif" id="status_aktif" class="form-control" required>
          <option value="aktif" <?= ($edit['status_aktif'] ?? '') === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
          <option value="nonaktif" <?= ($edit['status_aktif'] ?? '') === 'nonaktif' ? 'selected' : ''; ?>>Non-Aktif</option>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label" for="alamat">Alamat Lengkap</label>
      <textarea name="alamat" id="alamat" rows="2" class="form-control"
                placeholder="Alamat rumah"><?= e($edit['alamat'] ?? ''); ?></textarea>
    </div>

    <?php if ($edit): ?>
      <div class="form-group">
        <label class="form-label" for="password_baru">Password Baru (Kosongkan jika tidak diubah)</label>
        <input type="password" name="password_baru" id="password_baru" class="form-control"
               placeholder="Password baru">
      </div>
      <button type="submit" name="edit_anggota" class="btn btn-warning">
        <?= bi('pencil', 14); ?> Simpan Perubahan Anggota
      </button>
    <?php else: ?>
      <div class="form-group">
        <label class="form-label" for="password">Password Akun *</label>
        <input type="password" name="password" id="password" class="form-control"
               placeholder="Minimal 4 karakter" required>
      </div>
      <button type="submit" name="tambah_anggota" class="btn btn-primary">
        <?= bi('plus', 14); ?> Tambah Anggota Baru
      </button>
    <?php endif; ?>
  </form>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title"><?= bi('people', 18); ?> Data Seluruh Anggota & Petugas</div>
    <span style="font-size: 0.85rem; color: var(--text-muted);">
      Total: <?= count($daftar); ?> Akun
    </span>
  </div>

  <div class="filter-bar">
    <div class="search-input-wrapper">
      <input type="text" id="tableSearch" class="form-control"
             placeholder="Ketik kata kunci untuk mencari nama, NIS, kelas, jurusan, username...">
    </div>
  </div>

  <div class="table-responsive">
    <table class="live-table">
      <thead>
        <tr>
          <th>No</th>
          <th>NIS / NIP</th>
          <th>Nama Lengkap</th>
          <th>Kelas & Jurusan</th>
          <th>Username</th>
          <th>Telepon</th>
          <th>Role</th>
          <th>Status</th>
          <th style="text-align: center;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$daftar): ?>
          <tr>
            <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 25px;">
              Belum ada data anggota tersimpan.
            </td>
          </tr>
        <?php else: $no = 1; foreach ($daftar as $u): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><code><?= e($u['nis_nip']); ?></code></td>
            <td><strong><?= e($u['nama_lengkap']); ?></strong></td>
            <td>
              <?= e($u['kelas'] ?? '-'); ?><br>
              <small style="color: var(--text-muted);"><?= e($u['jurusan'] ?? '-'); ?></small>
            </td>
            <td><?= e($u['username']); ?></td>
            <td><?= e($u['telepon'] ?? '-'); ?></td>
            <td><span class="badge badge-<?= $u['level']; ?>"><?= $u['level'] === 'admin' ? 'Admin' : 'Siswa'; ?></span></td>
            <td>
              <span class="badge <?= $u['status_aktif'] === 'aktif' ? 'badge-dikembalikan' : 'badge-ditolak'; ?>">
                <?= $u['status_aktif']; ?>
              </span>
            </td>
            <td style="text-align: center; white-space: nowrap;">
              <a href="anggota.php?aksi=edit&id=<?= $u['id_user']; ?>" class="btn btn-warning btn-sm">
                <?= bi('pencil', 12); ?> Edit
              </a>
              <?php if ($u['id_user'] != $_SESSION['id_user']): ?>
                <a href="anggota.php?aksi=hapus&id=<?= $u['id_user']; ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Hapus anggota ini?');">
                  <?= bi('trash', 12); ?> Hapus
                </a>
              <?php else: ?>
                <span style="font-size: 0.75rem; color: var(--text-muted);">(Akun Anda)</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
