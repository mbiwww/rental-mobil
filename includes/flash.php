<?php

/**
 * Flash Message Toast — includes/flash.php
 *
 * Komponen reusable untuk menampilkan flash message dari session
 * sebagai Bootstrap Toast di pojok kanan atas halaman.
 *
 * Cara pakai: include file ini SETELAH navbar dan SEBELUM konten utama.
 * session_start() harus sudah dipanggil di halaman yang me-include file ini.
 *
 * Mendukung dua jenis pesan:
 * - $_SESSION['success'] : toast hijau dengan ikon centang  → class: flash-toast--success
 * - $_SESSION['error']   : toast merah dengan ikon peringatan → class: flash-toast--error
 *
 * Flash message dihapus dari session setelah dibaca (one-time display).
 * Styling dipisahkan ke assets/css/flash.css.
 */

$_flashSuccess = $_SESSION['success'] ?? '';
$_flashError   = $_SESSION['error']   ?? '';
unset($_SESSION['success'], $_SESSION['error']);

if (!$_flashSuccess && !$_flashError) return;
?>

<link rel="stylesheet" href="../assets/css/flash.css">

<!-- Flash Toast Container -->
<div class="flash-toast-container toast-container position-fixed top-0 end-0 p-3">

  <?php if ($_flashSuccess): ?>
    <div class="flash-toast flash-toast--success toast align-items-center text-white border-0 shadow"
      role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body d-flex align-items-center gap-2 fw-semibold">
          <i class="bi bi-check-circle-fill fs-5 flex-shrink-0"></i>
          <span><?= htmlspecialchars($_flashSuccess) ?></span>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto"
          data-bs-dismiss="toast" aria-label="Tutup"></button>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($_flashError): ?>
    <div class="flash-toast flash-toast--error toast align-items-center text-white border-0 shadow"
      role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body d-flex align-items-center gap-2 fw-semibold">
          <i class="bi bi-exclamation-circle-fill fs-5 flex-shrink-0"></i>
          <span><?= htmlspecialchars($_flashError) ?></span>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto"
          data-bs-dismiss="toast" aria-label="Tutup"></button>
      </div>
    </div>
  <?php endif; ?>

</div>

<script>
  // Tampilkan semua toast flash otomatis setelah Bootstrap selesai dimuat
  window.addEventListener('load', function() {
    document.querySelectorAll('.flash-toast').forEach(function(el) {
      new bootstrap.Toast(el, {
        delay: 4000
      }).show();
    });
  });
</script>