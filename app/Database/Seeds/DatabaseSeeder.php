<?php

namespace App\Database\Seeds;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Seeder;

/**
 * Seeder utama data demo KosKu.
 *
 * Jalankan dengan:  php spark db:seed DatabaseSeeder
 *
 * Catatan keamanan: pada environment production, seeder hanya berjalan
 * bila tabel users masih kosong (mencegah data produksi terhapus).
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Urutan seeding harus dijaga karena adanya foreign key.
     *
     * @var list<string>
     */
    private const URUTAN = [
        'SettingSeeder',
        'UserSeeder',
        'RoomSeeder',
        'TenantSeeder',
        'OccupancySeeder',
        'InvoiceSeeder',
        'PaymentSeeder',
        'ExpenseSeeder',
    ];

    /**
     * Tabel aplikasi (tanpa tabel migrations).
     *
     * @var list<string>
     */
    private const TABEL = ['payments', 'invoices', 'occupancies', 'expenses', 'tenants', 'rooms', 'users', 'settings'];

    public function run()
    {
        // Proteksi: jangan pernah mengosongkan data produksi.
        if (ENVIRONMENT === 'production') {
            $adaUser = $this->db->table('users')->countAllResults() > 0;

            if ($adaUser) {
                CLI::write('DatabaseSeeder dilewati: data users pada production sudah ada.', 'yellow');

                return;
            }
        }

        // Bersihkan tabel agar seeder aman dijalankan berulang kali.
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        foreach (self::TABEL as $tabel) {
            $this->db->table($tabel)->truncate();
        }
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');

        CLI::write('Tabel dibersihkan. Mulai mengisi data demo...', 'dark_gray');

        foreach (self::URUTAN as $seeder) {
            $this->call($seeder);
        }
    }
}
