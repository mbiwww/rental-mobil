<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Customer - RentalKu Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #2b67f6;
            --bg-body: #f8faff;
            --sidebar-width: 240px;
            --text-gray: #7f8c8d;
        }

        body { font-family: 'Segoe UI', sans-serif; margin: 0; background-color: var(--bg-body); display: flex; }

        /* Sidebar & Layout */
        .sidebar { width: var(--sidebar-width); height: 100vh; background: #fff; padding: 20px; position: fixed; border-right: 1px solid #edf2f7; }
        .logo-admin { font-weight: bold; font-size: 20px; margin-bottom: 40px; display: flex; align-items: center; gap: 10px; }
        .nav-menu { display: flex; flex-direction: column; gap: 10px; }
        .nav-item { text-decoration: none; color: #555; padding: 12px 15px; border-radius: 10px; display: flex; align-items: center; gap: 12px; }
        .nav-item.active { background: #eef4ff; color: var(--primary-blue); font-weight: 600; }

        .main-content { margin-left: calc(var(--sidebar-width) + 40px); flex: 1; padding: 20px 40px 40px 0; }

        /* Header */
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }

        /* Stats Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #fff; padding: 25px; border-radius: 16px; border: 1px solid #f0f0f0; }
        .stat-label { font-size: 14px; font-weight: 600; color: #333; margin-bottom: 15px; }
        .stat-number { font-size: 32px; font-weight: bold; }

        /* Search Bar */
        .search-card { background: #fff; border-radius: 16px; padding: 15px; margin-bottom: 25px; border: 1px solid #f0f0f0; }
        .search-inner { position: relative; display: flex; align-items: center; }
        .search-inner i { position: absolute; left: 15px; color: #aaa; }
        .search-input { width: 100%; padding: 12px 45px; border: none; background: #f1f3f5; border-radius: 10px; outline: none; }

        /* Table Card */
        .table-card { background: #fff; border-radius: 16px; padding: 25px; border: 1px solid #f0f0f0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; color: #888; font-size: 13px; padding: 15px 10px; border-bottom: 1px solid #f8f9fa; }
        td { padding: 18px 10px; border-bottom: 1px solid #f8f9fa; font-size: 14px; vertical-align: middle; }

        /* Badges */
        .badge { padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
        .badge-verified { background: #dcfce7; color: #15803d; }
        .badge-pending { background: #fef9c3; color: #a16207; }
        .badge-active { background: #0b0c10; color: #fff; }

        .contact-info { display: flex; flex-direction: column; font-size: 13px; }
        .contact-email { color: #555; }
        .contact-phone { color: #888; }
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
            <a href="customer.php" class="nav-item active"><i class="fas fa-users"></i> Data Customer</a>
            <a href="pengembalian.php" class="nav-item"><i class="fas fa-redo-alt"></i> Pengembalian</a>
            <a href="laporan.php" class="nav-item"><i class="fas fa-file-alt"></i> Laporan</a>
        </div>
    </div>

    <div class="main-content">
        <div class="dashboard-header">
            <div>
                <h1 style="margin: 0; font-size: 32px;">Data Customer</h1>
                <p style="color: var(--text-gray); margin-top: 5px;">Kelola data pelanggan terdaftar</p>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="text-align: right;">
                    <div style="font-weight: bold;">Admin User</div>
                    <div style="font-size: 12px; color: var(--text-gray);">Administrator</div>
                </div>
                <i class="fas fa-sign-out-alt"></i>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Customer</div>
                <div class="stat-number">3</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Terverifikasi</div>
                <div class="stat-number" style="color: #2ecc71;">2</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Belum Verifikasi</div>
                <div class="stat-number" style="color: #f1c40f;">1</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Customer Aktif</div>
                <div class="stat-number" style="color: var(--primary-blue);">3</div>
            </div>
        </div>

        <div class="search-card">
            <div class="search-inner">
                <i class="fas fa-search"></i>
                <input type="text" class="search-input" placeholder="Cari customer...">
            </div>
        </div>

        <div class="table-card">
            <h3 style="margin-top: 0;">Daftar Customer (3)</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Kontak</th>
                        <th>Verifikasi</th>
                        <th>Total Sewa</th>
                        <th>Status</th>
                        <th>Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2</td>
                        <td><strong>John Doe</strong></td>
                        <td class="contact-info">
                            <span class="contact-email">john@example.com</span>
                            <span class="contact-phone">081234567891</span>
                        </td>
                        <td><span class="badge badge-verified"><i class="fas fa-user-check"></i> Verified</span></td>
                        <td>3 kali</td>
                        <td><span class="badge badge-active">active</span></td>
                        <td>15/1/2026</td>
                        <td><i class="far fa-eye" style="cursor: pointer;"></i></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td><strong>Jane Smith</strong></td>
                        <td class="contact-info">
                            <span class="contact-email">jane@example.com</span>
                            <span class="contact-phone">081234567892</span>
                        </td>
                        <td><span class="badge badge-pending"><i class="fas fa-user-clock"></i> Pending</span></td>
                        <td>0 kali</td>
                        <td><span class="badge badge-active">active</span></td>
                        <td>1/4/2026</td>
                        <td><i class="far fa-eye" style="cursor: pointer;"></i></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td><strong>Bob Wilson</strong></td>
                        <td class="contact-info">
                            <span class="contact-email">bob@example.com</span>
                            <span class="contact-phone">081234567893</span>
                        </td>
                        <td><span class="badge badge-verified"><i class="fas fa-user-check"></i> Verified</span></td>
                        <td>5 kali</td>
                        <td><span class="badge badge-active">active</span></td>
                        <td>20/11/2025</td>
                        <td><i class="far fa-eye" style="cursor: pointer;"></i></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>