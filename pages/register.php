<?php

/**
 * Halaman Register — pages/register.php
 *
 * Menampilkan form pendaftaran customer baru.
 * Jika user sudah login, redirect ke dashboard.
 * Flash message (error) ditampilkan dari session.
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
$errorMsg = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

// Ambil URL redirect dari parameter GET yang dikirim navbar (?redirect=...)
// Validasi: hanya izinkan path lokal yang diawali '/' — tolak http:// atau //evil.com
$redirectUrl = '';
if (!empty($_GET['redirect'])) {
  $candidate = urldecode($_GET['redirect']);
  if (preg_match('/^\/[^\/]/', $candidate)) {
    $redirectUrl = $candidate;
  }
}

// Simpan nilai input sebelumnya agar tidak hilang saat error
$old = [
  'name'    => htmlspecialchars($_POST['name']    ?? ''),
  'nik'     => htmlspecialchars($_POST['nik']     ?? ''),
  'email'   => htmlspecialchars($_POST['email']   ?? ''),
  'phone'   => htmlspecialchars($_POST['phone']   ?? ''),
  'address' => htmlspecialchars($_POST['address'] ?? ''),
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RentalQu · Daftar</title>
  <meta name="description" content="Daftar akun RentalQu untuk mulai menyewa kendaraan dengan mudah dan terpercaya.">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../assets/css/register.css">
</head>

<body>
  <div class="container py-4 py-sm-5">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-10 col-md-10 col-lg-8">

        <div class="card rental-card p-3 p-md-4 p-xl-5">
          <div class="card-body">

            <div class="text-center mb-4">
              <div class="rental-brand justify-content-center">
                <i class="bi bi-car-front-fill"></i>
                <span>RentalQu</span>
              </div>
              <p class="text-secondary-emphasis mt-2 mb-0" style="font-weight: 400;">Buat Akun Baru</p>
              <p class="text-body-tertiary small mt-1">Daftar untuk mulai menyewa kendaraan</p>
            </div>

            <?php if ($errorMsg): ?>
              <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                <span><?= htmlspecialchars($errorMsg) ?></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>

            <form action="../handlers/auth_handler.php?action=register" method="POST" id="form-register" novalidate>

              <?php if ($redirectUrl): ?>
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectUrl) ?>">
              <?php endif; ?>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="reg-name" class="form-label fw-semibold small text-uppercase tracking-wide text-secondary">Nama Lengkap</label>
                  <div class="input-group">
                    <span class="input-group-text bg-transparent pe-2">
                      <i class="bi bi-person"></i>
                    </span>
                    <input
                      type="text"
                      id="reg-name"
                      name="name"
                      class="form-control ps-2"
                      placeholder="Masukan Nama Lengkap"
                      value="<?= $old['name'] ?>"
                      required
                      autocomplete="name">
                  </div>
                </div>

                <div class="col-md-6 mb-3">
                  <label for="reg-nik" class="form-label fw-semibold small text-uppercase tracking-wide text-secondary">NIK</label>
                  <div class="input-group">
                    <span class="input-group-text bg-transparent pe-2">
                      <i class="bi bi-person-vcard"></i>
                    </span>
                    <input
                      type="text"
                      id="reg-nik"
                      name="nik"
                      class="form-control ps-2"
                      placeholder="Masukan NIK (16 digit)"
                      value="<?= $old['nik'] ?>"
                      maxlength="16"
                      inputmode="numeric"
                      pattern="[0-9]{16}"
                      required>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="reg-email" class="form-label fw-semibold small text-uppercase tracking-wide text-secondary">Email</label>
                  <div class="input-group">
                    <span class="input-group-text bg-transparent pe-2">
                      <i class="bi bi-envelope"></i>
                    </span>
                    <input
                      type="email"
                      id="reg-email"
                      name="email"
                      class="form-control ps-2"
                      placeholder="Masukan Email Aktif"
                      value="<?= $old['email'] ?>"
                      required
                      autocomplete="email">
                  </div>
                </div>

                <div class="col-md-6 mb-3">
                  <label for="reg-phone" class="form-label fw-semibold small text-uppercase tracking-wide text-secondary">Nomor HP</label>
                  <div class="input-group">
                    <span class="input-group-text bg-transparent pe-2">
                      <i class="bi bi-telephone"></i>
                    </span>
                    <input
                      type="tel"
                      id="reg-phone"
                      name="phone"
                      class="form-control ps-2"
                      placeholder="Masukan Nomor Aktif"
                      value="<?= $old['phone'] ?>"
                      inputmode="numeric"
                      required
                      autocomplete="tel">
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label for="reg-address" class="form-label fw-semibold small text-uppercase tracking-wide text-secondary">Alamat Lengkap</label>
                <div class="input-group">
                  <span class="input-group-text bg-transparent pe-2">
                    <i class="bi bi-geo-alt"></i>
                  </span>
                  <input
                    type="text"
                    id="reg-address"
                    name="address"
                    class="form-control ps-2"
                    placeholder="Masukan Alamat Lengkap"
                    value="<?= $old['address'] ?>"
                    required
                    autocomplete="street-address">
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="reg-password" class="form-label fw-semibold small text-uppercase tracking-wide text-secondary">Password</label>
                  <div class="input-group">
                    <span class="input-group-text bg-transparent pe-2">
                      <i class="bi bi-lock"></i>
                    </span>
                    <input
                      type="password"
                      id="reg-password"
                      name="password"
                      class="form-control ps-2"
                      placeholder="Minimal 8 karakter"
                      required
                      autocomplete="new-password">
                    <button
                      type="button"
                      class="btn-toggle-password input-group-text bg-transparent"
                      id="toggle-password"
                      aria-label="Tampilkan/sembunyikan password"
                      title="Tampilkan password">
                      <i class="bi bi-eye" id="icon-eye"></i>
                    </button>
                  </div>

                  <!-- Panel syarat password — tampil saat user mulai mengetik -->
                  <div id="password-rules" class="mt-2 d-none">
                    <p class="mb-1 small text-secondary fw-semibold">Syarat password:</p>
                    <ul class="list-unstyled mb-0 small" style="line-height: 1.8;">
                      <li id="rule-length" class="rule-item"><i class="bi bi-x-circle-fill me-1"></i>Minimal 8 karakter <span class="text-body-tertiary">(12+ lebih aman)</span></li>
                      <li id="rule-upper" class="rule-item"><i class="bi bi-x-circle-fill me-1"></i>Mengandung huruf besar (A–Z)</li>
                      <li id="rule-lower" class="rule-item"><i class="bi bi-x-circle-fill me-1"></i>Mengandung huruf kecil (a–z)</li>
                      <li id="rule-number" class="rule-item"><i class="bi bi-x-circle-fill me-1"></i>Mengandung angka (0–9)</li>
                      <li id="rule-special" class="rule-item"><i class="bi bi-x-circle-fill me-1"></i>Mengandung karakter khusus (!&nbsp;@&nbsp;#&nbsp;$&nbsp;%&nbsp;^&nbsp;&amp;&nbsp;*)</li>
                    </ul>
                  </div>
                </div>

                <div class="col-md-6 mb-4">
                  <label for="reg-konfirmasi" class="form-label fw-semibold small text-uppercase tracking-wide text-secondary">Konfirmasi Password</label>
                  <div class="input-group">
                    <span class="input-group-text bg-transparent pe-2">
                      <i class="bi bi-lock-fill"></i>
                    </span>
                    <input
                      type="password"
                      id="reg-konfirmasi"
                      name="konfirmasi_password"
                      class="form-control ps-2"
                      placeholder="Ulangi Password Baru"
                      required
                      autocomplete="new-password">
                    <button
                      type="button"
                      class="btn-toggle-password input-group-text bg-transparent"
                      id="toggle-konfirmasi"
                      aria-label="Tampilkan/sembunyikan konfirmasi password"
                      title="Tampilkan password">
                      <i class="bi bi-eye" id="icon-eye-konfirm"></i>
                    </button>
                  </div>
                  <div id="konfirm-feedback" class="form-text text-danger d-none">
                    <i class="bi bi-x-circle me-1"></i>Password tidak cocok
                  </div>
                </div>
              </div>

              <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="agree-terms">
                <label class="form-check-label small" for="agree-terms">
                  Saya menyatakan bahwa data yang diisi adalah benar.
                </label>
                <div id="terms-feedback" class="form-text text-danger d-none">
                  <i class="bi bi-x-circle me-1"></i>Anda harus menyetujui pernyataan ini.
                </div>
              </div>

              <button type="submit" id="btn-register" class="btn btn-register w-100 text-white fs-5 mb-3">
                <i class="bi bi-person-plus me-2"></i>Daftar
              </button>

              <div class="text-center mt-3">
                <span class="text-secondary">Sudah punya akun?</span>
                <a href="login.php<?= $redirectUrl ? '?redirect=' . urlencode($redirectUrl) : '' ?>" class="login-link ms-1">Masuk Sekarang</a>
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
    const passwordInput = document.getElementById('reg-password');
    const iconEye = document.getElementById('icon-eye');

    toggleBtn.addEventListener('click', () => {
      const isPassword = passwordInput.type === 'password';
      passwordInput.type = isPassword ? 'text' : 'password';
      iconEye.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
      toggleBtn.title = isPassword ? 'Sembunyikan password' : 'Tampilkan password';
    });

    // Toggle show/hide konfirmasi password
    const toggleKonfirmBtn = document.getElementById('toggle-konfirmasi');
    const konfirmasiInput2 = document.getElementById('reg-konfirmasi');
    const iconEyeKonfirm = document.getElementById('icon-eye-konfirm');

    toggleKonfirmBtn.addEventListener('click', () => {
      const isPassword = konfirmasiInput2.type === 'password';
      konfirmasiInput2.type = isPassword ? 'text' : 'password';
      iconEyeKonfirm.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
      toggleKonfirmBtn.title = isPassword ? 'Sembunyikan password' : 'Tampilkan password';
    });

    // -------------------------------------------------------
    // Syarat kekuatan password — real-time
    // -------------------------------------------------------
    const passwordRules = document.getElementById('password-rules');
    const rules = {
      length: {
        el: document.getElementById('rule-length'),
        test: v => v.length >= 8
      },
      upper: {
        el: document.getElementById('rule-upper'),
        test: v => /[A-Z]/.test(v)
      },
      lower: {
        el: document.getElementById('rule-lower'),
        test: v => /[a-z]/.test(v)
      },
      number: {
        el: document.getElementById('rule-number'),
        test: v => /[0-9]/.test(v)
      },
      special: {
        el: document.getElementById('rule-special'),
        test: v => /[!@#$%^&*]/.test(v)
      },
    };

    function cekPassword() {
      const val = passwordInput.value;

      // Tampilkan panel syarat saat user mulai mengetik
      if (val.length > 0) {
        passwordRules.classList.remove('d-none');
      } else {
        passwordRules.classList.add('d-none');
      }

      // Update tiap item syarat
      Object.values(rules).forEach(({
        el,
        test
      }) => {
        const ok = test(val);
        const icon = el.querySelector('i');
        el.style.color = ok ? '#16a34a' : '#dc2626';
        icon.className = ok ? 'bi bi-check-circle-fill me-1' : 'bi bi-x-circle-fill me-1';
      });

      // Perbarui juga cek konfirmasi
      cekKonfirmasi();
    }

    function isPasswordValid() {
      return Object.values(rules).every(({
        test
      }) => test(passwordInput.value));
    }

    passwordInput.addEventListener('input', cekPassword);

    // -------------------------------------------------------
    // Real-time konfirmasi password
    // -------------------------------------------------------
    const konfirmasiInput = document.getElementById('reg-konfirmasi');
    const konfirmFeedback = document.getElementById('konfirm-feedback');

    function cekKonfirmasi() {
      if (konfirmasiInput.value && konfirmasiInput.value !== passwordInput.value) {
        konfirmFeedback.classList.remove('d-none');
      } else {
        konfirmFeedback.classList.add('d-none');
      }
    }

    konfirmasiInput.addEventListener('input', cekKonfirmasi);

    // -------------------------------------------------------
    // Submit handler
    // -------------------------------------------------------
    const form = document.getElementById('form-register');
    const btnRegister = document.getElementById('btn-register');
    const agreeTerms = document.getElementById('agree-terms');
    const termsFeedback = document.getElementById('terms-feedback');

    // Sembunyikan feedback checkbox saat user mencentang
    agreeTerms.addEventListener('change', () => {
      if (agreeTerms.checked) termsFeedback.classList.add('d-none');
    });

    form.addEventListener('submit', (e) => {
      // 1. Cek syarat password
      if (!isPasswordValid()) {
        e.preventDefault();
        passwordRules.classList.remove('d-none');
        cekPassword();
        passwordInput.focus();
        return;
      }

      // 2. Cek konfirmasi password
      if (konfirmasiInput.value !== passwordInput.value) {
        e.preventDefault();
        konfirmFeedback.classList.remove('d-none');
        konfirmasiInput.focus();
        return;
      }

      // 3. Cek checkbox persetujuan
      if (!agreeTerms.checked) {
        e.preventDefault();
        termsFeedback.classList.remove('d-none');
        agreeTerms.focus();
        return;
      }

      btnRegister.disabled = true;
      btnRegister.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Mendaftar...';
    });

    // Hanya izinkan angka pada input NIK dan HP
    document.getElementById('reg-nik').addEventListener('input', function() {
      this.value = this.value.replace(/\D/g, '');
    });
    document.getElementById('reg-phone').addEventListener('input', function() {
      this.value = this.value.replace(/\D/g, '');
    });
  </script>
</body>

</html>