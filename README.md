# Project Wiyung Surabaya (CodeIgniter 4)

README ini fokus untuk menyamakan pemahaman struktur proyek dan **konvensi penamaan file** agar tidak terjadi perbedaan nama (terutama masalah *case-sensitive* saat deploy ke Linux).

## Struktur folder & file penting

### Root project
- `app/` → kode utama aplikasi (Controller, Model, View, Config, dst.)
- `public/` → **document root** web server (entrypoint aplikasi ada di sini)
- `writable/` → folder runtime (logs, cache, session, upload) — harus writable
- `vendor/` → dependency Composer (jangan diedit manual)
- `.env` → konfigurasi environment (baseURL, database, dll.) **jangan commit**
- `composer.json` / `composer.lock` → daftar dependency & versi yang terkunci
- `spark` → CLI CodeIgniter (migrate, seed, cache, dll.)
- `tests/` → unit/integration test (opsional)

### `app/` (yang paling sering disentuh)
- `app/Config/Routes.php` → daftar route aplikasi (mapping URL → Controller::method)
- `app/Controllers/` → endpoint/handler request
- `app/Models/` → akses database & aturan data
- `app/Views/` → template tampilan
- `app/Database/` → migrations & seeds
- `app/Filters/` → filter request (auth, csrf, dll.)
- `app/Helpers/` → helper function
- `app/Libraries/` → library custom (class)
- `app/Language/` → file bahasa (i18n)

### `public/`
- `public/index.php` → entrypoint aplikasi (web server **wajib** mengarah ke folder `public/`)
- `public/.htaccess` → aturan rewrite (Apache)

## Konvensi penamaan (wajib konsisten)

### Aturan umum
1. **Nama file = nama class (case harus sama)** untuk semua class PHP yang di-autoload (PSR-4).
2. Gunakan **PascalCase** untuk nama file class: `Antrian.php`, `AntrianModel.php`.
3. Hindari spasi, gunakan huruf/angka/underscore saja.
4. Jika deploy ke Linux: `Antrian.php` ≠ `antrian.php` (beda file). Walau di Windows sering “terlihat sama”.

### Controllers (`app/Controllers/`)
- File: `PascalCase.php`
- Class: `PascalCase`
- Namespace: `App\Controllers`
- Method: `camelCase()`
- Contoh:
  - File `app/Controllers/Antrian.php` → class `Antrian`
  - Route: `$routes->get('master-data/belum-dipanggil', 'Dashboard::dataBelumDipanggil');`

### Models (`app/Models/`)
- File: `NamaModel.php` (akhiran **Model** konsisten)
- Class: `NamaModel`
- Namespace: `App\Models`
- Contoh:
  - File `app/Models/AntrianModel.php` → class `AntrianModel`

### Views (`app/Views/`)
- Folder: lowercase (contoh: `auth/`, `dashboard/`)
- File view: lowercase + `snake_case.php` (contoh: `data_belum_dipanggil.php`)
- Partial/layout: awali dengan underscore (contoh: `_layout.php`)
- Pemanggilan view **harus sesuai path**:
  - `return view('dashboard/index');` → `app/Views/dashboard/index.php`

### Routes (`app/Config/Routes.php`)
- URL gunakan **kebab-case** untuk keterbacaan: `master-data/belum-dipanggil`
- Target controller/method gunakan format CI4: `Controller::method`
- Hindari nama route yang “mirip-mirip” beda huruf besar/kecil.

### Database (`app/Database/`)
- `Migrations/` → gunakan format nama migration CI4 (timestamp + deskripsi), konsisten dengan tabel yang dibuat.
- `Seeds/` → nama file class PascalCase, mis. `AntrianSeeder.php`.

## Checklist saat menambah fitur baru
1. Buat Controller di `app/Controllers/` (PascalCase).
2. Tambah route di `app/Config/Routes.php` (URL kebab-case, target `Controller::method`).
3. Buat Model di `app/Models/` (akhiran `Model`).
4. Buat view di `app/Views/` (folder lowercase, file snake_case).
5. Pastikan semua referensi (route, view(), namespace, class name) **case-nya sama persis**.

## Catatan singkat environment
- File konfigurasi utama: `.env` (baseURL, DB, dll.)
- Pastikan server mengarah ke `public/` (bukan root project).
