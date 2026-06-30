<?php
/**
 * Class BookingHandler — Handler pemesanan mobil
 *
 * Menangani aksi booking dari halaman detail.php:
 * - create_booking : buat booking baru (customer harus login)
 * - cancel_booking : batalkan booking milik customer sendiri
 *
 * Aturan: tidak ada output HTML di class ini, selalu redirect+exit.
 */
require_once __DIR__ . '/../Payment.php';

class BookingHandler extends BaseHandler
{
    // Model yang dibutuhkan
    private Rental  $rentalModel;
    private Car     $carModel;
    private Payment $paymentModel;

    /**
     * Constructor — Inisialisasi model Rental, Car, dan Payment
     *
     * @param PDO $db Instance koneksi PDO
     */
    public function __construct(PDO $db)
    {
        parent::__construct($db);
        $this->rentalModel  = new Rental($db);
        $this->carModel     = new Car($db);
        $this->paymentModel = new Payment($db);
    }

    /**
     * Dispatch — Routing aksi berdasarkan nilai action
     */
    public function dispatch(): void
    {
        switch ($this->getAction()) {
            case 'create_booking': $this->createBooking(); break;
            case 'cancel_booking': $this->cancelBooking(); break;
            case 'pay_rental':     $this->payRental();      break;
            case 'request_refund': $this->requestRefund();  break;
            case 'pay_penalty':    $this->payPenalty();     break;
            default:               $this->redirect('../pages/katalog.php');
        }
    }

    // -------------------------------------------------------
    // CREATE BOOKING
    // -------------------------------------------------------

