<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterAntrianAddOfficeFields extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('antrian')) {
            return;
        }

        $fields = [];

        // Change pos from INT to VARCHAR to support values like "CIS 2"
        // Note: modifying an existing column must use modifyColumn(), not addColumn().
        if ($this->db->fieldExists('pos', 'antrian')) {
            try {
                $this->forge->modifyColumn('antrian', [
                    'pos' => [
                        'name'       => 'pos',
                        'type'       => 'VARCHAR',
                        'constraint' => 20,
                        'default'    => '1',
                    ],
                ]);
            } catch (\Throwable) {
                // ignore if already modified / unsupported on current driver
            }
        }

        if (!$this->db->fieldExists('office_no_antrian', 'antrian')) {
            $fields['office_no_antrian'] = [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ];
        }

        if (!$this->db->fieldExists('office_id_hasil_uji', 'antrian')) {
            $fields['office_id_hasil_uji'] = [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ];
        }

        if (!$this->db->fieldExists('office_id_daftar', 'antrian')) {
            $fields['office_id_daftar'] = [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ];
        }

        if (!$this->db->fieldExists('office_id_kendaraan', 'antrian')) {
            $fields['office_id_kendaraan'] = [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ];
        }

        if (!$this->db->fieldExists('office_jdatang', 'antrian')) {
            $fields['office_jdatang'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if (!$this->db->fieldExists('office_jselesai', 'antrian')) {
            $fields['office_jselesai'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if (!$this->db->fieldExists('office_payload', 'antrian')) {
            $fields['office_payload'] = [
                'type' => 'LONGTEXT',
                'null' => true,
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('antrian', $fields);
        }

        // Add helpful indexes
        if ($this->db->fieldExists('office_id_daftar', 'antrian')) {
            try {
                $this->forge->addKey('office_id_daftar', false, true);
                $this->forge->processIndexes('antrian');
            } catch (\Throwable) {
                // ignore if already exists / unsupported
            }
        }

        if ($this->db->fieldExists('office_id_hasil_uji', 'antrian')) {
            try {
                $this->forge->addKey('office_id_hasil_uji');
                $this->forge->processIndexes('antrian');
            } catch (\Throwable) {
                // ignore
            }
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('antrian')) {
            return;
        }

        foreach ([
            'office_no_antrian',
            'office_id_hasil_uji',
            'office_id_daftar',
            'office_id_kendaraan',
            'office_jdatang',
            'office_jselesai',
            'office_payload',
        ] as $col) {
            if ($this->db->fieldExists($col, 'antrian')) {
                $this->forge->dropColumn('antrian', $col);
            }
        }
    }
}
