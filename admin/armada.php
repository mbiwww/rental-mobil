<?php
/**
 * Halaman Manajemen Armada - Panel Admin
 * 
 * Menampilkan seluruh daftar mobil di database secara dinamis.
 * Menyediakan filter pencarian, filter status, dan fitur CRUD lengkap (Tambah, Edit, Hapus).
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
require_once '../classes/Car.php';
require_once '../classes/CarType.php';

$db = Database::getInstance()->getConnection();
$carModel = new Car($db);
$typeModel = new CarType($db);

// Filter Pencarian & Status
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

// Ambil data dari model
$cars = $carModel->getAll($search, $status);
$carTypes = $typeModel->getAll();
$activePage = 'armada';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Armada - RentalKu</title>
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
        .search-input, .status-select {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 12px 20px;
        }
        .table-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            border: 1px solid #efefef;
        }
        .img-mobil {
            width: 80px;
            height: 55px;
            object-fit: cover;
            border-radius: 8px;
            background-color: #f1f3f5;
        }
        
        /* Status Badges */
        .badge-available {
            background-color: #d1e7dd;
            color: #0f5132;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .badge-rented {
            background-color: #fff3cd;
            color: #664d03;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .badge-maintenance {
            background-color: #f8d7da;
            color: #842029;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .cursor-pointer {
            cursor: pointer;
        }
        .text-xs {
            font-size: 0.75rem;
        }
        
        /* Form Label */
        .form-label-required::after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>

    <!-- Sidebar Reusable -->
    <?php include '../includes/sidebar_admin.php'; ?>

    <!-- Content Area -->
    <div class="main-content">
        <!-- Top Header / Profile -->
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
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h1 class="fw-bold mb-1">Manajemen Armada</h1>
                    <p class="text-muted">Kelola data armada mobil rental dengan mudah</p>
                </div>
                <button class="btn btn-dark-custom d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalAddCar">
                    <i class="bi bi-plus-lg"></i> Tambah Mobil
                </button>
            </div>

            <!-- Notification Alert -->
            <?php if (isset($_GET['status'])): ?>
                <div class="alert alert-<?= $_GET['status'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi <?= $_GET['status'] === 'success' ? 'bi-check-circle-fill fs-5' : 'bi-exclamation-triangle-fill fs-5' ?> me-2"></i>
                        <div>
                            <?= htmlspecialchars($_GET['msg'] ?? '') ?>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Filter & Search Section -->
            <div class="table-container shadow-sm mb-4" style="padding: 20px;">
                <form method="GET" action="armada.php" class="row g-3 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control search-input border-start-0 ps-0" placeholder="Cari merk atau model mobil..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select status-select">
                            <option value="">Semua Status</option>
                            <option value="available" <?= $status === 'available' ? 'selected' : '' ?>>Tersedia (Available)</option>
                            <option value="rented" <?= $status === 'rented' ? 'selected' : '' ?>>Disewa (Rented)</option>
                            <option value="maintenance" <?= $status === 'maintenance' ? 'selected' : '' ?>>Servis (Maintenance)</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4 rounded-3"><i class="bi bi-funnel"></i> Filter</button>
                        <?php if (!empty($search) || !empty($status)): ?>
                            <a href="armada.php" class="btn btn-outline-secondary px-3 rounded-3 d-flex align-items-center">Reset</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Table Section -->
            <div class="table-container shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Daftar Mobil (<?= count($cars) ?>)</h5>
                </div>
                
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th>Mobil</th>
                                <th>Kategori</th>
                                <th>Harga / Hari</th>
                                <th>Status</th>
                                <th>Spesifikasi</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($cars)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-car-front fs-1 d-block mb-3"></i>
                                        Belum ada data mobil yang cocok dengan pencarian Anda.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($cars as $car): ?>
                                    <?php 
                                    // Penentuan gambar mobil
                                    $imagePath = '../assets/uploads/cars/' . $car['image'];
                                    $imageSrc = (!empty($car['image']) && file_exists($imagePath)) ? $imagePath : 'https://placehold.co/100x70/eaeaea/666666?text=' . urlencode($car['brand']);
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="<?= $imageSrc ?>" class="img-mobil shadow-sm" alt="Foto Mobil">
                                                <div>
                                                    <p class="mb-0 fw-bold"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></p>
                                                    <small class="text-muted"><?= $car['year'] ?> • <?= htmlspecialchars($car['engine_type'] ?? '-') ?></small>
                                                    <?php if (!empty($car['plate_number'])): ?>
                                                        <br><span class="badge bg-dark bg-opacity-75 px-2 py-1 mt-1" style="font-size: 11px; letter-spacing: 1px;"><i class="bi bi-credit-card-2-front me-1"></i><?= htmlspecialchars($car['plate_number']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary px-3 py-2 rounded-3 text-white small"><?= htmlspecialchars($car['type_name'] ?? 'Umum') ?></span>
                                        </td>
                                        <td class="fw-bold text-primary">
                                            Rp <?= number_format($car['price_per_day'], 0, ',', '.') ?>
                                        </td>
                                        <td>
                                            <!-- Dropdown Status Cepat -->
                                            <div class="dropdown">
                                                <button class="btn p-0 border-0 text-start" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <?php if ($car['status'] === 'available'): ?>
                                                        <div class="badge-available cursor-pointer">Tersedia <i class="bi bi-chevron-down text-xs"></i></div>
                                                    <?php elseif ($car['status'] === 'rented'): ?>
                                                        <div class="badge-rented cursor-pointer">Disewa <i class="bi bi-chevron-down text-xs"></i></div>
                                                    <?php else: ?>
                                                        <div class="badge-maintenance cursor-pointer">Servis <i class="bi bi-chevron-down text-xs"></i></div>
                                                    <?php endif; ?>
                                                </button>
                                                <ul class="dropdown-menu shadow border-0 rounded-3 text-sm">
                                                    <li><h6 class="dropdown-header text-xs text-uppercase font-semibold">Ubah Status Ke:</h6></li>
                                                    <li><a class="dropdown-item" href="../handlers/admin_handler.php?action=update_status&id=<?= $car['id'] ?>&status=available"><i class="bi bi-check-circle-fill text-success me-2"></i>Tersedia</a></li>
                                                    <li><a class="dropdown-item" href="../handlers/admin_handler.php?action=update_status&id=<?= $car['id'] ?>&status=rented"><i class="bi bi-arrow-right-circle-fill text-warning me-2"></i>Disewa</a></li>
                                                    <li><a class="dropdown-item" href="../handlers/admin_handler.php?action=update_status&id=<?= $car['id'] ?>&status=maintenance"><i class="bi bi-wrench-adjustable text-danger me-2"></i>Servis (Maint.)</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted d-block text-capitalize"><i class="bi bi-gear me-1"></i><?= $car['transmission'] ?></small>
                                            <small class="text-muted"><i class="bi bi-people me-1"></i><?= $car['passenger_capacity'] ?> Kursi • <i class="bi bi-briefcase me-1"></i><?= $car['luggage_capacity'] ?> Bagasi</small>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <!-- Detail Button -->
                                                <button type="button" class="btn btn-link text-info p-1 btn-view" 
                                                        data-brand="<?= htmlspecialchars($car['brand']) ?>"
                                                        data-model="<?= htmlspecialchars($car['model']) ?>"
                                                        data-year="<?= $car['year'] ?>"
                                                        data-plate-number="<?= htmlspecialchars($car['plate_number'] ?? '-') ?>"
                                                        data-category="<?= htmlspecialchars($car['type_name'] ?? 'Umum') ?>"
                                                        data-engine-type="<?= htmlspecialchars($car['engine_type'] ?? '-') ?>"
                                                        data-transmission="<?= $car['transmission'] ?>"
                                                        data-passenger-capacity="<?= $car['passenger_capacity'] ?>"
                                                        data-luggage-capacity="<?= $car['luggage_capacity'] ?>"
                                                        data-price="<?= number_format($car['price_per_day'], 0, ',', '.') ?>"
                                                        data-status="<?= $car['status'] ?>"
                                                        data-image="<?= $imageSrc ?>"
                                                        data-description="<?= htmlspecialchars($car['description'] ?? 'Tidak ada deskripsi.') ?>"
                                                        title="Lihat Detail">
                                                    <i class="bi bi-eye fs-5"></i>
                                                </button>
                                                
                                                <!-- Edit Button -->
                                                <button type="button" class="btn btn-link text-warning p-1 btn-edit" 
                                                        data-id="<?= $car['id'] ?>"
                                                        data-brand="<?= htmlspecialchars($car['brand']) ?>"
                                                        data-model="<?= htmlspecialchars($car['model']) ?>"
                                                        data-year="<?= $car['year'] ?>"
                                                        data-type-id="<?= $car['type_id'] ?>"
                                                        data-plate-number="<?= htmlspecialchars($car['plate_number'] ?? '') ?>"
                                                        data-engine-type="<?= htmlspecialchars($car['engine_type'] ?? '') ?>"
                                                        data-transmission="<?= $car['transmission'] ?>"
                                                        data-passenger-capacity="<?= $car['passenger_capacity'] ?>"
                                                        data-luggage-capacity="<?= $car['luggage_capacity'] ?>"
                                                        data-price-per-day="<?= $car['price_per_day'] ?>"
                                                        data-status="<?= $car['status'] ?>"
                                                        data-description="<?= htmlspecialchars($car['description'] ?? '') ?>"
                                                        title="Ubah Data">
                                                    <i class="bi bi-pencil fs-5"></i>
                                                </button>

                                                <!-- Delete Button -->
                                                <a href="../handlers/admin_handler.php?action=delete_car&id=<?= $car['id'] ?>" 
                                                   class="btn btn-link text-danger p-1 btn-delete" 
                                                   onclick="return confirm('Apakah Anda yakin ingin menghapus mobil <?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?> secara permanen?');"
                                                   title="Hapus Mobil">
                                                    <i class="bi bi-trash fs-5"></i>
                                                </a>
                                            </div>
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
    MODAL 1: TAMBAH MOBIL
    ========================================== -->
    <div class="modal fade" id="modalAddCar" tabindex="-1" aria-labelledby="modalAddCarLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalAddCarLabel"><i class="bi bi-car-front-fill text-primary me-2"></i>Tambah Mobil Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../handlers/admin_handler.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_car">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label form-label-required fw-semibold small">Merk Mobil</label>
                                <input type="text" name="brand" class="form-control rounded-3" placeholder="Contoh: Toyota, Honda" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-required fw-semibold small">Model Mobil</label>
                                <input type="text" name="model" class="form-control rounded-3" placeholder="Contoh: Avanza, Civic" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">No. Plat Mobil</label>
                                <input type="text" name="plate_number" class="form-control rounded-3" placeholder="Contoh: BP 1234 XX" style="text-transform: uppercase;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-required fw-semibold small">Tahun</label>
                                <input type="number" name="year" class="form-control rounded-3" placeholder="Contoh: 2023" required min="1990" max="<?= date('Y') + 1 ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Kategori / Tipe</label>
                                <select name="type_id" class="form-select rounded-3">
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($carTypes as $type): ?>
                                        <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Tipe Mesin</label>
                                <input type="text" name="engine_type" class="form-control rounded-3" placeholder="Contoh: 1.5L DOHC, Diesel">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Transmisi</label>
                                <select name="transmission" class="form-select rounded-3">
                                    <option value="manual">Manual</option>
                                    <option value="automatic">Automatic</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Kapasitas Penumpang (Orang)</label>
                                <input type="number" name="passenger_capacity" class="form-control rounded-3" value="5" min="1" max="20">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Kapasitas Bagasi (Koper)</label>
                                <input type="number" name="luggage_capacity" class="form-control rounded-3" value="2" min="0" max="10">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label form-label-required fw-semibold small">Harga Sewa / Hari (Rupiah)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Rp</span>
                                    <input type="number" name="price_per_day" class="form-control rounded-end-3" placeholder="Contoh: 350000" required min="10000">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Status Awal</label>
                                <select name="status" class="form-select rounded-3">
                                    <option value="available">Tersedia (Available)</option>
                                    <option value="rented">Disewa (Rented)</option>
                                    <option value="maintenance">Dalam Servis (Maintenance)</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Deskripsi Mobil</label>
                                <textarea name="description" class="form-control rounded-3" rows="3" placeholder="Tulis deskripsi detail mobil, fasilitas, dll..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Unggah Gambar Mobil</label>
                                <input type="file" name="image" class="form-control rounded-3" accept="image/png, image/jpeg, image/jpg">
                                <div class="form-text text-xs text-muted">Format yang didukung: JPG, JPEG, PNG. Ukuran maksimal 2MB.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light px-4 rounded-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 rounded-3"><i class="bi bi-save me-1"></i> Simpan Mobil</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ==========================================
    MODAL 2: EDIT MOBIL
    ========================================== -->
    <div class="modal fade" id="modalEditCar" tabindex="-1" aria-labelledby="modalEditCarLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalEditCarLabel"><i class="bi bi-pencil-square text-warning me-2"></i>Ubah Data Mobil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../handlers/admin_handler.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit_car">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label form-label-required fw-semibold small">Merk Mobil</label>
                                <input type="text" name="brand" id="edit-brand" class="form-control rounded-3" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-required fw-semibold small">Model Mobil</label>
                                <input type="text" name="model" id="edit-model" class="form-control rounded-3" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">No. Plat Mobil</label>
                                <input type="text" name="plate_number" id="edit-plate-number" class="form-control rounded-3" style="text-transform: uppercase;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-required fw-semibold small">Tahun</label>
                                <input type="number" name="year" id="edit-year" class="form-control rounded-3" required min="1990" max="<?= date('Y') + 1 ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Kategori / Tipe</label>
                                <select name="type_id" id="edit-type-id" class="form-select rounded-3">
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($carTypes as $type): ?>
                                        <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Tipe Mesin</label>
                                <input type="text" name="engine_type" id="edit-engine-type" class="form-control rounded-3">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Transmisi</label>
                                <select name="transmission" id="edit-transmission" class="form-select rounded-3">
                                    <option value="manual">Manual</option>
                                    <option value="automatic">Automatic</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Kapasitas Penumpang</label>
                                <input type="number" name="passenger_capacity" id="edit-passenger-capacity" class="form-control rounded-3" min="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Kapasitas Bagasi</label>
                                <input type="number" name="luggage_capacity" id="edit-luggage-capacity" class="form-control rounded-3" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label form-label-required fw-semibold small">Harga Sewa / Hari (Rupiah)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Rp</span>
                                    <input type="number" name="price_per_day" id="edit-price-per-day" class="form-control rounded-end-3" required min="10000">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Status Mobil</label>
                                <select name="status" id="edit-status" class="form-select rounded-3">
                                    <option value="available">Tersedia (Available)</option>
                                    <option value="rented">Disewa (Rented)</option>
                                    <option value="maintenance">Dalam Servis (Maintenance)</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Deskripsi Mobil</label>
                                <textarea name="description" id="edit-description" class="form-control rounded-3" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Ubah Gambar Mobil</label>
                                <input type="file" name="image" class="form-control rounded-3" accept="image/png, image/jpeg, image/jpg">
                                <div class="form-text text-xs text-muted">Biarkan kosong jika tidak ingin mengubah gambar saat ini. Format didukung: JPG, JPEG, PNG. Ukuran maksimal 2MB.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light px-4 rounded-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning px-4 rounded-3"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ==========================================
    MODAL 3: DETAIL MOBIL (VIEW)
    ========================================== -->
    <div class="modal fade" id="modalViewCar" tabindex="-1" aria-labelledby="modalViewCarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalViewCarLabel"><i class="bi bi-eye-fill text-info me-2"></i>Detail Informasi Mobil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <img id="view-image" src="" class="img-fluid rounded-4 mb-4 shadow-sm" style="max-height: 220px; width: 100%; object-fit: cover; background-color: #f8f9fa;">
                    
                    <div class="text-start">
                        <h4 id="view-name" class="fw-bold mb-1">Toyota Fortuner</h4>
                        <div class="mb-3">
                            <span id="view-category" class="badge bg-secondary px-3 py-1 rounded-3">SUV</span>
                            <span id="view-status-badge" class="badge px-3 py-1 rounded-3">Tersedia</span>
                        </div>
                        
                        <div class="row g-2 bg-light p-3 rounded-4 mb-3 border">
                            <div class="col-6">
                                <small class="text-muted d-block">Tahun Rilis</small>
                                <span id="view-year" class="fw-semibold">2023</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">No. Plat</small>
                                <span id="view-plate-number" class="fw-semibold" style="letter-spacing: 1px;">-</span>
                            </div>
                            <div class="col-6 mt-2 border-top pt-2">
                                <small class="text-muted d-block">Tipe Mesin</small>
                                <span id="view-engine" class="fw-semibold">2.4L Diesel</span>
                            </div>
                            <div class="col-6 mt-2 border-top pt-2">
                                <small class="text-muted d-block">Transmisi</small>
                                <span id="view-transmission" class="fw-semibold text-capitalize">Automatic</span>
                            </div>
                            <div class="col-6 mt-2 border-top pt-2">
                                <small class="text-muted d-block">Kapasitas</small>
                                <span id="view-capacity" class="fw-semibold">7 Kursi • 3 Bagasi</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Harga Rental Per Hari</small>
                            <h4 class="fw-bold text-primary mb-0">Rp <span id="view-price">500.000</span> <small class="text-muted text-xs">/ hari</small></h4>
                        </div>

                        <div class="mb-2">
                            <small class="text-muted d-block mb-1">Deskripsi Mobil</small>
                            <p id="view-description" class="text-secondary small bg-light p-3 rounded-3 border">Tidak ada deskripsi.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary w-100 rounded-3" data-bs-dismiss="modal">Tutup Detail</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script Kustom Integrasi Modal -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // ==========================================
            // LOGIKA POPULATE MODAL DETAIL (VIEW)
            // ==========================================
            const viewButtons = document.querySelectorAll('.btn-view');
            const viewImage = document.getElementById('view-image');
            const viewName = document.getElementById('view-name');
            const viewCategory = document.getElementById('view-category');
            const viewStatusBadge = document.getElementById('view-status-badge');
            const viewYear = document.getElementById('view-year');
            const viewPlateNumber = document.getElementById('view-plate-number');
            const viewEngine = document.getElementById('view-engine');
            const viewTransmission = document.getElementById('view-transmission');
            const viewCapacity = document.getElementById('view-capacity');
            const viewPrice = document.getElementById('view-price');
            const viewDescription = document.getElementById('view-description');
            
            viewButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const brand = this.getAttribute('data-brand');
                    const model = this.getAttribute('data-model');
                    const year = this.getAttribute('data-year');
                    const plateNumber = this.getAttribute('data-plate-number');
                    const category = this.getAttribute('data-category');
                    const engineType = this.getAttribute('data-engine-type');
                    const transmission = this.getAttribute('data-transmission');
                    const passenger = this.getAttribute('data-passenger-capacity');
                    const luggage = this.getAttribute('data-luggage-capacity');
                    const price = this.getAttribute('data-price');
                    const status = this.getAttribute('data-status');
                    const image = this.getAttribute('data-image');
                    const description = this.getAttribute('data-description');
                    
                    // Isi modal view
                    viewName.textContent = brand + ' ' + model;
                    viewCategory.textContent = category;
                    viewYear.textContent = year;
                    viewPlateNumber.textContent = plateNumber || '-';
                    viewEngine.textContent = engineType;
                    viewTransmission.textContent = transmission;
                    viewCapacity.textContent = `${passenger} Kursi • ${luggage} Bagasi`;
                    viewPrice.textContent = price;
                    viewImage.src = image;
                    viewDescription.textContent = description || 'Tidak ada deskripsi.';
                    
                    // Format badge status
                    viewStatusBadge.className = 'badge px-3 py-1 rounded-3 ';
                    if (status === 'available') {
                        viewStatusBadge.classList.add('bg-success');
                        viewStatusBadge.textContent = 'Tersedia';
                    } else if (status === 'rented') {
                        viewStatusBadge.classList.add('bg-warning', 'text-dark');
                        viewStatusBadge.textContent = 'Disewa';
                    } else {
                        viewStatusBadge.classList.add('bg-danger');
                        viewStatusBadge.textContent = 'Servis (Maintenance)';
                    }
                    
                    // Tampilkan modal
                    const modal = new bootstrap.Modal(document.getElementById('modalViewCar'));
                    modal.show();
                });
            });
            
            // ==========================================
            // LOGIKA POPULATE MODAL UBAH (EDIT)
            // ==========================================
            const editButtons = document.querySelectorAll('.btn-edit');
            const editId = document.getElementById('edit-id');
            const editBrand = document.getElementById('edit-brand');
            const editModel = document.getElementById('edit-model');
            const editYear = document.getElementById('edit-year');
            const editTypeId = document.getElementById('edit-type-id');
            const editPlateNumber = document.getElementById('edit-plate-number');
            const editEngineType = document.getElementById('edit-engine-type');
            const editTransmission = document.getElementById('edit-transmission');
            const editPassenger = document.getElementById('edit-passenger-capacity');
            const editLuggage = document.getElementById('edit-luggage-capacity');
            const editPrice = document.getElementById('edit-price-per-day');
            const editStatus = document.getElementById('edit-status');
            const editDescription = document.getElementById('edit-description');
            
            editButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    const brand = this.getAttribute('data-brand');
                    const model = this.getAttribute('data-model');
                    const year = this.getAttribute('data-year');
                    const typeId = this.getAttribute('data-type-id');
                    const plateNumber = this.getAttribute('data-plate-number');
                    const engineType = this.getAttribute('data-engine-type');
                    const transmission = this.getAttribute('data-transmission');
                    const passenger = this.getAttribute('data-passenger-capacity');
                    const luggage = this.getAttribute('data-luggage-capacity');
                    const price = this.getAttribute('data-price-per-day');
                    const status = this.getAttribute('data-status');
                    const description = this.getAttribute('data-description');
                    
                    // Isi data ke form edit modal
                    editId.value = id;
                    editBrand.value = brand;
                    editModel.value = model;
                    editYear.value = year;
                    editTypeId.value = typeId || '';
                    editPlateNumber.value = plateNumber || '';
                    editEngineType.value = engineType;
                    editTransmission.value = transmission;
                    editPassenger.value = passenger;
                    editLuggage.value = luggage;
                    editPrice.value = Math.round(price); // hilangkan desimal jika ada
                    editStatus.value = status;
                    editDescription.value = description;
                    
                    // Tampilkan modal edit
                    const modal = new bootstrap.Modal(document.getElementById('modalEditCar'));
                    modal.show();
                });
            });
        });
    </script>
</body>
</html>