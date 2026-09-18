<?php
$host = 'localhost';
$dbname = 'nama_database';
$username = 'username_database';
$password = 'isi_password_di_lokal';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    error_log($e->getMessage());
    exit('Koneksi database gagal. Periksa konfigurasi MySQL.');
}