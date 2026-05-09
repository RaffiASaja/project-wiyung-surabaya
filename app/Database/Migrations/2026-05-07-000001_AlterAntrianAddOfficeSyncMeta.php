<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterAntrianAddOfficeSyncMeta extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('antrian')) {
            return;
        }

        $fields = [];

        if (!$this->db->fieldExists('office_tgl_uji', 'antrian')) {
            $fields['office_tgl_uji'] = [
                'type' => 'DATE',
                'null' => true,
            ];
        }

        if (!$this->db->fieldExists('office_synced_at', 'antrian')) {
            $fields['office_synced_at'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('antrian', $fields);
        }

        // Helpful indexes/constraints (best-effort, DB engine may vary)
        try {
            if ($this->db->fieldExists('office_id_daftar', 'antrian')) {
                $this->forge->addKey('office_id_daftar', false, true);
            }
            if ($this->db->fieldExists('office_id_hasil_uji', 'antrian')) {
                $this->forge->addKey('office_id_hasil_uji', false, true);
            }
            if ($this->db->fieldExists('office_tgl_uji', 'antrian')) {
                $this->forge->addKey('office_tgl_uji');
            }
            if ($this->db->fieldExists('office_synced_at', 'antrian')) {
                $this->forge->addKey('office_synced_at');
            }

            $this->forge->processIndexes('antrian');
        } catch (\Throwable) {
            // ignore if already exists / unsupported
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('antrian')) {
            return;
        }

        foreach (['office_synced_at', 'office_tgl_uji'] as $col) {
            if ($this->db->fieldExists($col, 'antrian')) {
                $this->forge->dropColumn('antrian', $col);
            }
        }
    }
}

