# KosKu — Sistem Manajemen Kos-kosan

Aplikasi web untuk mengelola kos-kosan: data kamar, penghuni, sewa (check-in /
check-out), tagihan bulanan, pembayaran (termasuk cicilan), pengeluaran
operasional, dan laporan keuangan bulanan.

Dibangun dengan **CodeIgniter 4**, **PHP 8.1+**, **MySQL**, **Bootstrap 5**,
dan **Chart.js**.

---

## Latar Belakang

### Tujuan

KosKu dibuat untuk menjadi **satu tempat** bagi pengelola kos-kosan dalam menjalankan
operasional sehari-hari, tanpa perlu keahlian akuntansi dan tanpa berkas terpisah-pisah.

- **Memusatkan data** kamar, penghuni, kontrak sewa, tagihan, pembayaran, dan
  pengeluaran dalam satu basis data yang saling terhubung.
- **Memangkas pekerjaan berulang**: pembuatan tagihan bulanan, pencatatan pembayaran,
  dan perhitungan tunggakan cukup dilakukan lewat beberapa klik.
- **Memberi penghuni akses mandiri 24 jam** atas tagihan, riwayat pembayaran, dan
  kwitansinya sendiri, sehingga pertanyaan rutin tidak lagi menumpuk di pengelola.
- **Menyajikan laporan keuangan bulanan** yang siap dipakai sebagai dasar keputusan
  (pemasukan, pengeluaran, laba/rugi, tingkat hunian, dan tunggakan).
- **Menjaga data tetap aman dan rapi**: data pribadi penghuni dan data keuangan hanya
  dapat diakses oleh pihak yang berhak.

### Masalah

Sebelum ada sistem ini, pengelolaan kos umumnya berjalan dengan buku catatan atau
berkas spreadsheet yang tersebar. Pola tersebut menimbulkan sejumlah masalah:

| # | Masalah | Akibat |
| --- | --- | --- |
| 1 | Tagihan bulanan dibuat manual, satu per satu | Rawan lupa, salah hitung, dan tidak konsisten antar penghuni |
| 2 | Pembayaran cicilan dicatat seadanya | Sulit diketahui siapa yang baru membayar sebagian dan berapa sisanya |
| 3 | Status jatuh tempo hanya diingat pengelola | Tunggakan baru ketahuan saat sudah menumpuk |
| 4 | Penghuni harus bertanya tagihan/kwitansi lewat chat | Komunikasi tercampur, jawaban lambat, dan mudah terlewat |
| 5 | Bukti transfer berserakan di galeri pesan | Sulit dicari saat rekonsiliasi dan rawan hilang |
| 6 | Laporan bulanan direkap manual dari beberapa sumber | Menyita waktu dan rawan selisih |
| 7 | Tagihan/kwitansi ditulis tangan atau dibuat ulang | Tidak bernomor, mudah dipalsukan, dan sulit diarsipkan |
| 8 | Data KTP, alamat, dan kontak darurat tersimpan tidak terpusat | Sulit diaudit dan rawan bocor |

### Solusi

KosKu menjawab setiap masalah di atas dengan modul yang saling terhubung:

| Masalah | Solusi di KosKu |
| --- | --- |
| 1 | **Tagihan otomatis** untuk semua sewa aktif lewat `php spark invoices:generate`, dengan jatuh tempo mengikuti Pengaturan (default tanggal 10 bulan berikutnya). Nomor tagihan dibuat otomatis: `INV/YYYYMM/00001`. |
| 2 | **Pembayaran parsial (cicilan)** dengan validasi agar tidak melebihi sisa tagihan; status tagihan (belum bayar / terlambat / lunas) dihitung ulang otomatis setiap kali pembayaran dicatat atau dihapus. |
| 3 | **Penandaan terlambat otomatis** (perintah `--late` atau saat halaman tagihan dibuka) + banner peringatan dan daftar tunggakan di dashboard. |
| 4 | **Portal penghuni** mandiri: dashboard, daftar tagihan, detail tagihan, kwitansi, dan profil. Semua kueri disaring dengan `tenant_id` milik akun yang login; membuka data penghuni lain menghasilkan **404**. |
| 5 | **Unggah bukti transfer** ke `writable/uploads` (di luar folder publik) dengan nama berkas acak; akses hanya lewat controller `Berkas` yang memeriksa hak milik. |
| 6 | **Laporan bulanan otomatis** (pemasukan, pengeluaran, laba/rugi, tunggakan, rincian per kamar) plus **ekspor CSV** berformat Indonesia yang langsung terbuka rapi di Excel. |
| 7 | **Cetak tagihan & kwitansi** siap PDF dengan nomor dokumen otomatis (`KW/YYYYMMDD/00001`), jumlah dalam huruf (terbilang), dan kop dari Pengaturan. |
| 8 | **Keamanan berlapis**: password di-hash, CSRF pada semua form, filter `auth`/`role`, validasi server-side, `esc()` pada seluruh output, dan soft delete untuk data master. |

