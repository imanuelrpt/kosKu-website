<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * PaymentModel - pembayaran tagihan (mendukung cicilan / parsial).
 *
 * Satu tagihan boleh dibayar beberapa kali. Setelah pembayaran dicatat,
 * status tagihan dihitung ulang lewat InvoiceModel::sinkronStatusBayar().
 */
class PaymentModel extends Model
{
    protected $table            = 'payments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'invoice_id', 'amount', 'paid_at', 'method',
        'proof_file', 'note', 'created_by',
    ];

    // Timestamps (tanpa soft delete: pembayaran adalah riwayat)
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validasi
    protected $validationRules = [
        'invoice_id' => ['label' => 'Tagihan',       'rules' => 'required|is_natural_no_zero|is_not_unique[invoices.id]'],
        'amount'     => ['label' => 'Jumlah Bayar',  'rules' => 'required|numeric|greater_than[0]'],
        'paid_at'    => ['label' => 'Tanggal Bayar', 'rules' => 'required|valid_date'],
        'method'     => ['label' => 'Metode',        'rules' => 'required|in_list[tunai,transfer]'],
        'proof_file' => ['label' => 'Bukti Transfer', 'rules' => 'permit_empty|max_length[255]'],
        'note'       => ['label' => 'Catatan',       'rules' => 'permit_empty|max_length[500]'],
    ];

    protected $skipValidation = false;

    /** Metode pembayaran yang dikenali. */
    public const METODE = ['tunai', 'transfer'];

    /**
     * Daftar pembayaran terpaginasi dengan pencarian & filter.
     *
     * @return array<int, array<string, mixed>>
     */
    public function cariDanFilter(?string $cari, ?string $metode, ?int $bulan, ?int $tahun, int $perHalaman = 10): array
    {
        $this->select('payments.*, invoices.period_month, invoices.period_year, invoices.amount AS invoice_amount')
            ->select('invoices.status AS invoice_status, occupancies.tenant_id')
            ->select('tenants.name AS tenant_name, tenants.phone AS tenant_phone')
            ->select('rooms.room_number')
            ->join('invoices', 'invoices.id = payments.invoice_id')
            ->join('occupancies', 'occupancies.id = invoices.occupancy_id')
            ->join('tenants', 'tenants.id = occupancies.tenant_id')
            ->join('rooms', 'rooms.id = occupancies.room_id');

        if ($cari !== null && $cari !== '') {
            $this->groupStart()
                ->like('tenants.name', $cari)
                ->orLike('tenants.phone', $cari)
                ->orLike('rooms.room_number', $cari)
                ->orLike('payments.note', $cari)
                ->groupEnd();
        }

        if (in_array($metode, self::METODE, true)) {
            $this->where('payments.method', $metode);
        }

        if ($bulan !== null && $bulan >= 1 && $bulan <= 12) {
            $this->where('MONTH(payments.paid_at)', $bulan, false);
        }

        if ($tahun !== null && $tahun >= 2000) {
            $this->where('YEAR(payments.paid_at)', $tahun, false);
        }

        return $this->orderBy('payments.paid_at', 'DESC')
            ->orderBy('payments.id', 'DESC')
            ->paginate($perHalaman);
    }

    /**
     * Ringkasan pembayaran untuk kartu statistik.
     *
     * @return array<string, mixed>
     */
    public function ringkasan(?int $bulan = null, ?int $tahun = null): array
    {
        $builder = $this->db->table('payments');

        if ($bulan !== null && $bulan >= 1 && $bulan <= 12) {
            $builder->where('MONTH(paid_at)', $bulan, false);
        }

        if ($tahun !== null && $tahun >= 2000) {
            $builder->where('YEAR(paid_at)', $tahun, false);
        }

        $rows = $builder->select('id, amount, method')->get()->getResultArray();

        $total   = 0.0;
        $transfer = 0.0;
        $tunai   = 0.0;

        foreach ($rows as $row) {
            $nominal = (float) $row['amount'];
            $total  += $nominal;

            if ($row['method'] === 'transfer') {
                $transfer += $nominal;
            } else {
                $tunai += $nominal;
            }
        }

        return [
            'jumlah'   => count($rows),
            'total'    => $total,
            'transfer' => $transfer,
            'tunai'    => $tunai,
        ];
    }

    /**
     * Detail satu pembayaran lengkap dengan data tagihan & penghuni.
     *
     * @return array<string, mixed>|null
     */
    public function detail(int $id): ?array
    {
        $row = $this->db->table('payments p')
            ->select('p.*, i.period_month, i.period_year, i.amount AS invoice_amount, i.due_date AS invoice_due')
            ->select('i.occupancy_id, o.tenant_id, o.room_id, o.start_date AS occupancy_start')
            ->select('t.name AS tenant_name, t.phone AS tenant_phone, t.address AS tenant_address')
            ->select('r.room_number, r.floor')
            ->join('invoices i', 'i.id = p.invoice_id')
            ->join('occupancies o', 'o.id = i.occupancy_id')
            ->join('tenants t', 't.id = o.tenant_id')
            ->join('rooms r', 'r.id = o.room_id')
            ->where('p.id', $id)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * Semua pembayaran pada satu tagihan (riwayat cicilan).
     *
     * @return array<int, array<string, mixed>>
     */
    public function untukInvoice(int $invoiceId): array
    {
        return $this->db->table('payments')
            ->where('invoice_id', $invoiceId)
            ->orderBy('paid_at', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Pembayaran pada satu periode (dipakai laporan bulanan).
     *
     * @return array<int, array<string, mixed>>
     */
    public function untukPeriode(int $bulan, int $tahun): array
    {
        return $this->db->table('payments p')
            ->select('p.*, i.period_month, i.period_year, i.status AS invoice_status')
            ->select('t.name AS tenant_name, r.room_number')
            ->join('invoices i', 'i.id = p.invoice_id')
            ->join('occupancies o', 'o.id = i.occupancy_id')
            ->join('tenants t', 't.id = o.tenant_id')
            ->join('rooms r', 'r.id = o.room_id')
            ->where('MONTH(p.paid_at)', $bulan, false)
            ->where('YEAR(p.paid_at)', $tahun, false)
            ->orderBy('p.paid_at', 'ASC')
            ->orderBy('p.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Total pembayaran yang diterima pada satu periode.
     */
    public function totalPeriode(int $bulan, int $tahun): float
    {
        $row = $this->db->table('payments')
            ->selectSum('amount', 'total')
            ->where('MONTH(paid_at)', $bulan, false)
            ->where('YEAR(paid_at)', $tahun, false)
            ->get()
            ->getRowArray();

        return (float) ($row['total'] ?? 0);
    }

    /**
     * Total pembayaran per bulan untuk beberapa bulan terakhir.
     *
     * @return array<string, float> kunci = "Y-m"
     */
    public function totalPerBulan(int $jumlahBulan = 6): array
    {
        $hasil = [];

        for ($i = $jumlahBulan - 1; $i >= 0; $i--) {
            $awal  = date('Y-m-01', strtotime("-{$i} month"));
            $akhir = date('Y-m-t', strtotime($awal));

            $row = $this->db->table('payments')
                ->selectSum('amount', 'total')
                ->where('paid_at >=', $awal)
                ->where('paid_at <=', $akhir)
                ->get()
                ->getRowArray();

            $hasil[date('Y-m', strtotime($awal))] = (float) ($row['total'] ?? 0);
        }

        return $hasil;
    }

    /**
     * Pembayaran terbaru (untuk daftar di dashboard).
     *
     * @return array<int, array<string, mixed>>
     */
    public function terbaru(int $batas = 5): array
    {
        return $this->db->table('payments p')
            ->select('p.*, t.name AS tenant_name, r.room_number, i.period_month, i.period_year')
            ->join('invoices i', 'i.id = p.invoice_id')
            ->join('occupancies o', 'o.id = i.occupancy_id')
            ->join('tenants t', 't.id = o.tenant_id')
            ->join('rooms r', 'r.id = o.room_id')
            ->orderBy('p.paid_at', 'DESC')
            ->orderBy('p.id', 'DESC')
            ->limit($batas)
            ->get()
            ->getResultArray();
    }

    /**
     * Riwayat pembayaran seorang penghuni (terbaru di atas).
     *
     * @return array<int, array<string, mixed>>
     */
    public function untukTenant(int $tenantId): array
    {
        return $this->db->table('payments p')
            ->select('p.*, i.period_month, i.period_year, i.amount AS invoice_amount, r.room_number')
            ->join('invoices i', 'i.id = p.invoice_id')
            ->join('occupancies o', 'o.id = i.occupancy_id')
            ->join('rooms r', 'r.id = o.room_id')
            ->where('o.tenant_id', $tenantId)
            ->orderBy('p.paid_at', 'DESC')
            ->orderBy('p.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Ringkasan pembayaran seorang penghuni (kartu statistik portal).
     *
     * @return array<string, mixed>
     */
    public function ringkasanTenant(int $tenantId): array
    {
        $rows = $this->db->table('payments p')
            ->select('p.id, p.amount, p.method')
            ->join('invoices i', 'i.id = p.invoice_id')
            ->join('occupancies o', 'o.id = i.occupancy_id')
            ->where('o.tenant_id', $tenantId)
            ->get()
            ->getResultArray();

        $total    = 0.0;
        $transfer = 0.0;
        $tunai    = 0.0;

        foreach ($rows as $row) {
            $nominal = (float) $row['amount'];
            $total  += $nominal;

            if ($row['method'] === 'transfer') {
                $transfer += $nominal;
            } else {
                $tunai += $nominal;
            }
        }

        return [
            'jumlah'   => count($rows),
            'total'    => $total,
            'transfer' => $transfer,
            'tunai'    => $tunai,
        ];
    }

    /**
     * Beberapa pembayaran terbaru milik penghuni.
     *
     * @return array<int, array<string, mixed>>
     */
    public function terbaruTenant(int $tenantId, int $batas = 5): array
    {
        return array_slice($this->untukTenant($tenantId), 0, max(0, $batas));
    }

    /**
     * Cari pembayaran lewat nama berkas bukti transfer.
     * Dipakai untuk memeriksa hak akses saat berkas diminta.
     *
     * @return array<string, mixed>|null
     */
    public function cariLewatBukti(string $namaBerkas): ?array
    {
        $row = $this->db->table('payments p')
            ->select('p.id, p.proof_file, t.user_id AS pemilik_user_id')
            ->join('invoices i', 'i.id = p.invoice_id')
            ->join('occupancies o', 'o.id = i.occupancy_id')
            ->join('tenants t', 't.id = o.tenant_id')
            ->where('p.proof_file', $namaBerkas)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }
}
