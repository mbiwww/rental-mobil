<?php
/**
 * Konfigurasi koneksi database PDO
 * 
 * File ini berisi pengaturan koneksi ke database MySQL
 * menggunakan PDO (PHP Data Objects).
 */

// Pengaturan database
$host    = 'localhost';
$db      = 'car_rental';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

// DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Opsi PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,    // Lempar exception saat error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // Hasil query dalam bentuk array asosiatif
    PDO::ATTR_EMULATE_PREPARES   => false,                     // Gunakan prepared statement asli MySQL
];

// Buat koneksi PDO
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Jangan tampilkan pesan error detail di production
    die("Koneksi database gagal: " . $e->getMessage());
}
