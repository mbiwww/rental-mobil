<?php
/**
 * Auth Check — includes/auth_check.php
 *
 * Guard untuk halaman yang hanya boleh diakses oleh customer yang sudah login.
 * Include file ini di awal setiap halaman pages/ yang butuh proteksi.
 *
 * Cara pakai:
 *   require_once '../includes/auth_check.php';
 *
 * Efek:
 * - Jika belum login → redirect ke pages/login.php
 * - Jika sudah login tapi role = admin → redirect ke admin/index.php
 * - Jika sudah login sebagai customer → lanjut eksekusi halaman
 *
 * Catatan: session_start() harus sudah dipanggil sebelum include file ini.
 */

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

// Jika admin mengakses halaman customer, arahkan ke dashboard admin
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: ../admin/index.php');
    exit;
}
