<?php
// ============================================================
// FUNGSI BANTU (agar kode halaman lebih ringkas)
// ============================================================
require_once __DIR__ . '/icons.php';

// Amankan teks untuk ditampilkan
function e($teks) {
    return htmlspecialchars((string)($teks ?? ''));
}

// Jalankan query
function q($sql) {
    global $koneksi;
    return mysqli_query($koneksi, $sql);
}

// Amankan input sebelum masuk query
function esc($teks) {
    global $koneksi;
    return mysqli_real_escape_string($koneksi, trim((string)$teks));
}

// Ambil satu baris data
function db($sql) {
    $hasil = q($sql);
    return $hasil ? mysqli_fetch_assoc($hasil) : null;
}

// Ambil semua baris data
function semua($sql) {
    $hasil = q($sql);
    $data = [];
    if ($hasil) {
        while ($baris = mysqli_fetch_assoc($hasil)) {
            $data[] = $baris;
        }
    }
    return $data;
}

// Ambil satu nilai (COUNT / SUM)
function nilai($sql) {
    $baris = db($sql);
    return $baris ? (int)reset($baris) : 0;
}

// Wajib login, opsional wajib role tertentu
function cek_login($level = null) {
    if (!isset($_SESSION['id_user'])) {
        header("Location: login.php");
        exit;
    }
    if ($level && $_SESSION['level'] !== $level) {
        header("Location: dashboard.php");
        exit;
    }
}

// Tampilkan notifikasi sukses / error
function alert($sukses = '', $error = '') {
    if ($sukses) {
        echo '<div class="alert alert-success"><span>' . bi('check', 16) . ' ' . e($sukses) . '</span>
              <button class="alert-close" onclick="this.parentElement.remove();">&times;</button></div>';
    }
    if ($error) {
        echo '<div class="alert alert-danger"><span>' . bi('exclamation', 16) . ' ' . e($error) . '</span>
              <button class="alert-close" onclick="this.parentElement.remove();">&times;</button></div>';
    }
}

// Format tanggal Indonesia
function tgl($tanggal, $format = 'd/m/Y') {
    return $tanggal ? date($format, strtotime($tanggal)) : '-';
}

// Format rupiah
function rp($angka) {
    return 'Rp ' . number_format((int)$angka, 0, ',', '.');
}

// Hitung keterlambatan & denda (Rp 1.000 / hari)
function hitung_denda($jatuh_tempo) {
    $hari = max(0, (int)floor((strtotime(date('Y-m-d')) - strtotime($jatuh_tempo)) / 86400));
    return [$hari, $hari * 1000];
}
