<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use DateTimeImmutable;

/**
 * Seeder riwayat sewa (check-in/check-out):
 * 6 sewa aktif + 2 sewa yang sudah selesai.
 * Kamar yang sedang disewa otomatis berstatus "terisi".
 */
class OccupancySeeder extends Seeder
{
    public function run()
    {
        $now     = date('Y-m-d H:i:s');
        $bulanIni = new DateTimeImmutable(date('Y-m-01'));

        // [nama penghuni, nomor kamar, mulai (bulan lalu), selesai (bulan lalu)|null, deposit (bulan)]
        $sewa = [
            ['Siti Aminah',   'A-01', 8,  null, 1],
            ['Andi Wijaya',   'A-02', 6,  null, 1],
            ['Dewi Lestari',  'A-03', 5,  null, 1],
            ['Rizky Pratama', 'B-01', 3,  null, 1],
            ['Nurul Hidayah', 'B-02', 2,  null, 1],
            ['Fajar Nugroho', 'B-03', 1,  null, 1],
            ['Bagus Setiawan', 'C-01', 10, 2,    1],
            ['Maya Sari',     'C-02', 12, 3,    1],
        ];

        foreach ($sewa as [$nama, $nomorKamar, $mulaiMundur, $selesaiMundur, $depositBulan]) {
            $tenant = $this->db->table('tenants')->select('id')->where('name', $nama)->get()->getRow();
            $room   = $this->db->table('rooms')->select('id, price')->where('room_number', $nomorKamar)->get()->getRow();

            if ($tenant === null || $room === null) {
                continue;
            }

            $harga = (float) $room->price;
            $aktif = $selesaiMundur === null;

            $this->db->table('occupancies')->insert([
                'tenant_id'     => $tenant->id,
                'room_id'       => $room->id,
                'start_date'    => $bulanIni->modify("-{$mulaiMundur} month")->format('Y-m-05'),
                'end_date'      => $aktif ? null : $bulanIni->modify("-{$selesaiMundur} month")->format('Y-m-28'),
                'monthly_price' => $harga,
                'deposit'       => $harga * $depositBulan,
                'status'        => $aktif ? 'aktif' : 'selesai',
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);

            // Kamar yang sedang disewa menjadi "terisi".
            if ($aktif) {
                $this->db->table('rooms')
                    ->where('id', $room->id)
                    ->update(['status' => 'terisi', 'updated_at' => $now]);
            }
        }
    }
}
