<?php
/**
 * Class Payment — Model untuk mengelola data pembayaran
 *
 * Menangani semua operasi database pada tabel payments.
 * Extends BaseModel untuk mewarisi koneksi database.
 */
class Payment extends BaseModel
{
    // Tidak perlu constructor — diwarisi dari BaseModel

    /**
     * Ambil pembayaran berdasarkan rental ID beserta informasi rekening bank
     *
     * @param int $rentalId ID Rental
     * @return array|false Data pembayaran beserta detail bank account
     */
    public function getByRentalId(int $rentalId): array|false
    {
        return $this->query(
            "SELECT p.*, b.bank_name, b.account_number, b.account_holder
             FROM payments p
             LEFT JOIN bank_accounts b ON p.bank_account_id = b.id
             WHERE p.rental_id = ?",
            [$rentalId]
        )->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update status pembayaran
     *
     * @param int    $rentalId ID Rental
     * @param string $status   Status baru ('pending', 'confirmed', 'rejected')
     * @return bool True jika berhasil diperbarui
     */
    public function updateStatus(int $rentalId, string $status): bool
    {
        $paidAt = ($status === 'confirmed') ? date('Y-m-d H:i:s') : null;
        return $this->query(
            "UPDATE payments SET status = ?, paid_at = ? WHERE rental_id = ?",
            [$status, $paidAt, $rentalId]
        )->rowCount() > 0;
    }

    /**
     * Buat data pembayaran baru (biasanya dipanggil saat customer klik bayar)
     *
     * @param array $data Data pembayaran
     * @return int ID pembayaran yang baru dibuat
     */
    public function create(array $data): int
    {
        $this->query(
            "INSERT INTO payments (rental_id, method, bank_account_id, proof_image, status)
             VALUES (?, ?, ?, ?, 'pending')",
            [
                $data['rental_id'],
                $data['method'],
                $data['bank_account_id'] ?: null,
                $data['proof_image'] ?: null
            ]
        );
        return (int) $this->db->lastInsertId();
    }
}
