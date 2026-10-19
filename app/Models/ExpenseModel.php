<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ExpenseModel - pengeluaran operasional kos.
 *
 * Kategori: listrik, air, perbaikan, lainnya.
 * Tabel ini tanpa soft delete karena pengeluaran adalah catatan riwayat.
 */
class ExpenseModel extends Model
{
    protected $table            = 'expenses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['category', 'amount', 'date', 'note'];

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validasi
    protected $validationRules = [
        'category' => ['label' => 'Kategori', 'rules' => 'required|in_list[listrik,air,perbaikan,lainnya]'],
        'amount'   => ['label' => 'Jumlah',   'rules' => 'required|numeric|greater_than[0]'],
        'date'     => ['label' => 'Tanggal',  'rules' => 'required|valid_date'],
        'note'     => ['label' => 'Catatan',  'rules' => 'permit_empty|max_length[500]'],
    ];

    protected $skipValidation = false;

    /** Kategori pengeluaran yang dikenali. */
    public const KATEGORI = ['listrik', 'air', 'perbaikan', 'lainnya'];

    /**
     * Daftar pengeluaran terpaginasi dengan pencarian & filter.
     *
     * @return array<int, array<string, mixed>>
     */
    public function cariDanFilter(?string $cari, ?string $kategori, ?int $bulan, ?int $tahun, int $perHalaman = 10): array
    {
        if ($cari !== null && $cari !== '') {
            $this->groupStart()
                ->like('note', $cari)
                ->orLike('category', $cari)
                ->groupEnd();
        }

        if (in_array($kategori, self::KATEGORI, true)) {
            $this->where('category', $kategori);
        }

        if ($bulan !== null && $bulan >= 1 && $bulan <= 12) {
            $this->where('MONTH(date)', $bulan, false);
        }

        if ($tahun !== null && $tahun >= 2000) {
            $this->where('YEAR(date)', $tahun, false);
        }

        return $this->orderBy('date', 'DESC')
            ->orderBy('id', 'DESC')
            ->paginate($perHalaman);
    }

    /**
     * Ringkasan pengeluaran untuk kartu statistik.
     *
     * @return array<string, mixed>
     */
    public function ringkasan(?int $bulan = null, ?int $tahun = null): array
    {
        $builder = $this->db->table('expenses')->select('id, amount, category');

        if ($bulan !== null && $bulan >= 1 && $bulan <= 12) {
            $builder->where('MONTH(date)', $bulan, false);
        }

        if ($tahun !== null && $tahun >= 2000) {
            $builder->where('YEAR(date)', $tahun, false);
        }

        $rows = $builder->get()->getResultArray();

        $perKategori = array_fill_keys(self::KATEGORI, 0.0);
        $total       = 0.0;

        foreach ($rows as $row) {
            $nominal = (float) $row['amount'];
            $total  += $nominal;
            $perKategori[$row['category']] = ($perKategori[$row['category']] ?? 0) + $nominal;
        }

        return [
            'jumlah'       => count($rows),
            'total'        => $total,
            'per_kategori' => $perKategori,
        ];
    }

    /**
     * Pengeluaran pada satu periode (dipakai laporan bulanan).
     *
     * @return array<int, array<string, mixed>>
     */
    public function untukPeriode(int $bulan, int $tahun): array
    {
        return $this->db->table('expenses')
            ->where('MONTH(date)', $bulan, false)
            ->where('YEAR(date)', $tahun, false)
            ->orderBy('date', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Total pengeluaran pada satu periode.
     */
    public function totalPeriode(int $bulan, int $tahun): float
    {
        $row = $this->db->table('expenses')
            ->selectSum('amount', 'total')
            ->where('MONTH(date)', $bulan, false)
            ->where('YEAR(date)', $tahun, false)
            ->get()
            ->getRowArray();

        return (float) ($row['total'] ?? 0);
    }

    /**
     * Total pengeluaran per bulan untuk beberapa bulan terakhir.
     *
     * @return array<string, float> kunci = "Y-m"
     */
    public function totalPerBulan(int $jumlahBulan = 6): array
    {
        $hasil = [];

        for ($i = $jumlahBulan - 1; $i >= 0; $i--) {
            $awal  = date('Y-m-01', strtotime("-{$i} month"));
            $akhir = date('Y-m-t', strtotime($awal));

            $row = $this->db->table('expenses')
                ->selectSum('amount', 'total')
                ->where('date >=', $awal)
                ->where('date <=', $akhir)
                ->get()
                ->getRowArray();

            $hasil[date('Y-m', strtotime($awal))] = (float) ($row['total'] ?? 0);
        }

        return $hasil;
    }

    /**
     * Daftar tahun yang punya data pengeluaran (untuk filter).
     *
     * @return array<int, int>
     */
    public function daftarTahun(): array
    {
        $rows  = $this->db->table('expenses')->select('date')->get()->getResultArray();
        $hasil = [];

        foreach ($rows as $row) {
            $tahun         = (int) substr((string) $row['date'], 0, 4);
            $hasil[$tahun] = $tahun;
        }

        $hasil[(int) date('Y')] = (int) date('Y');
        krsort($hasil);

        return array_values($hasil);
    }
}
