<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel occupancies - riwayat sewa (check-in / check-out).
 * Satu baris = satu kontrak sewa seorang penghuni pada satu kamar.
 */
class CreateOccupanciesTable extends Migration
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
            'tenant_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'room_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'start_date' => [
                'type' => 'DATE',
            ],
            'end_date' => [
                'type'    => 'DATE',
                'null'    => true,
                'comment' => 'Null selama sewa masih aktif',
            ],
            'monthly_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
                'comment'    => 'Harga kesepakatan, bisa beda dari harga kamar',
            ],
            'deposit' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['aktif', 'selesai'],
                'default'    => 'aktif',
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
        $this->forge->addKey('tenant_id');
        $this->forge->addKey('room_id');
        $this->forge->addKey('status');
        // Riwayat sewa tidak boleh terhapus saat penghuni/kamar dihapus (RESTRICT).
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('room_id', 'rooms', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('occupancies');
    }

    public function down()
    {
        $this->forge->dropTable('occupancies');
    }
}
