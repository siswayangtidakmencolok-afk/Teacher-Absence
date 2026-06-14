# AGENTS.MD — Absensi Guru Project

> File ini dibuat untuk membantu AI Agent (dan developer) memahami keseluruhan struktur,
> arsitektur, alur kerja, dan skema database proyek **Absensi Guru**.
> Baca file ini sebelum membuat perubahan apa pun agar hemat token dan tidak membuat keputusan yang salah.

---

## 1. Ringkasan Proyek

**Absensi Guru** adalah aplikasi web berbasis Laravel 10 untuk mencatat kehadiran guru (absensi masuk & pulang),
pengajuan izin/sakit, dan pengelolaan data oleh admin sekolah.

- **Backend:** Laravel 10 (PHP 8.2)
- **Frontend:** Blade + TailwindCSS + Vanilla JS
- **Database:** PostgreSQL (migrasi dari MySQL — selesai Juni 2026)
- **Repo GitHub:** https://github.com/siswayangtidakmencolok-afk/Teacher-Absence

---

## 2. Struktur Direktori

```
absensi-guru/
├── app/
│   ├── Console/
│   │   └── Kernel.php                  # Perintah artisan & scheduled tasks
│   ├── Exceptions/
│   │   └── Handler.php                 # Global exception handler
│   ├── Exports/
│   │   └── AbsensiExport.php           # Export laporan absensi ke Excel (maatwebsite)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php      # Login, logout, register, reset password
│   │   │   ├── Controller.php          # Base controller (berisi helper sidebarMenu())
│   │   │   ├── admin/
│   │   │   │   ├── HomeController.php         # Dashboard admin
│   │   │   │   ├── DataAbsensiController.php  # Lihat & export rekap absensi
│   │   │   │   ├── DataGuruController.php     # CRUD data guru (user role=USER)
│   │   │   │   └── DataLokasiController.php   # Kelola lokasi absensi (lat/lng)
│   │   │   └── user/
│   │   │       ├── HomeController.php         # Halaman home guru (status absen hari ini)
│   │   │       ├── AbsenController.php        # Check-in & check-out (selfie + GPS)
│   │   │       ├── IzinController.php         # Pengajuan izin/sakit oleh guru
│   │   │       ├── ProfileController.php      # Edit profil guru
│   │   │       └── RiwayatController.php      # Rekap riwayat absensi bulanan
│   │   ├── Kernel.php                  # HTTP kernel & middleware stack
│   │   └── Middleware/
│   │       ├── AdminMiddleware.php     # Guard: hanya role=ADMIN yang bisa akses
│   │       ├── UserMiddleware.php      # Guard: hanya role=USER/ADMIN yang bisa akses
│   │       ├── Authenticate.php        # Redirect ke login jika belum auth
│   │       └── RedirectIfAuthenticated.php  # Redirect jika sudah login
├── database/
│   ├── migrations/                     # Semua migrasi tabel (lihat bagian 5)
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── UserSeeder.php              # Seed akun admin default
├── resources/
│   └── views/
│       ├── auth/                       # login.blade.php, register.blade.php, forgot-password.blade.php
│       ├── admin/
│       │   ├── dashboard.blade.php
│       │   ├── absensi/index.blade.php
│       │   ├── guru/data-guru.blade.php, edit.blade.php
│       │   ├── izin/index.blade.php
│       │   └── location/index.blade.php
│       ├── user_app/
│       │   ├── home.blade.php
│       │   ├── bottom_bar.blade.php    # Navigasi bawah (bottom navigation)
│       │   ├── absen/checkin_out.blade.php, sukses.blade.php
│       │   ├── izin/index.blade.php, sukses.blade.php
│       │   ├── profile/profile.blade.php, edit.blade.php
│       │   └── riwayat/index.blade.php
│       └── includes/                   # Partial views: header, navbar, sidebar, footer, script
├── routes/
│   ├── web.php                         # Semua web routes (lihat bagian 6)
│   └── api.php                         # API routes (untuk Sanctum)
├── config/
│   ├── database.php                    # Konfigurasi koneksi DB (default: pgsql)
│   └── ...                             # Config standar Laravel lainnya
├── .env                                # Konfigurasi environment aktif (TIDAK di-commit)
├── .env.example                        # Template konfigurasi (aman di-commit)
├── agents.md                           # ← FILE INI
└── cara_kerja.md                       # Dokumentasi alur kerja & update log
```

