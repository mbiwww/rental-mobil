<?php
// config/database.php

$host = "localhost";
$user = "root";
$pass = ""; // Kosongkan jika pakai Laragon/XAMPP default
$db   = "db_rentalku"; // Pastikan nama database di phpMyAdmin sama

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>