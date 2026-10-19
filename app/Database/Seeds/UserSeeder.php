<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeder akun default: 1 admin + akun login penghuni.
 * Password di-hash memakai password_hash() (PASSWORD_DEFAULT).
 */
class UserSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // [name, email, password, role, phone]
        $users = [
            ['Budi Hartono',    'admin@kosku.test',  'admin123',    'admin',    '0811-2000-100'],
            ['Siti Aminah',     'siti@kosku.test',   'penghuni123', 'penghuni', '0812-3456-7801'],
            ['Andi Wijaya',     'andi@kosku.test',   'penghuni123', 'penghuni', '0813-4567-8902'],
            ['Dewi Lestari',    'dewi@kosku.test',   'penghuni123', 'penghuni', '0812-9876-5433'],
            ['Rizky Pratama',   'rizky@kosku.test',  'penghuni123', 'penghuni', '0857-1122-3344'],
            ['Nurul Hidayah',   'nurul@kosku.test',  'penghuni123', 'penghuni', '0821-5566-7788'],
            ['Fajar Nugroho',   'fajar@kosku.test',  'penghuni123', 'penghuni', '0878-9900-1122'],
        ];

        foreach ($users as [$name, $email, $password, $role, $phone]) {
            $this->db->table('users')->insert([
                'name'       => $name,
                'email'      => $email,
                'password'   => password_hash($password, PASSWORD_DEFAULT),
                'role'       => $role,
                'phone'      => $phone,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
