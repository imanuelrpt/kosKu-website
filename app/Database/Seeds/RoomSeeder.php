<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeder 10 kamar kos (3 lantai, variasi harga & fasilitas).
 * Status awal "kosong"; kamar yang terisi akan diubah oleh OccupancySeeder.
 */
class RoomSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // [room_number, price, floor, status, facilities, notes]
        $rooms = [
            ['A-01', 800000,  1, 'kosong',    'AC, Kamar Mandi Dalam, WiFi, Kasur',        'Dekat tangga'],
            ['A-02', 800000,  1, 'kosong',    'AC, Kamar Mandi Dalam, WiFi, Kasur',        null],
            ['A-03', 750000,  1, 'kosong',    'Kipas Angin, Kamar Mandi Dalam, Kasur',     null],
            ['A-04', 750000,  1, 'perbaikan', 'Kipas Angin, Kamar Mandi Dalam, Kasur',     'Perbaikan pijakan kamar mandi'],
            ['B-01', 900000,  2, 'kosong',    'AC, Kamar Mandi Dalam, WiFi, Lemari, Kasur', null],
            ['B-02', 900000,  2, 'kosong',    'AC, Kamar Mandi Dalam, WiFi, Lemari, Kasur', null],
            ['B-03', 850000,  2, 'kosong',    'AC, Kamar Mandi Dalam, WiFi, Kasur',        null],
            ['C-01', 1100000, 3, 'kosong',    'AC, Kamar Mandi Dalam, WiFi, TV, Balkon',   'Kamar paling luas'],
            ['C-02', 1100000, 3, 'kosong',    'AC, Kamar Mandi Dalam, WiFi, TV, Balkon',   null],
            ['C-03', 1000000, 3, 'kosong',    'AC, Kamar Mandi Dalam, WiFi, TV',           null],
        ];

        foreach ($rooms as [$number, $price, $floor, $status, $facilities, $notes]) {
            $this->db->table('rooms')->insert([
                'room_number' => $number,
                'price'       => $price,
                'floor'       => $floor,
                'status'      => $status,
                'facilities'  => $facilities,
                'notes'       => $notes,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }
}