    /**
     * Proses booking baru dari form di detail.php
     * Customer wajib login. Validasi tanggal, ketersediaan mobil, dan hitung total.
     */
    private function createBooking(): void
    {
        $this->requireMethod('POST', '../pages/katalog.php');
        $this->requireAuth('customer', '../pages/login.php');

        $userId    = (int) $_SESSION['user_id'];
        $carId     = (int) ($_POST['car_id']    ?? 0);
        $startDate = trim($_POST['start_date']   ?? '');
        $endDate   = trim($_POST['end_date']     ?? '');
        $rentalType = in_array($_POST['rental_type'] ?? '', ['with_driver', 'without_driver'])
                        ? $_POST['rental_type']
                        : 'without_driver';
        $pickupLocation  = trim($_POST['pickup_location']  ?? '');
        $dropoffLocation = trim($_POST['dropoff_location'] ?? '');
        $pickupOption    = trim($_POST['pickup_option']    ?? 'kantor');
        $dropoffOption   = trim($_POST['dropoff_option']   ?? 'kantor');

        $redirectBack = '../pages/detail.php?id=' . $carId;

        // ── Validasi: field wajib ────────────────────────────────────────────
        if (!$carId || !$startDate || !$endDate || !$pickupLocation || !$dropoffLocation) {
            $this->flashError('Semua field booking wajib diisi.');
            $this->redirect($redirectBack);
        }

        // ── Validasi: format tanggal ─────────────────────────────────────────
        $start = \DateTime::createFromFormat('Y-m-d', $startDate);
        $end   = \DateTime::createFromFormat('Y-m-d', $endDate);

        if (!$start || !$end) {
            $this->flashError('Format tanggal tidak valid.');
            $this->redirect($redirectBack);
        }

        $today = new \DateTime('today');

        if ($start < $today) {
            $this->flashError('Tanggal mulai tidak boleh sebelum hari ini.');
            $this->redirect($redirectBack);
        }

        // Hitung selisih hari (minimum 1 hari)
        $totalDays = (int) $end->diff($start)->days;

        if ($totalDays < 1) {
            $this->flashError('Durasi sewa minimal 1 hari (tanggal selesai harus setelah tanggal mulai).');
            $this->redirect($redirectBack);
        }

        // ── Ambil data mobil dari database ───────────────────────────────────
        $car = $this->carModel->getById($carId);

        if (!$car) {
            $this->flashError('Mobil tidak ditemukan.');
            $this->redirect('../pages/katalog.php');
        }

        // ── Validasi: status mobil harus available ───────────────────────────
        if ($car['status'] !== 'available') {
            $this->flashError('Mobil ini sedang tidak tersedia untuk disewa.');
            $this->redirect($redirectBack);
        }

        // ── Validasi: cek double booking pada rentang tanggal ────────────────
        if ($this->rentalModel->isCarBooked($carId, $startDate, $endDate)) {
            $this->flashError('Mobil sudah dibooking pada rentang tanggal yang dipilih. Pilih tanggal lain.');
            $this->redirect($redirectBack);
        }

        // ── Hitung biaya ─────────────────────────────────────────────────────
        $pricePerDay = (float) $car['price_per_day'];

        // Ambil pengaturan biaya dari database
        $stmtSettings = $this->db->query("SELECT key_name, value FROM settings");
        $settings = $stmtSettings->fetchAll(PDO::FETCH_KEY_PAIR);

        $dbDriverCost = isset($settings['driver_cost_per_day']) ? (float)$settings['driver_cost_per_day'] : 200000.0;
        $dbPickupFee  = isset($settings['pickup_fee']) ? (float)$settings['pickup_fee'] : 50000.0;
        $dbDropoffFee = isset($settings['dropoff_fee']) ? (float)$settings['dropoff_fee'] : 50000.0;

        $driverCost  = ($rentalType === 'with_driver') ? ($totalDays * $dbDriverCost) : 0;

        // Biaya pickup & dropoff
        // Dengan supir: sudah include di biaya supir, jadi tidak ada biaya tambahan
        if ($rentalType === 'with_driver') {
            $pickupFee  = 0;
            $dropoffFee = 0;
        } else {
            $pickupFee  = ($pickupOption  === 'antar') ? $dbPickupFee : 0;
            $dropoffFee = ($dropoffOption === 'ambil') ? $dbDropoffFee : 0;
        }

        $totalPrice = ($pricePerDay * $totalDays) + $driverCost + $pickupFee + $dropoffFee;

        // ── Simpan ke database ───────────────────────────────────────────────
        $rentalId = $this->rentalModel->create([
            'user_id'          => $userId,
            'car_id'           => $carId,
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'pickup_location'  => $pickupLocation,
            'dropoff_location' => $dropoffLocation,
            'total_price'      => $totalPrice,
            'rental_type'      => $rentalType,
            'driver_cost'      => $driverCost,
            'pickup_fee'       => $pickupFee,
            'dropoff_fee'      => $dropoffFee,
        ]);

        if ($rentalId > 0) {
            $this->flashSuccess('Booking berhasil! Silakan lanjutkan ke pembayaran.');
            $this->redirect('../pages/pembayaran.php?rental_id=' . $rentalId);
        } else {
            $this->flashError('Gagal membuat booking. Coba lagi.');
            $this->redirect($redirectBack);
        }
    }

    // -------------------------------------------------------
    // CANCEL BOOKING
    // -------------------------------------------------------

    /**
     * Proses pembatalan booking oleh customer
     * Hanya bisa membatalkan rental milik sendiri yang masih berstatus pending/confirmed.
     */
    private function cancelBooking(): void
    {
        $this->requireMethod('POST', '../pages/dashboard.php');
        $this->requireAuth('customer', '../pages/login.php');

        $userId   = (int) $_SESSION['user_id'];
        $rentalId = (int) ($_POST['rental_id'] ?? 0);

        if (!$rentalId) {
            $this->flashError('ID booking tidak valid.');
            $this->redirect('../pages/dashboard.php');
        }

        // Ambil data rental dan pastikan milik user yang sedang login
        $rental = $this->rentalModel->getById($rentalId);

        if (!$rental || (int) $rental['user_id'] !== $userId) {
            $this->flashError('Booking tidak ditemukan atau bukan milik Anda.');
            $this->redirect('../pages/dashboard.php');
        }

        // Hanya bisa dibatalkan jika status pending
        if ($rental['status'] !== 'pending') {
            $this->flashError('Booking hanya bisa dibatalkan jika masih berstatus "Menunggu Verifikasi".');
            $this->redirect('../pages/dashboard.php');
        }

        $result = $this->rentalModel->updateStatus($rentalId, 'cancelled');

        $this->flashRedirect(
            $result,
            'Booking berhasil dibatalkan.',
            'Gagal membatalkan booking. Coba lagi.',
            '../pages/dashboard.php'
        );
    }

