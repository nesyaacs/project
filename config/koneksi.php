<?php
// ============================================================
// KONEKSI DATABASE + SESSION
// ============================================================
mysqli_report(MYSQLI_REPORT_OFF);

$koneksi = @mysqli_connect("localhost", "root", "", "dbn_perpustakaan");

// Jika database belum ada, buat otomatis lalu import file SQL
if (!$koneksi) {
    $koneksi = @mysqli_connect("localhost", "root", "");
    if (!$koneksi) {
        die("<h2>MySQL belum berjalan</h2><p>Nyalakan Apache dan MySQL di XAMPP, lalu refresh halaman ini.</p>");
    }

    mysqli_query($koneksi, "CREATE DATABASE IF NOT EXISTS dbn_perpustakaan");
    mysqli_select_db($koneksi, "dbn_perpustakaan");

    $sql = @file_get_contents(__DIR__ . "/../dbn_perpustakaan.sql");
    if ($sql) {
        mysqli_multi_query($koneksi, $sql);
        while (mysqli_more_results($koneksi) && mysqli_next_result($koneksi));
    }
}

mysqli_set_charset($koneksi, "utf8mb4");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/fungsi.php';
