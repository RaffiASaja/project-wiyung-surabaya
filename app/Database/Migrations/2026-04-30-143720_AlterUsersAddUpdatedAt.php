<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterUsersAddUpdatedAt extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('updated_at', 'users')) {
            $this->forge->addColumn('users', [
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'created_at',
                ],
            ]);
        }

        try {
            $this->db->query('ALTER TABLE `users` ADD UNIQUE KEY `users_username_unique` (`username`)');
        } catch (\Throwable $e) {
            // Ignore if it already exists or cannot be created due to duplicates.
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('updated_at', 'users')) {
            $this->forge->dropColumn('users', 'updated_at');
        }
    }
}

