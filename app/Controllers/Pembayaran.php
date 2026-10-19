<?php

namespace App\Controllers;

use App\Models\InvoiceModel;
use App\Models\PaymentModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Pembayaran - pencatatan pembayaran tagihan (mendukung cicilan),
 * unggah bukti transfer, kwitansi siap cetak (khusus admin).
 */
class Pembayaran extends BaseController
{
    private PaymentModel $paymentModel;
    private InvoiceModel $invoiceModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->paymentModel = new PaymentModel();
        $this->invoiceModel = new InvoiceModel();
    }

    /**
     * Daftar pembayaran + pencarian, filter metode & periode.
     */
    public function index(): string
    {
        $this->invoiceModel->tandaiTerlambat();

        $cari   = trim((string) $this->request->getGet('cari'));
        $metode = trim((string) $this->request->getGet('metode'));
        $bulan  = (int) $this->request->getGet('bulan');
        $tahun  = (int) $this->request->getGet('tahun');

        return $this->render('pembayaran/index', [
            'title'       => 'Pembayaran',
            'pembayaran'  => $this->paymentModel->cariDanFilter(
                $cari !== '' ? $cari : null,
                $metode !== '' ? $metode : null,
                $bulan > 0 ? $bulan : null,
                $tahun > 0 ? $tahun : null
            ),
            'pager'       => $this->paymentModel->pager,
            'cari'        => $cari,
            'metode'      => $metode,
            'bulan'       => $bulan,
            'tahun'       => $tahun,
            'ringkasan'   => $this->paymentModel->ringkasan($bulan > 0 ? $bulan : null, $tahun > 0 ? $tahun : null),
            'tunggakan'   => $this->invoiceModel->totalTunggakan(),
            'daftarTahun' => $this->invoiceModel->daftarTahun(),
        ]);
    }

    /**
     * Form pencatatan pembayaran baru.
     */
    public function create(): string
    {
        $invoiceId = (int) $this->request->getGet('tagihan');
        $tagihan   = $invoiceId > 0 ? $this->invoiceModel->detail($invoiceId) : null;

        return $this->render('pembayaran/form', [
            'title'         => 'Catat Pembayaran',
            'aksi'          => base_url('pembayaran/catat'),
            'tagihan'       => $tagihan,
            'daftarTagihan' => $this->invoiceModel->daftarBelumLunas(),
        ]);
    }

    /**
     * Simpan pembayaran (boleh sebagian / cicilan).
     */
    public function store()
    {
        $invoiceId = (int) $this->request->getPost('invoice_id');
        $tagihan   = $this->invoiceModel->detail($invoiceId);

        $jumlah = angka_dari_input($this->request->getPost('amount'));

        $data = [
            'invoice_id' => $invoiceId,
            'amount'     => $jumlah,
            'paid_at'    => (string) $this->request->getPost('paid_at'),
            'method'     => (string) $this->request->getPost('method'),
            'note'       => trim((string) $this->request->getPost('note')) ?: null,
            'created_by' => user_id(),
        ];

        // Bukti transfer (opsional untuk admin).
        $berkas = $this->request->getFile('proof_file');
        if ($berkas !== null && $berkas->isValid() && ! $berkas->hasMoved() && $berkas->getSize() > 0) {
            $hasil = simpan_berkas($berkas, 'pembayaran', [
                'ext'  => ['jpg', 'jpeg', 'png', 'pdf'],
                'mime' => ['image/jpeg', 'image/png', 'application/pdf'],
            ]);

            if (! $hasil['ok']) {
                return redirect()->back()->withInput()->with('gagal', $hasil['pesan']);
            }

            $data['proof_file'] = $hasil['nama'];
        }

        if (! $this->paymentModel->validate($data)) {
            // Berkas sudah terunggah tetapi data tidak valid - bersihkan.
            if (isset($data['proof_file'])) {
                hapus_berkas('pembayaran', $data['proof_file']);
            }

            return redirect()->back()->withInput()->with('errors', $this->paymentModel->errors());
        }

        if ($tagihan === null) {
            return redirect()->back()->withInput()->with('gagal', 'Tagihan yang dipilih tidak ditemukan.');
        }

        if ((float) $jumlah > $tagihan['sisa'] + 0.001) {
            if (isset($data['proof_file'])) {
                hapus_berkas('pembayaran', $data['proof_file']);
            }

            return redirect()->back()->withInput()->with('gagal',
                'Jumlah bayar melebihi sisa tagihan (' . rupiah($tagihan['sisa']) . ').');
        }

        $db = db_connect();
        $db->transBegin();

        if (! $this->paymentModel->insert($data)) {
            $db->transRollback();

            if (isset($data['proof_file'])) {
                hapus_berkas('pembayaran', $data['proof_file']);
            }

            return redirect()->back()->withInput()->with('errors', $this->paymentModel->errors());
        }

        // Hitung ulang status tagihan (lunas / belum bayar / terlambat).
        $this->invoiceModel->sinkronStatusBayar($invoiceId);
        $db->transCommit();

        $pesan = 'Pembayaran ' . rupiah($jumlah) . ' berhasil dicatat.';

        if ($this->invoiceModel->find($invoiceId)['status'] === 'lunas') {
            $pesan .= ' Tagihan sudah lunas.';
        }

        return redirect()->to(base_url('pembayaran'))->with('sukses', $pesan);
    }

    /**
     * Tampilan kwitansi siap cetak.
     */
    public function kwitansi(int $id)
    {
        $pembayaran = $this->paymentModel->detail($id);

        if ($pembayaran === null) {
            return redirect()->to(base_url('pembayaran'))->with('gagal', 'Data pembayaran tidak ditemukan.');
        }

        // Total tagihan & sisa setelah pembayaran ini.
        $tagihan = $this->invoiceModel->detail((int) $pembayaran['invoice_id']);

        return view('pembayaran/kwitansi', [
            'title'      => 'Kwitansi ' . nomor_kwitansi($pembayaran),
            'pembayaran' => $pembayaran,
            'tagihan'    => $tagihan,
        ]);
    }

    /**
     * Hapus pembayaran yang salah input beserta buktinya.
     */
    public function delete(int $id)
    {
        $pembayaran = $this->paymentModel->detail($id);

        if ($pembayaran === null) {
            return redirect()->to(base_url('pembayaran'))->with('gagal', 'Data pembayaran tidak ditemukan.');
        }

        $db = db_connect();
        $db->transBegin();

        $this->paymentModel->delete($id);
        $this->invoiceModel->sinkronStatusBayar((int) $pembayaran['invoice_id']);
        $db->transCommit();

        hapus_berkas('pembayaran', $pembayaran['proof_file']);

        return redirect()->to(base_url('pembayaran'))->with('sukses',
            'Pembayaran ' . rupiah($pembayaran['amount']) . ' untuk '
            . $pembayaran['tenant_name'] . ' berhasil dihapus.');
    }
}
