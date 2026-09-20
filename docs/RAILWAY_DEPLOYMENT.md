# Deploy ke Railway

## Konfigurasi service

Railway membaca `Dockerfile` dan menjalankan entrypoint container. Jangan isi Start Command dengan `apache2-foreground`; biarkan kosong agar `docker-entrypoint.sh` mengatur port `$PORT` dari Railway.

Healthcheck menggunakan `/up`, bukan `/`, sehingga tidak bergantung pada query homepage.

## Variables wajib

Isi pada Railway Project Settings -> Variables. Jangan commit atau upload file `.env`.

```text
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:<hasil-php-artisan-key-generate--show>
APP_URL=https://<domain-railway>

DB_CONNECTION=mysql
DB_HOST=<railway-mysql-host>
DB_PORT=<railway-mysql-port>
DB_DATABASE=<railway-mysql-database>
DB_USERNAME=<railway-mysql-user>
DB_PASSWORD=<railway-mysql-password>
```

Tambahkan credentials fitur yang dipakai aplikasi sebagai Railway Variables:

```text
MIDTRANS_CLIENT_KEY=...
MIDTRANS_SERVER_KEY=...
MIDTRANS_IS_PRODUCTION=false
CLOUDINARY_CLOUD_NAME=...
CLOUDINARY_API_KEY=...
CLOUDINARY_API_SECRET=...
CLOUDINARY_URL=...
```

Railway menyediakan `PORT` secara otomatis. Jangan hardcode `80` atau `8080` di Variables. Entrypoint mengubah konfigurasi Apache agar listen pada nilai `PORT` tersebut.

## Migrasi

Container menjalankan migrasi otomatis saat startup karena `RUN_MIGRATIONS` default-nya `true`. Untuk mematikan perilaku ini dan menjalankan migrasi melalui deployment command terpisah, set:

```text
RUN_MIGRATIONS=false
```

## Pemeriksaan setelah deploy

1. Buka `https://<domain-railway>/up`; respons harus berhasil.
2. Periksa log sampai terlihat `Apache configuration OK` dan port Railway.
3. Buka homepage setelah migrasi selesai.
4. Jika gagal, cek `APP_KEY`, seluruh `DB_*`, dan koneksi database sebelum memeriksa kode aplikasi.

`.dockerignore` mengecualikan `.env` dan `.env.*` dari build context. `.env.example` hanya berisi placeholder dan tidak boleh diisi dengan credential nyata.
