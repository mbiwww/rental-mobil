<?php
/**
 * Komponen Sidebar Admin
 * 
 * Digunakan secara konsisten di semua halaman admin.
 * Menandai menu aktif berdasarkan variabel $activePage.
 */
$currentPage = isset($activePage) ? $activePage : 'armada';
?>
<div class="sidebar">
    <div class="d-flex align-items-center mb-5 ps-2">
        <i class="bi bi-car-front-fill text-primary fs-3 me-2"></i>
        <h5 class="fw-bold mb-0">RentalKu Admin</h5>
    </div>
    <nav class="nav flex-column">
        <a class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>" href="index.php">
            <i class="bi bi-grid me-2"></i> Dashboard
        </a>
        <a class="nav-link <?= $currentPage === 'kategori' ? 'active' : '' ?>" href="kategori.php">
            <i class="bi bi-list-ul me-2"></i> Kategori
        </a>
        <a class="nav-link <?= $currentPage === 'armada' ? 'active' : '' ?>" href="armada.php">
            <i class="bi bi-car-front me-2"></i> Armada Mobil
        </a>
        <a class="nav-link <?= $currentPage === 'transaksi' ? 'active' : '' ?>" href="transaksi.php">
            <i class="bi bi-cash-stack me-2"></i> Transaksi
        </a>
        <a class="nav-link <?= $currentPage === 'customer' ? 'active' : '' ?>" href="customer.php">
            <i class="bi bi-people me-2"></i> Data Customer
        </a>
        <a class="nav-link <?= $currentPage === 'pengembalian' ? 'active' : '' ?>" href="pengembalian.php">
            <i class="bi bi-arrow-left-right me-2"></i> Pengembalian
        </a>
        <a class="nav-link <?= $currentPage === 'laporan' ? 'active' : '' ?>" href="laporan.php">
            <i class="bi bi-file-earmark-text me-2"></i> Laporan
        </a>
    </nav>
</div>
