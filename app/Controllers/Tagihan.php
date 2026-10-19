<?php

namespace App\Controllers;

use App\Models\InvoiceModel;
use App\Models\OccupancyModel;
use App\Models\PaymentModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Tagihan - daftar tagihan bulanan, pembuatan manual & otomatis,
 * halaman siap cetak, dan pengingat WhatsApp (khusus admin).
 */
class Tagihan extends BaseController
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

    /**
     * Daftar tagihan + pencarian, filter status & periode, pagination.
     */
    public function index(): string
    {
        // Status "terlambat" dijaga selalu mutakhir setiap halaman dibuka.
        $this->invoiceModel->tandaiTerlambat();

        $cari   = trim((string) $this->request->getGet('cari'));
        $status = trim((string) $this->request->getGet('status'));
        $bulan  = (int) $this->request->getGet('bulan');
        $tahun  = (int) $this->request->getGet('tahun');

        return $this->render('tagihan/index', [
            'title'       => 'Tagihan',
            'tagihan'     => $this->invoiceModel->cariDanFilter(
                $cari !== '' ? $cari : null,
                $status !== '' ? $status : null,
                $bulan > 0 ? $bulan : null,
                $tahun > 0 ? $tahun : null
            ),
            'pager'       => $this->invoiceModel->pager,
            'cari'        => $cari,
            'status'      => $status,
            'bulan'       => $bulan,
            'tahun'       => $tahun,
            'ringkasan'   => $this->invoiceModel->ringkasan($bulan > 0 ? $bulan : null, $tahun > 0 ? $tahun : null),
            'tunggakan'   => $this->invoiceModel->totalTunggakan(),
            'daftarTahun' => $this->invoiceModel->daftarTahun(),
        ]);
    }

    /**
     * Detail satu tagihan + riwayat pembayarannya.
     */
    public function show(int $id)
    {
        $tagihan = $this->invoiceModel->detail($id);

        if ($tagihan === null) {
            return redirect()->to(base_url('tagihan'))->with('gagal', 'Data tagihan tidak ditemukan.');
        }

        return $this->render('tagihan/show', [
            'title'      => 'Detail Tagihan',
            'tagihan'    => $tagihan,
            'pembayaran' => $this->paymentModel->untukInvoice($id),
        ]);
    }

    /**
     * Form pembuatan tagihan manual.
     */
    public function create(): string
    {
        $dueDay = (int) (setting('invoice_due_day', 10) ?: 10);

        return $this->render('tagihan/form', [
            'title'  => 'Buat Tagihan',
            'aksi'   => base_url('tagihan/buat'),
            'sewa'   => $this->occupancyModel->daftarAktif(),
            'dueDay' => max(1, min(28, $dueDay)),
        ]);
    }

    /**
     * Simpan tagihan manual.
     */
    public function store()
    {
        $data = [
            'occupancy_id' => (int) $this->request->getPost('occupancy_id'),
            'period_month' => (int) $this->request->getPost('period_month'),
            'period_year'  => (int) $this->request->getPost('period_year'),
            'amount'       => angka_dari_input($this->request->getPost('amount')),
            'due_date'     => (string) $this->request->getPost('due_date'),
            'status'       => 'belum_bayar',
        ];

        // Status awal mengikuti jatuh tempo yang diisi admin.
        $ts = strtotime($data['due_date']);
        if ($ts !== false && $ts < strtotime(date('Y-m-d'))) {
            $data['status'] = 'terlambat';
        }

        if (! $this->invoiceModel->validate($data)) {
            return redirect()->back()->withInput()->with('errors', $this->invoiceModel->errors());
        }

        if ($this->invoiceModel->sudahAda($data['occupancy_id'], $data['period_month'], $data['period_year'])) {
            return redirect()->back()->withInput()->with('gagal',
                'Tagihan untuk sewa dan periode tersebut sudah ada.');
        }

        if ($this->invoiceModel->buat($data) === false) {
            return redirect()->back()->withInput()->with('errors', $this->invoiceModel->errors());
        }

        return redirect()->to(base_url('tagihan'))->with('sukses',
            'Tagihan periode ' . periode_indo($data['period_month'], $data['period_year']) . ' berhasil dibuat.');
    }

    /**
     * Buat tagihan otomatis untuk semua sewa aktif pada satu periode.
     */
    public function generate()
    {
        $bulan = (int) $this->request->getPost('period_month');
        $tahun = (int) $this->request->getPost('period_year');

        if ($bulan < 1 || $bulan > 12 || $tahun < 2000 || $tahun > 2100) {
            return redirect()->back()->with('gagal', 'Periode tagihan tidak valid.');
        }

        $hasil = $this->invoiceModel->generateForPeriod($bulan, $tahun);

        if ($hasil['dibuat'] === 0) {
            return redirect()->to(base_url('tagihan'))->with('gagal',
                'Tidak ada tagihan baru untuk periode ' . $hasil['periode']
                . '. ' . $hasil['dilewati'] . ' sewa sudah punya tagihan atau tidak ada sewa aktif.');
        }

        return redirect()->to(base_url('tagihan'))->with('sukses',
            $hasil['dibuat'] . ' tagihan periode ' . $hasil['periode'] . ' berhasil dibuat. '
            . 'Jatuh tempo ' . tanggal_indo($hasil['jatuh_tempo']) . '.');
    }

    /**
     * Hapus tagihan (hanya bila belum ada pembayaran).
     */
    public function delete(int $id)
    {
        $tagihan = $this->invoiceModel->detail($id);

        if ($tagihan === null) {
            return redirect()->to(base_url('tagihan'))->with('gagal', 'Data tagihan tidak ditemukan.');
        }

        if ($this->paymentModel->untukInvoice($id) !== []) {
            return redirect()->to(base_url('tagihan'))->with('gagal',
                'Tagihan ini sudah punya pembayaran sehingga tidak dapat dihapus. Hapus pembayarannya lebih dulu bila memang salah input.');
        }

        $this->invoiceModel->delete($id);

        return redirect()->to(base_url('tagihan'))->with('sukses',
            'Tagihan ' . periode_indo($tagihan['period_month'], $tagihan['period_year'])
            . ' (' . $tagihan['room_number'] . ') berhasil dihapus.');
    }

    /**
     * Tampilan tagihan siap cetak.
     */
    public function cetak(int $id): string
    {
        $tagihan = $this->invoiceModel->detail($id);

        if ($tagihan === null) {
            return redirect()->to(base_url('tagihan'))->with('gagal', 'Data tagihan tidak ditemukan.');
        }

        return view('tagihan/cetak', [
            'title'      => 'Tagihan ' . nomor_invoice($tagihan),
            'tagihan'    => $tagihan,
            'pembayaran' => $this->paymentModel->untukInvoice($id),
        ]);
    }
}
