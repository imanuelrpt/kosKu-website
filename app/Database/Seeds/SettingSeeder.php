<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeder pengaturan aplikasi awal.
 *
 * Aman dijalankan berulang kali: baris yang sudah ada diperbarui nilainya,
 * bukan ditambahkan lagi (kolom setting_key bersifat unik).
 */
class SettingSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // key => value
        $settings = [
            'app_name'             => 'KosKu',
            'registration_enabled' => '1',      // pendaftaran mandiri penghuni
            'invoice_due_day'      => '10',     // jatuh tempo = tgl 10 bulan berikutnya

            // Identitas kos (dipakai pada halaman cetak tagihan & kwitansi).
            'kos_name'             => 'KosKu Residence',
            'kos_address'          => 'Jl. Melati No. 15, Bandung',
            'kos_phone'            => '081234567890',
            'kos_owner'            => 'Budi Hartono',
            'kos_bank'             => 'BCA 1234567890 a/n Budi Hartono',
        ];

        foreach ($settings as $key => $value) {
            $ada = $this->db->table('settings')->where('setting_key', $key)->countAllResults() > 0;

            if ($ada) {
                $this->db->table('settings')
                    ->where('setting_key', $key)
                    ->update(['setting_value' => $value, 'updated_at' => $now]);

                continue;
            }

            $this->db->table('settings')->insert([
                'setting_key'   => $key,
                'setting_value' => $value,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }
    }
}
