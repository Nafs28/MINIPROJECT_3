<?php
require "db.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT) 
   ?: filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: index.php");
    exit;
}

// Ambil data produk saat ini
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute(["id" => $id]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    exit("Produk tidak ditemukan.");
}

$errors = [];

// Proses UPDATE saat form disubmit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $category = trim($_POST["category"] ?? "Umum");
    $price = filter_input(INPUT_POST, "price", FILTER_VALIDATE_FLOAT);
    $stock = filter_input(INPUT_POST, "stock", FILTER_VALIDATE_INT);

    if (mb_strlen($name) < 3) $errors["name"] = "Nama minimal 3 karakter.";
    if ($price === false || $price <= 0) $errors["price"] = "Harga harus > 0.";
    if ($stock === false || $stock < 0) $errors["stock"] = "Stok tidak boleh negatif.";

    if (empty($errors)) {
        $updateStmt = $pdo->prepare(
            "UPDATE products SET name=:name, category=:category, price=:price, stock=:stock WHERE id=:id"
        );
        $updateStmt->execute(compact("name", "category", "price", "stock", "id"));
        header("Location: index.php?status=updated");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="card" style="max-width: 500px; margin: 40px auto;">
        <h2>Edit Produk</h2>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $e) echo "<p>" . htmlspecialchars($e) . "</p>"; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="edit.php">
            <input type="hidden" name="id" value="<?= (int)$product["id"] ?>">
            <div class="form-group">
                <label>Nama Produk</label>
                <input name="name" value="<?= htmlspecialchars($_POST["name"] ?? $product["name"], ENT_QUOTES, "UTF-8") ?>" required>
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <input name="category" value="<?= htmlspecialchars($_POST["category"] ?? $product["category"], ENT_QUOTES, "UTF-8") ?>" required>
            </div>
            <div class="form-group">
                <label>Harga (Rp)</label>
                <input name="price" type="number" step="any" value="<?= htmlspecialchars((string)($_POST["price"] ?? $product["price"]), ENT_QUOTES, "UTF-8") ?>" required>
            </div>
            <div class="form-group">
                <label>Stok</label>
                <input name="stock" type="number" value="<?= htmlspecialchars((string)($_POST["stock"] ?? $product["stock"]), ENT_QUOTES, "UTF-8") ?>" required>
            </div>
            <div class="actions">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>