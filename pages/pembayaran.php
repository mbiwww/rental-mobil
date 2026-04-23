<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - RentalKu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --bg-light: #f8f9fa;
            --primary-blue: #2b67f6;
            --text-dark: #000;
            --card-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; background-color: #fff; }
        
        /* Navbar */
        header { display: flex; justify-content: space-between; padding: 20px 80px; border-bottom: 1px solid #eee; align-items: center; }
        .logo { font-weight: bold; font-size: 22px; display: flex; align-items: center; gap: 8px; }
        nav a { text-decoration: none; color: #333; margin-right: 30px; }

        /* Container Utama */
        .main-container { padding: 40px 80px; display: grid; grid-template-columns: 1.8fr 1fr; gap: 40px; }

        h1 { grid-column: span 2; font-size: 32px; margin-bottom: 20px; }

        /* Card Section */
        .card { background: #fff; border-radius: 12px; padding: 25px; border: 1px solid #f0f0f0; box-shadow: var(--card-shadow); margin-bottom: 25px; }
        .card-title { font-weight: bold; font-size: 18px; margin-bottom: 20px; }

        /* Form Input */
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px; }
        input[type="text"], input[type="email"] {
            width: 100%; padding: 12px; border: none; background-color: #f1f3f5; border-radius: 8px; box-sizing: border-box;
        }

        /* Bank Select */
        .bank-option {
            display: flex; align-items: center; padding: 15px; border: 1px solid #eee; border-radius: 10px; gap: 15px;
        }

        /* Upload Box */
        .upload-area {
            border: 2px dashed #ddd; border-radius: 12px; padding: 40px; text-align: center; color: #777; cursor: pointer;
        }
        .upload-area i { font-size: 40px; margin-bottom: 10px; color: #bbb; }

        /* Summary Section (Right Side) */
        .summary-card { position: sticky; top: 20px; }
        .price-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .total-row { border-top: 1px solid #eee; padding-top: 20px; margin-top: 20px; display: flex; justify-content: space-between; font-weight: bold; font-size: 20px; color: var(--primary-blue); }

        /* Info Transfer Box */
        .info-transfer { background-color: #f0f4ff; padding: 20px; border-radius: 10px; margin-bottom: 25px; }
        .info-transfer p { margin: 5px 0; font-size: 14px; }

        .btn-confirm {
            width: 100%; background: #0b0c10; color: white; padding: 15px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 16px;
        }
    </style>
</head>
<body>

    <header>
        <div class="logo"><i class="fas fa-car-side" style="color: var(--primary-blue);"></i> RentalKu</div>
        <div>
            <a href="#">Katalog Mobil</a>
            <i class="fas fa-shopping-cart" style="margin-right: 20px; position: relative;">
                <span style="position: absolute; top: -8px; right: -10px; background: #000; color: #fff; font-size: 10px; padding: 2px 5px; border-radius: 50%;">1</span>
            </i>
            <i class="far fa-user"></i>
        </div>
    </header>

    <div class="main-container">
        <h1>Pembayaran</h1>

        <div class="left-content">
            <div class="card">
                <div class="card-title">Informasi Penyewa</div>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" value="John Doe" readonly>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="john@example.com" readonly>
                </div>
                <div class="form-group">
                    <label>Nomor HP</label>
                    <input type="text" value="081234567891" readonly>
                </div>
            </div>

            <div class="info-transfer">
                <p><strong>Informasi Transfer:</strong></p>
                <p>Bank: BCA</p>
                <p>No. Rekening: 1234567890</p>
                <p>Atas Nama: PT RentalKu Indonesia</p>
                <p style="color: var(--primary-blue); font-weight: bold;">Total: Rp 1.500.000</p>
            </div>

            <div class="card">
                <div class="card-title">Upload Bukti Pembayaran</div>
                <div class="upload-area">
                    <i class="fas fa-cloud-upload-alt"></i><br>
                    <span style="color: var(--primary-blue);">Klik untuk upload</span> 
                    <span style="font-size: 12px;">Format: JPG, PNG (Max 5MB)</span>
                </div>
            </div>
            
            <button class="btn-confirm">Konfirmasi Pembayaran</button>
        </div>

        <div class="right-content">
            <div class="card summary-card">
                <div class="card-title">Detail Pesanan</div>
                <p><strong>Toyota Fortuner 2023</strong></p>
                <p style="font-size: 14px; color: #777;">Mulai: 23 Apr 2026<br>Selesai: 26 Apr 2026</p>
                
                <div class="price-row">
                    <span>Rp 500.000 × 3 hari</span>
                    <span style="color: var(--primary-blue); font-weight: bold;">Rp 1.500.000</span>
                </div>

                <div class="total-row">
                    <span>Total Pembayaran</span>
                    <span>Rp 1.500.000</span>
                </div>
            </div>
        </div>
    </div>

</body>
</html>