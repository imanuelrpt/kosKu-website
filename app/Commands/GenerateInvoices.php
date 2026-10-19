<?php

namespace App\Commands;

use App\Models\InvoiceModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Perintah pembuatan tagihan bulanan.
 *
 * Contoh pemakaian:
 *   php spark invoices:generate            (periode bulan ini)
 *   php spark invoices:generate 11 2026    (periode November 2026)
 *   php spark invoices:generate --late     (hanya tandai tagihan terlambat)
 *
 * Aman dijalankan berulang kali: tagihan yang sudah ada tidak dibuat ganda.
 */
class GenerateInvoices extends BaseCommand
{
    protected $group       = 'KosKu';
    protected $name        = 'invoices:generate';
    protected $description = 'Membuat tagihan bulanan untuk semua sewa aktif.';
    protected $usage       = 'invoices:generate [bulan] [tahun] [--late]';
    protected $arguments   = [
        'bulan' => 'Bulan periode tagihan (1-12). Default: bulan berjalan.',
        'tahun' => 'Tahun periode tagihan (mis. 2026). Default: tahun berjalan.',
    ];
    protected $options = [
        '--late' => 'Hanya memperbarui status tagihan yang lewat jatuh tempo.',
    ];

    public function run(array $params)
    {
        helper(['kost', 'setting']);

        $model = new InvoiceModel();

        $bulan = (int) ($params[0] ?? date('n'));
        $tahun = (int) ($params[1] ?? date('Y'));

        if ($bulan < 1 || $bulan > 12) {
            CLI::error('Bulan harus antara 1 sampai 12.');

            return EXIT_ERROR;
        }

        if ($tahun < 2000 || $tahun > 2100) {
            CLI::error('Tahun tidak wajar (2000-2100).');

            return EXIT_ERROR;
        }

        // Mode khusus: hanya menandai tagihan yang terlambat.
        if (CLI::getOption('late') !== null) {
            $jumlah = $model->tandaiTerlambat();

            CLI::write('Tagihan yang ditandai terlambat: ' . CLI::color((string) $jumlah, 'yellow'));

            return EXIT_SUCCESS;
        }

        CLI::write('KosKu - Pembuatan Tagihan Bulanan', 'white');
        CLI::write('Periode: ' . CLI::color(trim(bulan_indo($bulan) . ' ' . $tahun), 'green'));

        $hasil = $model->generateForPeriod($bulan, $tahun);

        if ($hasil['rincian'] !== []) {
            CLI::newLine();
            foreach ($hasil['rincian'] as $baris) {
                CLI::write('  + ' . $baris, 'light_gray');
            }
        }

        CLI::newLine();
        CLI::write('Jatuh tempo       : ' . CLI::color(tanggal_indo($hasil['jatuh_tempo']), 'cyan'));
        CLI::write('Tagihan dibuat    : ' . CLI::color((string) $hasil['dibuat'], 'green'));
        CLI::write('Dilewati (sudah ada): ' . CLI::color((string) $hasil['dilewati'], 'yellow'));

        $terlambat = $model->tandaiTerlambat();
        CLI::write('Ditandai terlambat: ' . CLI::color((string) $terlambat, 'red'));

        CLI::newLine();
        CLI::write('Selesai.', 'green');

        return EXIT_SUCCESS;
    }
}
