<?php
require "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method tidak diizinkan.");
}

// Validasi CSRF Token (Slide 13)
if (!hash_equals($_SESSION["csrf"] ?? "", $_POST["csrf"] ?? "")) {
    http_response_code(403);
    exit("Akses ditolak: Token CSRF tidak valid!");
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute(["id" => $id]);
}

header("Location: index.php?status=deleted");
exit;