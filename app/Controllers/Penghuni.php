<?php

namespace App\Controllers;

use App\Models\InvoiceModel;
use App\Models\OccupancyModel;
use App\Models\PaymentModel;
use App\Models\TenantModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Penghuni - CRUD data penghuni + halaman detail (khusus admin).
 */
class Penghuni extends BaseController
{
    private TenantModel $tenantModel;
    private UserModel $userModel;
    private OccupancyModel $occupancyModel;
    private InvoiceModel $invoiceModel;
    private PaymentModel $paymentModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->tenantModel    = new TenantModel();
        $this->userModel      = new UserModel();
        $this->occupancyModel = new OccupancyModel();
        $this->invoiceModel   = new InvoiceModel();
        $this->paymentModel   = new PaymentModel();
    }

    /**
     * Daftar penghuni + pencarian & pagination.
     */
    public function index(): string
    {
        $cari = trim((string) $this->request->getGet('cari'));

        $penghuni = $this->tenantModel->cari($cari !== '' ? $cari : null);

        return $this->render('penghuni/index', [
            'title'     => 'Data Penghuni',
            'penghuni'  => $penghuni,
            'petaKamar' => $this->tenantModel->kamarAktif(array_column($penghuni, 'id')),
            'pager'     => $this->tenantModel->pager,
            'cari'      => $cari,
            'ringkasan' => [
                'total'   => $this->tenantModel->jumlahAktif(),
                'menyewa' => $this->tenantModel->jumlahMenyewa(),
            ],
        ]);
    }

    /**
     * Form tambah penghuni.
     */
    public function create(): string
    {
        return $this->render('penghuni/form', [
            'title'      => 'Tambah Penghuni',
            'penghuni'   => null,
            'aksi'       => base_url('penghuni/simpan'),
            'tombol'     => 'Simpan Penghuni',
            'akun'       => $this->tenantModel->akunTersedia(),
            'kamarAktif' => null,
        ]);
    }

    /**
     * Simpan penghuni baru (termasuk unggah foto opsional).
     */
    public function store()
    {
        $data = $this->dataDariForm();

        if ($data['user_id'] !== null && $this->tenantModel->akunSudahDipakai($data['user_id'])) {
            return redirect()->back()->withInput()
                ->with('gagal', 'Akun login tersebut sudah tertaut ke penghuni lain.');
        }

        $foto = $this->prosesFoto(null, false);

        if (! $foto['ok']) {
            return redirect()->back()->withInput()->with('gagal', $foto['pesan']);
        }

        $data['photo'] = $foto['nama'];

        if (! $this->tenantModel->insert($data)) {
            // Jangan tinggalkan berkas yatim bila data gagal disimpan.
            hapus_berkas('penghuni', $foto['nama']);

            return redirect()->back()->withInput()
                ->with('errors', $this->tenantModel->errors());
        }

        return redirect()->to(base_url('penghuni'))
            ->with('sukses', 'Penghuni ' . $data['name'] . ' berhasil ditambahkan.');
    }

    /**
     * Detail penghuni: data diri, kamar, riwayat sewa, tagihan, pembayaran.
     */
    public function show(int $id)
    {
        $penghuni = $this->tenantModel->find($id);

        if ($penghuni === null) {
            return redirect()->to(base_url('penghuni'))->with('gagal', 'Data penghuni tidak ditemukan.');
        }

        $riwayatSewa = $this->occupancyModel->riwayatTenant($id);
        $tagihan     = $this->invoiceModel->untukTenant($id);
        $pembayaran  = $this->paymentModel->untukTenant($id);

        $totalTagihan = array_sum(array_map('floatval', array_column($tagihan, 'amount')));
        $totalDibayar = array_sum(array_map('floatval', array_column($pembayaran, 'amount')));

        return $this->render('penghuni/show', [
            'title'        => 'Detail Penghuni',
            'penghuni'     => $penghuni,
            'akun'         => $penghuni['user_id'] !== null ? $this->userModel->find($penghuni['user_id']) : null,
            'sewaAktif'    => $this->occupancyModel->sewaAktifTenant($id),
            'riwayatSewa'  => $riwayatSewa,
            'tagihan'      => $tagihan,
            'pembayaran'   => $pembayaran,
            'ringkasan'    => [
                'tagihan' => $totalTagihan,
                'dibayar' => $totalDibayar,
                'sisa'    => max(0, $totalTagihan - $totalDibayar),
            ],
        ]);
    }

    /**
     * Form ubah penghuni.
     */
    public function edit(int $id)
    {
        $penghuni = $this->tenantModel->find($id);

        if ($penghuni === null) {
            return redirect()->to(base_url('penghuni'))->with('gagal', 'Data penghuni tidak ditemukan.');
        }

        return $this->render('penghuni/form', [
            'title'      => 'Ubah Penghuni',
            'penghuni'   => $penghuni,
            'aksi'       => base_url('penghuni/ubah/' . $id),
            'tombol'     => 'Simpan Perubahan',
            'akun'       => $this->tenantModel->akunTersedia($id),
            'kamarAktif' => $this->occupancyModel->sewaAktifTenant($id),
        ]);
    }

    /**
     * Perbarui data penghuni.
     */
    public function update(int $id)
    {
        $penghuni = $this->tenantModel->find($id);

        if ($penghuni === null) {
            return redirect()->to(base_url('penghuni'))->with('gagal', 'Data penghuni tidak ditemukan.');
        }

        $data = $this->dataDariForm();

        if ($data['user_id'] !== null && $this->tenantModel->akunSudahDipakai($data['user_id'], $id)) {
            return redirect()->back()->withInput()
                ->with('gagal', 'Akun login tersebut sudah tertaut ke penghuni lain.');
        }

        $foto = $this->prosesFoto($penghuni['photo'], $this->request->getPost('hapus_foto') === '1');

        if (! $foto['ok']) {
            return redirect()->back()->withInput()->with('gagal', $foto['pesan']);
        }

        $data['photo'] = $foto['nama'];
        $fotoLama      = $penghuni['photo'];

        if (! $this->tenantModel->update($id, $data)) {
            // Berkas baru yang sudah terunggah dibuang agar tidak menjadi sampah.
            if ($foto['baru'] && $foto['nama'] !== null) {
                hapus_berkas('penghuni', $foto['nama']);
            }

            return redirect()->back()->withInput()
                ->with('errors', $this->tenantModel->errors());
        }

        // Hapus berkas lama setelah data baru tersimpan.
        if ($foto['baru'] && $fotoLama !== null && $fotoLama !== $foto['nama']) {
            hapus_berkas('penghuni', $fotoLama);
        }

        return redirect()->to(base_url('penghuni/' . $id))
            ->with('sukses', 'Data penghuni ' . $data['name'] . ' berhasil diperbarui.');
    }

    /**
     * Hapus penghuni (soft delete).
     */
    public function delete(int $id)
    {
        $penghuni = $this->tenantModel->find($id);

        if ($penghuni === null) {
            return redirect()->to(base_url('penghuni'))->with('gagal', 'Data penghuni tidak ditemukan.');
        }

        if ($this->tenantModel->punyaSewaAktif($id)) {
            return redirect()->to(base_url('penghuni'))->with('gagal',
                'Penghuni ' . $penghuni['name'] . ' masih punya sewa aktif. Lakukan check-out terlebih dahulu.');
        }

        $this->tenantModel->delete($id);

        return redirect()->to(base_url('penghuni'))
            ->with('sukses', 'Penghuni ' . $penghuni['name'] . ' berhasil dihapus. Riwayat sewanya tetap tersimpan.');
    }

    /**
     * Ambil & bersihkan data penghuni dari form.
     *
     * @return array<string, mixed>
     */
    private function dataDariForm(): array
    {
        $userId = (string) $this->request->getPost('user_id');

        return [
            'user_id'           => ($userId === '' || $userId === '0') ? null : (int) $userId,
            'name'              => trim((string) $this->request->getPost('name')),
            'phone'             => trim((string) $this->request->getPost('phone')) ?: null,
            'id_number'         => trim((string) $this->request->getPost('id_number')) ?: null,
            'address'           => trim((string) $this->request->getPost('address')) ?: null,
            'emergency_contact' => trim((string) $this->request->getPost('emergency_contact')) ?: null,
        ];
    }

    /**
     * Tentukan nama foto penghuni setelah proses unggah / hapus.
     *
     * @return array{ok: bool, nama: string|null, baru: bool, pesan: string|null}
     */
    private function prosesFoto(?string $fotoLama, bool $mintaHapus): array
    {
        $berkas = $this->request->getFile('photo');

        // Ada berkas baru yang diunggah?
        if ($berkas !== null && $berkas->getError() !== UPLOAD_ERR_NO_FILE) {
            $hasil = simpan_berkas($berkas, 'penghuni');

            if (! $hasil['ok']) {
                return ['ok' => false, 'nama' => null, 'baru' => false, 'pesan' => $hasil['pesan']];
            }

            return ['ok' => true, 'nama' => $hasil['nama'], 'baru' => true, 'pesan' => null];
        }

        // Admin mencentang "hapus foto".
        if ($mintaHapus) {
            return ['ok' => true, 'nama' => null, 'baru' => true, 'pesan' => null];
        }

        return ['ok' => true, 'nama' => $fotoLama, 'baru' => false, 'pesan' => null];
    }
}