    // -------------------------------------------------------
    // PAY RENTAL
    // -------------------------------------------------------

    /**
     * Upload bukti transfer dan simpan data pembayaran
     */
    private function payRental(): void
    {
        $this->requireMethod('POST', '../pages/dashboard.php');
        $this->requireAuth('customer', '../pages/login.php');

        $userId        = (int) $_SESSION['user_id'];
        $rentalId      = (int) ($_POST['rental_id'] ?? 0);
        $bankAccountId = (int) ($_POST['bank_account_id'] ?? 0);

        if (!$rentalId || !$bankAccountId) {
            $this->flashError('Pilih rekening tujuan transfer bank.');
            $this->redirect('../pages/pembayaran.php?rental_id=' . $rentalId);
        }

        // Ambil data rental dan validasi kepemilikan
        $rental = $this->rentalModel->getById($rentalId);
        if (!$rental || (int) $rental['user_id'] !== $userId) {
            $this->flashError('Transaksi tidak ditemukan atau bukan milik Anda.');
            $this->redirect('../pages/dashboard.php');
        }

        // Validasi status sewa (hanya pending yang bisa dibayar)
        if ($rental['status'] !== 'pending') {
            $this->flashError('Transaksi ini tidak dapat dibayar.');
            $this->redirect('../pages/dashboard.php');
        }

        // File upload handling
        if (!isset($_FILES['proof_image']) || $_FILES['proof_image']['error'] !== UPLOAD_ERR_OK) {
            $this->flashError('Bukti transfer wajib diunggah.');
            $this->redirect('../pages/pembayaran.php?rental_id=' . $rentalId);
        }

        $file = $_FILES['proof_image'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            $this->flashError('Format gambar tidak valid. Hanya JPG, JPEG, dan PNG yang diperbolehkan.');
            $this->redirect('../pages/pembayaran.php?rental_id=' . $rentalId);
        }
        
        if ($file['size'] > 5 * 1024 * 1024) {
            $this->flashError('Ukuran file gambar terlalu besar. Maksimal 5MB.');
            $this->redirect('../pages/pembayaran.php?rental_id=' . $rentalId);
        }
        
        $uploadDir = '../assets/uploads/payments/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = uniqid('proof_') . '.' . $ext;
        $targetPath = $uploadDir . $newFilename;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->flashError('Gagal mengunggah bukti pembayaran.');
            $this->redirect('../pages/pembayaran.php?rental_id=' . $rentalId);
        }

        // Simpan / update ke database
        $existingPayment = $this->paymentModel->getByRentalId($rentalId);
        if ($existingPayment) {
            // Hapus gambar lama jika ada
            if (!empty($existingPayment['proof_image'])) {
                $oldPath = $uploadDir . $existingPayment['proof_image'];
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $stmt = $this->db->prepare(
                "UPDATE payments SET bank_account_id = ?, proof_image = ?, status = 'pending', paid_at = NULL WHERE rental_id = ?"
            );
            $result = $stmt->execute([$bankAccountId, $newFilename, $rentalId]);
        } else {
            $paymentId = $this->paymentModel->create([
                'rental_id' => $rentalId,
                'method' => 'bank_transfer',
                'bank_account_id' => $bankAccountId,
                'proof_image' => $newFilename
            ]);
            $result = $paymentId > 0;
        }

        if ($result) {
            $this->flashSuccess('Bukti transfer berhasil diunggah! Pembayaran Anda sedang diverifikasi oleh admin.');
            $this->redirect('../pages/dashboard.php');
        } else {
            $this->flashError('Gagal menyimpan data pembayaran. Coba lagi.');
            $this->redirect('../pages/pembayaran.php?rental_id=' . $rentalId);
        }
    }