---

## 3. Models & Relasi

### `App\Models\User`
**Tabel:** `users`

| Kolom               | Tipe                  | Keterangan                         |
|---------------------|-----------------------|------------------------------------|
| `id`                | bigint PK             | Auto increment                     |
| `name`              | string                | Username / nama singkat            |
| `full_name`         | string                | Nama lengkap                       |
| `jenis_kelamin`     | enum(`L`,`P`)         | Laki-laki / Perempuan              |
| `alamat`            | string                | Alamat rumah                       |
| `no_hp`             | string                | Nomor HP                           |
| `role`              | enum(`ADMIN`,`USER`)  | Default: `USER`                    |
| `profile_photo_path`| string (nullable)     | Path foto profil                   |
| `email`             | string (unique)       | Email untuk login                  |
| `password`          | string (hashed)       | Bcrypt password                    |
| `remember_token`    | string (nullable)     | Token "ingat saya"                 |
| `created_at`        | timestamp             |                                    |
| `updated_at`        | timestamp             |                                    |

**Relasi:**
- `hasMany(Absensi::class)` → seorang guru punya banyak catatan absensi
- `hasMany(Izin::class)` → seorang guru bisa punya banyak pengajuan izin

---

### `App\Models\Absensi`
**Tabel:** `absensis`

| Kolom        | Tipe                    | Keterangan                             |
|--------------|-------------------------|----------------------------------------|
| `id`         | bigint PK               |                                        |
| `user_id`    | bigint FK → `users.id`  | Cascade update & delete                |
| `jenis`      | enum(`MASUK`,`PULANG`)  | Tipe absensi                           |
| `photo_path` | string                  | Path foto selfie (storage/public)      |
| `lat`        | decimal(15,10)          | Latitude lokasi saat absen             |
| `lng`        | decimal(15,10)          | Longitude lokasi saat absen            |
| `izin`       | tinyint (default 0)     | Penanda terkait izin (0 = tidak ada)   |
| `izin_id`    | bigint FK → `izins.id` (nullable) | Referensi izin terkait       |
| `created_at` | timestamp               |                                        |
| `updated_at` | timestamp               |                                        |

**Relasi:**
- `belongsTo(User::class)` → setiap absensi milik satu guru

---

### `App\Models\Izin`
**Tabel:** `izins`

| Kolom        | Tipe                          | Keterangan                            |
|--------------|-------------------------------|---------------------------------------|
| `id`         | bigint PK                     |                                       |
| `user_id`    | bigint FK → `users.id`        | Cascade update & delete               |
| `tanggal`    | date                          | Tanggal izin yang diajukan            |
| `keterangan` | enum(`sakit`,`izin`)          | Jenis pengajuan                       |
| `photo_path` | string                        | Path surat keterangan (PDF/foto)      |
| `catatan`    | string                        | Catatan tambahan dari guru            |
| `status`     | enum(`belum`,`sudah`,`tolak`) | Default: `belum`, diubah admin        |
| `created_at` | timestamp                     |                                       |
| `updated_at` | timestamp                     |                                       |

---

### `App\Models\Location`
**Tabel:** `locations`

| Kolom    | Tipe      | Keterangan                          |
|----------|-----------|-------------------------------------|
| `id`     | bigint PK |                                     |
| `lat`    | string    | Latitude lokasi sekolah (referensi) |
| `lng`    | string    | Longitude lokasi sekolah (referensi)|

---

## 4. Controllers

### Auth
| Controller       | Method        | Keterangan                          |
|------------------|---------------|-------------------------------------|
| `AuthController` | `index()`     | Tampil halaman login                |
|                  | `doLogin()`   | Proses login email+password         |
|                  | `logout()`    | Logout & invalidasi session         |
|                  | `register()`  | Registrasi akun guru baru           |
|                  | `reset()`     | Kirim link reset password via email |

