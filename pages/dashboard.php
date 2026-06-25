<?php
session_start();

require_once '../config/database.php';
require_once '../classes/Database.php';
require_once '../classes/BaseModel.php';
require_once '../classes/User.php';
require_once '../classes/Rental.php';
require_once '../classes/Payment.php';
require_once '../classes/BankAccount.php';

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
$bankAccountModel = new BankAccount($db);
$bankAccounts = $bankAccountModel->getActive();

$userId = (int) $_SESSION['user_id'];
$user = $userModel->findById($userId);

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Fetch rentals
$rentals = $rentalModel->getByUserId($userId);

// Fetch penalty rate per hour setting
$stmtRate = $db->prepare("SELECT value FROM settings WHERE key_name = 'penalty_fee_per_hour'");
$stmtRate->execute();
$penaltyRate = (float) $stmtRate->fetchColumn();
if ($penaltyRate <= 0) {
    $penaltyRate = 20000.0; // fallback
}

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
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
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
  <style>
    .material-symbols-outlined {
      font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
      display: inline-block;
      line-height: 1;
      text-transform: none;
      letter-spacing: normal;
      word-wrap: normal;
      white-space: nowrap;
      direction: ltr;
    }
    .soft-bloom {
      box-shadow: 0px 10px 15px -3px rgba(0, 0, 0, 0.05);
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

        <div id="rental-list">
          <?php if (empty($rentals)): ?>
            <!-- Empty state -->
            <div id="empty-history" class="bg-white p-12 rounded-3xl shadow-sm text-center text-slate-500 mt-4 mb-4">
              <i class="bi bi-car-front block text-5xl text-slate-300 mb-4"></i>
              <h5 class="text-lg font-semibold text-slate-600 mb-1">Belum Ada Riwayat Sewa</h5>
              <p>Mulai menyewa mobil untuk melihat riwayat di sini</p>
            </div>
          <?php else: ?>
            <div class="bg-white rounded-xl soft-bloom border border-slate-200/30 overflow-hidden">
              <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                  <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/30">
                      <th class="px-6 py-4 text-xs font-semibold uppercase tracking-[0.05em] text-slate-400">Mobil</th>
                      <th class="px-6 py-4 text-xs font-semibold uppercase tracking-[0.05em] text-slate-400">Tanggal Sewa</th>
                      <th class="px-6 py-4 text-xs font-semibold uppercase tracking-[0.05em] text-slate-400">Durasi</th>
                      <th class="px-6 py-4 text-xs font-semibold uppercase tracking-[0.05em] text-slate-400">Total</th>
                      <th class="px-6 py-4 text-xs font-semibold uppercase tracking-[0.05em] text-slate-400">Status</th>
                      <th class="px-6 py-4 text-xs font-semibold uppercase tracking-[0.05em] text-slate-400 text-right">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100/50">
                    <?php foreach ($rentals as $r): ?>
                      <?php
                      $p = $paymentModel->getByRentalId($r['id']);
                      $days = (int) ((strtotime($r['end_date']) - strtotime($r['start_date'])) / 86400);
                      if ($days <= 0) $days = 1;

                      // Query refund data jika ada
                      $refund = null;
                      if (in_array($r['status'], ['cancel_requested', 'cancelled'])) {
                          $stmtRefund = $db->prepare("SELECT * FROM refunds WHERE rental_id = ? ORDER BY requested_at DESC LIMIT 1");
                          $stmtRefund->execute([$r['id']]);
                          $refund = $stmtRefund->fetch(PDO::FETCH_ASSOC);
                      }

                      // Hitung deadline pengembalian kendaraan dan denda
                      $penaltyInfo = $rentalModel->calculatePenalty($r);
                      $isOverdue = $penaltyInfo['is_overdue'] && ($r['status'] === 'ongoing');
                      $displayedPenaltyFee = ($r['penalty_fee'] > 0) ? (float)$r['penalty_fee'] : (float)$penaltyInfo['penalty_fee'];

                      // Hitung jam terlambat
                      if ($r['status'] === 'completed') {
                          $lateHours = ((float)$r['penalty_fee'] > 0) ? (int)(($r['penalty_fee'] ?? 0) / $penaltyRate) : 0;
                      } else {
                          $lateHours = $penaltyInfo['late_hours'];
                      }

                      $deadlineText = '';
                      if (in_array($r['status'], ['cancelled', 'cancel_requested'])) {
                          // Dibatalkan / refund — tidak ada deadline
                          $deadlineText = '-';
                      } elseif (!empty($r['started_at'])) {
                          if (!empty($penaltyInfo['deadline'])) {
                              $deadlineText = date('d M Y, H:i', strtotime($penaltyInfo['deadline'])) . ' WIB';
                          } else {
                              $deadlineTimestamp = strtotime($r['started_at']) + ($days * 86400);
                              $deadlineText = date('d M Y, H:i', $deadlineTimestamp) . ' WIB';
                          }
                      } else {
                          // Belum dimulai — tampilkan end_date pukul 23:59
                          $deadlineText = date('d M Y', strtotime($r['end_date'])) . ', 23:59 WIB';
                      }

                      // Determine visual configurations based on status
                      // Statuses: 'pending','confirmed','ongoing','completed','cancel_requested','cancelled'
                      $statusConfig = match($r['status']) {
                          'pending' => [
                              'bg' => 'bg-amber-500/10 text-amber-500',
                              'label' => $p ? ($p['status'] === 'rejected' ? 'Pembayaran Ditolak' : 'Menunggu Verifikasi') : 'Belum Dibayar',
                              'desc' => $p ? ($p['status'] === 'rejected' ? 'Pembayaran ditolak admin. Silakan bayar kembali.' : 'Menunggu konfirmasi pembayaran oleh admin') : 'Silakan lakukan pembayaran secepatnya',
                              'allow_pay' => !$p || $p['status'] === 'rejected',
                              'allow_cancel' => !$p || $p['status'] === 'rejected',
                              'allow_refund' => false
                          ],
                          'confirmed' => [
                              'bg' => 'bg-blue-800/10 text-blue-800',
                              'label' => 'Terkonfirmasi',
                              'desc' => 'Mobil siap diambil sesuai jadwal sewa',
                              'allow_pay' => false,
                              'allow_cancel' => false,
                              'allow_refund' => true
                          ],
                          'ongoing' => [
                              'bg' => 'bg-violet-600/10 text-violet-600',
                              'label' => 'Sedang Disewa',
                              'desc' => 'Mobil sedang aktif Anda gunakan',
                              'allow_pay' => false,
                              'allow_cancel' => false,
                              'allow_refund' => false
                          ],
                          'completed' => [
                              'bg' => 'bg-emerald-500/10 text-emerald-500',
                              'label' => 'Selesai',
                              'desc' => 'Masa sewa selesai dan mobil telah dikembalikan',
                              'allow_pay' => false,
                              'allow_cancel' => false,
                              'allow_refund' => false
                          ],
                          'cancel_requested' => [
                              'bg' => 'bg-orange-500/10 text-orange-500',
                              'label' => 'Refund Diproses',
                              'desc' => 'Permintaan pengembalian dana sedang diproses',
                              'allow_pay' => false,
                              'allow_cancel' => false,
                              'allow_refund' => false
                          ],
                          'cancelled' => [
                              'bg' => 'bg-red-500/10 text-red-500',
                              'label' => 'Dibatalkan',
                              'desc' => 'Pemesanan dibatalkan',
                              'allow_pay' => false,
                              'allow_cancel' => false,
                              'allow_refund' => false
                          ],
                          default => [
                              'bg' => 'bg-slate-500/10 text-slate-600',
                              'label' => ucfirst($r['status']),
                              'desc' => '',
                              'allow_pay' => false,
                              'allow_cancel' => false,
                              'allow_refund' => false
                          ]
                      };
                      ?>
                      <tr class="hover:bg-slate-50/50 transition-colors">
                        <!-- Mobil -->
                        <td class="px-6 py-4">
                          <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                              <span class="material-symbols-outlined text-slate-400">directions_car</span>
                            </div>
                            <span class="font-semibold text-[#0b2b4a] text-base"><?= htmlspecialchars($r['brand'] . ' ' . $r['model'] . ' ' . $r['year']) ?></span>
                          </div>
                        </td>
                        <!-- Tanggal Sewa -->
                        <td class="px-6 py-4">
                          <div class="flex items-center gap-2 text-slate-400 text-sm">
                            <span class="material-symbols-outlined" style="font-size:18px">calendar_today</span>
                            <span><?= date('d M Y', strtotime($r['start_date'])) ?> - <?= date('d M Y', strtotime($r['end_date'])) ?></span>
                          </div>
                        </td>
                        <!-- Durasi -->
                        <td class="px-6 py-4">
                          <span class="text-sm text-[#0b2b4a]"><?= $days ?> Hari</span>
                        </td>
                        <!-- Total -->
                        <td class="px-6 py-4">
                          <span class="text-sm font-semibold text-[#0b2b4a]">Rp <?= number_format($r['total_price'], 0, ',', '.') ?></span>
                          <?php if ($r['status'] === 'completed' && (float)$r['penalty_fee'] > 0): ?>
                            <span class="block text-xs text-red-500 font-medium mt-0.5">
                              + Denda Rp <?= number_format($r['penalty_fee'], 0, ',', '.') ?>
                              <span class="block text-[10px] text-slate-400 font-normal mt-0.5">(<?= $lateHours ?> jam × Rp <?= number_format($penaltyRate, 0, ',', '.') ?>)</span>
                            </span>
                          <?php elseif ($r['status'] === 'ongoing' && $isOverdue): ?>
                            <span class="block text-xs text-red-500 font-medium mt-0.5">
                              + Estimasi Denda Rp <?= number_format($displayedPenaltyFee, 0, ',', '.') ?>
                              <span class="block text-[10px] text-slate-400 font-normal mt-0.5">(<?= $lateHours ?> jam × Rp <?= number_format($penaltyRate, 0, ',', '.') ?>)</span>
                            </span>
                          <?php endif; ?>
                        </td>
                        <!-- Status -->
                        <td class="px-6 py-4">
                          <div class="flex flex-col gap-1 items-start">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?= $statusConfig['bg'] ?>">
                              <?= $statusConfig['label'] ?>
                            </span>
                            <?php if (($r['status'] === 'completed' && (float)$r['penalty_fee'] > 0) || ($r['status'] === 'ongoing' && $isOverdue)): ?>
                              <?php
                              $penaltyStatusConfig = match($r['penalty_payment_status']) {
                                  'unpaid' => ['bg' => 'bg-red-500/10 text-red-600', 'label' => 'Denda: Belum Dibayar'],
                                  'pending' => ['bg' => 'bg-amber-500/10 text-amber-600', 'label' => 'Denda: Menunggu Verifikasi'],
                                  'confirmed' => ['bg' => 'bg-emerald-500/10 text-emerald-600', 'label' => 'Denda: Lunas'],
                                  'rejected' => ['bg' => 'bg-red-500/10 text-red-600', 'label' => 'Denda: Pembayaran Ditolak'],
                                  default => ['bg' => 'bg-slate-500/10 text-slate-600', 'label' => 'Denda: Unknown']
                              };
                              ?>
                              <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?= $penaltyStatusConfig['bg'] ?>">
                                <?= $penaltyStatusConfig['label'] ?>
                              </span>
                            <?php endif; ?>
                          </div>
                        </td>
                        <!-- Aksi -->
                        <td class="px-6 py-4 text-right">
                          <div class="flex flex-col items-end gap-1">
                            <?php if ($statusConfig['allow_pay']): ?>
                              <a href="pembayaran.php?rental_id=<?= $r['id'] ?>" class="px-4 py-1.5 rounded-lg bg-[#00288e] text-white text-sm font-semibold shadow-sm hover:bg-[#1e40af] active:scale-95 transition-all text-center" style="text-decoration:none">
                                Bayar Sekarang
                              </a>
                            <?php endif; ?>

                            <?php if ($statusConfig['allow_cancel']): ?>
                              <form action="../handlers/booking_handler.php?action=cancel_booking" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan booking ini?');" class="inline">
                                <input type="hidden" name="rental_id" value="<?= $r['id'] ?>">
                                <button type="submit" class="px-4 py-1.5 rounded-lg bg-slate-200/60 text-slate-600 text-sm font-semibold hover:bg-slate-200 transition-all">
                                  Batalkan
                                </button>
                              </form>
                            <?php endif; ?>

                            <button type="button" class="px-4 py-1.5 rounded-lg <?= ($statusConfig['allow_pay'] || $statusConfig['allow_cancel']) ? 'bg-slate-200/60 text-slate-600 hover:bg-slate-200' : 'bg-[#00288e] text-white hover:bg-[#1e40af] shadow-sm' ?> text-sm font-semibold active:scale-95 transition-all btn-detail-modal"
                              data-rental-id="<?= $r['id'] ?>"
                              data-car-name="<?= htmlspecialchars($r['brand'] . ' ' . $r['model'] . ' ' . $r['year']) ?>"
                              data-car-image="<?= htmlspecialchars($r['image'] ?? '') ?>"
                              data-start-date="<?= date('d M Y', strtotime($r['start_date'])) ?>"
                              data-end-date="<?= date('d M Y', strtotime($r['end_date'])) ?>"
                              data-days="<?= $days ?>"
                              data-rental-type="<?= htmlspecialchars($r['rental_type'] ?? 'Lepas Kunci') ?>"
                              data-pickup="<?= htmlspecialchars($r['pickup_location'] ?? '-') ?>"
                              data-dropoff="<?= htmlspecialchars($r['dropoff_location'] ?? '-') ?>"
                              data-total="Rp <?= number_format($r['total_price'], 0, ',', '.') ?>"
                              data-driver-cost="Rp <?= number_format($r['driver_cost'] ?? 0, 0, ',', '.') ?>"
                              data-pickup-fee="Rp <?= number_format($r['pickup_fee'] ?? 0, 0, ',', '.') ?>"
                              data-dropoff-fee="Rp <?= number_format($r['dropoff_fee'] ?? 0, 0, ',', '.') ?>"
                              data-penalty="Rp <?= number_format($displayedPenaltyFee, 0, ',', '.') ?>"
                              data-price-per-day="<?= (float)($r['price_per_day'] ?? 0) ?>"
                              data-driver-cost-raw="<?= (float)($r['driver_cost'] ?? 0) ?>"
                              data-pickup-fee-raw="<?= (float)($r['pickup_fee'] ?? 0) ?>"
                              data-dropoff-fee-raw="<?= (float)($r['dropoff_fee'] ?? 0) ?>"
                              data-penalty-raw="<?= (float)$displayedPenaltyFee ?>"
                              data-penalty-hours="<?= $lateHours ?>"
                              data-penalty-rate="<?= $penaltyRate ?>"
                              data-status="<?= $statusConfig['label'] ?>"
                              data-status-class="<?= $statusConfig['bg'] ?>"
                              data-payment-method="<?= htmlspecialchars($p['method'] ?? '-') ?>"
                              data-payment-status="<?= htmlspecialchars($p['status'] ?? 'Belum Dibayar') ?>"
                              data-payment-bank="<?= htmlspecialchars($p ? ($p['bank_name'] . ' - ' . $p['account_number'] . ' (' . $p['account_holder'] . ')') : '-') ?>"
                              data-payment-proof="<?= htmlspecialchars($p['proof_image'] ?? '') ?>"
                              data-created-at="<?= date('d M Y H:i', strtotime($r['created_at'])) ?>"
                              data-allow-refund="<?= $statusConfig['allow_refund'] ? '1' : '0' ?>"
                              data-deadline="<?= htmlspecialchars($deadlineText) ?>"
                              data-is-overdue="<?= $isOverdue ? '1' : '0' ?>"
                              data-penalty-status="<?= htmlspecialchars($r['penalty_payment_status']) ?>"
                              data-penalty-proof="<?= htmlspecialchars($r['penalty_proof_image'] ?? '') ?>"
                              data-refund-reason="<?= htmlspecialchars($refund['reason_option'] ?? '') ?>"
                              data-refund-detail="<?= htmlspecialchars($refund['reason_detail'] ?? '') ?>"
                              data-refund-status="<?= htmlspecialchars($refund['status'] ?? '') ?>"
                              data-refund-date="<?= $refund ? date('d M Y H:i', strtotime($refund['requested_at'])) : '' ?>"
                              data-refund-proof="<?= htmlspecialchars($refund['refund_proof_image'] ?? '') ?>"
                              data-refund-bank-name="<?= htmlspecialchars($refund['refund_bank_name'] ?? '') ?>"
                              data-refund-account-number="<?= htmlspecialchars($refund['refund_account_number'] ?? '') ?>"
                              data-refund-account-holder="<?= htmlspecialchars($refund['refund_account_holder'] ?? '') ?>"
                            >
                              Detail
                            </button>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
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
                  <div class="relative">
                    <input type="password" name="old_password" id="inputOldPassword" class="w-full px-4 py-3 pr-12 rounded-2xl border-2 border-slate-200 bg-gray-50 font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all" placeholder="••••••••" required>
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600 transition-colors" id="toggleOldPassword" aria-label="Tampilkan/sembunyikan password">
                      <i class="bi bi-eye text-lg" id="iconOldPassword"></i>
                    </button>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">Password Baru</label>
                  <div class="relative">
                    <input type="password" name="new_password" id="inputNewPassword" class="w-full px-4 py-3 pr-12 rounded-2xl border-2 border-slate-200 bg-gray-50 font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all" placeholder="Minimal 8 karakter" required>
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600 transition-colors" id="toggleNewPassword" aria-label="Tampilkan/sembunyikan password">
                      <i class="bi bi-eye text-lg" id="iconNewPassword"></i>
                    </button>
                  </div>

                  <!-- Panel syarat password — tampil saat user mulai mengetik -->
                  <div id="dash-password-rules" class="mt-3 hidden">
                    <p class="mb-1.5 text-xs font-semibold text-slate-500">Syarat password:</p>
                    <ul class="space-y-1 text-sm">
                      <li id="dash-rule-length" class="flex items-center gap-1.5 text-red-500">
                        <i class="bi bi-x-circle-fill"></i>
                        <span>Minimal 8 karakter <span class="text-slate-400">(12+ lebih aman)</span></span>
                      </li>
                      <li id="dash-rule-upper" class="flex items-center gap-1.5 text-red-500">
                        <i class="bi bi-x-circle-fill"></i>
                        <span>Mengandung huruf besar (A–Z)</span>
                      </li>
                      <li id="dash-rule-lower" class="flex items-center gap-1.5 text-red-500">
                        <i class="bi bi-x-circle-fill"></i>
                        <span>Mengandung huruf kecil (a–z)</span>
                      </li>
                      <li id="dash-rule-number" class="flex items-center gap-1.5 text-red-500">
                        <i class="bi bi-x-circle-fill"></i>
                        <span>Mengandung angka (0–9)</span>
                      </li>
                      <li id="dash-rule-special" class="flex items-center gap-1.5 text-red-500">
                        <i class="bi bi-x-circle-fill"></i>
                        <span>Mengandung karakter khusus (! @ # $ % ^ & *)</span>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">Konfirmasi Password Baru</label>
                  <div class="relative">
                    <input type="password" name="konfirmasi_password" id="inputConfirmPassword" class="w-full px-4 py-3 pr-12 rounded-2xl border-2 border-slate-200 bg-gray-50 font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all" placeholder="Ulangi password baru" required>
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600 transition-colors" id="toggleConfirmPassword" aria-label="Tampilkan/sembunyikan konfirmasi password">
                      <i class="bi bi-eye text-lg" id="iconConfirmPassword"></i>
                    </button>
                  </div>
                  <div id="dash-konfirm-feedback" class="mt-2 text-sm text-red-500 hidden">
                    <i class="bi bi-x-circle mr-1"></i>Password tidak cocok
                  </div>
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

  <!-- Modal Detail Pesanan -->
  <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content rounded-2xl shadow-lg border-0">
        <div class="modal-header border-0 px-5 pt-5 pb-0">
          <div>
            <h5 class="text-lg font-bold text-[#0b2b4a]" id="detailModalLabel">Detail Pesanan</h5>
            <p class="text-xs text-slate-400 mt-0.5" id="detailCreatedAt"></p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body px-5 pb-5 pt-3">

          <!-- Status Badge -->
          <div class="mb-4">
            <span id="detailStatusBadge" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"></span>
          </div>

          <div class="flex flex-col lg:flex-row gap-6 items-start">
            <!-- Left Column (and Mobile main container) -->
            <div id="colLeft" class="w-full lg:w-1/2 space-y-4">
              <!-- 1. Informasi Mobil -->
              <div id="cardMobil" class="bg-slate-50 rounded-xl p-4 w-full">
                <h6 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Informasi Mobil</h6>
                <div class="flex items-center gap-4">
                  <div class="w-16 h-16 rounded-xl bg-white border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden">
                    <img id="detailCarImage" src="" alt="" class="w-full h-full object-cover hidden">
                    <span id="detailCarIcon" class="material-symbols-outlined text-slate-300" style="font-size:32px">directions_car</span>
                  </div>
                  <div>
                    <p class="font-semibold text-[#0b2b4a] text-base" id="detailCarName"></p>
                    <p class="text-sm text-slate-400" id="detailRentalType"></p>
                  </div>
                </div>
              </div>

              <!-- 2. Jadwal & Lokasi -->
              <div id="cardJadwal" class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
                <div class="bg-slate-50 rounded-xl p-4">
                  <h6 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Jadwal Sewa</h6>
                  <div class="space-y-2">
                    <div class="flex items-center gap-2">
                      <span class="material-symbols-outlined text-slate-400" style="font-size:18px">calendar_today</span>
                      <span class="text-sm text-[#0b2b4a]" id="detailDates"></span>
                    </div>
                    <div class="flex items-center gap-2">
                      <span class="material-symbols-outlined text-slate-400" style="font-size:18px">schedule</span>
                      <span class="text-sm text-[#0b2b4a]" id="detailDuration"></span>
                    </div>
                    <div class="flex items-center gap-2 mt-1 pt-2 border-t border-slate-200/60">
                      <span class="material-symbols-outlined text-amber-500" style="font-size:18px">alarm</span>
                      <div>
                        <p class="text-[10px] text-slate-400 leading-none mb-0.5">Deadline Pengembalian</p>
                        <span class="text-sm font-semibold" id="detailDeadline"></span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="bg-slate-50 rounded-xl p-4">
                  <h6 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Lokasi</h6>
                  <div class="space-y-2">
                    <div class="flex items-start gap-2">
                      <span class="material-symbols-outlined text-emerald-500 shrink-0" style="font-size:18px">trip_origin</span>
                      <div><p class="text-[10px] text-slate-400">Pengambilan</p><p class="text-sm text-[#0b2b4a]" id="detailPickup"></p></div>
                    </div>
                    <div class="flex items-start gap-2">
                      <span class="material-symbols-outlined text-red-400 shrink-0" style="font-size:18px">location_on</span>
                      <div><p class="text-[10px] text-slate-400">Pengembalian</p><p class="text-sm text-[#0b2b4a]" id="detailDropoff"></p></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- 3. Rincian Biaya -->
              <div id="cardRincian" class="bg-slate-50 rounded-xl p-4 w-full">
                <h6 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Rincian Biaya</h6>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between"><span class="text-slate-500" id="detailCarCostLabel">Sewa Mobil</span><span class="text-[#0b2b4a] font-medium" id="detailCarCost"></span></div>
                  <div class="flex justify-between"><span class="text-slate-500">Biaya Sopir</span><span class="text-[#0b2b4a] font-medium" id="detailDriverCost"></span></div>
                  <div class="flex justify-between"><span class="text-slate-500">Biaya Antar</span><span class="text-[#0b2b4a] font-medium" id="detailPickupFee"></span></div>
                  <div class="flex justify-between"><span class="text-slate-500">Biaya Jemput</span><span class="text-[#0b2b4a] font-medium" id="detailDropoffFee"></span></div>
                  <div class="flex justify-between" id="detailPenaltyRow"><span class="text-red-500" id="detailPenaltyLabel">Denda Keterlambatan</span><span class="text-red-500 font-medium" id="detailPenalty"></span></div>
                  <hr class="border-slate-200">
                  <div class="flex justify-between font-semibold text-base"><span class="text-[#0b2b4a]">Total</span><span class="text-[#00288e]" id="detailTotal"></span></div>
                </div>
              </div>

              <!-- 4. Pembayaran Denda Keterlambatan -->
              <div id="detailPenaltySection" class="bg-red-50/50 rounded-xl p-4 border border-red-100 hidden w-full">
                <h6 class="text-xs font-semibold uppercase tracking-wider text-red-600 mb-3 flex items-center gap-1.5">
                  <span class="material-symbols-outlined" style="font-size:16px">account_balance_wallet</span>
                  Pembayaran Denda Keterlambatan
                </h6>
                <div class="space-y-3 text-sm">
                  <div class="flex justify-between">
                    <div>
                      <span class="text-slate-500">Jumlah Denda</span>
                      <span class="text-[10px] text-slate-400 block font-normal mt-0.5" id="penaltySectionAmountDetail"></span>
                    </div>
                    <span class="text-red-600 font-semibold align-self-center" id="penaltySectionAmount"></span>
                  </div>
                  <div class="flex justify-between"><span class="text-slate-500">Status Pembayaran Denda</span><span class="font-medium" id="penaltySectionStatus"></span></div>

                  <!-- Info Rejection (Only shown if status is rejected) -->
                  <div id="penaltyRejectionInfo" class="bg-red-100/65 border border-red-200 text-red-700 rounded-xl p-3 text-xs leading-relaxed hidden">
                    <div class="flex items-start gap-1.5">
                      <span class="material-symbols-outlined text-red-600 mt-0.5" style="font-size:16px">error</span>
                      <div>
                        <strong>Pembayaran Denda Ditolak:</strong> Foto bukti transfer denda tidak sesuai atau tidak valid. Silakan lakukan transfer kembali dan unggah foto bukti pembayaran yang benar.
                      </div>
                    </div>
                  </div>
                  
                  <!-- Form Upload Bukti Denda (Unpaid / Rejected) -->
                  <div id="penaltyUploadFormContainer" class="hidden">
                    <hr class="border-red-100 my-3">
                    <p class="text-xs text-slate-600 mb-3 leading-relaxed">
                      Silakan lakukan transfer sebesar jumlah denda ke salah satu rekening bank admin berikut, kemudian unggah bukti transfer di bawah.
                      <strong class="text-red-600 block mt-1"><i class="bi bi-info-circle-fill"></i> Catatan: Pastikan foto bukti transfer denda yang Anda unggah jelas, asli, dan valid. Admin akan menolak pembayaran denda apabila bukti transfer denda tidak valid.</strong>
                    </p>
                    <div class="bg-white rounded-xl p-3 border border-red-200/60 mb-3 space-y-2">
                      <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Rekening Bank Admin:</p>
                      <?php foreach ($bankAccounts as $bank): ?>
                        <div class="text-xs text-[#0b2b4a]">
                          <span class="font-bold"><?= htmlspecialchars($bank['bank_name']) ?></span>: 
                          <?= htmlspecialchars($bank['account_number']) ?> a/n <?= htmlspecialchars($bank['account_holder']) ?>
                        </div>
                      <?php endforeach; ?>
                    </div>
                    
                    <form id="penaltyPaymentForm" method="POST" action="../handlers/booking_handler.php?action=pay_penalty" enctype="multipart/form-data">
                      <input type="hidden" name="rental_id" id="penaltyRentalId" value="">
                      <div class="mb-3">
                        <label for="penaltyProofImageInput" class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">Unggah Bukti Transfer Denda <span class="text-red-500">*</span></label>
                        <input type="file" name="penalty_proof_image" id="penaltyProofImageInput" class="w-full px-4 py-2 border border-slate-200 rounded-xl bg-white focus:border-red-600 outline-none" accept="image/jpeg,image/png,image/jpg" required>
                        <p class="text-[10px] text-slate-400 mt-1">Format: JPG, JPEG, PNG. Maksimal 5MB. Pastikan foto bukti transfer denda asli, jelas, dan terbaca dengan baik.</p>
                      </div>
                      <button type="submit" class="w-full px-5 py-2.5 rounded-xl bg-red-600 text-white font-semibold shadow-sm hover:bg-red-700 hover:-translate-y-0.5 transition-all">
                        <i class="bi bi-upload mr-2"></i>Kirim Bukti Pembayaran Denda
                      </button>
                    </form>
                  </div>

                  <!-- Menampilkan Bukti Denda (Pending / Confirmed) -->
                  <div id="penaltyProofImageContainer" class="hidden">
                    <hr class="border-red-100 my-3">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Bukti Pembayaran Denda Anda</p>
                    <div class="flex justify-center">
                      <img id="penaltyProofImageDisplay" src="" alt="Bukti Transfer Denda" class="max-w-full max-h-60 rounded-xl border border-slate-200 shadow-sm cursor-pointer" onclick="window.open(this.src, '_blank')">
                    </div>
                    <p class="text-[10px] text-slate-400 text-center mt-2">Klik gambar untuk melihat ukuran penuh</p>
                  </div>
                </div>
              </div>

              <!-- 5. Informasi Pembayaran -->
              <div id="cardPembayaran" class="bg-slate-50 rounded-xl p-4 w-full">
                <h6 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Informasi Pembayaran</h6>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between"><span class="text-slate-500">Metode</span><span class="text-[#0b2b4a] font-medium" id="detailPayMethod"></span></div>
                  <div class="flex justify-between"><span class="text-slate-500">Rekening Tujuan</span><span class="text-[#0b2b4a] font-medium text-right" id="detailPayBank" style="max-width:60%"></span></div>
                  <div class="flex justify-between"><span class="text-slate-500">Status Pembayaran</span><span class="font-medium" id="detailPayStatus"></span></div>
                </div>
              </div>

              <!-- 6. Bukti Transfer -->
              <div id="detailProofSection" class="bg-slate-50 rounded-xl p-4 hidden w-full">
                <h6 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Bukti Transfer</h6>
                <div class="flex justify-center">
                  <img id="detailProofImage" src="" alt="Bukti Transfer" class="max-w-full max-h-60 rounded-xl border border-slate-200 shadow-sm cursor-pointer" onclick="window.open(this.src, '_blank')">
                </div>
                <p class="text-[10px] text-slate-400 text-center mt-2">Klik gambar untuk melihat ukuran penuh</p>
              </div>

              <!-- 7. Informasi Refund -->
              <div id="detailRefundInfoSection" class="bg-amber-50 rounded-xl p-4 border border-amber-200/50 hidden w-full">
                <h6 class="text-xs font-semibold uppercase tracking-wider text-amber-600 mb-3 flex items-center gap-1.5">
                  <span class="material-symbols-outlined" style="font-size:16px">currency_exchange</span>
                  Informasi Pengembalian Dana
                </h6>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between"><span class="text-slate-500">Alasan Pembatalan</span><span class="text-[#0b2b4a] font-medium text-right" id="detailRefundReason" style="max-width:60%"></span></div>
                  <div class="flex justify-between" id="detailRefundDetailRow"><span class="text-slate-500">Detail Alasan</span><span class="text-[#0b2b4a] font-medium text-right" id="detailRefundDetail" style="max-width:60%"></span></div>
                  <div class="flex justify-between"><span class="text-slate-500">Status Refund</span><span class="font-medium" id="detailRefundStatus"></span></div>
                  <div class="flex justify-between"><span class="text-slate-500">Tanggal Pengajuan</span><span class="text-[#0b2b4a] font-medium" id="detailRefundDate"></span></div>
                </div>
                <!-- Rekening Tujuan Refund -->
                <div id="detailRefundBankSection" class="mt-3 pt-3 border-t border-amber-200/50 hidden">
                  <p class="text-xs font-semibold uppercase tracking-wider text-amber-600 mb-2">Rekening Tujuan Refund</p>
                  <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Nama Nasabah</span><span class="text-[#0b2b4a] font-medium" id="detailRefundAccountHolder"></span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Nama Bank</span><span class="text-[#0b2b4a] font-medium" id="detailRefundBankName"></span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Nomor Rekening</span><span class="text-[#0b2b4a] font-medium" id="detailRefundAccountNumber"></span></div>
                  </div>
                </div>
                <!-- Bukti Refund -->
                <div id="detailRefundProofSection" class="mt-3 pt-3 border-t border-amber-200/50 hidden">
                  <p class="text-xs font-semibold uppercase tracking-wider text-amber-600 mb-2">Bukti Pengembalian Dana</p>
                  <div class="flex justify-center">
                    <img id="detailRefundProofImage" src="" alt="Bukti Refund" class="max-w-full max-h-60 rounded-xl border border-amber-200 shadow-sm cursor-pointer" onclick="window.open(this.src, '_blank')">
                  </div>
                  <p class="text-[10px] text-slate-400 text-center mt-2">Klik gambar untuk melihat ukuran penuh</p>
                </div>
              </div>

              <!-- 8. Refund Section -->
              <div id="detailRefundSection" class="hidden w-full">
                <hr class="border-slate-200 my-4">
                <h6 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Ajukan Pembatalan & Refund</h6>
                <form id="refundForm" method="POST" action="../handlers/booking_handler.php?action=request_refund">
                  <input type="hidden" name="rental_id" id="refundRentalId" value="">
                  <div class="mb-3">
                    <label for="reasonOption" class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">Alasan Utama <span class="text-red-500">*</span></label>
                    <select name="reason_option" id="reasonOption" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all" required>
                      <option value="" disabled selected>-- Pilih Alasan Pembatalan --</option>
                      <option value="Perubahan rencana perjalanan">Perubahan rencana perjalanan</option>
                      <option value="Kondisi darurat keluarga">Kondisi darurat keluarga</option>
                      <option value="Mobil tidak sesuai ekspektasi">Mobil tidak sesuai ekspektasi</option>
                      <option value="Menemukan harga lebih murah">Menemukan harga lebih murah</option>
                      <option value="Masalah pembayaran">Masalah pembayaran</option>
                      <option value="Lainnya">Lainnya</option>
                    </select>
                  </div>
                  <div class="mb-4 hidden" id="reasonDetailContainer">
                    <label for="reasonDetail" class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">Detail Alasan <span class="text-red-500">*</span></label>
                    <textarea name="reason_detail" id="reasonDetail" rows="3" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all" placeholder="Tulis rincian alasan pembatalan Anda"></textarea>
                  </div>

                  <!-- Tujuan Transfer Refund -->
                  <div class="bg-blue-50 rounded-xl p-4 mb-4 border border-blue-200/50">
                    <h6 class="text-xs font-semibold uppercase tracking-wider text-blue-600 mb-3 flex items-center gap-1.5">
                      <span class="material-symbols-outlined" style="font-size:16px">account_balance</span>
                      Tujuan Transfer Refund <span class="text-red-500">*</span>
                    </h6>
                    <p class="text-xs text-slate-500 mb-3">Masukkan rekening Anda untuk penerimaan dana refund</p>
                    <div class="mb-3">
                      <label for="refundAccountHolder" class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">Nama Nasabah <span class="text-red-500">*</span></label>
                      <input type="text" name="refund_account_holder" id="refundAccountHolder" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all refund-transfer-field" placeholder="Contoh: Budi Santoso" required>
                    </div>
                    <div class="mb-3">
                      <label for="refundBankName" class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">Nama Bank <span class="text-red-500">*</span></label>
                      <input type="text" name="refund_bank_name" id="refundBankName" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all refund-transfer-field" placeholder="Contoh: BCA, Mandiri, BNI, BRI" required>
                    </div>
                    <div>
                      <label for="refundAccountNumber" class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2 block">Nomor Rekening <span class="text-red-500">*</span></label>
                      <input type="text" name="refund_account_number" id="refundAccountNumber" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white font-medium focus:border-blue-600 focus:ring-4 focus:ring-blue-100 outline-none transition-all refund-transfer-field" placeholder="Contoh: 1234567890" required>
                    </div>
                  </div>

                  <button type="submit" id="btnSubmitRefund" class="w-full px-5 py-3 rounded-xl bg-red-600 text-white font-semibold shadow-md hover:bg-red-700 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0" disabled>
                    <i class="bi bi-check2-circle mr-2"></i>Kirim Pengajuan Refund
                  </button>
                </form>
              </div>
            </div>

            <!-- Right Column (Desktop only container) -->
            <div id="colRight" class="w-full lg:w-1/2 space-y-4 hidden lg:block">
            </div>
          </div>

        </div>
      </div>
    </div>
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

    // Trigger Modal Detail & populate data
    document.addEventListener('DOMContentLoaded', function () {
      var detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
      document.querySelectorAll('.btn-detail-modal').forEach(function(btn) {
        btn.addEventListener('click', function() {
          var d = this.dataset;

          // Header
          document.getElementById('detailCreatedAt').textContent = 'Dibuat: ' + d.createdAt;

          // Status
          var badge = document.getElementById('detailStatusBadge');
          badge.textContent = d.status;
          badge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ' + d.statusClass;

          // Car info
          document.getElementById('detailCarName').textContent = d.carName;
          document.getElementById('detailRentalType').textContent = d.rentalType === 'with_driver' ? 'Dengan Sopir' : 'Lepas Kunci';
          var carImg = document.getElementById('detailCarImage');
          var carIcon = document.getElementById('detailCarIcon');
          if (d.carImage) {
            carImg.src = '../assets/uploads/cars/' + d.carImage;
            carImg.classList.remove('hidden');
            carIcon.classList.add('hidden');
          } else {
            carImg.classList.add('hidden');
            carIcon.classList.remove('hidden');
          }

          // Dates & Location
          document.getElementById('detailDates').textContent = d.startDate + ' — ' + d.endDate;
          document.getElementById('detailDuration').textContent = d.days + ' Hari';

          // Deadline pengembalian
          var deadlineEl = document.getElementById('detailDeadline');
          deadlineEl.textContent = d.deadline;
          if (d.isOverdue === '1') {
            deadlineEl.className = 'text-sm font-semibold text-red-500';
            deadlineEl.textContent = d.deadline + ' (Terlambat!)';
          } else {
            deadlineEl.className = 'text-sm font-semibold text-[#0b2b4a]';
          }

          document.getElementById('detailPickup').textContent = d.pickup;
          document.getElementById('detailDropoff').textContent = d.dropoff;

          // Costs
          var pricePerDay = parseFloat(d.pricePerDay) || 0;
          var days = parseInt(d.days) || 0;
          var carCost = pricePerDay * days;
          var driverCost = parseFloat(d.driverCostRaw) || 0;
          var pickupFee = parseFloat(d.pickupFeeRaw) || 0;
          var dropoffFee = parseFloat(d.dropoffFeeRaw) || 0;
          var penaltyFee = parseFloat(d.penaltyRaw) || 0;

          var totalCost = carCost + driverCost + pickupFee + dropoffFee + penaltyFee;

          function formatRupiah(num) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
          }

          document.getElementById('detailCarCostLabel').textContent = 'Sewa Mobil (' + formatRupiah(pricePerDay) + ' x ' + days + ' Hari)';
          document.getElementById('detailCarCost').textContent = formatRupiah(carCost);
          document.getElementById('detailDriverCost').textContent = formatRupiah(driverCost);
          document.getElementById('detailPickupFee').textContent = formatRupiah(pickupFee);
          document.getElementById('detailDropoffFee').textContent = formatRupiah(dropoffFee);
          document.getElementById('detailTotal').textContent = formatRupiah(totalCost);

          var penaltyRow = document.getElementById('detailPenaltyRow');
          var penaltyLabel = document.getElementById('detailPenaltyLabel');
          if (penaltyFee > 0) {
            document.getElementById('detailPenalty').textContent = formatRupiah(penaltyFee);
            var penaltyHours = parseInt(d.penaltyHours) || 0;
            var penaltyRate = parseFloat(d.penaltyRate) || 0;
            if (penaltyLabel) {
              penaltyLabel.textContent = 'Denda Keterlambatan (' + penaltyHours + ' jam × ' + formatRupiah(penaltyRate) + ')';
            }
            penaltyRow.classList.remove('hidden');
          } else {
            penaltyRow.classList.add('hidden');
          }

          // Payment
          document.getElementById('detailPayMethod').textContent = d.paymentMethod === 'bank_transfer' ? 'Transfer Bank' : d.paymentMethod;
          document.getElementById('detailPayBank').textContent = d.paymentBank;
          var payStatus = document.getElementById('detailPayStatus');
          var ps = d.paymentStatus;
          if (d.refundReason) {
            ps = 'confirmed';
          }
          payStatus.textContent = ps === 'pending' ? 'Menunggu Verifikasi' : ps === 'confirmed' ? 'Terverifikasi' : ps === 'rejected' ? 'Ditolak' : ps;
          payStatus.className = 'font-medium ' + (ps === 'confirmed' ? 'text-emerald-500' : ps === 'rejected' ? 'text-red-500' : 'text-amber-500');

          // Proof image
          var proofSection = document.getElementById('detailProofSection');
          var proofImg = document.getElementById('detailProofImage');
          if (d.paymentProof) {
            proofImg.src = '../assets/uploads/payments/' + d.paymentProof;
            proofSection.classList.remove('hidden');
          } else {
            proofSection.classList.add('hidden');
          }

          // Penalty Payment Section
          var penaltySection = document.getElementById('detailPenaltySection');
          var penaltyUploadFormContainer = document.getElementById('penaltyUploadFormContainer');
          var penaltyProofImageContainer = document.getElementById('penaltyProofImageContainer');
          var penaltyProofImageDisplay = document.getElementById('penaltyProofImageDisplay');
          var penaltyRentalIdInput = document.getElementById('penaltyRentalId');
          var penaltyProofImageInput = document.getElementById('penaltyProofImageInput');

          // Reset fields
          if (penaltyRentalIdInput) penaltyRentalIdInput.value = '';
          if (penaltyProofImageInput) penaltyProofImageInput.value = '';

          if (penaltyFee > 0 && (d.status === 'Selesai' || d.status === 'Sedang Disewa')) {
            penaltySection.classList.remove('hidden');
            document.getElementById('penaltySectionAmount').textContent = formatRupiah(penaltyFee);
            var penaltyHours = parseInt(d.penaltyHours) || 0;
            var penaltyRate = parseFloat(d.penaltyRate) || 0;
            var detailEl = document.getElementById('penaltySectionAmountDetail');
            if (detailEl) {
              detailEl.textContent = '(' + penaltyHours + ' jam × ' + formatRupiah(penaltyRate) + ')';
            }
            
            var penaltyStatusEl = document.getElementById('penaltySectionStatus');
            var pStatus = d.penaltyStatus; // unpaid, pending, confirmed, rejected
            
            var statusLabels = {
              'unpaid': 'Belum Dibayar',
              'pending': 'Menunggu Verifikasi',
              'confirmed': 'Lunas / Terverifikasi',
              'rejected': 'Pembayaran Ditolak'
            };
            
            var statusClasses = {
              'unpaid': 'text-red-500 font-semibold',
              'pending': 'text-amber-500 font-semibold',
              'confirmed': 'text-emerald-500 font-semibold',
              'rejected': 'text-red-500 font-semibold'
            };
            
            penaltyStatusEl.textContent = statusLabels[pStatus] || pStatus;
            penaltyStatusEl.className = 'font-medium ' + (statusClasses[pStatus] || 'text-slate-500');
            
            var penaltyRejectionInfo = document.getElementById('penaltyRejectionInfo');
            if (pStatus === 'rejected') {
              if (penaltyRejectionInfo) penaltyRejectionInfo.classList.remove('hidden');
            } else {
              if (penaltyRejectionInfo) penaltyRejectionInfo.classList.add('hidden');
            }

            if (pStatus === 'unpaid' || pStatus === 'rejected') {
              penaltyUploadFormContainer.classList.remove('hidden');
              penaltyProofImageContainer.classList.add('hidden');
              if (penaltyRentalIdInput) penaltyRentalIdInput.value = d.rentalId;
            } else {
              penaltyUploadFormContainer.classList.add('hidden');
              penaltyProofImageContainer.classList.remove('hidden');
              if (d.penaltyProof) {
                penaltyProofImageDisplay.src = '../assets/uploads/payments/' + d.penaltyProof;
              } else {
                penaltyProofImageContainer.classList.add('hidden');
              }
            }
          } else {
            penaltySection.classList.add('hidden');
            var penaltyRejectionInfo = document.getElementById('penaltyRejectionInfo');
            if (penaltyRejectionInfo) penaltyRejectionInfo.classList.add('hidden');
          }

          // Refund info section
          var refundInfoSection = document.getElementById('detailRefundInfoSection');
          if (d.refundReason) {
            refundInfoSection.classList.remove('hidden');
            document.getElementById('detailRefundReason').textContent = d.refundReason;

            // Detail alasan (hanya tampil jika ada)
            var refundDetailRow = document.getElementById('detailRefundDetailRow');
            if (d.refundDetail) {
              document.getElementById('detailRefundDetail').textContent = d.refundDetail;
              refundDetailRow.classList.remove('hidden');
            } else {
              refundDetailRow.classList.add('hidden');
            }

            // Status refund
            var refundStatusEl = document.getElementById('detailRefundStatus');
            var rs = d.refundStatus;
            refundStatusEl.textContent = rs === 'requested' ? 'Menunggu Persetujuan' : rs === 'approved' ? 'Disetujui' : rs === 'rejected' ? 'Ditolak' : rs;
            refundStatusEl.className = 'font-medium ' + (rs === 'approved' ? 'text-emerald-500' : rs === 'rejected' ? 'text-red-500' : 'text-amber-500');

            // Tanggal pengajuan
            document.getElementById('detailRefundDate').textContent = d.refundDate;

            // Rekening tujuan refund (tampil jika ada)
            var refundBankSection = document.getElementById('detailRefundBankSection');
            if (d.refundBankName && d.refundAccountNumber && d.refundAccountHolder) {
              document.getElementById('detailRefundAccountHolder').textContent = d.refundAccountHolder;
              document.getElementById('detailRefundBankName').textContent = d.refundBankName;
              document.getElementById('detailRefundAccountNumber').textContent = d.refundAccountNumber;
              refundBankSection.classList.remove('hidden');
            } else {
              refundBankSection.classList.add('hidden');
            }

            // Bukti refund (jika admin sudah upload)
            var refundProofSection = document.getElementById('detailRefundProofSection');
            var refundProofImg = document.getElementById('detailRefundProofImage');
            if (d.refundProof) {
              refundProofImg.src = '../assets/uploads/refunds/' + d.refundProof;
              refundProofSection.classList.remove('hidden');
            } else {
              refundProofSection.classList.add('hidden');
            }
          } else {
            refundInfoSection.classList.add('hidden');
          }

          // Refund section
          var refundSection = document.getElementById('detailRefundSection');
          if (d.allowRefund === '1') {
            document.getElementById('refundRentalId').value = d.rentalId;
            refundSection.classList.remove('hidden');
          } else {
            refundSection.classList.add('hidden');
          }

          // Reset refund form fields when modal is opened
          var reasonOption = document.getElementById('reasonOption');
          var reasonDetail = document.getElementById('reasonDetail');
          var reasonDetailContainer = document.getElementById('reasonDetailContainer');
          var refundAccountHolder = document.getElementById('refundAccountHolder');
          var refundBankName = document.getElementById('refundBankName');
          var refundAccountNumber = document.getElementById('refundAccountNumber');
          var btnSubmitRefund = document.getElementById('btnSubmitRefund');
          if (reasonOption) {
            reasonOption.value = "";
          }
          if (reasonDetailContainer) {
            reasonDetailContainer.classList.add('hidden');
          }
          if (reasonDetail) {
            reasonDetail.value = "";
            reasonDetail.removeAttribute('required');
          }
          if (refundAccountHolder) refundAccountHolder.value = '';
          if (refundBankName) refundBankName.value = '';
          if (refundAccountNumber) refundAccountNumber.value = '';
          if (btnSubmitRefund) btnSubmitRefund.disabled = true;

          detailModal.show();
        });
      });

      // Handle showing/hiding detail refund field based on reason choice
      var reasonOption = document.getElementById('reasonOption');
      var reasonDetail = document.getElementById('reasonDetail');
      var reasonDetailContainer = document.getElementById('reasonDetailContainer');

      if (reasonOption && reasonDetail && reasonDetailContainer) {
        reasonOption.addEventListener('change', function() {
          if (this.value === 'Lainnya') {
            reasonDetailContainer.classList.remove('hidden');
            reasonDetail.setAttribute('required', 'required');
          } else {
            reasonDetailContainer.classList.add('hidden');
            reasonDetail.removeAttribute('required');
            reasonDetail.value = '';
          }
        });
      }

      // Enable/disable refund submit button based on transfer fields
      var refundTransferFields = document.querySelectorAll('.refund-transfer-field');
      var btnSubmitRefund = document.getElementById('btnSubmitRefund');

      function checkRefundTransferFields() {
        if (!btnSubmitRefund) return;
        var allFilled = true;
        refundTransferFields.forEach(function(field) {
          if (!field.value.trim()) {
            allFilled = false;
          }
        });
        btnSubmitRefund.disabled = !allFilled;
      }

      refundTransferFields.forEach(function(field) {
        field.addEventListener('input', checkRefundTransferFields);
      });

      // Dynamic layout rearrangement for detail modal columns
      var colLeft = document.getElementById('colLeft');
      var colRight = document.getElementById('colRight');
      var cardMobil = document.getElementById('cardMobil');
      var cardJadwal = document.getElementById('cardJadwal');
      var cardRincian = document.getElementById('cardRincian');
      var cardDenda = document.getElementById('detailPenaltySection');
      var cardPembayaran = document.getElementById('cardPembayaran');
      var cardBukti = document.getElementById('detailProofSection');
      var cardRefundInfo = document.getElementById('detailRefundInfoSection');
      var cardRefundForm = document.getElementById('detailRefundSection');

      var layoutMediaQuery = window.matchMedia('(min-width: 1024px)');
      function handleLayoutRearrange(e) {
        if (!colLeft || !colRight) return;
        if (e.matches) {
          // Desktop: Col Left = Mobil, Jadwal, Pembayaran, Bukti
          if (cardMobil) colLeft.appendChild(cardMobil);
          if (cardJadwal) colLeft.appendChild(cardJadwal);
          if (cardPembayaran) colLeft.appendChild(cardPembayaran);
          if (cardBukti) colLeft.appendChild(cardBukti);

          // Desktop: Col Right = Rincian, Denda, RefundInfo, RefundForm
          if (cardRincian) colRight.appendChild(cardRincian);
          if (cardDenda) colRight.appendChild(cardDenda);
          if (cardRefundInfo) colRight.appendChild(cardRefundInfo);
          if (cardRefundForm) colRight.appendChild(cardRefundForm);
        } else {
          // Mobile: Stack all in colLeft in mobile order
          if (cardMobil) colLeft.appendChild(cardMobil);
          if (cardJadwal) colLeft.appendChild(cardJadwal);
          if (cardRincian) colLeft.appendChild(cardRincian);
          if (cardDenda) colLeft.appendChild(cardDenda);
          if (cardPembayaran) colLeft.appendChild(cardPembayaran);
          if (cardBukti) colLeft.appendChild(cardBukti);
          if (cardRefundInfo) colLeft.appendChild(cardRefundInfo);
          if (cardRefundForm) colLeft.appendChild(cardRefundForm);
        }
      }
      if (layoutMediaQuery.addEventListener) {
        layoutMediaQuery.addEventListener('change', handleLayoutRearrange);
      } else {
        layoutMediaQuery.addListener(handleLayoutRearrange);
      }
      handleLayoutRearrange(layoutMediaQuery);
    });

    // -------------------------------------------------------
    // Password toggle visibility — Dashboard
    // -------------------------------------------------------
    function setupToggle(btnId, inputId, iconId) {
      var btn = document.getElementById(btnId);
      var input = document.getElementById(inputId);
      var icon = document.getElementById(iconId);
      if (!btn || !input || !icon) return;
      btn.addEventListener('click', function() {
        var isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        icon.className = isPassword ? 'bi bi-eye-slash text-lg' : 'bi bi-eye text-lg';
        btn.title = isPassword ? 'Sembunyikan password' : 'Tampilkan password';
      });
    }
    setupToggle('toggleOldPassword', 'inputOldPassword', 'iconOldPassword');
    setupToggle('toggleNewPassword', 'inputNewPassword', 'iconNewPassword');
    setupToggle('toggleConfirmPassword', 'inputConfirmPassword', 'iconConfirmPassword');

    // -------------------------------------------------------
    // Syarat kekuatan password — real-time (Dashboard)
    // -------------------------------------------------------
    (function() {
      var newPwInput = document.getElementById('inputNewPassword');
      var confirmPwInput = document.getElementById('inputConfirmPassword');
      var rulesPanel = document.getElementById('dash-password-rules');
      var konfirmFeedback = document.getElementById('dash-konfirm-feedback');
      var passwordForm = document.getElementById('passwordForm');
      if (!newPwInput || !rulesPanel) return;

      var dashRules = {
        length:  { el: document.getElementById('dash-rule-length'),  test: function(v) { return v.length >= 8; } },
        upper:   { el: document.getElementById('dash-rule-upper'),   test: function(v) { return /[A-Z]/.test(v); } },
        lower:   { el: document.getElementById('dash-rule-lower'),   test: function(v) { return /[a-z]/.test(v); } },
        number:  { el: document.getElementById('dash-rule-number'),  test: function(v) { return /[0-9]/.test(v); } },
        special: { el: document.getElementById('dash-rule-special'), test: function(v) { return /[!@#$%^&*]/.test(v); } }
      };

      function cekDashPassword() {
        var val = newPwInput.value;
        if (val.length > 0) {
          rulesPanel.classList.remove('hidden');
        } else {
          rulesPanel.classList.add('hidden');
        }
        for (var key in dashRules) {
          var rule = dashRules[key];
          var ok = rule.test(val);
          var icon = rule.el.querySelector('i');
          rule.el.className = ok
            ? 'flex items-center gap-1.5 text-green-600'
            : 'flex items-center gap-1.5 text-red-500';
          icon.className = ok ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill';
        }
        cekDashKonfirmasi();
      }

      function isDashPasswordValid() {
        for (var key in dashRules) {
          if (!dashRules[key].test(newPwInput.value)) return false;
        }
        return true;
      }

      function cekDashKonfirmasi() {
        if (!confirmPwInput || !konfirmFeedback) return;
        if (confirmPwInput.value && confirmPwInput.value !== newPwInput.value) {
          konfirmFeedback.classList.remove('hidden');
        } else {
          konfirmFeedback.classList.add('hidden');
        }
      }

      newPwInput.addEventListener('input', cekDashPassword);
      if (confirmPwInput) confirmPwInput.addEventListener('input', cekDashKonfirmasi);

      // Submit validation
      if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
          if (!isDashPasswordValid()) {
            e.preventDefault();
            rulesPanel.classList.remove('hidden');
            cekDashPassword();
            newPwInput.focus();
            return;
          }
          if (confirmPwInput && confirmPwInput.value !== newPwInput.value) {
            e.preventDefault();
            konfirmFeedback.classList.remove('hidden');
            confirmPwInput.focus();
            return;
          }
        });
      }
    })();
  </script>
  <?php include '../includes/footer.php'; ?>
</body>

</html>
