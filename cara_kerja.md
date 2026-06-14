# CARA KERJA & UPDATE LOG — Absensi Guru

---

## 1. Cara Menjalankan Project Lokal

### Prasyarat
- PHP 8.2 (XAMPP) — dengan ekstensi `pdo_pgsql` dan `pgsql` aktif
- PostgreSQL (sudah terinstall dan berjalan di port 5432)
- Composer
- Node.js & NPM

### Langkah Setup

```bash
# 1. Clone atau buka folder project
cd absensi-guru

# 2. Install dependencies PHP
composer install

# 3. Install dependencies Node
npm install

# 4. Copy file .env (jika belum ada)
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Buat database di PostgreSQL terlebih dahulu (jalankan di psql)
# CREATE USER guru WITH PASSWORD 'guru12345';
# CREATE DATABASE absesnsi_guru OWNER guru;

# 7. Jalankan migrasi tabel
php artisan migrate

# 8. (Opsional) Seed data admin awal
php artisan db:seed --class=UserSeeder

# 9. Buat symlink untuk storage (foto selfie & surat izin)
php artisan storage:link

# 10. Compile assets frontend
npm run dev

# 11. Jalankan server
php artisan serve
```

Akses di browser: `http://localhost:8000`

---

## 2. Instalasi PostgreSQL (Jika Belum Ada)

> Proyek ini sudah dikonfigurasi untuk PostgreSQL. Jika PostgreSQL belum terinstall di Windows:

1. Download installer dari: https://www.postgresql.org/download/windows/
2. Install dengan default settings (port 5432)
3. Buat user & database seperti di langkah 6 di atas
4. Ekstensi PHP sudah diaktifkan: `pdo_pgsql` dan `pgsql` di `C:\xampp\php\php.ini`

---

## 3. Alur Kerja Sistem

### 3.1 Alur Login
```
User buka /login
  → Masukkan email & password
  → AuthController@doLogin() → Auth::attempt()
  → Jika sukses: redirect ke /home
  → HomeController@index() cek role
      → ADMIN: redirect /dashboard
      → USER:  redirect /home (halaman guru)
```

### 3.2 Alur Absensi Masuk / Pulang (Guru)
```
Guru buka /absen/masuk atau /absen/pulang
  → AbsenController@checkInOutIndex()
    → Tampil halaman kamera + peta (checkin_out.blade.php)
    → Guru ambil foto selfie (kamera browser → base64)
    → GPS browser ambil koordinat lat/lng
    → POST ke /absen/{jenis}
  → AbsenController@store()
    → Decode foto base64 → simpan ke storage/public/images/
    → Buat record Absensi baru (user_id, jenis, photo_path, lat, lng)
    → Simpan ke DB → redirect ke /absen/{jenis}/sukses
```

### 3.3 Alur Pengajuan Izin/Sakit (Guru)
```
Guru buka /izin
  → IzinController@index() → tampil daftar izin milik guru
  → Guru klik "Ajukan Izin"
  → Isi form: tanggal, keterangan (sakit/izin), catatan, upload surat
  → POST /submit_izin
  → IzinController@store()
    → Upload file surat ke storage/public/izin/
    → Buat record Izin baru (status='belum')
    → Redirect ke /izin
```

### 3.4 Alur Persetujuan Izin (Admin)
```
Admin buka /data-izin
  → IzinController@adminIndex()
    → Tampil dua list: pengajuan belum diproses & sudah diproses
  → Admin klik "Setujui" atau "Tolak"
  → POST /data-izin dengan {id, status}
  → IzinController@update()
    → Update Izin.status → 'sudah' atau 'tolak'
    → Redirect kembali ke /data-izin
```

### 3.5 Alur Rekap Absensi (Admin)
```
Admin buka /data-absensi
  → DataAbsensiController@index()
    → Filter by tanggal (hari ini default) & jenis (masuk/pulang/semua)
    → Query JOIN absensis + users
    → Tampil tabel rekap dengan foto & lokasi
  → Admin klik "Export Excel" → pilih bulan
  → POST /export-excel
  → DataAbsensiController@report()
    → Generate file .xlsx via AbsensiExport (Maatwebsite)
    → Download otomatis
```

