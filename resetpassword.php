<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RentalKu · Verifikasi & Reset Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/resetpassword.css">
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
              <p class="text-secondary-emphasis mt-2 mb-0" style="font-weight: 400;">
                Verifikasi Identitas
              </p>
              <p class="text-body-tertiary small mt-1">
                Masukkan data yang terdaftar di akun Anda
              </p>
            </div>

            <form id="forgotPasswordForm" onsubmit="return false;">

              <div class="mb-3">
                <label class="form-label fw-semibold small tracking-wide text-secondary">NAMA LENGKAP</label>
                <div class="input-group">
                  <span class="input-group-text bg-transparent pe-2">
                    <i class="bi bi-person"></i>
                  </span>
                  <input type="text" class="form-control ps-2" id="fullName" placeholder="Masukan Nama Lengkap" required>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold small tracking-wide text-secondary">EMAIL</label>
                <div class="input-group">
                  <span class="input-group-text bg-transparent pe-2">
                    <i class="bi bi-envelope"></i>
                  </span>
                  <input type="email" class="form-control ps-2" id="email" placeholder="Masukan Email" required>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold small tracking-wide text-secondary">NIK</label>
                <div class="input-group">
                  <span class="input-group-text bg-transparent pe-2">
                    <i class="bi bi-person-vcard"></i>
                  </span>
                  <input type="number" class="form-control ps-2" id="nik" placeholder="Masukan NIK" required>
                </div>
              </div>

              <div class="mb-4">
                <label class="form-label fw-semibold small tracking-wide text-secondary">NOMOR HP</label>
                <div class="input-group">
                  <span class="input-group-text bg-transparent pe-2">
                    <i class="bi bi-phone"></i>
                  </span>
                  <input type="number" class="form-control ps-2" id="phone" placeholder="Masukan Nomor HP" required>
                </div>
              </div>

              <button type="submit" class="btn btn-verify w-100 text-white fs-5 mb-3" id="verifyBtn">
                <i class="bi bi-check-circle me-2"></i>Verifikasi & Lanjutkan
              </button>

              <div id="resetSection" class="reset-section" style="display: none;">
                <p class="fw-semibold text-secondary mb-3">
                  <i class="bi bi-key me-1"></i> Buat Password Baru
                </p>
                
                <div class="mb-3">
                  <label class="form-label fw-semibold small tracking-wide text-secondary">PASSWORD BARU</label>
                  <div class="input-group">
                    <span class="input-group-text bg-transparent pe-2">
                      <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" class="form-control ps-2" id="newPassword" placeholder="Minimal 8 karakter">
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label fw-semibold small tracking-wide text-secondary">KONFIRMASI PASSWORD</label>
                  <div class="input-group">
                    <span class="input-group-text bg-transparent pe-2">
                      <i class="bi bi-lock-fill"></i>
                    </span>
                    <input type="password" class="form-control ps-2" id="confirmPassword" placeholder="Ulangi password baru">
                  </div>
                </div>

                <button type="button" class="btn btn-reset w-100 text-white fs-6" id="savePasswordBtn">
                  <i class="bi bi-save me-2"></i>Simpan Password Baru
                </button>

                <div id="resetMessage" class="mt-3 small text-center"></div>
              </div>

              <div class="text-center mt-3">
                <a href="login.php" class="back-to-login">
                  <i class="bi bi-arrow-left me-1"></i>Kembali ke Login
                </a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- JavaScript untuk simulasi verifikasi dan reset password -->
  <script>
    (function() {
      const form = document.getElementById('forgotPasswordForm');
      const verifyBtn = document.getElementById('verifyBtn');
      const resetSection = document.getElementById('resetSection');
      const fullNameInput = document.getElementById('fullName');
      const emailInput = document.getElementById('email');
      const phoneInput = document.getElementById('phone');
      const newPasswordInput = document.getElementById('newPassword');
      const confirmPasswordInput = document.getElementById('confirmPassword');
      const savePasswordBtn = document.getElementById('savePasswordBtn');
      const resetMessage = document.getElementById('resetMessage');

      // Variabel untuk menyimpan status verifikasi
      let isVerified = false;

      // Fungsi untuk menampilkan pesan error/sukses di bawah tombol verifikasi
      function showVerifyMessage(msg, isError = true) {
        // Buat elemen alert jika belum ada
        let alertDiv = document.getElementById('verifyAlert');
        if (!alertDiv) {
          alertDiv = document.createElement('div');
          alertDiv.id = 'verifyAlert';
          alertDiv.className = 'alert-message mt-3 p-2 small';
          verifyBtn.parentNode.insertBefore(alertDiv, verifyBtn.nextSibling);
        }
        alertDiv.textContent = msg;
        alertDiv.classList.remove('text-success', 'text-danger');
        alertDiv.classList.add(isError ? 'text-danger' : 'text-success');
      }

      // Event listener tombol verifikasi
      verifyBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        const fullName = fullNameInput.value.trim();
        const email = emailInput.value.trim();
        const phone = phoneInput.value.trim();

        // Validasi sederhana: semua field harus diisi
        if (!fullName || !email || !phone) {
          showVerifyMessage('❌ Harap lengkapi semua data (Nama, Email, Nomor Telepon).', true);
          resetSection.style.display = 'none';
          isVerified = false;
          return;
        }

        // Validasi format email sederhana
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
          showVerifyMessage('❌ Format email tidak valid.', true);
          resetSection.style.display = 'none';
          isVerified = false;
          return;
        }

        // Validasi nomor telepon minimal 10 digit (contoh)
        const phoneDigits = phone.replace(/\D/g, '');
        if (phoneDigits.length < 10) {
          showVerifyMessage('❌ Nomor telepon minimal 10 digit.', true);
          resetSection.style.display = 'none';
          isVerified = false;
          return;
        }

        // Simulasi verifikasi sukses (di sini seharusnya cek ke backend)
        // Anggap data cocok
        isVerified = true;
        showVerifyMessage('✅ Data cocok! Silakan buat password baru di bawah.', false);
        
        // Tampilkan bagian reset password
        resetSection.style.display = 'block';
        
        // Reset pesan pada bagian reset
        resetMessage.innerHTML = '';
      });

      // Event listener tombol simpan password baru
      savePasswordBtn.addEventListener('click', function() {
        if (!isVerified) {
          resetMessage.innerHTML = '<span class="text-danger">⚠️ Anda harus verifikasi terlebih dahulu.</span>';
          return;
        }

        const newPass = newPasswordInput.value;
        const confirmPass = confirmPasswordInput.value;

        if (!newPass || !confirmPass) {
          resetMessage.innerHTML = '<span class="text-danger">❌ Password dan konfirmasi harus diisi.</span>';
          return;
        }

        if (newPass.length < 8) {
          resetMessage.innerHTML = '<span class="text-danger">❌ Password minimal 8 karakter.</span>';
          return;
        }

        if (newPass !== confirmPass) {
          resetMessage.innerHTML = '<span class="text-danger">❌ Konfirmasi password tidak cocok.</span>';
          return;
        }

        // Simulasi penyimpanan sukses
        resetMessage.innerHTML = '<span class="text-success">✅ Password berhasil diperbarui! Anda akan diarahkan ke login.</span>';
        
        // Opsional: kosongkan field atau redirect
        newPasswordInput.value = '';
        confirmPasswordInput.value = '';
        
        // Bisa tambahkan redirect setelah 2 detik
        setTimeout(() => {
          window.location.href = 'login.html';
        }, 2500);
      });

      // Reset tampilan jika user mengubah data verifikasi (opsional)
      [fullNameInput, emailInput, phoneInput].forEach(input => {
        input.addEventListener('input', function() {
          // Hapus pesan verifikasi jika user mengubah input
          const alertDiv = document.getElementById('verifyAlert');
          if (alertDiv) alertDiv.textContent = '';
          // Jangan sembunyikan reset section, tapi status verifikasi di-reset
          // supaya user harus verifikasi ulang
          if (isVerified) {
            isVerified = false;
            resetSection.style.display = 'none';
          }
        });
      });

    })();
  </script>
</body>
</html>