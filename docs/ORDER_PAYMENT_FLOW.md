# Alur Order dan Pembayaran

## 1. Checkout dan stok

`OrderController::checkout()` melakukan validasi alamat, kurir, promo, dan stok di dalam transaksi database.

- Stok dikunci dengan `lockForUpdate()` sebelum dikurangi.
- Jika validasi atau pembuatan order gagal di dalam transaksi, database melakukan rollback.
- Jika pembuatan Snap Token Midtrans gagal setelah order dibuat, `cancelAndRestoreStock()` mengembalikan stok.
- Setelah cart berhasil menjadi order, item cart dihapus.

## 2. Status Midtrans

Midtrans adalah sumber kebenaran pembayaran:

- `settlement` atau `capture` -> `orders.status = completed`.
- `deny`, `cancel`, `expire`, atau `failure` -> `orders.status = cancelled`.
- Status dan payload disimpan di `transaction_status`, `payment_type`, `fraud_status`, dan `payment_payload`.
- Jika webhook terlambat, halaman riwayat/detail mencoba membaca status langsung dari Midtrans melalui `MidtransService::fetchTransactionStatus()`.

## 3. Order kedaluwarsa

`OrderExpiryService` mencari order `pending` yang melewati `expires_at` atau berusia lebih dari 24 jam.

Command scheduler:

```bash
php artisan orders:expire-pending
```

Scheduler menjalankannya setiap 10 menit. Order yang kedaluwarsa dibatalkan dan stoknya dikembalikan satu kali saja.

## 4. Komentar

Komentar milik user dapat dihapus melalui endpoint DELETE yang memvalidasi kepemilikan berdasarkan `user_id`. Fitur edit komentar sengaja tidak tersedia.

## 5. Bukti pembayaran PDF

Struk dibuat di browser menggunakan jsPDF dan AutoTable. Struk berisi ID transaksi, tanggal, nomor HP, produk, toko, jumlah, harga, subtotal, foto produk bila CORS mengizinkan, dan link produk. Jika foto eksternal tidak bisa diambil karena CORS, PDF tetap dibuat tanpa foto.

## 6. Pemeriksaan lokal

Perintah validasi utama:

```bash
php artisan test --filter=EcommerceTest
php artisan view:cache
npm run build
```
