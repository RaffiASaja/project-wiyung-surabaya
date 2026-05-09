<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterAntrianAddTimestamps extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('antrian')) {
            return;
        }

        $fields = [];

        if (!$this->db->fieldExists('created_at', 'antrian')) {
            $fields['created_at'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if (!$this->db->fieldExists('updated_at', 'antrian')) {
            $fields['updated_at'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('antrian', $fields);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('antrian')) {
            return;
        }

        if ($this->db->fieldExists('updated_at', 'antrian')) {
            $this->forge->dropColumn('antrian', 'updated_at');
        }

        if ($this->db->fieldExists('created_at', 'antrian')) {
            $this->forge->dropColumn('antrian', 'created_at');
        }
    }
}

