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
    <style>
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; color: #333; }
        
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

        /* Elements */
        .btn-dark-custom {
            background-color: #0b0e11;
            color: white;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            border: none;
        }
        .search-input, .status-select {
            background-color: #f1f3f5;
            border: none;
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
        }
        .badge-available {
            background-color: #d1e7dd;
            color: #0f5132;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
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
            <a class="nav-link" href="#"><i class="bi bi-list-ul"></i> Kategori</a>
            <a class="nav-link active" href="#"><i class="bi bi-car-front"></i> Armada Mobil</a>
            <a class="nav-link" href="#"><i class="bi bi-cash-stack"></i> Transaksi</a>
            <a class="nav-link" href="#"><i class="bi bi-people"></i> Data Customer</a>
            <a class="nav-link" href="#"><i class="bi bi-arrow-left-right"></i> Pengembalian</a>
            <a class="nav-link" href="#"><i class="bi bi-file-earmark-text"></i> Laporan</a>
        </nav>
    </div>

    <!-- Content -->
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
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h1 class="fw-bold mb-1">Manajemen Armada</h1>
                    <p class="text-muted">Kelola data mobil rental</p>
                </div>
                <button class="btn btn-dark-custom d-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i> Tambah Mobil
                </button>
            </div>

            <!-- Filter Section -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <input type="text" class="form-control search-input" placeholder="Cari mobil...">
                </div>
                <div class="col-md-4">
                    <select class="form-select status-select">
                        <option>Semua Status</option>
                        <option>Available</option>
                        <option>Rented</option>
                    </select>
                </div>
            </div>

            <!-- Table Section -->
            <div class="table-container shadow-sm">
                <h5 class="fw-bold mb-4">Daftar Mobil (6)</h5>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="text-muted small">
                            <tr>
                                <th>Mobil</th>
                                <th>Kategori</th>
                                <th>Harga/Hari</th>
                                <th>Status</th>
                                <th>Spesifikasi</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Item 1 -->
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="https://placehold.co/100x70/222/fff?text=Fortuner" class="img-mobil">
                                        <div>
                                            <p class="mb-0 fw-bold">Toyota Fortuner 2023</p>
                                            <small class="text-muted">2023</small>
                                        </div>
                                    </div>
                                </td>
                                <td>SUV</td>
                                <td class="fw-bold">Rp 500.000</td>
                                <td>
                                    <div class="badge-available">Available <i class="bi bi-chevron-down small"></i></div>
                                </td>
                                <td>
                                    <small class="text-muted d-block">Automatic</small>
                                    <small class="text-muted">7 kursi • 3 bagasi</small>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-link text-dark p-1"><i class="bi bi-eye"></i></button>
                                    <button class="btn btn-link text-dark p-1"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-link text-danger p-1"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            <!-- Item 2 -->
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="https://placehold.co/100x70/333/fff?text=Civic" class="img-mobil">
                                        <div>
                                            <p class="mb-0 fw-bold">Honda Civic 2024</p>
                                            <small class="text-muted">2024</small>
                                        </div>
                                    </div>
                                </td>
                                <td>Sedan</td>
                                <td class="fw-bold">Rp 400.000</td>
                                <td>
                                    <div class="badge-available">Available <i class="bi bi-chevron-down small"></i></div>
                                </td>
                                <td>
                                    <small class="text-muted d-block">Automatic</small>
                                    <small class="text-muted">5 kursi • 2 bagasi</small>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-link text-dark p-1"><i class="bi bi-eye"></i></button>
                                    <button class="btn btn-link text-dark p-1"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-link text-danger p-1"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            <!-- Item 3 -->
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="https://placehold.co/100x70/111/fff?text=Avanza" class="img-mobil">
                                        <div>
                                            <p class="mb-0 fw-bold">Toyota Avanza 2023</p>
                                            <small class="text-muted">2023</small>
                                        </div>
                                    </div>
                                </td>
                                <td>MPV</td>
                                <td class="fw-bold">Rp 300.000</td>
                                <td>
                                    <div class="badge-available">Available <i class="bi bi-chevron-down small"></i></div>
                                </td>
                                <td>
                                    <small class="text-muted d-block">Manual</small>
                                    <small class="text-muted">7 kursi • 2 bagasi</small>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-link text-dark p-1"><i class="bi bi-eye"></i></button>
                                    <button class="btn btn-link text-dark p-1"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-link text-danger p-1"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>