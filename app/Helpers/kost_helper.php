<?php

/**
 * Helper umum aplikasi KosKu.
 * Berisi fungsi format Rupiah, tanggal Indonesia, dan badge status.
 *
 * Dipakai di controller & view: helper('kost');
 */

if (! function_exists('rupiah')) {
    /**
     * Format angka menjadi Rupiah. Contoh: 1200000 => "Rp 1.200.000"
     */
    function rupiah(float|int|string|null $angka, bool $withPrefix = true): string
    {
        $angka = (float) ($angka ?? 0);
        // number_format pakai koma sebagai desimal & titik sebagai ribuan (locale id).
        $formatted = number_format($angka, 0, ',', '.');

        return $withPrefix ? 'Rp ' . $formatted : $formatted;
    }
}

if (! function_exists('bulan_indo')) {
    /**
     * Nama bulan dalam Bahasa Indonesia. 1 => Januari
     */
    function bulan_indo(int|string|null $bulan): string
    {
        $daftar = [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $daftar[(int) $bulan] ?? '';
    }
}

if (! function_exists('tanggal_indo')) {
    /**
     * Format tanggal ke Bahasa Indonesia.
     * Contoh: "2026-10-18" => "18 Oktober 2026"
     * Jika $withTime true => "18 Oktober 2026 14:30"
     */
    function tanggal_indo(?string $tanggal, bool $withTime = false): string
    {
        if (empty($tanggal) || $tanggal === '0000-00-00' || str_starts_with((string) $tanggal, '0000-00-00')) {
            return '-';
        }

        $ts = strtotime($tanggal);
        if ($ts === false) {
            return '-';
        }

        $hasil = date('j', $ts) . ' ' . bulan_indo((int) date('n', $ts)) . ' ' . date('Y', $ts);

        return $withTime ? $hasil . ' ' . date('H:i', $ts) : $hasil;
    }
}

if (! function_exists('periode_indo')) {
    /**
     * Format periode tagihan. Contoh: (10, 2026) => "Oktober 2026"
     */
    function periode_indo(int|string|null $bulan, int|string|null $tahun): string
    {
        return trim(bulan_indo($bulan) . ' ' . $tahun);
    }
}

if (! function_exists('badge_kamar')) {
    /**
     * Badge warna status kamar.
     */
    function badge_kamar(?string $status): string
    {
        $meta = [
            'kosong'    => ['Kosong', 'success'],
            'terisi'    => ['Terisi', 'primary'],
            'perbaikan' => ['Perbaikan', 'warning'],
        ];
        [$label, $warna] = $meta[$status] ?? [ucfirst((string) $status), 'secondary'];

        return '<span class="badge text-bg-' . $warna . '">' . esc($label) . '</span>';
    }
}

if (! function_exists('badge_tagihan')) {
    /**
     * Badge warna status tagihan.
     */
    function badge_tagihan(?string $status): string
    {
        $meta = [
            'belum_bayar' => ['Belum Bayar', 'secondary'],
            'lunas'       => ['Lunas', 'success'],
            'terlambat'   => ['Terlambat', 'danger'],
        ];
        [$label, $warna] = $meta[$status] ?? [ucfirst((string) $status), 'secondary'];

        return '<span class="badge text-bg-' . $warna . '">' . esc($label) . '</span>';
    }
}

if (! function_exists('badge_sewa')) {
    /**
     * Badge warna status sewa (occupancy).
     */
    function badge_sewa(?string $status): string
    {
        $meta = [
            'aktif'   => ['Aktif', 'success'],
            'selesai' => ['Selesai', 'secondary'],
        ];
        [$label, $warna] = $meta[$status] ?? [ucfirst((string) $status), 'secondary'];

        return '<span class="badge text-bg-' . $warna . '">' . esc($label) . '</span>';
    }
}

if (! function_exists('info_halaman')) {
    /**
     * Teks ringkas "Menampilkan x-y dari z data" untuk pagination.
     *
     * @param \CodeIgniter\Pager\Pager $pager Pager service (Model::$pager)
     */
    function info_halaman($pager, string $label = 'data', string $grup = 'default'): string
    {
        $total = (int) $pager->getTotal($grup);

        if ($total <= 0) {
            return 'Tidak ada data untuk ditampilkan';
        }

        $perHalaman = max(1, (int) $pager->getPerPage($grup));
        $halaman    = max(1, (int) $pager->getCurrentPage($grup));
        $mulai      = ($halaman - 1) * $perHalaman + 1;
        $akhir      = min($total, $halaman * $perHalaman);

        return 'Menampilkan ' . $mulai . '&ndash;' . $akhir . ' dari ' . $total . ' ' . $label;
    }
}

if (! function_exists('angka_dari_input')) {
    /**
     * Bersihkan angka yang diketik pengguna.
     *
     * Format Indonesia: titik = pemisah ribuan, koma = pemisah desimal.
     * "800.000" => "800000", "1.500,50" => "1500.50", "" => ""
     */
    function angka_dari_input(mixed $nilai): string
    {
        $teks = preg_replace('/[^0-9,.\-]/', '', (string) $nilai);

        return str_replace(',', '.', str_replace('.', '', (string) $teks));
    }
}

if (! function_exists('inisial_nama')) {
    /**
     * Ambil inisial dari nama untuk avatar. "Budi Santoso" => "BS"
     */
    function inisial_nama(?string $nama): string
    {
        $kata = preg_split('/\s+/', trim((string) $nama));
        $inisial = '';
        foreach (array_slice($kata, 0, 2) as $k) {
            $inisial .= mb_strtoupper(mb_substr($k, 0, 1));
        }

        return $inisial !== '' ? $inisial : '?';
    }
}

if (! function_exists('status_meta')) {
    /**
     * Metadata status (label + warna) untuk keperluan lain.
     */
    function status_meta(string $tipe, ?string $status): array
    {
        $map = [
            'kamar'   => ['kosong' => ['Kosong', 'success'], 'terisi' => ['Terisi', 'primary'], 'perbaikan' => ['Perbaikan', 'warning']],
            'tagihan' => ['belum_bayar' => ['Belum Bayar', 'secondary'], 'lunas' => ['Lunas', 'success'], 'terlambat' => ['Terlambat', 'danger']],
            'sewa'    => ['aktif' => ['Aktif', 'success'], 'selesai' => ['Selesai', 'secondary']],
            'metode'  => ['tunai' => ['Tunai', 'success'], 'transfer' => ['Transfer', 'info']],
        ];

        return $map[$tipe][$status] ?? [ucfirst((string) $status), 'secondary'];
    }
}

if (! function_exists('badge_metode')) {
    /**
     * Badge warna metode pembayaran.
     */
    function badge_metode(?string $metode): string
    {
        [$label, $warna] = status_meta('metode', $metode);

        return '<span class="badge text-bg-' . $warna . '">' . esc($label) . '</span>';
    }
}

if (! function_exists('label_kategori')) {
    /**
     * Nama kategori pengeluaran dalam Bahasa Indonesia.
     */
    function label_kategori(?string $kategori): string
    {
        $daftar = [
            'listrik'   => 'Listrik',
            'air'       => 'Air',
            'perbaikan' => 'Perbaikan',
            'lainnya'   => 'Lainnya',
        ];

        return $daftar[$kategori] ?? ucfirst((string) $kategori);
    }
}

if (! function_exists('badge_kategori')) {
    /**
     * Badge warna kategori pengeluaran.
     */
    function badge_kategori(?string $kategori): string
    {
        $warna = [
            'listrik'   => 'warning',
            'air'       => 'info',
            'perbaikan' => 'danger',
            'lainnya'   => 'secondary',
        ][$kategori] ?? 'secondary';

        return '<span class="badge text-bg-' . $warna . '">' . esc(label_kategori($kategori)) . '</span>';
    }
}

if (! function_exists('wa_link')) {
    /**
     * Tautan WhatsApp (wa.me) dengan pesan siap kirim.
     *
     * Nomor lokal Indonesia (08xx) otomatis diubah menjadi format
     * internasional (628xx) yang dipakai wa.me.
     */
    function wa_link(?string $telepon, string $pesan = ''): string
    {
        $nomor = preg_replace('/\D+/', '', (string) $telepon);

        if ($nomor !== '') {
            if (str_starts_with($nomor, '0')) {
                $nomor = '62' . substr($nomor, 1);
            } elseif (str_starts_with($nomor, '8')) {
                $nomor = '62' . $nomor;
            } elseif (str_starts_with($nomor, '620')) {
                $nomor = '62' . substr($nomor, 3);
            }
        }

        $url = 'https://wa.me/' . $nomor;

        return $pesan !== '' ? $url . '?text=' . rawurlencode($pesan) : $url;
    }
}

if (! function_exists('nomor_invoice')) {
    /**
     * Nomor tagihan yang rapi untuk ditampilkan / dicetak.
     * Contoh: id 12 periode Oktober 2026 => "INV/202610/00012"
     *
     * @param array<string, mixed> $tagihan
     */
    function nomor_invoice(array $tagihan): string
    {
        return sprintf(
            'INV/%04d%02d/%05d',
            (int) ($tagihan['period_year'] ?? 0),
            (int) ($tagihan['period_month'] ?? 0),
            (int) ($tagihan['id'] ?? 0)
        );
    }
}

if (! function_exists('nomor_kwitansi')) {
    /**
     * Nomor kwitansi pembayaran.
     * Contoh: pembayaran id 5 pada 18 Oktober 2026 => "KW/20261018/00005"
     *
     * @param array<string, mixed> $pembayaran
     */
    function nomor_kwitansi(array $pembayaran): string
    {
        $tanggal = ! empty($pembayaran['paid_at']) ? date('Ymd', strtotime((string) $pembayaran['paid_at'])) : date('Ymd');

        return sprintf('KW/%s/%05d', $tanggal, (int) ($pembayaran['id'] ?? 0));
    }
}

if (! function_exists('persen')) {
    /**
     * Hitung persentase bagian terhadap total (0-100).
     */
    function persen(float|int|string|null $bagian, float|int|string|null $total): float
    {
        $total = (float) $total;

        if ($total <= 0) {
            return 0.0;
        }

        return round(((float) $bagian / $total) * 100, 1);
    }
}

if (! function_exists('durasi_sewa')) {
    /**
     * Lama menempati dihitung dari tanggal masuk.
     *
     * @return array{bulan: int, hari: int, teks: string}
     */
    function durasi_sewa(?string $mulai, ?string $sampai = null): array
    {
        $kosong = ['bulan' => 0, 'hari' => 0, 'teks' => '-'];

        if (empty($mulai) || str_starts_with((string) $mulai, '0000-00-00')) {
            return $kosong;
        }

        $awal = strtotime((string) $mulai);
        if ($awal === false) {
            return $kosong;
        }

        $akhir = ($sampai !== null && $sampai !== '') ? strtotime((string) $sampai) : time();
        if ($akhir === false || $akhir < $awal) {
            return $kosong;
        }

        $selisih = (new \DateTime(date('Y-m-d', $awal)))->diff(new \DateTime(date('Y-m-d', $akhir)));
        $bulan   = ($selisih->y * 12) + $selisih->m;

        $bagian = [];
        if ($bulan > 0) {
            $bagian[] = $bulan . ' bulan';
        }
        if ($selisih->d > 0) {
            $bagian[] = $selisih->d . ' hari';
        }

        return [
            'bulan' => $bulan,
            'hari'  => $selisih->d,
            'teks'  => $bagian === [] ? 'Baru masuk' : implode(' ', $bagian),
        ];
    }
}

if (! function_exists('tanggal_untuk_input')) {
    /**
     * Normalisasi nilai tanggal ke format Y-m-d untuk <input type="date">.
     */
    function tanggal_untuk_input(?string $tanggal): string
    {
        if (empty($tanggal)) {
            return '';
        }

        $ts = strtotime((string) $tanggal);

        return $ts === false ? '' : date('Y-m-d', $ts);
    }
}

if (! function_exists('pesan_wa_tagihan')) {
    /**
     * Susun teks pengingat tagihan untuk dikirim lewat WhatsApp.
     *
     * @param array<string, mixed> $tagihan (butuh tenant_name, room_number,
     *                                      period_month, period_year, due_date,
     *                                      amount/sisa)
     */
    function pesan_wa_tagihan(array $tagihan): string
    {
        $namaKos = function_exists('setting') ? (string) setting('kos_name', 'KosKu') : 'KosKu';
        $nominal = (float) ($tagihan['sisa'] ?? $tagihan['amount'] ?? 0);

        return 'Halo ' . ($tagihan['tenant_name'] ?? 'Penghuni')
            . ', kami mengingatkan tagihan kos kamar ' . ($tagihan['room_number'] ?? '-')
            . ' periode ' . periode_indo($tagihan['period_month'] ?? null, $tagihan['period_year'] ?? null)
            . ' sebesar ' . rupiah($nominal)
            . ' yang jatuh tempo pada ' . tanggal_indo($tagihan['due_date'] ?? null)
            . '. Mohon segera diselesaikan pembayarannya. Terima kasih. - ' . $namaKos;
    }
}

if (! function_exists('pesan_wa_kwitansi')) {
    /**
     * Susun teks ucapan terima kasih pembayaran untuk WhatsApp.
     *
     * @param array<string, mixed> $pembayaran
     */
    function pesan_wa_kwitansi(array $pembayaran): string
    {
        $namaKos = function_exists('setting') ? (string) setting('kos_name', 'KosKu') : 'KosKu';

        return 'Halo ' . ($pembayaran['tenant_name'] ?? 'Penghuni')
            . ', pembayaran sebesar ' . rupiah($pembayaran['amount'] ?? 0)
            . ' untuk tagihan kamar ' . ($pembayaran['room_number'] ?? '-')
            . ' periode ' . periode_indo($pembayaran['period_month'] ?? null, $pembayaran['period_year'] ?? null)
            . ' sudah kami terima pada ' . tanggal_indo($pembayaran['paid_at'] ?? null)
            . '. Terima kasih. - ' . $namaKos;
    }
}

if (! function_exists('terbilang')) {
    /**
     * Ubah angka menjadi kata dalam Bahasa Indonesia.
     * Contoh: 150000 => "Seratus lima puluh ribu"
     *
     * @param bool $denganRupiah tambahkan kata "rupiah" di akhir
     */
    function terbilang(float|int|string|null $angka, bool $denganRupiah = false): string
    {
        $nilai = (int) round((float) ($angka ?? 0));

        if ($nilai === 0) {
            return $denganRupiah ? 'Nol rupiah' : 'Nol';
        }

        $negatif = $nilai < 0;
        $nilai   = abs($nilai);

        $satuan = [
            '', 'satu', 'dua', 'tiga', 'empat', 'lima',
            'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas',
        ];

        $baca = static function (int $n) use (&$baca, $satuan): string {
            if ($n < 12) {
                return $satuan[$n];
            }

            if ($n < 20) {
                return $baca($n - 10) . ' belas';
            }

            if ($n < 100) {
                return $baca(intdiv($n, 10)) . ' puluh ' . $baca($n % 10);
            }

            if ($n < 200) {
                return 'seratus ' . $baca($n - 100);
            }

            if ($n < 1000) {
                return $baca(intdiv($n, 100)) . ' ratus ' . $baca($n % 100);
            }

            if ($n < 2000) {
                return 'seribu ' . $baca($n - 1000);
            }

            if ($n < 1000000) {
                return $baca(intdiv($n, 1000)) . ' ribu ' . $baca($n % 1000);
            }

            if ($n < 1000000000) {
                return $baca(intdiv($n, 1000000)) . ' juta ' . $baca($n % 1000000);
            }

            if ($n < 1000000000000) {
                return $baca(intdiv($n, 1000000000)) . ' miliar ' . $baca($n % 1000000000);
            }

            return $baca(intdiv($n, 1000000000000)) . ' triliun ' . $baca($n % 1000000000000);
        };

        $hasil = ucfirst(trim((string) preg_replace('/\s+/', ' ', $baca($nilai))));

        if ($negatif) {
            $hasil = 'Minus ' . lcfirst($hasil);
        }

        return $denganRupiah ? $hasil . ' rupiah' : $hasil;
    }
}
