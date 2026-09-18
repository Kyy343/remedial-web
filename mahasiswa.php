<?php
require_once __DIR__ . '/config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

// Token CSRF untuk melindungi form POST
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];

$error = '';
$edit = null;

// Nilai form agar tidak hilang jika validasi gagal
$nama = '';
$nim = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($csrfToken, $token)
    ) {
        $error = 'Permintaan tidak valid. Silakan muat ulang halaman.';
    } else {
        $aksi = $_POST['aksi'] ?? '';

        if ($aksi === 'tambah' || $aksi === 'edit') {
            $nama = trim((string) ($_POST['nama'] ?? ''));
            $nim = trim((string) ($_POST['nim'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));

            $id = filter_var(
                $_POST['id'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            // Validasi wajib isi
            if ($nama === '' || $nim === '' || $email === '') {
                $error = 'Nama, NIM, dan email wajib diisi.';
            } elseif (
                mb_strlen($nama, 'UTF-8') > 100 ||
                mb_strlen($nim, 'UTF-8') > 20 ||
                mb_strlen($email, 'UTF-8') > 100
            ) {
                $error = 'Panjang nama, NIM, atau email melebihi batas.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Format email tidak valid.';
            } elseif (
                $aksi === 'edit' &&
                ($id === false || $id === null)
            ) {
                $error = 'ID mahasiswa tidak valid.';
            } else {
                try {
                    if ($aksi === 'tambah') {
                        $stmt = $pdo->prepare(
                            'INSERT INTO mahasiswa (nama, nim, email)
                             VALUES (?, ?, ?)'
                        );

                        $stmt->execute([$nama, $nim, $email]);
                    } else {
                        $stmt = $pdo->prepare(
                            'UPDATE mahasiswa
                             SET nama = ?, nim = ?, email = ?
                             WHERE id = ?'
                        );

                        $stmt->execute([$nama, $nim, $email, $id]);
                    }

                    header('Location: mahasiswa.php');
                    exit;
                } catch (PDOException $ex) {
                    // Detail error database tidak ditampilkan ke pengguna
                    error_log($ex->getMessage());

                    $error = 'Gagal menyimpan data. Pastikan NIM dan email belum digunakan.';
                }
            }
        } elseif ($aksi === 'hapus') {
            $id = filter_var(
                $_POST['id'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if ($id === false || $id === null) {
                $error = 'ID mahasiswa tidak valid.';
            } else {
                try {
                    $stmt = $pdo->prepare(
                        'DELETE FROM mahasiswa WHERE id = ?'
                    );
                    $stmt->execute([$id]);

                    header('Location: mahasiswa.php');
                    exit;
                } catch (PDOException $ex) {
                    error_log($ex->getMessage());

                    $error = 'Gagal menghapus data. Pastikan mahasiswa tidak sedang digunakan oleh data tugas.';
                }
            }
        } else {
            $error = 'Aksi tidak dikenal.';
        }
    }
}

// Ambil data untuk form edit
if (isset($_GET['edit'])) {
    $editId = filter_var(
        $_GET['edit'],
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($editId !== false && $editId !== null) {
        $stmt = $pdo->prepare(
            'SELECT * FROM mahasiswa WHERE id = ?'
        );
        $stmt->execute([$editId]);
        $edit = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    if (!$edit && $error === '') {
        $error = 'Data mahasiswa tidak ditemukan.';
    }

    // Isi form dengan nilai POST ketika validasi gagal
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error !== '') {
        $edit = [
            'id' => $editId ?: '',
            'nama' => $nama,
            'nim' => $nim,
            'email' => $email
        ];
    }
}

// Ambil seluruh data mahasiswa
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

    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="app-layout">

    <aside class="sidebar">
        <div class="brand">Task<span>Manager</span></div>

        <a href="index.php">Dashboard</a>
        <a href="mahasiswa.php" class="active">Data Mahasiswa</a>
        <a href="tugas.php">Data Tugas</a>
    </aside>

    <main class="main-content">

        <div class="topbar">
            <div>
                <h1>Data Mahasiswa</h1>
                <p class="subtitle">
                    Kelola data mahasiswa dengan mudah.
                </p>
            </div>
        </div>

        <!-- Statistik -->
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

        <!-- Pesan error -->
        <?php if ($error !== ''): ?>
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

        <!-- Form mahasiswa -->
        <section class="card shadow-sm mb-4">

            <div class="card-header bg-primary text-white">
                <h2 class="mb-0 fs-5">
                    <?= $edit ? 'Edit Mahasiswa' : 'Tambah Mahasiswa' ?>
                </h2>
            </div>

            <div class="card-body">

                <form method="post" action="mahasiswa.php<?= $edit ? '?edit=' . (int)$edit['id'] : '' ?>">

                    <input type="hidden"
                           name="csrf_token"
                           value="<?= e($csrfToken) ?>">

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
                            value="<?= e($edit['nama'] ?? $nama) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="nim" class="form-label">NIM</label>

                        <input
                            type="text"
                            class="form-control"
                            id="nim"
                            name="nim"
                            required
                            maxlength="20"
                            placeholder="Masukkan NIM"
                            value="<?= e($edit['nim'] ?? $nim) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>

                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            required
                            maxlength="100"
                            placeholder="nama@email.com"
                            value="<?= e($edit['email'] ?? $email) ?>">
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

        <!-- Tabel mahasiswa -->
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
                                                href="?edit=<?= (int)$m['id'] ?>">
                                                Edit
                                            </a>

                                            <form
                                                method="post"
                                                action="mahasiswa.php"
                                                onsubmit="return confirm('Hapus mahasiswa ini?')">

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= e($csrfToken) ?>">

                                                <input
                                                    type="hidden"
                                                    name="aksi"
                                                    value="hapus">

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= (int)$m['id'] ?>">

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>