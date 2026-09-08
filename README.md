# Rekap Data QC

Ringkasan
---------
Repositori ini berisi aplikasi Laravel untuk mengumpulkan, memproses, dan melaporkan data berat/kualitas (bruto/tara/netto) dari sumber Excel. Fitur utama meliputi: manajemen master data (produk, mitra, pengangkut, kendaraan), importer Excel tahan banting dengan resolusi master fuzzy dan deduplikasi, UI admin berbasis Filament, serta ekspor Excel multi-sheet dengan format dan gaya siap-pakai.

Fakta singkat
------------
- Framework: Laravel ^12 ([composer.json](composer.json))
- Kebutuhan PHP: ^8.2 ([composer.json](composer.json))
- UI Admin: Filament (~4.0) (lihat `app/Filament/Resources`)
- Import/Export Excel: `maatwebsite/excel` (lihat `app/Imports` / `app/Exports`)
- Frontend: Vite + Tailwind (`package.json`)
- Catatan env default: `DB_CONNECTION=sqlite` (`.env.example`) namun kode menggunakan fitur PostgreSQL (`ILIKE`, `DATE_TRUNC`) — lihat catatan DB di bawah.

Masalah
-------
Operasional membutuhkan mekanisme yang andal untuk memasukkan file Excel dunia nyata yang kotor, membersihkan dan menormalkan master (mitra, pengangkut, kendaraan), menghitung metrik domain (susut, FFA, dobi), menghindari duplikasi, dan menghasilkan laporan Excel yang dapat langsung dipakai pemangku kepentingan.

Solusi
-------
Aplikasi ini menyediakan:
- Antarmuka admin Filament untuk mengelola master dan menjalankan aksi impor/ekspor.
- Pipeline impor (`app/Imports/RekapDataImport.php`) yang mem-parsing baris Excel, menormalkan data, meresolusikan/membuat record master dengan pencocokan fuzzy (`app/Actions/ResolveRekapMasterData.php` + `app/Helpers/KodeGenerator.php`), menolak baris tidak valid, dan mencegah duplikasi (`app/Services/RekapDataImportService.php`).
- Logika perhitungan yang dapat digunakan ulang di `app/Services/RekapDataCalculator.php` dan penegakan model di `app/Models/RekapData.php`.
- Ekspor Excel multi-sheet berformat (`app/Exports/*`, `app/Exports/Sheets/*`) yang dioptimalkan untuk laporan bisnis.

Fitur Utama
-----------
- Impor Excel (parsing tahan banting, deduplikasi, resolusi master fuzzy)
- CRUD master (Produk, Mitra, Pengangkut, Kendaraan) melalui Filament
- Agregasi/bagian bulanan (Jan–Des) di Filament
- Ekspor Excel multi-sheet (mode perusahaan / pemasok)
- Otorisasi berbasis peran dan kebijakan akses

Peran Pengguna
---------------
- `superadmin` — akses penuh
- `admin` — impor/ekspor dan manajemen data
- `staff`, `qc` — pengguna operasional (akses terbatas)

Alur Pengguna (contoh: impor)
--------------------------------
1. Admin membuka Filament → `Rincian Rekap Data` → klik `Import Excel`.
2. Pilih file .xlsx lalu submit.
3. Aksi Filament memanggil `Excel::import()` dengan `app/Imports/RekapDataImport.php`.
4. Untuk tiap baris: validasi → parse tanggal → resolusi/buat master → hitung metrik → cek duplikasi → simpan `RekapData`.
5. Filament menampilkan notifikasi berisi jumlah `inserted` dan `skipped` serta nomor dokumen terakhir.

Tumpukan Teknologi
------------------
- Backend: PHP 8.2+, Laravel ^12
- UI Admin: Filament (Resources, Pages, Tables)
- Excel: maatwebsite/excel + PhpSpreadsheet
- Frontend: Vite, TailwindCSS
- DB: relasional (migrasi tersedia). Kode menggunakan fitur PostgreSQL (mis. `ILIKE`, `DATE_TRUNC`) — perhatikan catatan DB.

Arsitektur Sistem
-----------------
- Filament UI (resources/pages) → Aksi Import/Export → Lapisan Service/Action (`app/Services`, `app/Actions`) → Eloquent Models → Database.

Desain Database
---------------
Tabel utama (ringkasan):
- `rekap_data` — catatan inti (no_dokumen, urutan_produk, tanggal, bruto_kirim, tara_kirim, netto_kebun, bruto, tara, netto, susut, susut_persen, ffa, dobi, produk_id, mitra_id, pengangkut_id, kendaraan_id). Ada constraint unik pada `['produk_id','urutan_produk']` dan index pada `tanggal`, `produk_id`, `mitra_id`, dll. Lihat `database/migrations/2026_01_05_073517_create-rekap-data-table.php`.
- `produk` — `nama_produk`, `kode_produk` (unik)
- `mitra` — `nama_mitra`, `kode_mitra` (unik), enum `tipe_mitra`
- `pengangkut` — `nama_pengangkut`, `kode` (unik)
- `kendaraan` — `no_pol`, `nama_supir`, `pengangkut_id`

ERD (mermaid)

```mermaid
erDiagram
	PRODUK ||--o{ REKAP_DATA : has
	MITRA ||--o{ REKAP_DATA : has
	PENGANGKUT ||--o{ REKAP_DATA : has
	KENDARAAN ||--o{ REKAP_DATA : has
	PENGANGKUT ||--o{ KENDARAAN : owns
	USERS ||--|| : "application users"
```

