<?php
/**
 * Class User — Model untuk mengelola data user (customer & admin)
 *
 * Menangani semua operasi database yang berkaitan dengan tabel `users`:
 * - Login, register, update profil, ganti password, reset password
 * - Semua query menggunakan prepared statement PDO
 */
class User
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
     * Mencari user berdasarkan email
     *
     * @param string $email Email user
     * @return array|false Data user atau false jika tidak ditemukan
     */
    public function findByEmail(string $email): array|false
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([trim($email)]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Mencari user berdasarkan NIK
     *
     * @param string $nik NIK user
     * @return array|false Data user atau false jika tidak ditemukan
     */
    public function findByNik(string $nik): array|false
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE nik = ? LIMIT 1");
            $stmt->execute([trim($nik)]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Mencari user berdasarkan ID
     *
     * @param int $id ID user
     * @return array|false Data user atau false jika tidak ditemukan
     */
    public function findById(int $id): array|false
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Mendaftarkan user baru ke database
     * Password harus sudah di-hash sebelum dikirim ke method ini.
     *
     * @param array $data Data user (name, nik, email, phone, address, password, role)
     * @return bool True jika berhasil, false jika gagal
     */
    public function create(array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO users (name, nik, email, phone, address, password, role)
                VALUES (:name, :nik, :email, :phone, :address, :password, :role)
            ");
            return $stmt->execute([
                'name'     => $data['name'],
                'nik'      => $data['nik'],
                'email'    => $data['email'],
                'phone'    => $data['phone'],
                'address'  => $data['address'],
                'password' => $data['password'],
                'role'     => $data['role'] ?? 'customer',
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Memperbarui data profil user (tidak termasuk password)
     *
     * @param int   $id   ID user
     * @param array $data Data baru (name, nik, email, phone, address)
     * @return bool True jika berhasil, false jika gagal
     */
    public function updateProfile(int $id, array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE users
                SET name = :name, nik = :nik, email = :email, phone = :phone, address = :address
                WHERE id = :id
            ");
            return $stmt->execute([
                'name'    => $data['name'],
                'nik'     => $data['nik'],
                'email'   => $data['email'],
                'phone'   => $data['phone'],
                'address' => $data['address'],
                'id'      => $id,
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Memperbarui password user
     * Password harus sudah di-hash sebelum dikirim ke method ini.
     *
     * @param int    $id             ID user
     * @param string $hashedPassword Password baru yang sudah di-hash
     * @return bool True jika berhasil, false jika gagal
     */
    public function updatePassword(int $id, string $hashedPassword): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
            return $stmt->execute([$hashedPassword, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Verifikasi identitas user untuk fitur reset password.
     * Memeriksa kecocokan 4 data: nama, NIK, email, dan no. telepon sekaligus.
     * Tidak memerlukan kolom tambahan di tabel users.
     *
     * @param string $name  Nama lengkap
     * @param string $nik   NIK
     * @param string $email Email
     * @param string $phone No. telepon
     * @return array|false Data user jika semua cocok, false jika tidak
     */
    public function verifyIdentity(string $name, string $nik, string $email, string $phone): array|false
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM users
                WHERE name = :name AND nik = :nik AND email = :email AND phone = :phone
                LIMIT 1
            ");
            $stmt->execute([
                'name'  => trim($name),
                'nik'   => trim($nik),
                'email' => trim($email),
                'phone' => trim($phone),
            ]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Mengambil semua data customer (bukan admin) untuk halaman admin
     *
     * @return array Daftar semua customer
     */
    public function getAllCustomers(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT id, name, nik, email, phone, address, created_at
                FROM users
                WHERE role = 'customer'
                ORDER BY created_at DESC
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Menghitung total customer terdaftar
     *
     * @return int Jumlah customer
     */
    public function countCustomers(): int
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
}
