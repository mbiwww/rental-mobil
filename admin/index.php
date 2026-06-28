<?php
/**
 * Dashboard Admin — admin/index.php
 *
 * Menampilkan ringkasan statistik, grafik tren, dan transaksi terbaru.
 * Semua data diambil dari database via model classes.
 */
require_once __DIR__ . '/../includes/admin_auth_check.php';

// Include model classes
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/BaseModel.php';
require_once __DIR__ . '/../classes/Car.php';
require_once __DIR__ . '/../classes/Rental.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Payment.php';

// Inisialisasi model
$carModel    = new Car($pdo);
$rentalModel = new Rental($pdo);
$userModel   = new User($pdo);

// ============================================================
// AMBIL DATA STATISTIK DARI DATABASE
// ============================================================

// Statistik mobil
$totalMobil     = $carModel->countByStatus();
$mobilTersedia  = $carModel->countByStatus('available');
$mobilDisewa    = $carModel->countByStatus('rented');
$mobilPerawatan = $carModel->countByStatus('maintenance');

// Statistik rental
$totalRental    = $rentalModel->countByStatus();
$rentalPending  = $rentalModel->countByStatus('pending');
$rentalOngoing  = $rentalModel->countByStatus('ongoing');
$rentalSelesai  = $rentalModel->countByStatus('completed');

// Pendapatan
$pendapatanBulanIni = $rentalModel->getPendapatanBulanIni();
$totalPendapatan    = $rentalModel->getTotalPendapatan();

// Pertumbuhan
$pertumbuhan = $rentalModel->getPertumbuhanBulanan();

// Total customer
$totalCustomer = $userModel->countCustomers();

// Data chart
$timeframes = ['1_day', '1_week', '1_month', '3_months', '6_months'];
$chartDataByTimeframe = [];
foreach ($timeframes as $tf) {
    $chartDataByTimeframe[$tf] = $rentalModel->getChartDataForTimeframe($tf);
}

// Keep defaults for compatibility checks
$trenRental    = $chartDataByTimeframe['6_months'];
$trenPendapatan = $chartDataByTimeframe['6_months'];

// Transaksi terbaru
$recentTransaksi = $rentalModel->getRecentTransaksi(8);

