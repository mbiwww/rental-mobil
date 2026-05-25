<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Transaksi - RentalKu</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
        }
        .nav-link {
            color: #6c757d;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        .nav-link.active {
            background: #eef4ff;
            color: #0d6efd;
            font-weight: 600;
        }

        /* Main Content */
        .main-content { margin-left: 260px; }
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
        }
        .tab-item {
            padding: 8px 16px;
            border-radius: 10px;
            text-decoration: none;
            color: #495057;
            font-size: 14px;
            font-weight: 500;
        }
        .tab-item.active {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        /* Table Container */
        .table-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            border: 1px solid #efefef;
            margin-top: 30px;
        }
        .table thead th {
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            color: #212529;
            font-weight: 600;
        }
        .table tbody td {
            padding: 20px 0;
            border-bottom: 1px solid #f8f9fa;
            vertical-align: middle;
        }

        /* Status Badge */
        .badge-pending {
            background-color: #ffc107;
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
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
        }
        .btn-view { color: #212529; }
        .btn-check { color: #198754; }
        .btn-cancel { color: #dc3545; }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="d-flex align-items-center mb-5 ps-2">
            <i class="bi bi-car-front-fill text-primary fs-3 me-2"></i>
            <h5 class="fw-bold mb-0">RentalKu Admin</h5>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link" href="#"><i class="bi bi-grid"></i> Dashboard</a>
            <a class="nav-link" href="#"><i class="bi bi-list-ul"></i> Kategori</a>
            <a class="nav-link" href="#"><i class="bi bi-car-front"></i> Armada Mobil</a>
            <a class="nav-link active" href="#"><i class="bi bi-cash-stack"></i> Transaksi</a>
            <a class="nav-link" href="#"><i class="bi bi-people"></i> Data Customer</a>
            <a class="nav-link" href="#"><i class="bi bi-arrow-left-right"></i> Pengembalian</a>
            <a class="nav-link" href="#"><i class="bi bi-file-earmark-text"></i> Laporan</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header class="top-header">
            <div></div>
            <div class="d-flex align-items-center">
                <div class="text-end me-3">
                    <p class="mb-0 fw-bold">Admin User</p>
                    <small class="text-muted">Administrator</small>
                </div>
                <i class="bi bi-box-arrow-right fs-4 text-muted"></i>
            </div>
        </header>

        <div class="p-5">
            <!-- Title Section -->
            <div class="mb-4">
                <h1 class="fw-bold mb-1">Manajemen Transaksi</h1>
                <p class="text-muted">Kelola pesanan dan pembayaran</p>
            </div>

            <!-- Tabs Filter -->
            <div class="status-tabs mb-4">
                <a href="#" class="tab-item active">Semua (1)</a>
                <a href="#" class="tab-item">Pending (1)</a>
                <a href="#" class="tab-item">Confirmed (0)</a>
                <a href="#" class="tab-item">Ongoing (0)</a>
                <a href="#" class="tab-item">Completed (0)</a>
            </div>

            <!-- Table Section -->
            <div class="table-container shadow-sm">
                <h5 class="fw-bold mb-4">Semua Transaksi</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead class="small text-muted">
                            <tr>
                                <th>ID Pesanan</th>
                                <th>Customer</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold">R001</td>
                                <td>
                                    <div class="fw-bold">John Doe</div>
                                    <div class="text-muted small">john@example.com</div>
                                </td>
                                <td>08 Apr 2026</td>
                                <td>
                                    <div class="fw-bold">Rp 1.000.000</div>
                                    <div class="text-muted small">1 mobil</div>
                                </td>
                                <td>
                                    <span class="badge-pending">Pending</span>
                                </td>
                                <td class="text-end">
                                    <a href="#" class="btn-action btn-view" title="Detail"><i class="bi bi-eye"></i></a>
                                    <a href="#" class="btn-action btn-check" title="Konfirmasi"><i class="bi bi-check-circle"></i></a>
                                    <a href="#" class="btn-action btn-cancel" title="Batalkan"><i class="bi bi-x-circle"></i></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>