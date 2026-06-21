# SRRK MVC PHP

Project ini memakai pola MVC PHP sederhana untuk sistem reservasi ruangan.

## Ringkasan Revisi

- Admin tidak bisa lagi mengajukan reservasi ruangan.
- Menu admin untuk `Ajukan Reservasi` dan `Reservasi Saya` sudah dihapus.
- Route `reservation_form.php`, `my_reservations.php`, dan `reservation_cancel.php` sekarang hanya bisa diakses oleh role `dosen` dan `mahasiswa`.
- Alur backend reservasi disesuaikan. Semua reservasi baru dari dosen dan mahasiswa masuk dengan status `pending`.
- Koneksi database dipindahkan ke PDO agar bisa memakai PostgreSQL Neon.
- File konfigurasi Vercel sudah ditambahkan melalui `vercel.json` dan entry point `api/index.php`.
- File SQL PostgreSQL untuk Neon sudah tersedia di `sql/db_reservasi_ruangan_postgresql.sql`.
- File SQL utama `sql/db_reservasi_ruangan.sql` sekarang memakai format PostgreSQL.
- File SQL MySQL lama tetap disimpan di `sql/db_reservasi_ruangan_mysql_legacy.sql`.

## Struktur Folder

```text
app/
  Controllers/
  Core/
  Models/
  Views/
api/
assets/
config/
public/
routes/
sql/
uploads/
```

## Cara Pakai dengan Neon DB

1. Buat database PostgreSQL di Neon.
2. Buka SQL Editor Neon.
3. Import atau jalankan isi file:

```text
sql/db_reservasi_ruangan_postgresql.sql
```

4. Ambil connection string Neon dengan format seperti:

```text
postgresql://USER:PASSWORD@HOST.neon.tech/DBNAME?sslmode=require
```

5. Di Vercel, buka Project Settings lalu Environment Variables.
6. Tambahkan:

```text
DATABASE_URL=postgresql://USER:PASSWORD@HOST.neon.tech/DBNAME?sslmode=require
```

7. Deploy project ke Vercel.

## Cara Pakai Lokal dengan PostgreSQL

Buat file environment sendiri atau set environment variable berikut:

```text
DB_DRIVER=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_NAME=db_reservasi_ruangan
DB_USER=postgres
DB_PASS=password_kamu
DB_SSLMODE=prefer
```

Lalu jalankan project seperti biasa melalui server PHP lokal.

## Cara Pakai Lokal dengan MySQL Lama

Project masih bisa diarahkan ke MySQL lama dengan environment berikut:

```text
DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_NAME=db_reservasi_ruangan
DB_USER=root
DB_PASS=
```

Gunakan SQL lama:

```text
sql/db_reservasi_ruangan_mysql_legacy.sql
```

## Akun Demo

- Admin: `admin@example.com` / `admin123`
- Dosen: `dosen@example.com` / `dosen123`
- Mahasiswa: `mahasiswa@example.com` / `mahasiswa123`

## Catatan Penting Deploy Vercel

Vercel untuk PHP memakai community runtime `vercel-php`. Konfigurasi sudah disiapkan di `vercel.json`.

CSS dan JS sudah disalin ke `public/assets/` agar desain tetap terbaca di Vercel. Folder `assets/` tetap dipertahankan untuk local.

Jangan upload `.env` berisi password asli ke GitHub. Isi `DATABASE_URL` asli di Vercel melalui menu Project Settings -> Environment Variables.

Folder `uploads/` tetap dipertahankan agar desain dan alur lama tidak rusak. Namun pada serverless seperti Vercel, penyimpanan file lokal tidak ideal untuk jangka panjang. Untuk produksi, dokumen upload sebaiknya dipindahkan ke storage eksternal seperti S3, Supabase Storage, Cloudinary, atau layanan sejenis.
