<?php

/**
 * Halaman Reset Password — pages/resetpassword.php
 *
 * Alur 2 tahap berbasis session:
 *   Tahap 1 — Verifikasi Identitas: user input Nama, NIK, Email, No. HP
 *             → kirim ke auth_handler.php?action=verify_identity
 *             → jika cocok, session['reset_verified_id'] diset, redirect ke sini (tahap 2)
 *   Tahap 2 — Buat Password Baru: form input password baru & konfirmasi
 *             → kirim ke auth_handler.php?action=reset_password
 *             → jika berhasil, session dibersihkan, redirect ke login.php
 *
 * Flash message (success/error) dibaca dari session.
 */

if (session_status() === PHP_SESSION_NONE) session_start();

// Jika sudah login, redirect ke halaman utama
if (isset($_SESSION['user_id'])) {
  $target = $_SESSION['role'] === 'admin' ? '../admin/index.php' : '../index.php';
  header("Location: $target");
  exit;
}

// Baca flash message lalu hapus dari session
$errorMsg   = $_SESSION['error']   ?? '';
$successMsg = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

/**
 * Status verifikasi:
 * - Jika session 'reset_verified_id' ada → user sudah lolos verifikasi identitas (tampil tahap 2)
 * - Jika tidak ada → tampil tahap 1 (form verifikasi identitas)
 */
$isVerified = isset($_SESSION['reset_verified_id']);

// Pertahankan nilai input verifikasi agar field tidak kosong setelah error
$oldInput = $_SESSION['reset_old_input'] ?? [];
unset($_SESSION['reset_old_input']);

?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RentalQu · Verifikasi &amp; Reset Password</title>
  <meta name="description" content="Reset password akun RentalQu dengan memverifikasi identitas menggunakan data yang terdaftar.">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../assets/css/resetpassword.css">
</head>

