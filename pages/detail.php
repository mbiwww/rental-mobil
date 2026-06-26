<?php

/**
 * pages/detail.php — Halaman detail mobil & form booking
 *
 * Back-end:
 * - Ambil ID mobil dari query string (?id=X)
 * - Validasi: redirect ke katalog jika ID tidak ada / mobil tidak ditemukan
 * - Inject data nyata ke seluruh tampilan dan form booking
 * - Form booking POST ke handlers/booking_handler.php?action=create_booking
 */

session_start();

require_once '../config/database.php';
require_once '../classes/Database.php';
require_once '../classes/BaseModel.php';
require_once '../classes/Car.php';

$pdo      = Database::getInstance()->getConnection();
$carModel = new Car($pdo);

// Ambil setting dari database
$stmtSettings = $pdo->query("SELECT key_name, value FROM settings");
$settings = $stmtSettings->fetchAll(PDO::FETCH_KEY_PAIR);
$driverFee = isset($settings['driver_cost_per_day']) ? (float)$settings['driver_cost_per_day'] : 200000.0;
$pickupFee = isset($settings['pickup_fee']) ? (float)$settings['pickup_fee'] : 50000.0;
$dropoffFee = isset($settings['dropoff_fee']) ? (float)$settings['dropoff_fee'] : 50000.0;

// ── Baca & validasi ID dari query string ─────────────────────────────────────
$carId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($carId <= 0) {
  header('Location: katalog.php');
  exit;
}

// ── Ambil data mobil dari database ───────────────────────────────────────────
$car = $carModel->getById($carId);

if (!$car) {
  // Mobil tidak ditemukan → kembali ke katalog
  header('Location: katalog.php');
  exit;
}

// ── Siapkan nilai turunan untuk tampilan ─────────────────────────────────────
$namaLengkap    = htmlspecialchars($car['brand'] . ' ' . $car['model'] . ' ' . $car['year']);
$typeName       = htmlspecialchars($car['type_name'] ?? '-');
$transmisiLabel = $car['transmission'] === 'automatic' ? 'Automatic' : 'Manual';
$pricePerDay    = (float) $car['price_per_day'];
$priceFormatted = number_format($pricePerDay, 0, ',', '.');

// Badge status
$statusConfig = match ($car['status']) {
  'available'   => ['label' => 'Tersedia',    'class' => 'badge-available'],
  'rented'      => ['label' => 'Sedang Disewa', 'class' => 'badge-rented'],
  'maintenance' => ['label' => 'Maintenance',  'class' => 'badge-maintenance'],
  default       => ['label' => ucfirst($car['status']), 'class' => ''],
};

// Gambar: pakai upload jika ada, fallback ke placeholder
$imgSrc = !empty($car['image'])
  ? '../assets/uploads/cars/' . htmlspecialchars($car['image'])
  : 'https://placehold.co/900x500/1e2a3a/7eb3f5?text=' . urlencode($car['brand'] . '+' . $car['model']);

// Apakah user sudah login sebagai customer?
$isLoggedIn = isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'customer';
$isAvailable = $car['status'] === 'available';
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RentalKu &middot; <?= $namaLengkap ?></title>
  <meta name="description" content="Detail dan booking <?= $namaLengkap ?> di RentalKu. Sewa mulai Rp <?= $priceFormatted ?>/hari.">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/detail.css">
  <link rel="stylesheet" href="../assets/css/navbar.css">
  <link rel="stylesheet" href="../assets/css/footer.css">
  <link rel="stylesheet" href="../assets/css/flash.css">
</head>

