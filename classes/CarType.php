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

    /**
     * Menambahkan kategori baru ke database
     * 
     * @param string $name Nama kategori
     * @return bool True jika berhasil, false jika gagal (termasuk duplicate)
     */
    public function create(string $name): bool
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO car_types (name) VALUES (?)");
            return $stmt->execute([trim($name)]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Memperbarui nama kategori berdasarkan ID
     * 
     * @param int    $id   ID kategori
     * @param string $name Nama baru kategori
     * @return bool True jika berhasil, false jika gagal
     */
    public function update(int $id, string $name): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE car_types SET name = ? WHERE id = ?");
            return $stmt->execute([trim($name), $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Menghapus kategori berdasarkan ID
     * 
     * @param int $id ID kategori
     * @return bool True jika berhasil, false jika gagal
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM car_types WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Menghitung jumlah mobil yang menggunakan kategori tertentu
     * Digunakan untuk mencegah penghapusan kategori yang masih dipakai
     * 
     * @param int $typeId ID kategori
     * @return int Jumlah mobil yang menggunakan kategori ini
     */
    public function countCarsInType(int $typeId): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM cars WHERE type_id = ?");
            $stmt->execute([$typeId]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
}
