<?php
/**
 * Halaman Manajemen Kategori - Panel Admin
 *
 * Menampilkan daftar kategori kendaraan dari database secara dinamis.
 * Menyediakan fitur CRUD lengkap: Tambah, Edit, dan Hapus kategori.
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
require_once '../classes/CarType.php';

$db        = Database::getInstance()->getConnection();
$typeModel = new CarType($db);

// Ambil semua kategori dari database
$categories = $typeModel->getAll();
$activePage = 'kategori';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kategori - RentalKu Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
            color: #333;
        }

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

        /* UI Elements */
        .btn-dark-custom {
            background-color: #0b0e11;
            color: white;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            border: none;
            transition: background-color 0.2s;
        }
        .btn-dark-custom:hover {
            background-color: #212529;
            color: white;
        }

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
            padding: 16px 12px;
            border-bottom: 1px solid #f8f9fa;
            vertical-align: middle;
            font-size: 14px;
        }
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        .table tbody tr:hover {
            background-color: #f9fbff;
        }

        /* Category badge */
        .cat-icon-wrap {
            width: 42px;
            height: 42px;
            background: #eef4ff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #0d6efd;
        }

        /* Stat Cards */
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 22px 28px;
            border: 1px solid #efefef;
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        .stat-icon-blue  { background: #eef4ff; color: #0d6efd; }
        .stat-icon-green { background: #d1e7dd; color: #0f5132; }
        .stat-label { font-size: 13px; color: #888; margin-bottom: 2px; }
        .stat-value { font-size: 26px; font-weight: 700; color: #212529; line-height: 1; }

        .form-label-required::after {
            content: " *";
            color: red;
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

            <!-- Page Title + Tombol Tambah -->
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h1 class="fw-bold mb-1">Manajemen Kategori</h1>
                    <p class="text-muted">Kelola kategori kendaraan yang tersedia di sistem</p>
                </div>
                <button class="btn btn-dark-custom d-flex align-items-center gap-2"
                        data-bs-toggle="modal" data-bs-target="#modalAddKategori">
                    <i class="bi bi-plus-lg"></i> Tambah Kategori
                </button>
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

            <!-- Stat Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="stat-card shadow-sm">
                        <div class="stat-icon stat-icon-blue">
                            <i class="bi bi-list-ul"></i>
                        </div>
                        <div>
                            <div class="stat-label">Total Kategori</div>
                            <div class="stat-value"><?= count($categories) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card shadow-sm">
                        <div class="stat-icon stat-icon-green">
                            <i class="bi bi-car-front"></i>
                        </div>
                        <div>
                            <div class="stat-label">Total Armada Terkait</div>
                            <?php
                                // Hitung total mobil yang punya kategori
                                try {
                                    $stmt = $db->query("SELECT COUNT(*) FROM cars WHERE type_id IS NOT NULL");
                                    $totalCarsWithType = (int) $stmt->fetchColumn();
                                } catch (Exception $e) {
                                    $totalCarsWithType = 0;
                                }
                            ?>
                            <div class="stat-value"><?= $totalCarsWithType ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Kategori -->
            <div class="table-container shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">
                        Daftar Kategori
                        <span class="badge bg-light text-secondary fw-normal ms-1"><?= count($categories) ?></span>
                    </h5>
                </div>

                <?php if (empty($categories)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-list-ul fs-1 d-block mb-3 text-secondary"></i>
                        <p>Belum ada kategori yang tersedia. Klik <strong>Tambah Kategori</strong> untuk memulai.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th width="60">ID</th>
                                    <th>Nama Kategori</th>
                                    <th>Jumlah Armada</th>
                                    <th class="text-end" width="130">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat):
                                    $carCount = $typeModel->countCarsInType($cat['id']);
                                ?>
                                    <tr>
                                        <td class="text-muted fw-semibold">#<?= $cat['id'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="cat-icon-wrap">
                                                    <i class="bi bi-tag-fill"></i>
                                                </div>
                                                <span class="fw-semibold"><?= htmlspecialchars($cat['name']) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($carCount > 0): ?>
                                                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2 rounded-3">
                                                    <i class="bi bi-car-front me-1"></i><?= $carCount ?> Mobil
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small"><i class="bi bi-dash-circle me-1"></i>Belum ada</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <!-- Tombol Edit -->
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-warning rounded-3 me-1 btn-edit"
                                                    data-id="<?= $cat['id'] ?>"
                                                    data-name="<?= htmlspecialchars($cat['name']) ?>"
                                                    title="Edit Kategori">
                                                <i class="bi bi-pencil me-1"></i> Edit
                                            </button>

                                            <!-- Tombol Hapus -->
                                            <?php if ($carCount > 0): ?>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-secondary rounded-3"
                                                        disabled
                                                        title="Tidak bisa dihapus — masih digunakan oleh <?= $carCount ?> mobil">
                                                    <i class="bi bi-lock me-1"></i> Hapus
                                                </button>
                                            <?php else: ?>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger rounded-3 btn-hapus"
                                                        data-id="<?= $cat['id'] ?>"
                                                        data-name="<?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>"
                                                        title="Hapus Kategori">
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

        </div><!-- /p-5 -->
    </div><!-- /main-content -->


    <!-- ==========================================
    MODAL KONFIRMASI HAPUS
    ========================================== -->
    <div class="modal fade" id="modalHapusKonfirmasi" tabindex="-1" aria-labelledby="modalHapusLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger" id="modalHapusLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pt-2 pb-3">
                    <p class="text-muted mb-1">Anda yakin ingin menghapus kategori:</p>
                    <p class="fw-bold fs-5 mb-0" id="hapus-nama-kategori">-</p>
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

    <!-- ==========================================
    MODAL 1: TAMBAH KATEGORI
    ========================================== -->
    <div class="modal fade" id="modalAddKategori" tabindex="-1" aria-labelledby="modalAddKategoriLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalAddKategoriLabel">
                        <i class="bi bi-plus-circle-fill text-primary me-2"></i>Tambah Kategori Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../handlers/admin_handler.php" method="POST">
                    <input type="hidden" name="action" value="add_kategori">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="add-name" class="form-label form-label-required fw-semibold">Nama Kategori</label>
                            <input type="text" id="add-name" name="name" class="form-control form-control-lg rounded-3"
                                   placeholder="Contoh: SUV, Sedan, MPV, Hatchback..."
                                   maxlength="50" required autofocus>
                            <div class="form-text text-muted">Maksimal 50 karakter. Nama kategori harus unik.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn btn-light px-4 rounded-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 rounded-3">
                            <i class="bi bi-save me-1"></i> Simpan Kategori
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ==========================================
    MODAL 2: EDIT KATEGORI
    ========================================== -->
    <div class="modal fade" id="modalEditKategori" tabindex="-1" aria-labelledby="modalEditKategoriLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalEditKategoriLabel">
                        <i class="bi bi-pencil-square text-warning me-2"></i>Edit Kategori
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../handlers/admin_handler.php" method="POST">
                    <input type="hidden" name="action" value="edit_kategori">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="edit-name" class="form-label form-label-required fw-semibold">Nama Kategori</label>
                            <input type="text" id="edit-name" name="name" class="form-control form-control-lg rounded-3"
                                   placeholder="Nama kategori..." maxlength="50" required>
                            <div class="form-text text-muted">Maksimal 50 karakter. Nama kategori harus unik.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn btn-light px-4 rounded-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning px-4 rounded-3">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // ==========================================
            // Populate Modal EDIT dari tombol btn-edit
            // ==========================================
            const editButtons = document.querySelectorAll('.btn-edit');

            editButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const id   = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');

                    document.getElementById('edit-id').value   = id;
                    document.getElementById('edit-name').value = name;

                    const modal = new bootstrap.Modal(document.getElementById('modalEditKategori'));
                    modal.show();
                });
            });

            // ==========================================
            // LOGIKA TOMBOL HAPUS → Modal Konfirmasi
            // ==========================================
            const hapusButtons = document.querySelectorAll('.btn-hapus');
            const hapusNamaEl  = document.getElementById('hapus-nama-kategori');
            const hapusLinkEl  = document.getElementById('hapus-konfirmasi-link');

            hapusButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const id   = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');

                    // Isi nama kategori di modal
                    hapusNamaEl.textContent = name;

                    // Set href link konfirmasi ke handler
                    hapusLinkEl.setAttribute(
                        'href',
                        '../handlers/admin_handler.php?action=delete_kategori&id=' + id
                    );

                    // Tampilkan modal konfirmasi
                    const modal = new bootstrap.Modal(document.getElementById('modalHapusKonfirmasi'));
                    modal.show();
                });
            });

            // Auto-dismiss alert setelah 5 detik
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