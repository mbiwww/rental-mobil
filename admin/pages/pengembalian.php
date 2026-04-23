<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengembalian - RentalKu Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #2b67f6;
            --bg-body: #f8faff;
            --text-gray: #7f8c8d;
            --sidebar-width: 240px;
        }

        body { font-family: 'Segoe UI', sans-serif; margin: 0; background-color: var(--bg-body); display: flex; }

        /* Sidebar Styling */
        .sidebar { width: var(--sidebar-width); height: 100vh; background: #fff; padding: 20px; position: fixed; border-right: 1px solid #edf2f7; }
        .logo-admin { font-weight: bold; font-size: 20px; margin-bottom: 40px; display: flex; align-items: center; gap: 10px; }
        .nav-menu { display: flex; flex-direction: column; gap: 10px; }
        .nav-item { text-decoration: none; color: #555; padding: 12px 15px; border-radius: 10px; display: flex; align-items: center; gap: 12px; transition: 0.3s; }
        .nav-item:hover, .nav-item.active { background: #eef4ff; color: var(--primary-blue); font-weight: 600; }
        .nav-item.active { border: 1px solid var(--primary-blue); background: #fff; }

        /* Main Content Styling */
        .main-content { margin-left: calc(var(--sidebar-width) + 40px); flex: 1; padding: 20px 40px 40px 0; }
        
        /* Header Dashboard */
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .admin-profile { display: flex; align-items: center; gap: 15px; }

        /* Card Styling */
        .section-card { background: #fff; border-radius: 16px; padding: 30px; border: 1px solid #f0f0f0; margin-bottom: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .card-title { font-weight: bold; font-size: 18px; margin-bottom: 20px; display: block; }

        /* Search Section */
        .search-container { position: relative; display: flex; gap: 10px; }
        .search-container i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #aaa; }
        .search-input { width: 100%; padding: 12px 12px 12px 45px; border: none; background-color: #f1f3f5; border-radius: 10px; font-size: 15px; outline: none; }
        .btn-cari { background: #0b0c10; color: white; padding: 0 25px; border: none; border-radius: 10px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 10px; }

        /* Guide Section */
        .guide-list { padding-left: 20px; color: #444; line-height: 1.8; }
        .guide-list li { margin-bottom: 10px; font-size: 15px; }

    </style>
</head>
<body>

    <div class="sidebar">
        <div class="logo-admin"><i class="fas fa-car-side" style="color: var(--primary-blue);"></i> RentalKu Admin</div>
        <div class="nav-menu">
            <a href="dashboard.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="kategori.php" class="nav-item"><i class="fas fa-tags"></i> Kategori</a>
            <a href="armada.php" class="nav-item"><i class="fas fa-car"></i> Armada Mobil</a>
            <a href="transaksi.php" class="nav-item"><i class="fas fa-file-invoice-dollar"></i> Transaksi</a>
            <a href="customer.php" class="nav-item"><i class="fas fa-users"></i> Data Customer</a>
            <a href="pengembalian.php" class="nav-item active"><i class="fas fa-redo-alt"></i> Pengembalian</a>
            <a href="laporan.php" class="nav-item"><i class="fas fa-file-alt"></i> Laporan</a>
        </div>
    </div>

    <div class="main-content">
        <div class="dashboard-header">
            <div>
                <h1 style="margin: 0; font-size: 32px;">Proses Pengembalian</h1>
                <p style="color: var(--text-gray); margin-top: 5px;">Kelola pengembalian mobil dari customer</p>
            </div>
            <div class="admin-profile">
                <div style="text-align: right;">
                    <div style="font-weight: bold;">Admin User</div>
                    <div style="font-size: 12px; color: var(--text-gray);">Administrator</div>
                </div>
                <i class="fas fa-sign-out-alt"></i>
            </div>
        </div>

        <div class="section-card">
            <label class="card-title">Cari Rental ID</label>
            <form action="proses_cari.php" method="GET" class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" class="search-input" placeholder="Masukkan ID Rental (contoh: R001)" name="rental_id">
                <button type="submit" class="btn-cari">
                    <i class="fas fa-search"></i> Cari
                </button>
            </form>
        </div>

        <div class="section-card">
            <label class="card-title">Panduan Proses Pengembalian</label>
            <ol class="guide-list">
                <li>Cari rental berdasarkan ID yang diberikan customer</li>
                <li>Cek kondisi fisik mobil secara menyeluruh</li>
                <li>Input tanggal pengembalian aktual</li>
                <li>Catat kondisi mobil (lecet, bensin, kebersihan, dll)</li>
                <li>Sistem akan otomatis menghitung denda jika terlambat</li>
                <li>Konfirmasi total tagihan dengan customer</li>
                <li>Proses pengembalian akan mengubah status rental menjadi "Completed"</li>
                <li>Status mobil akan otomatis kembali ke "Available"</li>
            </ol>
        </div>
    </div>

</body>
</html>