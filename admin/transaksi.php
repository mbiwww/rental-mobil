<?php
/**
 * Halaman Manajemen Transaksi - Panel Admin
 *
 * Menampilkan daftar transaksi sewa dari database secara dinamis menggunakan OOP.
 * Menyediakan filter tab status dan detail view modal.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// MOCK SESSION ADMIN (Untuk kemudahan testing)
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'admin';
    $_SESSION['name'] = 'Admin Tester';
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../pages/login.php');
    exit;
}

require_once '../classes/Database.php';
require_once '../classes/BaseModel.php';
require_once '../classes/Rental.php';
require_once '../classes/Payment.php';

$db = Database::getInstance()->getConnection();
$rentalModel = new Rental($db);
$paymentModel = new Payment($db);

// Filter status
$activeStatus = $_GET['status'] ?? ''; // '' means Semua

// Ambil data rental
$rentals = $rentalModel->getAll($activeStatus);

// List tabs
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
    <title>Manajemen Transaksi - RentalKu Admin</title>
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
            border: 1px dashed #ddd;
            border-radius: 12px;
            padding: 10px;
            text-align: center;
            background-color: #f8f9fa;
            margin-top: 10px;
        }
        .proof-img {
            max-width: 100%;
            max-height: 250px;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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
                <a href="../pages/login.php" class="text-muted" title="Logout">
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
            <?php if (isset($_GET['status'])): ?>
                <div class="alert alert-<?= $_GET['status'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi <?= $_GET['status'] === 'success' ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger' ?> me-2 fs-5"></i>
                        <div><?= htmlspecialchars($_GET['msg'] ?? '') ?></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Tabs Filter -->
            <div class="status-tabs mb-4 shadow-sm">
                <?php foreach ($tabs as $statusVal => $label): 
                    $count = $rentalModel->countByStatus($statusVal);
                    $activeClass = ($activeStatus === $statusVal) ? 'active' : '';
                    $url = 'transaksi.php' . (!empty($statusVal) ? '?status=' . $statusVal : '');
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
                                <th class="text-end" width="160">Aksi</th>
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
                                    
                                    // Duration calculation
                                    $days = (int) (strtotime($r['end_date']) - strtotime($r['start_date'])) / 86400;
                                    if ($days <= 0) $days = 1;
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
                                        </td>
                                        <td class="text-end">
                                            <!-- Detail Button -->
                                            <button type="button" class="btn-action btn-view btn-show-detail" title="Detail"
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
                                               data-cost-driver="Rp <?= number_format($r['driver_cost'], 0, ',', '.') ?>"
                                               data-cost-total="Rp <?= number_format($r['total_price'], 0, ',', '.') ?>"
                                               data-status="<?= $r['status'] ?>"
                                               data-has-payment="<?= $hasPayment ?>"
                                               data-pay-method="<?= htmlspecialchars($paymentMethod) ?>"
                                               data-pay-status="<?= htmlspecialchars($paymentStatus) ?>"
                                               data-pay-bank="<?= htmlspecialchars($paymentBank) ?>"
                                               data-pay-proof="<?= htmlspecialchars($paymentProof) ?>"
                                               data-pay-paidat="<?= htmlspecialchars($paymentPaidAt) ?>"
                                            >
                                               <i class="bi bi-eye fs-5"></i>
                                            </button>

                                            <!-- Inline Fast Actions -->
                                            <?php if ($r['status'] === 'pending'): ?>
                                                <a href="../handlers/admin_handler.php?action=update_rental_status&id=<?= $r['id'] ?>&status=confirmed" class="btn-action btn-check" title="Konfirmasi Pembayaran" onclick="return confirm('Konfirmasi pembayaran dan setujui pesanan ini?');"><i class="bi bi-check-circle fs-5"></i></a>
                                                <a href="../handlers/admin_handler.php?action=update_rental_status&id=<?= $r['id'] ?>&status=cancelled" class="btn-action btn-cancel" title="Batalkan Pesanan" onclick="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?');"><i class="bi bi-x-circle fs-5"></i></a>
                                            <?php elseif ($r['status'] === 'confirmed'): ?>
                                                <a href="../handlers/admin_handler.php?action=update_rental_status&id=<?= $r['id'] ?>&status=ongoing" class="btn-action text-info" title="Mulai Sewa (Mobil Diambil)" onclick="return confirm('Mulai masa sewa mobil? Status mobil akan berubah menjadi Disewa.');"><i class="bi bi-play-circle fs-5"></i></a>
                                                <a href="../handlers/admin_handler.php?action=update_rental_status&id=<?= $r['id'] ?>&status=cancelled" class="btn-action btn-cancel" title="Batalkan Pesanan" onclick="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?');"><i class="bi bi-x-circle fs-5"></i></a>
                                            <?php elseif ($r['status'] === 'ongoing'): ?>
                                                <a href="../handlers/admin_handler.php?action=update_rental_status&id=<?= $r['id'] ?>&status=completed" class="btn-action text-success" title="Selesaikan Sewa (Mobil Kembali)" onclick="return confirm('Selesaikan masa sewa mobil? Status mobil akan kembali menjadi Tersedia.');"><i class="bi bi-check2-all fs-5"></i></a>
                                            <?php elseif ($r['status'] === 'cancel_requested'): ?>
                                                <a href="../handlers/admin_handler.php?action=update_rental_status&id=<?= $r['id'] ?>&status=cancelled" class="btn-action btn-cancel" title="Setujui Pembatalan" onclick="return confirm('Apakah Anda yakin ingin menyetujui pembatalan pesanan ini?');"><i class="bi bi-x-circle fs-5"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ==========================================
    MODAL DETAIL TRANSAKSI
    ========================================== -->
    <div class="modal fade" id="modalDetailRental" tabindex="-1" aria-labelledby="modalDetailRentalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <h5 class="modal-title fw-bold" id="modalDetailRentalLabel">
                        <i class="bi bi-file-earmark-text text-primary me-2"></i>Detail Transaksi <span id="lbl-trx-id" class="text-muted">#TRX-0000</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Left Column: Customer and Car Spec -->
                        <div class="col-md-6">
                            <!-- Customer Info -->
                            <div class="detail-section-title"><i class="bi bi-person me-2"></i>Data Penyewa</div>
                            <div class="row">
                                <div class="col-6 detail-item">
                                    <div class="detail-label">Nama Lengkap</div>
                                    <div id="det-cust-name" class="detail-value">-</div>
                                </div>
                                <div class="col-6 detail-item">
                                    <div class="detail-label">NIK</div>
                                    <div id="det-cust-nik" class="detail-value">-</div>
                                </div>
                                <div class="col-6 detail-item">
                                    <div class="detail-label">Email</div>
                                    <div id="det-cust-email" class="detail-value">-</div>
                                </div>
                                <div class="col-6 detail-item">
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
                                <div class="col-6 detail-item">
                                    <div class="detail-label">Spesifikasi Mesin & Transmisi</div>
                                    <div id="det-car-spec" class="detail-value">-</div>
                                </div>
                                <div class="col-6 detail-item">
                                    <div class="detail-label">Harga Sewa / Hari</div>
                                    <div id="det-car-price" class="detail-value">-</div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Rental info and Billing -->
                        <div class="col-md-6">
                            <!-- Rental Info -->
                            <div class="detail-section-title"><i class="bi bi-calendar-event me-2"></i>Detail Penyewaan</div>
                            <div class="row">
                                <div class="col-6 detail-item">
                                    <div class="detail-label">Tipe Sewa</div>
                                    <div id="det-rent-type" class="detail-value">-</div>
                                </div>
                                <div class="col-6 detail-item">
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

                            <!-- Cost & Billing -->
                            <div class="detail-section-title mt-4"><i class="bi bi-wallet2 me-2"></i>Rincian Biaya</div>
                            <div class="row bg-light p-3 rounded-3 border">
                                <div class="col-6 detail-item mb-2">
                                    <div class="detail-label">Biaya Supir</div>
                                    <div id="det-cost-driver" class="detail-value">-</div>
                                </div>
                                <div class="col-6 detail-item mb-2">
                                    <div class="detail-label">Status Rental</div>
                                    <div><span id="det-status-badge" class="badge rounded-pill">-</span></div>
                                </div>
                                <div class="col-12 border-top pt-2 mt-2">
                                    <div class="detail-label fw-bold">Total Pembayaran</div>
                                    <h4 class="fw-bold text-primary mb-0" id="det-cost-total">Rp 0</h4>
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
                                        <div class="col-6 detail-item">
                                            <div class="detail-label">Metode Pembayaran</div>
                                            <div id="det-pay-method" class="detail-value text-uppercase">-</div>
                                        </div>
                                        <div class="col-6 detail-item">
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
                                <div class="col-md-6 text-center">
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

                    const costDriver = this.getAttribute('data-cost-driver');
                    const costTotal = this.getAttribute('data-cost-total');
                    const status = this.getAttribute('data-status');

                    const hasPayment = this.getAttribute('data-has-payment') === 'true';
                    const payMethod = this.getAttribute('data-pay-method');
                    const payStatus = this.getAttribute('data-pay-status');
                    const payBank = this.getAttribute('data-pay-bank');
                    const payProof = this.getAttribute('data-pay-proof');
                    const payPaidAt = this.getAttribute('data-pay-paidat');

                    // Populate fields
                    document.getElementById('lbl-trx-id').textContent = trx;
                    document.getElementById('det-cust-name').textContent = custName;
                    document.getElementById('det-cust-nik').textContent = custNik;
                    document.getElementById('det-cust-email').textContent = custEmail;
                    document.getElementById('det-cust-phone').textContent = custPhone;
                    document.getElementById('det-cust-address').textContent = custAddress;

                    document.getElementById('det-car-name').textContent = carName;
                    document.getElementById('det-car-spec').textContent = carSpec;
                    document.getElementById('det-car-price').textContent = carPrice;

                    document.getElementById('det-rent-type').textContent = rentType;
                    document.getElementById('det-rent-duration').textContent = rentDuration;
                    document.getElementById('det-rent-dates').textContent = rentDates;
                    document.getElementById('det-pickup').textContent = pickup;
                    document.getElementById('det-dropoff').textContent = dropoff;

                    document.getElementById('det-cost-driver').textContent = costDriver;
                    document.getElementById('det-cost-total').textContent = costTotal;

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
                        statusLabelText = 'Ongoing (Sedang Sewa)';
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
                        if (payStatus === 'pending') {
                            payStatusEl.classList.add('text-warning');
                            payStatusEl.textContent = 'PENDING VERIFIKASI';
                        } else if (payStatus === 'confirmed') {
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

                    // Dynamically generate footer action buttons inside modal
                    const footerActions = document.getElementById('modal-footer-actions');
                    footerActions.innerHTML = ''; // Reset

                    let footerHtml = '<button type="button" class="btn btn-secondary px-4 rounded-3" data-bs-dismiss="modal">Tutup</button>';

                    if (status === 'pending') {
                        footerHtml += `
                            <a href="../handlers/admin_handler.php?action=update_rental_status&id=${id}&status=confirmed" class="btn btn-success px-4 rounded-3" onclick="return confirm('Konfirmasi pembayaran dan setujui pesanan ini?');">
                                <i class="bi bi-check-circle me-1"></i> Konfirmasi Bayar & Setujui
                            </a>
                            <a href="../handlers/admin_handler.php?action=update_rental_status&id=${id}&status=cancelled" class="btn btn-danger px-4 rounded-3" onclick="return confirm('Apakah Anda yakin ingin menolak & membatalkan pesanan ini?');">
                                <i class="bi bi-x-circle me-1"></i> Tolak / Batalkan
                            </a>
                        `;
                    } else if (status === 'confirmed') {
                        footerHtml += `
                            <a href="../handlers/admin_handler.php?action=update_rental_status&id=${id}&status=ongoing" class="btn btn-info text-dark px-4 rounded-3" onclick="return confirm('Mulai masa sewa mobil? Status mobil akan berubah menjadi Disewa.');">
                                <i class="bi bi-play-circle me-1"></i> Mulai Sewa (Diambil)
                            </a>
                            <a href="../handlers/admin_handler.php?action=update_rental_status&id=${id}&status=cancelled" class="btn btn-danger px-4 rounded-3" onclick="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?');">
                                <i class="bi bi-x-circle me-1"></i> Batalkan Pesanan
                            </a>
                        `;
                    } else if (status === 'ongoing') {
                        footerHtml += `
                            <a href="../handlers/admin_handler.php?action=update_rental_status&id=${id}&status=completed" class="btn btn-success px-4 rounded-3" onclick="return confirm('Selesaikan masa sewa mobil? Status mobil akan kembali menjadi Tersedia.');">
                                <i class="bi bi-check2-all me-1"></i> Selesaikan Sewa (Kembali)
                            </a>
                        `;
                    } else if (status === 'cancel_requested') {
                        footerHtml += `
                            <a href="../handlers/admin_handler.php?action=update_rental_status&id=${id}&status=cancelled" class="btn btn-danger px-4 rounded-3" onclick="return confirm('Apakah Anda yakin ingin menyetujui pembatalan pesanan ini?');">
                                <i class="bi bi-x-circle me-1"></i> Setujui Pembatalan
                            </a>
                        `;
                    }

                    footerActions.innerHTML = footerHtml;

                    // Display modal
                    detailModal.show();
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
        });
    </script>
</body>
</html>