    // -------------------------------------------------------
    // REQUEST REFUND (REFUND REQUEST BY CUSTOMER)
    // -------------------------------------------------------

    /**
     * Memproses permintaan refund/cancel sewa oleh customer
     */
    private function requestRefund(): void
    {
        $this->requireMethod('POST', '../pages/dashboard.php');
        $this->requireAuth('customer', '../pages/login.php');

        $userId       = (int) $_SESSION['user_id'];
        $rentalId     = (int) ($_POST['rental_id'] ?? 0);
        $reasonOption = trim($_POST['reason_option'] ?? '');
        $reasonDetail = trim($_POST['reason_detail'] ?? '');
        $refundBankName      = trim($_POST['refund_bank_name'] ?? '');
        $refundAccountNumber = trim($_POST['refund_account_number'] ?? '');
        $refundAccountHolder = trim($_POST['refund_account_holder'] ?? '');

        if (!$rentalId || empty($reasonOption)) {
            $this->flashError('ID rental dan alasan pembatalan wajib diisi.');
            $this->redirect('../pages/dashboard.php');
        }

        if (empty($refundBankName) || empty($refundAccountNumber) || empty($refundAccountHolder)) {
            $this->flashError('Data rekening tujuan refund wajib diisi lengkap (nama nasabah, nama bank, nomor rekening).');
            $this->redirect('../pages/dashboard.php');
        }

        // Ambil data rental dan pastikan milik user yang sedang login
        $rental = $this->rentalModel->getById($rentalId);
        if (!$rental || (int) $rental['user_id'] !== $userId) {
            $this->flashError('Transaksi tidak ditemukan atau bukan milik Anda.');
            $this->redirect('../pages/dashboard.php');
        }

        // Hanya rental berstatus confirmed yang bisa di-refund
        if ($rental['status'] !== 'confirmed') {
            $this->flashError('Hanya pesanan yang sudah disetujui (Confirmed) yang bisa diajukan refund/pembatalan.');
            $this->redirect('../pages/dashboard.php');
        }

        $this->db->beginTransaction();
        try {
            // 1. Simpan alasan refund ke tabel refunds
            $stmt = $this->db->prepare("
                INSERT INTO refunds (rental_id, reason_option, reason_detail, refund_bank_name, refund_account_number, refund_account_holder, status)
                VALUES (?, ?, ?, ?, ?, ?, 'requested')
            ");
            $stmt->execute([$rentalId, $reasonOption, $reasonDetail, $refundBankName, $refundAccountNumber, $refundAccountHolder]);

            // 2. Ubah status rental menjadi cancel_requested
            $this->rentalModel->updateStatus($rentalId, 'cancel_requested');

            $this->db->commit();
            $this->flashSuccess('Permintaan pembatalan & refund berhasil diajukan. Status akan segera diperiksa admin.');
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->flashError('Gagal memproses pembatalan: ' . $e->getMessage());
        }

        $this->redirect('../pages/dashboard.php');
    }

    /**
     * Upload bukti transfer denda keterlambatan pengembalian
     */
    private function payPenalty(): void
    {
        $this->requireMethod('POST', '../pages/dashboard.php');
        $this->requireAuth('customer', '../pages/login.php');

        $userId   = (int) $_SESSION['user_id'];
        $rentalId = (int) ($_POST['rental_id'] ?? 0);

        if (!$rentalId) {
            $this->flashError('ID rental tidak valid.');
            $this->redirect('../pages/dashboard.php');
        }

        // Ambil data rental dan validasi kepemilikan
        $rental = $this->rentalModel->getById($rentalId);
        if (!$rental || (int) $rental['user_id'] !== $userId) {
            $this->flashError('Transaksi tidak ditemukan atau bukan milik Anda.');
            $this->redirect('../pages/dashboard.php');
        }

        // Validasi status sewa (ongoing atau completed) dan denda keterlambatan
        if ($rental['status'] !== 'completed' && $rental['status'] !== 'ongoing') {
            $this->flashError('Transaksi ini tidak sedang disewa atau selesai.');
            $this->redirect('../pages/dashboard.php');
        }

        $penaltyFee = (float)$rental['penalty_fee'];
        if ($rental['status'] === 'ongoing') {
            $penaltyInfo = $this->rentalModel->calculatePenalty($rental);
            if (!$penaltyInfo['is_overdue'] || $penaltyInfo['penalty_fee'] <= 0) {
                $this->flashError('Transaksi ini tidak memiliki denda keterlambatan.');
                $this->redirect('../pages/dashboard.php');
            }
            $penaltyFee = (float)$penaltyInfo['penalty_fee'];
        } else {
            if ($penaltyFee <= 0) {
                $this->flashError('Transaksi ini tidak memiliki denda keterlambatan.');
                $this->redirect('../pages/dashboard.php');
            }
        }

        // Validasi status pembayaran denda (hanya unpaid / rejected yang bisa dibayar)
        if ($rental['penalty_payment_status'] === 'confirmed' || $rental['penalty_payment_status'] === 'pending') {
            $this->flashError('Pembayaran denda sudah dikirim atau telah lunas.');
            $this->redirect('../pages/dashboard.php');
        }

        // File upload handling
        if (!isset($_FILES['penalty_proof_image']) || $_FILES['penalty_proof_image']['error'] !== UPLOAD_ERR_OK) {
            $this->flashError('Bukti transfer denda wajib diunggah.');
            $this->redirect('../pages/dashboard.php');
        }

        $file = $_FILES['penalty_proof_image'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            $this->flashError('Format gambar tidak valid. Hanya JPG, JPEG, dan PNG yang diperbolehkan.');
            $this->redirect('../pages/dashboard.php');
        }
        
        if ($file['size'] > 5 * 1024 * 1024) {
            $this->flashError('Ukuran file gambar terlalu besar. Maksimal 5MB.');
            $this->redirect('../pages/dashboard.php');
        }
        
        $uploadDir = '../assets/uploads/payments/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = uniqid('penalty_proof_') . '.' . $ext;
        $targetPath = $uploadDir . $newFilename;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->flashError('Gagal mengunggah bukti pembayaran denda.');
            $this->redirect('../pages/dashboard.php');
        }

        // Hapus gambar denda lama jika ada
        if (!empty($rental['penalty_proof_image'])) {
            $oldPath = $uploadDir . $rental['penalty_proof_image'];
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        // Update ke database (simpan status pending, gambar bukti, dan nominal denda)
        $stmt = $this->db->prepare(
            "UPDATE rentals 
             SET penalty_payment_status = 'pending', 
                 penalty_proof_image = ?, 
                 penalty_fee = ? 
             WHERE id = ?"
        );
        $result = $stmt->execute([$newFilename, $penaltyFee, $rentalId]);

        if ($result) {
            $this->flashSuccess('Bukti transfer denda berhasil diunggah! Pembayaran denda Anda sedang diverifikasi oleh admin.');
        } else {
            if (file_exists($targetPath)) {
                @unlink($targetPath);
            }
            $this->flashError('Gagal menyimpan data pembayaran denda. Coba lagi.');
        }
        $this->redirect('../pages/dashboard.php');
    }
}
