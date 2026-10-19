<?php

namespace App\Controllers;

use App\Models\InvoiceModel;
use App\Models\OccupancyModel;
use App\Models\RoomModel;
use App\Models\TenantModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Sewa - check-in & check-out penghuni (khusus admin).
 *
 * Check-in  : membuat kontrak sewa baru, kamar otomatis menjadi "terisi".
 * Check-out : menutup kontrak sewa, kamar otomatis kembali "kosong".
 */
class Sewa extends BaseController
{
    private OccupancyModel $occupancyModel;
    private RoomModel $roomModel;
    private TenantModel $tenantModel;
    private InvoiceModel $invoiceModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->occupancyModel = new OccupancyModel();
        $this->roomModel      = new RoomModel();
        $this->tenantModel    = new TenantModel();
        $this->invoiceModel   = new InvoiceModel();
    }

    /**
     * Daftar sewa (riwayat check-in & check-out) + pencarian & pagination.
     */
    public function index(): string
    {
        $cari   = trim((string) $this->request->getGet('cari'));
        $status = trim((string) $this->request->getGet('status'));

        return $this->render('sewa/index', [
            'title'     => 'Sewa / Check-in',
            'sewa'      => $this->occupancyModel->cariDanFilter(
                $cari !== '' ? $cari : null,
                $status !== '' ? $status : null
            ),
            'pager'     => $this->occupancyModel->pager,
            'cari'      => $cari,
            'status'    => $status,
            'ringkasan' => $this->occupancyModel->ringkasan(),
        ]);
    }

    /**
     * Form check-in penghuni.
     */
    public function checkin(): string
    {
        $kamarSiap    = $this->occupancyModel->kamarSiapSewa();
        $penghuniSiap = $this->occupancyModel->penghuniSiapSewa();

        // Bisa dipra-pilih dari daftar kamar (?kamar=) atau detail penghuni (?penghuni=).
        $kamarTerpilih    = (int) $this->request->getGet('kamar');
        $penghuniTerpilih = (int) $this->request->getGet('penghuni');

        $hargaAwal = '';
        foreach ($kamarSiap as $k) {
            if ((int) $k['id'] === $kamarTerpilih) {
                $hargaAwal = rupiah($k['price'], false);
                break;
            }
        }

        // Tanpa pra-pilih: pakai kamar pertama sebagai contoh harga.
        if ($hargaAwal === '' && $kamarSiap !== []) {
            $hargaAwal = rupiah($kamarSiap[0]['price'], false);
        }

        return $this->render('sewa/checkin', [
            'title'            => 'Check-in Penghuni',
            'aksi'             => base_url('sewa/checkin'),
            'kamarSiap'        => $kamarSiap,
            'penghuniSiap'     => $penghuniSiap,
            'kamarTerpilih'    => $kamarTerpilih,
            'penghuniTerpilih' => $penghuniTerpilih,
            'hargaAwal'        => $hargaAwal,
        ]);
    }

    /**
     * Simpan check-in.
     */
    public function checkinSimpan()
    {
        $harga   = angka_dari_input($this->request->getPost('monthly_price'));
        $deposit = angka_dari_input($this->request->getPost('deposit'));

        $data = [
            'tenant_id'     => (int) $this->request->getPost('tenant_id'),
            'room_id'       => (int) $this->request->getPost('room_id'),
            'start_date'    => trim((string) $this->request->getPost('start_date')),
            'end_date'      => null,
            'monthly_price' => $harga,
            'deposit'       => $deposit === '' ? '0' : $deposit,
            'status'        => 'aktif',
        ];

        // Validasi aturan model lebih dulu supaya pesan kesalahan muncul
        // pada masing-masing kolom form.
        if (! $this->occupancyModel->validate($data)) {
            return redirect()->back()->withInput()->with('errors', $this->occupancyModel->errors());
        }

        $penghuni = $this->tenantModel->find($data['tenant_id']);
        $kamar    = $this->roomModel->find($data['room_id']);

        if ($penghuni === null) {
            return redirect()->back()->withInput()->with('gagal', 'Penghuni yang dipilih tidak ditemukan.');
        }

        if ($kamar === null) {
            return redirect()->back()->withInput()->with('gagal', 'Kamar yang dipilih tidak ditemukan.');
        }

        // Aturan bisnis: satu penghuni hanya boleh punya satu sewa aktif,
        // dan satu kamar hanya boleh ditempati satu penghuni.
        if ($this->tenantModel->punyaSewaAktif($data['tenant_id'])) {
            return redirect()->back()->withInput()->with('gagal',
                'Penghuni ' . $penghuni['name'] . ' masih punya sewa aktif. Lakukan check-out terlebih dahulu.');
        }

        if ($this->roomModel->punyaSewaAktif($data['room_id'])) {
            return redirect()->back()->withInput()->with('gagal',
                'Kamar ' . $kamar['room_number'] . ' sudah ditempati penghuni lain.');
        }

        if ($kamar['status'] !== 'kosong') {
            return redirect()->back()->withInput()->with('gagal',
                'Kamar ' . $kamar['room_number'] . ' berstatus "'
                . status_meta('kamar', $kamar['status'])[0]
                . '" sehingga belum bisa disewakan. Ubah statusnya menjadi "Kosong" lebih dulu.');
        }

        $db = db_connect();
        $db->transBegin();

        if (! $this->occupancyModel->insert($data)) {
            $db->transRollback();

            return redirect()->back()->withInput()->with('errors', $this->occupancyModel->errors());
        }

        // Kontrak tersimpan -> kamar otomatis menjadi terisi.
        $this->roomModel->setStatus($data['room_id'], 'terisi');
        $db->transCommit();

        return redirect()->to(base_url('sewa'))->with('sukses',
            'Check-in berhasil: ' . $penghuni['name'] . ' menempati kamar ' . $kamar['room_number'] . '.');
    }

    /**
     * Halaman konfirmasi check-out.
     */
    public function checkout(int $id)
    {
        $sewa = $this->occupancyModel->detail($id);

        if ($sewa === null) {
            return redirect()->to(base_url('sewa'))->with('gagal', 'Data sewa tidak ditemukan.');
        }

        if ($sewa['status'] !== 'aktif') {
            return redirect()->to(base_url('sewa'))->with('gagal',
                'Sewa ini sudah selesai (check-out ' . tanggal_indo($sewa['end_date']) . ').');
        }

        return $this->render('sewa/checkout', [
            'title'   => 'Check-out Penghuni',
            'aksi'    => base_url('sewa/checkout/' . $id),
            'sewa'    => $sewa,
            'tagihan' => $this->invoiceModel->belumLunasSewa($id),
        ]);
    }

    /**
     * Proses check-out.
     */
    public function checkoutSimpan(int $id)
    {
        $sewa = $this->occupancyModel->detail($id);

        if ($sewa === null) {
            return redirect()->to(base_url('sewa'))->with('gagal', 'Data sewa tidak ditemukan.');
        }

        if ($sewa['status'] !== 'aktif') {
            return redirect()->to(base_url('sewa'))->with('gagal', 'Sewa ini sudah selesai (check-out sebelumnya).');
        }

        $tagihan  = $this->invoiceModel->belumLunasSewa($id);
        $tanggal  = trim((string) $this->request->getPost('end_date'));
        $konfirm  = (string) $this->request->getPost('konfirmasi_tagihan');

        if ($tagihan !== [] && $konfirm !== '1') {
            return redirect()->back()->withInput()->with('gagal',
                'Masih ada ' . count($tagihan) . ' tagihan belum lunas. Centang pernyataan konfirmasi bila tetap melanjutkan check-out.');
        }

        // Validasi tanggal (tanpa library tambahan supaya pesannya jelas).
        $ts = strtotime($tanggal);

        if ($tanggal === '' || $ts === false || date('Y-m-d', $ts) !== $tanggal) {
            return redirect()->back()->withInput()->with('gagal', 'Tanggal keluar tidak valid.');
        }

        if ($ts < strtotime((string) $sewa['start_date'])) {
            return redirect()->back()->withInput()->with('gagal',
                'Tanggal keluar tidak boleh lebih awal dari tanggal masuk ('
                . tanggal_indo($sewa['start_date']) . ').');
        }

        if ($ts > strtotime(date('Y-m-d'))) {
            return redirect()->back()->withInput()->with('gagal',
                'Tanggal keluar tidak boleh melebihi hari ini. Catat check-out setelah penghuni benar-benar keluar.');
        }

        $db = db_connect();
        $db->transBegin();

        $this->occupancyModel->tutup($id, $tanggal);
        $this->roomModel->sinkronStatus((int) $sewa['room_id']);   // kamar jadi kosong lagi
        $db->transCommit();

        return redirect()->to(base_url('sewa'))->with('sukses',
            'Check-out berhasil: ' . $sewa['tenant_name'] . ' keluar dari kamar '
            . $sewa['room_number'] . ' pada ' . tanggal_indo($tanggal) . '.');
    }

    /**
     * Batalkan check-in yang salah input.
     *
     * Hanya untuk kontrak yang belum punya tagihan; bila sudah ada tagihan,
     * admin diarahkan memakai check-out agar riwayatnya tetap tercatat.
     */
    public function batal(int $id)
    {
        $sewa = $this->occupancyModel->detail($id);

        if ($sewa === null) {
            return redirect()->to(base_url('sewa'))->with('gagal', 'Data sewa tidak ditemukan.');
        }

        if ($sewa['status'] !== 'aktif') {
            return redirect()->to(base_url('sewa'))->with('gagal',
                'Hanya check-in yang masih aktif yang dapat dibatalkan.');
        }

        if ($this->occupancyModel->punyaTagihan($id)) {
            return redirect()->to(base_url('sewa'))->with('gagal',
                'Sewa ini sudah punya tagihan. Gunakan tombol Check-out agar riwayat keuangannya tetap tercatat.');
        }

        $db = db_connect();
        $db->transBegin();

        $this->occupancyModel->delete($id);
        $this->roomModel->sinkronStatus((int) $sewa['room_id']);
        $db->transCommit();

        return redirect()->to(base_url('sewa'))->with('sukses',
            'Check-in ' . $sewa['tenant_name'] . ' di kamar ' . $sewa['room_number']
            . ' dibatalkan. Kamar kembali tersedia.');
    }
}
