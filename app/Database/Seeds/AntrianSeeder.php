<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AntrianSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'no_uji' => 'UJI001',
                'nama' => 'Andi Saputra',
                'nomor_kendaraan' => 'DK1234AB',
                'alamat' => 'Denpasar',
                'pos' => 1,
                'status' => 'belum'
            ],
            [
                'no_uji' => 'UJI002',
                'nama' => 'Budi Santoso',
                'nomor_kendaraan' => 'DK5678CD',
                'alamat' => 'Badung',
                'pos' => 2,
                'status' => 'belum'
            ],
            [
                'no_uji' => 'UJI003',
                'nama' => 'Citra Dewi',
                'nomor_kendaraan' => 'DK1111EF',
                'alamat' => 'Gianyar',
                'pos' => 1,
                'status' => 'belum'
            ],
            [
                'no_uji' => 'UJI004',
                'nama' => 'Dewa Putu',
                'nomor_kendaraan' => 'DK2222GH',
                'alamat' => 'Tabanan',
                'pos' => 2,
                'status' => 'belum'
            ],
            [
                'no_uji' => 'UJI005',
                'nama' => 'Eka Pratama',
                'nomor_kendaraan' => 'DK3333IJ',
                'alamat' => 'Klungkung',
                'pos' => 1,
                'status' => 'belum'
            ]
        ];

        $this->db->table('antrian')->insertBatch($data);
    }
}