<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RentalKu · Daftar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/register.css">
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
              <p class="text-secondary-emphasis mt-2 mb-0" style="font-weight: 400;">Buat Akun Baru</p>
              <p class="text-body-tertiary small mt-1">Daftar untuk mulai menyewa kendaraan</p>
             </div>

            <form>
              <div class="mb-3">
                <label class="form-label fw-semibold small text-uppercase tracking-wide text-secondary">Nama Lengkap</label>
                <div class="input-group">
                  <span class="input-group-text bg-transparent pe-2">
                    <i class="bi bi-person"></i>
                  </span>
                  <input type="text" class="form-control ps-2" placeholder="Masukan Nama Lengkap" value="">
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold small text-uppercase tracking-wide text-secondary">NIK</label>
                <div class="input-group">
                  <span class="input-group-text bg-transparent pe-2">
                    <i class="bi bi-person-vcard"></i>
                  </span>
                  <input type="number" class="form-control ps-2" placeholder="Masukan NIK" value="">
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold small text-uppercase tracking-wide text-secondary">Email</label>
                <div class="input-group">
                  <span class="input-group-text bg-transparent pe-2">
                    <i class="bi bi-envelope"></i>
                  </span>
                  <input type="email" class="form-control ps-2" placeholder="Masukan Email Aktif" value="">
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold small text-uppercase tracking-wide text-secondary">Nomor HP</label>
                <div class="input-group">
                  <span class="input-group-text bg-transparent pe-2">
                    <i class="bi bi-telephone"></i>
                  </span>
                  <input type="number" class="form-control ps-2" placeholder="Masukan Nomor Aktif" value="">
                </div>
              </div>
              
              <div class="mb-3">
                <label class="form-label fw-semibold small text-uppercase tracking-wide text-secondary">Alamat Lengkap</label>
                <div class="input-group">
                  <span class="input-group-text bg-transparent pe-2">
                    <i class="bi bi-geo-alt"></i>
                  </span>
                  <input type="text" class="form-control ps-2" placeholder="Masukan Alamat Lengkap" value="">
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold small text-uppercase tracking-wide text-secondary">Password</label>
                <div class="input-group">
                  <span class="input-group-text bg-transparent pe-2">
                    <i class="bi bi-lock"></i>
                  </span>
                  <input type="password" class="form-control ps-2" placeholder="Masukan Password Baru">
                </div>
              </div>

              <div class="mb-4">
                <label class="form-label fw-semibold small text-uppercase tracking-wide text-secondary">Konfirmasi Password</label>
                <div class="input-group">
                  <span class="input-group-text bg-transparent pe-2">
                    <i class="bi bi-lock-fill"></i>
                  </span>
                  <input type="password" class="form-control ps-2" placeholder="Konfirmasi Password Baru">
                </div>
              </div>

              <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="agree_terms" required>
                <label class="form-check-label" for="agree_terms">
                  Saya menyatakan bahwa data yang diisi adalah benar.
                </label>
              </div>

              <button type="submit" class="btn btn-register w-100 text-white fs-5 mb-3">
                <i class="bi bi-person-plus me-2"></i>Daftar
              </button>

              <div class="text-center mt-3">
                <span class="text-secondary">Sudah punya akun?</span>
                <a href="login.php" class="login-link ms-1">Masuk Sekarang</a>
              </div>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>