Hasil akhirnya: pengelola cukup mengurus operasional dan verifikasi pembayaran,
sementara pencatatan, penomoran, penagihan, dan pelaporan berjalan otomatis.

---

## Tangkapan Layar

> **Catatan:** gambar di bawah masih kosong dan akan diisi manual.
> Letakkan berkas gambar di folder `docs/images/` dengan nama berkas yang sama
> seperti pada tautan, lalu gambar akan tampil otomatis di halaman ini.

### Dashboard Admin
![Dashboard admin](docs/images/dashboard-admin.png)

### Data Kamar
![Data kamar](docs/images/data-kamar.png)

### Tagihan Bulanan
![Daftar tagihan](docs/images/tagihan.png)

### Pembayaran & Kwitansi
![Kwitansi pembayaran](docs/images/kwitansi.png)

### Laporan Bulanan
![Laporan bulanan](docs/images/laporan.png)

### Portal Penghuni
![Dashboard penghuni](docs/images/portal-penghuni.png)

### Cetak Tagihan
![Cetak tagihan](docs/images/cetak-tagihan.png)

---

## Daftar Isi

1. [Latar Belakang](#latar-belakang)
2. [Tangkapan Layar](#tangkapan-layar)
3. [Fitur](#fitur)
4. [Kebutuhan Sistem](#kebutuhan-sistem)
5. [Instalasi](#instalasi)
6. [Akun Demo](#akun-demo)
7. [Aturan Bisnis Penting](#aturan-bisnis-penting)
8. [Tagihan Otomatis (Cron / Task Scheduler)](#tagihan-otomatis-cron--task-scheduler)
9. [Ekspor Laporan](#ekspor-laporan)
10. [Struktur Folder Penting](#struktur-folder-penting)
11. [Keamanan](#keamanan)
12. [Pengujian](#pengujian)
13. [Panduan Deploy](#panduan-deploy)

---

## Fitur

### Peran Admin

| Modul | Kemampuan |
| --- | --- |
| **Dashboard** | Statistik hunian, uang masuk/pengeluaran/laba bulan berjalan, total tunggakan, grafik 6 bulan (uang masuk vs pengeluaran), grafik status kamar & status tagihan, daftar tagihan terlambat, kamar kosong siap disewakan, sewa aktif. |
| **Kamar** | CRUD kamar (nomor, harga, lantai, fasilitas, status). Hapus permanen hanya bila kamar tidak pernah disewa. |
| **Penghuni** | CRUD data penghuni + foto, tautkan ke akun login, lihat riwayat sewa, tagihan, dan pembayaran. |
| **Sewa** | Check-in (kamar kosong + penghuni belum menyewa), check-out (blokir bila masih ada tagihan belum lunas), batalkan sewa yang salah. |
| **Tagihan** | Daftar + pencarian/filter (nama, status, bulan, tahun), buat tagihan manual, **buat tagihan otomatis** untuk semua sewa aktif, tandai terlambat otomatis, detail tagihan, cetak tagihan, pengingat WhatsApp. |
| **Pembayaran** | Catat pembayaran (tunai/transfer), **cicilan parsial**, validasi agar tidak melebihi sisa tagihan, unggah bukti transfer, kwitansi siap cetak, konfirmasi WhatsApp, hapus pembayaran salah input (status tagihan dihitung ulang). |
| **Pengeluaran** | CRUD biaya operasional (listrik, air, perbaikan, lainnya) dengan ringkasan per kategori. |
| **Laporan** | Rekap bulanan: uang masuk, pengeluaran, laba/rugi, tunggakan, perbandingan bulan sebelumnya, grafik 6 bulan, rincian per kamar, ekspor CSV (ringkasan/pemasukan/pengeluaran/tagihan). |
| **Pengaturan** | Identitas kos (nama, alamat, telepon, pemilik, rekening), tanggal jatuh tempo tagihan, aktif/nonaktif pendaftaran mandiri penghuni. |

### Peran Penghuni

Portal mandiri dengan navigasi **Dashboard**, **Tagihan Saya**, dan **Profil**.

| Halaman | Isi |
| --- | --- |
| **Dashboard** (`penghuni/beranda`) | Sapaan dan kartu kamar (nomor, lantai, harga sewa, deposit, fasilitas, status sewa, lama menempati), empat kartu ringkasan (total tunggakan, total sudah dibayar, jatuh tempo terdekat, lama menempati), peringatan bila ada tagihan terlambat, lima tagihan & lima pembayaran terbaru, grafik donat terbayar vs sisa, serta informasi kos (alamat, pengelola, telepon/WA, rekening). |
| **Tagihan Saya** (`penghuni/tagihan`) | Daftar tagihan milik sendiri dengan filter status/bulan/tahun dan pagination, kartu ringkasan, penanda jatuh tempo terdekat, halaman detail tagihan, cetak tagihan/PDF, dan kwitansi untuk setiap pembayaran. |
| **Profil** (`penghuni/profil`) | Ubah nama/email/nomor HP akun dan ganti kata sandi. Data KTP, alamat, dan kontak darurat dari pengelola ditampilkan hanya-baca. |

- Semua kueri disaring dengan `tenant_id` milik akun yang login. Membuka tagihan
  atau kwitansi milik penghuni lain menghasilkan **404**, sehingga keberadaan data
  penghuni lain tidak terbocor.
- Akun penghuni yang belum ditautkan ke data penghuni (mis. baru mendaftar sendiri)
  tetap dapat masuk dan mendapat arahan untuk menghubungi pengelola.
- Tombol WhatsApp pada halaman cetak milik penghuni mengarah ke pengelola kos,
  bukan ke nomor penghuni itu sendiri.

### Lintas Modul

- Filter autentikasi (`auth`) dan peran (`role:admin` / `role:penghuni`).
- Halaman cetak terpisah (tagihan & kwitansi) siap di-print atau disimpan
  sebagai PDF melalui dialog cetak peramban.
- Nomor dokumen otomatis: `INV/YYYYMM/00001` (tagihan) dan
  `KW/YYYYMMDD/00001` (kwitansi).
- Jumlah uang tertulis dalam huruf (terbilang) pada cetakan.
- Logo/teks kop dokumen diambil dari Pengaturan.
- Sidebar admin bisa dibuka/ditutup: tombol di topbar menyusutkannya menjadi rail
  ikon (tooltip saat di-hover). Pilihan disimpan di `localStorage` sehingga tetap
  sama saat berpindah halaman; di layar kecil sidebar menjadi menu geser (offcanvas).

---

## Kebutuhan Sistem

- PHP **8.1** atau lebih baru
- Ekstensi PHP: `intl`, `mbstring`, `curl`, `mysqli`, `gd`, `zip`
- MySQL 5.7+ / MariaDB 10.3+ (aplikasi diuji pada MySQL 8.0)
- Composer
- Web server dengan `mod_rewrite` (Apache/Laragon/XAMPP) — document root
  diarahkan ke folder `public/`

---

## Instalasi

### 1. Ambil kode & pasang dependensi

```bash
composer install
```

### 2. Siapkan konfigurasi environment

Windows (PowerShell):

```powershell
Copy-Item .env.example .env
```

Linux/macOS:

```bash
cp .env.example .env
```

Sesuaikan minimal nilai berikut di `.env`:

```ini
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost/manajemen-kost/public/'
database.default.hostname = localhost
database.default.database = db_kost
database.default.username = root
database.default.password =
```

> **Penting**
> - `app.baseURL` **wajib** diakhiri garis miring `/`.
> - `app.indexPage` dikosongkan agar URL bersih tanpa `index.php`.
> - Jangan mengisi `session.savePath` dengan `null` — nilainya dibaca sebagai
>   teks sehingga menjadi folder bernama `null`. Biarkan dikomentari.
> - File `.env` **tidak boleh** di-commit ke git.

### 3. Buat database

```bash
mysql -u root -e "CREATE DATABASE db_kost CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 4. Jalankan migrasi

```bash
php spark migrate
```

### 5. Isi data demo (opsional, sangat disarankan untuk mencoba)

```bash
php spark db:seed DatabaseSeeder
```

Seeder aman dijalankan berulang kali (tabel dibersihkan lebih dulu). Pada
`CI_ENVIRONMENT = production` seeder otomatis dilewati bila tabel `users`
sudah berisi data.

### 6. Buat kunci enkripsi

```bash
php spark key:generate
```

### 7. Jalankan aplikasi

Arahkan browser ke `app.baseURL` di atas, misalnya:

<http://localhost/manajemen-kost/public/>

**Laragon**: cukup letakkan folder proyek di `C:\laragon\www` dan pastikan
Apache + MySQL sudah *Start*. Bila PHP CLI default bukan PHP 8.1, gunakan
path lengkap, contoh:

```powershell
& 'C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe' spark migrate
```

`php spark serve` juga bisa dipakai untuk pengembangan cepat, namun aplikasi
ini disetel untuk URL bersih (`app.indexPage = ''`).

> **Windows — `zip` extension**
> Bila `php spark` mengeluh soal ekstensi `zip` (umum terjadi bila PHP CLI
> menunjuk ke instalasi berbeda), gunakan PHP dari Laragon seperti contoh di
> atas.

---

## Akun Demo

| Peran | Email | Kata Sandi |
| --- | --- | --- |
| Admin | `admin@kosku.test` | `admin123` |
| Penghuni | `siti@kosku.test` | `penghuni123` |
| Penghuni | `andi@kosku.test` | `penghuni123` |
| Penghuni | `dewi@kosku.test` | `penghuni123` |
| Penghuni | `rizky@kosku.test` | `penghuni123` |
| Penghuni | `nurul@kosku.test` | `penghuni123` |
| Penghuni | `fajar@kosku.test` | `penghuni123` |

> Ganti seluruh akun demo sebelum aplikasi dipakai sungguhan.

---

## Aturan Bisnis Penting

### Tagihan

- Periode tagihan memakai pasangan **bulan + tahun** (bukan rentang tanggal).
- Jatuh tempo = **tanggal `invoice_due_day`** (default **10**) pada **bulan
  berikutnya** setelah periode. Contoh: tagihan **Oktober 2026** jatuh tempo
  **10 November 2026**.
- Satu kombinasi `sewa + bulan + tahun` hanya boleh punya **satu** tagihan
  (dijaga oleh *unique index* di database).
- Status tagihan: `belum_bayar`, `terlambat`, `lunas`.
  Tagihan yang melewati jatuh tempo otomatis berubah menjadi `terlambat`
  setiap kali halaman Tagihan/Pembayaran/Laporan/Dashboard dibuka, dan oleh
  perintah `invoices:generate --late`.
- Tagihan hanya bisa dihapus bila **belum ada pembayaran** sama sekali.

### Pembayaran

- Satu tagihan boleh dibayar berkali-kali (**cicilan**).
- Jumlah bayar harus lebih besar dari `0` dan **tidak boleh melebihi sisa
  tagihan**.
- Status tagihan dihitung ulang otomatis setiap pembayaran ditambah atau
  dihapus:
  - total pembayaran ≥ jumlah tagihan → `lunas`
  - belum lunas & sudah lewat jatuh tempo → `terlambat`
  - selain itu → `belum_bayar`
- Bukti transfer bersifat **opsional** (pembayaran tunai tidak memerlukannya),
  maksimal 2 MB, format JPG/PNG/PDF, nama berkas diacak.

### Sewa

- Check-in memerlukan kamar berstatus `kosong` yang tidak sedang disewa, dan
  penghuni yang belum punya sewa aktif.
- Harga sewa terisi otomatis dari harga kamar tetapi boleh disesuaikan.
- Check-out diblokir bila masih ada tagihan belum lunas (kecuali admin
  memberi konfirmasi mengabaikan tagihan).
- Status kamar disinkronkan otomatis: ada sewa aktif → `terisi`, tidak ada →
  `kosong` (kamar `perbaikan` tidak pernah ditimpa).

### Penghapusan Data

- `users`, `rooms`, `tenants` memakai **soft delete** (kolom `deleted_at`).
- `occupancies`, `invoices`, `payments`, `expenses` adalah **riwayat** dan
  dihapus permanen bila memang salah input.

---

## Tagihan Otomatis (Cron / Task Scheduler)

Perintah artisan KosKu:

```bash
php spark invoices:generate            # periode bulan berjalan
php spark invoices:generate 11 2026    # periode November 2026
php spark invoices:generate --late     # hanya menandai tagihan terlambat
```

Perilaku:

- Membuat tagihan untuk **semua sewa aktif** pada periode tersebut.
- Tagihan yang sudah ada dilewati (aman dijalankan berulang kali).
- Jatuh tempo mengikuti pengaturan `invoice_due_day`.
- Setelah pembuatan, tagihan yang lewat tempo ditandai `terlambat`.

### Windows — Task Scheduler

Buat file `generate_tagihan.bat`:

```bat
@echo off
cd /d C:\laragon\www\manajemen-kost
"C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe" spark invoices:generate >> writable\logs\tagihan.log 2>&1
```

Daftarkan agar berjalan setiap tanggal 1 pukul 07:00:

```bat
schtasks /create /tn "KosKu - Buat Tagihan Bulanan" /tr "C:\laragon\www\manajemen-kost\generate_tagihan.bat" /sc monthly /d 1 /st 07:00 /ru SYSTEM
```

Jalankan manual untuk menguji:

```bat
schtasks /run /tn "KosKu - Buat Tagihan Bulanan"
```

### Linux / VPS — Cron

```cron
# Setiap tanggal 1 pukul 07:00 WIB
0 7 1 * * cd /var/www/kosku && php spark invoices:generate >> writable/logs/tagihan.log 2>&1

# Menandai tagihan terlambat setiap hari pukul 08:00
0 8 * * * cd /var/www/kosku && php spark invoices:generate --late >> writable/logs/tagihan.log 2>&1
```

Sesuaikan zona waktu server (aplikasi memakai `Asia/Jakarta`).

---

## Ekspor Laporan

Menu **Laporan** menyediakan unduhan CSV yang dapat dibuka langsung di Excel:

| Tombol | Isi |
| --- | --- |
| Ringkasan | Uang masuk, pengeluaran, laba/rugi, total tagihan, tunggakan periode. |
| Pemasukan | Rincian setiap pembayaran yang diterima pada periode tersebut. |
| Pengeluaran | Rincian setiap pengeluaran pada periode tersebut. |
| Tagihan | Rincian tagihan periode tersebut beserta sisa dan statusnya. |

Format berkas: UTF-8 dengan **BOM** dan baris penanda `sep=;` supaya Excel
mengenali pemisah kolom secara otomatis.

---

## Struktur Folder Penting

```
app/
├── Commands/          GenerateInvoices.php     (php spark invoices:generate)
├── Config/            Routes.php               (rute admin & penghuni)
├── Controllers/       Dashboard, Kamar, Penghuni, Sewa,
│                      Tagihan, Pembayaran, Pengeluaran, Laporan,
│                      Pengaturan, Berkas, Auth, Profil
├── Database/
│   ├── Migrations/    8 migrasi tabel
│   └── Seeds/         seeder data demo
├── Filters/           Auth, Guest, Role
├── Helpers/           kost, upload, setting, auth
├── Models/            Room, Tenant, Occupancy, Invoice, Payment,
│                      Expense, Setting
└── Views/
    ├── dashboard/     (statistik + grafik)
    ├── tagihan/       (index, form, show, cetak)
    ├── pembayaran/    (index, form, kwitansi)
    ├── pengeluaran/   (index, form)
    ├── laporan/       (index)
    ├── layouts/       admin, tenant, auth, print
    └── partials/      head, sidebar, topbar, flash, kop_cetak,
                       modal_hapus, script_hapus
writable/
└── uploads/           (di luar document root)
    ├── penghuni/      foto penghuni
    └── pembayaran/    bukti transfer
```

---

## Keamanan

- **Berkas unggahan tidak berada di `public/`.** Semua berkas disimpan di
  `writable/uploads/` dengan nama acak dan hanya dapat diakses melalui
  controller `Berkas`, yang memeriksa hak akses: admin boleh semua,
  penghuni hanya berkas miliknya.
- **CSRF** aktif secara global untuk semua permintaan `POST`.
- Kata sandi di-hash (`password_hash`), login memakai *throttle* percobaan.
- Semua keluaran di *view* di-escape dengan `esc()`.
- Query memakai Query Builder / Model (aman dari SQL injection).
- Unggahan dibatasi ekstensi, MIME, dan ukuran (maks 2 MB).
- Filter `role` memastikan penghuni tidak dapat membuka halaman admin
  (diuji otomatis: `/tagihan`, `/pembayaran`, `/pengeluaran`, `/laporan`,
  `/dashboard` menolak peran penghuni).
- Data penghuni tidak pernah bocor ke penghuni lain (setiap permintaan
  difilter berdasarkan `tenant_id` milik akun yang login).

---

## Pengujian

Berkas uji integrasi berada di luar repositori (folder sementara) dan dijalankan
terhadap server web yang aktif:

| Skrip | Cakupan | Hasil terakhir |
| --- | --- | --- |
| `crud_test.php` | Kamar, penghuni, foto, hapus | 77 lulus / 0 gagal |
| `sewa_test.php` | Check-in & check-out | 61 lulus / 0 gagal |
| `cek5.php` | Verifikasi data Langkah 5 | 18 lulus / 0 gagal |
| `tagihan_test.php` | Tagihan, pembayaran, pengeluaran, laporan, dashboard, perintah spark | 83 lulus / 0 gagal |
| `verifikasi_akhir.php` | Kop dokumen cetak, sidebar, tautan antar modul | 28 lulus / 0 gagal |
| `portal_penghuni_test.php` | Portal penghuni: dashboard, tagihan, cetak, kwitansi, profil, dan pembatasan akses antar penghuni | 59 lulus / 0 gagal |

Pemeriksaan sintaks menyeluruh:

```powershell
Get-ChildItem -Path app -Recurse -Filter *.php | ForEach-Object {
    & 'C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe' -l $_.FullName
}
```

Verifikasi cepat environment & data: buka `/cek-sistem` (hanya pada
`CI_ENVIRONMENT = development`).

---

## Panduan Deploy

1. **Set environment**

   ```ini
   CI_ENVIRONMENT = production
   app.baseURL = 'https://kosku.example.com/'
   app.indexPage = ''
   app.forceGlobalSecureRequests = true
   ```

2. **Amankan berkas sensitif**
   - Jangan pernah meng-commit `.env`.
   - Pastikan folder `writable/` tidak dapat diakses langsung dari web
     (di luar `public/` secara default).
   - Batasi izin tulis hanya pada `writable/` (`uploads`, `logs`, `session`,
     `cache`).

3. **Document root ke `public/`**

   Apache (virtual host):

   ```apache
   <VirtualHost *:443>
       ServerName kosku.example.com
       DocumentRoot /var/www/kosku/public

       <Directory /var/www/kosku/public>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

   Nginx:

   ```nginx
   server {
       listen 443 ssl;
       server_name kosku.example.com;
       root /var/www/kosku/public;
       index index.php;

       location / {
           try_files $uri $uri/ /index.php$is_args$args;
       }

       location ~ \.php$ {
           include fastcgi_params;
           fastcgi_pass unix:/run/php/php8.1-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
       }
   }
   ```

4. **Jalankan migrasi** (tanpa seeder) dan buat kunci enkripsi:

   ```bash
   php spark migrate
   php spark key:generate
   ```

5. **Buat akun admin pertama.** Karena seeder demo dilewati pada production,
   buat admin melalui pendaftaran lalu ubah perannya di database:

   ```sql
   UPDATE users SET role = 'admin' WHERE email = 'admin@kosku.example.com';
   ```

6. **Jadwalkan tagihan otomatis** (lihat bagian Cron / Task Scheduler).

7. **Aktifkan HTTPS & backup rutin** untuk database dan folder
   `writable/uploads`.

8. **Hapus rute pengembangan** bila perlu: `/cek-sistem` dan `/preview/(:segment)`
   sudah otomatis menolak akses di luar environment `development`.

---

## Lisensi

Proyek pembelajaran. CodeIgniter 4 dilisensikan MIT — lihat
<https://codeigniter.com/user_guide/license.html>.
