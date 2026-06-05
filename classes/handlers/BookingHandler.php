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
class BookingHandler extends BaseHandler
{
    // Model yang dibutuhkan
    private Rental $rentalModel;
    private Car    $carModel;

    /**
     * Constructor — Inisialisasi model Rental dan Car
     *
     * @param PDO $db Instance koneksi PDO
     */
    public function __construct(PDO $db)
    {
        parent::__construct($db);
        $this->rentalModel = new Rental($db);
        $this->carModel    = new Car($db);
    }

    /**
     * Dispatch — Routing aksi berdasarkan nilai action
     */
    public function dispatch(): void
    {
        switch ($this->getAction()) {
            case 'create_booking': $this->createBooking(); break;
            case 'cancel_booking': $this->cancelBooking(); break;
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
        $driverCost  = ($rentalType === 'with_driver') ? ($totalDays * 200000) : 0;

        // Biaya pickup & dropoff (masing-masing Rp 50.000)
        // Dengan supir: sudah include di biaya supir, jadi tidak ada biaya tambahan
        if ($rentalType === 'with_driver') {
            $pickupFee  = 0;
            $dropoffFee = 0;
        } else {
            $pickupFee  = ($pickupOption  === 'antar') ? 50000 : 0;
            $dropoffFee = ($dropoffOption === 'ambil') ? 50000 : 0;
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
}
