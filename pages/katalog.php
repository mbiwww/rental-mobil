<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RentalKu · Katalog Mobil</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/katalog.css">
  <link rel="stylesheet" href="../assets/css/navbar.css">
  <link rel="stylesheet" href="../assets/css/footer.css">
</head>
<body>
  <?php
  include '../assets/includes/navbar.php';
  ?>
<!-- Header -->
<section class="page-header">
  <div class="container">
    <h1>Katalog Mobil</h1>
    <p class="mb-0">Temukan mobil impian Anda dari koleksi kami</p>
  </div>
</section>

<div class="container py-2">
  <!-- Filter Section dengan pencarian kiri, dropdown auto-fit -->
  <div class="filter-card">
    <div class="row g-3 align-items-end">
      <!-- Input Pencarian -->
      <div class="col-12 col-md-4 col-lg-3">
        <label class="form-label fw-semibold small text-uppercase text-secondary">Cari Mobil</label>
        <div class="input-group">
          <span class="input-group-text bg-transparent pe-2" style="border-radius:16px 0 0 16px; border:1.5px solid #e2e8f0; border-right:none;">
            <i class="bi bi-search text-secondary"></i>
          </span>
          <input type="text" class="form-control ps-2" id="searchInput" placeholder="Nama mobil..." style="border-left:none;">
        </div>
      </div>
      <!-- Tiga dropdown lain menggunakan col (auto width) -->
      <div class="col-6 col-md col-lg">
        <label class="form-label fw-semibold small text-uppercase text-secondary">Tipe Mobil</label>
        <select class="form-select" id="filterType">
          <option value="semua">Semua Tipe</option>
          <option value="suv">SUV</option>
          <option value="sedan">Sedan</option>
          <option value="hatchback">Hatchback</option>
          <option value="mpv">MPV</option>
        </select>
      </div>
      <div class="col-6 col-md col-lg">
        <label class="form-label fw-semibold small text-uppercase text-secondary">Transmisi</label>
        <select class="form-select" id="filterTransmisi">
          <option value="semua">Semua Transmisi</option>
          <option value="automatic">Automatic</option>
          <option value="manual">Manual</option>
        </select>
      </div>
      <div class="col-6 col-md col-lg">
        <label class="form-label fw-semibold small text-uppercase text-secondary">Status</label>
        <select class="form-select" id="filterStatus">
          <option value="semua">Semua Status</option>
          <option value="tersedia">Tersedia</option>
          <option value="disewa">Disewa</option>
          <option value="maintenance">Maintenance</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Daftar Mobil -->
  <div class="row g-4" id="carList">
    <!-- Mobil 1: Toyota Fortuner 2023 (SUV, Automatic, Tersedia) -->
    <div class="col-md-6 col-lg-4 car-item" data-type="suv" data-transmisi="automatic" data-status="tersedia" data-name="Toyota Fortuner 2023">
      <div class="car-card p-3">
        <div class="car-img-placeholder rounded-4 mb-3">
          <i class="bi bi-truck-front"></i>
          <span class="car-type-badge">SUV</span>
        </div>
        <h5 class="fw-bold">Toyota Fortuner 2023</h5>
        <div class="car-detail-item"><i class="bi bi-gear-fill"></i> <strong>Transmisi:</strong> Automatic</div>
        <div class="car-detail-item"><i class="bi bi-people-fill"></i> <strong>Kursi:</strong> 7 Penumpang</div>
        <div class="car-detail-item"><i class="bi bi-briefcase-fill"></i> <strong>Bagasi:</strong> 3 Koper</div>
        <div class="car-detail-item"><i class="bi bi-calendar3"></i> <strong>Tahun:</strong> 2023</div>
        <div class="d-flex justify-content-between align-items-center mt-3">
          <span class="price">Rp 500.000 <small class="text-secondary fw-normal">/hari</small></span>
          <a href="detail.php"><button class="btn btn-sewa btn-sm">Sewa</button></a>
        </div>
        <span class="status-badge status-tersedia mt-2 d-inline-block">Tersedia</span>
      </div>
    </div>

    <!-- Mobil 2: Honda Civic 2024 (Sedan, Automatic, Disewa) -->
    <div class="col-md-6 col-lg-4 car-item" data-type="sedan" data-transmisi="automatic" data-status="disewa" data-name="Honda Civic 2024">
      <div class="car-card p-3">
        <div class="car-img-placeholder rounded-4 mb-3">
          <i class="bi bi-car-front-fill"></i>
          <span class="car-type-badge">Sedan</span>
        </div>
        <h5 class="fw-bold">Honda Civic 2024</h5>
        <div class="car-detail-item"><i class="bi bi-gear-fill"></i> <strong>Transmisi:</strong> Automatic</div>
        <div class="car-detail-item"><i class="bi bi-people-fill"></i> <strong>Kursi:</strong> 5 Penumpang</div>
        <div class="car-detail-item"><i class="bi bi-briefcase-fill"></i> <strong>Bagasi:</strong> 2 Koper</div>
        <div class="car-detail-item"><i class="bi bi-calendar3"></i> <strong>Tahun:</strong> 2024</div>
        <div class="d-flex justify-content-between align-items-center mt-3">
          <span class="price">Rp 400.000 <small class="text-secondary fw-normal">/hari</small></span>
          <button class="btn btn-sewa btn-sm" disabled>Sewa</button>
        </div>
        <span class="status-badge status-disewa mt-2 d-inline-block">Disewa</span>
      </div>
    </div>

    <!-- Mobil 3: Toyota Avanza 2023 (MPV, Manual, Tersedia) -->
    <div class="col-md-6 col-lg-4 car-item" data-type="mpv" data-transmisi="manual" data-status="tersedia" data-name="Toyota Avanza 2023">
      <div class="car-card p-3">
        <div class="car-img-placeholder rounded-4 mb-3">
          <i class="bi bi-van"></i>
          <span class="car-type-badge">MPV</span>
        </div>
        <h5 class="fw-bold">Toyota Avanza 2023</h5>
        <div class="car-detail-item"><i class="bi bi-gear-fill"></i> <strong>Transmisi:</strong> Manual</div>
        <div class="car-detail-item"><i class="bi bi-people-fill"></i> <strong>Kursi:</strong> 7 Penumpang</div>
        <div class="car-detail-item"><i class="bi bi-briefcase-fill"></i> <strong>Bagasi:</strong> 2 Koper</div>
        <div class="car-detail-item"><i class="bi bi-calendar3"></i> <strong>Tahun:</strong> 2023</div>
        <div class="d-flex justify-content-between align-items-center mt-3">
          <span class="price">Rp 300.000 <small class="text-secondary fw-normal">/hari</small></span>
          <button class="btn btn-sewa btn-sm">Sewa</button>
        </div>
        <span class="status-badge status-tersedia mt-2 d-inline-block">Tersedia</span>
      </div>
    </div>

    <!-- Mobil 4: Honda Brio 2024 (Hatchback, Automatic, Maintenance) -->
    <div class="col-md-6 col-lg-4 car-item" data-type="hatchback" data-transmisi="automatic" data-status="maintenance" data-name="Honda Brio 2024">
      <div class="car-card p-3">
        <div class="car-img-placeholder rounded-4 mb-3">
          <i class="bi bi-car-front"></i>
          <span class="car-type-badge">Hatchback</span>
        </div>
        <h5 class="fw-bold">Honda Brio 2024</h5>
        <div class="car-detail-item"><i class="bi bi-gear-fill"></i> <strong>Transmisi:</strong> Automatic</div>
        <div class="car-detail-item"><i class="bi bi-people-fill"></i> <strong>Kursi:</strong> 5 Penumpang</div>
        <div class="car-detail-item"><i class="bi bi-briefcase-fill"></i> <strong>Bagasi:</strong> 1 Koper</div>
        <div class="car-detail-item"><i class="bi bi-calendar3"></i> <strong>Tahun:</strong> 2024</div>
        <div class="d-flex justify-content-between align-items-center mt-3">
          <span class="price">Rp 250.000 <small class="text-secondary fw-normal">/hari</small></span>
          <button class="btn btn-sewa btn-sm" disabled>Sewa</button>
        </div>
        <span class="status-badge status-maintenance mt-2 d-inline-block">Maintenance</span>
      </div>
    </div>

    <!-- Mobil 5: Mitsubishi Pajero Sport 2023 (SUV, Automatic, Tersedia) -->
    <div class="col-md-6 col-lg-4 car-item" data-type="suv" data-transmisi="automatic" data-status="tersedia" data-name="Mitsubishi Pajero Sport">
      <div class="car-card p-3">
        <div class="car-img-placeholder rounded-4 mb-3">
          <i class="bi bi-truck"></i>
          <span class="car-type-badge">SUV</span>
        </div>
        <h5 class="fw-bold">Mitsubishi Pajero Sport</h5>
        <div class="car-detail-item"><i class="bi bi-gear-fill"></i> <strong>Transmisi:</strong> Automatic</div>
        <div class="car-detail-item"><i class="bi bi-people-fill"></i> <strong>Kursi:</strong> 7 Penumpang</div>
        <div class="car-detail-item"><i class="bi bi-briefcase-fill"></i> <strong>Bagasi:</strong> 3 Koper</div>
        <div class="car-detail-item"><i class="bi bi-calendar3"></i> <strong>Tahun:</strong> 2023</div>
        <div class="d-flex justify-content-between align-items-center mt-3">
          <span class="price">Rp 550.000 <small class="text-secondary fw-normal">/hari</small></span>
          <button class="btn btn-sewa btn-sm">Sewa</button>
        </div>
        <span class="status-badge status-tersedia mt-2 d-inline-block">Tersedia</span>
      </div>
    </div>

    <!-- Mobil 6: Toyota Camry 2024 (Sedan, Automatic, Disewa) -->
    <div class="col-md-6 col-lg-4 car-item" data-type="sedan" data-transmisi="automatic" data-status="disewa" data-name="Toyota Camry 2024">
      <div class="car-card p-3">
        <div class="car-img-placeholder rounded-4 mb-3">
          <i class="bi bi-car-front-fill"></i>
          <span class="car-type-badge">Sedan</span>
        </div>
        <h5 class="fw-bold">Toyota Camry 2024</h5>
        <div class="car-detail-item"><i class="bi bi-gear-fill"></i> <strong>Transmisi:</strong> Automatic</div>
        <div class="car-detail-item"><i class="bi bi-people-fill"></i> <strong>Kursi:</strong> 5 Penumpang</div>
        <div class="car-detail-item"><i class="bi bi-briefcase-fill"></i> <strong>Bagasi:</strong> 2 Koper</div>
        <div class="car-detail-item"><i class="bi bi-calendar3"></i> <strong>Tahun:</strong> 2024</div>
        <div class="d-flex justify-content-between align-items-center mt-3">
          <span class="price">Rp 600.000 <small class="text-secondary fw-normal">/hari</small></span>
          <button class="btn btn-sewa btn-sm" disabled>Sewa</button>
        </div>
        <span class="status-badge status-disewa mt-2 d-inline-block">Disewa</span>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  (function() {
    const searchInput = document.getElementById('searchInput');
    const filterType = document.getElementById('filterType');
    const filterTransmisi = document.getElementById('filterTransmisi');
    const filterStatus = document.getElementById('filterStatus');
    const carItems = document.querySelectorAll('.car-item');

    function applyFilters() {
      const searchText = searchInput.value.toLowerCase().trim();
      const typeVal = filterType.value;
      const transmisiVal = filterTransmisi.value;
      const statusVal = filterStatus.value;

      carItems.forEach(item => {
        const itemName = item.getAttribute('data-name').toLowerCase();
        const itemType = item.getAttribute('data-type');
        const itemTransmisi = item.getAttribute('data-transmisi');
        const itemStatus = item.getAttribute('data-status');

        const matchSearch = itemName.includes(searchText);
        const matchType = typeVal === 'semua' || itemType === typeVal;
        const matchTransmisi = transmisiVal === 'semua' || itemTransmisi === transmisiVal;
        const matchStatus = statusVal === 'semua' || itemStatus === statusVal;

        if (matchSearch && matchType && matchTransmisi && matchStatus) {
          item.style.display = 'block';
        } else {
          item.style.display = 'none';
        }
      });
    }

    searchInput.addEventListener('input', applyFilters);
    filterType.addEventListener('change', applyFilters);
    filterTransmisi.addEventListener('change', applyFilters);
    filterStatus.addEventListener('change', applyFilters);

    // Initial apply
    applyFilters();
  })();
</script>
<?php include '../assets/includes/footer.php'; ?>
</body>
</html>