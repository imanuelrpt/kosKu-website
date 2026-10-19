<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * TenantModel - data penghuni kos (master data orang).
 *
 * Terpisah dari users. Penghuni boleh dicatat manual oleh admin
 * walaupun belum punya akun login (user_id nullable).
 */
class TenantModel extends Model
{
    protected $table            = 'tenants';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 'name', 'phone', 'id_number', 'address',
        'emergency_contact', 'photo',
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validasi
    protected $validationRules = [
        'name'              => ['label' => 'Nama Penghuni',  'rules' => 'required|min_length[3]|max_length[100]'],
        'phone'             => ['label' => 'Nomor HP',        'rules' => 'permit_empty|max_length[30]'],
        'id_number'         => ['label' => 'Nomor KTP',       'rules' => 'permit_empty|max_length[30]'],
        'address'           => ['label' => 'Alamat',          'rules' => 'permit_empty|max_length[500]'],
        'emergency_contact' => ['label' => 'Kontak Darurat',  'rules' => 'permit_empty|max_length[100]'],
        'photo'             => ['label' => 'Foto',            'rules' => 'permit_empty|max_length[255]'],
    ];

    protected $validationMessages = [
        'name' => [
            'min_length' => 'Nama penghuni minimal {param} karakter.',
        ],
    ];

    protected $skipValidation = false;

    /**
     * Daftar penghuni terpaginasi dengan pencarian nama / HP / KTP.
     *
     * @return array<int, array<string, mixed>>
     */
    public function cari(?string $cari, int $perHalaman = 10): array
    {
        if ($cari !== null && $cari !== '') {
            $this->groupStart()
                ->like('name', $cari)
                ->orLike('phone', $cari)
                ->orLike('id_number', $cari)
                ->groupEnd();
        }

        return $this->orderBy('name', 'ASC')->paginate($perHalaman);
    }

    /**
     * Kamar yang sedang ditempati sekumpulan penghuni (1 query, tanpa N+1).
     *
     * @param array<int, int|string> $tenantIds
     *
     * @return array<int, array<string, mixed>> indeks = tenant_id
     */
    public function kamarAktif(array $tenantIds): array
    {
        if ($tenantIds === []) {
            return [];
        }

        $rows = $this->db->table('occupancies o')
            ->select('o.id AS occupancy_id, o.tenant_id, o.start_date, o.monthly_price')
            ->select('r.id AS room_id, r.room_number, r.floor')
            ->join('rooms r', 'r.id = o.room_id')
            ->where('o.status', 'aktif')
            ->whereIn('o.tenant_id', array_map('intval', $tenantIds))
            ->get()
            ->getResultArray();

        $peta = [];
        foreach ($rows as $row) {
            $peta[(int) $row['tenant_id']] = $row;
        }

        return $peta;
    }

    /**
     * Akun login berperan "penghuni" yang belum tertaut ke data penghuni.
     * Akun yang sedang dipakai record ini tetap ikut ditampilkan.
     *
     * @return array<int, array<string, mixed>>
     */
    public function akunTersedia(?int $idSaatIni = null): array
    {
        $terpakai = [];
        foreach ($this->db->table('tenants')->select('user_id')->where('deleted_at', null)->get()->getResultArray() as $row) {
            if ($row['user_id'] !== null) {
                $terpakai[] = (int) $row['user_id'];
            }
        }

        $builder = $this->db->table('users u')
            ->select('u.id, u.name, u.email')
            ->where('u.role', 'penghuni')
            ->where('u.deleted_at', null);

        // Buang akun yang sudah tertaut, kecuali akun milik record ini.
        if ($idSaatIni !== null) {
            $builder->groupStart();
            if ($terpakai !== []) {
                $builder->whereNotIn('u.id', $terpakai);
            } else {
                $builder->where('u.id !=', 0);
            }
            $builder->orWhere('u.id', $idSaatIni);
            $builder->groupEnd();
        } elseif ($terpakai !== []) {
            $builder->whereNotIn('u.id', $terpakai);
        }

        return $builder->orderBy('u.name', 'ASC')->get()->getResultArray();
    }

    /**
     * Apakah penghuni masih punya sewa aktif?
     */
    public function punyaSewaAktif(int $tenantId): bool
    {
        return $this->db->table('occupancies')
            ->where('tenant_id', $tenantId)
            ->where('status', 'aktif')
            ->countAllResults() > 0;
    }

    /**
     * Jumlah penghuni aktif (belum dihapus).
     */
    public function jumlahAktif(): int
    {
        return $this->db->table('tenants')->where('deleted_at', null)->countAllResults();
    }

    /**
     * Jumlah penghuni yang sedang menyewa kamar.
     */
    public function jumlahMenyewa(): int
    {
        return $this->db->table('occupancies o')
            ->join('tenants t', 't.id = o.tenant_id')
            ->where('o.status', 'aktif')
            ->where('t.deleted_at', null)
            ->countAllResults();
    }

    /**
     * Apakah akun login sudah tertaut ke penghuni lain?
     * (satu akun hanya boleh dipakai satu data penghuni)
     */
    public function akunSudahDipakai(int $userId, ?int $kecualiTenantId = null): bool
    {
        $builder = $this->db->table('tenants')
            ->where('user_id', $userId)
            ->where('deleted_at', null);

        if ($kecualiTenantId !== null) {
            $builder->where('id !=', $kecualiTenantId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Data penghuni lengkap dengan id (tanpa filter soft delete) untuk
     * keperluan pemeriksaan kepemilikan berkas oleh portal penghuni.
     */
    public function cariLewatAkun(int $userId): ?array
    {
        return $this->where('user_id', $userId)->first();
    }
}
