<?php
session_start();

require_once '../config/database.php';
require_once '../classes/Database.php';
require_once '../classes/BaseModel.php';
require_once '../classes/Rental.php';
require_once '../classes/BankAccount.php';
require_once '../classes/Payment.php';

// Proteksi Auth Customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    $_SESSION['error'] = 'Silakan login terlebih dahulu.';
    header('Location: login.php');
    exit;
}

$rentalId = isset($_GET['rental_id']) ? (int)$_GET['rental_id'] : 0;
if ($rentalId <= 0) {
    header('Location: dashboard.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$rentalModel = new Rental($db);
$rental = $rentalModel->getById($rentalId);

// Validasi data rental milik user
if (!$rental || (int)$rental['user_id'] !== (int)$_SESSION['user_id']) {
    $_SESSION['error'] = 'Pemesanan tidak ditemukan.';
    header('Location: dashboard.php');
    exit;
}

// Hanya pending rental yang boleh diakses halaman bayar
if ($rental['status'] !== 'pending') {
    $_SESSION['error'] = 'Pemesanan ini tidak dalam status pending/belum dibayar.';
    header('Location: dashboard.php');
    exit;
}

// Ambil bank account yang aktif
$bankAccountModel = new BankAccount($db);
$bankAccounts = $bankAccountModel->getActive();

// Ambil data pembayaran jika sudah pernah diupload sebelumnya
$paymentModel = new Payment($db);
$existingPayment = $paymentModel->getByRentalId($rentalId);

// Hitung hari sewa
$days = (int) ((strtotime($rental['end_date']) - strtotime($rental['start_date'])) / 86400);
if ($days <= 0) $days = 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - RentalKu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/flash.css">
    <style>
        :root {
            --bg-light: #f8f9fa;
            --primary-blue: #2b67f6;
            --text-dark: #000;
            --card-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; background-color: #f5f7fa; }
        
        /* Container Utama */
        .main-container { padding: 40px 80px; display: grid; grid-template-columns: 1.8fr 1fr; gap: 40px; }

        @media (max-width: 992px) {
            .main-container {
                grid-template-columns: 1fr;
                padding: 20px;
            }
        }

        h1 { grid-column: span 2; font-size: 32px; margin-bottom: 20px; color: #0b2b4a; font-weight: bold; }

        @media (max-width: 992px) {
            h1 { grid-column: span 1; }
        }

        /* Card Section */
        .card { background: #fff; border-radius: 12px; padding: 25px; border: 1px solid #f0f0f0; box-shadow: var(--card-shadow); margin-bottom: 25px; }
        .card-title { font-weight: bold; font-size: 18px; margin-bottom: 20px; color: #0b2b4a; }

        /* Form Input */
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px; color: #495057; }
        input[type="text"], input[type="email"] {
            width: 100%; padding: 12px; border: none; background-color: #f1f3f5; border-radius: 8px; box-sizing: border-box; font-weight: 500; color: #212529;
        }

        /* Upload Box */
        .upload-area {
            border: 2px dashed #ddd; border-radius: 12px; padding: 40px; text-align: center; color: #777; cursor: pointer; transition: all 0.2s ease-in-out;
        }
        .upload-area:hover {
            border-color: var(--primary-blue);
            background-color: rgba(43, 103, 246, 0.02);
        }
        .upload-area i { font-size: 40px; margin-bottom: 10px; color: #bbb; }

        /* Summary Section (Right Side) */
        .summary-card { position: sticky; top: 20px; }
        .price-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 15px; }
        .total-row { border-top: 1px solid #eee; padding-top: 20px; margin-top: 20px; display: flex; justify-content: space-between; font-weight: bold; font-size: 20px; color: var(--primary-blue); }

        /* Info Transfer Box */
        .info-transfer { background-color: #f0f4ff; padding: 20px; border-radius: 10px; margin-bottom: 25px; border-left: 4px solid var(--primary-blue); }
        .info-transfer p { margin: 5px 0; font-size: 14px; }

        .btn-confirm {
            width: 100%; background: #0b2b4a; color: white; padding: 15px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 16px; transition: all 0.2s;
        }
        .btn-confirm:hover {
            background-color: #1e3a6f;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/flash.php'; ?>

    <div class="container py-2">
        <div class="main-container">
            <h1>Pembayaran Pemesanan</h1>

            <div class="left-content">
                <form action="../handlers/booking_handler.php?action=pay_rental" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="rental_id" value="<?= $rentalId ?>">

                    <div class="card">
                        <div class="card-title">Informasi Penyewa</div>
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" value="<?= htmlspecialchars($rental['user_name']) ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" value="<?= htmlspecialchars($rental['user_email']) ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Nomor HP</label>
                            <input type="text" value="<?= htmlspecialchars($rental['user_phone']) ?>" readonly>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-title">Pilih Rekening Transfer Bank</div>
                        <div class="form-group">
                            <label for="bank_account_id">Pilih Bank Rekening Tujuan</label>
                            <select name="bank_account_id" id="bank_account_id" class="form-select p-3" style="background-color: #f1f3f5; border: none; border-radius: 8px;" required>
                                <option value="">-- Pilih Bank Rekening Admin --</option>
                                <?php foreach ($bankAccounts as $bank): ?>
                                    <option value="<?= $bank['id'] ?>" 
                                            data-bank-name="<?= htmlspecialchars($bank['bank_name']) ?>" 
                                            data-acc-number="<?= htmlspecialchars($bank['account_number']) ?>" 
                                            data-acc-holder="<?= htmlspecialchars($bank['account_holder']) ?>"
                                            <?= ($existingPayment && (int)$existingPayment['bank_account_id'] === (int)$bank['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($bank['bank_name']) ?> &middot; <?= htmlspecialchars($bank['account_number']) ?> (a/n <?= htmlspecialchars($bank['account_holder']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="info-transfer mt-4" id="transfer_details" style="display: none;">
                            <p><strong>Informasi Rekening Transfer:</strong></p>
                            <p>Bank: <span id="lbl_bank_name" class="fw-bold">-</span></p>
                            <p>No. Rekening: <span id="lbl_acc_number" class="fw-bold fs-5 text-primary">-</span></p>
                            <p>Atas Nama: <span id="lbl_acc_holder" class="fw-bold">-</span></p>
                            <p class="mt-3 text-secondary" style="font-size: 13px;"><i class="bi bi-info-circle me-1"></i>Silakan transfer sebesar nominal tagihan di kolom kanan dan unggah bukti transfer di bawah.</p>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-title">Upload Bukti Pembayaran</div>
                        <div class="upload-area" onclick="document.getElementById('proof_image').click();">
                            <i class="fas fa-cloud-upload-alt"></i><br>
                            <span style="color: var(--primary-blue);" id="upload_status">Klik untuk upload</span> <br>
                            <span style="font-size: 12px; color: #999;">Format: JPG, JPEG, PNG (Max 5MB)</span>
                            <input type="file" name="proof_image" id="proof_image" accept="image/png, image/jpeg, image/jpg" style="display: none;" required>
                        </div>
                        <?php if ($existingPayment && !empty($existingPayment['proof_image'])): ?>
                            <div class="mt-4 text-center">
                                <span class="text-secondary small d-block mb-2"><i class="bi bi-image me-1"></i>Bukti Transfer Terunggah Sebelumnya:</span>
                                <a href="../assets/uploads/payments/<?= htmlspecialchars($existingPayment['proof_image']) ?>" target="_blank" title="Klik untuk melihat full size">
                                    <img src="../assets/uploads/payments/<?= htmlspecialchars($existingPayment['proof_image']) ?>" alt="Bukti Transfer" class="img-thumbnail rounded-3 shadow-sm" style="max-height: 180px;">
                                </a>
                                <?php if ($existingPayment['status'] === 'rejected'): ?>
                                    <div class="text-danger mt-2 small fw-bold">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Pembayaran sebelumnya ditolak admin. Silakan upload bukti bayar baru.
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn-confirm"><i class="bi bi-shield-check me-2"></i>Konfirmasi Pembayaran</button>
                </form>
            </div>

            <div class="right-content">
                <div class="card summary-card">
                    <div class="card-title">Detail Pesanan</div>
                    <p class="mb-1" style="font-size: 18px; color: #0b2b4a; font-weight: bold;">
                        <?= htmlspecialchars($rental['brand'] . ' ' . $rental['model'] . ' ' . $rental['year']) ?>
                    </p>
                    <p style="font-size: 14px; color: #6c757d; margin-bottom: 20px;">
                        Masa Sewa: <br>
                        <strong><?= date('d M Y', strtotime($rental['start_date'])) ?></strong> s/d <strong><?= date('d M Y', strtotime($rental['end_date'])) ?></strong> (<?= $days ?> Hari)
                    </p>
                    
                    <div class="price-row">
                        <span class="text-muted">Biaya Sewa (<?= $days ?> Hari)</span>
                        <span>Rp <?= number_format($rental['price_per_day'] * $days, 0, ',', '.') ?></span>
                    </div>

                    <?php if ($rental['driver_cost'] > 0): ?>
                        <div class="price-row">
                            <span class="text-muted">Biaya Supir</span>
                            <span>Rp <?= number_format($rental['driver_cost'], 0, ',', '.') ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($rental['pickup_fee'] > 0): ?>
                        <div class="price-row">
                            <span class="text-muted">Biaya Antar (Pickup)</span>
                            <span>Rp <?= number_format($rental['pickup_fee'], 0, ',', '.') ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($rental['dropoff_fee'] > 0): ?>
                        <div class="price-row">
                            <span class="text-muted">Biaya Jemput (Dropoff)</span>
                            <span>Rp <?= number_format($rental['dropoff_fee'], 0, ',', '.') ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="total-row">
                        <span>Total Pembayaran</span>
                        <span>Rp <?= number_format($rental['total_price'], 0, ',', '.') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var bankSelect = document.getElementById('bank_account_id');
            var transferDetails = document.getElementById('transfer_details');
            var lblBankName = document.getElementById('lbl_bank_name');
            var lblAccNumber = document.getElementById('lbl_acc_number');
            var lblAccHolder = document.getElementById('lbl_acc_holder');
            var fileInput = document.getElementById('proof_image');
            var uploadStatus = document.getElementById('upload_status');

            bankSelect.addEventListener('change', function () {
                var selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    lblBankName.textContent = selectedOption.getAttribute('data-bank-name');
                    lblAccNumber.textContent = selectedOption.getAttribute('data-acc-number');
                    lblAccHolder.textContent = selectedOption.getAttribute('data-acc-holder');
                    transferDetails.style.display = 'block';
                } else {
                    transferDetails.style.display = 'none';
                }
            });

            // Trigger on load
            if (bankSelect.value) {
                bankSelect.dispatchEvent(new Event('change'));
            }

            fileInput.addEventListener('change', function () {
                var file = this.files[0];
                if (file) {
                    uploadStatus.textContent = 'File terpilih: ' + file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
                    uploadStatus.style.color = '#198754';
                } else {
                    uploadStatus.textContent = 'Klik untuk upload';
                    uploadStatus.style.color = 'var(--primary-blue)';
                }
            });
        });
    </script>
</body>
</html>