// Helper format Rupiah
function formatRupiah(float $angka): string {
    if ($angka >= 1000000000) {
        return 'Rp ' . number_format($angka / 1000000000, 1, ',', '.') . 'M';
    } elseif ($angka >= 1000000) {
        return 'Rp ' . number_format($angka / 1000000, 1, ',', '.') . 'jt';
    } elseif ($angka >= 1000) {
        return 'Rp ' . number_format($angka / 1000, 0, ',', '.') . 'rb';
    }
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function formatRupiahFull(float $angka): string {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Helper badge status rental
function badgeStatus(string $status): string {
    $map = [
        'pending'          => '<span class="badge bg-warning text-dark">Menunggu</span>',
        'confirmed'        => '<span class="badge bg-info text-dark">Terkonfirmasi</span>',
        'ongoing'          => '<span class="badge bg-primary">Sedang Disewa</span>',
        'completed'        => '<span class="badge bg-success">Selesai</span>',
        'cancelled'        => '<span class="badge bg-secondary">Dibatalkan</span>',
        'cancel_requested' => '<span class="badge bg-danger">Minta Batal</span>',
    ];
    return $map[$status] ?? '<span class="badge bg-secondary">' . htmlspecialchars($status) . '</span>';
}

// Siapkan data chart untuk JavaScript
$chartDataJSON = [];
foreach ($chartDataByTimeframe as $tf => $rows) {
    $chartDataJSON[$tf] = [
        'labels' => array_map(fn($r) => $r['label'], $rows),
        'jumlah' => array_map(fn($r) => (int)$r['jumlah'], $rows),
        'total'  => array_map(fn($r) => (float)$r['total'], $rows),
    ];
}

// Set active page untuk sidebar
$activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentalKu Admin - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Inter', sans-serif; }
        .sidebar {
            width: 250px; height: 100vh; background: white;
            position: fixed; border-right: 1px solid #eee; padding: 20px;
            overflow-y: auto; z-index: 100;
        }
        .nav-link {
            color: #6c757d; padding: 12px 15px; border-radius: 8px;
            margin-bottom: 5px; display: flex; align-items: center;
            transition: all 0.2s;
        }
        .nav-link i { margin-right: 12px; font-size: 1.2rem; }
        .nav-link:hover, .nav-link.active {
            background: #eef4ff; color: #0d6efd; font-weight: 500;
        }
        .main-content { margin-left: 250px; }
        .top-header {
            background: white; padding: 15px 30px; border-bottom: 1px solid #eee;
            display: flex; justify-content: space-between; align-items: center;
        }
        .stat-card {
            border: none; border-radius: 15px; padding: 22px; background: white;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04); height: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }
        .icon-box {
            width: 44px; height: 44px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
        }
        .table-recent th { font-size: 12px; text-transform: uppercase; color: #6c757d; font-weight: 600; }
        .table-recent td { font-size: 13px; vertical-align: middle; }
        .empty-state { text-align: center; padding: 40px 20px; color: #adb5bd; }
        .empty-state i { font-size: 3rem; margin-bottom: 10px; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

    <div class="main-content">
        <!-- Header -->
        <header class="top-header">
            <div>
                <h5 class="fw-bold mb-0">Dashboard</h5>
                <small class="text-muted"><?= date('l, d F Y') ?></small>
            </div>
            <div class="d-flex align-items-center">
                <div class="text-end me-3">
                    <p class="mb-0 fw-bold small"><?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></p>
                    <p class="text-muted mb-0" style="font-size:11px;">Administrator</p>
                </div>
                <a href="../handlers/auth_handler.php?action=logout" class="text-muted text-decoration-none">
                    <i class="bi bi-box-arrow-right fs-4"></i>
                </a>
            </div>
        </header>

        <div class="p-4">
            <!-- Stats Grid -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small fw-bold mb-1">Total Mobil</p>
                                <h2 class="fw-bold mb-1"><?= $totalMobil ?></h2>
                                <p class="text-muted mb-0 small"><?= $mobilTersedia ?> tersedia · <?= $mobilDisewa ?> disewa</p>
                            </div>
                            <div class="icon-box bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-car-front"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small fw-bold mb-1">Orderan Aktif</p>
                                <h2 class="fw-bold mb-1"><?= $rentalPending + $rentalOngoing ?></h2>
                                <p class="text-muted mb-0 small"><?= $rentalPending ?> pending · <?= $rentalOngoing ?> ongoing</p>
                            </div>
                            <div class="icon-box bg-success bg-opacity-10 text-success">
                                <i class="bi bi-receipt"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small fw-bold mb-1">Pendapatan Bulan Ini</p>
                                <h2 class="fw-bold mb-1"><?= formatRupiah($pendapatanBulanIni) ?></h2>
                                <p class="text-muted mb-0 small"><?= $rentalSelesai ?> transaksi selesai</p>
                            </div>
                            <div class="icon-box bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-wallet2"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted small fw-bold mb-1">Total Customer</p>
                                <h2 class="fw-bold mb-1"><?= $totalCustomer ?></h2>
                                <p class="text-muted mb-0 small">
                                    <?php if ($pertumbuhan > 0): ?>
                                        <span class="text-success"><i class="bi bi-arrow-up"></i> +<?= $pertumbuhan ?>%</span> dari bulan lalu
                                    <?php elseif ($pertumbuhan < 0): ?>
                                        <span class="text-danger"><i class="bi bi-arrow-down"></i> <?= $pertumbuhan ?>%</span> dari bulan lalu
                                    <?php else: ?>
                                        Stabil dari bulan lalu
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="icon-box bg-info bg-opacity-10 text-info">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 id="rentTrendTitle" class="fw-bold mb-0">Tren Penyewaan (6 Bulan Terakhir)</h6>
                            <?php if (!empty($trenRental)): ?>
                            <select id="rentTrendTimeframe" class="form-select form-select-sm border-0 bg-light fw-semibold" style="width: auto; cursor: pointer;">
                                <option value="1_day">1 Hari</option>
                                <option value="1_week">1 Minggu</option>
                                <option value="1_month">1 Bulan</option>
                                <option value="3_months">3 Bulan</option>
                                <option value="6_months" selected>6 Bulan</option>
                            </select>
                            <?php endif; ?>
                        </div>
                        <?php if (empty($trenRental)): ?>
                            <div class="empty-state"><i class="bi bi-bar-chart"></i><p>Belum ada data rental</p></div>
                        <?php else: ?>
                            <canvas id="rentTrendChart" height="200"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 id="incomeTitle" class="fw-bold mb-0">Pendapatan Bulanan (6 Bulan Terakhir)</h6>
                            <?php if (!empty($trenPendapatan)): ?>
                            <select id="incomeTimeframe" class="form-select form-select-sm border-0 bg-light fw-semibold" style="width: auto; cursor: pointer;">
                                <option value="1_day">1 Hari</option>
                                <option value="1_week">1 Minggu</option>
                                <option value="1_month">1 Bulan</option>
                                <option value="3_months">3 Bulan</option>
                                <option value="6_months" selected>6 Bulan</option>
                            </select>
                            <?php endif; ?>
                        </div>
                        <?php if (empty($trenPendapatan)): ?>
                            <div class="empty-state"><i class="bi bi-graph-up"></i><p>Belum ada data pendapatan</p></div>
                        <?php else: ?>
                            <canvas id="incomeChart" height="200"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Transaksi Terbaru</h6>
                    <a href="transaksi.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <?php if (empty($recentTransaksi)): ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>Belum ada transaksi</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-recent mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Mobil</th>
                                    <th>Tanggal Sewa</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTransaksi as $trx): ?>
                                <tr>
                                    <td class="fw-bold">#<?= $trx['id'] ?></td>
                                    <td><?= htmlspecialchars($trx['user_name']) ?></td>
                                    <td><?= htmlspecialchars($trx['brand'] . ' ' . $trx['model']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($trx['start_date'])) ?> - <?= date('d/m/Y', strtotime($trx['end_date'])) ?></td>
                                    <td class="fw-bold"><?= formatRupiahFull((float)$trx['total_price']) ?></td>
                                    <td><?= badgeStatus($trx['status']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const chartDataByTimeframe = <?= json_encode($chartDataJSON) ?>;

    function formatRupiahJs(value) {
        if (value >= 1000000000) {
            return 'Rp ' + (value / 1000000000).toFixed(1).replace('.', ',') + 'M';
        } else if (value >= 1000000) {
            return 'Rp ' + (value / 1000000).toFixed(1).replace('.', ',') + 'jt';
        } else if (value >= 1000) {
            return 'Rp ' + (value / 1000).toFixed(0) + 'rb';
        }
        return 'Rp ' + value;
    }

    const timeframeTitles = {
        '1_day': '24 Jam Terakhir',
        '1_week': '7 Hari Terakhir',
        '1_month': '30 Hari Terakhir',
        '3_months': '3 Bulan Terakhir',
        '6_months': '6 Bulan Terakhir'
    };

    <?php if (!empty($trenRental)): ?>
    const rentTrendChartInstance = new Chart(document.getElementById('rentTrendChart'), {
        type: 'bar',
        data: {
            labels: chartDataByTimeframe['6_months'].labels,
            datasets: [{
                label: 'Total Sewa',
                data: chartDataByTimeframe['6_months'].jumlah,
                backgroundColor: '#4e89ff',
                borderRadius: 6
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f0f0f0' } },
                x: { grid: { display: false } }
            }
        }
    });

    document.getElementById('rentTrendTimeframe').addEventListener('change', function(e) {
        const tf = e.target.value;
        const data = chartDataByTimeframe[tf];
        
        // Update Chart
        rentTrendChartInstance.data.labels = data.labels;
        rentTrendChartInstance.data.datasets[0].data = data.jumlah;
        rentTrendChartInstance.update();
        
        // Update Title
        document.getElementById('rentTrendTitle').innerText = 'Tren Penyewaan (' + timeframeTitles[tf] + ')';
    });
    <?php endif; ?>

    <?php if (!empty($trenPendapatan)): ?>
    const incomeChartInstance = new Chart(document.getElementById('incomeChart'), {
        type: 'line',
        data: {
            labels: chartDataByTimeframe['6_months'].labels,
            datasets: [{
                label: 'Pendapatan',
                data: chartDataByTimeframe['6_months'].total,
                borderColor: '#4bc0c0',
                backgroundColor: 'rgba(75,192,192,0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#4bc0c0',
                pointRadius: 5
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: '#f0f0f0' },
                    ticks: { callback: v => formatRupiahJs(v) } 
                },
                x: { grid: { display: false } }
            }
        }
    });

    document.getElementById('incomeTimeframe').addEventListener('change', function(e) {
        const tf = e.target.value;
        const data = chartDataByTimeframe[tf];
        
        // Update Chart
        incomeChartInstance.data.labels = data.labels;
        incomeChartInstance.data.datasets[0].data = data.total;
        incomeChartInstance.update();
        
        // Update Title
        document.getElementById('incomeTitle').innerText = 'Pendapatan Bulanan (' + timeframeTitles[tf] + ')';
    });
    <?php endif; ?>
    </script>
</body>
</html>