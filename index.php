
<?php
require_once 'config/database.php';

// Menghitung jumlah data
$totalMahasiswa = $pdo->query(
    "SELECT COUNT(*) FROM mahasiswa"
)->fetchColumn();

$totalTugas = $pdo->query(
    "SELECT COUNT(*) FROM tugas"
)->fetchColumn();

$totalSelesai = $pdo->query(
    "SELECT COUNT(*) FROM tugas WHERE status = 'selesai'"
)->fetchColumn();

$totalBelum = $pdo->query(
    "SELECT COUNT(*) FROM tugas WHERE status = 'belum'"
)->fetchColumn();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Manajemen Tugas Mahasiswa</title>

    <!-- Library Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- CSS buatan sendiri -->
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="app-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="brand">Task<span>Manager</span></div>

        <nav>
            <a href="index.php" class="active">Dashboard</a>
            <a href="mahasiswa.php">Data Mahasiswa</a>
            <a href="tugas.php">Manajemen Tugas</a>
        </nav>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="main-content">

        <header class="topbar">
            <div>
                <h1>Dashboard</h1>
                <p>Selamat datang di Manajemen Tugas Mahasiswa.</p>
            </div>
        </header>

        <!-- STATISTIK -->
        <section class="stats-grid">

            <div class="stat-card">
                <span>Total Mahasiswa</span>
                <h2><?= (int) $totalMahasiswa ?></h2>
                <p>Mahasiswa terdaftar</p>
            </div>

            <div class="stat-card">
                <span>Total Tugas</span>
                <h2><?= (int) $totalTugas ?></h2>
                <p>Seluruh tugas</p>
            </div>

            <div class="stat-card">
                <span>Tugas Belum Selesai</span>
                <h2><?= (int) $totalBelum ?></h2>
                <p>Perlu dikerjakan</p>
            </div>

            <div class="stat-card">
                <span>Tugas Selesai</span>
                <h2><?= (int) $totalSelesai ?></h2>
                <p>Sudah diselesaikan</p>
            </div>

        </section>

        <!-- MENU UTAMA DENGAN BOOTSTRAP -->
        <section class="card shadow-sm mt-4">

            <div class="card-header bg-primary text-white">
                <h2 class="mb-0 fs-5">Menu Utama</h2>
            </div>

            <div class="card-body">
                <p class="card-text">
                    Pilih menu di bawah untuk mengelola data aplikasi.
                </p>

                <div class="d-flex flex-wrap gap-2">

                    <a href="mahasiswa.php"
                       class="btn btn-primary">
                        Kelola Mahasiswa
                    </a>

                    <a href="tugas.php"
                       class="btn btn-secondary">
                        Kelola Tugas
                    </a>

                </div>
            </div>

        </section>

    </main>
</div>

<!-- JavaScript Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>