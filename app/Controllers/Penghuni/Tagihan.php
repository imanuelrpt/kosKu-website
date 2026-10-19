<?php

namespace App\Controllers\Penghuni;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;
use App\Models\PaymentModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Tagihan Saya - daftar, detail, cetak, dan kwitansi tagihan milik
 * penghuni yang sedang login.
 *
 * Setiap permintaan wajib melewati pemeriksaan kepemilikan: bila tagihan
 * (atau pembayaran) bukan milik penghuni tersebut, halaman dianggap tidak
 * ada (404) sehingga keberadaan data penghuni lain tidak terbocor.
 */
class Tagihan extends BaseController
{
    private InvoiceModel $invoiceModel;
    private PaymentModel $paymentModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->invoiceModel = new InvoiceModel();
        $this->paymentModel = new PaymentModel();
    }

    /**
     * Daftar tagihan milik penghuni + filter status/periode.
     */
    public function index(): string
    {
        $this->invoiceModel->tandaiTerlambat();

        $status = trim((string) $this->request->getGet('status'));
        $bulan  = (int) $this->request->getGet('bulan');
        $tahun  = (int) $this->request->getGet('tahun');

        $tenant   = penghuni_login();
        $tenantId = $tenant !== null ? (int) $tenant['id'] : 0;

        if ($tenantId === 0) {
            return $this->render('penghuni/tagihan', [
                'title'  => 'Tagihan Saya',
                'tenant' => null,
            ]);
        }

        return $this->render('penghuni/tagihan', [
            'title'             => 'Tagihan Saya',
            'tenant'            => $tenant,
            'tagihan'           => $this->invoiceModel->cariUntukTenant(
                $tenantId,
                in_array($status, InvoiceModel::STATUS, true) ? $status : null,
                $bulan >= 1 && $bulan <= 12 ? $bulan : null,
                $tahun >= 2000 ? $tahun : null
            ),
            'pager'             => $this->invoiceModel->pager,
            'ringkasan'         => $this->invoiceModel->ringkasanTenant($tenantId),
            'ringkasanBayar'    => $this->paymentModel->ringkasanTenant($tenantId),
            'berikutnya'        => $this->invoiceModel->berikutnyaTenant($tenantId),
            'status'            => $status,
            'bulan'             => $bulan,
            'tahun'             => $tahun,
            'daftarTahun'       => $this->invoiceModel->daftarTahunTenant($tenantId),
        ]);
    }

    /**
     * Detail satu tagihan milik penghuni.
     */
    public function show(int $id): string
    {
        $tenant = $this->wajibPenghuni();

        return $this->render('penghuni/tagihan_show', [
            'title'      => 'Detail Tagihan',
            'tenant'     => $tenant,
            'tagihan'    => $this->tagihanMilik($id, (int) $tenant['id']),
            'pembayaran' => $this->paymentModel->untukInvoice($id),
        ]);
    }

    /**
     * Tagihan versi cetak / simpan PDF.
     */
    public function cetak(int $id)
    {
        $tenant  = $this->wajibPenghuni();
        $tagihan = $this->tagihanMilik($id, (int) $tenant['id']);

        return view('tagihan/cetak', [
            'title'        => 'Tagihan ' . nomor_invoice($tagihan),
            'tagihan'      => $tagihan,
            'pembayaran'   => $this->paymentModel->untukInvoice($id),
            'kembaliUrl'   => base_url('penghuni/tagihan/' . $id),
            'kembaliLabel' => 'Kembali ke Tagihan Saya',
            'waUrl'        => $this->waPengelola($tagihan, $tenant),
            'waLabel'      => 'Hubungi Pengelola',
        ]);
    }

    /**
     * Kwitansi sebuah pembayaran milik penghuni.
     */
    public function kwitansi(int $id)
    {
        $tenant     = $this->wajibPenghuni();
        $pembayaran = $this->pembayaranMilik($id, (int) $tenant['id']);

        return view('pembayaran/kwitansi', [
            'title'        => 'Kwitansi ' . nomor_kwitansi($pembayaran),
            'pembayaran'   => $pembayaran,
            'tagihan'      => $this->invoiceModel->detail((int) $pembayaran['invoice_id']),
            'kembaliUrl'   => base_url('penghuni/tagihan/' . $pembayaran['invoice_id']),
            'kembaliLabel' => 'Kembali ke Tagihan Saya',
            'waUrl'        => false,
            'waLabel'      => 'Hubungi Pengelola',
        ]);
    }

    /**
     * Penghuni yang tertaut pada akun ini, atau 404 bila belum tertaut.
     *
     * @return array<string, mixed>
     */
    private function wajibPenghuni(): array
    {
        $tenant = penghuni_login();

        if ($tenant === null) {
            throw PageNotFoundException::forPageNotFound('Akun Anda belum tertaut ke data penghuni.');
        }

        return $tenant;
    }

    /**
     * Ambil tagihan sekaligus pastikan itu milik penghuni yang login.
     *
     * @return array<string, mixed>
     */
    private function tagihanMilik(int $id, int $tenantId): array
    {
        $tagihan = $this->invoiceModel->detail($id);

        if ($tagihan === null || (int) $tagihan['tenant_id'] !== $tenantId) {
            throw PageNotFoundException::forPageNotFound('Tagihan tidak ditemukan.');
        }

        return $tagihan;
    }

    /**
     * Ambil pembayaran sekaligus pastikan pemiliknya penghuni yang login.
     *
     * @return array<string, mixed>
     */
    private function pembayaranMilik(int $id, int $tenantId): array
    {
        $pembayaran = $this->paymentModel->detail($id);

        if ($pembayaran === null || (int) $pembayaran['tenant_id'] !== $tenantId) {
            throw PageNotFoundException::forPageNotFound('Pembayaran tidak ditemukan.');
        }

        return $pembayaran;
    }

    /**
     * Tautan WhatsApp ke pengelola kos (null bila nomornya belum diatur).
     *
     * @param array<string, mixed> $tagihan
     * @param array<string, mixed> $tenant
     */
    private function waPengelola(array $tagihan, array $tenant): ?string
    {
        $nomor = trim((string) setting('kos_phone', ''));

        if ($nomor === '') {
            return null;
        }

        $pesan = 'Halo ' . setting('kos_name', 'KosKu') . ', saya ' . ($tenant['name'] ?? 'penghuni')
            . ' kamar ' . ($tagihan['room_number'] ?? '-')
            . ' ingin menanyakan tagihan ' . nomor_invoice($tagihan)
            . ' periode ' . periode_indo($tagihan['period_month'] ?? null, $tagihan['period_year'] ?? null)
            . ' sebesar ' . rupiah($tagihan['sisa'] ?? $tagihan['amount'] ?? 0) . '.';

        return wa_link($nomor, $pesan);
    }
}
