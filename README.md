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

---

## Lisensi

Proyek pembelajaran. CodeIgniter 4 dilisensikan MIT — lihat
<https://codeigniter.com/user_guide/license.html>.
