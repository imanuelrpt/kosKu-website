<?php

namespace App\Controllers;

use App\Models\ExpenseModel;
use App\Models\InvoiceModel;
use App\Models\PaymentModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Laporan - rekap keuangan bulanan (pemasukan, pengeluaran, tunggakan)
 * beserta ekspor CSV yang bisa dibuka di Excel (khusus admin).
 */
class Laporan extends BaseController
{
    private PaymentModel $paymentModel;
    private ExpenseModel $expenseModel;
    private InvoiceModel $invoiceModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->paymentModel = new PaymentModel();
        $this->expenseModel = new ExpenseModel();
        $this->invoiceModel = new InvoiceModel();
    }

    /**
     * Laporan bulanan satu periode.
     */
    public function index(): string
    {
        $this->invoiceModel->tandaiTerlambat();

        [$bulan, $tahun] = $this->periodeDariQuery();

        $pemasukan   = $this->paymentModel->untukPeriode($bulan, $tahun);
        $pengeluaran = $this->expenseModel->untukPeriode($bulan, $tahun);
        $tagihan     = $this->invoiceModel->untukPeriode($bulan, $tahun);

        $totalPemasukan   = 0.0;
        foreach ($pemasukan as $p) {
            $totalPemasukan += (float) $p['amount'];
        }

        $totalPengeluaran = 0.0;
        $perKategori      = ['listrik' => 0.0, 'air' => 0.0, 'perbaikan' => 0.0, 'lainnya' => 0.0];
        foreach ($pengeluaran as $p) {
            $totalPengeluaran += (float) $p['amount'];
            $perKategori[$p['category']] = ($perKategori[$p['category']] ?? 0) + (float) $p['amount'];
        }

        $totalTagihan = 0.0;
        $sisaTagihan  = 0.0;
        $perStatus    = ['lunas' => 0, 'belum_bayar' => 0, 'terlambat' => 0];
        foreach ($tagihan as $t) {
            $totalTagihan += (float) $t['amount'];
            $sisaTagihan  += (float) $t['sisa'];
            $perStatus[$t['status']] = ($perStatus[$t['status']] ?? 0) + 1;
        }

        // Pemasukan bulan sebelumnya sebagai pembanding.
        $bulanLalu       = $bulan === 1 ? 12 : $bulan - 1;
        $tahunLalu       = $bulan === 1 ? $tahun - 1 : $tahun;
        $pemasukanLalu   = $this->paymentModel->totalPeriode($bulanLalu, $tahunLalu);
        $pengeluaranLalu = $this->expenseModel->totalPeriode($bulanLalu, $tahunLalu);

        // Grafik 6 bulan terakhir.
        $masukBulanan = $this->paymentModel->totalPerBulan(6);
        $keluarBulanan = $this->expenseModel->totalPerBulan(6);

        $labelGrafik = [];
        $grafikMasuk = [];
        $grafikKeluar = [];
        foreach ($masukBulanan as $kunci => $nilai) {
            [$tahunPoin, $bulanPoin] = explode('-', $kunci);
            $labelGrafik[] = substr(bulan_indo((int) $bulanPoin), 0, 3) . ' ' . substr($tahunPoin, 2);
            $grafikMasuk[] = $nilai;
            $grafikKeluar[] = (float) ($keluarBulanan[$kunci] ?? 0);
        }

        // Rincian pemasukan per kamar.
        $perKamar = [];
        foreach ($pemasukan as $p) {
            $kunci = (string) $p['room_number'];
            if (! isset($perKamar[$kunci])) {
                $perKamar[$kunci] = ['room_number' => $p['room_number'], 'tenant_name' => $p['tenant_name'], 'total' => 0.0, 'jumlah' => 0];
            }
            $perKamar[$kunci]['total']  += (float) $p['amount'];
            $perKamar[$kunci]['jumlah'] += 1;
        }
        usort($perKamar, static fn (array $a, array $b): int => strcmp((string) $a['room_number'], (string) $b['room_number']));

        return $this->render('laporan/index', [
            'title'            => 'Laporan Keuangan',
            'bulan'            => $bulan,
            'tahun'            => $tahun,
            'daftarTahun'      => $this->invoiceModel->daftarTahun(),
            'pemasukan'        => $pemasukan,
            'pengeluaran'      => $pengeluaran,
            'tagihan'          => $tagihan,
            'totalPemasukan'   => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'laba'             => $totalPemasukan - $totalPengeluaran,
            'totalTagihan'     => $totalTagihan,
            'sisaTagihan'      => $sisaTagihan,
            'perStatus'        => $perStatus,
            'perKategori'      => $perKategori,
            'perKamar'         => array_values($perKamar),
            'pemasukanLalu'    => $pemasukanLalu,
            'pengeluaranLalu'  => $pengeluaranLalu,
            'labelGrafik'      => $labelGrafik,
            'grafikMasuk'      => $grafikMasuk,
            'grafikKeluar'     => $grafikKeluar,
        ]);
    }

    /**
     * Ekspor laporan ke CSV (dapat dibuka dengan Excel).
     */
    public function ekspor(string $jenis): ResponseInterface
    {
        [$bulan, $tahun] = $this->periodeDariQuery();

        $namaFile = 'laporan-' . $jenis . '-' . $tahun . '-' . str_pad((string) $bulan, 2, '0', STR_PAD_LEFT) . '.csv';

        $isi = "\xEF\xBB\xBF";          // BOM UTF-8 agar huruf beraksen tampil benar
        $isi .= "sep=;\n";              // beri tahu Excel pemisah kolomnya

        switch ($jenis) {
            case 'pemasukan':
                $isi .= $this->csvPemasukan($bulan, $tahun);
                break;

            case 'pengeluaran':
                $isi .= $this->csvPengeluaran($bulan, $tahun);
                break;

            case 'tagihan':
                $isi .= $this->csvTagihan($bulan, $tahun);
                break;

            case 'ringkasan':
                $isi .= $this->csvRingkasan($bulan, $tahun);
                break;

            default:
                return redirect()->to(base_url('laporan'))->with('gagal', 'Jenis ekspor tidak dikenali.');
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $namaFile . '"')
            ->setHeader('Cache-Control', 'no-store')
            ->setBody($isi);
    }

    /**
     * Baca periode dari query string dengan nilai bawaan bulan berjalan.
     *
     * @return array{0: int, 1: int}
     */
    private function periodeDariQuery(): array
    {
        $bulan = (int) $this->request->getGet('bulan');
        $tahun = (int) $this->request->getGet('tahun');

        if ($bulan < 1 || $bulan > 12) {
            $bulan = (int) date('n');
        }

        if ($tahun < 2000 || $tahun > 2100) {
            $tahun = (int) date('Y');
        }

        return [$bulan, $tahun];
    }

    /**
     * Bungkus satu baris CSV (semua nilai dikutip agar aman).
     *
     * @param array<int, string|int|float|null> $kolom
     */
    private function barisCsv(array $kolom): string
    {
        $sel = array_map(static function ($nilai): string {
            return '"' . str_replace('"', '""', (string) ($nilai ?? '')) . '"';
        }, $kolom);

        return implode(';', $sel) . "\n";
    }

    /**
     * Angka untuk CSV: tanpa pemisah ribuan, tanpa desimal.
     */
    private function angkaCsv(float|int|string|null $nilai): string
    {
        return (string) (int) round((float) ($nilai ?? 0));
    }

    private function csvPemasukan(int $bulan, int $tahun): string
    {
        $isi  = $this->barisCsv(['Laporan Pemasukan', periode_indo($bulan, $tahun)]);
        $isi .= $this->barisCsv(['Tanggal', 'Penghuni', 'Kamar', 'Periode Tagihan', 'Metode', 'Jumlah', 'Catatan']);

        $total = 0.0;
        foreach ($this->paymentModel->untukPeriode($bulan, $tahun) as $p) {
            $total += (float) $p['amount'];
            $isi .= $this->barisCsv([
                $p['paid_at'],
                $p['tenant_name'],
                $p['room_number'],
                periode_indo($p['period_month'], $p['period_year']),
                $p['method'] === 'transfer' ? 'Transfer' : 'Tunai',
                $this->angkaCsv($p['amount']),
                $p['note'] ?? '',
            ]);
        }

        $isi .= $this->barisCsv(['TOTAL', '', '', '', '', $this->angkaCsv($total), '']);

        return $isi;
    }

    private function csvPengeluaran(int $bulan, int $tahun): string
    {
        $isi  = $this->barisCsv(['Laporan Pengeluaran', periode_indo($bulan, $tahun)]);
        $isi .= $this->barisCsv(['Tanggal', 'Kategori', 'Jumlah', 'Catatan']);

        $total = 0.0;
        foreach ($this->expenseModel->untukPeriode($bulan, $tahun) as $p) {
            $total += (float) $p['amount'];
            $isi .= $this->barisCsv([
                $p['date'],
                label_kategori($p['category']),
                $this->angkaCsv($p['amount']),
                $p['note'] ?? '',
            ]);
        }

        $isi .= $this->barisCsv(['TOTAL', '', $this->angkaCsv($total), '']);

        return $isi;
    }

    private function csvTagihan(int $bulan, int $tahun): string
    {
        $isi  = $this->barisCsv(['Laporan Tagihan', periode_indo($bulan, $tahun)]);
        $isi .= $this->barisCsv(['Penghuni', 'Kamar', 'Jumlah', 'Sudah Dibayar', 'Sisa', 'Jatuh Tempo', 'Status']);

        $total = 0.0;
        foreach ($this->invoiceModel->untukPeriode($bulan, $tahun) as $t) {
            $total += (float) $t['amount'];
            $isi .= $this->barisCsv([
                $t['tenant_name'],
                $t['room_number'],
                $this->angkaCsv($t['amount']),
                $this->angkaCsv($t['dibayar']),
                $this->angkaCsv($t['sisa']),
                $t['due_date'],
                status_meta('tagihan', $t['status'])[0],
            ]);
        }

        $isi .= $this->barisCsv(['TOTAL', '', $this->angkaCsv($total), '', '', '', '']);

        return $isi;
    }

    private function csvRingkasan(int $bulan, int $tahun): string
    {
        $pemasukan        = $this->paymentModel->totalPeriode($bulan, $tahun);
        $pengeluaran      = $this->expenseModel->totalPeriode($bulan, $tahun);
        $totalTagihan     = 0.0;
        $sisaTagihan      = 0.0;

        foreach ($this->invoiceModel->untukPeriode($bulan, $tahun) as $t) {
            $totalTagihan += (float) $t['amount'];
            $sisaTagihan  += (float) $t['sisa'];
        }

        $isi  = $this->barisCsv(['Ringkasan Keuangan KosKu', periode_indo($bulan, $tahun)]);
        $isi .= $this->barisCsv(['Keterangan', 'Jumlah']);
        $isi .= $this->barisCsv(['Uang masuk (pembayaran diterima)', $this->angkaCsv($pemasukan)]);
        $isi .= $this->barisCsv(['Pengeluaran operasional', $this->angkaCsv($pengeluaran)]);
        $isi .= $this->barisCsv(['Laba / (rugi) bersih', $this->angkaCsv($pemasukan - $pengeluaran)]);
        $isi .= $this->barisCsv(['Total tagihan diterbitkan', $this->angkaCsv($totalTagihan)]);
        $isi .= $this->barisCsv(['Tunggakan periode ini', $this->angkaCsv($sisaTagihan)]);

        return $isi;
    }
}
