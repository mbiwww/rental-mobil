<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RentalKu · Home</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
  <?php
  include 'assets/includes/navbar.php';
  ?>
<section class="hero-section">
  <div class="container hero-content">
    <div class="row align-items-center g-4">

      <div class="col-lg-6">
        <h1 class="hero-title">Sewa Mobil Impian Anda dengan Mudah</h1>
        <p class="hero-desc mt-3">Ratusan pilihan mobil berkualitas dengan harga terjangkau. Proses cepat, aman, dan terpercaya.</p>
        <div class="search-card mt-4">
          <h5 class="fw-bold mb-3"><i class="bi bi-funnel-fill me-2"></i>Jenis Mobil</h5>
          <div class="mb-3">
            <select class="form-select" id="carCategory">
              <option selected disabled>Pilih kategori</option>
              <option value="semua">Semua Tipe</option>
              <option value="suv">SUV</option>
              <option value="sedan">Sedan</option>
              <option value="hatchback">Hatchback</option>
              <option value="mpv">MPV</option>
            </select>
          </div>
          <a href="#">
            <button class="btn btn-search w-100" id="searchBtn">
              <i class="bi bi-search me-2"></i>Cari Mobil
            </button>
          </a>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="car-img-placeholder d-none d-lg-flex">
        <img src="https://4kwallpapers.com/images/walls/thumbs_3t/24397.jpg" class="custom-image" alt="Mobil">
        </div>
      </div>
    </div>
  </div>
</section>

<!-- MOBIL UNGGULAN -->
<section class="container py-5">
  <div class="text-center mb-5">
    <h2 class="fw-bold">Mobil Unggulan</h2>
    <p class="text-secondary">Pilihan mobil terbaik untuk perjalanan Anda</p>
  </div>
  <div class="row g-4">
    <!-- Card 1: -->
    <div class="col-md-4">
      <div class="car-card p-3">
        <div class="car-img-placeholder rounded-4 mb-3">
          <img src="https://thumb.katadata.co.id/frontend/thumbnail/2024/06/29/zigi-668026c50bcd0-toyota-fortuner-bekas_910_512.jpg" class="custom-image" alt="Mobil">
        </div>
        <h5 class="fw-bold">Toyota Fortuner 2023</h5>
        <div class="car-detail-item"><i class="bi bi-gear-fill"></i> <strong>Transmisi:</strong> Automatic </div>
        <div class="car-detail-item"><i class="bi bi-people-fill"></i> <strong>Kursi:</strong> 7 Penumpang </div>
        <div class="car-detail-item"><i class="bi bi-briefcase-fill"></i> <strong>Bagasi:</strong> 3 Koper</div>
        <div class="car-detail-item"><i class="bi bi-calendar3"></i> <strong>Tahun:</strong> 2023</div>
        <div class="d-flex justify-content-between align-items-center mt-3">
          <span class="price">Rp 500.000 <small class="text-secondary fw-normal">/hari</small></span>
          <a href="#" class="btn btn-sewa btn-sm">Sewa</a>
        </div>
      </div>
    </div>
    <!-- Card 2: -->
    <div class="col-md-4">
      <div class="car-card p-3">
        <div class="car-img-placeholder rounded-4 mb-3">
          <img src="https://static1.hotcarsimages.com/wordpress/wp-content/uploads/2024/02/2023-honda-civic-si.jpg" class="custom-image" alt="Mobil">
        </div>
        <h5 class="fw-bold">Honda Civic Tahun 2024</h5>
        <div class="car-detail-item"><i class="bi bi-gear-fill"></i> <strong>Transmisi:</strong> Automatic </div>
        <div class="car-detail-item"><i class="bi bi-people-fill"></i> <strong>Kursi:</strong> 5 Penumpang </div>
        <div class="car-detail-item"><i class="bi bi-briefcase-fill"></i> <strong>Bagasi:</strong> 3 Koper</div>
        <div class="car-detail-item"><i class="bi bi-calendar3"></i> <strong>Tahun:</strong> 2024</div>
        <div class="d-flex justify-content-between align-items-center mt-3">
          <span class="price">Rp 400.000 <small class="text-secondary fw-normal">/hari</small></span>
          <a href="#" class="btn btn-sewa btn-sm">Sewa</a>
        </div>
      </div>
    </div>
    <!-- Card 3 -->
    <div class="col-md-4">
      <div class="car-card p-3">
        <div class="car-img-placeholder rounded-4 mb-3">
          <img src="https://wallpapercave.com/wp/wp12186590.jpg" class="custom-image" alt="Mobil">
        </div>
        <h5 class="fw-bold">Toyota Avanza 2023</h5>
        <div class="car-detail-item"><i class="bi bi-gear-fill"></i> <strong>Transmisi:</strong> Manual </div>
        <div class="car-detail-item"><i class="bi bi-people-fill"></i> <strong>Kursi:</strong> 5 Penumpang </div>
        <div class="car-detail-item"><i class="bi bi-briefcase-fill"></i> <strong>Bagasi:</strong> 3 Koper</div>
        <div class="car-detail-item"><i class="bi bi-calendar3"></i> <strong>Tahun:</strong> 2023</div>
        <div class="d-flex justify-content-between align-items-center mt-3">
          <span class="price">Rp 300.000 <small class="text-secondary fw-normal">/hari</small></span>
          <a href="#" class="btn btn-sewa btn-sm">Sewa</a>
        </div>
      </div>
    </div>
  </div>
  <div class="text-center mt-5">
    <a href="#" class="btn btn-seeall btn-lg px-5">
      <i class="bi bi-grid-3x3-gap-fill me-2"></i>Lihat Semua Mobil
    </a>
  </div>