<body>
  <?php include '../includes/navbar.php'; ?>
  <?php include '../includes/flash.php'; ?>

  <div class="container detail-container">
    <a href="katalog.php" class="back-link"><i class="bi bi-arrow-left"></i> Kembali ke Katalog</a>

    <!-- FOTO MOBIL -->
    <div class="car-image-container" data-bs-toggle="modal" data-bs-target="#imageModal" title="Klik untuk melihat full size">
      <img src="<?= $imgSrc ?>" alt="<?= $namaLengkap ?>" id="carImage">
    </div>

    <div class="row g-4">
      <!-- ── Kolom Kiri: Info & Spesifikasi ───────────────────────────────── -->
      <div class="col-md-7">
        <div class="card-custom">
          <h1 class="car-title"><?= $namaLengkap ?></h1>
          <span class="badge-availability <?= $statusConfig['class'] ?>"><?= $statusConfig['label'] ?></span>
          <?php if (!empty($typeName) && $typeName !== '-'): ?>
            <span class="badge-availability" style="background:rgba(99,102,241,.12);color:#6366f1;margin-left:6px;"><?= $typeName ?></span>
          <?php endif; ?>
          <hr>
          <h4 class="fw-bold mb-3">Spesifikasi</h4>
          <?php if (!empty($car['engine_type'])): ?>
            <div class="spec-item"><i class="bi bi-gear-fill"></i><span><strong>Mesin:</strong> <?= htmlspecialchars($car['engine_type']) ?></span></div>
          <?php endif; ?>
          <div class="spec-item"><i class="bi bi-people-fill"></i><span><strong>Kapasitas:</strong> <?= (int)$car['passenger_capacity'] ?> Penumpang</span></div>
          <div class="spec-item"><i class="bi bi-briefcase-fill"></i><span><strong>Bagasi:</strong> <?= (int)$car['luggage_capacity'] ?> Koper</span></div>
          <div class="spec-item"><i class="bi bi-calendar3"></i><span><strong>Tahun:</strong> <?= (int)$car['year'] ?></span></div>
          <div class="spec-item"><i class="bi bi-sliders"></i><span><strong>Transmisi:</strong> <?= $transmisiLabel ?></span></div>
        </div>

        <?php if (!empty($car['description'])): ?>
          <div class="card-custom">
            <h4 class="fw-bold mb-3">Deskripsi</h4>
            <p class="text-secondary"><?= nl2br(htmlspecialchars($car['description'])) ?></p>
          </div>
        <?php endif; ?>
      </div>

      <!-- ── Kolom Kanan: Form Booking ────────────────────────────────────── -->
      <div class="col-md-5">
        <div class="card-custom">
          <h4 class="fw-bold mb-3"><i class="bi bi-calendar-check me-2"></i>Booking Mobil</h4>

          <?php if (!$isAvailable): ?>
            <!-- Mobil tidak tersedia -->
            <div class="alert alert-warning rounded-3 d-flex align-items-center gap-2">
              <i class="bi bi-exclamation-triangle-fill fs-5"></i>
              <span>Mobil ini sedang <strong><?= $statusConfig['label'] ?></strong> dan tidak dapat dipesan.</span>
            </div>
            <a href="katalog.php" class="btn btn-outline-primary w-100">
              <i class="bi bi-grid-3x3-gap-fill me-1"></i>Lihat Mobil Lain
            </a>

          <?php elseif (!$isLoggedIn): ?>
            <!-- Belum login -->
            <div class="alert alert-info rounded-3 d-flex align-items-center gap-2">
              <i class="bi bi-info-circle-fill fs-5"></i>
              <span>Silakan <strong>login</strong> terlebih dahulu untuk melakukan booking.</span>
            </div>
            <a href="login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-rental w-100">
              <i class="bi bi-box-arrow-in-right me-1"></i>Masuk untuk Booking
            </a>

          <?php else: ?>
            <!-- Form Booking — hanya tampil jika available & sudah login -->
            <form id="bookingForm" method="POST" action="../handlers/booking_handler.php?action=create_booking">
              <!-- Hidden: data yang diteruskan ke handler -->
              <input type="hidden" name="car_id" value="<?= (int)$car['id'] ?>">
              <!-- Harga per hari (dikirim untuk konfirmasi; validasi harga tetap dilakukan server-side) -->
              <input type="hidden" name="price_per_day" value="<?= $pricePerDay ?>">

              <!-- Tanggal Mulai -->
              <div class="mb-3">
                <label for="startDate" class="form-label">Tanggal Mulai</label>
                <input type="date" class="form-control" id="startDate" name="start_date" required>
              </div>

              <!-- Tanggal Selesai -->
              <div class="mb-3">
                <label for="endDate" class="form-label">Tanggal Selesai</label>
                <input type="date" class="form-control" id="endDate" name="end_date" required>
              </div>

              <!-- Opsi Supir -->
              <div class="mb-3">
                <label for="driverOption" class="form-label">Opsi Supir</label>
                <select class="form-select" id="driverOption" name="rental_type">
                  <option value="without_driver">Tanpa Supir</option>
                  <option value="with_driver">Dengan Supir (+Rp <?= number_format($driverFee, 0, ',', '.') ?>/hari)</option>
                </select>
              </div>

              <!-- Pickup & Dropoff (dinamis lewat JS) -->
              <div id="pickupDropoffSection" class="mb-3"></div>

              <!-- Harga per hari (display saja) -->
              <div class="mb-3">
                <label class="form-label">Harga sewa mobil per hari</label>
                <div class="input-group">
                  <span class="input-group-text bg-white" style="border-radius:16px 0 0 16px;">Rp</span>
                  <input type="text" class="form-control text-end" readonly id="pricePerDayDisplay"
                    value="<?= $priceFormatted ?>"
                    style="border-left:none; border-radius:0 16px 16px 0;">
                </div>
                <div class="price-detail" id="priceDetail"></div>
              </div>

              <!-- Durasi sewa -->
              <div class="mb-3">
                <label class="form-label">Durasi sewa</label>
                <input type="text" class="form-control text-center" id="duration" readonly placeholder="Pilih tanggal">
              </div>

              <!-- Total harga -->
              <div class="mb-3">
                <label class="form-label">Total</label>
                <div class="input-group">
                  <span class="input-group-text bg-white" style="border-radius:16px 0 0 16px;">Rp</span>
                  <input type="text" class="form-control text-end total-display" readonly id="totalPriceDisplay"
                    value="<?= $priceFormatted ?>"
                    style="border-left:none; border-radius:0 16px 16px 0;">
                </div>
                <!-- Hidden input total untuk dikirim ke server -->
                <input type="hidden" name="total_price" id="totalPriceHidden" value="<?= $pricePerDay ?>">
                <input type="hidden" name="driver_cost" id="driverCostHidden" value="0">
              </div>

              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                <label class="form-check-label small text-secondary" for="agreeTerms">
                  Saya setuju dengan <a href="#" target="_blank">syarat dan ketentuan</a> yang berlaku
                </label>
              </div>

              <button type="submit" class="btn btn-rental w-100" id="submitBooking" disabled>
                <i class="bi bi-bag-fill"></i> Pesan Sekarang
              </button>
            </form>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </div>

  <!-- MODAL FULL IMAGE -->
  <div class="modal fade modal-full-image" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content bg-transparent border-0">
        <button type="button" class="btn-close-light" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
        <div class="modal-body text-center">
          <img src="" alt="Full size" id="fullImage">
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function() {
      // ── Konstanta harga dari PHP ─────────────────────────────────────────────
      const basePrice = <?= $pricePerDay ?>;
      const driverFee = <?= $driverFee ?>;
      const pickupFee = <?= $pickupFee ?>;
      const dropoffFee = <?= $dropoffFee ?>;

      // ── Referensi elemen form ────────────────────────────────────────────────
      const startDateInput = document.getElementById('startDate');
      const endDateInput = document.getElementById('endDate');
      const driverOption = document.getElementById('driverOption');
      const pickupDropoffSection = document.getElementById('pickupDropoffSection');
      const pricePerDayDisplay = document.getElementById('pricePerDayDisplay');
      const durationInput = document.getElementById('duration');
      const totalDisplay = document.getElementById('totalPriceDisplay');
      const totalHidden = document.getElementById('totalPriceHidden');
      const driverCostHidden = document.getElementById('driverCostHidden');
      const priceDetail = document.getElementById('priceDetail');

      // Jika tidak ada form booking (belum login / tidak tersedia), hentikan
      if (!startDateInput) return;

      // ── Helpers ───────────────────────────────────────────────────────────────
      function toLocalDateString(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
      }

      function formatRupiah(num) {
        return Math.round(num).toLocaleString('id-ID');
      }

      // ── Set tanggal awal ──────────────────────────────────────────────────────
      function setInitialDates() {
        const today = new Date();
        const todayStr = toLocalDateString(today);
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        const tomorrowStr = toLocalDateString(tomorrow);

        startDateInput.min = todayStr;
        if (!startDateInput.value) startDateInput.value = todayStr;

        endDateInput.min = tomorrowStr;
        if (!endDateInput.value || endDateInput.value < endDateInput.min) {
          endDateInput.value = endDateInput.min;
        }
      }

      // ── Update min tanggal selesai ────────────────────────────────────────────
      function updateEndDateMin() {
        const startVal = startDateInput.value;
        if (!startVal) return;
        const start = new Date(startVal + 'T00:00:00');
        const minEnd = new Date(start);
        minEnd.setDate(minEnd.getDate() + 1);
        const minEndStr = toLocalDateString(minEnd);
        endDateInput.min = minEndStr;
        if (endDateInput.value < minEndStr) endDateInput.value = minEndStr;
      }

      // ── Hitung biaya extra pickup/dropoff ─────────────────────────────────────
      function getExtraFees() {
        const withDriver = driverOption.value === 'with_driver';
        if (withDriver) {
          // Dengan supir: pickup & dropoff sudah include di biaya supir
          return {
            pickup: 0,
            dropoff: 0
          };
        }
        let p = 0,
          d = 0;
        const pickupRadio = document.querySelector('input[name="pickup_radio"]:checked');
        if (pickupRadio && pickupRadio.value === 'antar') p = pickupFee;
        const dropoffRadio = document.querySelector('input[name="dropoff_radio"]:checked');
        if (dropoffRadio && dropoffRadio.value === 'ambil') d = dropoffFee;
        return {
          pickup: p,
          dropoff: d
        };
      }

      // ── Hitung & update ringkasan harga ──────────────────────────────────────
      function updateBooking() {
        const startVal = startDateInput.value;
        const endVal = endDateInput.value;

        if (!startVal || !endVal) {
          durationInput.value = '...';
          totalDisplay.value = '0';
          totalHidden.value = '0';
          driverCostHidden.value = '0';
          priceDetail.textContent = '';
          return;
        }

        const start = new Date(startVal + 'T00:00:00');
        const end = new Date(endVal + 'T00:00:00');

        if (end <= start) {
          durationInput.value = 'Min. 1 hari';
          totalDisplay.value = '0';
          totalHidden.value = '0';
          driverCostHidden.value = '0';
          return;
        }

        const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
        const withDriver = driverOption.value === 'with_driver';
        const driverCost = withDriver ? days * driverFee : 0;
        const {
          pickup,
          dropoff
        } = getExtraFees();
        const total = (basePrice * days) + driverCost + pickup + dropoff;

        durationInput.value = days + ' hari';
        pricePerDayDisplay.value = formatRupiah(basePrice);
        totalDisplay.value = formatRupiah(total);
        totalHidden.value = total;
        driverCostHidden.value = driverCost;

        // Detail harga per hari
        priceDetail.textContent = withDriver ? '(termasuk supir + pickup & dropoff)' : '';
      }

      // ── Render section pickup & dropoff ──────────────────────────────────────
      function renderPickupDropoff() {
        const withDriver = driverOption.value === 'with_driver';
        let html = '';

        if (withDriver) {
          // Dengan supir: pickup & dropoff sudah include di biaya supir
          html = `
        <div class="mb-3">
          <label class="form-label">Alamat Pickup <span class="text-danger">*</span> <small class="text-success"><i class="bi bi-check-circle"></i> gratis</small></label>
          <input type="text" class="form-control" name="pickup_location" id="pickupAddress"
                 placeholder="Masukkan alamat penjemputan" required>
          <input type="hidden" name="pickup_option" value="antar">
        </div>
        <div class="mb-3">
          <label class="form-label">Alamat Dropoff <span class="text-danger">*</span> <small class="text-success"><i class="bi bi-check-circle"></i> gratis</small></label>
          <input type="text" class="form-control" name="dropoff_location" id="dropoffAddress"
                 placeholder="Masukkan alamat tujuan akhir" required>
          <input type="hidden" name="dropoff_option" value="ambil">
        </div>
      `;
        } else {
          // Tanpa supir: opsi radio ambil sendiri / diantar
          html = `
        <label class="form-label">Pickup</label>
        <div class="mb-2">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="pickup_radio" id="pickupKantor" value="kantor" checked>
            <label class="form-check-label" for="pickupKantor">Ambil sendiri di kantor (gratis)</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="pickup_radio" id="pickupAntar" value="antar">
            <label class="form-check-label" for="pickupAntar">Diantar ke alamat (+Rp ${formatRupiah(pickupFee)})</label>
          </div>
          <div id="pickupAddressGroup" class="address-group mt-2" style="display:none;">
            <input type="text" class="form-control" id="pickupAddress" placeholder="Alamat pengantaran mobil">
          </div>
          <!-- Hidden inputs yang dikirim ke server -->
          <input type="hidden" name="pickup_location" id="pickupLocationHidden" value="Kantor RentalKu">
          <input type="hidden" name="pickup_option" id="pickupOptionHidden" value="kantor">
        </div>
        <label class="form-label mt-2">Dropoff</label>
        <div class="mb-2">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="dropoff_radio" id="dropoffKantor" value="kantor" checked>
            <label class="form-check-label" for="dropoffKantor">Antar sendiri ke kantor (gratis)</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="dropoff_radio" id="dropoffAmbil" value="ambil">
            <label class="form-check-label" for="dropoffAmbil">Dijemput perusahaan di alamat (+Rp ${formatRupiah(dropoffFee)})</label>
          </div>
          <div id="dropoffAddressGroup" class="address-group mt-2" style="display:none;">
            <input type="text" class="form-control" id="dropoffAddress" placeholder="Alamat penjemputan mobil">
          </div>
          <!-- Hidden inputs yang dikirim ke server -->
          <input type="hidden" name="dropoff_location" id="dropoffLocationHidden" value="Kantor RentalKu">
          <input type="hidden" name="dropoff_option" id="dropoffOptionHidden" value="kantor">
        </div>
      `;
        }

        pickupDropoffSection.innerHTML = html;

        if (!withDriver) {
          // Radio pickup
          const pickupRadios = document.getElementsByName('pickup_radio');
          const pickupAddrGroup = document.getElementById('pickupAddressGroup');
          const pickupAddrInput = document.getElementById('pickupAddress');
          const pickupHidden = document.getElementById('pickupLocationHidden');

          const pickupOptHidden = document.getElementById('pickupOptionHidden');

          function togglePickup() {
            const val = document.querySelector('input[name="pickup_radio"]:checked').value;
            const show = val === 'antar';
            pickupAddrGroup.style.display = show ? 'block' : 'none';
            if (pickupAddrInput) pickupAddrInput.required = show;
            pickupHidden.value = show ?
              (pickupAddrInput ? pickupAddrInput.value : '') :
              'Kantor RentalKu';
            if (pickupOptHidden) pickupOptHidden.value = val;
            updateBooking();
          }

          if (pickupAddrInput) {
            pickupAddrInput.addEventListener('input', () => {
              pickupHidden.value = pickupAddrInput.value || 'Kantor RentalKu';
            });
          }

          pickupRadios.forEach(r => r.addEventListener('change', togglePickup));
          togglePickup();

          // Radio dropoff
          const dropoffRadios = document.getElementsByName('dropoff_radio');
          const dropoffAddrGroup = document.getElementById('dropoffAddressGroup');
          const dropoffAddrInput = document.getElementById('dropoffAddress');
          const dropoffHidden = document.getElementById('dropoffLocationHidden');

          const dropoffOptHidden = document.getElementById('dropoffOptionHidden');

          function toggleDropoff() {
            const val = document.querySelector('input[name="dropoff_radio"]:checked').value;
            const show = val === 'ambil';
            dropoffAddrGroup.style.display = show ? 'block' : 'none';
            if (dropoffAddrInput) dropoffAddrInput.required = show;
            dropoffHidden.value = show ?
              (dropoffAddrInput ? dropoffAddrInput.value : '') :
              'Kantor RentalKu';
            if (dropoffOptHidden) dropoffOptHidden.value = val;
            updateBooking();
          }

          if (dropoffAddrInput) {
            dropoffAddrInput.addEventListener('input', () => {
              dropoffHidden.value = dropoffAddrInput.value || 'Kantor RentalKu';
            });
          }

          dropoffRadios.forEach(r => r.addEventListener('change', toggleDropoff));
          toggleDropoff();

        } else {
          // Dengan supir: sinkronisasi input teks langsung ke hidden (tidak perlu hidden, nama sudah benar)
          updateBooking();
        }
      }

      // ── Event listeners ───────────────────────────────────────────────────────
      driverOption.addEventListener('change', () => {
        renderPickupDropoff();
        updateBooking();
      });
      startDateInput.addEventListener('change', () => {
        updateEndDateMin();
        updateBooking();
      });
      endDateInput.addEventListener('change', updateBooking);

      // Checkbox syarat & ketentuan → toggle tombol submit
      const agreeCheckbox = document.getElementById('agreeTerms');
      const submitBtn = document.getElementById('submitBooking');
      if (agreeCheckbox && submitBtn) {
        agreeCheckbox.addEventListener('change', () => {
          submitBtn.disabled = !agreeCheckbox.checked;
        });
      }

      // ── Inisialisasi ─────────────────────────────────────────────────────────
      setInitialDates();
      updateEndDateMin();
      renderPickupDropoff();
      updateBooking();

      // ── Modal full image ──────────────────────────────────────────────────────
      const imageModal = document.getElementById('imageModal');
      if (imageModal) {
        imageModal.addEventListener('show.bs.modal', function() {
          document.getElementById('fullImage').src = document.getElementById('carImage').src;
        });
      }
    })();
  </script>

  <?php include '../includes/footer.php'; ?>
</body>

</html>