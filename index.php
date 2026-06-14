<?php

session_start();

// Muat konfigurasi database & class yang dibutuhkan
require_once 'config/database.php';
require_once 'classes/Database.php';
require_once 'classes/Car.php';
require_once 'classes/CarType.php';

// Buat koneksi melalui Singleton Database
$pdo       = Database::getInstance()->getConnection();
$carModel  = new Car($pdo);
$typeModel = new CarType($pdo);

// Ambil 3 mobil unggulan (status: available, urut terbaru)
$featuredCars = $carModel->getFeatured(3);

// Ambil semua kategori mobil untuk dropdown
$carTypes = $typeModel->getAll();

// Total mobil tersedia (untuk badge di tombol "Lihat Semua")
$totalAvailable = $carModel->countByStatus('available');
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RentalKu &middot; Home</title>
  <meta name="description" content="RentalKu - Sewa mobil berkualitas dengan harga terjangkau. Proses cepat, aman, dan terpercaya. Tersedia berbagai pilihan mobil SUV, Sedan, MPV, Hatchback, dan City Car.">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/index.css">
  <link rel="stylesheet" href="assets/css/navbar.css">
  <link rel="stylesheet" href="assets/css/footer.css">
  <link rel="stylesheet" href="assets/css/flash.css">
</head>

<body>
  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/flash.php'; ?>

  <section class="hero-section">
    <div class="container hero-content">
      <div class="row align-items-center g-4">

        <div class="col-lg-6">
          <h1 class="hero-title">Sewa Mobil Impian Anda dengan Mudah</h1>
          <p class="hero-desc mt-3">Ratusan pilihan mobil berkualitas dengan harga terjangkau. Proses cepat, aman, dan terpercaya.</p>
          <div class="search-card mt-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-funnel-fill me-2"></i>Jenis Mobil</h5>
            <!-- Form search: GET ke halaman katalog dengan parameter type_id -->
            <form action="pages/katalog.php" method="GET">
              <div class="mb-3">
                <select class="form-select" id="carCategory" name="type_id">
                  <option value="">Semua Tipe</option>
                  <?php foreach ($carTypes as $type): ?>
                    <option value="<?= (int)$type['id'] ?>">
                      <?= htmlspecialchars($type['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <button type="submit" class="btn btn-search w-100" id="searchBtn">
                <i class="bi bi-search me-2"></i>Cari Mobil
              </button>
            </form>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="d-none d-lg-flex">
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

    <?php if (empty($featuredCars)): ?>
      <!-- State kosong: tampilkan pesan jika belum ada mobil tersedia -->
      <div class="text-center py-5">
        <i class="bi bi-car-front fs-1 text-secondary"></i>
        <p class="text-secondary mt-3">Belum ada mobil yang tersedia saat ini.</p>
        <a href="pages/katalog.php" class="btn btn-seeall mt-2">Lihat Katalog Lengkap</a>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($featuredCars as $car): ?>
          <?php
          // Tentukan path gambar: gunakan file upload jika ada, fallback ke placeholder
          $imgSrc = !empty($car['image'])
            ? 'assets/uploads/cars/' . htmlspecialchars($car['image'])
            : 'https://placehold.co/600x360/1e2a3a/7eb3f5?text=' . urlencode($car['brand'] . ' ' . $car['model']);

          // Format harga ke format Rupiah
          $hargaFormat = 'Rp ' . number_format((float)$car['price_per_day'], 0, ',', '.');

          // Label transmisi yang ramah pengguna
          $transmisiLabel = $car['transmission'] === 'automatic' ? 'Automatic' : 'Manual';
          ?>
          <div class="col-md-4">
            <div class="car-card p-3">
              <a href="pages/detail.php?id=<?= (int)$car['id'] ?>">
                <div class="car-img-placeholder rounded-4 mb-3">
                  <img
                    src="<?= $imgSrc ?>"
                    class="custom-image"
                    alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>">
                </div>
                <h5 class="fw-bold">
                  <?= htmlspecialchars($car['brand'] . ' ' . $car['model'] . ' ' . $car['year']) ?>
                </h5>
                <?php if (!empty($car['type_name'])): ?>
                  <div class="car-detail-item">
                    <i class="bi bi-tag-fill"></i>
                    <strong>Tipe:</strong> <?= htmlspecialchars($car['type_name']) ?>
                  </div>
                <?php endif; ?>
                <div class="car-detail-item">
                  <i class="bi bi-gear-fill"></i>
                  <strong>Transmisi:</strong> <?= $transmisiLabel ?>
                </div>
                <div class="car-detail-item">
                  <i class="bi bi-people-fill"></i>
                  <strong>Kursi:</strong> <?= (int)$car['passenger_capacity'] ?> Penumpang
                </div>
                <div class="car-detail-item">
                  <i class="bi bi-briefcase-fill"></i>
                  <strong>Bagasi:</strong> <?= (int)$car['luggage_capacity'] ?> Koper
                </div>
                <div class="car-detail-item">
                  <i class="bi bi-calendar3"></i>
                  <strong>Tahun:</strong> <?= (int)$car['year'] ?>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                  <span class="price">
                    <?= $hargaFormat ?> <small class="text-secondary fw-normal">/hari</small>
                  </span>
                </div>
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="text-center mt-5">
        <a href="pages/katalog.php" class="btn btn-seeall btn-lg px-5">
          <i class="bi bi-grid-3x3-gap-fill me-2"></i>Lihat Semua Mobil
        </a>
      </div>
    <?php endif; ?>
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
          <h5 class="fw-bold">Booking &amp; Bayar</h5>
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
        <h5 class="fw-bold">Aman &amp; Terpercaya</h5>
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

  <?php include 'includes/footer.php'; ?>
</body>

</html>