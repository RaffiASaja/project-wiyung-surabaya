<?php

namespace App\Controllers;

use App\Models\AntrianModel;

class Display extends BaseController
{
    public function index()
    {
        $model = new AntrianModel();

        $pos1 = $model->where('pos', 1)
            ->where('status', 'dipanggil')
            ->orderBy('waktu_panggil', 'DESC')
            ->findAll(2);

        $pos2 = $model->where('pos', 2)
            ->where('status', 'dipanggil')
            ->orderBy('waktu_panggil', 'DESC')
            ->findAll(2);

        return view('display/index', [
            'pos1' => $pos1,
            'pos2' => $pos2
        ]);
    }

    public function data()
    {
        $model = new AntrianModel();

        $pos1 = $model->where('pos', 1)
            ->where('status', 'dipanggil')
            ->orderBy('waktu_panggil', 'DESC')
            ->findAll(2);

        $pos2 = $model->where('pos', 2)
            ->where('status', 'dipanggil')
            ->orderBy('waktu_panggil', 'DESC')
            ->findAll(2);

        return $this->response->setJSON([
            'pos1' => $pos1,
            'pos2' => $pos2
        ]);
    }
}