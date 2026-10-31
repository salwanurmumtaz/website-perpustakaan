
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

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

$id = $_GET['id'] ?? '';

if ($id === '' || !ctype_digit((string)$id)) {
    die("ID buku tidak valid.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $judul = trim($_POST['judul'] ?? '');
    $penulis = trim($_POST['penulis'] ?? '');
    $tahun = $_POST['tahun'] ?? '';
    $stok = $_POST['stok'] ?? '';

    if (
        $judul === '' ||
        $penulis === '' ||
        !ctype_digit((string)$tahun) ||
        (int)$tahun < 1900 ||
        (int)$tahun > (int)date('Y') ||
        !ctype_digit((string)$stok)
    ) {
        die("Data buku tidak valid. Silakan periksa kembali.");
    }

    $stmt = $conn->prepare(
        "UPDATE buku
         SET Judul = :judul,
             penulis = :penulis,
             tahun = :tahun,
             stok = :stok
         WHERE ID = :id"
    );

    $stmt->execute([
        ':judul' => $judul,
        ':penulis' => $penulis,
        ':tahun' => $tahun,
        ':stok' => $stok,
        ':id' => $id
    ]);

    header("Location: index.php?status=updated");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM buku WHERE ID = ?");
$stmt->execute([$id]);

$buku = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$buku) {
    die("Data buku tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
    rel="stylesheet">

    <title>Edit Buku</title>
</head>
<body>

<div class="container mt-5">

    <h2>Edit Data Buku</h2>
    <form action="edit.php?id=<?= $id ?>" method="POST">

        <div class="mb-3">
            <label class="form-label">Judul Buku</label>

            <input
                type="text"
                name="judul"
                class="form-control"
                value="<?= htmlspecialchars($buku['Judul']) ?>"
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Penulis</label>

            <input
                type="text"
                name="penulis"
                class="form-control"
                value="<?= htmlspecialchars($buku['penulis']) ?>"
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Tahun Terbit</label>

            <input
                type="number"
                name="tahun"
                class="form-control"
                min="1900"
                max="<?= date('Y') ?>"
                value="<?= htmlspecialchars($buku['tahun']) ?>"
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Stok</label>

            <input
                type="number"
                name="stok"
                class="form-control"
                min="0"
                value="<?= htmlspecialchars($buku['stok']) ?>"
                required
            >
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>

        <a href="index.php" class="btn btn-secondary">Kembali</a>
    </form>

</div>
</body>
</html>