</section>

<!-- CARA KERJA -->
<section class="bg-white py-5">
  <div class="container text-center">
    <h2 class="fw-bold mb-4">Cara Kerja Rental</h2>
    <p class="text-secondary mb-5">Proses mudah dalam 3 langkah</p>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="step-icon">1</div>
        <h5 class="fw-bold">Pilih Mobil</h5>
        <p class="text-secondary">Cari dan pilih mobil sesuai kebutuhan Anda dari katalog kami</p>
      </div>
      <div class="col-md-4">
        <div class="step-icon">2</div>
        <h5 class="fw-bold">Booking & Bayar</h5>
        <p class="text-secondary">Pilih tanggal sewa dan lakukan pembayaran dengan mudah</p>
      </div>
      <div class="col-md-4">
        <div class="step-icon">3</div>
        <h5 class="fw-bold">Ambil Mobil</h5>
        <p class="text-secondary">Ambil mobil di lokasi kami dan nikmati perjalanan Anda</p>
      </div>
    </div>
  </div>
</section>

<!-- MENGAPA PILIH RENTALKU -->
<section class="container py-5">
  <div class="text-center mb-5">
    <h2 class="fw-bold">Mengapa Pilih RentalKu?</h2>
  </div>
  <div class="row g-4">
    <div class="col-md-4 text-center">
      <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
      <h5 class="fw-bold">Aman & Terpercaya</h5>
      <p class="text-secondary">Mobil terawat dengan asuransi lengkap</p>
    </div>
    <div class="col-md-4 text-center">
      <div class="feature-icon"><i class="bi bi-lightning-charge"></i></div>
      <h5 class="fw-bold">Proses Cepat</h5>
      <p class="text-secondary">Booking online dalam hitungan menit</p>
    </div>
    <div class="col-md-4 text-center">
      <div class="feature-icon"><i class="bi bi-tag"></i></div>
      <h5 class="fw-bold">Harga Terbaik</h5>
      <p class="text-secondary">Harga kompetitif dengan kualitas premium</p>
    </div>
  </div>
</section>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'assets/includes/footer.php'; ?>
</body>
</html>
