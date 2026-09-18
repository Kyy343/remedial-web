
<?php
require_once 'config/database.php';

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$pesan = '';
$error = '';

try {
    // TAMBAH TUGAS
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'tambah') {
        $mahasiswa_id = (int) ($_POST['mahasiswa_id'] ?? 0);
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $status = $_POST['status'] ?? 'belum';

        if ($mahasiswa_id <= 0 || $judul === '') {
            throw new Exception('Mahasiswa dan judul tugas wajib diisi.');
        }

        if (!in_array($status, ['belum', 'proses', 'selesai'], true)) {
            throw new Exception('Status tugas tidak valid.');
        }

        $cek = $pdo->prepare('SELECT id FROM mahasiswa WHERE id = ?');
        $cek->execute([$mahasiswa_id]);

        if (!$cek->fetch()) {
            throw new Exception('Mahasiswa tidak ditemukan.');
        }

        $stmt = $pdo->prepare(
            'INSERT INTO tugas (mahasiswa_id, judul, deskripsi, status)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$mahasiswa_id, $judul, $deskripsi, $status]);

        $pesan = 'Tugas berhasil ditambahkan.';
    }

    // EDIT TUGAS
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'edit') {
        $id = (int) ($_POST['id'] ?? 0);
        $mahasiswa_id = (int) ($_POST['mahasiswa_id'] ?? 0);
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $status = $_POST['status'] ?? 'belum';

        if ($id <= 0 || $mahasiswa_id <= 0 || $judul === '') {
            throw new Exception('Data tugas belum lengkap.');
        }

        if (!in_array($status, ['belum', 'proses', 'selesai'], true)) {
            throw new Exception('Status tugas tidak valid.');
        }

        $cek = $pdo->prepare('SELECT id FROM mahasiswa WHERE id = ?');
        $cek->execute([$mahasiswa_id]);

        if (!$cek->fetch()) {
            throw new Exception('Mahasiswa tidak ditemukan.');
        }

        $stmt = $pdo->prepare(
            'UPDATE tugas
             SET mahasiswa_id = ?, judul = ?, deskripsi = ?, status = ?
             WHERE id = ?'
        );
        $stmt->execute([$mahasiswa_id, $judul, $deskripsi, $status, $id]);

        $pesan = 'Tugas berhasil diperbarui.';
    }

    // HAPUS TUGAS
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            throw new Exception('ID tugas tidak valid.');
        }

        $stmt = $pdo->prepare('DELETE FROM tugas WHERE id = ?');
        $stmt->execute([$id]);

        $pesan = 'Tugas berhasil dihapus.';
    }
} catch (Throwable $ex) {
    $error = $ex->getMessage();
}

// Ambil data mahasiswa untuk pilihan form
$mahasiswa = $pdo->query(
    'SELECT id, nama, nim FROM mahasiswa ORDER BY nama ASC'
)->fetchAll(PDO::FETCH_ASSOC);

