
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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Tugas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="app-layout">
    <aside class="sidebar">
        <div class="brand">Task<span>Manager</span></div>

        <nav>
            <a href="index.php">Dashboard</a>
            <a href="mahasiswa.php">Data Mahasiswa</a>
            <a href="tugas.php" class="active">Manajemen Tugas</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div>
                <h1>Manajemen Tugas</h1>
                <p>Kelola dan pantau tugas mahasiswa.</p>
            </div>
        </header>

        <?php if ($pesan): ?>
            <div class="alert"><?= e($pesan) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert"><?= e($error) ?></div>
        <?php endif; ?>

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

        <section class="card">
            <h2><?= $edit ? 'Edit Tugas' : 'Tambah Tugas Baru' ?></h2>

            <?php if (count($mahasiswa) === 0): ?>
                <p>Belum ada data mahasiswa. Tambahkan mahasiswa terlebih dahulu.</p>
                <a href="mahasiswa.php" class="btn">Ke Data Mahasiswa</a>
            <?php else: ?>
                <form method="POST" class="form-grid">
                    <input
                        type="hidden"
                        name="aksi"
                        value="<?= $edit ? 'edit' : 'tambah' ?>"
                    >

                    <?php if ($edit): ?>
                        <input type="hidden" name="id" value="<?= (int) $edit['id'] ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Mahasiswa</label>
                        <select name="mahasiswa_id" required>
                            <option value="">-- Pilih Mahasiswa --</option>
                            <?php foreach ($mahasiswa as $m): ?>
                                <option
                                    value="<?= (int) $m['id'] ?>"
                                    <?= ($edit && (int) $edit['mahasiswa_id'] === (int) $m['id']) ? 'selected' : '' ?>
                                >
                                    <?= e($m['nama']) ?> (<?= e($m['nim']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Judul Tugas</label>
                        <input
                            type="text"
                            name="judul"
                            placeholder="Masukkan judul tugas"
                            value="<?= e($edit['judul'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea
                            name="deskripsi"
                            rows="4"
                            placeholder="Deskripsi tugas (opsional)"
                        ><?= e($edit['deskripsi'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" required>
                            <?php
                            $statusEdit = $edit['status'] ?? 'belum';
                            $daftarStatus = [
                                'belum' => 'Belum Dikerjakan',
                                'proses' => 'Sedang Dikerjakan',
                                'selesai' => 'Selesai'
                            ];
                            ?>
                            <?php foreach ($daftarStatus as $nilai => $label): ?>
                                <option
                                    value="<?= e($nilai) ?>"
                                    <?= $statusEdit === $nilai ? 'selected' : '' ?>
                                >
                                    <?= e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn">
                            <?= $edit ? 'Simpan Perubahan' : 'Tambah Tugas' ?>
                        </button>

                        <?php if ($edit): ?>
                            <a href="tugas.php" class="btn btn-secondary">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            <?php endif; ?>
        </section>

        <section class="card">
            <h2>Daftar Tugas</h2>

            <div class="table-wrapper">
                <table>
                    <thead>
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
                                        <small><?= e($item['nim']) ?></small>
                                    </td>
                                    <td><?= e($item['judul']) ?></td>
                                    <td><?= e($item['deskripsi']) ?></td>
                                    <td>
                                        <span class="status">
                                            <?= e($daftarStatus[$item['status']] ?? $item['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a
                                                href="tugas.php?edit=<?= (int) $item['id'] ?>"
                                                class="btn btn-secondary"
                                            >Edit</a>

                                            <form method="POST" onsubmit="return confirm('Yakin ingin menghapus tugas ini?')">
                                                <input type="hidden" name="aksi" value="hapus">
                                                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                                <button type="submit" class="btn btn-danger">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">Belum ada data tugas.</td>
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