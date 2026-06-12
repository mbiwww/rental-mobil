<?php
/**
 * Class Car — Model untuk mengelola armada mobil
 * 
 * Class ini menangani semua query dan manipulasi data mobil (CRUD)
 * di database menggunakan Prepared Statement PDO.
 */
class Car
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
     * Mengambil semua data mobil dengan filter pencarian dan status
     * 
     * @param string $search Kata kunci pencarian (brand atau model)
     * @param string $status Filter status (available, rented, maintenance)
     * @return array Daftar mobil yang sesuai kriteria
     */
    public function getAll(string $search = '', string $status = ''): array
    {
        try {
            $sql = "SELECT c.*, t.name AS type_name 
                    FROM cars c 
                    LEFT JOIN car_types t ON c.type_id = t.id 
                    WHERE 1=1";
            $params = [];

            if (!empty($search)) {
                $sql .= " AND (c.brand LIKE :search OR c.model LIKE :search OR t.name LIKE :search)";
                $params['search'] = '%' . $search . '%';
            }

            if (!empty($status)) {
                $sql .= " AND c.status = :status";
                $params['status'] = $status;
            }

            $sql .= " ORDER BY c.id DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Mengambil detail satu mobil berdasarkan ID
     * 
     * @param int $id ID mobil
     * @return array|false Data detail mobil atau false jika tidak ditemukan
     */
    public function getById(int $id): array|false
    {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, t.name AS type_name 
                FROM cars c 
                LEFT JOIN car_types t ON c.type_id = t.id 
                WHERE c.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Menambahkan mobil baru ke database
     * 
     * @param array $data Assosiative array berisi data mobil
     * @return bool True jika berhasil, false jika gagal
     */
    public function create(array $data): bool
    {
        try {
            $sql = "INSERT INTO cars (
                        type_id, plate_number, brand, model, year, engine_type, 
                        transmission, passenger_capacity, luggage_capacity, 
                        price_per_day, status, image, description
                    ) VALUES (
                        :type_id, :plate_number, :brand, :model, :year, :engine_type, 
                        :transmission, :passenger_capacity, :luggage_capacity, 
                        :price_per_day, :status, :image, :description
                    )";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'type_id'            => $data['type_id'] ?: null,
                'plate_number'       => $data['plate_number'] ?: null,
                'brand'              => $data['brand'],
                'model'              => $data['model'],
                'year'               => $data['year'],
                'engine_type'        => $data['engine_type'] ?: null,
                'transmission'       => $data['transmission'],
                'passenger_capacity' => $data['passenger_capacity'],
                'luggage_capacity'   => $data['luggage_capacity'],
                'price_per_day'      => $data['price_per_day'],
                'status'             => $data['status'] ?: 'available',
                'image'              => $data['image'] ?: null,
                'description'        => $data['description'] ?: null
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Memperbarui data mobil
     * 
     * @param int $id ID mobil yang akan diperbarui
     * @param array $data Assosiative array berisi data baru mobil
     * @return bool True jika berhasil, false jika gagal
     */
    public function update(int $id, array $data): bool
    {
        try {
            $sql = "UPDATE cars SET 
                        type_id = :type_id, 
                        plate_number = :plate_number, 
                        brand = :brand, 
                        model = :model, 
                        year = :year, 
                        engine_type = :engine_type, 
                        transmission = :transmission, 
                        passenger_capacity = :passenger_capacity, 
                        luggage_capacity = :luggage_capacity, 
                        price_per_day = :price_per_day, 
                        status = :status, 
                        description = :description";
            
            $params = [
                'type_id'            => $data['type_id'] ?: null,
                'plate_number'       => $data['plate_number'] ?: null,
                'brand'              => $data['brand'],
                'model'              => $data['model'],
                'year'               => $data['year'],
                'engine_type'        => $data['engine_type'] ?: null,
                'transmission'       => $data['transmission'],
                'passenger_capacity' => $data['passenger_capacity'],
                'luggage_capacity'   => $data['luggage_capacity'],
                'price_per_day'      => $data['price_per_day'],
                'status'             => $data['status'],
                'description'        => $data['description'] ?: null,
                'id'                 => $id
            ];

            // Update gambar hanya jika ada gambar baru yang disuplai
            if (isset($data['image']) && $data['image'] !== null) {
                $sql .= ", image = :image";
                $params['image'] = $data['image'];
            }

            $sql .= " WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Menghapus data mobil dari database
     * 
     * @param int $id ID mobil yang akan dihapus
     * @return bool True jika berhasil, false jika gagal
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM cars WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Memperbarui status mobil secara spesifik
     * 
     * @param int $id ID mobil
     * @param string $status Status baru (available, rented, maintenance)
     * @return bool True jika berhasil, false jika gagal
     */
    public function updateStatus(int $id, string $status): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE cars SET status = ? WHERE id = ?");
            return $stmt->execute([$status, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Mengambil daftar mobil unggulan (status available, dibatasi jumlahnya)
     * Digunakan untuk menampilkan mobil di landing page.
     *
     * @param int $limit Jumlah maksimal mobil yang diambil (default 6)
     * @return array Daftar mobil unggulan
     */
    public function getFeatured(int $limit = 6): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, t.name AS type_name
                FROM cars c
                LEFT JOIN car_types t ON c.type_id = t.id
                WHERE c.status = 'available'
                ORDER BY c.created_at DESC
                LIMIT :lmt
            ");
            $stmt->bindValue(':lmt', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Menghitung total mobil berdasarkan status
     *
     * @param string $status Filter status (kosong = semua)
     * @return int Jumlah mobil
     */
    public function countByStatus(string $status = ''): int
    {
        try {
            if (!empty($status)) {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM cars WHERE status = ?");
                $stmt->execute([$status]);
            } else {
                $stmt = $this->db->query("SELECT COUNT(*) FROM cars");
            }
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Mengambil daftar mobil dengan kombinasi filter server-side
     * Digunakan di halaman katalog.
     *
     * @param string      $search       Kata kunci pencarian (brand / model)
     * @param int|null    $typeId       ID kategori mobil (null = semua)
     * @param string      $transmission Filter transmisi: 'manual' | 'automatic' | ''
     * @param string      $status       Filter status: 'available' | 'rented' | 'maintenance' | ''
     * @return array Daftar mobil yang sesuai filter
     */
    public function getFiltered(
        string $search = '',
        ?int   $typeId = null,
        string $transmission = '',
        string $status = ''
    ): array {
        try {
            $sql    = "SELECT c.*, t.name AS type_name
                       FROM cars c
                       LEFT JOIN car_types t ON c.type_id = t.id
                       WHERE 1=1";
            $params = [];

            // Filter pencarian teks: brand, model, atau gabungan keduanya (nama lengkap)
            if (!empty($search)) {
                $sql .= " AND (
                    c.brand LIKE :search
                    OR c.model LIKE :search
                    OR CONCAT(c.brand, ' ', c.model) LIKE :search
                    OR CONCAT(c.brand, ' ', c.model, ' ', c.year) LIKE :search
                )";
                $params['search'] = '%' . $search . '%';
            }

            // Filter tipe/kategori
            if ($typeId !== null) {
                $sql .= " AND c.type_id = :type_id";
                $params['type_id'] = $typeId;
            }

            // Filter transmisi
            if (!empty($transmission)) {
                $sql .= " AND c.transmission = :transmission";
                $params['transmission'] = $transmission;
            }

            // Filter status
            if (!empty($status)) {
                $sql .= " AND c.status = :status";
                $params['status'] = $status;
            }

            $sql .= " ORDER BY c.status ASC, c.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}

