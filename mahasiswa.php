
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
                <p class="subtitle">Kelola data mahasiswa dengan mudah.</p>
            </div>
        </div>

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
                <strong style="font-size:18px;color:#16a34a">Aktif</strong>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <section class="card">
            <h2><?= $edit ? 'Edit Mahasiswa' : 'Tambah Mahasiswa' ?></h2>

            <form method="post">
                <input type="hidden" name="aksi"
                       value="<?= $edit ? 'edit' : 'tambah' ?>">

                <?php if ($edit): ?>
                    <input type="hidden" name="id"
                           value="<?= e($edit['id']) ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Nama Mahasiswa</label>
                    <input name="nama" required maxlength="100"
                           placeholder="Masukkan nama mahasiswa"
                           value="<?= e($edit['nama'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>NIM</label>
                    <input name="nim" required maxlength="20"
                           placeholder="Masukkan NIM"
                           value="<?= e($edit['nim'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required maxlength="100"
                           placeholder="nama@email.com"
                           value="<?= e($edit['email'] ?? '') ?>">
                </div>

                <button class="btn" type="submit">
                    <?= $edit ? 'Simpan Perubahan' : '+ Tambah Mahasiswa' ?>
                </button>

                <?php if ($edit): ?>
                    <a class="btn btn-secondary" href="mahasiswa.php">
                        Batal
                    </a>
                <?php endif; ?>
            </form>
        </section>

        <section class="card">
            <h2>Daftar Mahasiswa</h2>

            <div class="table-wrapper">
                <table>
                    <thead>
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
                                        <a class="btn"
                                           href="?edit=<?= e($m['id']) ?>">
                                            Edit
                                        </a>

                                        <form method="post"
                                              style="display:inline"
                                              onsubmit="return confirm('Hapus mahasiswa ini?')">
                                            <input type="hidden"
                                                   name="aksi" value="hapus">
                                            <input type="hidden"
                                                   name="id"
                                                   value="<?= e($m['id']) ?>">
                                            <button class="btn btn-danger"
                                                    type="submit">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">
                                    Belum ada data mahasiswa.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>
</div>

</body>
</html>