<?php

namespace App\Controllers;

use App\Models\ExpenseModel;
use App\Models\InvoiceModel;
use App\Models\OccupancyModel;
use App\Models\PaymentModel;
use App\Models\RoomModel;
use App\Models\TenantModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Dashboard admin - statistik ringkas, grafik (Chart.js), dan daftar
 * hal yang perlu ditindaklanjuti (tagihan telat, kamar kosong, dsb).
 */
class Dashboard extends BaseController
{
    private RoomModel $roomModel;
    private TenantModel $tenantModel;
    private OccupancyModel $occupancyModel;
    private InvoiceModel $invoiceModel;
    private PaymentModel $paymentModel;
    private ExpenseModel $expenseModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->roomModel      = new RoomModel();
        $this->tenantModel    = new TenantModel();
        $this->occupancyModel = new OccupancyModel();
        $this->invoiceModel   = new InvoiceModel();
        $this->paymentModel   = new PaymentModel();
        $this->expenseModel   = new ExpenseModel();
    }

    public function index(): string
    {
        // Jaga status tagihan tetap mutakhir setiap dashboard dibuka.
        $this->invoiceModel->tandaiTerlambat();

        $bulan = (int) date('n');
        $tahun = (int) date('Y');

        $kamar          = $this->roomModel->ringkasan();
        $tagihanBulan   = $this->invoiceModel->ringkasan($bulan, $tahun);
        $pemasukanBulan = $this->paymentModel->totalPeriode($bulan, $tahun);
        $pengeluaranBln = $this->expenseModel->totalPeriode($bulan, $tahun);

        // Grafik 6 bulan terakhir: uang masuk vs pengeluaran.
        $masukBulanan  = $this->paymentModel->totalPerBulan(6);
        $keluarBulanan = $this->expenseModel->totalPerBulan(6);

        $labelGrafik   = [];
        $grafikMasuk   = [];
        $grafikKeluar  = [];

        foreach ($masukBulanan as $kunci => $nilai) {
            [$tahunPoin, $bulanPoin] = explode('-', $kunci);
            $labelGrafik[]  = substr(bulan_indo((int) $bulanPoin), 0, 3) . ' ' . substr($tahunPoin, 2);
            $grafikMasuk[]  = $nilai;
            $grafikKeluar[] = (float) ($keluarBulanan[$kunci] ?? 0);
        }

        $sewaAktif = $this->occupancyModel->cariDanFilter(null, 'aktif', 100);

        return $this->render('dashboard/index', [
            'title'             => 'Dashboard',
            'bulan'             => $bulan,
            'tahun'             => $tahun,
            'kamar'             => $kamar,
            'penghuniTotal'     => $this->tenantModel->jumlahAktif(),
            'penghuniMenyewa'   => $this->tenantModel->jumlahMenyewa(),
            'sewaAktif'         => $sewaAktif,
            'jumlahSewaAktif'   => $this->occupancyModel->ringkasan()['aktif'],
            'tagihanBulan'      => $tagihanBulan,
            'pemasukanBulan'    => $pemasukanBulan,
            'pengeluaranBulan'  => $pengeluaranBln,
            'labaBulan'         => $pemasukanBulan - $pengeluaranBln,
            'tunggakan'         => $this->invoiceModel->totalTunggakan(),
            'tagihanTerlambat'  => $this->invoiceModel->terlambatTerbaru(5),
            'pembayaranTerbaru' => $this->paymentModel->terbaru(5),
            'kamarKosong'       => $this->occupancyModel->kamarSiapSewa(),
            'labelGrafik'       => $labelGrafik,
            'grafikMasuk'       => $grafikMasuk,
            'grafikKeluar'      => $grafikKeluar,
            'statusKamar'       => [
                'kosong'    => (int) $kamar['kosong'],
                'terisi'    => (int) $kamar['terisi'],
                'perbaikan' => (int) $kamar['perbaikan'],
            ],
            'statusTagihan'     => [
                'lunas'       => (int) ($tagihanBulan['per_status']['lunas'] ?? 0),
                'belum_bayar' => (int) ($tagihanBulan['per_status']['belum_bayar'] ?? 0),
                'terlambat'   => (int) ($tagihanBulan['per_status']['terlambat'] ?? 0),
            ],
            'pengingat'         => [
                'registrasi' => setting_aktif('registration_enabled'),
                'due_day'    => (int) (setting('invoice_due_day', 10) ?: 10),
            ],
        ]);
    }
}
