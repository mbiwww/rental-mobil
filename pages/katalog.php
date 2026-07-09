<?php

session_start();

require_once '../config/database.php';
require_once '../classes/Database.php';
require_once '../classes/Car.php';
require_once '../classes/CarType.php';

$pdo       = Database::getInstance()->getConnection();
$carModel  = new Car($pdo);
$typeModel = new CarType($pdo);

// ── Baca parameter filter dropdown (server-side) ─────────────────────────────
// Catatan: 'search' dibaca untuk sticky input, tapi TIDAK dikirim ke query DB
$filterSearch       = trim($_GET['search'] ?? '');
$filterTypeId       = isset($_GET['type_id']) && $_GET['type_id'] !== '' ? (int)$_GET['type_id'] : null;
$filterTransmission = in_array($_GET['transmission'] ?? '', ['manual', 'automatic'])
  ? $_GET['transmission'] : '';
$filterStatus       = in_array($_GET['status'] ?? '', ['available', 'rented', 'maintenance'])
  ? $_GET['status'] : '';

// ── Ambil semua kategori mobil untuk dropdown ────────────────────────────────
$carTypes = $typeModel->getAll();

// ── Query DB — hanya filter dropdown, text search ditangani client-side ──────
$cars = $carModel->getFiltered('', $filterTypeId, $filterTransmission, $filterStatus);

