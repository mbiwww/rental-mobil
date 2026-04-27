<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RentalKu · Detail Mobil</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/detail.css">
  <link rel="stylesheet" href="../assets/css/navbar.css">
  <link rel="stylesheet" href="../assets/css/footer.css">
</head>
<body>
  <?php
  include '../assets/includes/navbar.php';
  ?>
<div class="container detail-container">
  <a href="katalog.php" class="back-link"><i class="bi bi-arrow-left"></i> Kembali ke Katalog</a>

  <!-- FOTO MOBIL -->
  <div class="car-image-container" data-bs-toggle="modal" data-bs-target="#imageModal" title="Klik untuk melihat full size">
    <img src="https://thumb.katadata.co.id/frontend/thumbnail/2024/06/29/zigi-668026c50bcd0-toyota-fortuner-bekas_910_512.jpg" alt="Toyota Fortuner 2023" id="carImage">
  </div>

  <div class="car-header">
    <h1 class="car-title">Toyota Fortuner 2023</h1>
    <span class="badge-availability badge-available">Tersedia</span>
  </div>
  <p class="text-secondary fs-5">Tahun 2023</p>

  <div class="row g-4">
    <div class="col-md-7">
      <div class="card-custom">
        <h4 class="fw-bold mb-3">Spesifikasi</h4>
        <div class="spec-item"><i class="bi bi-gear-fill"></i><span><strong>Mesin:</strong> 2.4L Diesel Turbo</span></div>
        <div class="spec-item"><i class="bi bi-people-fill"></i><span><strong>Kapasitas:</strong> 7 Penumpang</span></div>
        <div class="spec-item"><i class="bi bi-briefcase-fill"></i><span><strong>Bagasi:</strong> 3 Koper</span></div>
        <div class="spec-item"><i class="bi bi-calendar3"></i><span><strong>Tahun:</strong> 2023</span></div>
      </div>
      <div class="card-custom">
        <h4 class="fw-bold mb-3">Deskripsi</h4>
        <p class="text-secondary">SUV tangguh dengan performa mesin diesel yang bertenaga. Cocok untuk perjalanan keluarga atau off-road.</p>
      </div>
    </div>

    <div class="col-md-5">
      <div class="card-custom">
        <h4 class="fw-bold mb-3"><i class="bi bi-calendar-check me-2"></i>Booking Mobil</h4>
        <form id="bookingForm" onsubmit="return false;">
          <!-- Tanggal -->
          <div class="mb-3">
            <label for="startDate" class="form-label">Tanggal Mulai</label>
            <input type="date" class="form-control" id="startDate" required>
          </div>
          <div class="mb-3">
            <label for="endDate" class="form-label">Tanggal Selesai</label>
            <input type="date" class="form-control" id="endDate" required>
          </div>

          <!-- Opsi Supir -->
          <div class="mb-3">
            <label for="driverOption" class="form-label">Opsi Supir</label>
            <select class="form-select" id="driverOption">
              <option value="tanpa_supir">Tanpa Supir</option>
              <option value="dengan_supir">Dengan Supir (+Rp 200.000/hari)</option>
            </select>
          </div>

          <!-- Pickup & Dropoff (dinamis) -->
          <div id="pickupDropoffSection" class="mb-3"></div>

          <!-- Harga per hari (termasuk supir jika dipilih) -->
          <div class="mb-3">
            <label class="form-label">Harga per hari</label>
            <div class="input-group">
              <span class="input-group-text bg-white" style="border-radius:16px 0 0 16px;">Rp</span>
              <input type="text" class="form-control text-end" value="500.000" readonly id="pricePerDay" style="border-left:none; border-radius:0 16px 16px 0;">
            </div>
            <div class="price-detail" id="priceDetail"></div>
          </div>

          <!-- Durasi -->
          <div class="mb-3">
            <label class="form-label">Durasi sewa</label>
            <input type="text" class="form-control text-center" id="duration" readonly placeholder="1 hari">
          </div>

          <!-- Total -->
          <div class="mb-3">
            <label class="form-label">Total</label>
            <div class="input-group">
              <span class="input-group-text bg-white" style="border-radius:16px 0 0 16px;">Rp</span>
              <input type="text" class="form-control text-end total-display" value="500.000" readonly id="totalPrice" style="border-left:none; border-radius:0 16px 16px 0;">
            </div>
            <div class="price-detail" id="totalDetail"></div>
          </div>

          <button type="submit" class="btn btn-rental w-100">
            <i class="bi bi-cart-plus me-2"></i>Tambahkan ke Keranjang
          </button>
          <p class="disclaimer text-center mt-2">
            Dengan melanjutkan, Anda setuju dengan <a href="#">syarat dan ketentuan</a> kami
          </p>
        </form>
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
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const driverOption = document.getElementById('driverOption');
    const pickupDropoffSection = document.getElementById('pickupDropoffSection');
    const pricePerDayInput = document.getElementById('pricePerDay');
    const durationInput = document.getElementById('duration');
    const totalInput = document.getElementById('totalPrice');
    const priceDetail = document.getElementById('priceDetail');
    const totalDetail = document.getElementById('totalDetail');

    const basePrice = 500000;
    const driverFee = 200000;
    const pickupFee = 50000;
    const dropoffFee = 50000;

    function getLocalDateString(date) {
      const y = date.getFullYear();
      const m = String(date.getMonth() + 1).padStart(2,'0');
      const d = String(date.getDate()).padStart(2,'0');
      return `${y}-${m}-${d}`;
    }

    function setInitialDates() {
      const today = new Date();
      const todayStr = getLocalDateString(today);
      startDateInput.min = todayStr;
      if (!startDateInput.value) startDateInput.value = todayStr;
      const tomorrow = new Date(today);
      tomorrow.setDate(tomorrow.getDate() + 1);
      const tomorrowStr = getLocalDateString(tomorrow);
      endDateInput.min = tomorrowStr;
      if (!endDateInput.value || endDateInput.value < endDateInput.min) endDateInput.value = endDateInput.min;
    }

    function updateEndDateMin() {
      const startVal = startDateInput.value;
      if (!startVal) return;
      const start = new Date(startVal + 'T00:00:00');
      const minEnd = new Date(start);
      minEnd.setDate(minEnd.getDate() + 1);
      const minEndStr = getLocalDateString(minEnd);
      const tomorrow = new Date();
      tomorrow.setDate(tomorrow.getDate() + 1);
      const tomorrowStr = getLocalDateString(tomorrow);
      const effMin = minEndStr > tomorrowStr ? minEndStr : tomorrowStr;
      endDateInput.min = effMin;
      if (endDateInput.value < effMin) endDateInput.value = effMin;
    }

    function getExtraFees() {
      const withDriver = driverOption.value === 'dengan_supir';
      if (withDriver) {
        return { pickup: pickupFee, dropoff: dropoffFee };
      } else {
        let p = 0, d = 0;
        const pickupRadio = document.querySelector('input[name="pickupOption"]:checked');
        if (pickupRadio && pickupRadio.value === 'antar') p = pickupFee;
        const dropoffRadio = document.querySelector('input[name="dropoffOption"]:checked');
        if (dropoffRadio && dropoffRadio.value === 'ambil') d = dropoffFee;
        return { pickup: p, dropoff: d };
      }
    }

    function updateBooking() {
      const startVal = startDateInput.value;
      const endVal = endDateInput.value;
      if (!startVal || !endVal) {
        durationInput.value = '...';
        totalInput.value = '0';
        priceDetail.textContent = '';
        totalDetail.textContent = '';
        return;
      }
      const start = new Date(startVal + 'T00:00:00');
      const end = new Date(endVal + 'T00:00:00');
      if (end <= start) {
        durationInput.value = 'Min. 1 hari';
        totalInput.value = '0';
        return;
      }
      const diff = end.getTime() - start.getTime();
      const days = Math.ceil(diff / (1000 * 60 * 60 * 24));
      const withDriver = driverOption.value === 'dengan_supir';
      const pricePerDay = basePrice + (withDriver ? driverFee : 0);
      const { pickup, dropoff } = getExtraFees();
      const total = (pricePerDay * days) + pickup + dropoff;

      durationInput.value = days + ' hari';
      pricePerDayInput.value = pricePerDay.toLocaleString('id-ID');
      totalInput.value = total.toLocaleString('id-ID');

      // Detail tambahan
      let priceDetailText = '';
      if (withDriver) priceDetailText = '(termasuk supir)';
      priceDetail.textContent = priceDetailText;

      let totalDetailText = '';
      const extras = [];
      if (pickup > 0) extras.push('Pickup Rp 50.000');
      if (dropoff > 0) extras.push('Dropoff Rp 50.000');
      if (extras.length) totalDetailText = 'Termasuk: ' + extras.join(', ');
      totalDetail.textContent = totalDetailText;
    }

    function renderPickupDropoff() {
      const withDriver = driverOption.value === 'dengan_supir';
      let html = '';
      if (withDriver) {
        html = `
          <div class="mb-3">
            <label class="form-label">Alamat Pickup</label>
            <input type="text" class="form-control" id="pickupAddress" placeholder="Masukkan alamat penjemputan" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Alamat Dropoff</label>
            <input type="text" class="form-control" id="dropoffAddress" placeholder="Masukkan alamat tujuan akhir" required>
          </div>
        `;
      } else {
        html = `
          <label class="form-label">Pickup</label>
          <div class="mb-2">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="pickupOption" id="pickupKantor" value="kantor" checked>
              <label class="form-check-label" for="pickupKantor">Ambil sendiri di kantor (gratis)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="pickupOption" id="pickupAntar" value="antar">
              <label class="form-check-label" for="pickupAntar">Diantar ke alamat (Rp 50.000)</label>
            </div>
            <div id="pickupAddressGroup" class="address-group mt-2" style="display:none;">
              <input type="text" class="form-control" id="pickupAddress" placeholder="Alamat pengantaran mobil">
            </div>
          </div>
          <label class="form-label mt-3">Dropoff</label>
          <div class="mb-2">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="dropoffOption" id="dropoffKantor" value="kantor" checked>
              <label class="form-check-label" for="dropoffKantor">Antar sendiri ke kantor (gratis)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="dropoffOption" id="dropoffAmbil" value="ambil">
              <label class="form-check-label" for="dropoffAmbil">Diambil oleh perusahaan di alamat (Rp 50.000)</label>
            </div>
            <div id="dropoffAddressGroup" class="address-group mt-2" style="display:none;">
              <input type="text" class="form-control" id="dropoffAddress" placeholder="Alamat penjemputan mobil">
            </div>
          </div>
        `;
      }
      pickupDropoffSection.innerHTML = html;

      if (!withDriver) {
        const pickupRadios = document.getElementsByName('pickupOption');
        const dropoffRadios = document.getElementsByName('dropoffOption');
        const pickupAddrGroup = document.getElementById('pickupAddressGroup');
        const dropoffAddrGroup = document.getElementById('dropoffAddressGroup');

        function togglePickup() {
          const val = document.querySelector('input[name="pickupOption"]:checked').value;
          pickupAddrGroup.style.display = val === 'antar' ? 'block' : 'none';
          const input = document.getElementById('pickupAddress');
          if (input) input.required = val === 'antar';
          updateBooking();
        }
        function toggleDropoff() {
          const val = document.querySelector('input[name="dropoffOption"]:checked').value;
          dropoffAddrGroup.style.display = val === 'ambil' ? 'block' : 'none';
          const input = document.getElementById('dropoffAddress');
          if (input) input.required = val === 'ambil';
          updateBooking();
        }
        pickupRadios.forEach(r => r.addEventListener('change', togglePickup));
        dropoffRadios.forEach(r => r.addEventListener('change', toggleDropoff));
        togglePickup();
        toggleDropoff();
      } else {
        // dengan supir langsung update biaya
        updateBooking();
      }
    }

    driverOption.addEventListener('change', () => {
      renderPickupDropoff();
      updateBooking();
    });
    startDateInput.addEventListener('change', () => {
      updateEndDateMin();
      updateBooking();
    });
    endDateInput.addEventListener('change', updateBooking);

    setInitialDates();
    updateEndDateMin();
    renderPickupDropoff();
    updateBooking();

    document.getElementById('bookingForm').addEventListener('submit', function() {
      if (totalInput.value === '0' || durationInput.value.includes('Min')) {
        alert('Silakan pilih tanggal yang valid.');
        return;
      }
      alert('Mobil berhasil ditambahkan ke keranjang.\nTotal: Rp ' + totalInput.value);
    });
  })();

  // Modal image
  document.getElementById('imageModal').addEventListener('show.bs.modal', function() {
    document.getElementById('fullImage').src = document.getElementById('carImage').src;
  });
</script>
<?php include '../assets/includes/footer.php'; ?>
</body>
</html>