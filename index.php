<?php
// index.php - Landing Page Utama RentalKu
include 'config/database.php'; // Nanti lo buat file koneksinya
include 'includes/header.php'; // Navbar ditaruh di sini biar rapi
?>

<section class="hero-section bg-primary text-white py-5" style="border-radius: 0 0 50px 50px;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold">Sewa Mobil Impian Anda dengan Mudah</h1>
                <p class="lead">Ratusan pilihan mobil berkualitas dengan harga terjangkau. Proses cepat, aman, dan terpercaya.</p>
                
                <div class="card p-3 shadow-lg border-0 text-dark mt-4">
                    <form action="pages/katalog.php" method="GET" class="row g-2">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Jenis Mobil</label>
                            <select name="kategori" class="form-select">
                                <option value="">Pilih kategori</option>
                                <option value="SUV">SUV</option>
                                <option value="Sedan">Sedan</option>
                                <option value="MPV">MPV</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-dark w-100 py-2">
                                <i class="bi bi-search"></i> Cari Mobil
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <img src="assets/img/hero-cars.png" class="img-fluid rounded-3" alt="RentalKu Hero">
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container text-center">
        <h2 class="fw-bold">Mobil Unggulan</h2>
        <p class="text-muted">Pilihan mobil terbaik untuk perjalanan Anda</p>
        
        <div class="row mt-5">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <img src="assets/img/mobil/fortuner.jpg" class="card-img-top rounded-top-4" alt="Fortuner">
                    <div class="card-body text-start">
                        <span class="badge bg-light text-primary mb-2">SUV</span>
                        <h5 class="fw-bold">Toyota Fortuner 2023</h5>
                        <p class="text-muted small">Automatic • 7 Kursi</p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <h5 class="text-primary fw-bold mb-0">Rp 500.000<span class="text-muted fs-6 fw-normal">/hari</span></h5>
                            <a href="pages/detail.php?id=1" class="btn btn-dark btn-sm px-3">Sewa</a>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        
        <a href="pages/katalog.php" class="btn btn-outline-dark px-4 mt-4">Lihat Semua Mobil</a>
    </div>
</section>

<section class="bg-light py-5">
    <div class="container text-center py-4">
        <h2 class="fw-bold">Cara Kerja Rental</h2>
        <p class="text-muted">Proses mudah dalam 3 langkah</p>
        
        <div class="row mt-5">
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-4 shadow-sm h-100">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                        <i class="bi bi-search text-primary fs-3"></i>
                    </div>
                    <h5>1. Pilih Mobil</h5>
                    <p class="small text-muted">Cari dan pilih mobil sesuai kebutuhan Anda dari katalog kami</p>
                </div>
            </div>
            </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>