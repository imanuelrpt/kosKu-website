<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use DateTimeImmutable;

/**
 * Seeder pembayaran:
 * - setiap tagihan "lunas" mendapat 1 pembayaran penuh (tunai/transfer bergantian);
 * - salah satu tagihan "terlambat" diberi pembayaran sebagian (cicilan)
 *   sebagai contoh bahwa status belum lunas sampai total pembayaran >= nominal.
 */
class PaymentSeeder extends Seeder
{
    public function run()
    {
        $admin = $this->db->table('users')->select('id')->where('role', 'admin')->orderBy('id', 'ASC')->get()->getRow();
        $adminId = $admin->id ?? null;

        // 1) Pembayaran penuh untuk tagihan yang sudah lunas.
        $lunas = $this->db->table('invoices')
            ->select('id, amount, due_date')
            ->where('status', 'lunas')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($lunas as $n => $invoice) {
            $dueDate = new DateTimeImmutable($invoice['due_date']);
            // Dibayar 3-7 hari sebelum jatuh tempo.
            $paidAt   = $dueDate->modify('-' . (3 + $n % 5) . ' days')->format('Y-m-d');
            $transfer = $n % 2 === 1;

            $this->db->table('payments')->insert([
                'invoice_id' => $invoice['id'],
                'amount'     => (float) $invoice['amount'],
                'paid_at'    => $paidAt,
                'method'     => $transfer ? 'transfer' : 'tunai',
                'proof_file' => null,
                'note'       => $transfer ? 'Transfer bank' : 'Bayar tunai di rumah',
                'created_by' => $adminId,
                'created_at' => $paidAt . ' 10:00:00',
                'updated_at' => $paidAt . ' 10:00:00',
            ]);
        }

        // 2) Contoh pembayaran sebagian (cicilan) pada tagihan terlambat kedua.
        $terlambat = $this->db->table('invoices')
            ->select('id, amount, due_date')
            ->where('status', 'terlambat')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        if (isset($terlambat[1])) {
            $invoice = $terlambat[1];
            $dueDate = new DateTimeImmutable($invoice['due_date']);

            // Cicilan ~55% dari nominal, dibulatkan ke bawah per 10 ribu.
            $cicilan = floor(((float) $invoice['amount'] * 0.55) / 10000) * 10000;

            // Dibayar terlambat, paling lambat hari ini.
            $paidAt = min($dueDate->modify('+9 days')->format('Y-m-d'), date('Y-m-d'));

            $this->db->table('payments')->insert([
                'invoice_id' => $invoice['id'],
                'amount'     => $cicilan,
                'paid_at'    => $paidAt,
                'method'     => 'transfer',
                'proof_file' => null,
                'note'       => 'Cicilan pertama (transfer)',
                'created_by' => $adminId,
                'created_at' => $paidAt . ' 14:30:00',
                'updated_at' => $paidAt . ' 14:30:00',
            ]);
        }
    }
}
