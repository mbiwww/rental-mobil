<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kategori - RentalKu</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
        
        /* Sidebar */
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
            transition: 0.3s;
        }
        .nav-link i { margin-right: 12px; font-size: 1.2rem; }
        .nav-link.active {
            background: #eef4ff;
            color: #0d6efd;
            font-weight: 600;
        }
        .nav-link:hover:not(.active) { background: #f1f1f1; }

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

        .btn-dark-custom {
            background-color: #0b0e11;
            color: white;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            border: none;
        }

        .btn-dark-custom:hover { background-color: #23272b; color: white; }

        .table-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            border: 1px solid #efefef;
        }

        .table thead th {
            border-bottom: 1px solid #eee;
            color: #212529;
            font-weight: 600;
            text-transform: none;
            padding-bottom: 20px;
        }

        .table tbody td {
            padding: 20px 0;
            border-bottom: 1px solid #f8f9fa;
            vertical-align: middle;
        }
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
            <a class="nav-link active" href="#"><i class="bi bi-list-ul"></i> Kategori</a>
            <a class="nav-link" href="#"><i class="bi bi-car-front"></i> Armada Mobil</a>
            <a class="nav-link" href="#"><i class="bi bi-cash-stack"></i> Transaksi</a>
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
                <i class="bi bi-box-arrow-right fs-4 text-muted ms-2"></i>
            </div>
        </header>

        <div class="p-5">
            <!-- Title Section -->
            <div class="d-flex justify-content-between align-items-start mb-5">
                <div>
                    <h1 class="fw-bold text-dark mb-1">Manajemen Kategori</h1>
                    <p class="text-muted">Kelola kategori kendaraan</p>
                </div>
                <button class="btn btn-dark-custom d-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i> Tambah Kategori
                </button>
            </div>

            <!-- Table Section -->
            <div class="table-container shadow-sm">
                <h5 class="fw-bold mb-4">Daftar Kategori</h5>
                <table class="table">
                    <thead>
                        <tr>
                            <th width="50">ID</th>
                            <th width="150">Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th width="100" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td class="fw-semibold">SUV</td>
                            <td class="text-muted">Sport Utility Vehicle - Mobil keluarga berkapasitas besar</td>
                            <td class="text-end">
                                <button class="btn btn-link text-dark p-1"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-link text-danger p-1"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td class="fw-semibold">Sedan</td>
                            <td class="text-muted">Mobil mewah dan nyaman untuk perjalanan bisnis</td>
                            <td class="text-end">
                                <button class="btn btn-link text-dark p-1"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-link text-danger p-1"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td class="fw-semibold">MPV</td>
                            <td class="text-muted">Multi Purpose Vehicle - Mobil untuk rombongan</td>
                            <td class="text-end">
                                <button class="btn btn-link text-dark p-1"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-link text-danger p-1"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td class="fw-semibold">Hatchback</td>
                            <td class="text-muted">Mobil kompak untuk dalam kota</td>
                            <td class="text-end">
                                <button class="btn btn-link text-dark p-1"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-link text-danger p-1"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>