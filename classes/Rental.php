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
                total_price, rental_type, driver_cost, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')",
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
            "SELECT r.*, c.brand, c.model, c.year, c.price_per_day,
                    u.name AS user_name, u.email AS user_email
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
     * @return array Daftar semua rental dengan info mobil dan user
     */
    public function getAll(): array
    {
        return $this->query(
            "SELECT r.*, c.brand, c.model, c.year,
                    u.name AS user_name, u.email AS user_email
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
            return (int) $this->query(
                "SELECT COUNT(*) FROM rentals WHERE status = ?",
                [$status]
            )->fetchColumn();
        }
        return (int) $this->query("SELECT COUNT(*) FROM rentals")->fetchColumn();
    }
}
