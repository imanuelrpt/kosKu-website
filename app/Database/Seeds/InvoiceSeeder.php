<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use DateTimeImmutable;

/**
 * Seeder tagihan bulanan untuk 4 periode terakhir (termasuk bulan berjalan)
 * dengan status campuran: lunas, belum_bayar, dan terlambat.
 *
 * Aturan jatuh tempo: tanggal 10 bulan berikutnya setelah periode tagihan,
 * sehingga tagihan bulan berjalan berstatus "belum_bayar" sampai jatuh tempo.
 */
class InvoiceSeeder extends Seeder
{
    /** Jumlah periode demo, termasuk bulan berjalan. */
    private const JUMLAH_PERIODE = 4;

    public function run()
    {
        $bulanIni = new DateTimeImmutable(date('Y-m-01'));
        $hariIni  = date('Y-m-d');

        $occupancies = $this->db->table('occupancies')
            ->select('id, monthly_price, start_date, end_date')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($occupancies as $i => $occ) {
            $mulaiSewa = substr($occ['start_date'], 0, 7);                        // YYYY-MM
            $akhirSewa = $occ['end_date'] ? substr($occ['end_date'], 0, 7) : null;

            for ($mundur = self::JUMLAH_PERIODE - 1; $mundur >= 0; $mundur--) {
                $bulan   = $bulanIni->modify("-{$mundur} month");
                $periode = $bulan->format('Y-m');

                // Lewati periode di luar masa sewa.
                if ($periode < $mulaiSewa) {
                    continue;
                }
                if ($akhirSewa !== null && $periode > $akhirSewa) {
                    continue;
                }

                $dueDate = $bulan->modify('+1 month')->format('Y-m-10');

                // Pola pembayaran supaya data demo beragam namun tetap konsisten.
                $dibayar = match ($mundur) {
                    1       => $i % 3 !== 0, // tagihan bulan lalu: sebagian menunggak
                    0       => $i % 2 !== 0, // tagihan bulan ini: separuh belum dibayar
                    default => true,          // tagihan lama sudah lunas
                };

                $status = $dibayar
                    ? 'lunas'
                    : ($dueDate < $hariIni ? 'terlambat' : 'belum_bayar');

                $this->db->table('invoices')->insert([
                    'occupancy_id' => $occ['id'],
                    'period_month' => (int) $bulan->format('n'),
                    'period_year'  => (int) $bulan->format('Y'),
                    'amount'       => (float) $occ['monthly_price'],
                    'due_date'     => $dueDate,
                    'status'       => $status,
                    'created_at'   => $bulan->format('Y-m-01 08:00:00'),
                    'updated_at'   => $bulan->format('Y-m-01 08:00:00'),
                ]);
            }
        }
    }
}
