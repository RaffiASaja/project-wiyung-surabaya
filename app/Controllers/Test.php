<?php

namespace App\Controllers;

use App\Models\AntrianModel;

class Test extends BaseController
{
    public function index()
    {
        $model = new AntrianModel();

        $data = [
            'no_uji' => 'B1234XYZ',
            'nama' => 'Test User'
        ];

        $model->insert($data);

        return "Data berhasil masuk!";
    }
}