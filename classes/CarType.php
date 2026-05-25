<?php
/**
 * Class CarType — Model untuk mengelola kategori mobil
 * 
 * Class ini menangani pengambilan data tipe/kategori mobil (seperti SUV, Sedan, MPV, dll.)
 * dari database menggunakan PDO.
 */
class CarType
{
    // Koneksi database
    private PDO $db;

    /**
     * Constructor — Menginjeksi koneksi database
     * 
     * @param PDO $db Instance dari koneksi PDO
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Mengambil semua kategori/tipe mobil
     * 
     * @return array Daftar semua kategori mobil
     */
    public function getAll(): array
    {
        try {
            $stmt = $this->db->query("SELECT * FROM car_types ORDER BY name ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            // Log error jika diperlukan, untuk sekarang return array kosong
            return [];
        }
    }

    /**
     * Mendapatkan kategori mobil berdasarkan ID
     * 
     * @param int $id ID kategori
     * @return array|false Data kategori atau false jika tidak ditemukan
     */
    public function getById(int $id): array|false
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM car_types WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
}
