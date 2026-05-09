<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixAntrianTableNameAndSchema extends Migration
{
    public function up()
    {
        // 1) Fix wrong table name: `antrians` -> `antrian`
        if ($this->db->tableExists('antrians') && !$this->db->tableExists('antrian')) {
            try {
                $this->forge->renameTable('antrians', 'antrian');
            } catch (\Throwable) {
                // ignore
            }
        }

        // 2) Ensure table exists (fresh install safety)
        if (!$this->db->tableExists('antrian')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'auto_increment' => true,
                ],
                'nama' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                ],
                'no_uji' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                ],
                'nomor_kendaraan' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => true,
                ],
                'alamat' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'pos' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => '1',
                ],
                'status' => [
                    'type' => 'ENUM',
                    'constraint' => ['belum', 'dipanggil', 'selesai'],
                    'default' => 'belum',
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

        // 3) Ensure schema fields exist (because previous migrations might have been skipped)
        // pos type
        if ($this->db->fieldExists('pos', 'antrian')) {
            try {
                $this->forge->modifyColumn('antrian', [
                    'pos' => [
                        'name' => 'pos',
                        'type' => 'VARCHAR',
                        'constraint' => 20,
                        'default' => '1',
                    ],
                ]);
            } catch (\Throwable) {
                // ignore
            }
        }

        $addCols = [];

        foreach ([
            'office_no_antrian' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'office_id_hasil_uji' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'office_id_daftar' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'office_id_kendaraan' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'office_jdatang' => ['type' => 'DATETIME', 'null' => true],
            'office_jselesai' => ['type' => 'DATETIME', 'null' => true],
            'office_payload' => ['type' => 'LONGTEXT', 'null' => true],
            'office_tgl_uji' => ['type' => 'DATE', 'null' => true],
            'office_synced_at' => ['type' => 'DATETIME', 'null' => true],
        ] as $col => $def) {
            if (!$this->db->fieldExists($col, 'antrian')) {
                $addCols[$col] = $def;
            }
        }

        if ($addCols !== []) {
            $this->forge->addColumn('antrian', $addCols);
        }

        // Best-effort helpful indexes
        try {
            if ($this->db->fieldExists('office_id_daftar', 'antrian')) {
                $this->forge->addKey('office_id_daftar', false, true);
            }
            if ($this->db->fieldExists('office_id_hasil_uji', 'antrian')) {
                $this->forge->addKey('office_id_hasil_uji', false, true);
            }
            $this->forge->processIndexes('antrian');
        } catch (\Throwable) {
            // ignore
        }
    }

    public function down()
    {
        // No-op (avoid destructive changes in production rollback).
    }
}

