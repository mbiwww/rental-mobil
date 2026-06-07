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
            "SELECT r.*, c.brand, c.model, c.year, c.image
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
               AND end_date > ?",
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
     * Jika waktu sekarang melewati deadline, denda dihitung per jam.
     * Pembulatan: >= 30 menit dibulatkan ke atas (round half up).
     * Minimum 1 jam jika terlambat.
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
            $result['is_overdue'] = true;

            // Hitung menit keterlambatan
            $lateMinutes = ($now - $deadlineTime) / 60;

            // Pembulatan: >= 30 menit bulatkan ke atas, minimum 1 jam
            $lateHours = max(1, (int) round($lateMinutes / 60));

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
}
