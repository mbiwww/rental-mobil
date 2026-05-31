<?php

/**
 * Halaman Login — pages/login.php
 *
 * Menampilkan form login customer.
 * Jika user sudah login, redirect ke index sesuai role.
 * Flash message (success/error) ditampilkan dari session.
 * Setelah login berhasil, user diarahkan kembali ke halaman asal.
 */

if (session_status() === PHP_SESSION_NONE) session_start();

// Jika sudah login, redirect ke halaman utama sesuai role
if (isset($_SESSION['user_id'])) {
  if ($_SESSION['role'] === 'admin') {
    header('Location: ../admin/index.php');
  } else {
    header('Location: ../index.php');
  }
  exit;
}

// Ambil flash message lalu hapus dari session
$errorMsg   = $_SESSION['error']   ?? '';
$successMsg = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

// Ambil URL redirect dari parameter GET yang dikirim navbar (?redirect=...)
// Validasi: hanya izinkan path lokal yang diawali '/' — tolak http:// atau //evil.com
$redirectUrl = '';
if (!empty($_GET['redirect'])) {
  $candidate = urldecode($_GET['redirect']);
  if (preg_match('/^\/[^\/]/', $candidate)) {
    $redirectUrl = $candidate;
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RentalKu · Login</title>
  <meta name="description" content="Login ke akun RentalKu untuk mulai menyewa kendaraan dengan mudah dan cepat.">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>
  <div class="container py-4 py-sm-5">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">

        <div class="card rental-card p-3 p-md-4 p-xl-5">
          <div class="card-body">

            <div class="text-center mb-4">
              <div class="rental-brand justify-content-center">
                <i class="bi bi-car-front-fill"></i>
                <span>RentalKu</span>
              </div>
              <p class="text-secondary-emphasis mt-2 mb-0" style="font-weight: 400;">Login ke Akun Anda</p>
              <p class="text-body-tertiary small mt-1">Masukkan email dan password untuk melanjutkan</p>
            </div>

            <?php if ($successMsg): ?>
              <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                <span><?= htmlspecialchars($successMsg) ?></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>

            <?php if ($errorMsg): ?>
              <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                <span><?= htmlspecialchars($errorMsg) ?></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>

            <form action="../handlers/auth_handler.php?action=login" method="POST" id="form-login" novalidate>

              <?php if ($redirectUrl): ?>
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectUrl) ?>">
              <?php endif; ?>

              <div class="mb-3">
                <label for="login-email" class="form-label fw-semibold small text-uppercase tracking-wide text-secondary">Email</label>
                <div class="input-group">
                  <span class="input-group-text bg-transparent pe-2" id="email-addon">
                    <i class="bi bi-envelope"></i>
                  </span>
                  <input
                    type="email"
                    id="login-email"
                    name="email"
                    class="form-control ps-2"
                    placeholder="Masukan Email"
                    aria-label="Email"
                    aria-describedby="email-addon"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    required
                    autocomplete="email">
                </div>
              </div>

              <div class="mb-2">
                <label for="login-password" class="form-label fw-semibold small text-uppercase tracking-wide text-secondary">Password</label>
                <div class="input-group">
                  <span class="input-group-text bg-transparent pe-2" id="password-addon">
                    <i class="bi bi-lock"></i>
                  </span>
                  <input
                    type="password"
                    id="login-password"
                    name="password"
                    class="form-control ps-2"
                    placeholder="Masukan Password"
                    aria-label="Password"
                    aria-describedby="password-addon"
                    required
                    autocomplete="current-password">
                  <button
                    type="button"
                    class="btn-toggle-password input-group-text bg-transparent"
                    id="toggle-password"
                    aria-label="Tampilkan/sembunyikan password"
                    title="Tampilkan password">
                    <i class="bi bi-eye" id="icon-eye"></i>
                  </button>
                </div>
              </div>

              <div class="d-flex justify-content-end mb-4">
                <a href="resetpassword.php" class="forgot-link">Lupa Password?</a>
              </div>

              <button type="submit" id="btn-login" class="btn btn-login w-100 text-white fs-5 mb-3">
                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
              </button>

              <div class="text-center mt-3">
                <span class="text-secondary">Belum punya akun?</span>
                <a href="register.php" class="signup-link ms-1">Daftar Sekarang</a>
              </div>

            </form>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Toggle show/hide password
    const toggleBtn = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('login-password');
    const iconEye = document.getElementById('icon-eye');

    toggleBtn.addEventListener('click', () => {
      const isPassword = passwordInput.type === 'password';
      passwordInput.type = isPassword ? 'text' : 'password';
      iconEye.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
      toggleBtn.title = isPassword ? 'Sembunyikan password' : 'Tampilkan password';
    });

    // Loading state saat submit
    const form = document.getElementById('form-login');
    const btnLogin = document.getElementById('btn-login');

    form.addEventListener('submit', () => {
      btnLogin.disabled = true;
      btnLogin.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Masuk...';
    });
  </script>
</body>

</html>