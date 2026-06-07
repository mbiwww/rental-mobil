<?php
/**
 * Halaman Data Customer - Panel Admin
 *
 * Menampilkan daftar customer terdaftar secara dinamis dari database.
 * Menyediakan fitur: Pencarian, Detail Customer, dan Hapus Customer.
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
require_once '../classes/User.php';

$db        = Database::getInstance()->getConnection();
$userModel = new User($db);

// Filter Pencarian
$search = $_GET['search'] ?? '';

// Ambil data dari model
$customers       = $userModel->getAllCustomersWithRentalCount($search);
$totalCustomers  = $userModel->countCustomers();
$activeCustomers = $userModel->countActiveCustomers();
$withRentals     = $userModel->countCustomersWithRentals();
$activePage      = 'customer';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Customer - RentalKu Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; color: #333; }

        /* Sidebar */
        .sidebar { width: 260px; height: 100vh; background: white; position: fixed; border-right: 1px solid #eee; padding: 20px; z-index: 1000; }
        .nav-link { color: #6c757d; padding: 12px 15px; border-radius: 10px; margin-bottom: 5px; display: flex; align-items: center; text-decoration: none; transition: all 0.2s ease; }
        .nav-link:hover, .nav-link.active { background: #eef4ff; color: #0d6efd; font-weight: 600; }

        /* Main Content */
        .main-content { margin-left: 260px; min-height: 100vh; }
        .top-header { background: white; padding: 15px 40px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }

        /* Stat Cards */
        .stat-card { background: white; border-radius: 16px; padding: 22px 28px; border: 1px solid #efefef; display: flex; align-items: center; gap: 18px; }
        .stat-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
        .stat-icon-blue   { background: #eef4ff; color: #0d6efd; }
        .stat-icon-green  { background: #d1e7dd; color: #0f5132; }
        .stat-icon-purple { background: #e8daef; color: #6f42c1; }
        .stat-icon-orange { background: #ffecd2; color: #e67e22; }
        .stat-label { font-size: 13px; color: #888; margin-bottom: 2px; }
        .stat-value { font-size: 26px; font-weight: 700; color: #212529; line-height: 1; }

        /* Table */
        .table-container { background: white; border-radius: 20px; padding: 30px; border: 1px solid #efefef; }
        .table thead th { border-bottom: 2px solid #f0f0f0; color: #555; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; padding-bottom: 15px; }
        .table tbody td { padding: 16px 12px; border-bottom: 1px solid #f8f9fa; vertical-align: middle; font-size: 14px; }
        .table tbody tr:last-child td { border-bottom: none; }
        .table tbody tr:hover { background-color: #f9fbff; }

        /* Avatar */
        .avatar-circle { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px; color: #fff; flex-shrink: 0; }

        /* Detail Modal */
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0f0f0; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #888; font-size: 13px; }
        .detail-value { font-weight: 600; font-size: 14px; text-align: right; }

        .search-input { background-color: #fff; border: 1px solid #dee2e6; border-radius: 10px; padding: 12px 20px; }
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
                <a href="../pages/login.php" class="text-muted" title="Logout">
                    <i class="bi bi-box-arrow-right fs-4"></i>
                </a>
            </div>
        </header>

        <div class="p-5">

            <!-- Page Title -->
            <div class="mb-4">
                <h1 class="fw-bold mb-1">Data Customer</h1>
                <p class="text-muted">Kelola data pelanggan terdaftar di sistem</p>
            </div>

            <!-- Alert Notifikasi -->
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
                <div class="col-md-3">
                    <div class="stat-card shadow-sm">
                        <div class="stat-icon stat-icon-blue"><i class="bi bi-people-fill"></i></div>
                        <div>
                            <div class="stat-label">Total Customer</div>
                            <div class="stat-value"><?= $totalCustomers ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card shadow-sm">
                        <div class="stat-icon stat-icon-green"><i class="bi bi-person-check-fill"></i></div>
                        <div>
                            <div class="stat-label">Pernah Menyewa</div>
                            <div class="stat-value"><?= $withRentals ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card shadow-sm">
                        <div class="stat-icon stat-icon-orange"><i class="bi bi-person-lines-fill"></i></div>
                        <div>
                            <div class="stat-label">Sedang Aktif</div>
                            <div class="stat-value"><?= $activeCustomers ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card shadow-sm">
                        <div class="stat-icon stat-icon-purple"><i class="bi bi-person-plus-fill"></i></div>
                        <div>
                            <div class="stat-label">Belum Menyewa</div>
                            <div class="stat-value"><?= $totalCustomers - $withRentals ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search -->
            <div class="table-container shadow-sm mb-4" style="padding: 20px;">
                <form method="GET" action="customer.php" class="row g-3 align-items-center">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control search-input border-start-0 ps-0"
                                   placeholder="Cari nama, email, NIK, atau no. telepon customer..."
                                   value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4 rounded-3"><i class="bi bi-search me-1"></i> Cari</button>
                        <?php if (!empty($search)): ?>
                            <a href="customer.php" class="btn btn-outline-secondary px-3 rounded-3 d-flex align-items-center">Reset</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="table-container shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">
                        Daftar Customer
                        <span class="badge bg-light text-secondary fw-normal ms-1"><?= count($customers) ?></span>
                    </h5>
                    <?php if (!empty($search)): ?>
                        <small class="text-muted">Hasil pencarian: "<?= htmlspecialchars($search) ?>"</small>
                    <?php endif; ?>
                </div>

                <?php if (empty($customers)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-people fs-1 d-block mb-3 text-secondary"></i>
                        <?php if (!empty($search)): ?>
                            <p>Tidak ada customer yang cocok dengan pencarian "<strong><?= htmlspecialchars($search) ?></strong>".</p>
                        <?php else: ?>
                            <p>Belum ada customer yang terdaftar di sistem.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th width="50">ID</th>
                                    <th>Customer</th>
                                    <th>Kontak</th>
                                    <th>Total Sewa</th>
                                    <th>Total Belanja</th>
                                    <th>Bergabung</th>
                                    <th class="text-end" width="130">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $colors = ['#0d6efd','#6f42c1','#d63384','#fd7e14','#20c997','#0dcaf0','#198754','#dc3545'];
                                foreach ($customers as $i => $cust):
                                    $initials = strtoupper(substr($cust['name'], 0, 1));
                                    $bgColor = $colors[$i % count($colors)];
                                    $activeRentals = (int)($cust['active_rentals'] ?? 0);
                                ?>
                                    <tr>
                                        <td class="text-muted fw-semibold">#<?= $cust['id'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar-circle" style="background-color: <?= $bgColor ?>">
                                                    <?= $initials ?>
                                                </div>
                                                <div>
                                                    <p class="mb-0 fw-bold"><?= htmlspecialchars($cust['name']) ?></p>
                                                    <small class="text-muted">NIK: <?= htmlspecialchars($cust['nik']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column" style="font-size: 13px;">
                                                <span class="text-dark"><i class="bi bi-envelope me-1 text-muted"></i><?= htmlspecialchars($cust['email']) ?></span>
                                                <span class="text-muted"><i class="bi bi-phone me-1"></i><?= htmlspecialchars($cust['phone']) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ((int)$cust['total_rentals'] > 0): ?>
                                                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2 rounded-3">
                                                    <i class="bi bi-car-front me-1"></i><?= $cust['total_rentals'] ?> kali
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small"><i class="bi bi-dash-circle me-1"></i>Belum ada</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold text-success">
                                            <?php if ((float)$cust['total_spent'] > 0): ?>
                                                Rp <?= number_format($cust['total_spent'], 0, ',', '.') ?>
                                            <?php else: ?>
                                                <span class="text-muted fw-normal">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= date('d M Y', strtotime($cust['created_at'])) ?></small>
                                        </td>
                                        <td class="text-end">
                                            <!-- Tombol Detail -->
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-info rounded-3 me-1 btn-detail"
                                                    data-id="<?= $cust['id'] ?>"
                                                    data-name="<?= htmlspecialchars($cust['name']) ?>"
                                                    data-nik="<?= htmlspecialchars($cust['nik']) ?>"
                                                    data-email="<?= htmlspecialchars($cust['email']) ?>"
                                                    data-phone="<?= htmlspecialchars($cust['phone']) ?>"
                                                    data-address="<?= htmlspecialchars($cust['address'] ?? '-') ?>"
                                                    data-total-rentals="<?= $cust['total_rentals'] ?>"
                                                    data-total-spent="<?= number_format($cust['total_spent'], 0, ',', '.') ?>"
                                                    data-active-rentals="<?= $activeRentals ?>"
                                                    data-created="<?= date('d M Y, H:i', strtotime($cust['created_at'])) ?>"
                                                    title="Lihat Detail">
                                                <i class="bi bi-eye me-1"></i> Detail
                                            </button>

                                            <!-- Tombol Hapus -->
                                            <?php if ($activeRentals > 0): ?>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-secondary rounded-3"
                                                        disabled
                                                        title="Tidak bisa dihapus — masih memiliki <?= $activeRentals ?> rental aktif">
                                                    <i class="bi bi-lock me-1"></i> Hapus
                                                </button>
                                            <?php else: ?>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger rounded-3 btn-hapus"
                                                        data-id="<?= $cust['id'] ?>"
                                                        data-name="<?= htmlspecialchars($cust['name'], ENT_QUOTES, 'UTF-8') ?>"
                                                        title="Hapus Customer">
                                                    <i class="bi bi-trash me-1"></i> Hapus
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- ==========================================
    MODAL: DETAIL CUSTOMER
    ========================================== -->
    <div class="modal fade" id="modalDetailCustomer" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalDetailLabel">
                        <i class="bi bi-person-badge-fill text-info me-2"></i>Detail Customer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Customer Header -->
                    <div class="text-center mb-4">
                        <div class="avatar-circle mx-auto mb-3" id="detail-avatar" style="width:64px;height:64px;font-size:28px;background:#0d6efd;">?</div>
                        <h4 class="fw-bold mb-1" id="detail-name">-</h4>
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-3 px-3 py-1" id="detail-rental-badge">0 Rental</span>
                    </div>

                    <!-- Info Rows -->
                    <div class="bg-light rounded-4 p-3 border">
                        <div class="detail-row">
                            <span class="detail-label"><i class="bi bi-hash me-1"></i>ID</span>
                            <span class="detail-value" id="detail-id">-</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><i class="bi bi-credit-card me-1"></i>NIK</span>
                            <span class="detail-value" id="detail-nik">-</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><i class="bi bi-envelope me-1"></i>Email</span>
                            <span class="detail-value" id="detail-email">-</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><i class="bi bi-phone me-1"></i>Telepon</span>
                            <span class="detail-value" id="detail-phone">-</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><i class="bi bi-geo-alt me-1"></i>Alamat</span>
                            <span class="detail-value" id="detail-address" style="max-width:55%;text-align:right;">-</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><i class="bi bi-wallet2 me-1"></i>Total Belanja</span>
                            <span class="detail-value text-success" id="detail-spent">Rp 0</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><i class="bi bi-activity me-1"></i>Rental Aktif</span>
                            <span class="detail-value" id="detail-active">0</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><i class="bi bi-calendar-event me-1"></i>Bergabung</span>
                            <span class="detail-value" id="detail-created">-</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-secondary w-100 rounded-3" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==========================================
    MODAL: KONFIRMASI HAPUS
    ========================================== -->
    <div class="modal fade" id="modalHapusCustomer" tabindex="-1" aria-labelledby="modalHapusCustLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger" id="modalHapusCustLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pt-2 pb-3">
                    <p class="text-muted mb-1">Anda yakin ingin menghapus customer:</p>
                    <p class="fw-bold fs-5 mb-0" id="hapus-nama-customer">-</p>
                    <small class="text-danger"><i class="bi bi-info-circle me-1"></i>Aksi ini tidak bisa dibatalkan.</small>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2">
                    <button type="button" class="btn btn-light rounded-3 flex-fill" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="hapus-konfirmasi-link" class="btn btn-danger rounded-3 flex-fill">
                        <i class="bi bi-trash me-1"></i> Ya, Hapus
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // ==========================================
            // MODAL DETAIL CUSTOMER
            // ==========================================
            document.querySelectorAll('.btn-detail').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const name = this.dataset.name;
                    const initial = name.charAt(0).toUpperCase();

                    document.getElementById('detail-avatar').textContent = initial;
                    document.getElementById('detail-name').textContent = name;
                    document.getElementById('detail-id').textContent = '#' + this.dataset.id;
                    document.getElementById('detail-nik').textContent = this.dataset.nik;
                    document.getElementById('detail-email').textContent = this.dataset.email;
                    document.getElementById('detail-phone').textContent = this.dataset.phone;
                    document.getElementById('detail-address').textContent = this.dataset.address;
                    document.getElementById('detail-spent').textContent = 'Rp ' + this.dataset.totalSpent;
                    document.getElementById('detail-active').textContent = this.dataset.activeRentals;
                    document.getElementById('detail-created').textContent = this.dataset.created;
                    document.getElementById('detail-rental-badge').textContent = this.dataset.totalRentals + ' Rental';

                    new bootstrap.Modal(document.getElementById('modalDetailCustomer')).show();
                });
            });

            // ==========================================
            // MODAL KONFIRMASI HAPUS
            // ==========================================
            const hapusNamaEl = document.getElementById('hapus-nama-customer');
            const hapusLinkEl = document.getElementById('hapus-konfirmasi-link');

            document.querySelectorAll('.btn-hapus').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    hapusNamaEl.textContent = this.dataset.name;
                    hapusLinkEl.setAttribute('href',
                        '../handlers/admin_handler.php?action=delete_customer&id=' + this.dataset.id
                    );
                    new bootstrap.Modal(document.getElementById('modalHapusCustomer')).show();
                });
            });

            // Auto-dismiss alert
            const alertEl = document.querySelector('.alert');
            if (alertEl) {
                setTimeout(function () {
                    bootstrap.Alert.getOrCreateInstance(alertEl).close();
                }, 5000);
            }
        });
    </script>
</body>
</html>