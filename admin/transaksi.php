<?php
/**
 * Halaman Manajemen Transaksi - Panel Admin
 *
 * Menampilkan daftar transaksi sewa dari database secara dinamis menggunakan OOP.
 * Menyediakan filter tab status dan detail view modal.
 */

require_once __DIR__ . '/../includes/admin_auth_check.php';

require_once '../classes/Database.php';
require_once '../classes/BaseModel.php';
require_once '../classes/Rental.php';
require_once '../classes/Payment.php';
require_once '../classes/Car.php';
require_once '../classes/Setting.php';
require_once '../classes/BankAccount.php';

$db = Database::getInstance()->getConnection();
$rentalModel = new Rental($db);
$paymentModel = new Payment($db);
$carModel = new Car($db);
$settingModel = new Setting($db);
$bankAccountModel = new BankAccount($db);

// Filter tab aktif halaman transaksi (transaksi, mobil, layanan, rekening)
$activeTab = $_GET['tab'] ?? 'transaksi';
if (isset($_GET['status']) && !isset($_GET['tab'])) {
    $activeTab = 'transaksi';
}

// Ambil data setting penalty rate untuk format javascript
$penaltyRate = (float) $settingModel->getValueByKey('penalty_fee_per_hour', 20000.0);

// Filter status
$statusInput = $_GET['status'] ?? '';
$activeStatus = in_array($statusInput, ['pending', 'confirmed', 'ongoing', 'completed', 'cancelled', 'cancel_requested']) ? $statusInput : '';

// Ambil data rental
$rentals = $rentalModel->getAll($activeStatus);

// List status tabs
$tabs = [
    '' => 'Semua',
    'pending' => 'Pending',
    'confirmed' => 'Confirmed',
    'ongoing' => 'Ongoing',
    'completed' => 'Completed',
    'cancelled' => 'Batal'
];

