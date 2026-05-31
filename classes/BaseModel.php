<?php
/**
 * Class BaseModel — Parent class semua model
 *
 * Menyediakan koneksi database PDO dan method query umum
 * yang diwarisi oleh semua class model di folder classes/.
 */
class BaseModel
{
    // Koneksi database, bisa diakses oleh child class
    protected PDO $db;

    /**
     * Constructor — Menginjeksi koneksi PDO
     * Diwarisi oleh semua child class, tidak perlu ditulis ulang.
     *
     * @param PDO $db Instance koneksi PDO
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Method query umum dengan prepared statement
     * Bisa dipakai langsung oleh semua child class.
     *
     * @param string $sql    Query SQL dengan placeholder (?)
     * @param array  $params Parameter untuk binding
     * @return PDOStatement  Statement yang sudah dieksekusi
     */
    protected function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
