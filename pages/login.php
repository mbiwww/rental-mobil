<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RentalKu · Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/login.css">
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

            <form>
              <div class="mb-3">
                <label class="form-label fw-semibold small text-uppercase tracking-wide text-secondary">Email</label>
                <div class="input-group">
                  <span class="input-group-text bg-transparent pe-2" id="email-addon">
                    <i class="bi bi-envelope"></i>
                  </span>
                  <input type="email" class="form-control ps-2" placeholder="Masukan Email" aria-label="Email" aria-describedby="email-addon" value="">
                </div>
              </div>

              <div class="mb-2">
                <label class="form-label fw-semibold small text-uppercase tracking-wide text-secondary">Password</label>
                <div class="input-group">
                  <span class="input-group-text bg-transparent pe-2" id="password-addon">
                    <i class="bi bi-lock"></i>
                  </span>
                  <input type="password" class="form-control ps-2" placeholder="Masukan Password" aria-label="Password" aria-describedby="password-addon">
                </div>
              </div>

              <div class="d-flex justify-content-end mb-4">
                <a href="resetpassword.php" class="forgot-link">Lupa Password?</a>
              </div>

              <button type="submit" class="btn btn-login w-100 text-white fs-5 mb-3">
                <a href="navbar.php"><i class="bi bi-box-arrow-in-right me-2"></i>Masuk</a>
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
</body>
</html>