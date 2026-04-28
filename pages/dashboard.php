<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RentalKu · Dashboard Saya</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/dashboard.css">
  <link rel="stylesheet" href="../assets/css/navbar.css">
  <link rel="stylesheet" href="../assets/css/footer.css">
  <style>

  </style>
</head>
<body>
  <?php
  include '../assets/includes/navbar.php';
  ?>
<div class="container dashboard-container">
  <div class="dashboard-header mb-4">
    <h1>Dashboard Saya</h1>
    <p class="text-secondary">Kelola pesanan dan profil Anda</p>
  </div>

  <ul class="nav nav-tabs-custom" id="dashboardTab" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="riwayat-tab" data-bs-toggle="tab" data-bs-target="#riwayat" type="button" role="tab">Riwayat Sewa</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="profil-tab" data-bs-toggle="tab" data-bs-target="#profil" type="button" role="tab">Profil Saya</button>
    </li>
  </ul>

  <div class="tab-content" id="dashboardTabContent">
    <!-- Riwayat Sewa -->
    <div class="tab-pane fade show active" id="riwayat" role="tabpanel">
      <div id="empty-history" class="card-custom empty-state">
        <i class="bi bi-car-front"></i>
        <h5>Belum Ada Riwayat Sewa</h5>
        <p>Mulai menyewa mobil untuk melihat riwayat di sini</p>
      </div>

      <div id="rental-list" class="d-none">
        <!-- 1. Menunggu Verifikasi -->
        <div class="rental-item status-menunggu-verifikasi">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h6 class="fw-bold mb-1">Toyota Avanza 2023</h6>
              <div class="text-secondary small"><i class="bi bi-calendar3 me-1"></i> 02 Mei 2026 - 03 Mei 2026 (1 hari)</div>
              <span class="badge bg-warning text-dark mt-1">Menunggu Verifikasi</span>
            </div>
            <div class="col-md-4 text-end mt-2 mt-md-0">
              <button class="btn btn-rental-outline btn-sm" disabled>Refund</button>
              <small class="d-block text-muted">Menunggu konfirmasi admin</small>
            </div>
          </div>
        </div>

        <!-- 2. Terkonfirmasi (Refund aktif) -->
        <div class="rental-item status-terkonfirmasi">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h6 class="fw-bold mb-1">Honda Civic 2024</h6>
              <div class="text-secondary small"><i class="bi bi-calendar3 me-1"></i> 28 Apr 2026 - 30 Apr 2026 (2 hari)</div>
              <span class="badge bg-primary mt-1">Terkonfirmasi</span>
            </div>
            <div class="col-md-4 text-end mt-2 mt-md-0">
              <button class="btn btn-rental-outline btn-sm btn-refund">Refund</button>
              <small class="d-block text-muted">Mobil belum diambil</small>
            </div>
          </div>
        </div>

        <!-- 3. Sedang Disewa -->
        <div class="rental-item status-disewa">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h6 class="fw-bold mb-1">Toyota Fortuner 2023</h6>
              <div class="text-secondary small"><i class="bi bi-calendar3 me-1"></i> 27 Apr 2026 - 29 Apr 2026 (2 hari)</div>
              <span class="badge bg-indigo mt-1" style="background:#6366f1;">Sedang Disewa</span>
            </div>
            <div class="col-md-4 text-end mt-2 mt-md-0">
              <button class="btn btn-rental-outline btn-sm" disabled>Refund</button>
              <small class="d-block text-muted">Mobil sudah diambil</small>
            </div>
          </div>
        </div>

        <!-- 4. Selesai -->
        <div class="rental-item status-selesai">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h6 class="fw-bold mb-1">Honda Brio 2024</h6>
              <div class="text-secondary small"><i class="bi bi-calendar3 me-1"></i> 20 Apr 2026 - 22 Apr 2026 (2 hari)</div>
              <span class="badge bg-success mt-1">Selesai</span>
            </div>
            <div class="col-md-4 text-end mt-2 mt-md-0">
              <button class="btn btn-rental-outline btn-sm" disabled>Refund</button>
              <small class="d-block text-muted">Sewa telah selesai</small>
            </div>
          </div>
        </div>

        <!-- 5. Dibatalkan -->
        <div class="rental-item status-dibatalkan">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h6 class="fw-bold mb-1">Mitsubishi Pajero Sport</h6>
              <div class="text-secondary small"><i class="bi bi-calendar3 me-1"></i> 18 Apr 2026 - 19 Apr 2026 (1 hari)</div>
              <span class="badge bg-secondary mt-1">Dibatalkan</span>
            </div>
            <div class="col-md-4 text-end mt-2 mt-md-0">
              <button class="btn btn-rental-outline btn-sm" disabled>Refund</button>
              <small class="d-block text-muted">Pesanan dibatalkan</small>
            </div>
          </div>
        </div>

        <!-- 6. Refund Diproses -->
        <div class="rental-item status-refund-diproses">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h6 class="fw-bold mb-1">Toyota Camry 2024</h6>
              <div class="text-secondary small"><i class="bi bi-calendar3 me-1"></i> 10 Apr 2026 - 12 Apr 2026 (2 hari)</div>
              <span class="badge bg-orange mt-1" style="background:#f97316;">Refund Diproses</span>
            </div>
            <div class="col-md-4 text-end mt-2 mt-md-0">
              <button class="btn btn-rental-outline btn-sm" disabled>Refund</button>
              <small class="d-block text-muted">Refund sedang diproses</small>
            </div>
          </div>
        </div>

        <!-- 7. Refund Berhasil -->
        <div class="rental-item status-refund-berhasil">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h6 class="fw-bold mb-1">Honda Jazz 2024</h6>
              <div class="text-secondary small"><i class="bi bi-calendar3 me-1"></i> 05 Apr 2026 - 06 Apr 2026 (1 hari)</div>
              <span class="badge bg-purple mt-1" style="background:#8b5cf6;">Refund Berhasil</span>
            </div>
            <div class="col-md-4 text-end mt-2 mt-md-0">
              <button class="btn btn-rental-outline btn-sm" disabled>Refund</button>
              <small class="d-block text-muted">Dana sudah dikembalikan</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Profil Saya (sama seperti sebelumnya) -->
    <div class="tab-pane fade" id="profil" role="tabpanel">
      <div class="card-custom">
        <h5 class="fw-bold mb-3">Informasi Profil</h5>
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="info-label">Nama Lengkap</div>
            <div class="info-value" id="displayName">John Doe</div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="info-label">NIK</div>
            <div class="info-value" id="displayNik">3273012345678901</div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="info-label">Email</div>
            <div class="info-value" id="displayEmail">john@example.com</div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="info-label">No. HP</div>
            <div class="info-value" id="displayPhone">0812-3456-7890</div>
          </div>
          <div class="col-12 mb-3">
            <div class="info-label">Alamat</div>
            <div class="info-value" id="displayAddress">Jl. Sudirman No. 10, Jakarta</div>
          </div>
          <div class="col-12 mt-2">
            <span class="verified-badge"><i class="bi bi-check-circle-fill me-1"></i>Identitas Terverifikasi</span>
          </div>
        </div>
        <button class="btn btn-rental mt-3" data-bs-toggle="modal" data-bs-target="#editProfileModal">
          <i class="bi bi-pencil-square me-2"></i>Edit Profil
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Edit Profil (sama seperti sebelumnya) -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow-lg">
      <div class="modal-header border-0 px-4 pt-4">
        <h5 class="modal-title fw-bold" id="editProfileModalLabel">Edit Profil</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-4 pb-4">
        <ul class="nav nav-pills mb-4" id="profileModalTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="data-diri-tab" data-bs-toggle="pill" data-bs-target="#dataDiri" type="button" role="tab">Data Diri</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="ganti-password-tab" data-bs-toggle="pill" data-bs-target="#gantiPassword" type="button" role="tab">Ganti Password</button>
          </li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane fade show active" id="dataDiri" role="tabpanel">
            <form id="dataDiriForm">
              <div class="mb-3">
                <label class="form-label fw-semibold small text-uppercase text-secondary">Nama Lengkap</label>
                <input type="text" class="form-control" id="inputName" value="John Doe">
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold small text-uppercase text-secondary">NIK</label>
                <input type="text" class="form-control" id="inputNik" value="3273012345678901">
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold small text-uppercase text-secondary">Email</label>
                <input type="email" class="form-control" id="inputEmail" value="john@example.com">
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold small text-uppercase text-secondary">No. HP</label>
                <input type="tel" class="form-control" id="inputPhone" value="0812-3456-7890">
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold small text-uppercase text-secondary">Alamat</label>
                <textarea class="form-control" id="inputAddress" rows="2">Jl. Sudirman No. 10, Jakarta</textarea>
              </div>
              <button type="submit" class="btn btn-rental w-100"><i class="bi bi-save me-2"></i>Simpan Perubahan</button>
            </form>
          </div>
          <div class="tab-pane fade" id="gantiPassword" role="tabpanel">
            <form id="passwordForm">
              <div class="mb-3">
                <label class="form-label fw-semibold small text-uppercase text-secondary">Password Lama</label>
                <input type="password" class="form-control" placeholder="••••••••">
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold small text-uppercase text-secondary">Password Baru</label>
                <input type="password" class="form-control" placeholder="Minimal 8 karakter">
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold small text-uppercase text-secondary">Konfirmasi Password Baru</label>
                <input type="password" class="form-control" placeholder="Ulangi password baru">
              </div>
              <button type="submit" class="btn btn-rental w-100"><i class="bi bi-shield-lock me-2"></i>Ganti Password</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Tampilkan riwayat (sembunyikan empty state)
  document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('empty-history').classList.add('d-none');
    document.getElementById('rental-list').classList.remove('d-none');
  });

  // Logika tombol Refund: hanya yang tidak disabled (yaitu status Terkonfirmasi) bisa diklik
  document.querySelectorAll('#riwayat .btn-refund').forEach(btn => {
    btn.addEventListener('click', function() {
      if (!this.disabled) {
        alert('Permintaan refund akan diproses. Status akan berubah menjadi Refund Diproses. (Simulasi)');
        // Di sini bisa update status via backend
      }
    });
  });

  // Update tampilan profil setelah simpan
  document.getElementById('dataDiriForm').addEventListener('submit', function(e) {
    e.preventDefault();
    document.getElementById('displayName').textContent = document.getElementById('inputName').value;
    document.getElementById('displayNik').textContent = document.getElementById('inputNik').value;
    document.getElementById('displayEmail').textContent = document.getElementById('inputEmail').value;
    document.getElementById('displayPhone').textContent = document.getElementById('inputPhone').value;
    document.getElementById('displayAddress').textContent = document.getElementById('inputAddress').value;
    alert('Profil berhasil diperbarui.');
    bootstrap.Modal.getInstance(document.getElementById('editProfileModal')).hide();
  });

  // Ganti password
  document.getElementById('passwordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const inputs = this.querySelectorAll('input[type="password"]');
    if (inputs[1].value !== inputs[2].value) {
      alert('Konfirmasi password baru tidak cocok.');
      return;
    }
    if (inputs[1].value.length < 8) {
      alert('Password baru minimal 8 karakter.');
      return;
    }
    alert('Password berhasil diubah. (Simulasi)');
  });
</script>
<?php include '../assets/includes/footer.php'; ?>
</body>
</html>