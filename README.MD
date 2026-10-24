# Product Manager - Praktikum Pemrograman Web Pertemuan 3

Aplikasi manajemen produk berbasis PHP (PDO), MySQL, dan CSS (Box Model & Flexbox).

## Fitur Utama
1. **CRUD Produk**: Tambah (Create), Tampil (Read), Ubah (Update), dan Hapus (Delete) produk.
2. **Keamanan**:
   - Koneksi PDO dengan Prepared Statements (`ATTR_EMULATE_PREPARES => false`) untuk mencegah SQL Injection.
   - Output Escaping menggunakan `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` untuk mencegah XSS.
   - Proteksi CSRF Token menggunakan `$_SESSION['csrf']` dan `hash_equals()` pada aksi perubahan/penghapusan data.
3. **Validasi Server-Side & Pola PRG**:
   - Validasi panjang nama (minimal 3 karakter), nama unik, harga (> 0), dan stok (>= 0).
   - Pola Post-Redirect-Get (PRG) agar tidak terjadi duplikasi data ketika halaman di-refresh.
4. **Fitur Bonus**: Pencarian nama dan filter kategori menggunakan metode `GET` dengan Prepared Statement.

## Cara Menjalankan Aplikasi
1. Letakkan folder `product-manager` di dalam direktori web server (`htdocs` pada XAMPP atau `www` pada Laragon).
2. Nyalakan modul **Apache** dan **MySQL**.
3. Buka browser dan akses: `http://localhost/product-manager/public/index.php`
   *(Catatan: Database `store_db` dan tabel `products` akan otomatis dibuat saat halaman pertama kali diakses, atau dapat diimpor secara manual melalui file `database/store_db.sql` di phpMyAdmin).*