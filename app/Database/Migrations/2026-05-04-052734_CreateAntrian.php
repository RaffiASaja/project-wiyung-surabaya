<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAntrian extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],

            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],

            'no_uji' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],

            'nomor_kendaraan' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],

            'alamat' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'pos' => [
                'type'       => 'INT',
                'constraint' => 1,
                'default'    => 1,
            ],

            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['belum', 'dipanggil', 'selesai'],
                'default'    => 'belum',
            ],

            'waktu_panggil' => [
                'type' => 'DATETIME',
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
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('antrian');
    }

    public function down()
    {
        $this->forge->dropTable('antrian');
    }
}