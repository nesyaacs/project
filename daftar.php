<?php
// ============================================================
// PENDAFTARAN ANGGOTA SISWA
// ============================================================
$page_title = "Daftar Anggota Siswa Baru";
require_once __DIR__ . '/config/koneksi.php';

if (isset($_SESSION['id_user'])) {
    header("Location: dashboard.php");
    exit;
}

$pesan_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nis        = esc($_POST['nis'] ?? '');
    $nama       = esc($_POST['nama_lengkap'] ?? '');
    $kelas      = esc($_POST['kelas'] ?? '');
    $jurusan    = esc($_POST['jurusan'] ?? '');
    $telepon    = esc($_POST['telepon'] ?? '');
    $alamat     = esc($_POST['alamat'] ?? '');
    $username   = esc($_POST['username'] ?? '');
    $password   = trim($_POST['password'] ?? '');
    $konfirmasi = trim($_POST['konfirmasi_password'] ?? '');

    if (!$nis || !$nama || !$username || !$password) {
        $pesan_error = "Harap lengkapi semua kolom bertanda wajib (*)!";
    } elseif ($password !== $konfirmasi) {
        $pesan_error = "Konfirmasi password tidak cocok!";
    } elseif (strlen($password) < 4) {
        $pesan_error = "Password minimal terdiri dari 4 karakter!";
    } elseif (nilai("SELECT COUNT(*) FROM users WHERE username='$username' OR nis_nip='$nis'") > 0) {
        $pesan_error = "Username atau NIS sudah terdaftar sebelumnya!";
    } else {
        $pass = md5($password);
        q("INSERT INTO users (nis_nip, nama_lengkap, username, password, level, kelas, jurusan, telepon, alamat, status_aktif)
           VALUES ('$nis', '$nama', '$username', '$pass', 'siswa', '$kelas', '$jurusan', '$telepon', '$alamat', 'aktif')");
        header("Location: login.php?daftar=berhasil");
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-container" style="min-height: 90vh;">
  <div class="auth-card" style="max-width: 550px;">
    <div class="auth-header">
      <div style="margin-bottom: 6px;"><?= bi('card-checklist', 34); ?></div>
      <h2>Pendaftaran Anggota Siswa</h2>
      <p>Isi formulir berikut untuk menjadi anggota perpustakaan sekolah</p>
    </div>

    <div class="auth-body">
      <?php alert('', $pesan_error); ?>

      <form action="daftar.php" method="POST">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="nis">NIS (Nomor Induk Siswa) *</label>
            <input type="text" name="nis" id="nis" class="form-control"
                   placeholder="Contoh: 202410101" required value="<?= e($_POST['nis'] ?? ''); ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="nama_lengkap">Nama Lengkap Siswa *</label>
            <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control"
                   placeholder="Nama lengkap Anda" required value="<?= e($_POST['nama_lengkap'] ?? ''); ?>">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="kelas">Kelas *</label>
            <input type="text" name="kelas" id="kelas" class="form-control"
                   placeholder="Contoh: XII RPL 1" required value="<?= e($_POST['kelas'] ?? ''); ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="jurusan">Jurusan / Kompetensi Keahlian</label>
            <input type="text" name="jurusan" id="jurusan" class="form-control"
                   placeholder="Contoh: Rekayasa Perangkat Lunak" value="<?= e($_POST['jurusan'] ?? ''); ?>">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="telepon">No. Telepon / WhatsApp</label>
            <input type="text" name="telepon" id="telepon" class="form-control"
                   placeholder="08xxxxxxxxxx" value="<?= e($_POST['telepon'] ?? ''); ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="username">Username Akun *</label>
            <input type="text" name="username" id="username" class="form-control"
                   placeholder="Username untuk login" required value="<?= e($_POST['username'] ?? ''); ?>">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="alamat">Alamat Tinggal</label>
          <textarea name="alamat" id="alamat" rows="2" class="form-control"
                    placeholder="Alamat tempat tinggal"><?= e($_POST['alamat'] ?? ''); ?></textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="password">Password *</label>
            <input type="password" name="password" id="password" class="form-control"
                   placeholder="Minimal 4 karakter" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="konfirmasi_password">Ulangi Password *</label>
            <input type="password" name="konfirmasi_password" id="konfirmasi_password"
                   class="form-control" placeholder="Konfirmasi password" required>
          </div>
        </div>

        <div style="margin-top: 15px;">
          <button type="submit" class="btn btn-primary" style="width: 100%;">
            <?= bi('card-checklist', 15); ?> Daftarkan Akun Saya
          </button>
        </div>
      </form>

      <div class="auth-footer">
        Sudah memiliki akun perpustakaan?<br>
        <a href="login.php"><?= bi('box-arrow-in-right', 14); ?> Kembali ke Halaman Login</a>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
