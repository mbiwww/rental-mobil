<?php
/**
 * Halaman Pengembalian Mobil - Panel Admin
 *
 * Menampilkan rental ongoing yang perlu dikembalikan dan riwayat pengembalian.
 * Fitur: Proses pengembalian, hitung denda otomatis, riwayat completed.
 */
require_once __DIR__ . '/../includes/admin_auth_check.php';

require_once '../classes/Database.php';
require_once '../classes/BaseModel.php';
require_once '../classes/Rental.php';

$db = Database::getInstance()->getConnection();
$rentalModel = new Rental($db);

$ongoingRentals = $rentalModel->getOngoingRentals();
$completedRentals = $rentalModel->getRecentlyCompleted(10);
$totalOngoing = $rentalModel->countByStatus('ongoing');
$totalCompleted = $rentalModel->countByStatus('completed');
$totalPenalty = $rentalModel->getTotalPenalty();
$activePage = 'pengembalian';

// Hitung info denda untuk setiap rental ongoing
foreach ($ongoingRentals as &$r) {
    $r['penalty_info'] = $rentalModel->calculatePenalty($r);
}
unset($r);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengembalian - RentalKu Admin</title>
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
        .stat-icon-orange { background: #ffecd2; color: #e67e22; }
        .stat-icon-green { background: #d1e7dd; color: #0f5132; }
        .stat-icon-red { background: #f8d7da; color: #842029; }
        .stat-label { font-size: 13px; color: #888; margin-bottom: 2px; }
        .stat-value { font-size: 26px; font-weight: 700; color: #212529; line-height: 1; }
        .table-container { background: white; border-radius: 20px; padding: 30px; border: 1px solid #efefef; }
        .table thead th { border-bottom: 2px solid #f0f0f0; color: #555; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; padding-bottom: 15px; }
        .table tbody td { padding: 16px 12px; border-bottom: 1px solid #f8f9fa; vertical-align: middle; font-size: 14px; }
        .table tbody tr:last-child td { border-bottom: none; }
        .table tbody tr:hover { background-color: #f9fbff; }
        .badge-overdue { background: #f8d7da; color: #842029; }
        .badge-ontime { background: #d1e7dd; color: #0f5132; }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0f0f0; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #888; font-size: 13px; }
        .detail-value { font-weight: 600; font-size: 14px; text-align: right; }
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
                <a href="../pages/login.php" class="text-muted" title="Logout"><i class="bi bi-box-arrow-right fs-4"></i></a>
            </div>
        </header>

        <div class="p-5">
            <div class="mb-4">
                <h1 class="fw-bold mb-1">Proses Pengembalian</h1>
                <p class="text-muted">Kelola pengembalian mobil dari customer</p>
            </div>

            <?php if (isset($_GET['status'])): ?>
                <div class="alert alert-<?= $_GET['status'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi <?= $_GET['status'] === 'success' ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger' ?> me-2 fs-5"></i>
                        <div><?= htmlspecialchars($_GET['msg'] ?? '') ?></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Stat Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="stat-card shadow-sm">
                        <div class="stat-icon stat-icon-orange"><i class="bi bi-arrow-repeat"></i></div>
                        <div><div class="stat-label">Sedang Berjalan</div><div class="stat-value"><?= $totalOngoing ?></div></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card shadow-sm">
                        <div class="stat-icon stat-icon-green"><i class="bi bi-check-circle"></i></div>
                        <div><div class="stat-label">Selesai Dikembalikan</div><div class="stat-value"><?= $totalCompleted ?></div></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card shadow-sm">
                        <div class="stat-icon stat-icon-red"><i class="bi bi-exclamation-diamond"></i></div>
                        <div><div class="stat-label">Total Denda Terkumpul</div><div class="stat-value" style="font-size:20px;">Rp <?= number_format($totalPenalty, 0, ',', '.') ?></div></div>
                    </div>
                </div>
            </div>

            <!-- Ongoing Rentals -->
            <div class="table-container shadow-sm mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-arrow-repeat text-warning me-2"></i>Rental Aktif — Menunggu Pengembalian <span class="badge bg-warning text-dark fw-normal ms-1"><?= count($ongoingRentals) ?></span></h5>
                </div>

                <?php if (empty($ongoingRentals)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-check-circle fs-1 d-block mb-3 text-success"></i>
                        <p>Tidak ada rental yang sedang berjalan saat ini. Semua mobil sudah dikembalikan.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr>
                                <th>ID</th><th>Mobil</th><th>Customer</th><th>Periode Sewa</th><th>Deadline</th><th>Status Waktu</th><th>Total Harga</th><th class="text-end">Aksi</th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($ongoingRentals as $r):
                                $penalty = $r['penalty_info'];
                                $days = max(1, (int)((strtotime($r['end_date']) - strtotime($r['start_date'])) / 86400));
                            ?>
                                <tr>
                                    <td class="text-muted fw-semibold">#<?= $r['id'] ?></td>
                                    <td><p class="mb-0 fw-bold"><?= htmlspecialchars($r['brand'] . ' ' . $r['model']) ?></p><small class="text-muted"><?= $r['year'] ?></small></td>
                                    <td><p class="mb-0 fw-semibold"><?= htmlspecialchars($r['user_name']) ?></p><small class="text-muted"><?= htmlspecialchars($r['user_phone']) ?></small></td>
                                    <td><small><?= date('d M Y', strtotime($r['start_date'])) ?> — <?= date('d M Y', strtotime($r['end_date'])) ?></small><br><small class="text-muted"><?= $days ?> hari</small></td>
                                    <td>
                                        <?php if ($penalty['deadline']): ?>
                                            <small class="fw-semibold"><?= date('d M Y, H:i', strtotime($penalty['deadline'])) ?></small>
                                        <?php else: ?>
                                            <small class="text-muted">-</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($penalty['is_overdue']): ?>
                                            <span class="badge badge-overdue px-3 py-2 rounded-3"><i class="bi bi-exclamation-triangle me-1"></i>Terlambat <?= $penalty['late_hours'] ?> jam</span>
                                            <br><small class="text-danger fw-bold">Denda: Rp <?= number_format($penalty['penalty_fee'], 0, ',', '.') ?></small>
                                        <?php else: ?>
                                            <span class="badge badge-ontime px-3 py-2 rounded-3"><i class="bi bi-clock me-1"></i>Tepat Waktu</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold text-primary">Rp <?= number_format($r['total_price'], 0, ',', '.') ?></td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-info rounded-3 me-1 btn-detail-return"
                                            data-id="<?= $r['id'] ?>" data-car="<?= htmlspecialchars($r['brand'] . ' ' . $r['model'] . ' (' . $r['year'] . ')') ?>"
                                            data-customer="<?= htmlspecialchars($r['user_name']) ?>" data-phone="<?= htmlspecialchars($r['user_phone']) ?>"
                                            data-email="<?= htmlspecialchars($r['user_email']) ?>" data-start="<?= date('d M Y', strtotime($r['start_date'])) ?>"
                                            data-end="<?= date('d M Y', strtotime($r['end_date'])) ?>" data-days="<?= $days ?>"
                                            data-started="<?= $r['started_at'] ? date('d M Y, H:i', strtotime($r['started_at'])) : '-' ?>"
                                            data-deadline="<?= $penalty['deadline'] ? date('d M Y, H:i', strtotime($penalty['deadline'])) : '-' ?>"
                                            data-overdue="<?= $penalty['is_overdue'] ? '1' : '0' ?>" data-late-hours="<?= $penalty['late_hours'] ?>"
                                            data-penalty="<?= number_format($penalty['penalty_fee'], 0, ',', '.') ?>"
                                            data-total="<?= number_format($r['total_price'], 0, ',', '.') ?>"
                                            data-rental-type="<?= $r['rental_type'] === 'with_driver' ? 'Dengan Sopir' : 'Lepas Kunci' ?>"
                                            title="Detail"><i class="bi bi-eye me-1"></i>Detail</button>
                                        <button type="button" class="btn btn-sm btn-success rounded-3 btn-proses-return"
                                            data-id="<?= $r['id'] ?>" data-car="<?= htmlspecialchars($r['brand'] . ' ' . $r['model']) ?>"
                                            data-customer="<?= htmlspecialchars($r['user_name']) ?>"
                                            data-penalty="<?= number_format($penalty['penalty_fee'], 0, ',', '.') ?>"
                                            data-overdue="<?= $penalty['is_overdue'] ? '1' : '0' ?>"
                                            title="Proses Pengembalian"><i class="bi bi-check-lg me-1"></i>Kembalikan</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Riwayat Pengembalian -->
            <div class="table-container shadow-sm">
                <h5 class="fw-bold mb-4"><i class="bi bi-clock-history text-success me-2"></i>Riwayat Pengembalian Terbaru <span class="badge bg-light text-secondary fw-normal ms-1"><?= count($completedRentals) ?></span></h5>
                <?php if (empty($completedRentals)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-3 text-secondary"></i>
                        <p>Belum ada riwayat pengembalian.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>ID</th><th>Mobil</th><th>Customer</th><th>Dikembalikan</th><th>Denda</th><th>Total</th></tr></thead>
                            <tbody>
                            <?php foreach ($completedRentals as $c): ?>
                                <tr>
                                    <td class="text-muted fw-semibold">#<?= $c['id'] ?></td>
                                    <td><span class="fw-bold"><?= htmlspecialchars($c['brand'] . ' ' . $c['model']) ?></span> <small class="text-muted"><?= $c['year'] ?></small></td>
                                    <td><?= htmlspecialchars($c['user_name']) ?></td>
                                    <td><small><?= $c['actual_return_at'] ? date('d M Y, H:i', strtotime($c['actual_return_at'])) : '-' ?></small></td>
                                    <td>
                                        <?php if ((float)$c['penalty_fee'] > 0): ?>
                                            <span class="badge badge-overdue px-2 py-1 rounded-3">Rp <?= number_format($c['penalty_fee'], 0, ',', '.') ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-ontime px-2 py-1 rounded-3">Tidak ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold text-primary">Rp <?= number_format($c['total_price'] + $c['penalty_fee'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </div><!-- /p-5 -->
    </div><!-- /main-content -->

    <!-- MODAL: DETAIL RENTAL -->
    <div class="modal fade" id="modalDetailReturn" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-info-circle-fill text-info me-2"></i>Detail Rental</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="bg-light rounded-4 p-3 border">
                        <div class="detail-row"><span class="detail-label">ID Rental</span><span class="detail-value" id="dr-id">-</span></div>
                        <div class="detail-row"><span class="detail-label">Mobil</span><span class="detail-value" id="dr-car">-</span></div>
                        <div class="detail-row"><span class="detail-label">Customer</span><span class="detail-value" id="dr-customer">-</span></div>
                        <div class="detail-row"><span class="detail-label">Telepon</span><span class="detail-value" id="dr-phone">-</span></div>
                        <div class="detail-row"><span class="detail-label">Tipe Sewa</span><span class="detail-value" id="dr-type">-</span></div>
                        <div class="detail-row"><span class="detail-label">Periode</span><span class="detail-value" id="dr-period">-</span></div>
                        <div class="detail-row"><span class="detail-label">Mulai Sewa</span><span class="detail-value" id="dr-started">-</span></div>
                        <div class="detail-row"><span class="detail-label">Deadline</span><span class="detail-value" id="dr-deadline">-</span></div>
                        <div class="detail-row"><span class="detail-label">Status</span><span class="detail-value" id="dr-status">-</span></div>
                        <div class="detail-row"><span class="detail-label">Denda</span><span class="detail-value" id="dr-penalty">-</span></div>
                        <div class="detail-row"><span class="detail-label">Harga Sewa</span><span class="detail-value text-primary" id="dr-total">-</span></div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-secondary w-100 rounded-3" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: KONFIRMASI PENGEMBALIAN -->
    <div class="modal fade" id="modalKonfirmasiReturn" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-success"><i class="bi bi-check-circle-fill me-2"></i>Proses Pengembalian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 pt-2 pb-3">
                    <p class="text-muted mb-1">Proses pengembalian untuk:</p>
                    <p class="fw-bold mb-0" id="return-car">-</p>
                    <p class="text-muted small mb-2" id="return-customer">-</p>
                    <div id="return-penalty-info" class="alert alert-danger py-2 px-3 small d-none">
                        <i class="bi bi-exclamation-triangle me-1"></i>Denda keterlambatan: <strong id="return-penalty-amount">Rp 0</strong>
                    </div>
                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Status mobil akan otomatis berubah ke Available.</small>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2">
                    <button type="button" class="btn btn-light rounded-3 flex-fill" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="return-konfirmasi-link" class="btn btn-success rounded-3 flex-fill"><i class="bi bi-check-lg me-1"></i>Ya, Proses</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Detail Modal
        document.querySelectorAll('.btn-detail-return').forEach(btn => {
            btn.addEventListener('click', function () {
                document.getElementById('dr-id').textContent = '#' + this.dataset.id;
                document.getElementById('dr-car').textContent = this.dataset.car;
                document.getElementById('dr-customer').textContent = this.dataset.customer;
                document.getElementById('dr-phone').textContent = this.dataset.phone;
                document.getElementById('dr-type').textContent = this.dataset.rentalType;
                document.getElementById('dr-period').textContent = this.dataset.start + ' — ' + this.dataset.end + ' (' + this.dataset.days + ' hari)';
                document.getElementById('dr-started').textContent = this.dataset.started;
                document.getElementById('dr-deadline').textContent = this.dataset.deadline;
                document.getElementById('dr-total').textContent = 'Rp ' + this.dataset.total;
                const isOverdue = this.dataset.overdue === '1';
                if (isOverdue) {
                    document.getElementById('dr-status').innerHTML = '<span class="badge badge-overdue px-2 py-1 rounded-3">Terlambat ' + this.dataset.lateHours + ' jam</span>';
                    document.getElementById('dr-penalty').innerHTML = '<span class="text-danger fw-bold">Rp ' + this.dataset.penalty + '</span>';
                } else {
                    document.getElementById('dr-status').innerHTML = '<span class="badge badge-ontime px-2 py-1 rounded-3">Tepat Waktu</span>';
                    document.getElementById('dr-penalty').textContent = 'Tidak ada';
                }
                new bootstrap.Modal(document.getElementById('modalDetailReturn')).show();
            });
        });
        // Konfirmasi Return Modal
        document.querySelectorAll('.btn-proses-return').forEach(btn => {
            btn.addEventListener('click', function () {
                document.getElementById('return-car').textContent = this.dataset.car;
                document.getElementById('return-customer').textContent = 'Customer: ' + this.dataset.customer;
                document.getElementById('return-konfirmasi-link').href = '../handlers/admin_handler.php?action=return_rental&id=' + this.dataset.id;
                const penaltyInfo = document.getElementById('return-penalty-info');
                if (this.dataset.overdue === '1') {
                    penaltyInfo.classList.remove('d-none');
                    document.getElementById('return-penalty-amount').textContent = 'Rp ' + this.dataset.penalty;
                } else {
                    penaltyInfo.classList.add('d-none');
                }
                new bootstrap.Modal(document.getElementById('modalKonfirmasiReturn')).show();
            });
        });
        // Auto-dismiss alert
        const alertEl = document.querySelector('.alert-dismissible');
        if (alertEl) { setTimeout(() => { bootstrap.Alert.getOrCreateInstance(alertEl).close(); }, 5000); }
    });
    </script>
</body>
</html>