<?php
require "db.php";

$errors = [];
$name = "";
$category = "Umum";
$price = "";
$stock = "";

// 1. PROSES CREATE PRODUK (Method POST) - Modul 3 & 4
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Normalisasi & Ambil Input (Slide 6)
    $name = trim($_POST["name"] ?? "");
    $category = trim($_POST["category"] ?? "Umum");
    $price = filter_input(INPUT_POST, "price", FILTER_VALIDATE_FLOAT);
    $stock = filter_input(INPUT_POST, "stock", FILTER_VALIDATE_INT);

    // Validasi Server-Side (Slide 6)
    if (mb_strlen($name) < 3) {
        $errors["name"] = "Nama produk minimal 3 karakter.";
    }
    if ($category === "") {
        $category = "Umum";
    }
    if ($price === false || $price <= 0) {
        $errors["price"] = "Harga harus berupa angka dan lebih dari 0.";
    }
    if ($stock === false || $stock < 0) {
        $errors["stock"] = "Stok harus berupa angka bulat dan tidak boleh negatif.";
    }

    // Jika lolos validasi, simpan ke database dengan Prepared Statement (Slide 7 & 9)
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO products (name, category, price, stock) 
                 VALUES (:name, :category, :price, :stock)"
            );
            $stmt->execute([
                "name" => $name,
                "category" => $category,
                "price" => $price,
                "stock" => $stock
            ]);

            // Pola PRG (Post-Redirect-Get) agar tidak duplikat saat di-refresh (Slide 7)
            header("Location: index.php?status=created");
            exit;
        } catch (PDOException $e) {
            // Tangkap error jika nama produk sudah ada (UNIQUE constraint)
            if ($e->getCode() == 23000) {
                $errors["name"] = "Nama produk sudah terdaftar, gunakan nama lain.";
            } else {
                $errors["db"] = "Gagal menyimpan data: " . $e->getMessage();
            }
        }
    }
}

// 2. PROSES READ & FITUR BONUS SEARCH (Method GET) - Slide 11 & 19
$q = trim($_GET["q"] ?? "");
if ($q !== "") {
    $sql = "SELECT * FROM products WHERE name LIKE :q1 OR category LIKE :q2 ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["q1" => "%$q%", "q2" => "%$q%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
}
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Manager - Praktikum Web 3</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Manajemen Produk (Product Manager)</h1>

    <!-- Notifikasi Status dari Pola PRG -->
    <?php if (($_GET["status"] ?? "") === "created"): ?>
        <div class="alert">Produk baru berhasil disimpan!</div>
    <?php elseif (($_GET["status"] ?? "") === "updated"): ?>
        <div class="alert">Data produk berhasil diperbarui!</div>
    <?php elseif (($_GET["status"] ?? "") === "deleted"): ?>
        <div class="alert">Produk telah dihapus.</div>
    <?php endif; ?>

    <!-- Menampilkan Pesan Error Validasi -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <strong>Gagal menyimpan produk:</strong>
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err, ENT_QUOTES, "UTF-8") ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- FORM TAMBAH PRODUK (POST) -->
    <div class="card" style="margin-bottom: 24px;">
        <h3>Tambah Produk Baru</h3>
        <form action="index.php" method="POST">
            <div class="form-group">
                <label for="name">Nama Produk</label>
                <input id="name" name="name" value="<?= htmlspecialchars($name, ENT_QUOTES, "UTF-8") ?>" minlength="3" required placeholder="Contoh: Kopi Arabika">
            </div>
            <div class="form-group">
                <label for="category">Kategori</label>
                <input id="category" name="category" value="<?= htmlspecialchars($category, ENT_QUOTES, "UTF-8") ?>" required>
            </div>
            <div class="form-group">
                <label for="price">Harga (Rp)</label>
                <input id="price" name="price" type="number" min="1" step="any" value="<?= htmlspecialchars((string)$price, ENT_QUOTES, "UTF-8") ?>" required placeholder="Contoh: 50000">
            </div>
            <div class="form-group">
                <label for="stock">Stok</label>
                <input id="stock" name="stock" type="number" min="0" value="<?= htmlspecialchars((string)$stock, ENT_QUOTES, "UTF-8") ?>" required placeholder="Contoh: 10">
            </div>
            <button type="submit" class="btn-primary">Simpan Produk</button>
        </form>
    </div>

    <!-- FORM PENCARIAN PRODUK (GET - Slide 4 & 19) -->
    <form method="GET" action="index.php" class="actions" style="margin-bottom: 16px;">
        <input name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, "UTF-8") ?>" placeholder="Cari nama atau kategori produk..." style="padding: 8px; flex: 1; border: 1px solid #cbd5e1; border-radius: 6px;">
        <button type="submit" class="btn-primary">Cari</button>
        <?php if ($q !== ""): ?>
            <a href="index.php" class="btn btn-secondary">Reset</a>
        <?php endif; ?>
    </form>

    <!-- DAFTAR PRODUK DENGAN FLEXBOX & ANTI-XSS (Slide 10, 11, 15) -->
    <div class="products">
        <?php if (empty($products)): ?>
            <p>Tidak ada produk yang ditemukan.</p>
        <?php endif; ?>

        <?php foreach ($products as $p): ?>
            <div class="card product-card">
                <div>
                    <span class="badge"><?= htmlspecialchars($p["category"], ENT_QUOTES, "UTF-8") ?></span>
                    <h3 style="margin: 4px 0;"><?= htmlspecialchars($p["name"], ENT_QUOTES, "UTF-8") ?></h3>
                    <p class="price">Rp <?= number_format($p["price"], 0, ",", ".") ?></p>
                    <p style="margin: 0; color: #64748b;">Stok tersedia: <strong><?= (int)$p["stock"] ?></strong></p>
                </div>

                <!-- Tombol Edit & Hapus (Dilengkapi CSRF Token - Slide 12 & 13) -->
                <div class="actions">
                    <a href="edit.php?id=<?= (int)$p["id"] ?>" class="btn btn-edit">Edit</a>
                    
                    <form method="POST" action="delete.php" onsubmit="return confirm('Yakin ingin menghapus produk ini?');">
                        <input type="hidden" name="id" value="<?= (int)$p["id"] ?>">
                        <input type="hidden" name="csrf" value="<?= $_SESSION["csrf"] ?>">
                        <button type="submit" class="btn-delete">Hapus</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>