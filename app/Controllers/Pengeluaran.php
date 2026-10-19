<?php

namespace App\Controllers;

use App\Models\ExpenseModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Pengeluaran - CRUD biaya operasional kos (khusus admin).
 */
class Pengeluaran extends BaseController
{
    private ExpenseModel $expenseModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->expenseModel = new ExpenseModel();
    }

    /**
     * Daftar pengeluaran + pencarian, filter kategori & periode.
     */
    public function index(): string
    {
        $cari     = trim((string) $this->request->getGet('cari'));
        $kategori = trim((string) $this->request->getGet('kategori'));
        $bulan    = (int) $this->request->getGet('bulan');
        $tahun    = (int) $this->request->getGet('tahun');

        return $this->render('pengeluaran/index', [
            'title'        => 'Pengeluaran',
            'pengeluaran'  => $this->expenseModel->cariDanFilter(
                $cari !== '' ? $cari : null,
                $kategori !== '' ? $kategori : null,
                $bulan > 0 ? $bulan : null,
                $tahun > 0 ? $tahun : null
            ),
            'pager'        => $this->expenseModel->pager,
            'cari'         => $cari,
            'kategori'     => $kategori,
            'bulan'        => $bulan,
            'tahun'        => $tahun,
            'ringkasan'    => $this->expenseModel->ringkasan($bulan > 0 ? $bulan : null, $tahun > 0 ? $tahun : null),
            'ringkasBulan' => $this->expenseModel->ringkasan((int) date('n'), (int) date('Y')),
            'daftarTahun'  => $this->expenseModel->daftarTahun(),
        ]);
    }

    /**
     * Form tambah pengeluaran.
     */
    public function create(): string
    {
        return $this->render('pengeluaran/form', [
            'title'        => 'Tambah Pengeluaran',
            'aksi'         => base_url('pengeluaran/simpan'),
            'pengeluaran'  => null,
        ]);
    }

    /**
     * Simpan pengeluaran baru.
     */
    public function store()
    {
        $data = $this->dataDariForm();

        if (! $this->expenseModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->expenseModel->errors());
        }

        return redirect()->to(base_url('pengeluaran'))->with('sukses',
            'Pengeluaran ' . label_kategori($data['category']) . ' sebesar ' . rupiah($data['amount']) . ' berhasil dicatat.');
    }

    /**
     * Form ubah pengeluaran.
     */
    public function edit(int $id)
    {
        $pengeluaran = $this->expenseModel->find($id);

        if ($pengeluaran === null) {
            return redirect()->to(base_url('pengeluaran'))->with('gagal', 'Data pengeluaran tidak ditemukan.');
        }

        return $this->render('pengeluaran/form', [
            'title'       => 'Ubah Pengeluaran',
            'aksi'        => base_url('pengeluaran/ubah/' . $id),
            'pengeluaran' => $pengeluaran,
        ]);
    }

    /**
     * Perbarui pengeluaran.
     */
    public function update(int $id)
    {
        if ($this->expenseModel->find($id) === null) {
            return redirect()->to(base_url('pengeluaran'))->with('gagal', 'Data pengeluaran tidak ditemukan.');
        }

        $data = $this->dataDariForm();

        if (! $this->expenseModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->expenseModel->errors());
        }

        return redirect()->to(base_url('pengeluaran'))->with('sukses', 'Pengeluaran berhasil diperbarui.');
    }

    /**
     * Hapus pengeluaran.
     */
    public function delete(int $id)
    {
        $pengeluaran = $this->expenseModel->find($id);

        if ($pengeluaran === null) {
            return redirect()->to(base_url('pengeluaran'))->with('gagal', 'Data pengeluaran tidak ditemukan.');
        }

        $this->expenseModel->delete($id);

        return redirect()->to(base_url('pengeluaran'))->with('sukses',
            'Pengeluaran ' . label_kategori($pengeluaran['category']) . ' tanggal '
            . tanggal_indo($pengeluaran['date']) . ' berhasil dihapus.');
    }

    /**
     * Ambil & bersihkan data pengeluaran dari form.
     *
     * @return array<string, mixed>
     */
    private function dataDariForm(): array
    {
        return [
            'category' => (string) $this->request->getPost('category'),
            'amount'   => angka_dari_input($this->request->getPost('amount')),
            'date'     => (string) $this->request->getPost('date'),
            'note'     => trim((string) $this->request->getPost('note')) ?: null,
        ];
    }
}
