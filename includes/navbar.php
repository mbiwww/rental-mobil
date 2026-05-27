<?php
/**
 * Navbar — includes/navbar.php
 *
 * Komponen navigasi yang bersifat dinamis:
 * - Jika user sudah login: tampilkan avatar dengan inisial nama, dropdown profil, dan link logout
 * - Jika user belum login: tampilkan tombol Masuk dan Daftar
 *
 * Catatan: session_start() harus sudah dipanggil di halaman yang me-include file ini.
 */

// Tentukan base URL agar link berfungsi dari direktori mana pun
// Deteksi apakah navbar di-include dari subfolder (pages/ atau admin/)
$currentFile = $_SERVER['SCRIPT_FILENAME'] ?? '';
$isSubfolder = str_contains(str_replace('\\', '/', $currentFile), '/pages/')
            || str_contains(str_replace('\\', '/', $currentFile), '/admin/');
$baseUrl = $isSubfolder ? '../' : './';

// Cek status login dari session
$isLoggedIn  = isset($_SESSION['user_id']);
$userName    = $isLoggedIn ? htmlspecialchars($_SESSION['name']  ?? 'User') : '';
$userEmail   = $isLoggedIn ? htmlspecialchars($_SESSION['email'] ?? '')     : '';
$userRole    = $isLoggedIn ? ($_SESSION['role'] ?? 'customer')              : '';

// Ambil inisial nama untuk avatar (maks 2 karakter)
$initials = '';
if ($isLoggedIn && !empty($_SESSION['name'])) {
    $nameParts = explode(' ', trim($_SESSION['name']));
    $initials  = strtoupper(substr($nameParts[0], 0, 1));
    if (count($nameParts) > 1) {
        $initials .= strtoupper(substr(end($nameParts), 0, 1));
    }
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<nav class="navbar navbar-rental">
  <div class="container">

    <!-- Brand / Logo -->
    <a href="<?= $baseUrl ?>index.php" class="brand-wrapper">
      <i class="bi bi-car-front-fill brand-icon"></i>
      <span class="brand-text">RentalKu</span>
    </a>

    <div class="nav-right">
      <!-- Link Katalog -->
      <a href="<?= $baseUrl ?>pages/katalog.php" class="catalog-link">
        <i class="bi bi-grid-3x3-gap-fill me-1"></i>
        <span>Katalog Mobil</span>
      </a>

      <?php if ($isLoggedIn): ?>
        <!-- ===== USER SUDAH LOGIN ===== -->
        <div class="dropdown">
          <a href="#"
             class="profile-icon dropdown-toggle profile-toggle"
             id="profileDropdown"
             data-bs-toggle="dropdown"
             aria-expanded="false"
             role="button">
            <div class="avatar-circle">
              <?= $initials ?: 'U' ?>
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-profile" aria-labelledby="profileDropdown">
            <!-- Info profil di header dropdown -->
            <li class="profile-header">
              <div class="profile-name"><?= $userName ?></div>
              <div class="profile-email">
                <i class="bi bi-envelope-fill me-1"></i><?= $userEmail ?>
              </div>
            </li>
            <li><hr class="dropdown-divider my-1"></li>

            <?php if ($userRole === 'admin'): ?>
              <!-- Link khusus admin -->
              <li>
                <a class="dropdown-item dropdown-item-custom" href="<?= $baseUrl ?>admin/index.php">
                  <i class="bi bi-speedometer2"></i> Dashboard Admin
                </a>
              </li>
            <?php else: ?>
              <!-- Link khusus customer -->
              <li>
                <a class="dropdown-item dropdown-item-custom" href="<?= $baseUrl ?>pages/dashboard.php">
                  <i class="bi bi-grid-fill"></i> Dashboard Saya
                </a>
              </li>
            <?php endif; ?>

            <li>
              <a class="dropdown-item dropdown-item-custom text-danger" href="<?= $baseUrl ?>handlers/auth_handler.php?action=logout">
                <i class="bi bi-box-arrow-right"></i> Logout
              </a>
            </li>
          </ul>
        </div>

      <?php else: ?>
        <!-- ===== USER BELUM LOGIN ===== -->
        <a href="<?= $baseUrl ?>pages/login.php" class="btn-nav-outline">
          <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
        </a>
      <?php endif; ?>

    </div>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>