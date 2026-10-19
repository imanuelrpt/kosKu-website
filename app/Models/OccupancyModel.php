<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * OccupancyModel - riwayat sewa (check-in / check-out).
 *
 * Satu baris = satu kontrak sewa seorang penghuni pada satu kamar.
 * Tabel ini sengaja tanpa soft delete karena merupakan riwayat.
 *
 * Catatan: paginate() memakai builder milik model ini, jadi kolom pada
 * select()/join() ditulis dengan nama tabel lengkap (tanpa alias).
 */
class OccupancyModel extends Model
{
    protected $table            = 'occupancies';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'tenant_id', 'room_id', 'start_date', 'end_date',
        'monthly_price', 'deposit', 'status',
    ];

    // Timestamps (tanpa created_by)
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validasi
    protected $validationRules = [
        'tenant_id'     => ['label' => 'Penghuni',  'rules' => 'required|is_natural_no_zero|is_not_unique[tenants.id]'],
        'room_id'       => ['label' => 'Kamar',     'rules' => 'required|is_natural_no_zero|is_not_unique[rooms.id]'],
        'start_date'    => ['label' => 'Tanggal Masuk', 'rules' => 'required|valid_date'],
        'end_date'      => ['label' => 'Tanggal Keluar', 'rules' => 'permit_empty|valid_date'],
        'monthly_price' => ['label' => 'Harga Sewa', 'rules' => 'required|numeric|greater_than_equal_to[0]'],
        'deposit'       => ['label' => 'Deposit',    'rules' => 'permit_empty|numeric|greater_than_equal_to[0]'],
        'status'        => ['label' => 'Status',     'rules' => 'required|in_list[aktif,selesai]'],
    ];

    protected $validationMessages = [
        'tenant_id' => [
            'is_not_unique' => 'Data penghuni tidak ditemukan.',
        ],
        'room_id' => [
            'is_not_unique' => 'Data kamar tidak ditemukan.',
        ],
        'start_date' => [
            'required' => 'Tanggal masuk wajib diisi.',
        ],
        'monthly_price' => [
            'required' => 'Harga sewa wajib diisi.',
        ],
    ];

    protected $skipValidation = false;

    /** Status sewa yang valid. */
    public const STATUS = ['aktif', 'selesai'];

    /**
     * Daftar sewa terpaginasi dengan pencarian & filter status.
     * Sewa yang masih aktif ditampilkan paling atas.
     *
     * @return array<int, array<string, mixed>>
     */
    public function cariDanFilter(?string $cari, ?string $status, int $perHalaman = 10): array
    {
        $this->select('occupancies.*, tenants.name AS tenant_name')
            ->select('tenants.phone AS tenant_phone, tenants.deleted_at AS tenant_deleted_at')
            ->select('rooms.room_number, rooms.floor, rooms.price AS harga_kamar')
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
            $this->where('occupancies.status', $status);
        }

        return $this->orderBy('occupancies.status', 'ASC')   // 'aktif' di atas 'selesai'
            ->orderBy('occupancies.start_date', 'DESC')
            ->orderBy('occupancies.id', 'DESC')
            ->paginate($perHalaman);
    }

    /**
     * Ringkasan untuk kartu statistik di halaman daftar sewa.
     *
     * @return array<string, int|float>
     */
    public function ringkasan(): array
    {
        $aktif   = (int) $this->db->table('occupancies')->where('status', 'aktif')->countAllResults();
        $selesai = (int) $this->db->table('occupancies')->where('status', 'selesai')->countAllResults();
        $kosong  = (int) $this->db->table('rooms')
            ->where('deleted_at', null)
            ->where('status', 'kosong')
            ->countAllResults();

        $total = $this->db->table('occupancies')
            ->selectSum('monthly_price', 'total')
            ->where('status', 'aktif')
            ->get()
            ->getRowArray();

        return [
            'aktif'        => $aktif,
            'selesai'      => $selesai,
            'kamar_kosong' => $kosong,
            'sewa_bulanan' => (float) ($total['total'] ?? 0),
        ];
    }

    /**
     * ID kamar yang sedang punya sewa aktif.
     *
     * @return array<int, int>
     */
    public function idKamarTersewa(): array
    {
        return array_map('intval', array_column(
            $this->db->table('occupancies')->select('room_id')->where('status', 'aktif')->get()->getResultArray(),
            'room_id'
        ));
    }

    /**
     * Kamar yang siap ditempati: statusnya kosong dan tidak sedang disewa.
     *
     * @return array<int, array<string, mixed>>
     */
    public function kamarSiapSewa(): array
    {
        $kamar = $this->db->table('rooms')
            ->select('id, room_number, floor, price, facilities')
            ->where('deleted_at', null)
            ->where('status', 'kosong')
            ->orderBy('floor', 'ASC')
            ->orderBy('room_number', 'ASC')
            ->get()
            ->getResultArray();

        $terpakai = $this->idKamarTersewa();

        return array_values(array_filter(
            $kamar,
            static fn (array $k): bool => ! in_array((int) $k['id'], $terpakai, true)
        ));
    }

    /**
     * Penghuni yang belum punya sewa aktif (siap di-check-in).
     *
     * @return array<int, array<string, mixed>>
     */
    public function penghuniSiapSewa(): array
    {
        $menyewa = array_map('intval', array_column(
            $this->db->table('occupancies')->select('tenant_id')->where('status', 'aktif')->get()->getResultArray(),
            'tenant_id'
        ));

        $builder = $this->db->table('tenants')
            ->select('id, name, phone')
            ->where('deleted_at', null);

        if ($menyewa !== []) {
            $builder->whereNotIn('id', $menyewa);
        }

        return $builder->orderBy('name', 'ASC')->get()->getResultArray();
    }

    /**
     * Daftar sewa aktif beserta penghuni & kamar (untuk dropdown
     * pembuatan tagihan manual).
     *
     * @return array<int, array<string, mixed>>
     */
    public function daftarAktif(): array
    {
        return $this->db->table('occupancies o')
            ->select('o.id, o.monthly_price, o.start_date')
            ->select('t.name AS tenant_name, r.room_number, r.floor')
            ->join('tenants t', 't.id = o.tenant_id')
            ->join('rooms r', 'r.id = o.room_id')
            ->where('o.status', 'aktif')
            ->orderBy('r.room_number', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Detail satu sewa lengkap dengan data penghuni & kamar.
     *
     * @return array<string, mixed>|null
     */
    public function detail(int $id): ?array
    {
        $row = $this->db->table('occupancies o')
            ->select('o.*, t.name AS tenant_name, t.phone AS tenant_phone, t.photo AS tenant_photo')
            ->select('r.room_number, r.floor, r.price AS harga_kamar')
            ->join('tenants t', 't.id = o.tenant_id')
            ->join('rooms r', 'r.id = o.room_id')
            ->where('o.id', $id)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * Riwayat sewa seorang penghuni (terbaru di atas).
     *
     * @return array<int, array<string, mixed>>
     */
    public function riwayatTenant(int $tenantId): array
    {
        return $this->db->table('occupancies o')
            ->select('o.*, r.room_number, r.floor, r.price AS harga_kamar, r.facilities')
            ->join('rooms r', 'r.id = o.room_id')
            ->where('o.tenant_id', $tenantId)
            ->orderBy('o.start_date', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Sewa aktif seorang penghuni (maksimal satu).
     *
     * @return array<string, mixed>|null
     */
    public function sewaAktifTenant(int $tenantId): ?array
    {
        $row = $this->db->table('occupancies o')
            ->select('o.*, r.room_number, r.floor, r.price AS harga_kamar')
            ->select('r.facilities, r.status AS room_status')
            ->join('rooms r', 'r.id = o.room_id')
            ->where('o.tenant_id', $tenantId)
            ->where('o.status', 'aktif')
            ->orderBy('o.start_date', 'DESC')
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * Sewa aktif pada sebuah kamar (maksimal satu).
     *
     * @return array<string, mixed>|null
     */
    public function sewaAktifKamar(int $roomId): ?array
    {
        $row = $this->db->table('occupancies o')
            ->select('o.*, t.name AS tenant_name')
            ->join('tenants t', 't.id = o.tenant_id')
            ->where('o.room_id', $roomId)
            ->where('o.status', 'aktif')
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * Apakah sewa ini sudah punya tagihan? (dipakai sebelum membatalkan check-in)
     */
    public function punyaTagihan(int $occupancyId): bool
    {
        return $this->db->table('invoices')->where('occupancy_id', $occupancyId)->countAllResults() > 0;
    }

    /**
     * Tutup masa sewa (check-out).
     *
     * Ditulis langsung lewat Query Builder agar tidak terkena validasi
     * model yang mengharuskan seluruh kolom terisi.
     */
    public function tutup(int $id, string $endDate): bool
    {
        return $this->db->table('occupancies')->where('id', $id)->update([
            'end_date'   => $endDate,
            'status'     => 'selesai',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