// Ambil data tugas untuk diedit
$edit = null;
if (isset($_GET['edit'])) {
    $id_edit = (int) $_GET['edit'];

    $stmt = $pdo->prepare('SELECT * FROM tugas WHERE id = ?');
    $stmt->execute([$id_edit]);
    $edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Ambil semua tugas
$tugas = $pdo->query(
    'SELECT tugas.*, mahasiswa.nama, mahasiswa.nim
     FROM tugas
     JOIN mahasiswa ON tugas.mahasiswa_id = mahasiswa.id
     ORDER BY tugas.id DESC'
)->fetchAll(PDO::FETCH_ASSOC);

// Statistik
$totalTugas = count($tugas);
$totalSelesai = 0;
$totalProses = 0;
$totalBelum = 0;

foreach ($tugas as $item) {
    if ($item['status'] === 'selesai') {
        $totalSelesai++;
    } elseif ($item['status'] === 'proses') {
        $totalProses++;
    } else {
        $totalBelum++;
    }
}

$daftarStatus = [
    'belum' => 'Belum Dikerjakan',
    'proses' => 'Sedang Dikerjakan',
    'selesai' => 'Selesai'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manajemen Tugas | TaskManager</title>

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
            <a href="index.php">Dashboard</a>
            <a href="mahasiswa.php">Data Mahasiswa</a>
            <a href="tugas.php" class="active">Manajemen Tugas</a>
        </nav>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="main-content">

        <header class="topbar">
            <div>
                <h1>Manajemen Tugas</h1>
                <p>Kelola dan pantau tugas mahasiswa.</p>
            </div>
        </header>

        <!-- NOTIFIKASI BOOTSTRAP -->
        <?php if ($pesan): ?>
            <div class="alert alert-success alert-dismissible fade show"
                 role="alert">
                <?= e($pesan) ?>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Tutup">
                </button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"
                 role="alert">
                <?= e($error) ?>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Tutup">
                </button>
            </div>
        <?php endif; ?>

        <!-- STATISTIK -->
        <section class="stats-grid">

            <div class="stat-card">
                <span>Total Tugas</span>
                <h2><?= $totalTugas ?></h2>
            </div>

            <div class="stat-card">
                <span>Belum Dikerjakan</span>
                <h2><?= $totalBelum ?></h2>
            </div>

            <div class="stat-card">
                <span>Sedang Dikerjakan</span>
                <h2><?= $totalProses ?></h2>
            </div>

            <div class="stat-card">
                <span>Selesai</span>
                <h2><?= $totalSelesai ?></h2>
            </div>

        </section>

        <!-- FORM TAMBAH / EDIT -->
        <section class="card shadow-sm mb-4">

            <div class="card-header bg-primary text-white">
                <h2 class="mb-0 fs-5">
                    <?= $edit ? 'Edit Tugas' : 'Tambah Tugas Baru' ?>
                </h2>
            </div>

            <div class="card-body">

                <?php if (count($mahasiswa) === 0): ?>

                    <div class="alert alert-warning" role="alert">
                        Belum ada data mahasiswa. Tambahkan mahasiswa terlebih dahulu.
                    </div>

                    <a href="mahasiswa.php" class="btn btn-primary">
                        Ke Data Mahasiswa
                    </a>

                <?php else: ?>

                    <form method="POST">

                        <input
                            type="hidden"
                            name="aksi"
                            value="<?= $edit ? 'edit' : 'tambah' ?>">

                        <?php if ($edit): ?>
                            <input
                                type="hidden"
                                name="id"
                                value="<?= (int) $edit['id'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="mahasiswa_id" class="form-label">
                                Mahasiswa
                            </label>

                            <select
                                class="form-select"
                                id="mahasiswa_id"
                                name="mahasiswa_id"
                                required>

                                <option value="">-- Pilih Mahasiswa --</option>

                                <?php foreach ($mahasiswa as $m): ?>
                                    <option
                                        value="<?= (int) $m['id'] ?>"
                                        <?= ($edit && (int) $edit['mahasiswa_id'] === (int) $m['id']) ? 'selected' : '' ?>>

                                        <?= e($m['nama']) ?> (<?= e($m['nim']) ?>)

                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="judul" class="form-label">
                                Judul Tugas
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="judul"
                                name="judul"
                                maxlength="255"
                                placeholder="Masukkan judul tugas"
                                value="<?= e($edit['judul'] ?? '') ?>"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">
                                Deskripsi
                            </label>

                            <textarea
                                class="form-control"
                                id="deskripsi"
                                name="deskripsi"
                                rows="4"
                                placeholder="Deskripsi tugas (opsional)"><?= e($edit['deskripsi'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">
                                Status
                            </label>

                            <?php
                            $statusEdit = $edit['status'] ?? 'belum';
                            ?>

                            <select
                                class="form-select"
                                id="status"
                                name="status"
                                required>

                                <?php foreach ($daftarStatus as $nilai => $label): ?>
                                    <option
                                        value="<?= e($nilai) ?>"
                                        <?= $statusEdit === $nilai ? 'selected' : '' ?>>

                                        <?= e($label) ?>

                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <?= $edit ? 'Simpan Perubahan' : '+ Tambah Tugas' ?>
                        </button>

                        <?php if ($edit): ?>
                            <a href="tugas.php" class="btn btn-secondary">
                                Batal
                            </a>
                        <?php endif; ?>

                    </form>

                <?php endif; ?>

            </div>
        </section>

        <!-- TABEL DAFTAR TUGAS -->
        <section class="card shadow-sm">

            <div class="card-header">
                <h2 class="mb-0 fs-5">Daftar Tugas</h2>
            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-striped table-hover table-bordered align-middle">

                        <thead class="table-primary">
                            <tr>
                                <th>No</th>
                                <th>Mahasiswa</th>
                                <th>Judul</th>
                                <th>Deskripsi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (count($tugas) > 0): ?>

                                <?php foreach ($tugas as $i => $item): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>

                                        <td>
                                            <strong><?= e($item['nama']) ?></strong><br>
                                            <small class="text-muted">
                                                <?= e($item['nim']) ?>
                                            </small>
                                        </td>

                                        <td><?= e($item['judul']) ?></td>

                                        <td><?= e($item['deskripsi']) ?></td>

                                        <td>
                                            <?php
                                            $warnaStatus = [
                                                'belum' => 'bg-secondary',
                                                'proses' => 'bg-warning text-dark',
                                                'selesai' => 'bg-success'
                                            ];

                                            $status = $item['status'];
                                            ?>

                                            <span class="badge <?= $warnaStatus[$status] ?? 'bg-secondary' ?>">
                                                <?= e($daftarStatus[$status] ?? $status) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <div class="d-flex flex-wrap gap-2">

                                                <a
                                                    href="tugas.php?edit=<?= (int) $item['id'] ?>"
                                                    class="btn btn-warning btn-sm">
                                                    Edit
                                                </a>

                                                <form
                                                    method="POST"
                                                    onsubmit="return confirm('Yakin ingin menghapus tugas ini?')">

                                                    <input
                                                        type="hidden"
                                                        name="aksi"
                                                        value="hapus">

                                                    <input
                                                        type="hidden"
                                                        name="id"
                                                        value="<?= (int) $item['id'] ?>">

                                                    <button
                                                        type="submit"
                                                        class="btn btn-danger btn-sm">
                                                        Hapus
                                                    </button>

                                                </form>

                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="6"
                                        class="text-center text-muted">
                                        Belum ada data tugas.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>
            </div>
        </section>

    </main>
</div>

<!-- JavaScript Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>