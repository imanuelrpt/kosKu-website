<?php

namespace App\Controllers;

use App\Models\OccupancyModel;
use App\Models\RoomModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Kamar - CRUD data kamar kos (khusus admin).
 */
class Kamar extends BaseController
{
    private RoomModel $roomModel;
    private OccupancyModel $occupancyModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->roomModel      = new RoomModel();
        $this->occupancyModel = new OccupancyModel();
    }

    /**
     * Daftar kamar + pencarian, filter status, dan pagination.
     */
    public function index(): string
    {
        $cari   = trim((string) $this->request->getGet('cari'));
        $status = (string) $this->request->getGet('status');

        $kamar = $this->roomModel->cariDanFilter(
            $cari !== '' ? $cari : null,
            in_array($status, RoomModel::STATUS, true) ? $status : null
        );

        return $this->render('kamar/index', [
            'title'        => 'Data Kamar',
            'kamar'        => $kamar,
            'petaPenghuni' => $this->roomModel->penghuniAktif(array_column($kamar, 'id')),
            'ringkasan'    => $this->roomModel->ringkasan(),
            'pager'        => $this->roomModel->pager,
            'cari'         => $cari,
            'status'       => $status,
        ]);
    }

    /**
     * Form tambah kamar.
     */
    public function create(): string
    {
        return $this->render('kamar/form', [
            'title'     => 'Tambah Kamar',
            'kamar'     => null,
            'aksi'      => base_url('kamar/simpan'),
            'tombol'    => 'Simpan Kamar',
            'sewaAktif' => null,
        ]);
    }

    /**
     * Simpan kamar baru.
     */
    public function store()
    {
        $data = $this->dataDariForm();

        if (! $this->roomModel->insert($data)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->roomModel->errors());
        }

        return redirect()->to(base_url('kamar'))
            ->with('sukses', 'Kamar ' . $data['room_number'] . ' berhasil ditambahkan.');
    }

    /**
     * Form ubah kamar.
     */
    public function edit(int $id)
    {
        $kamar = $this->roomModel->find($id);

        if ($kamar === null) {
            return redirect()->to(base_url('kamar'))->with('gagal', 'Data kamar tidak ditemukan.');
        }

        return $this->render('kamar/form', [
            'title'     => 'Ubah Kamar ' . $kamar['room_number'],
            'kamar'     => $kamar,
            'aksi'      => base_url('kamar/ubah/' . $id),
            'tombol'    => 'Simpan Perubahan',
            'sewaAktif' => $this->occupancyModel->sewaAktifKamar($id),
        ]);
    }

    /**
     * Perbarui data kamar.
     */
    public function update(int $id)
    {
        $kamar = $this->roomModel->find($id);

        if ($kamar === null) {
            return redirect()->to(base_url('kamar'))->with('gagal', 'Data kamar tidak ditemukan.');
        }

        $data = $this->dataDariForm();

        // Nomor kamar unik, tetapi baris yang sedang diubah harus dikecualikan.
        $this->roomModel->setValidationRule(
            'room_number',
            'required|max_length[20]|is_unique[rooms.room_number,id,' . $id . ']'
        );

        // Kamar yang sedang disewa tidak boleh ditandai kosong / perbaikan.
        if ($data['status'] !== 'terisi' && $this->roomModel->punyaSewaAktif($id)) {
            return redirect()->back()->withInput()->with('gagal',
                'Kamar ' . $kamar['room_number'] . ' sedang disewa. Selesaikan sewa (check-out) lebih dulu sebelum mengubah statusnya.');
        }

        if (! $this->roomModel->update($id, $data)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->roomModel->errors());
        }

        return redirect()->to(base_url('kamar'))
            ->with('sukses', 'Kamar ' . $data['room_number'] . ' berhasil diperbarui.');
    }

    /**
     * Hapus kamar.
     *
     * Kamar yang sudah punya riwayat sewa hanya dinonaktifkan (soft delete)
     * agar riwayatnya tetap utuh. Kamar yang belum pernah disewa dihapus
     * permanen supaya nomor kamarnya bisa dipakai lagi.
     */
    public function delete(int $id)
    {
        $kamar = $this->roomModel->find($id);

        if ($kamar === null) {
            return redirect()->to(base_url('kamar'))->with('gagal', 'Data kamar tidak ditemukan.');
        }

        if ($this->roomModel->punyaSewaAktif($id)) {
            return redirect()->to(base_url('kamar'))->with('gagal',
                'Kamar ' . $kamar['room_number'] . ' sedang disewa sehingga tidak bisa dihapus.');
        }

        if ($this->roomModel->punyaRiwayatSewa($id)) {
            $this->roomModel->delete($id);

            return redirect()->to(base_url('kamar'))->with('sukses',
                'Kamar ' . $kamar['room_number'] . ' dihapus dari daftar. Riwayat sewanya tetap tersimpan.');
        }

        $this->roomModel->delete($id, true);

        return redirect()->to(base_url('kamar'))->with('sukses',
            'Kamar ' . $kamar['room_number'] . ' dihapus permanen (belum punya riwayat sewa). Nomor kamar dapat dipakai lagi.');
    }

    /**
     * Ambil & bersihkan data kamar dari form.
     *
     * @return array<string, mixed>
     */
    private function dataDariForm(): array
    {
        return [
            'room_number' => trim((string) $this->request->getPost('room_number')),
            'price'       => angka_dari_input($this->request->getPost('price')),
            'floor'       => angka_dari_input($this->request->getPost('floor')),
            'status'      => (string) $this->request->getPost('status'),
            'facilities'  => trim((string) $this->request->getPost('facilities')) ?: null,
            'notes'       => trim((string) $this->request->getPost('notes')) ?: null,
        ];
    }
}
