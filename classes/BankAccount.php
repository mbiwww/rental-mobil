<?php
/**
 * Class BankAccount — Model untuk mengelola data akun rekening transfer admin
 *
 * Menangani semua operasi database pada tabel bank_accounts menggunakan
 * Prepared Statement PDO. Extends BaseModel.
 */
class BankAccount extends BaseModel
{
    /**
     * Ambil semua data rekening bank
     *
     * @return array Daftar semua rekening bank
     */
    public function getAll(): array
    {
        return $this->query("SELECT * FROM bank_accounts ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ambil rekening bank yang aktif saja
     *
     * @return array Daftar rekening bank aktif
     */
    public function getActive(): array
    {
        return $this->query("SELECT * FROM bank_accounts WHERE is_active = 1 ORDER BY bank_name ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ambil satu rekening bank berdasarkan ID
     *
     * @param int $id ID rekening
     * @return array|false Data rekening atau false jika tidak ditemukan
     */
    public function getById(int $id): array|false
    {
        return $this->query("SELECT * FROM bank_accounts WHERE id = ?", [$id])->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Tambah rekening bank baru
     *
     * @param array $data Data rekening baru (bank_name, account_number, account_holder, is_active)
     * @return bool True jika berhasil
     */
    public function create(array $data): bool
    {
        return $this->query(
            "INSERT INTO bank_accounts (bank_name, account_number, account_holder, is_active) VALUES (?, ?, ?, ?)",
            [
                $data['bank_name'],
                $data['account_number'],
                $data['account_holder'],
                isset($data['is_active']) ? (int)$data['is_active'] : 1
            ]
        )->rowCount() > 0;
    }

    /**
     * Update data rekening bank
     *
     * @param int   $id   ID rekening
     * @param array $data Data baru
     * @return bool True jika berhasil
     */
    public function update(int $id, array $data): bool
    {
        return $this->query(
            "UPDATE bank_accounts SET bank_name = ?, account_number = ?, account_holder = ?, is_active = ? WHERE id = ?",
            [
                $data['bank_name'],
                $data['account_number'],
                $data['account_holder'],
                (int)$data['is_active'],
                $id
            ]
        )->rowCount() > 0;
    }

    /**
     * Toggle status aktif/nonaktif rekening bank
     *
     * @param int $id ID rekening
     * @return bool True jika berhasil
     */
    public function toggleStatus(int $id): bool
    {
        return $this->query("UPDATE bank_accounts SET is_active = 1 - is_active WHERE id = ?", [$id])->rowCount() > 0;
    }

    /**
     * Hapus rekening bank
     *
     * @param int $id ID rekening
     * @return bool True jika berhasil
     */
    public function delete(int $id): bool
    {
        return $this->query("DELETE FROM bank_accounts WHERE id = ?", [$id])->rowCount() > 0;
    }
}