### Admin
| Controller               | Method              | Keterangan                              |
|--------------------------|---------------------|-----------------------------------------|
| `admin\HomeController`   | `index()`           | Dashboard: jumlah guru, absen hari ini  |
| `admin\DataGuruController`| `index()`          | List semua guru (role=USER)             |
|                          | `detailGuru($id)`   | Form edit data guru                     |
|                          | `updateGuru()`      | Simpan perubahan data guru              |
|                          | `tambahGuru()`      | Form tambah guru baru                   |
|                          | `storeTambahGuru()` | Simpan guru baru ke DB                  |
|                          | `delete()`          | Hapus data guru                         |
| `admin\DataAbsensiController`| `index()`      | Rekap absensi dengan filter tanggal/jenis|
|                          | `report()`          | Export rekap bulanan ke Excel           |
| `admin\DataLokasiController` | `index()`      | Tampil & kelola lokasi GPS sekolah      |
|                          | `store()`           | Simpan koordinat lokasi sekolah         |

### User (Guru)
| Controller               | Method              | Keterangan                                   |
|--------------------------|---------------------|----------------------------------------------|
| `user\HomeController`    | `index()`           | Redirect ke dashboard/home sesuai role       |
|                          | `userHome()`        | Halaman home guru: status masuk & pulang     |
| `user\AbsenController`   | `checkInOutIndex()` | Halaman kamera selfie + GPS (masuk/pulang)   |
|                          | `store()`           | Simpan data absensi (foto base64 + lat/lng)  |
|                          | `absenSukses()`     | Halaman konfirmasi sukses absen              |
| `user\IzinController`    | `index()`           | List pengajuan izin milik guru               |
|                          | `store()`           | Buat pengajuan izin/sakit baru               |
|                          | `adminIndex()`      | (Admin) List semua pengajuan izin            |
|                          | `update()`          | (Admin) Update status izin: sudah/tolak      |
| `user\ProfileController` | `index()`           | Tampil profil guru                           |
|                          | `update()`          | Form edit profil                             |
|                          | `goUpdate()`        | Simpan perubahan profil                      |
| `user\RiwayatController` | `index()`           | Rekap absensi bulanan guru (kalender)        |

---

## 5. Middleware

| Middleware              | Guard                                               |
|-------------------------|-----------------------------------------------------|
| `AdminMiddleware`       | Role `ADMIN` — akses route grup `/dashboard`, dll.  |
| `UserMiddleware`        | Role `USER` atau `ADMIN` — akses route grup `/home` |
| `Authenticate`          | Redirect ke `/login` jika belum login               |
| `RedirectIfAuthenticated` | Redirect ke `/home` jika sudah login             |

---

## 6. Routes (Web)

### Publik (tanpa auth)
| Method | URI             | Controller/Action          | Nama Route       |
|--------|-----------------|----------------------------|------------------|
| GET    | `/login`        | `AuthController@index`     | `login`          |
| POST   | `/login`        | `AuthController@doLogin`   | `doLogin`        |
| GET    | `/logout`       | `AuthController@logout`    | `logout`         |
| GET    | `/register`     | (view langsung)            | `register.show`  |
| POST   | `/register`     | `AuthController@register`  | `doRegister`     |
| GET    | `/forgot`       | (view langsung)            | `password.reset` |
| POST   | `/reset`        | `AuthController@reset`     | `pass-reset`     |