$activePage = 'transaksi';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Transaksi - RentalQu Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; color: #333; }
        
        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            height: 100vh;
            background: white;
            position: fixed;
            border-right: 1px solid #eee;
            padding: 20px;
            z-index: 1000;
        }
        .nav-link {
            color: #6c757d;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .nav-link:hover, .nav-link.active {
            background: #eef4ff;
            color: #0d6efd;
            font-weight: 600;
        }

        /* Main Content */
        .main-content { 
            margin-left: 260px; 
            min-height: 100vh;
        }
        .top-header {
            background: white;
            padding: 15px 40px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Tabs Filter */
        .status-tabs {
            background-color: #e9ecef;
            padding: 5px;
            border-radius: 12px;
            display: inline-flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .tab-item {
            padding: 8px 16px;
            border-radius: 10px;
            text-decoration: none;
            color: #495057;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .tab-item:hover {
            color: #0d6efd;
        }
        .tab-item.active {
            background-color: white;
            color: #0d6efd;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        /* Table Container */
        .table-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            border: 1px solid #efefef;
        }
        .table thead th {
            border-bottom: 2px solid #f0f0f0;
            color: #555;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 15px;
        }
        .table tbody td {
            padding: 18px 12px;
            border-bottom: 1px solid #f8f9fa;
            vertical-align: middle;
            font-size: 14px;
        }
        .table tbody tr:hover {
            background-color: #f9fbff;
        }

        /* Action Buttons */
        .btn-action {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            text-decoration: none;
            margin-left: 5px;
            transition: background-color 0.2s;
        }
        .btn-action:hover {
            background-color: #f1f3f5;
        }
        .btn-view { color: #0d6efd; }
        .btn-check { color: #198754; }
        .btn-cancel { color: #dc3545; }
        .btn-reject { color: #ffc107; }

        /* Modal Specific Styles */
        .detail-section-title {
            font-size: 14px;
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-bottom: 12px;
        }
        .detail-item {
            margin-bottom: 8px;
        }
        .detail-label {
            font-size: 12px;
            color: #999;
            margin-bottom: 1px;
        }
        .detail-value {
            font-weight: 500;
            color: #333;
        }
        .proof-img-container {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
            background-color: #f8f9fa;
            margin-top: 8px;
        }
        .proof-img {
            max-height: 160px;
            max-width: 100%;
            object-fit: contain;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.2s ease;
        }
        .proof-img:hover {
            transform: scale(1.02);
        }
        
        /* Custom styles for transaction page main tabs */
        .page-tab-link {
            color: #6c757d;
            padding: 10px 22px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            border: 1px solid #dee2e6;
            background: white;
            display: inline-flex;
            align-items: center;
        }
        .page-tab-link:hover {
            color: #0d6efd;
            background-color: #f8f9fa;
            border-color: #0d6efd;
        }
        .page-tab-link.active {
            color: white;
            background-color: #0d6efd;
            border-color: #0d6efd;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <?php include '../includes/sidebar_admin.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div></div>
            <div class="d-flex align-items-center">
                <div class="text-end me-3">
                    <p class="mb-0 fw-bold"><?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></p>
                    <small class="text-muted">Administrator</small>
                </div>
                <a href="../handlers/auth_handler.php?action=logout" class="text-muted text-decoration-none" title="Logout">
                    <i class="bi bi-box-arrow-right fs-4"></i>
                </a>
            </div>
        </header>

        <div class="p-5">
            <!-- Title Section -->
            <div class="mb-4">
                <h1 class="fw-bold mb-1">Manajemen Transaksi</h1>
                <p class="text-muted">Kelola pesanan, pembayaran, dan status sewa kendaraan</p>
            </div>

            <!-- Notifikasi Alert -->
            <?php if (isset($_GET['status']) && in_array($_GET['status'], ['success', 'error']) && isset($_GET['msg'])): ?>
                <div class="alert alert-<?= $_GET['status'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi <?= $_GET['status'] === 'success' ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger' ?> me-2 fs-5"></i>
                        <div><?= htmlspecialchars($_GET['msg']) ?></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Main Page Tabs -->
            <div class="d-flex gap-2 border-bottom pb-3 mb-4 flex-wrap">
                <a href="transaksi.php?tab=transaksi" class="page-tab-link <?= $activeTab === 'transaksi' ? 'active' : '' ?>">
                    <i class="bi bi-list-task me-2"></i>Daftar Transaksi
                </a>
                <a href="transaksi.php?tab=mobil" class="page-tab-link <?= $activeTab === 'mobil' ? 'active' : '' ?>">
                    <i class="bi bi-car-front me-2"></i>Harga Sewa Mobil
                </a>
                <a href="transaksi.php?tab=layanan" class="page-tab-link <?= $activeTab === 'layanan' ? 'active' : '' ?>">
                    <i class="bi bi-gear me-2"></i>Harga Driver &amp; Layanan
                </a>
                <a href="transaksi.php?tab=rekening" class="page-tab-link <?= $activeTab === 'rekening' ? 'active' : '' ?>">
                    <i class="bi bi-credit-card me-2"></i>Rekening Bank Transfer
                </a>
            </div>

            <?php if ($activeTab === 'transaksi'): ?>
                <!-- Tabs Filter -->
                <div class="status-tabs mb-4 shadow-sm">
                    <?php foreach ($tabs as $statusVal => $label): 
                        $count = $rentalModel->countByStatus($statusVal);
                        $activeClass = ($activeStatus === $statusVal) ? 'active' : '';
                        $url = 'transaksi.php?tab=transaksi' . (!empty($statusVal) ? '&status=' . $statusVal : '');
                    ?>
                        <a href="<?= $url ?>" class="tab-item <?= $activeClass ?>"><?= $label ?> (<?= $count ?>)</a>
                    <?php endforeach; ?>
                </div>

            <!-- Table Section -->
            <div class="table-container shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">
                        Daftar Transaksi
                        <span class="badge bg-light text-secondary fw-normal ms-1"><?= count($rentals) ?></span>
                    </h5>
                </div>
                
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>ID Pesanan</th>
                                <th>Customer</th>
                                <th>Mobil</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th class="text-end" width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rentals)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-cash-stack fs-1 d-block mb-3 text-secondary"></i>
                                        Tidak ada transaksi yang ditemukan.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($rentals as $r): ?>
                                    <?php
                                    // Fetch payment details for JavaScript modal usage
                                    $p = $paymentModel->getByRentalId($r['id']);
                                    $hasPayment = $p ? 'true' : 'false';
                                    $paymentMethod = $p ? $p['method'] : '';
                                    $paymentStatus = $p ? $p['status'] : '';
                                    $paymentBank = $p ? ($p['bank_name'] . ' - ' . $p['account_number'] . ' a/n ' . $p['account_holder']) : '';
                                    $paymentProof = $p ? $p['proof_image'] : '';
                                    $paymentPaidAt = $p && $p['paid_at'] ? date('d M Y H:i', strtotime($p['paid_at'])) : '';

                                    // Fetch refund data jika ada
                                    $refund = null;
                                    if (in_array($r['status'], ['cancel_requested', 'cancelled'])) {
                                        $stmtRefund = $db->prepare("SELECT * FROM refunds WHERE rental_id = ? ORDER BY requested_at DESC LIMIT 1");
                                        $stmtRefund->execute([$r['id']]);
                                        $refund = $stmtRefund->fetch(PDO::FETCH_ASSOC);
                                    }
                                    
                                    // Duration calculation
                                    $days = (int) (strtotime($r['end_date']) - strtotime($r['start_date'])) / 86400;
                                    if ($days <= 0) $days = 1;

                                    // Hitung biaya sewa dasar (harga mobil × hari)
                                    $baseCost = (float)$r['price_per_day'] * $days;

                                    // Hitung info denda untuk rental yang sedang berjalan
                                    $penaltyInfo = ['deadline' => null, 'late_hours' => 0, 'penalty_fee' => 0, 'is_overdue' => false];
                                    if ($r['status'] === 'ongoing' && !empty($r['started_at'])) {
                                        $penaltyInfo = $rentalModel->calculatePenalty($r);
                                    }
                                    ?>
                                    <tr>
                                        <td class="fw-bold text-muted">#TRX-<?= str_pad($r['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($r['user_name']) ?></div>
                                            <div class="text-muted small"><?= htmlspecialchars($r['user_email']) ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($r['brand'] . ' ' . $r['model']) ?></div>
                                            <div class="text-muted small"><?= date('d M Y', strtotime($r['start_date'])) ?> - <?= date('d M Y', strtotime($r['end_date'])) ?> (<?= $days ?> Hari)</div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-primary">Rp <?= number_format($r['total_price'], 0, ',', '.') ?></div>
                                            <div class="text-muted small"><?= $r['rental_type'] === 'with_driver' ? 'Dengan Supir' : 'Lepas Kunci' ?></div>
                                        </td>
                                        <td>
                                            <?php
                                            $badgeClasses = [
                                                'pending' => 'bg-warning text-dark',
                                                'confirmed' => 'bg-primary text-white',
                                                'ongoing' => 'bg-info text-dark',
                                                'completed' => 'bg-success text-white',
                                                'cancel_requested' => 'bg-danger text-white',
                                                'cancelled' => 'bg-secondary text-white'
                                            ];
                                            $statusLabels = [
                                                'pending' => 'Pending',
                                                'confirmed' => 'Confirmed',
                                                'ongoing' => 'Ongoing',
                                                'completed' => 'Completed',
                                                'cancel_requested' => 'Cancel Req',
                                                'cancelled' => 'Cancelled'
                                            ];
                                            $badgeClass = $badgeClasses[$r['status']] ?? 'bg-dark text-white';
                                            $statusLabel = $statusLabels[$r['status']] ?? $r['status'];
                                            ?>
                                            <span class="badge <?= $badgeClass ?> px-3 py-2 rounded-pill"><?= $statusLabel ?></span>
                                            <?php if ($penaltyInfo['is_overdue']): ?>
                                                <br><span class="badge bg-danger px-2 py-1 rounded-pill mt-1" style="font-size: 11px;"><i class="bi bi-exclamation-triangle me-1"></i>Terlambat <?= $penaltyInfo['late_hours'] ?> Jam</span>
                                            <?php endif; ?>
                                            <?php if ($penaltyInfo['is_overdue'] || ($r['status'] === 'completed' && (float)$r['penalty_fee'] > 0)): ?>
                                                <br><span class="badge bg-danger px-2 py-1 rounded-pill mt-1" style="font-size: 11px;"><i class="bi bi-cash me-1"></i>Denda: Rp <?= number_format($r['status'] === 'completed' ? $r['penalty_fee'] : $penaltyInfo['penalty_fee'], 0, ',', '.') ?></span>
                                                <?php
                                                $pStatusBadge = match($r['penalty_payment_status']) {
                                                    'unpaid' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                                    'pending' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                                    'confirmed' => 'bg-success-subtle text-success-emphasis border border-success-subtle',
                                                    'rejected' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                                    default => 'bg-secondary-subtle text-secondary-emphasis'
                                                };
                                                $pStatusLabel = match($r['penalty_payment_status']) {
                                                    'unpaid' => 'Denda: Belum Dibayar',
                                                    'pending' => 'Denda: Pending Verif',
                                                    'confirmed' => 'Denda: Lunas',
                                                    'rejected' => 'Denda: Ditolak',
                                                    default => 'Denda: Unknown'
                                                };
                                                ?>
                                                <br><span class="badge <?= $pStatusBadge ?> px-2 py-1 rounded-pill mt-1" style="font-size: 10px;"><?= $pStatusLabel ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <!-- Detail Button -->
                                            <button type="button" class="btn btn-outline-primary btn-sm btn-show-detail d-inline-flex align-items-center" title="Detail"
                                               data-id="<?= $r['id'] ?>"
                                               data-trx="#TRX-<?= str_pad($r['id'], 4, '0', STR_PAD_LEFT) ?>"
                                               data-cust-name="<?= htmlspecialchars($r['user_name']) ?>"
                                               data-cust-email="<?= htmlspecialchars($r['user_email']) ?>"
                                               data-cust-nik="<?= htmlspecialchars($r['user_nik'] ?? '-') ?>"
                                               data-cust-phone="<?= htmlspecialchars($r['user_phone'] ?? '-') ?>"
                                               data-cust-address="<?= htmlspecialchars($r['user_address'] ?? '-') ?>"
                                               data-car-name="<?= htmlspecialchars($r['brand'] . ' ' . $r['model'] . ' (' . $r['year'] . ')') ?>"
                                               data-car-spec="<?= htmlspecialchars(($r['engine_type'] ?? '-') . ' • ' . ucfirst($r['transmission']) . ' • ' . $r['passenger_capacity'] . ' Kursi') ?>"
                                               data-car-price="Rp <?= number_format($r['price_per_day'], 0, ',', '.') ?>"
                                               data-rent-type="<?= $r['rental_type'] === 'with_driver' ? 'Dengan Supir' : 'Lepas Kunci' ?>"
                                               data-rent-dates="<?= date('d M Y', strtotime($r['start_date'])) ?> s/d <?= date('d M Y', strtotime($r['end_date'])) ?>"
                                               data-rent-duration="<?= $days ?> Hari"
                                               data-pickup="<?= htmlspecialchars($r['pickup_location']) ?>"
                                               data-dropoff="<?= htmlspecialchars($r['dropoff_location']) ?>"
                                               data-cost-base="Rp <?= number_format($baseCost, 0, ',', '.') ?>"
                                               data-cost-driver="Rp <?= number_format($r['driver_cost'], 0, ',', '.') ?>"
                                               data-cost-pickup="Rp <?= number_format($r['pickup_fee'] ?? 0, 0, ',', '.') ?>"
                                               data-cost-dropoff="Rp <?= number_format($r['dropoff_fee'] ?? 0, 0, ',', '.') ?>"
                                               data-cost-penalty="Rp <?= number_format($r['status'] === 'completed' ? ($r['penalty_fee'] ?? 0) : $penaltyInfo['penalty_fee'], 0, ',', '.') ?>"
                                               data-cost-base-raw="<?= $baseCost ?>"
                                               data-cost-driver-raw="<?= (float)$r['driver_cost'] ?>"
                                               data-cost-pickup-raw="<?= (float)($r['pickup_fee'] ?? 0) ?>"
                                               data-cost-dropoff-raw="<?= (float)($r['dropoff_fee'] ?? 0) ?>"
                                               data-cost-penalty-raw="<?= $r['status'] === 'completed' ? (float)($r['penalty_fee'] ?? 0) : (float)$penaltyInfo['penalty_fee'] ?>"
                                               data-cost-total="Rp <?= number_format($r['total_price'], 0, ',', '.') ?>"
                                               data-penalty-hours="<?= $r['status'] === 'completed' ? (((float)($r['penalty_fee'] ?? 0) > 0) ? (int)(($r['penalty_fee'] ?? 0) / $penaltyRate) : 0) : $penaltyInfo['late_hours'] ?>"
                                               data-is-overdue="<?= $penaltyInfo['is_overdue'] ? 'true' : 'false' ?>"
                                               data-penalty-status="<?= htmlspecialchars($r['penalty_payment_status']) ?>"
                                               data-penalty-proof="<?= htmlspecialchars($r['penalty_proof_image'] ?? '') ?>"
                                               data-started-at="<?= !empty($r['started_at']) ? date('d M Y H:i', strtotime($r['started_at'])) : '' ?>"
                                               data-deadline="<?= $penaltyInfo['deadline'] ? date('d M Y H:i', strtotime($penaltyInfo['deadline'])) : (!empty($r['started_at']) ? date('d M Y H:i', strtotime($r['started_at']) + ($days * 86400)) : '') ?>"
                                               data-actual-return="<?= !empty($r['actual_return_at']) ? date('d M Y H:i', strtotime($r['actual_return_at'])) : '' ?>"
                                               data-status="<?= $r['status'] ?>"
                                               data-has-payment="<?= $hasPayment ?>"
                                               data-pay-method="<?= htmlspecialchars($paymentMethod) ?>"
                                               data-pay-status="<?= htmlspecialchars($paymentStatus) ?>"
                                               data-pay-bank="<?= htmlspecialchars($paymentBank) ?>"
                                               data-pay-proof="<?= htmlspecialchars($paymentProof) ?>"
                                               data-pay-paidat="<?= htmlspecialchars($paymentPaidAt) ?>"
                                               data-refund-reason="<?= htmlspecialchars($refund['reason_option'] ?? '') ?>"
                                               data-refund-detail="<?= htmlspecialchars($refund['reason_detail'] ?? '') ?>"
                                               data-refund-status="<?= htmlspecialchars($refund['status'] ?? '') ?>"
                                               data-refund-date="<?= $refund ? date('d M Y H:i', strtotime($refund['requested_at'])) : '' ?>"
                                               data-refund-proof="<?= htmlspecialchars($refund['refund_proof_image'] ?? '') ?>"
                                               data-refund-bank-name="<?= htmlspecialchars($refund['refund_bank_name'] ?? '') ?>"
                                               data-refund-account-number="<?= htmlspecialchars($refund['refund_account_number'] ?? '') ?>"
                                               data-refund-account-holder="<?= htmlspecialchars($refund['refund_account_holder'] ?? '') ?>"
                                            >
                                               <i class="bi bi-eye me-1"></i> Detail
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php elseif ($activeTab === 'mobil'): ?>
                <!-- TAMPILAN 2: UPDATE HARGA SEWA MOBIL -->
                <?php
                $cars = $carModel->getAll();
                ?>
                <div class="table-container shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">
                            Daftar Harga Sewa Mobil
                            <span class="badge bg-light text-secondary fw-normal ms-1"><?= count($cars) ?></span>
                        </h5>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Mobil</th>
                                    <th>Tipe &amp; Transmisi</th>
                                    <th>Kapasitas</th>
                                    <th>Harga Sewa Saat Ini</th>
                                    <th width="300">Update Harga Baru</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($cars)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bi bi-car-front fs-1 d-block mb-3 text-secondary"></i>
                                            Tidak ada armada mobil ditemukan.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($cars as $c): ?>
                                        <?php
                                        $carImg = !empty($c['image'])
                                            ? '../assets/uploads/cars/' . htmlspecialchars($c['image'])
                                            : 'https://placehold.co/120x80/1e2a3a/7eb3f5?text=' . urlencode($c['brand']);
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= $carImg ?>" alt="<?= htmlspecialchars($c['brand']) ?>" class="rounded me-3" style="width: 80px; height: 50px; object-fit: cover;">
                                                    <div>
                                                        <div class="fw-bold"><?= htmlspecialchars($c['brand'] . ' ' . $c['model']) ?></div>
                                                        <div class="text-muted small">Tahun <?= $c['year'] ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold"><?= htmlspecialchars($c['type_name'] ?? 'Lainnya') ?></div>
                                                <div class="text-muted small text-capitalize"><?= htmlspecialchars($c['transmission']) ?></div>
                                            </td>
                                            <td>
                                                <div class="text-secondary"><?= $c['passenger_capacity'] ?> Penumpang</div>
                                                <div class="text-muted small"><?= $c['luggage_capacity'] ?> Koper</div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-primary">Rp <?= number_format($c['price_per_day'], 0, ',', '.') ?></div>
                                            </td>
                                            <td>
                                                <form action="../handlers/car_handler.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="update_car_price">
                                                    <input type="hidden" name="car_id" value="<?= $c['id'] ?>">
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light text-secondary border-end-0" style="border-radius:10px 0 0 10px;">Rp</span>
                                                        <input type="number" name="price_per_day" class="form-control border-start-0 ps-1" value="<?= (int)$c['price_per_day'] ?>" required min="1000" style="border-radius:0; max-width: 130px;">
                                                        <button type="submit" class="btn btn-primary px-3 fw-semibold" style="border-radius:0 10px 10px 0;">
                                                            <i class="bi bi-save me-1"></i> Simpan
                                                        </button>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($activeTab === 'layanan'): ?>
                <!-- TAMPILAN 3: UPDATE PENGATURAN BIAYA DRIVER & LAYANAN -->
                <?php
                $driverCost = $settingModel->getValueByKey('driver_cost_per_day', 200000);
                $pickupFee = $settingModel->getValueByKey('pickup_fee', 50000);
                $dropoffFee = $settingModel->getValueByKey('dropoff_fee', 50000);
                $penaltyFee = $settingModel->getValueByKey('penalty_fee_per_hour', 20000);
                ?>
                <div class="row">
                    <div class="col-lg-8 col-md-10">
                        <div class="card border-0 rounded-4 shadow-sm">
                            <div class="card-header bg-primary text-white p-4 rounded-top-4 border-0">
                                <h5 class="fw-bold mb-1"><i class="bi bi-gear-fill me-2"></i>Pengaturan Biaya Layanan</h5>
                                <p class="mb-0 text-white-50 small">Atur tarif biaya supir, pick up, drop off, dan denda keterlambatan secara global.</p>
                            </div>
                            <div class="card-body p-5">
                                <form action="../handlers/setting_handler.php" method="POST">
                                    <input type="hidden" name="action" value="update_settings">
                                    
                                    <!-- Harga Driver -->
                                    <div class="mb-4">
                                        <label for="driver_cost_per_day" class="form-label fw-semibold text-secondary">Biaya Supir per Hari</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0" style="border-radius: 12px 0 0 12px;"><i class="bi bi-person-badge text-muted me-1"></i> Rp</span>
                                            <input type="number" id="driver_cost_per_day" name="driver_cost_per_day" class="form-control border-start-0 ps-1" value="<?= (int)$driverCost ?>" required min="0" style="border-radius: 0;">
                                            <span class="input-group-text bg-light text-muted border-start-0" style="border-radius: 0 12px 12px 0;">/ Hari</span>
                                        </div>
                                        <div class="form-text text-muted">Biaya harian tambahan jika pelanggan menyewa dengan supir.</div>
                                    </div>
                                    
                                    <!-- Biaya Pick Up -->
                                    <div class="mb-4">
                                        <label for="pickup_fee" class="form-label fw-semibold text-secondary">Biaya Pick Up (Antar Mobil)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0" style="border-radius: 12px 0 0 12px;"><i class="bi bi-geo-alt text-muted me-1"></i> Rp</span>
                                            <input type="number" id="pickup_fee" name="pickup_fee" class="form-control border-start-0 ps-1" value="<?= (int)$pickupFee ?>" required min="0" style="border-radius: 0 12px 12px 0;">
                                        </div>
                                        <div class="form-text text-muted">Tarif pengantaran mobil lepas kunci ke lokasi pelanggan.</div>
                                    </div>
                                    
                                    <!-- Biaya Drop Off -->
                                    <div class="mb-4">
                                        <label for="dropoff_fee" class="form-label fw-semibold text-secondary">Biaya Drop Off (Ambil Mobil)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0" style="border-radius: 12px 0 0 12px;"><i class="bi bi-signpost text-muted me-1"></i> Rp</span>
                                            <input type="number" id="dropoff_fee" name="dropoff_fee" class="form-control border-start-0 ps-1" value="<?= (int)$dropoffFee ?>" required min="0" style="border-radius: 0 12px 12px 0;">
                                        </div>
                                        <div class="form-text text-muted">Tarif penjemputan mobil lepas kunci kembali dari lokasi pelanggan.</div>
                                    </div>
                                    
                                    <!-- Biaya Denda -->
                                    <div class="mb-5">
                                        <label for="penalty_fee_per_hour" class="form-label fw-semibold text-secondary">Biaya Denda Keterlambatan</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0" style="border-radius: 12px 0 0 12px;"><i class="bi bi-clock-history text-muted me-1"></i> Rp</span>
                                            <input type="number" id="penalty_fee_per_hour" name="penalty_fee_per_hour" class="form-control border-start-0 ps-1" value="<?= (int)$penaltyFee ?>" required min="0" style="border-radius: 0;">
                                            <span class="input-group-text bg-light text-muted border-start-0" style="border-radius: 0 12px 12px 0;">/ Jam</span>
                                        </div>
                                        <div class="form-text text-muted">Tarif denda keterlambatan per jam saat mobil dikembalikan melewati batas waktu.</div>
                                    </div>
                                    
                                    <!-- Submit Button -->
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <button type="submit" class="btn btn-primary btn-lg px-5 fw-semibold rounded-3 shadow-sm">
                                            <i class="bi bi-check-circle me-1"></i> Simpan Pengaturan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ($activeTab === 'rekening'): ?>
                <!-- TAMPILAN 4: PENGATURAN REKENING TRANSFER ADMIN -->
                <?php
                $bankAccounts = $bankAccountModel->getAll();
                ?>
                <div class="table-container shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">
                            Daftar Rekening Transfer Admin
                            <span class="badge bg-light text-secondary fw-normal ms-1"><?= count($bankAccounts) ?></span>
                        </h5>
                        <button type="button" class="btn btn-primary btn-sm fw-semibold px-3 py-2 rounded-3" data-bs-toggle="modal" data-bs-target="#modalAddBankAccount">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Rekening
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Nama Bank</th>
                                    <th>Nomor Rekening</th>
                                    <th>Nama Pemilik</th>
                                    <th>Status</th>
                                    <th class="text-end" width="180">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($bankAccounts)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bi bi-credit-card fs-1 d-block mb-3 text-secondary"></i>
                                            Belum ada rekening bank yang ditambahkan.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($bankAccounts as $bank): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-[#0b2b4a]"><?= htmlspecialchars($bank['bank_name']) ?></div>
                                            </td>
                                            <td>
                                                <div class="font-monospace fw-semibold"><?= htmlspecialchars($bank['account_number']) ?></div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-secondary"><?= htmlspecialchars($bank['account_holder']) ?></div>
                                            </td>
                                            <td>
                                                <?php if ($bank['is_active'] == 1): ?>
                                                    <span class="badge bg-success px-2 py-1 rounded-pill">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary px-2 py-1 rounded-pill">Nonaktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <a href="../handlers/bank_account_handler.php?action=toggle_bank_account_status&id=<?= $bank['id'] ?>" class="btn-action <?= $bank['is_active'] == 1 ? 'text-warning' : 'text-success' ?>" title="<?= $bank['is_active'] == 1 ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                                    <i class="bi <?= $bank['is_active'] == 1 ? 'bi-toggle-on' : 'bi-toggle-off' ?> fs-5"></i>
                                                </a>
                                                <button type="button" class="btn-action btn-view btn-edit-bank" title="Edit Rekening"
                                                        data-id="<?= $bank['id'] ?>"
                                                        data-bank-name="<?= htmlspecialchars($bank['bank_name']) ?>"
                                                        data-account-number="<?= htmlspecialchars($bank['account_number']) ?>"
                                                        data-account-holder="<?= htmlspecialchars($bank['account_holder']) ?>"
                                                        data-is-active="<?= $bank['is_active'] ?>">
                                                    <i class="bi bi-pencil-square fs-5"></i>
                                                </button>
                                                <a href="../handlers/bank_account_handler.php?action=delete_bank_account&id=<?= $bank['id'] ?>" class="btn-action btn-cancel" title="Hapus Rekening" onclick="return confirm('Apakah Anda yakin ingin menghapus rekening bank ini?');">
                                                    <i class="bi bi-trash fs-5"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ==========================================
    MODAL DETAIL TRANSAKSI
    ========================================== -->
    <div class="modal fade" id="modalDetailRental" tabindex="-1" aria-labelledby="modalDetailRentalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <h5 class="modal-title fw-bold" id="modalDetailRentalLabel">
                        <i class="bi bi-file-earmark-text text-primary me-2"></i>Detail Transaksi <span id="lbl-trx-id" class="text-muted">#TRX-0000</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Column 1: Customer and Car Spec -->
                        <div class="col-lg-4 col-md-6">
                            <!-- Customer Info -->
                            <div class="detail-section-title"><i class="bi bi-person me-2"></i>Data Penyewa</div>
                            <div class="row">
                                <div class="col-12 detail-item">
                                    <div class="detail-label">Nama Lengkap</div>
                                    <div id="det-cust-name" class="detail-value">-</div>
                                </div>
                                <div class="col-12 detail-item">
                                    <div class="detail-label">NIK</div>
                                    <div id="det-cust-nik" class="detail-value">-</div>
                                </div>
                                <div class="col-12 detail-item">
                                    <div class="detail-label">Email</div>
                                    <div id="det-cust-email" class="detail-value text-break">-</div>
                                </div>
                                <div class="col-12 detail-item">
                                    <div class="detail-label">Nomor HP</div>
                                    <div id="det-cust-phone" class="detail-value">-</div>
                                </div>
                                <div class="col-12 detail-item">
                                    <div class="detail-label">Alamat Lengkap</div>
                                    <div id="det-cust-address" class="detail-value">-</div>
                                </div>
                            </div>

                            <!-- Car Spec -->
                            <div class="detail-section-title mt-4"><i class="bi bi-car-front me-2"></i>Spesifikasi Armada</div>
                            <div class="row">
                                <div class="col-12 detail-item">
                                    <div class="detail-label">Nama Kendaraan</div>
                                    <div id="det-car-name" class="detail-value">-</div>
                                </div>
                                <div class="col-sm-6 col-12 detail-item">
                                    <div class="detail-label">Spesifikasi Mesin & Transmisi</div>
                                    <div id="det-car-spec" class="detail-value">-</div>
                                </div>
                                <div class="col-sm-6 col-12 detail-item">
                                    <div class="detail-label">Harga Sewa / Hari</div>
                                    <div id="det-car-price" class="detail-value">-</div>
                                </div>
                            </div>
                        </div>

                        <!-- Column 2: Rental info and Timing Info -->
                        <div class="col-lg-4 col-md-6">
                            <!-- Rental Info -->
                            <div class="detail-section-title"><i class="bi bi-calendar-event me-2"></i>Detail Penyewaan</div>
                            <div class="row">
                                <div class="col-sm-6 col-12 detail-item">
                                    <div class="detail-label">Tipe Sewa</div>
                                    <div id="det-rent-type" class="detail-value">-</div>
                                </div>
                                <div class="col-sm-6 col-12 detail-item">
                                    <div class="detail-label">Durasi</div>
                                    <div id="det-rent-duration" class="detail-value">-</div>
                                </div>
                                <div class="col-12 detail-item">
                                    <div class="detail-label">Masa Sewa</div>
                                    <div id="det-rent-dates" class="detail-value">-</div>
                                </div>
                                <div class="col-12 detail-item">
                                    <div class="detail-label font-semibold">Lokasi Pengambilan (Pickup)</div>
                                    <div id="det-pickup" class="detail-value text-secondary">-</div>
                                </div>
                                <div class="col-12 detail-item">
                                    <div class="detail-label font-semibold">Lokasi Pengembalian (Dropoff)</div>
                                    <div id="det-dropoff" class="detail-value text-secondary">-</div>
                                </div>
                            </div>

                            <!-- Waktu Sewa (hanya tampil jika sudah started) -->
                            <div id="det-timing-section" class="d-none">
                                <div class="detail-section-title mt-4"><i class="bi bi-clock-history me-2"></i>Waktu Sewa</div>
                                <div class="row">
                                    <div class="col-sm-6 col-12 detail-item">
                                        <div class="detail-label">Mulai Sewa</div>
                                        <div id="det-started-at" class="detail-value">-</div>
                                    </div>
                                    <div class="col-sm-6 col-12 detail-item">
                                        <div class="detail-label">Batas Pengembalian</div>
                                        <div id="det-deadline" class="detail-value">-</div>
                                    </div>
                                    <div id="det-return-row" class="col-sm-6 col-12 detail-item d-none">
                                        <div class="detail-label">Waktu Dikembalikan</div>
                                        <div id="det-actual-return" class="detail-value">-</div>
                                    </div>
                                    <div id="det-overdue-row" class="col-sm-6 col-12 detail-item d-none">
                                        <div class="detail-label">Status Waktu</div>
                                        <div id="det-overdue-status" class="detail-value">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Column 3: Billing & Cost -->
                        <div class="col-lg-4 col-md-12">
                            <!-- Cost & Billing -->
                            <div class="detail-section-title"><i class="bi bi-wallet2 me-2"></i>Rincian Biaya</div>
                            <div class="bg-light p-3 rounded-3 border">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted small">Biaya Sewa Mobil</span>
                                    <span id="det-cost-base" class="fw-semibold small">-</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted small">Biaya Supir</span>
                                    <span id="det-cost-driver" class="fw-semibold small">-</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted small">Biaya Pickup (Antar)</span>
                                    <span id="det-cost-pickup" class="fw-semibold small">-</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Biaya Dropoff (Ambil)</span>
                                    <span id="det-cost-dropoff" class="fw-semibold small">-</span>
                                </div>
                                <div id="det-penalty-row" class="d-flex justify-content-between mb-2 d-none">
                                    <span class="text-danger small fw-semibold"><i class="bi bi-exclamation-triangle me-1"></i>Denda Keterlambatan <span id="det-penalty-detail" class="fw-normal"></span></span>
                                    <span id="det-cost-penalty" class="fw-bold small text-danger">-</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">Total Pembayaran</span>
                                    <h5 class="fw-bold text-primary mb-0" id="det-cost-total">Rp 0</h5>
                                </div>
                                <div class="mt-2">
                                    <span class="detail-label">Status Rental</span>
                                    <span id="det-status-badge" class="badge rounded-pill ms-2">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Proof section (full width at bottom) -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="detail-section-title"><i class="bi bi-credit-card me-2"></i>Informasi & Bukti Pembayaran</div>
                            <div id="pay-info-missing" class="text-muted py-3 bg-light rounded-3 text-center border d-none">
                                <i class="bi bi-info-circle me-1"></i>Belum ada informasi pembayaran.
                            </div>
                            <div id="pay-info-container" class="row">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-6 col-12 detail-item">
                                            <div class="detail-label">Metode Pembayaran</div>
                                            <div id="det-pay-method" class="detail-value text-uppercase">-</div>
                                        </div>
                                        <div class="col-sm-6 col-12 detail-item">
                                            <div class="detail-label">Status Verifikasi</div>
                                            <div id="det-pay-status" class="detail-value">-</div>
                                        </div>
                                        <div class="col-12 detail-item mt-2">
                                            <div class="detail-label">Rekening Bank Tujuan</div>
                                            <div id="det-pay-bank" class="detail-value">-</div>
                                        </div>
                                        <div class="col-12 detail-item">
                                            <div class="detail-label">Waktu Konfirmasi Bayar</div>
                                            <div id="det-pay-paidat" class="detail-value">-</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 text-center mt-3 mt-md-0">
                                    <div class="detail-label text-start">Bukti Transfer</div>
                                    <div id="pay-proof-missing" class="py-4 bg-light rounded-3 text-muted border text-center small mt-2">
                                        <i class="bi bi-image me-1"></i>Bukti transfer belum diunggah.
                                    </div>
                                    <div id="pay-proof-image-container" class="proof-img-container d-none">
                                        <a id="det-pay-proof-link" href="#" target="_blank" title="Klik untuk memperbesar">
                                            <img id="det-pay-proof-img" class="proof-img" src="" alt="Bukti Transfer">
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Penalty Payment Proof Section (hanya tampil jika ada denda) -->
                    <div class="row mt-4 d-none" id="penalty-proof-section">
                        <div class="col-12">
                            <div class="detail-section-title"><i class="bi bi-wallet2 text-danger me-2"></i>Informasi & Bukti Pembayaran Denda</div>
                            <div class="row bg-red-50/50 p-3 rounded-3 border border-danger-subtle g-3">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-12 detail-item">
                                            <div class="detail-label">Status Pembayaran Denda</div>
                                            <div id="det-penalty-pay-status" class="fw-bold">-</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 text-center mt-3 mt-md-0">
                                    <div class="detail-label text-start">Bukti Transfer Denda</div>
                                    <div id="penalty-proof-missing" class="py-4 bg-light rounded-3 text-muted border text-center small mt-2 d-none">
                                        <i class="bi bi-image me-1"></i>Bukti transfer denda belum diunggah.
                                    </div>
                                    <div id="penalty-proof-image-container" class="proof-img-container d-none">
                                        <a id="det-penalty-proof-link" href="#" target="_blank" title="Klik untuk memperbesar">
                                            <img id="det-penalty-proof-img" class="proof-img" src="" alt="Bukti Transfer Denda" style="max-height: 150px; object-fit: contain;">
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Refund Info Section (hanya tampil jika ada data refund) -->
                    <div class="row mt-4" id="refund-info-section" style="display:none;">
                        <div class="col-12">
                            <div class="detail-section-title"><i class="bi bi-arrow-return-left me-2"></i>Informasi & Bukti Refund</div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-12 detail-item">
                                            <div class="detail-label">Alasan Pembatalan</div>
                                            <div id="det-refund-reason" class="detail-value">-</div>
                                        </div>
                                        <div class="col-12 detail-item" id="det-refund-detail-row">
                                            <div class="detail-label">Detail Alasan</div>
                                            <div id="det-refund-detail" class="detail-value">-</div>
                                        </div>
                                        <div class="col-sm-6 col-12 detail-item">
                                            <div class="detail-label">Status Refund</div>
                                            <div id="det-refund-status" class="detail-value">-</div>
                                        </div>
                                        <div class="col-sm-6 col-12 detail-item">
                                            <div class="detail-label">Tanggal Pengajuan</div>
                                            <div id="det-refund-date" class="detail-value">-</div>
                                        </div>
                                    </div>
                                    <!-- Rekening Tujuan Refund -->
                                    <div class="mt-3 pt-3 border-top" id="det-refund-bank-section">
                                        <div class="detail-label fw-semibold text-primary mb-2"><i class="bi bi-bank me-1"></i>Rekening Tujuan Refund Pelanggan</div>
                                        <div class="row">
                                            <div class="col-12 detail-item">
                                                <div class="detail-label">Nama Nasabah</div>
                                                <div id="det-refund-account-holder" class="detail-value">-</div>
                                            </div>
                                            <div class="col-sm-6 col-12 detail-item">
                                                <div class="detail-label">Nama Bank</div>
                                                <div id="det-refund-bank-name" class="detail-value">-</div>
                                            </div>
                                            <div class="col-sm-6 col-12 detail-item">
                                                <div class="detail-label">Nomor Rekening</div>
                                                <div id="det-refund-account-number" class="detail-value fw-bold font-monospace">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-3 mt-md-0">
                                    <!-- Bukti Refund (jika sudah diupload admin) -->
                                    <div class="detail-label text-start">Bukti Transfer Refund</div>
                                    <div id="refund-proof-missing" class="py-4 bg-light rounded-3 text-muted border text-center small mt-2">
                                        <i class="bi bi-image me-1"></i>Bukti transfer refund belum diunggah oleh admin.
                                    </div>
                                    <div id="refund-proof-image-container" class="proof-img-container d-none">
                                        <a id="det-refund-proof-link" href="#" target="_blank" title="Klik untuk memperbesar">
                                            <img id="det-refund-proof-img" class="proof-img" src="" alt="Bukti Transfer Refund">
                                        </a>
                                    </div>
                                    <!-- Form Upload Bukti Refund (hanya tampil jika cancel_requested & belum ada bukti) -->
                                    <div id="refund-upload-section" class="mt-3 d-none">
                                        <form id="formUploadRefundProof" action="../handlers/rental_handler.php" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="action" value="approve_refund">
                                            <input type="hidden" name="rental_id" id="refund-upload-rental-id" value="">
                                            <div class="mb-2">
                                                <label class="form-label fw-semibold text-danger small"><i class="bi bi-upload me-1"></i>Upload Bukti Transfer Refund <span class="text-danger">*</span></label>
                                                <input type="file" name="refund_proof" class="form-control form-control-sm" accept="image/jpeg,image/jpg,image/png" required id="inputRefundProof">
                                                <div class="form-text text-muted">Format: JPG, JPEG, PNG. Maks 5MB.</div>
                                            </div>
                                            <!-- Preview gambar -->
                                            <div id="refund-proof-preview" class="mb-2 d-none text-center">
                                                <img id="refund-proof-preview-img" src="" alt="Preview" class="proof-img" style="max-height:120px;">
                                            </div>
                                            <button type="submit" class="btn btn-danger btn-sm w-100 fw-semibold rounded-3" id="btnSubmitRefundProof">
                                                <i class="bi bi-check-circle me-1"></i>Setujui Refund & Upload Bukti
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Dynamic Footer Actions inside the Modal -->
                <div class="modal-footer border-0 bg-light p-4 rounded-bottom-4 gap-2" id="modal-footer-actions">
                    <!-- Buttons will be generated dynamically by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const penaltyRate = <?= $penaltyRate ?>;
        function formatRupiah(num) {
            return Math.round(num).toLocaleString('id-ID');
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Modal Detail Logic
            const detailModalEl = document.getElementById('modalDetailRental');
            const detailModal = new bootstrap.Modal(detailModalEl);

            const detailButtons = document.querySelectorAll('.btn-show-detail');

            detailButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    const trx = this.getAttribute('data-trx');
                    const custName = this.getAttribute('data-cust-name');
                    const custEmail = this.getAttribute('data-cust-email');
                    const custNik = this.getAttribute('data-cust-nik');
                    const custPhone = this.getAttribute('data-cust-phone');
                    const custAddress = this.getAttribute('data-cust-address');
                    
                    const carName = this.getAttribute('data-car-name');
                    const carSpec = this.getAttribute('data-car-spec');
                    const carPrice = this.getAttribute('data-car-price');

                    const rentType = this.getAttribute('data-rent-type');
                    const rentDates = this.getAttribute('data-rent-dates');
                    const rentDuration = this.getAttribute('data-rent-duration');
                    const pickup = this.getAttribute('data-pickup');
                    const dropoff = this.getAttribute('data-dropoff');

                    // Rincian biaya
                    const costBase = this.getAttribute('data-cost-base');
                    const costDriver = this.getAttribute('data-cost-driver');
                    const costPickup = this.getAttribute('data-cost-pickup');
                    const costDropoff = this.getAttribute('data-cost-dropoff');
                    const costPenalty = this.getAttribute('data-cost-penalty');
                    const costTotal = this.getAttribute('data-cost-total');
                    // Raw numeric values untuk kalkulasi total pembayaran
                    const costBaseRaw = parseFloat(this.getAttribute('data-cost-base-raw')) || 0;
                    const costDriverRaw = parseFloat(this.getAttribute('data-cost-driver-raw')) || 0;
                    const costPickupRaw = parseFloat(this.getAttribute('data-cost-pickup-raw')) || 0;
                    const costDropoffRaw = parseFloat(this.getAttribute('data-cost-dropoff-raw')) || 0;
                    const costPenaltyRaw = parseFloat(this.getAttribute('data-cost-penalty-raw')) || 0;
                    const penaltyHours = parseInt(this.getAttribute('data-penalty-hours')) || 0;
                    const isOverdue = this.getAttribute('data-is-overdue') === 'true';
                    const penaltyStatus = this.getAttribute('data-penalty-status');
                    const penaltyProof = this.getAttribute('data-penalty-proof');

                    // Info waktu sewa
                    const startedAt = this.getAttribute('data-started-at');
                    const deadline = this.getAttribute('data-deadline');
                    const actualReturn = this.getAttribute('data-actual-return');

                    const status = this.getAttribute('data-status');

                    const hasPayment = this.getAttribute('data-has-payment') === 'true';
                    const payMethod = this.getAttribute('data-pay-method');
                    const payStatus = this.getAttribute('data-pay-status');
                    const payBank = this.getAttribute('data-pay-bank');
                    const payProof = this.getAttribute('data-pay-proof');
                    const payPaidAt = this.getAttribute('data-pay-paidat');

                    // Refund data
                    const refundReason = this.getAttribute('data-refund-reason');
                    const refundDetail = this.getAttribute('data-refund-detail');
                    const refundStatus = this.getAttribute('data-refund-status');
                    const refundDate = this.getAttribute('data-refund-date');
                    const refundProof = this.getAttribute('data-refund-proof');
                    const refundBankName = this.getAttribute('data-refund-bank-name');
                    const refundAccountNumber = this.getAttribute('data-refund-account-number');
                    const refundAccountHolder = this.getAttribute('data-refund-account-holder');

                    // Populate fields — Customer
                    document.getElementById('lbl-trx-id').textContent = trx;
                    document.getElementById('det-cust-name').textContent = custName;
                    document.getElementById('det-cust-nik').textContent = custNik;
                    document.getElementById('det-cust-email').textContent = custEmail;
                    document.getElementById('det-cust-phone').textContent = custPhone;
                    document.getElementById('det-cust-address').textContent = custAddress;

                    // Populate fields — Car
                    document.getElementById('det-car-name').textContent = carName;
                    document.getElementById('det-car-spec').textContent = carSpec;
                    document.getElementById('det-car-price').textContent = carPrice;

                    // Populate fields — Rental info
                    document.getElementById('det-rent-type').textContent = rentType;
                    document.getElementById('det-rent-duration').textContent = rentDuration;
                    document.getElementById('det-rent-dates').textContent = rentDates;
                    document.getElementById('det-pickup').textContent = pickup;
                    document.getElementById('det-dropoff').textContent = dropoff;

                    // Populate fields — Rincian biaya
                    document.getElementById('det-cost-base').textContent = costBase;
                    document.getElementById('det-cost-driver').textContent = costDriver;
                    document.getElementById('det-cost-pickup').textContent = costPickup;
                    document.getElementById('det-cost-dropoff').textContent = costDropoff;
                    // Hitung total pembayaran = semua biaya (sewa + supir + pickup + dropoff + denda)
                    const grandTotal = costBaseRaw + costDriverRaw + costPickupRaw + costDropoffRaw + costPenaltyRaw;
                    document.getElementById('det-cost-total').textContent = 'Rp ' + formatRupiah(grandTotal);

                    // Tampilkan denda jika ada
                    const penaltyRow = document.getElementById('det-penalty-row');
                    if (penaltyHours > 0) {
                        penaltyRow.classList.remove('d-none');
                        document.getElementById('det-cost-penalty').textContent = costPenalty;
                        document.getElementById('det-penalty-detail').textContent = '(' + penaltyHours + ' jam × Rp ' + formatRupiah(penaltyRate) + ')';
                    } else {
                        penaltyRow.classList.add('d-none');
                    }

                    // Status badge formatting
                    const statusBadge = document.getElementById('det-status-badge');
                    statusBadge.className = 'badge px-3 py-2 rounded-pill ';
                    
                    let statusLabelText = status;
                    if (status === 'pending') {
                        statusBadge.classList.add('bg-warning', 'text-dark');
                        statusLabelText = 'Pending (Menunggu)';
                    } else if (status === 'confirmed') {
                        statusBadge.classList.add('bg-primary');
                        statusLabelText = 'Confirmed (Disetujui)';
                    } else if (status === 'ongoing') {
                        statusBadge.classList.add('bg-info', 'text-dark');
                        statusLabelText = isOverdue ? 'Ongoing (TERLAMBAT)' : 'Ongoing (Sedang Sewa)';
                    } else if (status === 'completed') {
                        statusBadge.classList.add('bg-success');
                        statusLabelText = 'Completed (Selesai)';
                    } else if (status === 'cancel_requested') {
                        statusBadge.classList.add('bg-danger');
                        statusLabelText = 'Cancel Requested';
                    } else if (status === 'cancelled') {
                        statusBadge.classList.add('bg-secondary');
                        statusLabelText = 'Cancelled (Batal)';
                    }
                    statusBadge.textContent = statusLabelText;

                    // Tampilkan section waktu sewa jika sudah dimulai
                    const timingSection = document.getElementById('det-timing-section');
                    const returnRow = document.getElementById('det-return-row');
                    const overdueRow = document.getElementById('det-overdue-row');

                    if (startedAt) {
                        timingSection.classList.remove('d-none');
                        document.getElementById('det-started-at').textContent = startedAt;
                        document.getElementById('det-deadline').textContent = deadline || '-';

                        // Tampilkan waktu dikembalikan jika sudah completed
                        if (actualReturn) {
                            returnRow.classList.remove('d-none');
                            document.getElementById('det-actual-return').textContent = actualReturn;
                        } else {
                            returnRow.classList.add('d-none');
                        }

                        // Status waktu: tepat waktu / terlambat
                        if (isOverdue || penaltyHours > 0) {
                            overdueRow.classList.remove('d-none');
                            document.getElementById('det-overdue-status').innerHTML = '<span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle me-1"></i>Terlambat ' + penaltyHours + ' Jam</span>';
                        } else if (status === 'completed') {
                            overdueRow.classList.remove('d-none');
                            document.getElementById('det-overdue-status').innerHTML = '<span class="text-success fw-bold"><i class="bi bi-check-circle me-1"></i>Tepat Waktu</span>';
                        } else {
                            overdueRow.classList.add('d-none');
                        }
                    } else {
                        timingSection.classList.add('d-none');
                    }

                    // Payment details rendering
                    const payInfoMissing = document.getElementById('pay-info-missing');
                    const payInfoContainer = document.getElementById('pay-info-container');
                    const payProofMissing = document.getElementById('pay-proof-missing');
                    const payProofImageContainer = document.getElementById('pay-proof-image-container');

                    if (!hasPayment) {
                        payInfoMissing.classList.remove('d-none');
                        payInfoContainer.classList.add('d-none');
                    } else {
                        payInfoMissing.classList.add('d-none');
                        payInfoContainer.classList.remove('d-none');

                        document.getElementById('det-pay-method').textContent = payMethod.replace('_', ' ');
                        document.getElementById('det-pay-bank').textContent = payBank || 'Pembayaran Cash / Tunai';
                        document.getElementById('det-pay-paidat').textContent = payPaidAt || '-';

                        const payStatusEl = document.getElementById('det-pay-status');
                        payStatusEl.className = 'fw-bold ';
                        let finalPayStatus = payStatus;
                        if (status === 'cancel_requested' || (status === 'cancelled' && hasPayment)) {
                            finalPayStatus = 'confirmed';
                        }
                        if (finalPayStatus === 'pending') {
                            payStatusEl.classList.add('text-warning');
                            payStatusEl.textContent = 'PENDING VERIFIKASI';
                        } else if (finalPayStatus === 'confirmed') {
                            payStatusEl.classList.add('text-success');
                            payStatusEl.textContent = 'TERKONFIRMASI';
                        } else {
                            payStatusEl.classList.add('text-danger');
                            payStatusEl.textContent = 'DITOLAK';
                        }

                        // Proof image rendering
                        if (payProof) {
                            payProofMissing.classList.add('d-none');
                            payProofImageContainer.classList.remove('d-none');
                            
                            const proofImgPath = '../assets/uploads/payments/' + payProof;
                            document.getElementById('det-pay-proof-img').src = proofImgPath;
                            document.getElementById('det-pay-proof-link').href = proofImgPath;
                        } else {
                            payProofMissing.classList.remove('d-none');
                            payProofImageContainer.classList.add('d-none');
                        }
                    }

                    // ======= Refund Info Section =======
                    const refundInfoSection = document.getElementById('refund-info-section');
                    const refundDetailRow = document.getElementById('det-refund-detail-row');
                    const refundBankSection = document.getElementById('det-refund-bank-section');
                    const refundProofMissing = document.getElementById('refund-proof-missing');
                    const refundProofImageContainer = document.getElementById('refund-proof-image-container');
                    const refundUploadSection = document.getElementById('refund-upload-section');

                    if (refundReason) {
                        refundInfoSection.style.display = '';

                        // Populate refund info
                        document.getElementById('det-refund-reason').textContent = refundReason;

                        if (refundDetail) {
                            refundDetailRow.style.display = '';
                            document.getElementById('det-refund-detail').textContent = refundDetail;
                        } else {
                            refundDetailRow.style.display = 'none';
                        }

                        // Status refund badge
                        const refundStatusEl = document.getElementById('det-refund-status');
                        refundStatusEl.className = 'detail-value fw-bold ';
                        if (refundStatus === 'requested') {
                            refundStatusEl.classList.add('text-warning');
                            refundStatusEl.textContent = 'MENUNGGU PERSETUJUAN';
                        } else if (refundStatus === 'approved') {
                            refundStatusEl.classList.add('text-success');
                            refundStatusEl.textContent = 'DISETUJUI';
                        } else if (refundStatus === 'rejected') {
                            refundStatusEl.classList.add('text-danger');
                            refundStatusEl.textContent = 'DITOLAK';
                        } else {
                            refundStatusEl.textContent = refundStatus || '-';
                        }

                        document.getElementById('det-refund-date').textContent = refundDate || '-';

                        // Rekening tujuan refund
                        if (refundBankName || refundAccountNumber || refundAccountHolder) {
                            refundBankSection.style.display = '';
                            document.getElementById('det-refund-account-holder').textContent = refundAccountHolder || '-';
                            document.getElementById('det-refund-bank-name').textContent = refundBankName || '-';
                            document.getElementById('det-refund-account-number').textContent = refundAccountNumber || '-';
                        } else {
                            refundBankSection.style.display = 'none';
                        }

                        // Bukti refund image
                        if (refundProof) {
                            refundProofMissing.classList.add('d-none');
                            refundProofImageContainer.classList.remove('d-none');
                            refundUploadSection.classList.add('d-none');
                            const refundProofPath = '../assets/uploads/refunds/' + refundProof;
                            document.getElementById('det-refund-proof-img').src = refundProofPath;
                            document.getElementById('det-refund-proof-link').href = refundProofPath;
                        } else {
                            refundProofImageContainer.classList.add('d-none');
                            // Tampilkan form upload jika status cancel_requested
                            if (status === 'cancel_requested') {
                                refundProofMissing.classList.add('d-none');
                                refundUploadSection.classList.remove('d-none');
                                document.getElementById('refund-upload-rental-id').value = id;
                            } else {
                                refundProofMissing.classList.remove('d-none');
                                refundUploadSection.classList.add('d-none');
                            }
                        }
                    } else {
                        refundInfoSection.style.display = 'none';
                    }

                    // ======= Penalty Payment Section =======
                    const penaltyProofSection = document.getElementById('penalty-proof-section');
                    const penaltyProofMissing = document.getElementById('penalty-proof-missing');
                    const penaltyProofImageContainer = document.getElementById('penalty-proof-image-container');
                    const penaltyProofImg = document.getElementById('det-penalty-proof-img');
                    const penaltyProofLink = document.getElementById('det-penalty-proof-link');
                    const penaltyPayStatusEl = document.getElementById('det-penalty-pay-status');

                    if ((status === 'completed' || (status === 'ongoing' && isOverdue)) && penaltyHours > 0) {
                        penaltyProofSection.classList.remove('d-none');
                        
                        penaltyPayStatusEl.className = 'fw-bold';
                        if (penaltyStatus === 'unpaid') {
                            penaltyPayStatusEl.classList.add('text-danger');
                            penaltyPayStatusEl.textContent = 'BELUM DIBAYAR';
                        } else if (penaltyStatus === 'pending') {
                            penaltyPayStatusEl.classList.add('text-warning');
                            penaltyPayStatusEl.textContent = 'PENDING VERIFIKASI';
                        } else if (penaltyStatus === 'confirmed') {
                            penaltyPayStatusEl.classList.add('text-success');
                            penaltyPayStatusEl.textContent = 'LUNAS / TERVERIFIKASI';
                        } else if (penaltyStatus === 'rejected') {
                            penaltyPayStatusEl.classList.add('text-danger');
                            penaltyPayStatusEl.textContent = 'DITOLAK';
                        } else {
                            penaltyPayStatusEl.textContent = penaltyStatus || 'BELUM DIBAYAR';
                        }

                        if (penaltyProof) {
                            penaltyProofMissing.classList.add('d-none');
                            penaltyProofImageContainer.classList.remove('d-none');
                            const penaltyProofPath = '../assets/uploads/payments/' + penaltyProof;
                            penaltyProofImg.src = penaltyProofPath;
                            penaltyProofLink.href = penaltyProofPath;
                        } else {
                            penaltyProofMissing.classList.remove('d-none');
                            penaltyProofImageContainer.classList.add('d-none');
                        }
                    } else {
                        penaltyProofSection.classList.add('d-none');
                    }

                    // Dynamically generate footer action buttons inside modal
                    const footerActions = document.getElementById('modal-footer-actions');
                    footerActions.innerHTML = ''; // Reset

                    let footerHtml = '<button type="button" class="btn btn-secondary px-4 rounded-3" data-bs-dismiss="modal">Tutup</button>';

                    if (status === 'pending') {
                        footerHtml += `
                            <a href="../handlers/rental_handler.php?action=update_rental_status&id=${id}&status=confirmed" class="btn btn-success px-4 rounded-3" onclick="return confirm('Konfirmasi pembayaran dan setujui pesanan ini?');">
                                <i class="bi bi-check-circle me-1"></i> Konfirmasi Bayar & Setujui
                            </a>
                        `;
                        if (hasPayment && payStatus === 'pending') {
                            footerHtml += `
                                <a href="../handlers/payment_handler.php?action=reject_payment&id=${id}" class="btn btn-warning text-dark px-4 rounded-3" onclick="return confirm('Apakah Anda yakin ingin menolak bukti pembayaran ini? Pelanggan harus mengunggah bukti baru.');">
                                    <i class="bi bi-exclamation-octagon me-1"></i> Tolak Bukti Pembayaran
                                </a>
                            `;
                        }
                        footerHtml += `
                            <a href="../handlers/rental_handler.php?action=update_rental_status&id=${id}&status=cancelled" class="btn btn-danger px-4 rounded-3" onclick="return confirm('Apakah Anda yakin ingin menolak & membatalkan pesanan ini?');">
                                <i class="bi bi-x-circle me-1"></i> Tolak & Batalkan Pesanan
                            </a>
                        `;
                    } else if (status === 'confirmed') {
                        footerHtml += `
                            <a href="../handlers/rental_handler.php?action=update_rental_status&id=${id}&status=ongoing" class="btn btn-info text-dark px-4 rounded-3" onclick="return confirm('Mulai masa sewa mobil? Waktu sewa dimulai dari sekarang.');">
                                <i class="bi bi-play-circle me-1"></i> Mulai Sewa (Diambil)
                            </a>
                            <a href="../handlers/rental_handler.php?action=update_rental_status&id=${id}&status=cancelled" class="btn btn-danger px-4 rounded-3" onclick="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?');">
                                <i class="bi bi-x-circle me-1"></i> Batalkan Pesanan
                            </a>
                        `;
                    } else if (status === 'ongoing') {
                        if (isOverdue) {
                            if (penaltyStatus === 'confirmed') {
                                footerHtml += `
                                    <a href="../handlers/rental_handler.php?action=update_rental_status&id=${id}&status=completed" class="btn btn-success px-4 rounded-3" onclick="return confirm('Selesaikan masa sewa mobil? Status mobil akan kembali menjadi Tersedia.');">
                                        <i class="bi bi-check2-all me-1"></i> Selesaikan Sewa (Kembali)
                                    </a>
                                `;
                                } else if (penaltyStatus === 'pending') {
                                footerHtml += `
                                    <a href="../handlers/payment_handler.php?action=confirm_penalty_payment&id=${id}" class="btn btn-success px-4 rounded-3" onclick="return confirm('Apakah Anda yakin ingin menyetujui pembayaran denda ini?');">
                                        <i class="bi bi-check-circle me-1"></i> Setujui Denda Lunas
                                    </a>
                                    <a href="../handlers/payment_handler.php?action=reject_penalty_payment&id=${id}" class="btn btn-warning text-dark px-4 rounded-3" onclick="return confirm('Apakah Anda yakin ingin menolak bukti pembayaran denda ini?');">
                                        <i class="bi bi-exclamation-octagon me-1"></i> Tolak Bukti Denda
                                    </a>
                                `;
                            } else {
                                footerHtml += `
                                    <span class="text-danger small fw-semibold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Menunggu pembayaran denda keterlambatan dari customer.</span>
                                `;
                            }
                        } else {
                            footerHtml += `
                                <a href="../handlers/rental_handler.php?action=update_rental_status&id=${id}&status=completed" class="btn btn-success px-4 rounded-3" onclick="return confirm('Selesaikan masa sewa mobil? Status mobil akan kembali menjadi Tersedia.');">
                                    <i class="bi bi-check2-all me-1"></i> Selesaikan Sewa (Kembali)
                                </a>
                            `;
                        }
                    } else if (status === 'cancel_requested') {
                        if (!refundProof) {
                            footerHtml += `
                                <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>Upload bukti transfer refund di atas untuk menyetujui pembatalan.</span>
                            `;
                        }
                    } else if (status === 'completed' && penaltyHours > 0) {
                        if (penaltyStatus === 'pending') {
                            footerHtml += `
                                <a href="../handlers/payment_handler.php?action=confirm_penalty_payment&id=${id}" class="btn btn-success px-4 rounded-3" onclick="return confirm('Apakah Anda yakin ingin menyetujui pembayaran denda ini?');">
                                    <i class="bi bi-check-circle me-1"></i> Setujui Denda Lunas
                                </a>
                                <a href="../handlers/payment_handler.php?action=reject_penalty_payment&id=${id}" class="btn btn-warning text-dark px-4 rounded-3" onclick="return confirm('Apakah Anda yakin ingin menolak bukti pembayaran denda ini?');">
                                    <i class="bi bi-exclamation-octagon me-1"></i> Tolak Bukti Denda
                                </a>
                            `;
                        }
                    }

                    footerActions.innerHTML = footerHtml;

                    // Display modal
                    detailModal.show();
                });
            });

            // Edit Bank Account Modal Trigger
            const editBankButtons = document.querySelectorAll('.btn-edit-bank');
            const editBankModal = new bootstrap.Modal(document.getElementById('modalEditBankAccount'));
            editBankButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    const bankName = this.getAttribute('data-bank-name');
                    const accountNumber = this.getAttribute('data-account-number');
                    const accountHolder = this.getAttribute('data-account-holder');
                    const isActive = this.getAttribute('data-is-active');

                    document.getElementById('edit_bank_id').value = id;
                    document.getElementById('edit_bank_name').value = bankName;
                    document.getElementById('edit_account_number').value = accountNumber;
                    document.getElementById('edit_account_holder').value = accountHolder;
                    document.getElementById('edit_is_active').value = isActive;

                    editBankModal.show();
                });
            });

            // Auto-dismiss alert after 5 seconds
            const alertEl = document.querySelector('.alert');
            if (alertEl) {
                setTimeout(function () {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
                    bsAlert.close();
                }, 5000);
            }

            // Refund proof image preview on file select
            const inputRefundProof = document.getElementById('inputRefundProof');
            if (inputRefundProof) {
                inputRefundProof.addEventListener('change', function () {
                    const previewContainer = document.getElementById('refund-proof-preview');
                    const previewImg = document.getElementById('refund-proof-preview-img');
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            previewImg.src = e.target.result;
                            previewContainer.classList.remove('d-none');
                        };
                        reader.readAsDataURL(this.files[0]);
                    } else {
                        previewContainer.classList.add('d-none');
                        previewImg.src = '';
                    }
                });
            }

            // Confirm before submitting refund approval form
            const formRefundProof = document.getElementById('formUploadRefundProof');
            if (formRefundProof) {
                formRefundProof.addEventListener('submit', function (e) {
                    if (!confirm('Apakah Anda yakin ingin menyetujui refund dan mengunggah bukti transfer ini?')) {
                        e.preventDefault();
                    }
                });
            }
        });
    </script>

    <!-- Modal Add Bank Account -->
    <div class="modal fade" id="modalAddBankAccount" tabindex="-1" aria-labelledby="modalAddBankAccountLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <h5 class="modal-title fw-bold" id="modalAddBankAccountLabel">
                        <i class="bi bi-credit-card-2-front text-primary me-2"></i>Tambah Rekening Bank Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../handlers/bank_account_handler.php" method="POST">
                    <input type="hidden" name="action" value="add_bank_account">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="add_bank_name" class="form-label fw-semibold text-secondary">Nama Bank (contoh: BCA, Mandiri)</label>
                            <input type="text" id="add_bank_name" name="bank_name" class="form-control" placeholder="Nama Bank" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_account_number" class="form-label fw-semibold text-secondary">Nomor Rekening</label>
                            <input type="text" id="add_account_number" name="account_number" class="form-control" placeholder="Nomor Rekening" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_account_holder" class="form-label fw-semibold text-secondary">Nama Pemilik Rekening</label>
                            <input type="text" id="add_account_holder" name="account_holder" class="form-control" placeholder="Atas Nama" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_is_active" class="form-label fw-semibold text-secondary">Status Aktif</label>
                            <select id="add_is_active" name="is_active" class="form-select">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light p-3 rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4">Simpan Rekening</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Bank Account -->
    <div class="modal fade" id="modalEditBankAccount" tabindex="-1" aria-labelledby="modalEditBankAccountLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <h5 class="modal-title fw-bold" id="modalEditBankAccountLabel">
                        <i class="bi bi-pencil-square text-primary me-2"></i>Edit Rekening Bank
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../handlers/bank_account_handler.php" method="POST">
                    <input type="hidden" name="action" value="edit_bank_account">
                    <input type="hidden" id="edit_bank_id" name="id" value="">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="edit_bank_name" class="form-label fw-semibold text-secondary">Nama Bank</label>
                            <input type="text" id="edit_bank_name" name="bank_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_account_number" class="form-label fw-semibold text-secondary">Nomor Rekening</label>
                            <input type="text" id="edit_account_number" name="account_number" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_account_holder" class="form-label fw-semibold text-secondary">Nama Pemilik Rekening</label>
                            <input type="text" id="edit_account_holder" name="account_holder" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_is_active" class="form-label fw-semibold text-secondary">Status Aktif</label>
                            <select id="edit_is_active" name="is_active" class="form-select">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light p-3 rounded-bottom-4">
                        <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4">Update Rekening</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>