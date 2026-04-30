<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentalKu Admin - Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
        
        /* Sidebar Styling */
        .sidebar {
            width: 250px;
            height: 100vh;
            background: white;
            position: fixed;
            border-right: 1px solid #eee;
            padding: 20px;
        }
        .nav-link {
            color: #6c757d;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }
        .nav-link i { margin-right: 12px; font-size: 1.2rem; }
        .nav-link:hover, .nav-link.active {
            background: #eef4ff;
            color: #0d6efd;
            font-weight: 500;
        }

        /* Main Content */
        .main-content { margin-left: 250px; }
        
        /* Header */
        .top-header {
            background: white;
            padding: 15px 30px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Dashboard Cards */
        .stat-card {
            border: none;
            border-radius: 15px;
            padding: 20px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            height: 100%;
        }
        .icon-box {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="d-flex align-items-center mb-4 ps-2">
            <i class="bi bi-car-front-fill text-primary fs-3 me-2"></i>
            <h5 class="fw-bold mb-0">RentalKu Admin</h5>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link active" href="#"><i class="bi bi-grid"></i> Dashboard</a>
            <a class="nav-link" href="#"><i class="bi bi-list-ul"></i> Kategori</a>
            <a class="nav-link" href="#"><i class="bi bi-car-front"></i> Armada Mobil</a>
            <a class="nav-link" href="#"><i class="bi bi-cash-stack"></i> Transaksi</a>
            <a class="nav-link" href="#"><i class="bi bi-people"></i> Data Customer</a>
            <a class="nav-link" href="#"><i class="bi bi-arrow-left-right"></i> Pengembalian</a>
            <a class="nav-link" href="#"><i class="bi bi-file-earmark-text"></i> Laporan</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <header class="top-header">
            <div></div> <!-- Spacer -->
            <div class="d-flex align-items-center">
                <div class="text-end me-3">
                    <p class="mb-0 fw-bold small">Admin User</p>
                    <p class="text-muted mb-0" style="font-size: 11px;">Administrator</p>
                </div>
                <i class="bi bi-box-arrow-right fs-4 text-muted cursor-pointer"></i>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="p-4">
            <div class="mb-4">
                <h2 class="fw-bold">Dashboard</h2>
                <p class="text-muted">Selamat datang di panel admin RentalKu</p>
            </div>

            <!-- Stats Grid -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between">
                            <p class="text-muted small fw-bold">Total Mobil</p>
                            <i class="bi bi-car-front text-primary"></i>
                        </div>
                        <h2 class="fw-bold">6</h2>
                        <p class="text-muted mb-0 small">5 tersedia</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between">
                            <p class="text-muted small fw-bold">Orderan Aktif</p>
                            <i class="bi bi-receipt text-success"></i>
                        </div>
                        <h2 class="fw-bold">0</h2>
                        <p class="text-muted mb-0 small">Sedang berjalan</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between">
                            <p class="text-muted small fw-bold">Pendapatan Bulan Ini</p>
                            <i class="bi bi-currency-dollar text-warning"></i>
                        </div>
                        <h2 class="fw-bold">Rp 1.0jt</h2>
                        <p class="text-muted mb-0 small">1 transaksi</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between">
                            <p class="text-muted small fw-bold">Pertumbuhan</p>
                            <i class="bi bi-graph-up-arrow text-info"></i>
                        </div>
                        <h2 class="fw-bold text-dark">+23%</h2>
                        <p class="text-muted mb-0 small">Dari bulan lalu</p>
                    </div>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="stat-card">
                        <h6 class="fw-bold mb-4">Tren Penyewaan</h6>
                        <canvas id="rentTrendChart" height="200"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card">
                        <h6 class="fw-bold mb-4">Pendapatan Bulanan</h6>
                        <canvas id="incomeChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Transaction -->
            <div class="stat-card">
                <h6 class="fw-bold mb-4">Transaksi Terbaru</h6>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-0 fw-bold text-dark">John Doe</p>
                        <p class="text-muted small mb-0">1 mobil • 8/4/2026</p>
                    </div>
                    <div class="text-end">
                        <p class="mb-0 fw-bold text-primary">Rp 1.000.000</p>
                        <p class="text-muted small mb-0">Pending</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Rent Trend Chart (Bar)
        new Chart(document.getElementById('rentTrendChart'), {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr'],
                datasets: [{
                    label: 'Total Sewa',
                    data: [12, 19, 15, 8],
                    backgroundColor: '#4e89ff',
                    borderRadius: 5
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, border: { dash: [5, 5] } } }
            }
        });

        // Income Chart (Line)
        new Chart(document.getElementById('incomeChart'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr'],
                datasets: [{
                    label: 'Pendapatan',
                    data: [2500000, 4000000, 3000000, 500000],
                    borderColor: '#4bc0c0',
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#4bc0c0',
                    pointRadius: 5
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, border: { dash: [5, 5] } } }
            }
        });
    </script>
</body>
</html>