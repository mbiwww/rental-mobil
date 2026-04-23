<?php
session_start();
include '../config/database.php';
include '../config/functions.php';

// Proteksi halaman admin
// cekLoginAdmin(); 

// Hitung Statistik Asli dari Database
$query_mobil  = mysqli_query($conn, "SELECT COUNT(*) as total FROM mobil");
$data_mobil   = mysqli_fetch_assoc($query_mobil);

$query_sewa   = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE status_pembayaran='Terverifikasi'");
$data_sewa    = mysqli_fetch_assoc($query_sewa);

$query_income = mysqli_query($conn, "SELECT SUM(total_biaya) as total FROM transaksi WHERE status_pembayaran='Selesai'");
$data_income  = mysqli_fetch_assoc($query_income);
?>

<h3 class="fw-bold mb-0"><?= $data_mobil['total']; ?></h3>  
<h3 class="fw-bold mb-0"><?= formatRupiah($data_income['total'] ?? 0); ?></h3>

<div class="d-flex">
    <?php include '../includes/sidebar_admin.php'; ?>

    <div class="content-main p-4 w-100 bg-light">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">Dashboard</h4>
                <p class="text-muted small">Selamat datang di panel admin RentalKu</p>
            </div>
            <div class="user-admin d-flex align-items-center">
                <div class="text-end me-3">
                    <p class="mb-0 fw-bold">Admin User</p>
                    <small class="text-muted">Administrator</small>
                </div>
                <img src="../assets/img/admin-avatar.png" class="rounded-circle" width="40" alt="Admin">
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3">
                    <small class="text-muted fw-bold">Total Mobil</small>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <h3 class="fw-bold mb-0">6</h3>
                        <div class="text-primary bg-primary bg-opacity-10 p-2 rounded">
                            <i class="bi bi-car-front"></i>
                        </div>
                    </div>
                    <small class="text-muted mt-2">5 tersedia</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3">
                    <small class="text-muted fw-bold">Orderan Aktif</small>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <h3 class="fw-bold mb-0">0</h3>
                        <div class="text-success bg-success bg-opacity-10 p-2 rounded">
                            <i class="bi bi- megaphone"></i>
                        </div>
                    </div>
                    <small class="text-muted mt-2">Sedang berjalan</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3">
                    <small class="text-muted fw-bold">Pendapatan Bulan Ini</small>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <h3 class="fw-bold mb-0">Rp 1.5jt</h3>
                        <div class="text-warning bg-warning bg-opacity-10 p-2 rounded">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                    </div>
                    <small class="text-muted mt-2">2 transaksi</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3">
                    <small class="text-muted fw-bold">Pertumbuhan</small>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <h3 class="fw-bold mb-0">+23%</h3>
                        <div class="text-info bg-info bg-opacity-10 p-2 rounded">
                            <i class="bi bi-graph-up"></i>
                        </div>
                    </div>
                    <small class="text-muted mt-2">Dari bulan lalu</small>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm p-4 h-100">
                    <h6 class="fw-bold mb-4">Tren Penyewaan</h6>
                    <canvas id="rentTrendChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm p-4 h-100">
                    <h6 class="fw-bold mb-4">Pendapatan Bulanan</h6>
                    <canvas id="incomeChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm p-4">
            <h6 class="fw-bold mb-4">Transaksi Terbaru</h6>
            <div class="transaction-list">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                    <div>
                        <p class="mb-0 fw-bold">John Doe</p>
                        <small class="text-muted">1 mobil • 15/4/2026</small>
                    </div>
                    <div class="text-end">
                        <p class="mb-0 fw-bold text-primary">Rp 500.000</p>
                        <span class="badge bg-warning text-dark small">Pending</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('rentTrendChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr'],
        datasets: [{
            label: 'Total Sewa',
            data: [12, 19, 15, 8],
            backgroundColor: '#0d6efd',
            borderRadius: 5
        }]
    }
});

// Income Chart (Line Chart)
const ctx2 = document.getElementById('incomeChart').getContext('2d');
new Chart(ctx2, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr'],
        datasets: [{
            label: 'Pendapatan',
            data: [2500000, 4000000, 3000000, 500000],
            borderColor: '#198754',
            tension: 0.4
        }]
    }
});
</script>

<?php include '../includes/footer_admin.php'; ?>