Dokumentasi Fitur (ringkasan)
----------------------------

Fitur: Impor Excel
- Tujuan: memasukkan Excel yang tidak bersih, menormalkan master, menghitung metrik, mencegah duplikasi.
- Entry: Aksi Filament di `app/Filament/Resources/RincianRekapData/Pages/ListRincianRekapData.php`.
- Pemroses: `app/Imports/RekapDataImport.php` (startRow=4, handling heading, validasi baris, parsing tanggal).
- Resolusi master: `app/Actions/ResolveRekapMasterData.php` (firstOrCreate, pencocokan fuzzy untuk `pengangkut`).
- Perhitungan: `app/Services/RekapDataCalculator.php`.
- Cek duplikasi: `app/Services/RekapDataImportService::isDuplicate()`.

Fitur: Ekspor Multi-sheet
- Tujuan: menghasilkan laporan Excel siap pakai (All, Rekap, per Pengangkut/Mitra).
- Entry: Aksi ekspor Filament di `app/Filament/Resources/RincianRekapData/Pages/ListRincianRekapData.php`.
- Kelas inti: `app/Exports/RekapDataExport.php`, `app/Exports/CompanyExport.php`, `app/Exports/SupplierLuarExport.php`, dan builder sheet di `app/Exports/Sheets/`.

Fitur: Agregasi Bulanan (Rekap Data)
- Tujuan: menampilkan agregat Jan–Des untuk tahun/produk/mitra yang dipilih.
- Implementasi: `app/Filament/Resources/RekapData/Pages/ListRekapData.php` membangun SUM per `bulan` dan menampilkan 12 baris; kolom tabel di `app/Filament/Resources/RekapData/Tables/RekapDataTable.php`.

Kode Sumber Penting
--------------------
- Pipeline impor: `app/Imports/RekapDataImport.php`
- Resolusi master & logika fuzzy: `app/Actions/ResolveRekapMasterData.php`, `app/Helpers/KodeGenerator.php`
- Perhitungan: `app/Services/RekapDataCalculator.php`, `app/Models/RekapData.php`
- Ekspor & builder: `app/Exports/*`, `app/Exports/Sheets/*`
- Resource Filament: `app/Filament/Resources/*`
- Migrasi: `database/migrations/*`

Instalasi
--------
Clone repositori dan instal dependensi:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run dev
```

Catatan:
- Jika Anda akan menggunakan PostgreSQL (direkomendasikan untuk fitur ekspor dan pencarian case-insensitive `ILIKE` serta `DATE_TRUNC`), buat database dan perbarui `.env`. `.env.example` memakai SQLite untuk percobaan lokal cepat.

Konfigurasi Lingkungan
----------------------
- Salin `.env.example` → `.env` dan atur variabel DB. Contoh PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=rekap_data_qc
DB_USERNAME=youruser
DB_PASSWORD=yourpass
```

Menyiapkan Database
--------------------
Jalankan migrasi:

```bash
php artisan migrate
```

Untuk cepat memulai dengan sqlite (development), pastikan `database/database.sqlite` ada dan `.env` menggunakan `DB_CONNECTION=sqlite`.

Menjalankan Aplikasi
--------------------
- Jalankan aplikasi:

```bash
php artisan serve
npm run dev
```

- Buka Filament Admin (default `/admin` atau panel yang dikonfigurasi) dan masuk dengan user yang sudah di-seed atau buat akun baru. (TODO: tambahkan instruksi seeding jika diperlukan.)

Pengujian
---------
Jalankan PHPUnit:

```bash
php artisan test
```

Tantangan & Solusi (ringkasan)
-----------------------------
1. Kualitas data & Excel yang berantakan — importer memiliki parsing yang tahan banting dan melewati baris yang tidak valid; rekomendasi: tambahkan laporan baris yang dilewati dan mode dry-run.
2. Deteksi duplikasi — saat ini menggunakan pencocokan persis; pertimbangkan kunci ternormalisasi atau ambang fuzzy.
3. Performa fuzzy-match — `KodeGenerator::findSimilarKode` memindai DB di PHP; pertimbangkan indeks trigram di DB atau token pra-hitung.
4. Konkurensi pada pembuatan `urutan_produk` — risiko pelanggaran constraint unik; gunakan sequence DB/locking atau retry-on-conflict.
5. Kompatibilitas DB — kode memakai fitur Postgres; dokumentasikan penggunaan Postgres atau buat fallback agar kompatibel dengan DB lain.

Proses Pengembangan
-------------------
Dari kode terkonfirmasi:
- Resource dan halaman Filament ada, impor/ekspor diimplementasikan, migrasi tersedia (lihat `database/migrations`).

Estimasi urutan pengerjaan (kemungkinan):
1. Kebutuhan & desain DB
2. Scaffolding proyek & Filament
3. CRUD master
4. Engine impor + resolusi master
5. Builder ekspor/laporan
6. Validasi, polishing, testing, deploy

Sorotan Portofolio
------------------
- Pipeline ingest data yang solid (parsing Excel + resolusi master fuzzy)
- Ekspor Excel multi-sheet berformat untuk pemangku kepentingan
- Pemisahan tanggung jawab yang baik: Actions/Services/Helpers