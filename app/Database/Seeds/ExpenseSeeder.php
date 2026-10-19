<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use DateTimeImmutable;

/**
 * Seeder pengeluaran operasional 4 bulan terakhir
 * (listrik, air, internet, dan satu biaya perbaikan).
 */
class ExpenseSeeder extends Seeder
{
    public function run()
    {
        $bulanIni = new DateTimeImmutable(date('Y-m-01'));
        $now      = date('Y-m-d H:i:s');

        for ($mundur = 3; $mundur >= 0; $mundur--) {
            $bulan = $bulanIni->modify("-{$mundur} month");

            $items = [
                ['listrik', 420000 + ($mundur * 15000), 5,  'Pembelian token listrik bersama'],
                ['air',     300000,                     7,  'Tagihan air PDAM'],
                ['lainnya', 350000,                     8,  'Internet / WiFi bulanan'],
            ];

            // Biaya perbaikan hanya muncul pada bulan tertentu.
            if ($mundur === 2) {
                $items[] = ['perbaikan', 450000, 15, 'Perbaikan kran & pijakan kamar mandi A-04'];
            }
            if ($mundur === 0) {
                $items[] = ['perbaikan', 175000, 12, 'Servis pompa air lantai 2'];
            }

            foreach ($items as [$category, $amount, $day, $note]) {
                $tanggal = $bulan->modify("+{$day} days")->format('Y-m-d');

                $this->db->table('expenses')->insert([
                    'category'   => $category,
                    'amount'     => $amount,
                    'date'       => $tanggal,
                    'note'       => $note,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
