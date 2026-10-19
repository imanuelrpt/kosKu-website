<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * RoomModel - data kamar kos.
 *
 * Memakai soft delete supaya kamar yang punya riwayat sewa tidak hilang.
 */
class RoomModel extends Model
{
    protected $table            = 'rooms';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['room_number', 'price', 'floor', 'status', 'facilities', 'notes'];

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validasi
    protected $validationRules = [
        // Saat update, controller menambahkan bagian ",id,<ID>" lewat
        // setValidationRule() supaya baris yang sedang diubah tidak dianggap
        // duplikat. Untuk perubahan status otomatis gunakan setStatus()
        // yang menulis langsung tanpa validasi.
        'room_number' => ['label' => 'Nomor Kamar', 'rules' => 'required|max_length[20]|is_unique[rooms.room_number]'],
        'price'       => ['label' => 'Harga Sewa',  'rules' => 'required|numeric|greater_than_equal_to[0]'],
        'floor'       => ['label' => 'Lantai',      'rules' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[50]'],
        'status'      => ['label' => 'Status',      'rules' => 'required|in_list[kosong,terisi,perbaikan]'],
        'facilities'  => ['label' => 'Fasilitas',   'rules' => 'permit_empty|max_length[1000]'],
        'notes'       => ['label' => 'Catatan',     'rules' => 'permit_empty|max_length[1000]'],
    ];

    protected $validationMessages = [
        'room_number' => [
            'is_unique' => 'Nomor kamar {value} sudah dipakai kamar lain.',
        ],
        'price' => [
            'numeric'                 => 'Harga sewa harus berupa angka.',
            'greater_than_equal_to'   => 'Harga sewa tidak boleh negatif.',
        ],
    ];

    protected $skipValidation = false;

    /** Status kamar yang valid. */
    public const STATUS = ['kosong', 'terisi', 'perbaikan'];

    /**
     * Daftar kamar terpaginasi dengan pencarian & filter status.
     *
     * @return array<int, array<string, mixed>>
     */
    public function cariDanFilter(?string $cari, ?string $status, int $perHalaman = 10): array
    {
        if ($cari !== null && $cari !== '') {
            $this->groupStart()
                ->like('room_number', $cari)
                ->orLike('facilities', $cari)
                ->orLike('notes', $cari)
                ->groupEnd();
        }

        if (in_array($status, self::STATUS, true)) {
            $this->where('status', $status);
        }

        return $this->orderBy('floor', 'ASC')
            ->orderBy('room_number', 'ASC')
            ->paginate($perHalaman);
    }

    /**
     * Penghuni yang sedang menempati sekumpulan kamar.
     * Satu query untuk semua kamar di halaman aktif (menghindari N+1).
     *
     * @param array<int, int|string> $roomIds
     *
     * @return array<int, array<string, mixed>> indeks = room_id
     */
    public function penghuniAktif(array $roomIds): array
    {
        if ($roomIds === []) {
            return [];
        }

        $rows = $this->db->table('occupancies o')
            ->select('o.id AS occupancy_id, o.room_id, o.start_date, o.monthly_price')
            ->select('t.id AS tenant_id, t.name AS tenant_name, t.phone AS tenant_phone, t.photo AS tenant_photo')
            ->join('tenants t', 't.id = o.tenant_id')
            ->where('o.status', 'aktif')
            ->where('t.deleted_at', null)
            ->whereIn('o.room_id', array_map('intval', $roomIds))
            ->get()
            ->getResultArray();

        $peta = [];
        foreach ($rows as $row) {
            $peta[(int) $row['room_id']] = $row;
        }

        return $peta;
    }

    /**
     * Ringkasan jumlah kamar per status (untuk kartu kecil di atas tabel).
     *
     * @return array<string, int>
     */
    public function ringkasan(): array
    {
        $hasil = ['kosong' => 0, 'terisi' => 0, 'perbaikan' => 0, 'total' => 0];

        $rows = $this->db->table('rooms')
            ->select('status')
            ->selectCount('id', 'jumlah')
            ->where('deleted_at', null)
            ->groupBy('status')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $hasil[$row['status']] = (int) $row['jumlah'];
            $hasil['total']       += (int) $row['jumlah'];
        }

        return $hasil;
    }

    /**
     * Apakah kamar masih punya sewa aktif?
     */
    public function punyaSewaAktif(int $roomId): bool
    {
        return $this->db->table('occupancies')
            ->where('room_id', $roomId)
            ->where('status', 'aktif')
            ->countAllResults() > 0;
    }

    /**
     * Apakah kamar punya riwayat sewa (aktif maupun selesai)?
     */
    public function punyaRiwayatSewa(int $roomId): bool
    {
        return $this->db->table('occupancies')
            ->where('room_id', $roomId)
            ->countAllResults() > 0;
    }

    /**
     * Ubah status kamar tanpa validasi.
     *
     * Dipakai untuk perubahan status otomatis (check-in / check-out) karena
     * menyimpan sebagian kolom saja akan gagal divalidasi oleh aturan
     * is_unique pada nomor kamar.
     */
    public function setStatus(int $roomId, string $status): bool
    {
        if (! in_array($status, self::STATUS, true)) {
            return false;
        }

        return $this->db->table('rooms')
            ->where('id', $roomId)
            ->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Samakan status kamar dengan keadaan sewa saat ini.
     *
     * Status "perbaikan" tidak pernah ditimpa otomatis karena itu
     * keputusan manual admin.
     */
    public function sinkronStatus(int $roomId): void
    {
        $kamar = $this->find($roomId);

        if ($kamar === null || $kamar['status'] === 'perbaikan') {
            return;
        }

        $statusBaru = $this->punyaSewaAktif($roomId) ? 'terisi' : 'kosong';

        if ($kamar['status'] !== $statusBaru) {
            $this->setStatus($roomId, $statusBaru);
        }
    }
}
