<?php
/**
 * Admin Auth Check — includes/admin_auth_check.php
 *
 * Guard untuk semua halaman di folder admin/.
 * Include file ini di awal setiap halaman admin yang butuh proteksi.
 *
 * Cara pakai:
 *   require_once __DIR__ . '/../includes/admin_auth_check.php';
 *
 * Efek:
 * - Jika belum login (tidak ada session user_id) → redirect ke pages/login.php
 * - Jika sudah login tapi role bukan 'admin' → redirect ke pages/login.php
 * - Jika sudah login sebagai admin → lanjut eksekusi halaman
 *
 * Catatan: File ini otomatis memanggil session_start() jika belum aktif.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login DAN memiliki role admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../pages/login.php');
    exit;
}