// ── Helper: label & CSS class untuk badge status ─────────────────────────────
function getStatusBadge(string $status): array
{
  return match ($status) {
    'available'   => ['label' => 'Tersedia',    'class' => 'status-tersedia'],
    'rented'      => ['label' => 'Disewa',       'class' => 'status-disewa'],
    'maintenance' => ['label' => 'Maintenance',  'class' => 'status-maintenance'],
    default       => ['label' => ucfirst($status), 'class' => ''],
  };
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RentalQu &middot; Katalog Mobil</title>
  <meta name="description" content="Temukan mobil impian Anda dari koleksi RentalQu. Filter berdasarkan tipe, transmisi, dan status ketersediaan.">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/katalog.css">
  <link rel="stylesheet" href="../assets/css/navbar.css">
  <link rel="stylesheet" href="../assets/css/footer.css">
  <link rel="stylesheet" href="../assets/css/flash.css">
</head>

<body>
  <?php include '../includes/navbar.php'; ?>
  <?php include '../includes/flash.php'; ?>

  <!-- Header -->
  <section class="page-header">
    <div class="container">
      <h1>Katalog Mobil</h1>
      <p class="mb-0">Temukan mobil impian Anda dari koleksi kami</p>
    </div>
  </section>

  <div class="container py-2">
    <!-- Filter Section — dikirim via GET ke halaman ini sendiri -->
    <form method="GET" action="katalog.php" id="filterForm">
      <div class="filter-card">
        <div class="row g-3 align-items-end">

          <!-- Input Pencarian -->
          <div class="col-12 col-md-4 col-lg-3">
            <label class="form-label fw-semibold small text-uppercase text-secondary" for="searchInput">Cari Mobil</label>
            <div class="input-group">
              <span class="input-group-text bg-transparent pe-2" style="border-radius:16px 0 0 16px; border:1.5px solid #e2e8f0; border-right:none;">
                <i class="bi bi-search text-secondary"></i>
              </span>
              <input
                type="text"
                class="form-control ps-2"
                id="searchInput"
                name="search"
                placeholder="Nama mobil..."
                value="<?= htmlspecialchars($filterSearch) ?>"
                style="border-left:none;">
            </div>
          </div>

          <!-- Dropdown Tipe Mobil (dinamis dari DB) -->
          <div class="col-6 col-md col-lg">
            <label class="form-label fw-semibold small text-uppercase text-secondary" for="filterType">Tipe Mobil</label>
            <select class="form-select" id="filterType" name="type_id">
              <option value="">Semua Tipe</option>
              <?php foreach ($carTypes as $type): ?>
                <option
                  value="<?= (int)$type['id'] ?>"
                  <?= $filterTypeId === (int)$type['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($type['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Dropdown Transmisi -->
          <div class="col-6 col-md col-lg">
            <label class="form-label fw-semibold small text-uppercase text-secondary" for="filterTransmisi">Transmisi</label>
            <select class="form-select" id="filterTransmisi" name="transmission">
              <option value="">Semua Transmisi</option>
              <option value="automatic" <?= $filterTransmission === 'automatic' ? 'selected' : '' ?>>Automatic</option>
              <option value="manual" <?= $filterTransmission === 'manual'    ? 'selected' : '' ?>>Manual</option>
            </select>
          </div>

          <!-- Dropdown Status -->
          <div class="col-6 col-md col-lg">
            <label class="form-label fw-semibold small text-uppercase text-secondary" for="filterStatus">Status</label>
            <select class="form-select" id="filterStatus" name="status">
              <option value="">Semua Status</option>
              <option value="available" <?= $filterStatus === 'available'   ? 'selected' : '' ?>>Tersedia</option>
              <option value="rented" <?= $filterStatus === 'rented'      ? 'selected' : '' ?>>Disewa</option>
              <option value="maintenance" <?= $filterStatus === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
            </select>
          </div>

          <!-- Tombol Cari & Reset -->
          <div class="col-6 col-md-auto">
            <button type="submit" class="btn btn-primary w-100" id="searchBtn">
              <i class="bi bi-search me-1"></i>Cari
            </button>
          </div>
          <?php if ($filterTypeId || $filterTransmission || $filterStatus): ?>
            <div class="col-6 col-md-auto">
              <a href="katalog.php" class="btn btn-outline-secondary w-100">
                <i class="bi bi-x-circle me-1"></i>Reset
              </a>
            </div>
          <?php endif; ?>

        </div>
      </div>
    </form>

    <!-- Info jumlah hasil -->
    <div class="d-flex align-items-center justify-content-between mb-3 px-1">
      <span class="text-secondary small">
        Menampilkan <strong id="resultCount"><?= count($cars) ?></strong> mobil
        <?php if ($filterTypeId || $filterTransmission || $filterStatus): ?>
          <span class="badge bg-primary ms-1">Filter aktif</span>
        <?php endif; ?>
      </span>
    </div>

    <!-- Daftar Mobil -->
    <div class="row g-4" id="carList">

      <?php if (empty($cars)): ?>
        <!-- State kosong dari server (filter dropdown tidak ada hasil) -->
        <div class="col-12 text-center py-5" id="emptyState">
          <i class="bi bi-search fs-1 text-secondary"></i>
          <p class="text-secondary mt-3 mb-1">Tidak ada mobil yang sesuai dengan filter Anda.</p>
          <a href="katalog.php" class="btn btn-outline-primary btn-sm mt-2">
            <i class="bi bi-arrow-counterclockwise me-1"></i>Tampilkan Semua
          </a>
        </div>

      <?php else: ?>

        <?php foreach ($cars as $car): ?>
          <?php
          $badge          = getStatusBadge($car['status']);
          $namaLengkap    = htmlspecialchars($car['brand'] . ' ' . $car['model'] . ' ' . $car['year']);
          $typeName       = htmlspecialchars($car['type_name'] ?? '-');
          $transmisiLabel = $car['transmission'] === 'automatic' ? 'Automatic' : 'Manual';
          $harga          = 'Rp ' . number_format((float)$car['price_per_day'], 0, ',', '.');
          $imgSrc         = !empty($car['image'])
            ? '../assets/uploads/cars/' . htmlspecialchars($car['image'])
            : 'https://placehold.co/600x360/1e2a3a/7eb3f5?text=' . urlencode($car['brand'] . '+' . $car['model']);

          // Mobil berstatus selain 'available' tidak bisa diklik ke detail
          $isAvailable = $car['status'] === 'available';
          $linkHref    = $isAvailable ? 'detail.php?id=' . (int)$car['id'] : '#';
          ?>
          <div class="col-md-6 col-lg-4 car-item"
            data-type="<?= strtolower($typeName) ?>"
            data-transmisi="<?= $car['transmission'] ?>"
            data-status="<?= $car['status'] ?>"
            data-name="<?= strtolower($car['brand'] . ' ' . $car['model'] . ' ' . $car['year']) ?>">
            <div class="car-card p-3 <?= !$isAvailable ? 'car-unavailable' : '' ?>">
              <a href="<?= $linkHref ?>" <?= !$isAvailable ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                <div class="car-img-placeholder rounded-4 mb-3">
                  <img src="<?= $imgSrc ?>" class="custom-image" alt="<?= $namaLengkap ?>">
                  <?php if (!empty($car['type_name'])): ?>
                    <span class="car-type-badge"><?= $typeName ?></span>
                  <?php endif; ?>
                </div>
                <h5 class="fw-bold"><?= $namaLengkap ?></h5>
                <div class="car-detail-item">
                  <i class="bi bi-gear-fill"></i> <strong>Transmisi:</strong> <?= $transmisiLabel ?>
                </div>
                <div class="car-detail-item">
                  <i class="bi bi-people-fill"></i> <strong>Kursi:</strong> <?= (int)$car['passenger_capacity'] ?> Penumpang
                </div>
                <div class="car-detail-item">
                  <i class="bi bi-briefcase-fill"></i> <strong>Bagasi:</strong> <?= (int)$car['luggage_capacity'] ?> Koper
                </div>
                <div class="car-detail-item">
                  <i class="bi bi-calendar3"></i> <strong>Tahun:</strong> <?= (int)$car['year'] ?>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                  <span class="price"><?= $harga ?> <small class="text-secondary fw-normal">/hari</small></span>
                </div>
                <span class="status-badge <?= $badge['class'] ?> mt-2 d-inline-block">
                  <?= $badge['label'] ?>
                </span>
              </a>
            </div>
          </div>
        <?php endforeach; ?>

        <!-- State kosong client-side: tampil via JS saat pencarian teks tidak ada hasil -->
        <div class="col-12 text-center py-5" id="emptyState" style="display:none;">
          <i class="bi bi-search fs-1 text-secondary"></i>
          <p class="text-secondary mt-3 mb-1">Tidak ada mobil dengan nama "<span id="emptyKeyword"></span>".</p>
          <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="document.getElementById('searchInput').value=''; document.getElementById('searchInput').dispatchEvent(new Event('input'));">
            <i class="bi bi-arrow-counterclockwise me-1"></i>Hapus Pencarian
          </button>
        </div>

      <?php endif; ?>

    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function() {
      const form = document.getElementById('filterForm');
      const searchInput = document.getElementById('searchInput');
      const carItems = document.querySelectorAll('.car-item');
      const emptyState = document.getElementById('emptyState');

      // Filter kartu secara client-side berdasarkan data-name (brand + model + year)
      function applySearch() {
        const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const emptyKw = document.getElementById('emptyKeyword');
        const resultCount = document.getElementById('resultCount');
        let visible = 0;

        carItems.forEach(function(item) {
          const name = item.getAttribute('data-name') || '';
          const matched = name.includes(keyword);
          item.style.display = matched ? '' : 'none';
          if (matched) visible++;
        });

        // Update angka jumlah hasil secara real-time
        if (resultCount) resultCount.textContent = visible;

        // Tampilkan / sembunyikan state kosong client-side
        if (emptyState) {
          emptyState.style.display = visible === 0 ? '' : 'none';
          if (emptyKw) emptyKw.textContent = searchInput ? searchInput.value.trim() : '';
        }
      }

      // Jalankan filter teks setiap kali user mengetik (real-time, tanpa round-trip)
      if (searchInput) {
        searchInput.addEventListener('input', applySearch);
        applySearch(); // terapkan nilai awal jika ada (misalnya dari URL)
      }

      // Auto-submit langsung saat dropdown berubah (server-side filter)
      ['filterType', 'filterTransmisi', 'filterStatus'].forEach(function(id) {
        const el = document.getElementById(id);
        if (el) {
          el.addEventListener('change', function() {
            form.submit();
          });
        }
      });
    })();
  </script>

  <?php include '../includes/footer.php'; ?>
</body>

</html>