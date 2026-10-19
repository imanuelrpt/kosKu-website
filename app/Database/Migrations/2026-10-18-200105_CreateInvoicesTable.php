<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel invoices - tagihan bulanan per sewa.
 * UNIQUE (occupancy_id, period_month, period_year) mencegah tagihan ganda.
 */
class CreateInvoicesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'occupancy_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'period_month' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
                'comment'    => '1 - 12',
            ],
            'period_year' => [
                'type'       => 'SMALLINT',
                'constraint' => 4,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
            'due_date' => [
                'type' => 'DATE',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['belum_bayar', 'lunas', 'terlambat'],
                'default'    => 'belum_bayar',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['occupancy_id', 'period_month', 'period_year'], false, true); // anti tagihan ganda
        $this->forge->addKey('status');
        $this->forge->addKey('due_date');
        // Tagihan adalah riwayat - tidak boleh terhapus.
        $this->forge->addForeignKey('occupancy_id', 'occupancies', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('invoices');
    }

    public function down()
    {
        $this->forge->dropTable('invoices');
    }
}
