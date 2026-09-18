
<?php
require_once __DIR__ . '/config/database.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$error = '';
$edit = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah' || $aksi === 'edit') {
        $nama = trim($_POST['nama'] ?? '');
        $nim = trim($_POST['nim'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($nama === '' || $nim === '' ||
            !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Nama, NIM, dan email yang valid wajib diisi.';
        } else {
            try {
                if ($aksi === 'tambah') {
                    $stmt = $pdo->prepare(
                        'INSERT INTO mahasiswa (nama, nim, email)
                         VALUES (?, ?, ?)'
                    );
                    $stmt->execute([$nama, $nim, $email]);
                } else {
                    $id = (int)($_POST['id'] ?? 0);
                    $stmt = $pdo->prepare(
                        'UPDATE mahasiswa SET nama=?, nim=?, email=?
                         WHERE id=?'
                    );
                    $stmt->execute([$nama, $nim, $email, $id]);
                }

                header('Location: mahasiswa.php');
                exit;
            } catch (PDOException $ex) {
                $error = 'Gagal menyimpan. Pastikan NIM dan email belum digunakan.';
            }
        }
    } elseif ($aksi === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM mahasiswa WHERE id=?');
        $stmt->execute([$id]);

        header('Location: mahasiswa.php');
        exit;
    }
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM mahasiswa WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

$mahasiswa = $pdo->query(
    'SELECT * FROM mahasiswa ORDER BY id DESC'
)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Data Mahasiswa | TaskManager</title>

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

        <a href="index.php">Dashboard</a>
        <a href="mahasiswa.php" class="active">Data Mahasiswa</a>
        <a href="tugas.php">Data Tugas</a>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="main-content">

        <div class="topbar">
            <div>
                <h1>Data Mahasiswa</h1>
                <p class="subtitle">
                    Kelola data mahasiswa dengan mudah.
                </p>
            </div>
        </div>

        <!-- STATISTIK -->
        <div class="stats-grid">

            <div class="stat-card">
                <p>Total Mahasiswa</p>
                <strong><?= count($mahasiswa) ?></strong>
            </div>

            <div class="stat-card">
                <p>Database</p>
                <strong style="font-size:18px">MySQL</strong>
            </div>

            <div class="stat-card">
                <p>Status Sistem</p>
                <strong style="font-size:18px;color:#16a34a">
                    Aktif
                </strong>
            </div>

        </div>

        <!-- PESAN ERROR BOOTSTRAP -->
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

        <!-- FORM MAHASISWA -->
        <section class="card shadow-sm mb-4">

            <div class="card-header bg-primary text-white">
                <h2 class="mb-0 fs-5">
                    <?= $edit ? 'Edit Mahasiswa' : 'Tambah Mahasiswa' ?>
                </h2>
            </div>

            <div class="card-body">

                <form method="post">

                    <input type="hidden"
                           name="aksi"
                           value="<?= $edit ? 'edit' : 'tambah' ?>">

                    <?php if ($edit): ?>
                        <input type="hidden"
                               name="id"
                               value="<?= e($edit['id']) ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="nama" class="form-label">
                            Nama Mahasiswa
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="nama"
                            name="nama"
                            required
                            maxlength="100"
                            placeholder="Masukkan nama mahasiswa"
                            value="<?= e($edit['nama'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="nim" class="form-label">
                            NIM
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="nim"
                            name="nim"
                            required
                            maxlength="20"
                            placeholder="Masukkan NIM"
                            value="<?= e($edit['nim'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">
                            Email
                        </label>

                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            required
                            maxlength="100"
                            placeholder="nama@email.com"
                            value="<?= e($edit['email'] ?? '') ?>">
                    </div>

                    <button class="btn btn-primary" type="submit">
                        <?= $edit ? 'Simpan Perubahan' : '+ Tambah Mahasiswa' ?>
                    </button>

                    <?php if ($edit): ?>
                        <a class="btn btn-secondary"
                           href="mahasiswa.php">
                            Batal
                        </a>
                    <?php endif; ?>

                </form>

            </div>
        </section>

        <!-- TABEL DATA MAHASISWA -->
        <section class="card shadow-sm">

            <div class="card-header">
                <h2 class="mb-0 fs-5">Daftar Mahasiswa</h2>
            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-striped table-hover table-bordered align-middle">

                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>NIM</th>
                                <th>Email</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (count($mahasiswa) > 0): ?>

                                <?php foreach ($mahasiswa as $m): ?>
                                    <tr>
                                        <td><?= e($m['id']) ?></td>
                                        <td><?= e($m['nama']) ?></td>
                                        <td><?= e($m['nim']) ?></td>
                                        <td><?= e($m['email']) ?></td>

                                        <td>
                                            <div class="d-flex flex-wrap gap-2">

                                                <a
                                                    class="btn btn-warning btn-sm"
                                                    href="?edit=<?= e($m['id']) ?>">
                                                    Edit
                                                </a>

                                                <form
                                                    method="post"
                                                    onsubmit="return confirm('Hapus mahasiswa ini?')">

                                                    <input
                                                        type="hidden"
                                                        name="aksi"
                                                        value="hapus">

                                                    <input
                                                        type="hidden"
                                                        name="id"
                                                        value="<?= e($m['id']) ?>">

                                                    <button
                                                        class="btn btn-danger btn-sm"
                                                        type="submit">
                                                        Hapus
                                                    </button>

                                                </form>

                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="5"
                                        class="text-center text-muted">
                                        Belum ada data mahasiswa.
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

<!-- JavaScript Bootstrap untuk tombol alert -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>