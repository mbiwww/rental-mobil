<?php
/**
 * Class Setting — Model untuk mengelola konfigurasi/settings
 *
 * Menangani semua operasi database pada tabel settings menggunakan
 * Prepared Statement PDO. Extends BaseModel.
 */
class Setting extends BaseModel
{
    /**
     * Ambil semua data settings
     *
     * @return array Daftar settings
     */
    public function getAll(): array
    {
        return $this->query("SELECT * FROM settings ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ambil setting berdasarkan key_name
     *
     * @param string $key Kunci setting
     * @return array|false Data setting atau false jika tidak ditemukan
     */
    public function getByKey(string $key): array|false
    {
        return $this->query("SELECT * FROM settings WHERE key_name = ?", [$key])->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Ambil nilai value (float) berdasarkan key_name secara langsung
     *
     * @param string $key Kunci setting
     * @param float $default Nilai default jika key tidak ditemukan
     * @return float Nilai value dari setting
     */
    public function getValueByKey(string $key, float $default = 0.0): float
    {
        $setting = $this->getByKey($key);
        return $setting ? (float) $setting['value'] : $default;
    }

    /**
     * Update nilai setting berdasarkan key_name
     *
     * @param string $key Kunci setting
     * @param float $value Nilai baru
     * @return bool True jika berhasil
     */
    public function updateByKey(string $key, float $value): bool
    {
        return $this->query("UPDATE settings SET value = ? WHERE key_name = ?", [$value, $key])->rowCount() > 0;
    }
}
