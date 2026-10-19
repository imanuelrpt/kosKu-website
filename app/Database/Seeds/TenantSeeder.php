<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeder 8 penghuni. Enam di antaranya punya akun login (user_id),
 * satu mantan penghuni tanpa akun, dan satu penghuni tanpa akun
 * untuk memperlihatkan bahwa pencatatan manual tetap didukung.
 */
class TenantSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // [nama, email akun (null bila tanpa akun), phone, no KTP, alamat, kontak darurat]
        $tenants = [
            ['Siti Aminah',   'siti@kosku.test',  '0812-3456-7801', '3273010101900001', 'Jl. Merdeka No. 12, Bandung',   'Ibu Aminah - 0813-1111-2222'],
            ['Andi Wijaya',   'andi@kosku.test',  '0813-4567-8902', '3273010202910002', 'Jl. Sudirman No. 45, Bandung',  'Budi Wijaya - 0813-3333-4444'],
            ['Dewi Lestari',  'dewi@kosku.test',  '0812-9876-5433', '3273010303920003', 'Jl. Asia Afrika No. 8, Bandung', 'Rina - 0812-5555-6666'],
            ['Rizky Pratama', 'rizky@kosku.test', '0857-1122-3344', '3273010404930004', 'Jl. Dago No. 21, Bandung',      'Sari - 0857-7777-8888'],
            ['Nurul Hidayah', 'nurul@kosku.test', '0821-5566-7788', '3273010505940005', 'Jl. Riau No. 3, Bandung',       'Ahmad - 0821-9999-0000'],
            ['Fajar Nugroho', 'fajar@kosku.test', '0878-9900-1122', '3273010606950006', 'Jl. Setiabudi No. 77, Bandung', 'Lina - 0878-1212-1313'],
            ['Bagus Setiawan', null,              '0819-2233-4455', '3273010707960007', 'Jl. Pasteur No. 5, Bandung',    'Wati - 0819-1414-1515'],
            ['Maya Sari',      null,              '0817-6677-8899', '3273010808970008', 'Jl. Buah Batu No. 90, Bandung', 'Doni - 0817-1616-1717'],
        ];

        foreach ($tenants as [$name, $email, $phone, $idNumber, $address, $emergency]) {
            $userId = null;

            // Hubungkan ke akun login bila email tersedia.
            if ($email !== null) {
                $user = $this->db->table('users')->select('id')->where('email', $email)->get()->getRow();
                $userId = $user->id ?? null;
            }

            $this->db->table('tenants')->insert([
                'user_id'           => $userId,
                'name'              => $name,
                'phone'             => $phone,
                'id_number'         => $idNumber,
                'address'           => $address,
                'emergency_contact' => $emergency,
                'photo'             => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }
    }
}
