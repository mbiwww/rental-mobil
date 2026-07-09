<?php
/**
 * Halaman Laporan - Panel Admin
 *
 * Menampilkan laporan transaksi dengan filter tanggal dan status.
 * Statistik ringkasan dinamis dari database.
 */
require_once __DIR__ . '/../includes/admin_auth_check.php';

require_once '../classes/Database.php';
require_once '../classes/BaseModel.php';
require_once '../classes/Rental.php';
require_once '../classes/User.php';

$db = Database::getInstance()->getConnection();
$rentalModel = new Rental($db);
$userModel = new User($db);

// Filter dari GET
$startDate = $_GET['start_date'] ?? '';
$endDate   = $_GET['end_date'] ?? '';
$filterStatus = $_GET['filter_status'] ?? '';

// Ambil data laporan
$reportData  = $rentalModel->getReportData($startDate, $endDate, $filterStatus);
$reportStats = $rentalModel->getReportStats($startDate, $endDate);
$totalCustomers = $userModel->countCustomers();
$activePage = 'laporan';

// Cek apakah ada filter aktif
$hasFilter = !empty($startDate) || !empty($endDate) || !empty($filterStatus);

// Status label mapping
$statusLabels = [
    'pending' => ['label' => 'Pending', 'class' => 'bg-warning text-dark'],
    'confirmed' => ['label' => 'Dikonfirmasi', 'class' => 'bg-info text-white'],
    'ongoing' => ['label' => 'Berjalan', 'class' => 'bg-primary'],
    'completed' => ['label' => 'Selesai', 'class' => 'bg-success'],
    'cancelled' => ['label' => 'Dibatalkan', 'class' => 'bg-danger'],
    'cancel_requested' => ['label' => 'Minta Batal', 'class' => 'bg-secondary'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - RentalQu Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; color: #333; }
        .sidebar { width: 260px; height: 100vh; background: white; position: fixed; border-right: 1px solid #eee; padding: 20px; z-index: 1000; }
        .nav-link { color: #6c757d; padding: 12px 15px; border-radius: 10px; margin-bottom: 5px; display: flex; align-items: center; text-decoration: none; transition: all 0.2s ease; }
        .nav-link:hover, .nav-link.active { background: #eef4ff; color: #0d6efd; font-weight: 600; }
        .main-content { margin-left: 260px; min-height: 100vh; }
        .top-header { background: white; padding: 15px 40px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .stat-card { background: white; border-radius: 16px; padding: 22px 28px; border: 1px solid #efefef; display: flex; align-items: center; gap: 18px; }
        .stat-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
        .stat-icon-blue { background: #eef4ff; color: #0d6efd; }
        .stat-icon-green { background: #d1e7dd; color: #0f5132; }
        .stat-icon-orange { background: #ffecd2; color: #e67e22; }
        .stat-icon-purple { background: #e8daef; color: #6f42c1; }
        .stat-label { font-size: 13px; color: #888; margin-bottom: 2px; }
        .stat-value { font-size: 26px; font-weight: 700; color: #212529; line-height: 1; }
        .table-container { background: white; border-radius: 20px; padding: 30px; border: 1px solid #efefef; }
        .table thead th { border-bottom: 2px solid #f0f0f0; color: #555; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; padding-bottom: 15px; }
        .table tbody td { padding: 14px 12px; border-bottom: 1px solid #f8f9fa; vertical-align: middle; font-size: 14px; }
        .table tbody tr:last-child td { border-bottom: none; }
        .table tbody tr:hover { background-color: #f9fbff; }
        .filter-container { background: white; border-radius: 20px; padding: 25px 30px; border: 1px solid #efefef; }
    </style>
</head>
<body>
    <?php include '../includes/sidebar_admin.php'; ?>

    <div class="main-content">
        <header class="top-header">
            <div></div>
            <div class="d-flex align-items-center">
                <div class="text-end me-3">
                    <p class="mb-0 fw-bold"><?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></p>
                    <small class="text-muted">Administrator</small>
                </div>
                <a href="../handlers/auth_handler.php?action=logout" class="text-muted text-decoration-none" title="Logout"><i class="bi bi-box-arrow-right fs-4"></i></a>
            </div>
        </header>

        <div class="p-5">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h1 class="fw-bold mb-1">Laporan</h1>
                    <p class="text-muted">Laporan transaksi dan pendapatan sistem</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="export_excel.php?start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>&filter_status=<?= urlencode($filterStatus) ?>" class="btn btn-success d-flex align-items-center gap-2 rounded-3">
                        <i class="bi bi-file-earmark-excel"></i> Export Excel
                    </a>
                </div>
            </div>

            <!-- Filter -->
            <div class="filter-container shadow-sm mb-4">
                <form method="GET" action="laporan.php" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control rounded-3" value="<?= htmlspecialchars($startDate) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Tanggal Selesai</label>
                        <input type="date" name="end_date" class="form-control rounded-3" value="<?= htmlspecialchars($endDate) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Status</label>
                        <select name="filter_status" class="form-select rounded-3">
                            <option value="">Semua Status</option>
                            <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="confirmed" <?= $filterStatus === 'confirmed' ? 'selected' : '' ?>>Dikonfirmasi</option>
                            <option value="ongoing" <?= $filterStatus === 'ongoing' ? 'selected' : '' ?>>Berjalan</option>
                            <option value="completed" <?= $filterStatus === 'completed' ? 'selected' : '' ?>>Selesai</option>
                            <option value="cancelled" <?= $filterStatus === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4 rounded-3"><i class="bi bi-funnel me-1"></i>Tampilkan</button>
                        <?php if ($hasFilter): ?>
                            <a href="laporan.php" class="btn btn-outline-secondary px-3 rounded-3">Reset</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Stat Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-card shadow-sm">
                        <div class="stat-icon stat-icon-blue"><i class="bi bi-receipt-cutoff"></i></div>
                        <div>
                            <div class="stat-label">Total Transaksi</div>
                            <div class="stat-value"><?= (int)$reportStats['total_transaksi'] ?></div>
                            <small class="text-muted"><?= (int)$reportStats['total_completed'] ?> selesai</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card shadow-sm">
                        <div class="stat-icon stat-icon-green"><i class="bi bi-cash-stack"></i></div>
                        <div>
                            <div class="stat-label">Total Pendapatan</div>
                            <div class="stat-value" style="font-size:20px;">Rp <?= number_format($reportStats['total_pendapatan'], 0, ',', '.') ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card shadow-sm">
                        <div class="stat-icon stat-icon-orange"><i class="bi bi-exclamation-diamond"></i></div>
                        <div>
                            <div class="stat-label">Total Denda</div>
                            <div class="stat-value" style="font-size:20px;">Rp <?= number_format($reportStats['total_denda'], 0, ',', '.') ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card shadow-sm">
                        <div class="stat-icon stat-icon-purple"><i class="bi bi-calendar3"></i></div>
                        <div>
                            <div class="stat-label">Rata-rata Sewa</div>
                            <div class="stat-value"><?= round($reportStats['rata_rata_hari'], 1) ?></div>
                            <small class="text-muted">hari / transaksi</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Laporan -->
            <div class="table-container shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-table me-2 text-primary"></i>Laporan Transaksi
                        <span class="badge bg-light text-secondary fw-normal ms-1"><?= count($reportData) ?></span>
                    </h5>
                    <?php if ($hasFilter): ?>
                        <small class="text-muted">
                            Filter:
                            <?php if (!empty($startDate)): ?><?= date('d M Y', strtotime($startDate)) ?><?php endif; ?>
                            <?php if (!empty($startDate) && !empty($endDate)): ?> — <?php endif; ?>
                            <?php if (!empty($endDate)): ?><?= date('d M Y', strtotime($endDate)) ?><?php endif; ?>
                            <?php if (!empty($filterStatus)): ?> | <?= ucfirst($filterStatus) ?><?php endif; ?>
                        </small>
                    <?php endif; ?>
                </div>

                <?php if (empty($reportData)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-3 text-secondary"></i>
                        <p>Tidak ada data transaksi<?= $hasFilter ? ' untuk filter yang dipilih' : '' ?>.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr>
                                <th>ID</th><th>Tanggal</th><th>Customer</th><th>Mobil</th><th>Durasi</th><th>Denda</th><th>Total</th><th>Status</th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($reportData as $r):
                                $days = max(1, (int)((strtotime($r['end_date']) - strtotime($r['start_date'])) / 86400));
                                $st = $statusLabels[$r['status']] ?? ['label' => $r['status'], 'class' => 'bg-secondary'];
                                $grandTotal = (float)$r['total_price'] + (float)($r['penalty_fee'] ?? 0);
                            ?>
                                <tr>
                                    <td class="text-muted fw-semibold">#<?= $r['id'] ?></td>
                                    <td><small><?= date('d M Y', strtotime($r['created_at'])) ?></small></td>
                                    <td>
                                        <p class="mb-0 fw-semibold"><?= htmlspecialchars($r['user_name']) ?></p>
                                        <small class="text-muted"><?= htmlspecialchars($r['user_phone']) ?></small>
                                    </td>
                                    <td>
                                        <span class="fw-semibold"><?= htmlspecialchars($r['brand'] . ' ' . $r['model']) ?></span>
                                        <small class="text-muted d-block"><?= $r['year'] ?></small>
                                    </td>
                                    <td><?= $days ?> hari</td>
                                    <td>
                                        <?php if ((float)($r['penalty_fee'] ?? 0) > 0): ?>
                                            <span class="text-danger fw-semibold">Rp <?= number_format($r['penalty_fee'], 0, ',', '.') ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold text-primary">Rp <?= number_format($grandTotal, 0, ',', '.') ?></td>
                                    <td><span class="badge <?= $st['class'] ?> px-3 py-2 rounded-3"><?= $st['label'] ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Ringkasan bawah tabel -->
                    <div class="border-top pt-3 mt-3 d-flex justify-content-between align-items-center">
                        <small class="text-muted">Menampilkan <?= count($reportData) ?> transaksi</small>
                        <div class="text-end">
                            <small class="text-muted">Total Pendapatan (Selesai):</small>
                            <span class="fw-bold text-success ms-2 fs-5">Rp <?= number_format($reportStats['total_pendapatan'], 0, ',', '.') ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div><!-- /p-5 -->
    </div><!-- /main-content -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>