### User/Guru (middleware: `auth` + `user`)
| Method | URI                   | Controller@Method               | Nama Route        |
|--------|-----------------------|---------------------------------|-------------------|
| GET    | `/`                   | `user\HomeController@index`     | `main`            |
| GET    | `/home`               | `user\HomeController@userHome`  | `home`            |
| GET    | `/profil`             | `ProfileController@index`       | `profile`         |
| GET    | `/edit-profile`       | `ProfileController@update`      | `update-profile`  |
| POST   | `/edit-profile`       | `ProfileController@goUpdate`    | `go-update-profile`|
| GET    | `/absen/{jenis}`      | `AbsenController@checkInOutIndex`| `checkInOut`     |
| POST   | `/absen/{jenis}`      | `AbsenController@store`         | `storeAbsen`      |
| GET    | `/absen/{jenis}/sukses`| `AbsenController@absenSukses`  | `absenSuccess`    |
| GET    | `/izin`               | `IzinController@index`          | `izin.index`      |
| POST   | `/submit_izin`        | `IzinController@store`          | —                 |
| GET    | `/izin/sukses`        | `IzinController@successIndex`   | `izin_success`    |
| GET    | `/riwayat`            | `RiwayatController@index`       | `riwayat`         |

### Admin (middleware: `auth` + `admin`)
| Method | URI               | Controller@Method                    | Nama Route       |
|--------|-------------------|--------------------------------------|------------------|
| GET    | `/dashboard`      | `admin\HomeController@index`         | `dashboard`      |
| GET    | `/data-guru`      | `DataGuruController@index`           | `data-guru`      |
| GET    | `/data-guru/{id}` | `DataGuruController@detailGuru`      | `detail-guru`    |
| POST   | `/update-guru`    | `DataGuruController@updateGuru`      | `update-guru`    |
| GET    | `/tambah-guru`    | `DataGuruController@tambahGuru`      | `tambah-guru`    |
| POST   | `/tambah-guru`    | `DataGuruController@storeTambahGuru` | `post.tambah-guru`|
| POST   | `/delete-guru`    | `DataGuruController@delete`          | `guru.delete`    |
| GET    | `/data-absensi`   | `DataAbsensiController@index`        | `data-absensi`   |
| GET    | `/data-lokasi`    | `DataLokasiController@index`         | `data.lokasi`    |
| POST   | `/tambah-lokasi`  | `DataLokasiController@store`         | `add.location`   |
| GET    | `/data-izin`      | `IzinController@adminIndex`          | `data.izin`      |
| POST   | `/data-izin`      | `IzinController@update`              | `izin.ubah`      |
| POST   | `/export-excel`   | `DataAbsensiController@report`       | `export.excel`   |

---

## 7. Konfigurasi Database (PostgreSQL)

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=absesnsi_guru
DB_USERNAME=guru
DB_PASSWORD=guru12345
```

Ekstensi PHP yang dibutuhkan (sudah diaktifkan di `C:\xampp\php\php.ini`):
- `pdo_pgsql`
- `pgsql`

---

## 8. Packages Composer Penting

| Package                 | Versi  | Fungsi                                    |
|-------------------------|--------|-------------------------------------------|
| `laravel/framework`     | ^10.10 | Core framework                            |
| `laravel/sanctum`       | ^3.3   | API token authentication                  |
| `maatwebsite/excel`     | ^3.1   | Export rekap absensi ke file `.xlsx`      |
| `guzzlehttp/guzzle`     | ^7.2   | HTTP client                               |
| `laravel/tinker`        | ^2.8   | REPL untuk debugging                      |

---

## 9. Catatan Penting untuk AI Agent

> [!WARNING]
> **Query MySQL vs PostgreSQL**: `user/HomeController.php` menggunakan raw SQL.
> Pastikan syntax selalu PostgreSQL-compatible:
> - Gunakan `CURRENT_DATE` bukan `CURDATE()`
> - Gunakan `INTERVAL '1 day'` bukan `INTERVAL 1 DAY`
> - PostgreSQL **tidak support** `enum` di `alter table` tanpa type casting khusus

> [!NOTE]
> **Upload File**: Selfie absen disimpan di `storage/app/public/images/`.
> Surat izin disimpan di `storage/app/public/izin/`.
> Akses publik via `Storage::url(...)` dan link simbolik `php artisan storage:link`.

> [!NOTE]
> **Role Sistem**: Hanya ada 2 role — `ADMIN` dan `USER`.
> Guru = `USER`, Kepala Sekolah/Pengelola = `ADMIN`.
> Seeder default ada di `database/seeders/UserSeeder.php`.
