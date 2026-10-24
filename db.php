<?php
// db.php - Auto-Create Database & Tabel jika belum ada
session_start();
$_SESSION["csrf"] ??= bin2hex(random_bytes(32));

$host = "localhost";
$dbname = "store_db";
$username = "root";
$password = ""; // Kosongkan jika pakai XAMPP/Laragon standar

try {
    // 1. Koneksi ke MySQL tanpa menyebut nama database dulu
    $pdo = new PDO(
        "mysql:host=$host;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // 2. Buat database store_db otomatis jika belum ada
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // 3. Gunakan database store_db
    $pdo->exec("USE `$dbname`");

    // 4. Buat tabel products otomatis jika belum ada
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        category VARCHAR(50) NOT NULL DEFAULT 'Umum',
        price DECIMAL(12,2) NOT NULL,
        stock INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}