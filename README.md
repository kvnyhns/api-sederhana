<h1 align="center">User & Hobby Management API</h1>

<p align="center">Technical Test - Laravel Blade CRUD dan REST API dengan JWT Authentication</p>

<p align="center">
	<strong>Laravel 13</strong> &middot;
	<strong>PHP 8.5</strong> &middot;
	<strong>MySQL 8.4</strong> &middot;
	<strong>Docker Compose</strong>
</p>

## Technical Test: User dan Hobby CRUD

Aplikasi ini dibuat untuk Technical Test proses rekrutmen. Fitur utama yang tersedia:

- CRUD User menggunakan halaman Blade.
- Relasi one-to-many antara User dan Hobby.
- Satu User dapat memiliki banyak Hobby.
- CRUD User melalui REST API.
- JWT Authentication untuk melindungi endpoint API.
- Validasi server-side untuk User dan Hobby.
- Normalisasi nama Hobby dan pencegahan duplikasi Hobby pada User yang sama.
- Penghapusan Hobby secara cascade ketika User dihapus.

## Teknologi

- Laravel 13
- PHP 8.5
- MySQL 8.4
- Docker Compose / Laravel Sail
- Blade
- JWT Authentication melalui `tymon/jwt-auth` 2.3.0
- PHPUnit

## Persyaratan

Pastikan perangkat sudah memiliki:

- Docker Desktop atau Docker Engine dengan Docker Compose.
- Composer jika ingin menjalankan perintah Composer dari host.

## Instalasi

Jalankan dari root project:

```bash
composer install
cp .env.example .env
docker compose up -d
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan jwt:secret
docker compose exec laravel.test php artisan migrate
```

File `.env` disiapkan sebelum container dijalankan. Setelah package terpasang dan container aktif, generate application key dan JWT secret di environment lokal. Jangan commit file `.env`, application key, atau JWT secret.

Aplikasi dapat diakses melalui:

```text
http://localhost:8000
```

Port dapat diubah melalui nilai `APP_PORT` pada environment lokal. Jangan commit file `.env`, application key, atau JWT secret.

## Fitur Blade

Halaman User tersedia pada:

```text
GET /users
GET /users/create
GET /users/{user}/edit
```

Workflow Blade meliputi:

- Melihat daftar User dan Hobby.
- Membuat User dengan password dan banyak Hobby.
- Menambahkan input Hobby secara dinamis melalui tombol `Add hobby`.
- Mengubah data User dan mengganti daftar Hobby. Password yang kosong atau tidak dikirim akan mempertahankan password lama.
- Menghapus User beserta seluruh Hobby terkait.
- Menampilkan pesan sukses dan validasi error.
- Konfirmasi sebelum menghapus User.

Untuk update Hobby, daftar yang dikirim dianggap sebagai daftar akhir. Hobby yang tidak dikirim akan dihapus. Jika daftar Hobby tidak dikirim pada update parsial, daftar lama tetap dipertahankan. Jika array kosong dikirim secara eksplisit (`"hobbies": []`), semua Hobby User tersebut akan dihapus.

## Endpoint API

### Endpoint publik

```text
POST /api/auth/register
POST /api/auth/login
```

### Endpoint dengan JWT

Gunakan header berikut pada endpoint yang dilindungi:

```text
Authorization: Bearer <token>
Accept: application/json
```

```text
POST   /api/auth/logout
GET    /api/users
POST   /api/users
GET    /api/users/{user}
PUT    /api/users/{user}
PATCH  /api/users/{user}
DELETE /api/users/{user}
```

Parameter `per_page` pada `GET /api/users` memiliki nilai default 15 dan maksimum 100.

## Contoh Request API

### Register

```bash
curl.exe -X POST http://localhost:8000/api/auth/register -H "Accept: application/json" -H "Content-Type: application/json" -d "{\"name\":\"Dewi Lestari\",\"email\":\"dewi.lestari@gmail.com\",\"password\":\"password123\",\"password_confirmation\":\"password123\"}"
```

Response register mengembalikan JWT. Simpan nilai `token` untuk request berikutnya.

### Login

```bash
curl.exe -X POST http://localhost:8000/api/auth/login -H "Accept: application/json" -H "Content-Type: application/json" -d "{\"email\":\"dewi.lestari@gmail.com\",\"password\":\"password123\"}"
```

### Membuat User dengan banyak Hobby

```bash
curl.exe -X POST http://localhost:8000/api/users -H "Accept: application/json" -H "Content-Type: application/json" -H "Authorization: Bearer <token>" -d "{\"name\":\"Budi Santoso\",\"email\":\"budi.santoso@gmail.com\",\"password\":\"password123\",\"password_confirmation\":\"password123\",\"hobbies\":[\"Membaca\",\"Bersepeda\"]}"
```

### Melihat Daftar User

```bash
curl.exe "http://localhost:8000/api/users?per_page=15" -H "Accept: application/json" -H "Authorization: Bearer <token>"
```

### Logout

```bash
curl.exe -X POST http://localhost:8000/api/auth/logout -H "Accept: application/json" -H "Authorization: Bearer <token>"
```

Setelah logout, token tersebut tidak dapat digunakan lagi untuk mengakses endpoint yang dilindungi.

## Validasi dan Response

- `name` wajib diisi dan maksimal 255 karakter.
- `email` wajib valid dan unik.
- Password wajib dikonfirmasi dan minimal 8 karakter saat dibuat atau diubah.
- Password kosong atau tidak dikirim saat update tidak mengubah password yang sudah tersimpan.
- Nama Hobby dinormalisasi dengan menghapus spasi di awal/akhir dan merapikan spasi berulang.
- Nama Hobby maksimal 100 karakter dan tidak boleh duplikat pada User yang sama.
- Password tidak pernah dikembalikan pada response API.
- `201` digunakan untuk pembuatan data.
- `200` digunakan untuk response sukses dan update.
- `204` digunakan untuk penghapusan dan logout.
- `401` digunakan untuk JWT yang tidak valid atau kredensial login yang salah.
- `404` digunakan jika data tidak ditemukan.
- `422` digunakan untuk validasi input.

## Testing

Jalankan test di dalam container:

```bash
docker compose exec laravel.test php artisan test
docker compose exec laravel.test vendor/bin/pint
```

Test mencakup:

- Relasi User dan Hobby.
- Normalisasi serta unique constraint Hobby.
- Cascade deletion.
- CRUD Blade.
- Register, login, logout, dan invalidasi JWT.
- Protected API CRUD.
- Validasi, pagination, response format, dan password protection.

## Catatan Scope

Halaman Blade dibuat publik sesuai ketentuan Technical Test. Pada aplikasi production, operasi User CRUD sebaiknya dilindungi autentikasi dan authorization yang sesuai.
