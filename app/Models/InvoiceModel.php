<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * InvoiceModel - tagihan bulanan per sewa.
 *
 * Aturan jatuh tempo mengikuti pengaturan `invoice_due_day` (default 10),
 * yaitu tanggal tersebut pada bulan berikutnya setelah periode tagihan.
 * Contoh: tagihan Oktober 2026 jatuh tempo 10 November 2026.
 *
 * Catatan: paginate() memakai builder milik model ini, jadi kolom pada
 * select()/join() ditulis dengan nama tabel lengkap (tanpa alias).
 */
class InvoiceModel extends Model
{
    protected $table            = 'invoices';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'occupancy_id', 'period_month', 'period_year',
        'amount', 'due_date', 'status',
    ];

    // Timestamps (tanpa soft delete: tagihan adalah riwayat)
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validasi
    protected $validationRules = [
        'occupancy_id' => ['label' => 'Sewa',        'rules' => 'required|is_natural_no_zero|is_not_unique[occupancies.id]'],
        'period_month' => ['label' => 'Bulan',       'rules' => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[12]'],
        'period_year'  => ['label' => 'Tahun',       'rules' => 'required|integer|greater_than_equal_to[2000]|less_than_equal_to[2100]'],
        'amount'       => ['label' => 'Jumlah',      'rules' => 'required|numeric|greater_than_equal_to[0]'],
        'due_date'     => ['label' => 'Jatuh Tempo', 'rules' => 'required|valid_date'],
        'status'       => ['label' => 'Status',      'rules' => 'required|in_list[belum_bayar,lunas,terlambat]'],
    ];

    protected $skipValidation = false;

    /** Status tagihan yang dikenali. */
    public const STATUS = ['belum_bayar', 'lunas', 'terlambat'];

    /**
     * Daftar tagihan terpaginasi dengan pencarian & filter.
     *
     * @return array<int, array<string, mixed>>
     */
    public function cariDanFilter(?string $cari, ?string $status, ?int $bulan, ?int $tahun, int $perHalaman = 10): array
    {
        $this->select('invoices.*, occupancies.tenant_id, occupancies.room_id')
            ->select('tenants.name AS tenant_name, tenants.phone AS tenant_phone')
            ->select('rooms.room_number, rooms.floor')
            ->join('occupancies', 'occupancies.id = invoices.occupancy_id')
            ->join('tenants', 'tenants.id = occupancies.tenant_id')
            ->join('rooms', 'rooms.id = occupancies.room_id');

        if ($cari !== null && $cari !== '') {
            $this->groupStart()
                ->like('tenants.name', $cari)
                ->orLike('tenants.phone', $cari)
                ->orLike('rooms.room_number', $cari)
                ->groupEnd();
        }

        if (in_array($status, self::STATUS, true)) {
            $this->where('invoices.status', $status);
        }

        if ($bulan !== null && $bulan >= 1 && $bulan <= 12) {
            $this->where('invoices.period_month', $bulan);
        }

        if ($tahun !== null && $tahun >= 2000) {
            $this->where('invoices.period_year', $tahun);
        }

        $rows = $this->orderBy('invoices.period_year', 'DESC')
            ->orderBy('invoices.period_month', 'DESC')
            ->orderBy('rooms.room_number', 'ASC')
            ->orderBy('invoices.id', 'ASC')
            ->paginate($perHalaman);

        return $this->lengkapiPembayaran($rows);
    }

    /**
     * Tambahkan kolom `dibayar` & `sisa` ke daftar tagihan (satu query).
     *
     * @param array<int, array<string, mixed>> $rows
     *
     * @return array<int, array<string, mixed>>
     */
    public function lengkapiPembayaran(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        $peta = $this->totalPembayaran(array_column($rows, 'id'));

        foreach ($rows as &$row) {
            $row['dibayar'] = (float) ($peta[(int) $row['id']] ?? 0);
            $row['sisa']    = max(0, (float) $row['amount'] - $row['dibayar']);
        }
        unset($row);

        return $rows;
    }

    /**
     * Ringkasan tagihan untuk kartu statistik.
     *
     * @return array<string, mixed>
     */
    public function ringkasan(?int $bulan = null, ?int $tahun = null): array
    {
        $perStatus = ['belum_bayar' => 0, 'lunas' => 0, 'terlambat' => 0];

        $builder = $this->db->table('invoices')->select('id, amount, status');

        if ($bulan !== null && $bulan >= 1 && $bulan <= 12) {
            $builder->where('period_month', $bulan);
        }

        if ($tahun !== null && $tahun >= 2000) {
            $builder->where('period_year', $tahun);
        }

        $rows = $builder->get()->getResultArray();

        $total = 0.0;
        $ids   = [];
        foreach ($rows as $row) {
            $total += (float) $row['amount'];
            $ids[]  = (int) $row['id'];
            $perStatus[$row['status']] = ($perStatus[$row['status']] ?? 0) + 1;
        }

        $dibayar = $ids === [] ? 0.0 : array_sum($this->totalPembayaran($ids));

        return [
            'jumlah'     => count($rows),
            'total'      => $total,
            'dibayar'    => $dibayar,
            'sisa'       => max(0, $total - $dibayar),
            'per_status' => $perStatus,
            'terlambat'  => $perStatus['terlambat'],
        ];
    }

    /**
     * Ubah tagihan yang lewat jatuh tempo menjadi "terlambat".
     * Tagihan yang sudah lunas tidak diubah.
     *
     * @return int jumlah baris yang diubah
     */
    public function tandaiTerlambat(): int
    {
        return (int) $this->db->table('invoices')
            ->where('status', 'belum_bayar')
            ->where('due_date <', date('Y-m-d'))
            ->update(['status' => 'terlambat', 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Apakah tagihan periode tertentu sudah ada untuk sewa ini?
     */
    public function sudahAda(int $occupancyId, int $bulan, int $tahun): bool
    {
        return $this->db->table('invoices')
            ->where('occupancy_id', $occupancyId)
            ->where('period_month', $bulan)
            ->where('period_year', $tahun)
            ->countAllResults() > 0;
    }

    /**
     * Simpan satu tagihan baru. Mengembalikan ID atau false bila gagal.
     *
     * @param array<string, mixed> $data
     *
     * @return int|false
     */
    public function buat(array $data)
    {
        if (! $this->insert($data)) {
            return false;
        }

        return (int) $this->getInsertID();
    }

    /**
     * Hitung ulang status tagihan dari pembayaran yang sudah masuk.
     *
     * lunas     -> pembayaran >= jumlah tagihan
     * terlambat -> belum lunas & sudah lewat jatuh tempo
     * lainnya   -> belum_bayar
     */
    public function sinkronStatusBayar(int $invoiceId): void
    {
        $invoice = $this->find($invoiceId);

        if ($invoice === null) {
            return;
        }

        $dibayar = (float) ($this->totalPembayaran([$invoiceId])[$invoiceId] ?? 0);

        if ($dibayar + 0.001 >= (float) $invoice['amount']) {
            $statusBaru = 'lunas';
        } elseif (strtotime((string) $invoice['due_date']) < strtotime(date('Y-m-d'))) {
            $statusBaru = 'terlambat';
        } else {
            $statusBaru = 'belum_bayar';
        }

        if ($statusBaru !== $invoice['status']) {
            // Query Builder langsung: perubahan status tidak perlu validasi penuh.
            $this->db->table('invoices')->where('id', $invoiceId)->update([
                'status'     => $statusBaru,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Detail satu tagihan lengkap dengan penghuni, kamar, dan sewa.
     *
     * @return array<string, mixed>|null
     */
    public function detail(int $id): ?array
    {
        $row = $this->db->table('invoices i')
            ->select('i.*, o.tenant_id, o.room_id, o.start_date AS occupancy_start, o.status AS occupancy_status')
            ->select('t.name AS tenant_name, t.phone AS tenant_phone, t.address AS tenant_address')
            ->select('r.room_number, r.floor, r.price AS harga_kamar')
            ->join('occupancies o', 'o.id = i.occupancy_id')
            ->join('tenants t', 't.id = o.tenant_id')
            ->join('rooms r', 'r.id = o.room_id')
            ->where('i.id', $id)
            ->get()
            ->getRowArray();

        if ($row === null) {
            return null;
        }

        $dibayar = (float) ($this->totalPembayaran([$id])[$id] ?? 0);
        $row['dibayar'] = $dibayar;
        $row['sisa']    = max(0, (float) $row['amount'] - $dibayar);

        return $row;
    }

    /**
     * Semua tagihan pada satu periode (dipakai laporan bulanan).
     *
     * @return array<int, array<string, mixed>>
     */
    public function untukPeriode(int $bulan, int $tahun): array
    {
        $rows = $this->db->table('invoices i')
            ->select('i.*, o.tenant_id')
            ->select('t.name AS tenant_name, r.room_number')
            ->join('occupancies o', 'o.id = i.occupancy_id')
            ->join('tenants t', 't.id = o.tenant_id')
            ->join('rooms r', 'r.id = o.room_id')
            ->where('i.period_month', $bulan)
            ->where('i.period_year', $tahun)
            ->orderBy('r.room_number', 'ASC')
            ->get()
            ->getResultArray();

        return $this->lengkapiPembayaran($rows);
    }

    /**
     * Semua tagihan yang masih punya sisa (belum lunas), lengkap dengan
     * sisa terkini. Dipakai pada form pencatatan pembayaran.
     *
     * @return array<int, array<string, mixed>>
     */
    public function daftarBelumLunas(): array
    {
        $rows = $this->db->table('invoices i')
            ->select('i.id, i.period_month, i.period_year, i.amount, i.due_date, i.status')
            ->select('o.id AS occupancy_id, t.name AS tenant_name, r.room_number')
            ->join('occupancies o', 'o.id = i.occupancy_id')
            ->join('tenants t', 't.id = o.tenant_id')
            ->join('rooms r', 'r.id = o.room_id')
            ->whereNotIn('i.status', ['lunas'])
            ->orderBy('i.due_date', 'ASC')
            ->orderBy('i.id', 'ASC')
            ->get()
            ->getResultArray();

        $rows = $this->lengkapiPembayaran($rows);

        return array_values(array_filter($rows, static fn (array $r): bool => $r['sisa'] > 0));
    }

    /**
     * Total sisa tagihan yang belum lunas (semua periode).
     */
    public function totalTunggakan(): float
    {
        $rows = $this->db->table('invoices')
            ->select('id, amount')
            ->whereNotIn('status', ['lunas'])
            ->get()
            ->getResultArray();

        if ($rows === []) {
            return 0.0;
        }

        $peta  = $this->totalPembayaran(array_column($rows, 'id'));
        $total = 0.0;

        foreach ($rows as $row) {
            $sisa = (float) $row['amount'] - (float) ($peta[(int) $row['id']] ?? 0);
            if ($sisa > 0) {
                $total += $sisa;
            }
        }

        return $total;
    }

    /**
     * Tagihan terlambat terbaru (untuk daftar di dashboard).
     *
     * @return array<int, array<string, mixed>>
     */
    public function terlambatTerbaru(int $batas = 5): array
    {
        $rows = $this->db->table('invoices i')
            ->select('i.*, t.name AS tenant_name, t.phone AS tenant_phone, r.room_number')
            ->join('occupancies o', 'o.id = i.occupancy_id')
            ->join('tenants t', 't.id = o.tenant_id')
            ->join('rooms r', 'r.id = o.room_id')
            ->where('i.status', 'terlambat')
            ->orderBy('i.due_date', 'ASC')
            ->limit($batas)
            ->get()
            ->getResultArray();

        return $this->lengkapiPembayaran($rows);
    }

    /**
     * Daftar tahun yang punya data tagihan (untuk filter).
     *
     * @return array<int, int>
     */
    public function daftarTahun(): array
    {
        $tahun = array_column(
            $this->db->table('invoices')->select('period_year')->distinct()->orderBy('period_year', 'DESC')->get()->getResultArray(),
            'period_year'
        );

        $tahun = array_map('intval', $tahun);

        if (! in_array((int) date('Y'), $tahun, true)) {
            array_unshift($tahun, (int) date('Y'));
        }

        return $tahun;
    }

    /**
     * Buat tagihan untuk SEMUA sewa aktif pada satu periode.
     *
     * Dipakai oleh perintah `php spark invoices:generate` maupun tombol
     * "Buat Tagihan Otomatis" di halaman Tagihan.
     *
     * @return array{dibuat: int, dilewati: int, periode: string, jatuh_tempo: string, rincian: list<string>}
     */
    public function generateForPeriod(int $bulan, int $tahun): array
    {
        $dueDay = (int) (function_exists('setting') ? setting('invoice_due_day', 10) : 10);
        // Batasi 1-28 supaya selalu tanggal yang valid di bulan mana pun.
        $dueDay = max(1, min(28, $dueDay > 0 ? $dueDay : 10));

        $awalPeriode  = sprintf('%04d-%02d-01', $tahun, $bulan);
        $akhirPeriode = date('Y-m-t', strtotime($awalPeriode));
        $bulanDepan   = date('Y-m', strtotime($awalPeriode . ' +1 month'));
        $jatuhTempo   = $bulanDepan . '-' . str_pad((string) $dueDay, 2, '0', STR_PAD_LEFT);

        // Sewa yang masih berjalan pada periode tersebut.
        $sewa = $this->db->table('occupancies o')
            ->select('o.id, o.monthly_price, t.name AS tenant_name, r.room_number')
            ->join('tenants t', 't.id = o.tenant_id')
            ->join('rooms r', 'r.id = o.room_id')
            ->where('o.status', 'aktif')
            ->where('o.start_date <=', $akhirPeriode)
            ->groupStart()
                ->where('o.end_date', null)
                ->orWhere('o.end_date >=', $awalPeriode)
            ->groupEnd()
            ->orderBy('o.id', 'ASC')
            ->get()
            ->getResultArray();

        $sudah = array_map('intval', array_column(
            $this->db->table('invoices')
                ->select('occupancy_id')
                ->where('period_month', $bulan)
                ->where('period_year', $tahun)
                ->get()
                ->getResultArray(),
            'occupancy_id'
        ));

        $statusAwal = strtotime($jatuhTempo) < strtotime(date('Y-m-d')) ? 'terlambat' : 'belum_bayar';

        $dibuat   = 0;
        $dilewati = 0;
        $rincian  = [];

        foreach ($sewa as $s) {
            if (in_array((int) $s['id'], $sudah, true)) {
                $dilewati++;
                continue;
            }

            $hasil = $this->buat([
                'occupancy_id' => (int) $s['id'],
                'period_month' => $bulan,
                'period_year'  => $tahun,
                'amount'       => (float) $s['monthly_price'],
                'due_date'     => $jatuhTempo,
                'status'       => $statusAwal,
            ]);

            if ($hasil === false) {
                $dilewati++;
                continue;
            }

            $dibuat++;
            $rincian[] = $s['tenant_name'] . ' - kamar ' . $s['room_number'] . ' - ' . number_format((float) $s['monthly_price'], 0, ',', '.');
        }

        return [
            'dibuat'      => $dibuat,
            'dilewati'    => $dilewati,
            'periode'     => trim(bulan_indo($bulan) . ' ' . $tahun),
            'jatuh_tempo' => $jatuhTempo,
            'rincian'     => $rincian,
        ];
    }

    /**
     * Semua tagihan milik seorang penghuni, dilengkapi total pembayaran
     * dan sisa tagihan. Dipakai di halaman detail penghuni.
     *
     * @return array<int, array<string, mixed>>
     */
    public function untukTenant(int $tenantId): array
    {
        $tagihan = $this->db->table('invoices i')
            ->select('i.*, o.room_id, r.room_number')
            ->join('occupancies o', 'o.id = i.occupancy_id')
            ->join('rooms r', 'r.id = o.room_id')
            ->where('o.tenant_id', $tenantId)
            ->orderBy('i.period_year', 'DESC')
            ->orderBy('i.period_month', 'DESC')
            ->orderBy('i.id', 'DESC')
            ->get()
            ->getResultArray();

        return $this->lengkapiPembayaran($tagihan);
    }

    /**
     * Ringkasan tagihan seorang penghuni (kartu statistik portal penghuni).
     *
     * @return array<string, mixed>
     */
    public function ringkasanTenant(int $tenantId): array
    {
        $perStatus = ['belum_bayar' => 0, 'lunas' => 0, 'terlambat' => 0];

        $rows = $this->db->table('invoices i')
            ->select('i.id, i.amount, i.status')
            ->join('occupancies o', 'o.id = i.occupancy_id')
            ->where('o.tenant_id', $tenantId)
            ->get()
            ->getResultArray();

        $total = 0.0;
        $ids   = [];

        foreach ($rows as $row) {
            $total += (float) $row['amount'];
            $ids[]  = (int) $row['id'];
            $perStatus[$row['status']] = ($perStatus[$row['status']] ?? 0) + 1;
        }

        $dibayar = $ids === [] ? 0.0 : array_sum($this->totalPembayaran($ids));

        return [
            'jumlah'     => count($rows),
            'total'      => $total,
            'dibayar'    => $dibayar,
            'sisa'       => max(0, $total - $dibayar),
            'per_status' => $perStatus,
            'terlambat'  => $perStatus['terlambat'],
        ];
    }

    /**
     * Tagihan milik satu penghuni untuk portal penghuni (terpaginasi + filter).
     *
     * @return array<int, array<string, mixed>>
     */
    public function cariUntukTenant(int $tenantId, ?string $status, ?int $bulan, ?int $tahun, int $perHalaman = 10): array
    {
        $this->select('invoices.*, occupancies.tenant_id, occupancies.room_id')
            ->select('rooms.room_number, rooms.floor')
            ->join('occupancies', 'occupancies.id = invoices.occupancy_id')
            ->join('rooms', 'rooms.id = occupancies.room_id')
            ->where('occupancies.tenant_id', $tenantId);

        if (in_array($status, self::STATUS, true)) {
            $this->where('invoices.status', $status);
        }

        if ($bulan !== null && $bulan >= 1 && $bulan <= 12) {
            $this->where('invoices.period_month', $bulan);
        }

        if ($tahun !== null && $tahun >= 2000) {
            $this->where('invoices.period_year', $tahun);
        }

        $rows = $this->orderBy('invoices.period_year', 'DESC')
            ->orderBy('invoices.period_month', 'DESC')
            ->orderBy('invoices.id', 'DESC')
            ->paginate($perHalaman);

        return $this->lengkapiPembayaran($rows);
    }

    /**
     * Tagihan belum lunas dengan jatuh tempo paling awal milik penghuni.
     *
     * @return array<string, mixed>|null
     */
    public function berikutnyaTenant(int $tenantId): ?array
    {
        $row = $this->db->table('invoices i')
            ->select('i.*, r.room_number')
            ->join('occupancies o', 'o.id = i.occupancy_id')
            ->join('rooms r', 'r.id = o.room_id')
            ->where('o.tenant_id', $tenantId)
            ->whereNotIn('i.status', ['lunas'])
            ->orderBy('i.due_date', 'ASC')
            ->orderBy('i.id', 'ASC')
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($row === null) {
            return null;
        }

        $dibayar = (float) ($this->totalPembayaran([(int) $row['id']])[(int) $row['id']] ?? 0);
        $row['dibayar'] = $dibayar;
        $row['sisa']    = max(0, (float) $row['amount'] - $dibayar);

        return $row['sisa'] > 0 ? $row : null;
    }

    /**
     * Beberapa tagihan terbaru milik penghuni.
     *
     * @return array<int, array<string, mixed>>
     */
    public function terbaruTenant(int $tenantId, int $batas = 5): array
    {
        return array_slice($this->untukTenant($tenantId), 0, max(0, $batas));
    }

    /**
     * Tahun yang punya tagihan untuk penghuni tertentu (pengisi filter).
     *
     * @return array<int, int>
     */
    public function daftarTahunTenant(int $tenantId): array
    {
        $tahun = array_map('intval', array_column(
            $this->db->table('invoices i')
                ->select('i.period_year')
                ->join('occupancies o', 'o.id = i.occupancy_id')
                ->where('o.tenant_id', $tenantId)
                ->distinct()
                ->orderBy('i.period_year', 'DESC')
                ->get()
                ->getResultArray(),
            'period_year'
        ));

        if (! in_array((int) date('Y'), $tahun, true)) {
            array_unshift($tahun, (int) date('Y'));
        }

        return $tahun;
    }

    /**
     * Tagihan sebuah sewa yang benar-benar belum lunas.
     *
     * Status tagihan dipakai sebagai saringan awal, lalu sisa tagihan
     * dihitung dari pembayaran yang sudah masuk supaya cicilan ikut
     * diperhitungkan (dipakai saat check-out).
     *
     * @return array<int, array<string, mixed>>
     */
    public function belumLunasSewa(int $occupancyId): array
    {
        $rows = $this->db->table('invoices')
            ->where('occupancy_id', $occupancyId)
            ->whereNotIn('status', ['lunas'])
            ->orderBy('period_year', 'ASC')
            ->orderBy('period_month', 'ASC')
            ->get()
            ->getResultArray();

        if ($rows === []) {
            return [];
        }

        $peta = $this->totalPembayaran(array_column($rows, 'id'));

        foreach ($rows as &$row) {
            $row['dibayar'] = (float) ($peta[(int) $row['id']] ?? 0);
            $row['sisa']    = max(0, (float) $row['amount'] - $row['dibayar']);
        }
        unset($row);

        // Tagihan yang ternyata sudah tertutup pembayaran tidak dihitung.
        return array_values(array_filter($rows, static fn (array $r): bool => $r['sisa'] > 0));
    }

    /**
     * Total pembayaran per invoice_id.
     *
     * @param array<int, int|string> $invoiceIds
     *
     * @return array<int, float> indeks = invoice_id
     */
    public function totalPembayaran(array $invoiceIds): array
    {
        if ($invoiceIds === []) {
            return [];
        }

        $rows = $this->db->table('payments')
            ->select('invoice_id')
            ->selectSum('amount', 'total')
            ->whereIn('invoice_id', array_map('intval', $invoiceIds))
            ->groupBy('invoice_id')
            ->get()
            ->getResultArray();

        $peta = [];
        foreach ($rows as $row) {
            $peta[(int) $row['invoice_id']] = (float) $row['total'];
        }

        return $peta;
    }
}
