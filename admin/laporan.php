<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - RentalKu Admin</title>
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

        /* Main Content Styling */
        .main-content { margin-left: calc(var(--sidebar-width) + 40px); flex: 1; padding: 20px 40px 40px 0; }
        
        /* Header Dashboard */
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; margin-top: 10px; }
        .admin-profile { display: flex; align-items: center; gap: 15px; }

        /* Filter Section */
        .section-card { background: #fff; border-radius: 16px; padding: 25px; border: 1px solid #f0f0f0; margin-bottom: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .filter-row { display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap; }
        .input-box { display: flex; flex-direction: column; gap: 8px; }
        .input-box label { font-size: 13px; font-weight: 600; color: #333; }
        .date-input-wrapper { position: relative; }
        .date-input-wrapper i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #888; pointer-events: none; }
        .date-input-wrapper input { padding: 10px 10px 10px 35px; border: 1px solid #ddd; border-radius: 8px; outline: none; cursor: pointer; background: #fafafa; }

        /* Action Buttons */
        .btn { padding: 10px 20px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; }
        .btn-dark { background: #0b0c10; color: #fff; }
        .btn-outline { background: #fff; border: 1px solid #ddd; color: #333; }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 16px; border: 1px solid #f0f0f0; }
        .stat-val { font-size: 28px; font-weight: bold; margin: 10px 0; }
        .stat-label { color: var(--text-gray); font-size: 13px; }

        /* Table Styling */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { text-align: left; padding: 12px; border-bottom: 2px solid #f8f9fa; color: #888; font-size: 13px; }
        td { padding: 15px 12px; border-bottom: 1px solid #f8f9fa; font-size: 14px; }
        .status { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .status-pending { background: #fff8e6; color: #ffa000; }
        .status-completed { background: #e6fffa; color: #00bfa5; }

        /* Activity Log */
        .log-item { display: flex; gap: 15px; padding: 15px 0; border-bottom: 1px solid #f9f9f9; }
        .log-icon { width: 35px; height: 35px; background: #eef4ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary-blue); }
    </style>
</head>
<body>

   <div class="sidebar">
    <div class="logo-admin">
        <i class="fas fa-car-side" style="color: var(--primary-blue);"></i> RentalKu Admin
    </div>
    <div class="nav-menu">
        <a href="../index.php" class="nav-item">
            <i class="fas fa-th-large"></i> Dashboard
        </a>
        
        <a href="kategori.php" class="nav-item">
            <i class="fas fa-tags"></i> Kategori
        </a>
        
        <a href="armada.php" class="nav-item">
            <i class="fas fa-car"></i> Armada Mobil
        </a>
        
        <a href="transaksi.php" class="nav-item">
            <i class="fas fa-file-invoice-dollar"></i> Transaksi
        </a>
        
        <a href="customer.php" class="nav-item">
            <i class="fas fa-users"></i> Data Customer
        </a>
        
        <a href="pengembalian.php" class="nav-item">
            <i class="fas fa-redo-alt"></i> Pengembalian
        </a>
        
        <a href="laporan.php" class="nav-item">
            <i class="fas fa-file-alt"></i> Laporan
        </a>
    </div>
</div>

    <div class="main-content">
        <div class="dashboard-header">
            <div>
                <h1 style="margin: 0;">Laporan</h1>
                <p style="color: var(--text-gray); margin-top: 5px;">Laporan transaksi dan aktivitas sistem</p>
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
            <div class="filter-row">
                <div class="input-box">
                    <label>Tanggal Mulai</label>
                    <div class="date-input-wrapper">
                        <i class="fas fa-calendar-alt"></i>
                        <input type="date">
                    </div>
                </div>
                <div class="input-box">
                    <label>Tanggal Selesai</label>
                    <div class="date-input-wrapper">
                        <i class="fas fa-calendar-alt"></i>
                        <input type="date">
                    </div>
                </div>
                <button class="btn btn-dark"><i class="fas fa-file-alt"></i> Tampilkan</button>
                <button class="btn btn-outline"><i class="fas fa-file-excel"></i> Excel</button>
                <button class="btn btn-outline"><i class="fas fa-file-pdf"></i> PDF</button>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Transaksi</div>
                <div class="stat-val">3</div>
                <div class="stat-label">2 selesai</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Pendapatan</div>
                <div class="stat-val" style="color: #27ae60;">Rp 5.2jt</div>
                <div class="stat-label">Periode saat ini</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Rata-rata Sewa</div>
                <div class="stat-val" style="color: var(--primary-blue);">3.3 hari</div>
                <div class="stat-label">Per transaksi</div>
            </div>
        </div>

        <div class="section-card">
            <h3 style="margin-top: 0;">Laporan Transaksi</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Customer</th>
                        <th>Mobil</th>
                        <th>Durasi</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>R001</strong></td>
                        <td>08 Apr 2026</td>
                        <td>John Doe</td>
                        <td>Toyota Fortuner 2023</td>
                        <td>2 hari</td>
                        <td>Rp 1.000.000</td>
                        <td><span class="status status-pending">Pending</span></td>
                    </tr>
                    <tr>
                        <td><strong>R002</strong></td>
                        <td>05 Apr 2026</td>
                        <td>Jane Smith</td>
                        <td>Honda Civic 2024</td>
                        <td>3 hari</td>
                        <td>Rp 1.200.000</td>
                        <td><span class="status status-completed">Completed</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="section-card">
            <h3 style="margin-top: 0;">Log Aktivitas Admin</h3>
            <div class="log-item">
                <div class="log-icon"><i class="far fa-file-alt"></i></div>
                <div>
                    <div style="font-weight: bold; font-size: 14px;">Approved rental R001</div>
                    <div style="font-size: 12px; color: var(--text-gray);">oleh Admin User • 08 Apr 2026, 10:30</div>
                </div>
            </div>
            <div class="log-item">
                <div class="log-icon"><i class="far fa-file-alt"></i></div>
                <div>
                    <div style="font-weight: bold; font-size: 14px;">Updated car status - Toyota Fortuner</div>
                    <div style="font-size: 12px; color: var(--text-gray);">oleh Admin User • 08 Apr 2026, 09:15</div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>