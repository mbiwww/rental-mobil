<?php
/**
 * Class Rental — Model untuk mengelola data pemesanan/booking
 *
 * Menangani semua operasi database pada tabel rentals menggunakan
 * Prepared Statement PDO. Extends BaseModel untuk mewarisi koneksi
 * dan method query umum.
 */
class Rental extends BaseModel
{
    // Tidak perlu constructor — diwarisi dari BaseModel

    /**
     * Buat booking baru
     *
     * @param array $data Data booking yang akan disimpan
     * @return int ID rental yang baru dibuat, atau 0 jika gagal
     */
    public function create(array $data): int
    {
        $this->query(
            "INSERT INTO rentals (
                user_id, car_id, start_date, end_date,
                pickup_location, dropoff_location,
                total_price, rental_type, driver_cost,
                pickup_fee, dropoff_fee, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')",
            [
                $data['user_id'],
                $data['car_id'],
                $data['start_date'],
                $data['end_date'],
                $data['pickup_location'],
                $data['dropoff_location'],
                $data['total_price'],
                $data['rental_type'],
                $data['driver_cost'],
                $data['pickup_fee'] ?? 0,
                $data['dropoff_fee'] ?? 0,
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    /**
     * Ambil semua rental milik satu user (untuk dashboard customer)
     *
     * @param int $userId ID user
     * @return array Daftar rental beserta nama mobil
     */
    public function getByUserId(int $userId): array
    {
        return $this->query(
            "SELECT r.*, c.brand, c.model, c.year, c.image, c.price_per_day
             FROM rentals r
             JOIN cars c ON r.car_id = c.id
             WHERE r.user_id = ?
             ORDER BY r.created_at DESC",
            [$userId]
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ambil satu rental berdasarkan ID
     *
     * @param int $id ID rental
     * @return array|false Data rental atau false jika tidak ditemukan
     */
    public function getById(int $id): array|false
    {
        return $this->query(
            "SELECT r.*, c.brand, c.model, c.year, c.price_per_day, c.transmission, c.engine_type, c.passenger_capacity, c.luggage_capacity,
                    u.name AS user_name, u.email AS user_email, u.nik AS user_nik, u.phone AS user_phone, u.address AS user_address
             FROM rentals r
             JOIN cars c ON r.car_id = c.id
             JOIN users u ON r.user_id = u.id
             WHERE r.id = ?",
            [$id]
        )->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Ambil semua rental (untuk halaman admin)
     *
     * @param string $status Filter status (kosong = semua)
     * @return array Daftar semua rental dengan info mobil dan user
     */
    public function getAll(string $status = ''): array
    {
        if (!empty($status)) {
            if ($status === 'cancelled') {
                return $this->query(
                    "SELECT r.*, c.brand, c.model, c.year, c.transmission, c.engine_type, c.passenger_capacity, c.luggage_capacity, c.price_per_day,
                            u.name AS user_name, u.email AS user_email, u.nik AS user_nik, u.phone AS user_phone, u.address AS user_address
                     FROM rentals r
                     JOIN cars c ON r.car_id = c.id
                     JOIN users u ON r.user_id = u.id
                     WHERE r.status IN ('cancelled', 'cancel_requested')
                     ORDER BY r.created_at DESC"
                )->fetchAll(PDO::FETCH_ASSOC);
            }
            return $this->query(
                "SELECT r.*, c.brand, c.model, c.year, c.transmission, c.engine_type, c.passenger_capacity, c.luggage_capacity, c.price_per_day,
                        u.name AS user_name, u.email AS user_email, u.nik AS user_nik, u.phone AS user_phone, u.address AS user_address
                 FROM rentals r
                 JOIN cars c ON r.car_id = c.id
                 JOIN users u ON r.user_id = u.id
                 WHERE r.status = ?
                 ORDER BY r.created_at DESC",
                [$status]
            )->fetchAll(PDO::FETCH_ASSOC);
        }
        return $this->query(
            "SELECT r.*, c.brand, c.model, c.year, c.transmission, c.engine_type, c.passenger_capacity, c.luggage_capacity, c.price_per_day,
                    u.name AS user_name, u.email AS user_email, u.nik AS user_nik, u.phone AS user_phone, u.address AS user_address
             FROM rentals r
             JOIN cars c ON r.car_id = c.id
             JOIN users u ON r.user_id = u.id
             ORDER BY r.created_at DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update status rental
     *
     * @param int    $id     ID rental
     * @param string $status Status baru
     * @return bool True jika berhasil
     */
    public function updateStatus(int $id, string $status): bool
    {
        return $this->query(
            "UPDATE rentals SET status = ? WHERE id = ?",
            [$status, $id]
        )->rowCount() > 0;
    }

    /**
     * Cek apakah mobil sudah dibooking pada rentang tanggal tertentu
     * (untuk mencegah double booking)
     *
     * @param int    $carId     ID mobil
     * @param string $startDate Tanggal mulai (YYYY-MM-DD)
     * @param string $endDate   Tanggal selesai (YYYY-MM-DD)
     * @param int    $excludeId ID rental yang dikecualikan (saat edit, default 0)
     * @return bool True jika ada konflik tanggal
     */
    public function isCarBooked(int $carId, string $startDate, string $endDate, int $excludeId = 0): bool
    {
        $count = (int) $this->query(
            "SELECT COUNT(*) FROM rentals
             WHERE car_id = ?
               AND id != ?
               AND status NOT IN ('cancelled')
               AND start_date < ?
               AND (CASE WHEN status = 'completed' THEN DATE(COALESCE(actual_return_at, end_date)) ELSE end_date END) > ?",
             [$carId, $excludeId, $endDate, $startDate]
        )->fetchColumn();

        return $count > 0;
    }

    /**
     * Hitung total rental berdasarkan status (untuk dashboard admin)
     *
     * @param string $status Filter status (kosong = semua)
     * @return int Jumlah rental
     */
    public function countByStatus(string $status = ''): int
    {
        if (!empty($status)) {
            if ($status === 'cancelled') {
                return (int) $this->query(
                    "SELECT COUNT(*) FROM rentals WHERE status IN ('cancelled', 'cancel_requested')"
                )->fetchColumn();
            }
            return (int) $this->query(
                "SELECT COUNT(*) FROM rentals WHERE status = ?",
                [$status]
            )->fetchColumn();
        }
        return (int) $this->query("SELECT COUNT(*) FROM rentals")->fetchColumn();
    }

    // -------------------------------------------------------
    // METHOD DENDA KETERLAMBATAN
    // -------------------------------------------------------

    // Tarif denda per jam keterlambatan
    const PENALTY_RATE_PER_HOUR = 20000;

    /**
     * Mulai masa sewa — catat waktu mulai (started_at) dan ubah status ke ongoing
     * Dipanggil saat admin klik "Mulai Sewa (Mobil Diambil)"
     *
     * @param int $id ID rental
     * @return bool True jika berhasil
     */
    public function startRental(int $id): bool
    {
        return $this->query(
            "UPDATE rentals SET status = 'ongoing', started_at = NOW() WHERE id = ?",
            [$id]
        )->rowCount() > 0;
    }

    /**
     * Hitung denda keterlambatan berdasarkan started_at dan jumlah hari sewa
     *
     * Deadline = started_at + (jumlah_hari × 24 jam)
     * Grace period: 30 menit setelah deadline → tidak kena denda.
     * Jika lewat 30 menit, denda dihitung per jam dengan pembulatan:
     *   penalty_hours = ceil((late_minutes - 30) / 60)
     *
     * Contoh (deadline 08:00):
     *   08:00 – 08:30 → tidak kena denda (grace period)
     *   08:31 – 09:30 → denda 1 jam
     *   09:31 – 10:30 → denda 2 jam
     *
     * @param array $rental Data rental (harus mengandung started_at, start_date, end_date)
     * @return array ['deadline' => string, 'late_hours' => int, 'penalty_fee' => float, 'is_overdue' => bool]
     */
    public function calculatePenalty(array $rental): array
    {
        $result = [
            'deadline'    => null,
            'late_hours'  => 0,
            'penalty_fee' => 0,
            'is_overdue'  => false,
        ];

        // Jika belum dimulai, tidak ada denda
        if (empty($rental['started_at'])) {
            return $result;
        }

        // Hitung jumlah hari sewa
        $days = (int) ((strtotime($rental['end_date']) - strtotime($rental['start_date'])) / 86400);
        if ($days < 1) $days = 1;

        // Hitung deadline: started_at + (hari × 24 jam)
        $startedAt    = strtotime($rental['started_at']);
        $deadlineTime = $startedAt + ($days * 86400);
        $result['deadline'] = date('Y-m-d H:i:s', $deadlineTime);

        // Bandingkan dengan waktu sekarang
        $now = time();
        if ($now > $deadlineTime) {
            // Hitung menit keterlambatan
            $lateMinutes = ($now - $deadlineTime) / 60;

            if ($lateMinutes <= 30) {
                // Grace period 30 menit — tidak kena denda
                // is_overdue tetap false, late_hours dan penalty_fee tetap 0
            } else {
                // Lewat grace period — hitung denda per jam, bulatkan ke atas
                $result['is_overdue'] = true;
                $lateHours = (int) ceil(($lateMinutes - 30) / 60);

                // Ambil biaya denda per jam dari settings database
                $stmt = $this->db->prepare("SELECT value FROM settings WHERE key_name = 'penalty_fee_per_hour'");
                $stmt->execute();
                $rate = (float) $stmt->fetchColumn();
                if ($rate <= 0) {
                    $rate = 20000.0; // fallback jika setting tidak ditemukan
                }

                $result['late_hours']  = $lateHours;
                $result['penalty_fee'] = $lateHours * $rate;
            }
        }

        return $result;
    }

    /**
     * Selesaikan rental — catat waktu pengembalian dan denda
     * Dipanggil saat admin klik "Selesaikan Sewa (Mobil Kembali)"
     *
     * @param int   $id         ID rental
     * @param float $penaltyFee Total denda keterlambatan
     * @return bool True jika berhasil
     */
    public function completeRental(int $id, float $penaltyFee = 0): bool
    {
        return $this->query(
            "UPDATE rentals
             SET status = 'completed',
                 actual_return_at = NOW(),
                 penalty_fee = ?
             WHERE id = ?",
            [$penaltyFee, $id]
        )->rowCount() > 0;
    }

    /**
     * Hitung total pendapatan bulan ini (dari rental completed)
     * Berdasarkan created_at rental (bukan payment) karena payment bisa null
     *
     * @return float Total pendapatan bulan ini
     */
    public function getPendapatanBulanIni(): float
    {
        $result = $this->query(
            "SELECT COALESCE(SUM(total_price + COALESCE(penalty_fee, 0)), 0)
             FROM rentals
             WHERE status = 'completed'
               AND MONTH(created_at) = MONTH(CURDATE())
               AND YEAR(created_at) = YEAR(CURDATE())"
        )->fetchColumn();
        return (float) $result;
    }

    /**
     * Hitung total pendapatan keseluruhan (dari rental completed)
     *
     * @return float Total pendapatan semua waktu
     */
    public function getTotalPendapatan(): float
    {
        $result = $this->query(
            "SELECT COALESCE(SUM(total_price + COALESCE(penalty_fee, 0)), 0)
             FROM rentals
             WHERE status = 'completed'"
        )->fetchColumn();
        return (float) $result;
    }

    /**
     * Ambil data tren jumlah rental per bulan (6 bulan terakhir)
     *
     * @return array Array dengan label bulan dan jumlah rental
     */
    public function getTrenRental6Bulan(): array
    {
        return $this->query(
            "SELECT
                DATE_FORMAT(created_at, '%b %Y') AS bulan,
                DATE_FORMAT(created_at, '%Y-%m') AS bulan_sort,
                COUNT(*) AS jumlah
             FROM rentals
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
               AND created_at < DATE_ADD(LAST_DAY(CURDATE()), INTERVAL 1 DAY)
             GROUP BY bulan_sort, bulan
             ORDER BY bulan_sort ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ambil data pendapatan per bulan dari rental completed (6 bulan terakhir)
     *
     * @return array Array dengan label bulan dan total pendapatan
     */
    public function getPendapatan6Bulan(): array
    {
        return $this->query(
            "SELECT
                DATE_FORMAT(created_at, '%b %Y') AS bulan,
                DATE_FORMAT(created_at, '%Y-%m') AS bulan_sort,
                COALESCE(SUM(total_price + COALESCE(penalty_fee, 0)), 0) AS total
             FROM rentals
             WHERE status = 'completed'
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
               AND created_at < DATE_ADD(LAST_DAY(CURDATE()), INTERVAL 1 DAY)
             GROUP BY bulan_sort, bulan
             ORDER BY bulan_sort ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ambil transaksi terbaru untuk ditampilkan di dashboard admin
     *
     * @param int $limit Jumlah data yang diambil
     * @return array Daftar rental terbaru dengan info user, mobil, dan pembayaran
     */
    public function getRecentTransaksi(int $limit = 8): array
    {
        $stmt = $this->db->prepare(
            "SELECT r.id, r.total_price, r.status, r.created_at,
                    r.start_date, r.end_date, r.rental_type,
                    u.name AS user_name,
                    c.brand, c.model,
                    p.method AS payment_method,
                    p.status AS payment_status
             FROM rentals r
             JOIN users u ON r.user_id = u.id
             JOIN cars c ON r.car_id = c.id
             LEFT JOIN payments p ON p.rental_id = r.id
             ORDER BY r.created_at DESC
             LIMIT :lmt"
        );
        $stmt->bindValue(':lmt', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Hitung persentase pertumbuhan rental bulan ini vs bulan lalu
     *
     * @return float Persentase pertumbuhan (bisa negatif)
     */
    public function getPertumbuhanBulanan(): float
    {
        $bulanIni = (int) $this->query(
            "SELECT COUNT(*) FROM rentals
             WHERE MONTH(created_at) = MONTH(CURDATE())
               AND YEAR(created_at) = YEAR(CURDATE())"
        )->fetchColumn();

        $bulanLalu = (int) $this->query(
            "SELECT COUNT(*) FROM rentals
             WHERE MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
               AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))"
        )->fetchColumn();

        if ($bulanLalu === 0) {
            return $bulanIni > 0 ? 100.0 : 0.0;
        }

        return round((($bulanIni - $bulanLalu) / $bulanLalu) * 100, 1);
    }

    // -------------------------------------------------------
    // METHOD UNTUK HALAMAN PENGEMBALIAN
    // -------------------------------------------------------

    /**
     * Ambil semua rental yang sedang berjalan (ongoing) untuk halaman pengembalian
     *
     * @return array Daftar rental ongoing dengan info mobil dan user
     */
    public function getOngoingRentals(): array
    {
        return $this->query(
            "SELECT r.*, c.brand, c.model, c.year, c.image,
                    u.name AS user_name, u.email AS user_email, u.phone AS user_phone
             FROM rentals r
             JOIN cars c ON r.car_id = c.id
             JOIN users u ON r.user_id = u.id
             WHERE r.status = 'ongoing'
             ORDER BY r.started_at ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ambil rental yang baru saja selesai (completed) untuk riwayat pengembalian
     *
     * @param int $limit Jumlah data
     * @return array Daftar rental completed terbaru
     */
    public function getRecentlyCompleted(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT r.*, c.brand, c.model, c.year,
                    u.name AS user_name, u.email AS user_email, u.phone AS user_phone
             FROM rentals r
             JOIN cars c ON r.car_id = c.id
             JOIN users u ON r.user_id = u.id
             WHERE r.status = 'completed'
             ORDER BY r.actual_return_at DESC
             LIMIT :lmt"
        );
        $stmt->bindValue(':lmt', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Hitung total denda yang sudah terkumpul
     *
     * @return float Total denda dari semua rental completed
     */
    public function getTotalPenalty(): float
    {
        $result = $this->query(
            "SELECT COALESCE(SUM(penalty_fee), 0) FROM rentals WHERE status = 'completed' AND penalty_fee > 0"
        )->fetchColumn();
        return (float) $result;
    }

    // -------------------------------------------------------
    // METHOD UNTUK HALAMAN LAPORAN
    // -------------------------------------------------------

    /**
     * Ambil data laporan rental berdasarkan rentang tanggal
     *
     * @param string $startDate Tanggal awal (YYYY-MM-DD)
     * @param string $endDate   Tanggal akhir (YYYY-MM-DD)
     * @param string $status    Filter status (kosong = semua)
     * @return array Daftar rental dalam periode tersebut
     */
    public function getReportData(string $startDate = '', string $endDate = '', string $status = ''): array
    {
        $sql = "SELECT r.*, c.brand, c.model, c.year,
                       u.name AS user_name, u.email AS user_email, u.phone AS user_phone
                FROM rentals r
                JOIN cars c ON r.car_id = c.id
                JOIN users u ON r.user_id = u.id
                WHERE 1=1";
        $params = [];

        if (!empty($startDate)) {
            $sql .= " AND DATE(r.created_at) >= ?";
            $params[] = $startDate;
        }
        if (!empty($endDate)) {
            $sql .= " AND DATE(r.created_at) <= ?";
            $params[] = $endDate;
        }
        if (!empty($status)) {
            $sql .= " AND r.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY r.created_at DESC";

        return $this->query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ambil ringkasan statistik laporan dalam rentang tanggal
     *
     * @param string $startDate Tanggal awal
     * @param string $endDate   Tanggal akhir
     * @return array Statistik: total_transaksi, total_completed, total_pendapatan, total_denda, rata_rata_hari
     */
    public function getReportStats(string $startDate = '', string $endDate = ''): array
    {
        $where = "WHERE 1=1";
        $params = [];
        if (!empty($startDate)) { $where .= " AND DATE(r.created_at) >= ?"; $params[] = $startDate; }
        if (!empty($endDate))   { $where .= " AND DATE(r.created_at) <= ?"; $params[] = $endDate; }

        $row = $this->query(
            "SELECT COUNT(*) AS total_transaksi,
                    SUM(CASE WHEN r.status = 'completed' THEN 1 ELSE 0 END) AS total_completed,
                    COALESCE(SUM(CASE WHEN r.status = 'completed' THEN r.total_price + COALESCE(r.penalty_fee,0) ELSE 0 END), 0) AS total_pendapatan,
                    COALESCE(SUM(CASE WHEN r.status = 'completed' THEN COALESCE(r.penalty_fee,0) ELSE 0 END), 0) AS total_denda,
                    COALESCE(AVG(DATEDIFF(r.end_date, r.start_date)), 0) AS rata_rata_hari
             FROM rentals r $where", $params
        )->fetch(PDO::FETCH_ASSOC);

        return $row ?: ['total_transaksi'=>0,'total_completed'=>0,'total_pendapatan'=>0,'total_denda'=>0,'rata_rata_hari'=>0];
    }
}
