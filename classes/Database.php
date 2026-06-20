<?php
/**
 * Class Database — Singleton PDO Connection
 * 
 * Mengelola koneksi database menggunakan pola Singleton
 * agar hanya ada 1 instance koneksi PDO selama siklus request.
 */

// Set timezone to UTC+7 (Asia/Jakarta)
date_default_timezone_set('Asia/Jakarta');

class Database
{
    // Instance tunggal dari class ini
    private static ?Database $instance = null;

    // Instance koneksi PDO
    private PDO $pdo;

    // Konfigurasi database
    private string $host    = 'localhost';
    private string $db      = 'car_rental';
    private string $user    = 'root';
    private string $pass    = '';
    private string $charset = 'utf8mb4';

    /**
     * Constructor — private agar tidak bisa di-instantiate dari luar
     * Membuat koneksi PDO saat pertama kali dipanggil
     */
    private function __construct()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,    // Lempar exception saat error
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // Hasil query array asosiatif
            PDO::ATTR_EMULATE_PREPARES   => false,                     // Gunakan prepared statement asli MySQL
        ];

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }

    /**
     * Mencegah cloning instance
     */
    private function __clone() {}

    /**
     * Mencegah unserialization instance
     */
    public function __wakeup()
    {
        throw new \Exception("Tidak bisa unserialize singleton Database.");
    }

    /**
     * Mendapatkan instance tunggal dari class Database
     * 
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Mendapatkan instance PDO untuk digunakan di class lain
     * 
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
