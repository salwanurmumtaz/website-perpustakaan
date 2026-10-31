<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'perpustakaan';

try {
    $conn = new PDO(
    "mysql:host=$host;port=3307;dbname=$database",
    $user,
    $password
);

    $conn->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $e) {
    http_response_code(500);
    exit("Koneksi ke database gagal: " . $e->getMessage());
}

$query = $conn->query("SELECT * FROM buku ORDER BY created_at DESC");
$list_buku = $query->fetchAll(PDO::FETCH_ASSOC);

$pesan = '';
$jenisPesan = '';
$error = [];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $judul = $_POST['judul'] ?? '';
    $penulis = $_POST['penulis'] ?? '';
    $terbit = $_POST['terbit'] ?? '';
    $stok = $_POST['stok'] ?? '';

    if (empty($judul)) {
        $error[] = "Judul buku harus di isi.";
    }
    if (empty($penulis)) {
        $error[] = "Penulis harus di isi.";
    }
    if (!is_numeric($terbit)) {
        $error[] = "Tahun terbit harus diisi.";
    }
    if (!is_numeric($stok)) {
        $error[] = "Stok harus di isi.";
    }

    if (empty($error)) {

        $stmt = $conn->prepare("INSERT INTO buku (Judul, penulis, tahun, stok, created_at) VALUES (:judul, :penulis, :tahun, :stok, NOW())");
        $stmt->bindParam(':judul', $judul);
        $stmt->bindParam(':penulis', $penulis);
        $stmt->bindParam(':tahun', $terbit);
        $stmt->bindParam(':stok', $stok);

        $stmt->execute();

        header("Location: index.php?status=success");
        exit();
    }else{
        $pesan = implode("<br>", $error);
        $jenisPesan = 'danger';
    }
}

if (($_GET['status'] ?? '') === 'success') {
    $pesan = "Data buku berhasil disimpan.";
    $jenisPesan = 'success';

} else if (($_GET['status'] ?? '') === 'updated') {
    $pesan = "Data buku berhasil diperbarui.";
    $jenisPesan = 'success';

} else if (($_GET['status'] ?? '') === 'deleted') {
    $pesan = "Data buku berhasil dihapus.";
    $jenisPesan = 'success';

} else if (($_GET['status'] ?? '') === 'error') {
    $pesan = "Terjadi kesalahan saat memproses data buku.";
    $jenisPesan = 'danger';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>Website Praktik</title>
</head>
<body>
    <header class="bg-primary text-white p-3">
        <h1 class="h3 m-0">Website Praktik</h1>
    </header>

    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Navigasi</a>
            </form>
        </div>
    </nav>

    <div class="container-fluid my-4">
        <div class="row">
            <aside class="col-md-3">
                <div class="bg-light border rounded p-3">
                <h2>Menu</h2>
                    <ul class="list-group">
                        <li class="list-group-item">
                            <a href="index.php" class="text-decoration-none text-black">Dashboard</a>
                        </li>

                        <li class="list-group-item">
                            <a href="index.php" class="text-decoration-none text-black">Daftar Buku</a>
                        </li>

                        <li class="list-group-item">
                            <a href="peminjaman.php" class="text-decoration-none text-black">Form Peminjaman</a>
                        </li>
                    </ul>
                </div>
            </aside>

            <main class="col-md-9">
                <section>
                    <h2>Form Tambah Buku</h2>
                    <form action="index.php" method="post" id="form-buku" novalidate>
                        <input type="hidden" name="id" value="" />
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="judul" class="form-label">Judul Buku</label>
                                <input type="text" class="form-control" name="judul" id="judul" required minlength="3"> 
                                <div class="invalid-feedback"> 
                                    Minimal 3 karakter
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="penulis" class="form-label">Penulis</label>
                                <input type="text" class="form-control" name="penulis" id="penulis" required>
                                <div class="invalid-feedback">
                                    Isi Penulis
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="terbit" class="form-label">Tahun Terbit</label>
                                <input type="number" class="form-control" name="terbit" min="1900" max="2024" id="terbit" required>
                                <div class="invalid-feedback">
                                    Isi Tahun Terbit
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="stok" class="form-label">Stok</label>
                                <input type="number" class="form-control" name="stok" min="0" step="1" id="stok" required>
                                <div class="invalid-feedback">
                                    Stok tidak boleh kosong
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </section>
                <section class="border rounded p-3 mt-4">
                    <h2>Daftar Buku</h2>
                    <?php if ($pesan !== ''): ?>
                        <div class="alert alert-<?= $jenisPesan ?>">
                            <?= $pesan ?>
                        </div>
                    <?php endif; ?>

                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Judul</th>
                                <th>Penulis</th>
                                <th>Tahun Terbit</th>
                                <th>Stok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($list_buku)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        Tidak ada data buku
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($list_buku as $index => $buku): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= $buku['Judul'] ?></td>
                                        <td><?= $buku['penulis'] ?></td>
                                        <td><?= $buku['tahun'] ?></td>
                                        <td><?= $buku['stok'] ?></td>
                                        <td>
                                            <a href="edit.php?id=<?= $buku['ID'] ?>"
                                            class="btn btn-sm btn-warning">Edit</a>

                                            <a href="delete.php?id=<?= $buku['ID'] ?>"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?')">
                                            Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>
            </main>
        </div>
    </div>
    <footer class="bg-secondary text-white text-center p-2 m-4">
        <p class="mb-0">&copy; 2026 Website Praktik. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>