<body>
  <div class="container py-4 py-sm-5">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">

        <div class="card rental-card p-3 p-md-4 p-xl-5">
          <div class="card-body">

            <!-- Header -->
            <div class="text-center mb-4">
              <div class="rental-brand justify-content-center">
                <i class="bi bi-car-front-fill"></i>
                <span>RentalQu</span>
              </div>
              <p class="text-secondary-emphasis mt-2 mb-0" style="font-weight: 400;">
                <?= $isVerified ? 'Buat Password Baru' : 'Verifikasi Identitas' ?>
              </p>
              <p class="text-body-tertiary small mt-1">
                <?= $isVerified
                  ? 'Buat password baru untuk akun Anda'
                  : 'Masukkan data yang terdaftar di akun Anda' ?>
              </p>
            </div>

            <!-- Flash message: error -->
            <?php if ($errorMsg): ?>
              <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                <span><?= htmlspecialchars($errorMsg) ?></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
              </div>
            <?php endif; ?>

            <!-- Flash message: success -->
            <?php if ($successMsg): ?>
              <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                <span><?= htmlspecialchars($successMsg) ?></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
              </div>
            <?php endif; ?>

            <!-- ================================================
                 TAHAP 1: Form Verifikasi Identitas
                 Ditampilkan jika user belum terverifikasi
                 ================================================ -->
            <?php if (!$isVerified): ?>
              <form
                id="form-verify"
                action="../handlers/auth_handler.php?action=verify_identity"
                method="POST"
                novalidate>

                <!-- Nama Lengkap -->
                <div class="mb-3">
                  <label for="verify-name" class="form-label fw-semibold small tracking-wide text-secondary text-uppercase">
                    Nama Lengkap
                  </label>
                  <div class="input-group">
                    <span class="input-group-text bg-transparent pe-2">
                      <i class="bi bi-person"></i>
                    </span>
                    <input
                      type="text"
                      id="verify-name"
                      name="name"
                      class="form-control ps-2"
                      placeholder="Masukkan Nama Lengkap"
                      value="<?= htmlspecialchars($oldInput['name'] ?? '') ?>"
                      required
                      autocomplete="name">
                  </div>
                </div>

                <!-- NIK -->
                <div class="mb-3">
                  <label for="verify-nik" class="form-label fw-semibold small tracking-wide text-secondary text-uppercase">
                    NIK
                  </label>
                  <div class="input-group">
                    <span class="input-group-text bg-transparent pe-2">
                      <i class="bi bi-person-vcard"></i>
                    </span>
                    <input
                      type="text"
                      id="verify-nik"
                      name="nik"
                      class="form-control ps-2"
                      placeholder="Masukkan NIK (16 digit)"
                      maxlength="16"
                      inputmode="numeric"
                      pattern="[0-9]{16}"
                      value="<?= htmlspecialchars($oldInput['nik'] ?? '') ?>"
                      required>
                  </div>
                </div>

                <!-- Email -->
                <div class="mb-3">
                  <label for="verify-email" class="form-label fw-semibold small tracking-wide text-secondary text-uppercase">
                    Email
                  </label>
                  <div class="input-group">
                    <span class="input-group-text bg-transparent pe-2">
                      <i class="bi bi-envelope"></i>
                    </span>
                    <input
                      type="email"
                      id="verify-email"
                      name="email"
                      class="form-control ps-2"
                      placeholder="Masukkan Email"
                      value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>"
                      required
                      autocomplete="email">
                  </div>
                </div>

                <!-- Nomor HP -->
                <div class="mb-4">
                  <label for="verify-phone" class="form-label fw-semibold small tracking-wide text-secondary text-uppercase">
                    Nomor HP
                  </label>
                  <div class="input-group">
                    <span class="input-group-text bg-transparent pe-2">
                      <i class="bi bi-phone"></i>
                    </span>
                    <input
                      type="tel"
                      id="verify-phone"
                      name="phone"
                      class="form-control ps-2"
                      placeholder="Masukkan Nomor HP"
                      inputmode="numeric"
                      value="<?= htmlspecialchars($oldInput['phone'] ?? '') ?>"
                      required
                      autocomplete="tel">
                  </div>
                </div>

                <button
                  type="submit"
                  id="btn-verify"
                  class="btn btn-verify w-100 text-white fs-5 mb-3">
                  <i class="bi bi-check-circle me-2"></i>Verifikasi &amp; Lanjutkan
                </button>

              </form>

            <?php else: ?>
              <!-- ================================================
                 TAHAP 2: Form Reset Password
                 Ditampilkan setelah verifikasi identitas berhasil
                 ================================================ -->

              <!-- Indikator: verifikasi berhasil -->
              <div class="alert alert-success d-flex align-items-center gap-2 mb-4 py-2" role="status">
                <i class="bi bi-shield-check-fill flex-shrink-0"></i>
                <span class="small">Identitas terverifikasi. Buat password baru Anda.</span>
              </div>

              <form
                id="form-reset"
                action="../handlers/auth_handler.php?action=reset_password"
                method="POST"
                novalidate>

                <!-- Password Baru -->
                <div class="mb-3">
                  <label for="reset-new-password" class="form-label fw-semibold small tracking-wide text-secondary text-uppercase">
                    Password Baru
                  </label>
                  <div class="input-group">
                    <span class="input-group-text bg-transparent pe-2">
                      <i class="bi bi-lock"></i>
                    </span>
                    <input
                      type="password"
                      id="reset-new-password"
                      name="new_password"
                      class="form-control ps-2"
                      placeholder="Minimal 8 karakter"
                      required
                      autocomplete="new-password">
                    <button
                      type="button"
                      class="btn-toggle-password input-group-text bg-transparent"
                      id="toggle-new-password"
                      aria-label="Tampilkan/sembunyikan Password Baru"
                      title="Tampilkan password">
                      <i class="bi bi-eye" id="icon-new-pass"></i>
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

                <!-- Konfirmasi Password -->
                <div class="mb-4">
                  <label for="reset-confirm-password" class="form-label fw-semibold small tracking-wide text-secondary text-uppercase">
                    Konfirmasi Password
                  </label>
                  <div class="input-group">
                    <span class="input-group-text bg-transparent pe-2">
                      <i class="bi bi-lock-fill"></i>
                    </span>
                    <input
                      type="password"
                      id="reset-confirm-password"
                      name="konfirmasi_password"
                      class="form-control ps-2"
                      placeholder="Ulangi password baru"
                      required
                      autocomplete="new-password">
                    <button
                      type="button"
                      class="btn-toggle-password input-group-text bg-transparent"
                      id="toggle-confirm-password"
                      aria-label="Tampilkan/sembunyikan konfirmasi password"
                      title="Tampilkan password">
                      <i class="bi bi-eye" id="icon-confirm-pass"></i>
                    </button>
                  </div>
                  <div id="konfirm-feedback" class="form-text text-danger d-none">
                    <i class="bi bi-x-circle me-1"></i>Password tidak cocok
                  </div>
                </div>

                <button
                  type="submit"
                  id="btn-reset"
                  class="btn btn-reset w-100 text-white fs-6 mb-3">
                  <i class="bi bi-save me-2"></i>Simpan Password Baru
                </button>

              </form>

              <!-- Tombol ganti identitas (batalkan verifikasi) -->
              <div class="text-center">
                <a
                  href="../handlers/auth_handler.php?action=cancel_reset"
                  class="back-to-login small"
                  id="link-cancel-reset">
                  <i class="bi bi-arrow-counterclockwise me-1"></i>Masukkan identitas berbeda
                </a>
              </div>

            <?php endif; ?>

            <!-- Kembali ke Login -->
            <div class="text-center mt-3">
              <a href="login.php" class="back-to-login" id="link-back-to-login">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke Login
              </a>
            </div>

          </div><!-- /.card-body -->
        </div><!-- /.card -->

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // ── Toggle show/hide password ────────────────────────────────────────────
    function bindToggle(btnId, inputId, iconId) {
      const btn = document.getElementById(btnId);
      const input = document.getElementById(inputId);
      const icon = document.getElementById(iconId);
      if (!btn) return;
      btn.addEventListener('click', function() {
        const isPass = input.type === 'password';
        input.type = isPass ? 'text' : 'password';
        icon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
        btn.title = isPass ? 'Sembunyikan password' : 'Tampilkan password';
      });
    }
    bindToggle('toggle-new-password', 'reset-new-password', 'icon-new-pass');
    bindToggle('toggle-confirm-password', 'reset-confirm-password', 'icon-confirm-pass');

    // ── Syarat kekuatan password — real-time ─────────────────────────────────
    const passwordInput = document.getElementById('reset-new-password');
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

      // Perbarui cek konfirmasi
      cekKonfirmasi();
    }

    function isPasswordValid() {
      return Object.values(rules).every(({
        test
      }) => test(passwordInput.value));
    }

    if (passwordInput) passwordInput.addEventListener('input', cekPassword);

    // ── Real-time konfirmasi password ────────────────────────────────────────
    const konfirmasiInput = document.getElementById('reset-confirm-password');
    const konfirmFeedback = document.getElementById('konfirm-feedback');

    function cekKonfirmasi() {
      if (!konfirmasiInput || !konfirmFeedback) return;
      if (konfirmasiInput.value && konfirmasiInput.value !== passwordInput.value) {
        konfirmFeedback.classList.remove('d-none');
      } else {
        konfirmFeedback.classList.add('d-none');
      }
    }

    if (konfirmasiInput) konfirmasiInput.addEventListener('input', cekKonfirmasi);

    // ── Validasi form reset sebelum submit ───────────────────────────────────
    const resetForm = document.getElementById('form-reset');
    if (resetForm) {
      resetForm.addEventListener('submit', function(e) {
        // 1. Cek semua syarat password
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

        // Loading state
        const btnReset = document.getElementById('btn-reset');
        if (btnReset) {
          btnReset.disabled = true;
          btnReset.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Menyimpan...';
        }
      });
    }

    // ── Loading state form verifikasi ────────────────────────────────────────
    const verifyForm = document.getElementById('form-verify');
    if (verifyForm) {
      verifyForm.addEventListener('submit', function() {
        const btnVerify = document.getElementById('btn-verify');
        if (btnVerify) {
          btnVerify.disabled = true;
          btnVerify.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Memverifikasi...';
        }
      });
    }
  </script>
</body>

</html>