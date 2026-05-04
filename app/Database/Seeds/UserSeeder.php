<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        $data = [
            [
                'username' => 'admin',
                'password' => password_hash('admin', PASSWORD_DEFAULT),
                'role' => 'admin',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username' => 'operator',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'role' => 'operator',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        $this->db->table('users')
            ->whereIn('username', array_column($data, 'username'))
            ->delete();

        $this->db->table('users')->insertBatch($data);
    }
}
