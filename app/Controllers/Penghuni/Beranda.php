<?php

namespace App\Controllers\Penghuni;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;
use App\Models\OccupancyModel;
use App\Models\PaymentModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Beranda (dashboard) portal penghuni.
 *
 * Menampilkan ringkasan kamar, tagihan, dan pembayaran milik penghuni
 * yang sedang login. Semua data disaring dengan tenant_id milik akun
 * tersebut sehingga penghuni tidak melihat data penghuni lain.
 */
class Beranda extends BaseController
{
    private InvoiceModel $invoiceModel;
    private PaymentModel $paymentModel;
    private OccupancyModel $occupancyModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->invoiceModel   = new InvoiceModel();
        $this->paymentModel   = new PaymentModel();
        $this->occupancyModel = new OccupancyModel();
    }

    public function index(): string
    {
        // Segarkan status tagihan yang sudah lewat jatuh tempo.
        $this->invoiceModel->tandaiTerlambat();

        $tenant = penghuni_login();

        if ($tenant === null) {
            return $this->render('penghuni/beranda', [
                'title'  => 'Dashboard',
                'tenant' => null,
            ]);
        }

        $tenantId = (int) $tenant['id'];
        $sewa     = $this->occupancyModel->sewaAktifTenant($tenantId);
        $terakhir = null;

        if ($sewa === null) {
            // Tidak sedang menyewa: tampilkan kamar terakhir sebagai riwayat.
            $riwayat  = $this->occupancyModel->riwayatTenant($tenantId);
            $terakhir = $riwayat[0] ?? null;
        }

        $dasar = $sewa ?? $terakhir;

        return $this->render('penghuni/beranda', [
            'title'             => 'Dashboard',
            'tenant'            => $tenant,
            'sewa'              => $sewa,
            'sewaTerakhir'      => $terakhir,
            'ringkasanTagihan'  => $this->invoiceModel->ringkasanTenant($tenantId),
            'ringkasanBayar'    => $this->paymentModel->ringkasanTenant($tenantId),
            'berikutnya'        => $this->invoiceModel->berikutnyaTenant($tenantId),
            'tagihanTerbaru'    => $this->invoiceModel->terbaruTenant($tenantId, 5),
            'pembayaranTerbaru' => $this->paymentModel->terbaruTenant($tenantId, 5),
            'durasi'            => durasi_sewa(
                $dasar['start_date'] ?? null,
                $sewa === null ? ($terakhir['end_date'] ?? null) : null
            ),
        ]);
    }
}
