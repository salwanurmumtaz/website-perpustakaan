
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

$stmt = $conn->prepare("DELETE FROM buku WHERE ID = ?");
$stmt->execute([$id]);

header("Location: index.php?status=deleted");
exit();
?>
