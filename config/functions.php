<?php
// config/functions.php

// 1. Fungsi Format Rupiah
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

// 2. Fungsi Cek Login Admin (Biar halaman admin gak bisa ditembak URL-nya)
function cekLoginAdmin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../login.php?pesan=nologin");
        exit;
    }
}

// 3. Fungsi Alert (Buat notifikasi sukses/gagal CRUD)
function setAlert($pesan, $tipe = 'success') {
    echo "<div class='alert alert-$tipe alert-dismissible fade show' role='alert'>
            $pesan
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
          </div>";
}

// 4. Fungsi Hitung Selisih Hari (Penting buat Nabil nanti di Transaksi)
function hitungHari($tgl_mulai, $tgl_selesai) {
    $awal  = new DateTime($tgl_mulai);
    $akhir = new DateTime($tgl_selesai);
    $diff  = $awal->diff($akhir);
    return $diff->days + 1; // Ditambah 1 karena hari mulai dihitung
}
?>