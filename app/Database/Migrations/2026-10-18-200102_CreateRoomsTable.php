<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel rooms - data kamar kos.
 * Soft delete dipakai supaya nomor kamar yang punya riwayat sewa tidak hilang.
 */
class CreateRoomsTable extends Migration
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
            'room_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'price' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
                'comment'    => 'Harga sewa per bulan',
            ],
            'floor' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'default'    => 1,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['kosong', 'terisi', 'perbaikan'],
                'default'    => 'kosong',
            ],
            'facilities' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Fasilitas, boleh dipisah koma',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('room_number', false, true); // nomor kamar unik
        $this->forge->addKey('status');
        $this->forge->createTable('rooms');
    }

    public function down()
    {
        $this->forge->dropTable('rooms');
    }
}