---

## 4. Skema Relasi Database

```
users (1) ──────────────── (many) absensis
users (1) ──────────────── (many) izins
izins (1) ──────────────── (many) absensis [izin_id nullable]
locations (standalone) ─── referensi GPS sekolah
```

---

## 5. Catatan Migrasi dari MySQL ke PostgreSQL

**Tanggal migrasi:** Juni 2026

### Perubahan yang sudah dilakukan:

| File | Perubahan |
|------|-----------|
| `.env` | `DB_CONNECTION=pgsql`, port 5432, credentials baru |
| `.env.example` | Diupdate ke konfigurasi PostgreSQL |
| `config/database.php` | Default connection sudah support `pgsql` |
| `php.ini` | Ekstensi `pdo_pgsql` dan `pgsql` diaktifkan |
| `database/migrations/2024_07_01_074851_create_absensi_table.php` | `izin_id` dibuat `nullable()` |
| `app/Http/Controllers/user/HomeController.php` | Raw SQL: `CURDATE()` → `CURRENT_DATE`, `INTERVAL 1 DAY` → `INTERVAL '1 day'` |

### Hal yang perlu diperhatikan ke depan:
- PostgreSQL **case-sensitive** pada nama kolom di raw SQL — selalu gunakan lowercase
- PostgreSQL tidak support `enum` di `ALTER TABLE` langsung — gunakan `varchar` + constraint jika perlu modifikasi
- `auto_increment` di MySQL = `SERIAL` / `BIGSERIAL` di PostgreSQL (Laravel otomatis menangani lewat `->id()`)

---

## 6. Update & Penambahan Fitur

### [2026-06-14] — Migrasi Database & Sinkronisasi Git
**Oleh:** AI Agent (Antigravity)

**Yang dilakukan:**
- Sinkronisasi repositori lokal dengan GitHub remote (`origin/main`)
- Commit semua file lokal ke branch `master`, kemudian checkout ke `main` dari remote
- Aktifkan ekstensi PHP `pdo_pgsql` dan `pgsql` di `C:\xampp\php\php.ini`
- Update `.env` dan `.env.example` ke konfigurasi PostgreSQL:
  - Database: `absesnsi_guru`
  - Username: `guru`
  - Password: `guru12345`
  - Port: `5432`
- Perbaiki migrasi `absensis` table: kolom `izin_id` sekarang `nullable()`
- Perbaiki raw SQL di `user/HomeController.php` agar kompatibel dengan PostgreSQL
- Buat file `agents.md` (dokumentasi struktur lengkap proyek)
- Buat file `cara_kerja.md` (file ini)

**Masih perlu dilakukan:**
- Install PostgreSQL di mesin lokal
- Buat user & database PostgreSQL:
  ```sql
  CREATE USER guru WITH PASSWORD 'guru12345';
  CREATE DATABASE absesnsi_guru OWNER guru;
  ```
- Jalankan `php artisan migrate`
- Jalankan `php artisan storage:link`

---

## 7. Troubleshooting Umum

| Masalah | Solusi |
|---------|--------|
| `PDOException: could not find driver` | Aktifkan `pdo_pgsql` di `php.ini`, restart XAMPP |
| `SQLSTATE: password authentication failed` | Periksa `DB_USERNAME` & `DB_PASSWORD` di `.env` |
| `relation "users" does not exist` | Jalankan `php artisan migrate` |
| Foto absen tidak tampil | Jalankan `php artisan storage:link` |
| `Class not found` setelah git pull | Jalankan `composer dump-autoload` |
| `enum` error saat migrate ke PostgreSQL | PostgreSQL tidak support ubah nilai enum; buat migration baru dengan type `varchar` |
