
# CLO2_TB_KPL — Sistem Manajemen Apotek

## Deskripsi Singkat
Aplikasi sistem manajemen apotek berbasis web (PHP + MySQL) yang mendukung pengelolaan **kategori**, **supplier**, **obat**, **riwayat transaksi**, dan **transaksi**. Semua fitur diimplementasikan sebagai **REST API** yang dipanggil oleh frontend secara terpisah — tidak ada akses database langsung dari halaman view.

## Fitur Utama
- CRUD lengkap untuk: Kategori, Supplier, Obat, Transaksi, Riwayat Transaksi
- Keranjang belanja (cart) berbasis session
- Autentikasi login/logout dengan `password_hash`/`password_verify`
- Design by Contract (DbC) formal di seluruh model
- Table-driven validation untuk semua entitas
- Performance benchmarking dengan `microtime()`
- Parameterized generic repository (base class reusable)

## Struktur Proyek
```
├─ backend/
│   ├─ api/             # REST endpoint (auth, obat, kategori, supplier, transaksi, riwayat)
│   ├─ config/          # (reserved)
│   ├─ helpers/
│   │   ├─ DbC.php                    # Design by Contract (precondition, postcondition, invariant)
│   │   ├─ TableDrivenValidator.php   # Table-driven validation rules
│   │   ├─ Performance.php            # microtime() benchmarking helper
│   │   ├─ PerformanceLogger.php      # Simpan hasil benchmark ke logs/
│   │   └─ ParameterizedRepository.php # Generic CRUD base class
│   ├─ middleware/
│   │   └─ AuthMiddleware.php         # Validasi session login
│   ├─ models/
│   │   ├─ Database.php
│   │   ├─ Obat.php
│   │   ├─ Kategori.php
│   │   ├─ Supplier.php
│   │   ├─ Transaksi.php
│   │   ├─ RiwayatTransaksi.php
│   │   └─ User.php
│   └─ routes/          # (reserved, routing via API)
├─ frontend/
│   ├─ pages/           # View PHP (dashboard, kategori, supplier, transaksi, riwayat, login)
│   ├─ js/              # JavaScript untuk interaksi frontend
│   ├─ css/             # Stylesheet
│   └─ uploads/         # Gambar obat
├─ tests/
│   ├─ RunAllTests.php              # Test runner utama (tanpa PHPUnit)
│   ├─ ObatTest.php
│   ├─ KategoriTest.php
│   ├─ SupplierTest.php
│   ├─ TransaksiTest.php
│   ├─ RiwayatTransaksiTest.php
│   ├─ LoginTest.php
│   ├─ PerformanceTest.php          # Benchmark semua model
│   └─ TableDrivenValidatorTest.php # Test tabel validasi
├─ logs/                # Output log performance (auto-generated JSON)
├─ migration.sql        # Skrip migrasi database
└─ README.md
```

## Tim & Teknik Konstruksi
| Nama | Teknik 1 | Teknik 2 | File Implementasi |
|------|----------|----------|-------------------|
| Steven | Code Reuse / Library | Runtime Configuration | `helpers/Performance.php`, `helpers/PerformanceLogger.php`, `models/Database.php` |
| Jovanov | API | Parameterization / Generics | `api/*.php` (8 endpoint), `helpers/ParameterizedRepository.php` |
| Hafiz | Automata | Table-driven construction | `frontend/js/modal.js` (UI state), `helpers/TableDrivenValidator.php` |
| Zakaria | Table-driven construction | Runtime Configuration | `helpers/TableDrivenValidator.php`, `middleware/AuthMiddleware.php` |
| Fatan | Parameterization / Generics | API | `helpers/ParameterizedRepository.php`, `helpers/DbC.php` |

## Unit Testing
Test tanpa PHP/Unit:
```bash
php tests/RunAllTests.php
```

Modul yang diuji:
- `ObatTest.php` — CRUD + validasi + DbC precondition
- `KategoriTest.php` — CRUD + validasi
- `SupplierTest.php` — CRUD + validasi
- `TransaksiTest.php` — validasi + create dengan riwayat (end-to-end)
- `RiwayatTransaksiTest.php` — CRUD + DbC
- `LoginTest.php` — autentikasi + bcrypt + validasi
- `PerformanceTest.php` — benchmark semua model + PerformanceLogger
- `TableDrivenValidatorTest.php` — semua rule types + edge cases

## Performance Testing
```bash
php tests/RunAllTests.php
# Hasil benchmark tersimpan di logs/performance_YYYY-MM-DD.json
```

Hasil benchmark aktual (rata-rata per operasi):
| Operasi | avg (ms) |
|---------|----------|
| Kategori::getAll() | ~0.47 ms |
| Obat::getAll() | ~0.55 ms |
| Supplier::getAll() | ~0.31 ms |
| RiwayatTransaksi::getAll() | ~0.38 ms |
| TableDrivenValidator::validate() | ~0.003 ms |

## Design by Contract (DbC)
Implementasi formal di `backend/helpers/DbC.php`:
- `DbC::require($condition, $message)` — **Precondition**: validasi input sebelum eksekusi
- `DbC::ensure($condition, $message)` — **Postcondition**: verifikasi hasil setelah eksekusi
- `DbC::invariant($condition, $message)` — **Invariant**: konsistensi state objek
- `DbC::requireNonEmpty($value, $field)` — helper string tidak kosong
- `DbC::requirePositive($value, $field)` — helper nilai non-negatif
- `DbC::requireValidId($id, $field)` — helper ID > 0

Digunakan di semua model: `Obat`, `Kategori`, `Supplier`, `Transaksi`, `RiwayatTransaksi`.

## Secure Coding
- **Prepared statements PDO** di semua query — aman dari SQL injection
- **`password_hash`/`password_verify`** untuk autentikasi
- **`htmlspecialchars()`** untuk semua output di view
- **Session-based auth** dengan validasi di `AuthMiddleware::check()`
- **Input validation** via `TableDrivenValidator` sebelum query dieksekusi

## Cara Menjalankan
1. Clone repo `CLO2_TB_KPL`
2. Import `migration.sql` ke MySQL (pastikan database `apotek_db` sudah ada)
3. Pastikan XAMPP/Apache + MySQL berjalan
4. Akses: `http://localhost/TUBES_KPL_NULL/frontend/pages/login.php`
5. Jalankan test: `php tests/RunAllTests.php`

