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
    <div class="d-flex align-items-center justify-content-between mb-5 ps-2">
        <div class="d-flex align-items-center">
            <i class="bi bi-car-front-fill text-primary fs-3 me-2"></i>
            <h5 class="fw-bold mb-0">RentalQu Admin</h5>
        </div>
        <button class="btn d-lg-none p-0 border-0" id="sidebarClose" type="button" style="color: #6c757d;">
            <i class="bi bi-x fs-2"></i>
        </button>
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

<!-- Backdrop untuk mobile sidebar -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<style>
/* CSS Tambahan untuk Responsivitas Sidebar Admin */
@media (max-width: 991.98px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease-in-out;
        z-index: 1050 !important;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    .sidebar.show {
        transform: translateX(0);
    }
    .main-content {
        margin-left: 0 !important;
    }
    .top-header {
        padding: 15px 20px !important;
    }
}

.sidebar-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0,0,0,0.4);
    z-index: 1040;
    display: none;
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
}
.sidebar-backdrop.show {
    display: block !important;
    opacity: 1;
}

/* Responsive padding untuk content area */
@media (max-width: 767.98px) {
    .main-content > .p-5, .main-content > .p-4 {
        padding: 1.25rem !important;
    }
    .table-container {
        padding: 15px !important;
        border-radius: 12px !important;
    }
    h1 {
        font-size: 1.75rem !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const closeBtn = document.getElementById('sidebarClose');

    // Create and prepend hamburger toggle button
    const topHeader = document.querySelector('.top-header');
    if (topHeader) {
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'btn d-lg-none me-3 p-0 border-0';
        toggleBtn.id = 'sidebarToggle';
        toggleBtn.innerHTML = '<i class="bi bi-list fs-2"></i>';
        toggleBtn.style.color = '#6c757d';

        const firstChild = topHeader.firstElementChild;
        if (firstChild) {
            firstChild.classList.add('d-flex', 'align-items-center', 'gap-2');
            firstChild.prepend(toggleBtn);
        }

        toggleBtn.addEventListener('click', toggleSidebar);
    }

    function toggleSidebar() {
        if (sidebar) sidebar.classList.toggle('show');
        if (backdrop) backdrop.classList.toggle('show');
    }

    if (closeBtn) closeBtn.addEventListener('click', toggleSidebar);
    if (backdrop) backdrop.addEventListener('click', toggleSidebar);
});
</script>

