<?php
session_start();

require_once '../config/database.php';
require_once '../classes/Database.php';
require_once '../classes/BaseModel.php';
require_once '../classes/User.php';
require_once '../classes/Rental.php';
require_once '../classes/Payment.php';

// Auth Guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    $_SESSION['error'] = 'Silakan login terlebih dahulu.';
    header('Location: login.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$userModel = new User($db);
$rentalModel = new Rental($db);
$paymentModel = new Payment($db);

$userId = (int) $_SESSION['user_id'];
$user = $userModel->findById($userId);

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Fetch rentals
$rentals = $rentalModel->getByUserId($userId);

// Calculate stats
$totalSewa = count($rentals);
$totalDuration = 0;
$durationCount = 0;
$totalPengeluaran = 0.0;

foreach ($rentals as $r) {
    // Days
    $days = (int) ((strtotime($r['end_date']) - strtotime($r['start_date'])) / 86400);
    if ($days <= 0) $days = 1;
    
    $totalDuration += $days;
    $durationCount++;

    if ($r['status'] === 'completed') {
        $totalPengeluaran += (float)$r['total_price'] + (float)$r['penalty_fee'];
    }
}

$rataRataDurasi = $durationCount > 0 ? round($totalDuration / $durationCount, 1) : 0;
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RentalKu · Dashboard Saya</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/navbar.css">
  <link rel="stylesheet" href="../assets/css/footer.css">
  <link rel="stylesheet" href="../assets/css/flash.css">
  <style type="text/tailwindcss">
    @import "tailwindcss";

    .dash-container {
      width: 100%;
      margin-right: auto;
      margin-left: auto;
      padding-right: 1rem; 
      padding-left: 1rem;
    }

    @media (width >= 576px) {
      .dash-container {
        max-width: 540px;
      }
    }

    @media (width >= 768px) {
      .dash-container {
        max-width: 720px;
      }
    }

    @media (width >= 992px) {
      .dash-container {
        max-width: 960px;
      }
    }

    @media (width >= 1200px) {
      .dash-container {
        max-width: 1140px;
      }
    }

    @media (width >= 1400px) {
      .dash-container {
        max-width: 1320px;
      }
    }
  </style>
</head>

<body class="bg-[#f5f7fa] font-['Inter',system-ui,-apple-system,sans-serif]">
  <?php include '../includes/navbar.php'; ?>
  <?php include '../includes/flash.php'; ?>

  <div class="dash-container py-4">

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold tracking-tight text-[#0b2b4a] pt-6">Dashboard Saya</h1>
      <p class="text-slate-500 mt-1">Kelola pesanan dan profil Anda</p>
    </div>

    <!-- Section 1: Total Summary -->
    <h2 class="text-base font-bold text-[#0b2b4a] mb-4">Total Ringkasan</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

      <div class="bg-white p-5 rounded-2xl shadow-[0_10px_25px_-5px_rgba(0,0,0,0.04)] border border-black/5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-2xl shrink-0">
          <i class="bi bi-car-front-fill"></i>
        </div>
        <div>
          <div class="text-sm font-medium text-slate-500">Total Sewa</div>
          <div class="text-xl font-bold text-[#0b2b4a]"><?= $totalSewa ?> Sewa</div>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl shadow-[0_10px_25px_-5px_rgba(0,0,0,0.04)] border border-black/5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-2xl shrink-0">
          <i class="bi bi-clock-history"></i>
        </div>
        <div>
          <div class="text-sm font-medium text-slate-500">Rata-rata Durasi</div>
          <div class="text-xl font-bold text-[#0b2b4a]"><?= $rataRataDurasi ?> Hari</div>
        </div>
      </div>

      <div class="bg-white p-5 rounded-2xl shadow-[0_10px_25px_-5px_rgba(0,0,0,0.04)] border border-black/5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-2xl shrink-0">
          <i class="bi bi-wallet2"></i>
        </div>
        <div>
          <div class="text-sm font-medium text-slate-500">Total Pengeluaran</div>
          <div class="text-xl font-bold text-[#0b2b4a]">Rp <?= number_format($totalPengeluaran, 0, ',', '.') ?></div>
        </div>
      </div>

    </div>

    <!-- Tabs -->
    <div class="flex border-b border-slate-200 mb-6" id="dashboardTab" role="tablist">
      <button class="px-6 py-3 font-semibold text-blue-600 border-b-[3px] border-blue-600 -mb-[2px] transition-all" id="riwayat-tab" data-tab="riwayat" type="button" role="tab">
        Riwayat Sewa
      </button>
      <button class="px-6 py-3 font-semibold text-slate-500 border-b-[3px] border-transparent hover:text-slate-700 transition-all" id="profil-tab" data-tab="profil" type="button" role="tab">
        Profil Saya
      </button>
    </div>

    <div class="tab-content" id="dashboardTabContent">

      <!-- ===== Riwayat Sewa ===== -->
      <div class="tab-pane fade show active" id="riwayat" role="tabpanel">

        <!-- Rental list -->
        <div id="rental-list">
          <?php if (empty($rentals)): ?>
            <!-- Empty state -->
            <div id="empty-history" class="bg-white p-12 rounded-3xl shadow-sm text-center text-slate-500 mt-4 mb-4">
              <i class="bi bi-car-front block text-5xl text-slate-300 mb-4"></i>
              <h5 class="text-lg font-semibold text-slate-600 mb-1">Belum Ada Riwayat Sewa</h5>
              <p>Mulai menyewa mobil untuk melihat riwayat di sini</p>
            </div>
          <?php else: ?>
            <?php foreach ($rentals as $r): ?>
              <?php
              $p = $paymentModel->getByRentalId($r['id']);
              $days = (int) ((strtotime($r['end_date']) - strtotime($r['start_date'])) / 86400);
              if ($days <= 0) $days = 1;

              // Determine visual configurations based on status
              // Statuses: 'pending','confirmed','ongoing','completed','cancel_requested','cancelled'
              $statusConfig = match($r['status']) {
                  'pending' => [
                      'border' => 'border-l-amber-400',
                      'bg' => 'bg-amber-100 text-amber-800',
                      'label' => $p ? ($p['status'] === 'rejected' ? 'Pembayaran Ditolak' : 'Menunggu Verifikasi') : 'Belum Dibayar',
                      'desc' => $p ? ($p['status'] === 'rejected' ? 'Pembayaran ditolak admin. Silakan bayar kembali.' : 'Menunggu konfirmasi pembayaran oleh admin') : 'Silakan lakukan pembayaran secepatnya',
                      'allow_pay' => !$p || $p['status'] === 'rejected',
                      'allow_cancel' => !$p || $p['status'] === 'rejected',
                      'allow_refund' => false
                  ],
                  'confirmed' => [
                      'border' => 'border-l-blue-500',
                      'bg' => 'bg-blue-100 text-blue-800',
                      'label' => 'Terkonfirmasi',
                      'desc' => 'Mobil siap diambil sesuai jadwal sewa',
                      'allow_pay' => false,
                      'allow_cancel' => false,
                      'allow_refund' => true
                  ],
                  'ongoing' => [
                      'border' => 'border-l-indigo-500',
                      'bg' => 'bg-indigo-100 text-indigo-800',
                      'label' => 'Sedang Disewa',
                      'desc' => 'Mobil sedang aktif Anda gunakan',
                      'allow_pay' => false,
                      'allow_cancel' => false,
                      'allow_refund' => false
                  ],
                  'completed' => [
                      'border' => 'border-l-emerald-500',
                      'bg' => 'bg-emerald-100 text-emerald-800',
                      'label' => 'Selesai',
                      'desc' => 'Masa sewa selesai dan mobil telah dikembalikan',
                      'allow_pay' => false,
                      'allow_cancel' => false,
                      'allow_refund' => false
                  ],
                  'cancel_requested' => [
                      'border' => 'border-l-orange-500',
                      'bg' => 'bg-orange-100 text-orange-800',
                      'label' => 'Refund Diproses',
                      'desc' => 'Permintaan pengembalian dana sedang diproses',
                      'allow_pay' => false,
                      'allow_cancel' => false,
                      'allow_refund' => false
                  ],
                  'cancelled' => [
                      'border' => 'border-l-slate-400',
                      'bg' => 'bg-slate-200 text-slate-700',
                      'label' => 'Dibatalkan',
                      'desc' => 'Pemesanan dibatalkan',
                      'allow_pay' => false,
                      'allow_cancel' => false,
                      'allow_refund' => false
                  ],
                  default => [
                      'border' => 'border-l-slate-300',
                      'bg' => 'bg-slate-100 text-slate-800',
                      'label' => ucfirst($r['status']),
                      'desc' => '',
                      'allow_pay' => false,
                      'allow_cancel' => false,
                      'allow_refund' => false
                  ]
              };
              ?>
              <div class="bg-white p-5 rounded-2xl border-l-4 <?= $statusConfig['border'] ?> shadow-[0_4px_12px_rgba(0,0,0,0.02)] border border-black/5 mb-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                  <div>
                    <h6 class="text-base font-bold text-[#0b2b4a] mb-1">
                      <?= htmlspecialchars($r['brand'] . ' ' . $r['model'] . ' ' . $r['year']) ?>
                    </h6>
                    <div class="text-sm text-slate-500 flex flex-wrap gap-x-4 gap-y-1">
                      <span><i class="bi bi-calendar3 mr-1"></i> <?= date('d M Y', strtotime($r['start_date'])) ?> - <?= date('d M Y', strtotime($r['end_date'])) ?> (<?= $days ?> hari)</span>
                      <span><i class="bi bi-wallet2 mr-1"></i> Rp <?= number_format($r['total_price'], 0, ',', '.') ?></span>
                    </div>
                    <div class="mt-2 flex gap-2 items-center">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $statusConfig['bg'] ?>">
                        <?= $statusConfig['label'] ?>
                      </span>
                      <?php if ($r['status'] === 'completed' && (float)$r['penalty_fee'] > 0): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                          Denda: Rp <?= number_format($r['penalty_fee'], 0, ',', '.') ?>
                        </span>
                      <?php endif; ?>
                    </div>
                    <small class="block text-xs text-slate-400 mt-1.5"><?= $statusConfig['desc'] ?></small>
                  </div>
                  <div class="text-left md:text-right flex flex-wrap gap-2 justify-start md:justify-end shrink-0">
                    <?php if ($statusConfig['allow_pay']): ?>
                      <a href="pembayaran.php?rental_id=<?= $r['id'] ?>" class="px-5 py-2 rounded-full bg-blue-600 text-white text-xs font-semibold shadow-md hover:bg-blue-700 transition-all text-center no-underline">
                        Bayar Sekarang
                      </a>
                    <?php endif; ?>

                    <?php if ($statusConfig['allow_cancel']): ?>
                      <form action="../handlers/booking_handler.php?action=cancel_booking" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan booking ini?');" class="inline">
                        <input type="hidden" name="rental_id" value="<?= $r['id'] ?>">
                        <button type="submit" class="px-5 py-2 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200 border border-slate-200 transition-all">
                          Batalkan
                        </button>
                      </form>
                    <?php endif; ?>

                    <?php if ($statusConfig['allow_refund']): ?>
                      <button type="button" class="px-5 py-2 rounded-full bg-red-500 text-white text-xs font-semibold shadow-md hover:bg-red-600 hover:-translate-y-0.5 transition-all btn-refund-modal" data-rental-id="<?= $r['id'] ?>" data-car-name="<?= htmlspecialchars($r['brand'] . ' ' . $r['model']) ?>">
                        Refund / Batal
                      </button>
                    <?php endif; ?>

                    <?php if (!$statusConfig['allow_pay'] && !$statusConfig['allow_cancel'] && !$statusConfig['allow_refund']): ?>
                      <span class="text-xs text-slate-400 italic">Tidak ada aksi</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- ===== Profil Saya ===== -->
      <div class="tab-pane fade" id="profil" role="tabpanel">
        <div class="bg-white p-6 rounded-3xl shadow-sm mt-4 mb-4 border border-black/5">
          <h5 class="text-lg font-bold text-[#0b2b4a] mb-4">Informasi Profil</h5>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-0.5">Nama Lengkap</div>
              <div class="font-semibold text-[#0b2b4a]" id="displayName"><?= htmlspecialchars($user['name']) ?></div>
            </div>
            <div>
              <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-0.5">NIK</div>
              <div class="font-semibold text-[#0b2b4a]" id="displayNik"><?= htmlspecialchars($user['nik']) ?></div>
            </div>
            <div>
              <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-0.5">Email</div>
              <div class="font-semibold text-[#0b2b4a]" id="displayEmail"><?= htmlspecialchars($user['email']) ?></div>
            </div>
            <div>
              <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-0.5">No. HP</div>
              <div class="font-semibold text-[#0b2b4a]" id="displayPhone"><?= htmlspecialchars($user['phone']) ?></div>
            </div>
            <div class="md:col-span-2">
              <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-0.5">Alamat</div>
              <div class="font-semibold text-[#0b2b4a]" id="displayAddress"><?= htmlspecialchars($user['address']) ?></div>
            </div>
            <div class="md:col-span-2 mt-1">
              <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-600">
                <i class="bi bi-check-circle-fill mr-1"></i>Identitas Terverifikasi
              </span>
            </div>
          </div>
          <button class="inline-flex items-center px-5 py-2.5 rounded-full bg-[#0b2b4a] text-white font-semibold shadow-md hover:bg-[#1e3a6f] hover:-translate-y-0.5 transition-all mt-4" data-bs-toggle="modal" data-bs-target="#editProfileModal">
            <i class="bi bi-pencil-square mr-2"></i>Edit Profil
          </button>
        </div>
      </div>

    </div>
  </div>

  <!-- Modal Edit Profil -->
  <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content rounded-2xl shadow-lg border-0">
        <div class="modal-header border-0 px-4 pt-4">
          <h5 class="text-lg font-bold text-[#0b2b4a]" id="editProfileModalLabel">Edit Profil</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body px-4 pb-4">

          <!-- Modal pills tab -->
          <div class="flex gap-2 mb-4" id="profileModalTab" role="tablist">
            <button class="px-4 py-2 rounded-xl text-sm font-medium bg-[#0b2b4a] text-white transition-all" id="data-diri-tab" data-modal-tab="dataDiri" type="button" role="tab">
              Data Diri
            </button>
            <button class="px-4 py-2 rounded-xl text-sm font-medium text-slate-600 hover:text-[#0b2b4a] transition-all" id="ganti-password-tab" data-modal-tab="gantiPassword" type="button" role="tab">
              Ganti Password
            </button>
          </div>

          <div class="tab-content">
            <!-- Data Diri -->
            <div class="tab-pane fade show active" id="dataDiri" role="tabpanel">
              <form id="dataDiriForm" method="POST" action="../handlers/auth_handler.php?action=update_profil">
                <div class="mb-3">
                  <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">Nama Lengkap</label>
                  <input type="text" name="name" class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-gray-50 font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all" id="inputName" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                <div class="mb-3">
                  <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">NIK</label>
                  <input type="text" name="nik" class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-gray-50 font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all" id="inputNik" value="<?= htmlspecialchars($user['nik']) ?>" required>
                </div>
                <div class="mb-3">
                  <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">Email</label>
                  <input type="email" name="email" class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-gray-50 font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all" id="inputEmail" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="mb-3">
                  <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">No. HP</label>
                  <input type="tel" name="phone" class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-gray-50 font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all" id="inputPhone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                </div>
                <div class="mb-3">
                  <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">Alamat</label>
                  <textarea name="address" class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-gray-50 font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all" id="inputAddress" rows="2" required><?= htmlspecialchars($user['address']) ?></textarea>
                </div>
                <button type="submit" class="w-full px-5 py-3 rounded-full bg-[#0b2b4a] text-white font-semibold shadow-md hover:bg-[#1e3a6f] hover:-translate-y-0.5 transition-all">
                  <i class="bi bi-save mr-2"></i>Simpan Perubahan
                </button>
              </form>
            </div>

            <!-- Ganti Password -->
            <div class="tab-pane fade" id="gantiPassword" role="tabpanel">
              <form id="passwordForm" method="POST" action="../handlers/auth_handler.php?action=change_password">
                <div class="mb-3">
                  <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">Password Lama</label>
                  <input type="password" name="old_password" class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-gray-50 font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all" placeholder="••••••••" required>
                </div>
                <div class="mb-3">
                  <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">Password Baru</label>
                  <input type="password" name="new_password" class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-gray-50 font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all" placeholder="Minimal 8 karakter" required>
                </div>
                <div class="mb-3">
                  <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">Konfirmasi Password Baru</label>
                  <input type="password" name="konfirmasi_password" class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-gray-50 font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all" placeholder="Ulangi password baru" required>
                </div>
                <button type="submit" class="w-full px-5 py-3 rounded-full bg-[#0b2b4a] text-white font-semibold shadow-md hover:bg-[#1e3a6f] hover:-translate-y-0.5 transition-all">
                  <i class="bi bi-shield-lock mr-2"></i>Ganti Password
                </button>
              </form>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- Modal Refund / Pembatalan -->
  <div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-2xl shadow-lg border-0">
        <div class="modal-header border-0 px-4 pt-4">
          <h5 class="text-lg font-bold text-[#0b2b4a]" id="refundModalLabel">Pengajuan Refund & Pembatalan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body px-4 pb-4">
          <form id="refundForm" method="POST" action="../handlers/booking_handler.php?action=request_refund">
            <input type="hidden" name="rental_id" id="refundRentalId" value="">
            <div class="mb-3">
              <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">Mobil</label>
              <input type="text" id="refundCarName" class="w-full px-4 py-3 rounded-2xl border-2 border-slate-100 bg-slate-50 font-semibold text-[#0b2b4a] outline-none" readonly>
            </div>
            <div class="mb-3">
              <label for="reasonOption" class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">Alasan Utama <span class="text-danger">*</span></label>
              <select name="reason_option" id="reasonOption" class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-white font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all" required>
                <option value="">-- Pilih Alasan Pembatalan --</option>
                <option value="Perubahan rencana perjalanan">Perubahan rencana perjalanan</option>
                <option value="Kondisi darurat keluarga">Kondisi darurat keluarga</option>
                <option value="Mobil tidak sesuai ekspektasi">Mobil tidak sesuai ekspektasi</option>
                <option value="Menemukan harga lebih murah">Menemukan harga lebih murah</option>
                <option value="Masalah pembayaran">Masalah pembayaran</option>
                <option value="Lainnya">Lainnya</option>
              </select>
            </div>
            <div class="mb-4">
              <label for="reasonDetail" class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">Detail Alasan & Rekening Refund <span class="text-danger">*</span></label>
              <textarea name="reason_detail" id="reasonDetail" rows="3" class="w-full px-4 py-3 rounded-2xl border-2 border-slate-200 bg-white font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all" placeholder="Tulis rincian alasan dan nomor rekening Anda untuk pengembalian dana" required></textarea>
            </div>
            <button type="submit" class="w-full px-5 py-3 rounded-full bg-red-600 text-white font-semibold shadow-md hover:bg-red-700 hover:-translate-y-0.5 transition-all">
              <i class="bi bi-check2-circle mr-2"></i>Kirim Pengajuan Refund
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="../assets/js/tailwind.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Tab controller — dashboard utama
    (function() {
      var container = document.getElementById('dashboardTab');
      if (!container) return;
      var tabs = container.querySelectorAll('[data-tab]');
      function activateTab(id) {
        var panel, btn, i;
        for (i = 0; i < tabs.length; i++) {
          btn = tabs[i];
          if (btn.getAttribute('data-tab') === id) {
            btn.classList.add('text-blue-600', 'border-blue-600');
            btn.classList.remove('text-slate-500', 'border-transparent');
          } else {
            btn.classList.remove('text-blue-600', 'border-blue-600');
            btn.classList.add('text-slate-500', 'border-transparent');
          }
        }
        var panels = ['riwayat', 'profil'];
        for (i = 0; i < panels.length; i++) {
          panel = document.getElementById(panels[i]);
          if (!panel) continue;
          if (panels[i] === id) {
            panel.classList.add('show', 'active');
            panel.classList.remove('hidden');
          } else {
            panel.classList.remove('show', 'active');
            panel.classList.add('hidden');
          }
        }
      }
      for (var i = 0; i < tabs.length; i++) {
        tabs[i].addEventListener('click', function() {
          activateTab(this.getAttribute('data-tab'));
        });
      }
      activateTab('riwayat');
    })();

    // Tab controller — modal profil
    (function() {
      var container = document.getElementById('profileModalTab');
      if (!container) return;
      var pills = container.querySelectorAll('[data-modal-tab]');
      function activatePill(id) {
        var panel, btn, i;
        for (i = 0; i < pills.length; i++) {
          btn = pills[i];
          if (btn.getAttribute('data-modal-tab') === id) {
            btn.classList.remove('text-slate-600');
            btn.classList.add('bg-[#0b2b4a]', 'text-white');
          } else {
            btn.classList.remove('bg-[#0b2b4a]', 'text-white');
            btn.classList.add('text-slate-600');
          }
        }
        var panels = ['dataDiri', 'gantiPassword'];
        for (i = 0; i < panels.length; i++) {
          panel = document.getElementById(panels[i]);
          if (!panel) continue;
          if (panels[i] === id) {
            panel.classList.add('show', 'active');
            panel.classList.remove('hidden');
          } else {
            panel.classList.remove('show', 'active');
            panel.classList.add('hidden');
          }
        }
      }
      for (var i = 0; i < pills.length; i++) {
        pills[i].addEventListener('click', function() {
          activatePill(this.getAttribute('data-modal-tab'));
        });
      }
      activatePill('dataDiri');
    })();

    // Trigger Modal Refund & set data
    document.addEventListener('DOMContentLoaded', function () {
      var refundModal = new bootstrap.Modal(document.getElementById('refundModal'));
      document.querySelectorAll('.btn-refund-modal').forEach(function(btn) {
        btn.addEventListener('click', function() {
          var rentalId = this.getAttribute('data-rental-id');
          var carName = this.getAttribute('data-car-name');
          
          document.getElementById('refundRentalId').value = rentalId;
          document.getElementById('refundCarName').value = carName;
          
          refundModal.show();
        });
      });
    });
  </script>
  <?php include '../includes/footer.php'; ?>
</body>

</html>
