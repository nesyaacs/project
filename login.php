<?php
// ============================================================
// LOGIN ADMIN & SISWA
// ============================================================
$page_title = "Masuk Sistem";
require_once __DIR__ . '/config/koneksi.php';

if (isset($_SESSION['id_user'])) {
    header("Location: dashboard.php");
    exit;
}

$pesan_error  = '';
$pesan_sukses = isset($_GET['daftar']) ? "Pendaftaran berhasil! Silakan masuk dengan akun Anda." : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = esc($_POST['username'] ?? '');
    $password = md5(trim($_POST['password'] ?? ''));

    $user = db("SELECT * FROM users WHERE username='$username' AND password='$password' LIMIT 1");

    if (!$user) {
        $pesan_error = "Username atau Password salah! Silakan coba lagi.";
    } elseif ($user['status_aktif'] !== 'aktif') {
        $pesan_error = "Akun Anda berstatus non-aktif. Silakan hubungi admin perpustakaan.";
    } else {
        foreach (['id_user', 'nis_nip', 'username', 'nama_lengkap', 'level', 'kelas'] as $kolom) {
            $_SESSION[$kolom] = $user[$kolom];
        }
        header("Location: dashboard.php");
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-container">
  <div class="auth-card">
    <div class="auth-header">
      <div style="margin-bottom: 6px;"><?= bi('book', 34); ?></div>
      <h2>Masuk ke Perpustakaan</h2>
      <p>Silakan masuk menggunakan akun Admin atau Siswa</p>
    </div>

    <div class="auth-body">
      <?php alert($pesan_sukses, $pesan_error); ?>

      <form action="login.php" method="POST" autocomplete="off">
        <div class="form-group">
          <label class="form-label" for="username">Username</label>
          <input type="text" name="username" id="username" class="form-control"
                 placeholder="Masukkan username" autocomplete="off" required autofocus>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <input type="password" name="password" id="password" class="form-control"
                 placeholder="Masukkan password" autocomplete="new-password" required>
        </div>

        <div style="margin-top: 20px;">
          <button type="submit" class="btn btn-primary" style="width: 100%;">
            <?= bi('box-arrow-in-right', 15); ?> Masuk Sekarang
          </button>
        </div>
      </form>

      <div class="auth-footer">
        Belum terdaftar sebagai anggota siswa?<br>
        <a href="daftar.php"><?= bi('card-checklist', 14); ?> Klik di sini untuk Daftar Anggota</a>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
