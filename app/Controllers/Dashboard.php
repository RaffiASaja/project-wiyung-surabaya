<?php

namespace App\Controllers;

use App\Models\AntrianModel;

class Dashboard extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        helper(['url']);

        $ujiHariIni = (new AntrianModel())->countAllResults();
        $belumDipanggil = (new AntrianModel())
            ->where('status', 'belum')
            ->countAllResults();

        return view('dashboard/index', [
            'ujiHariIni' => $ujiHariIni,
            'belumDipanggil' => $belumDipanggil,
            'informasiUrl' => site_url('master-data/belum-dipanggil'),
        ]);
    }

    public function dataBelumDipanggil()
    {
        $model = new AntrianModel();

        $data['antrian'] = $model
            ->where('status', 'belum')
            ->findAll();

        return view('dashboard/data_belum_dipanggil', $data);
    }

    public function dataSudahDipanggil()
    {
        $model = new AntrianModel();

        $data['antrian'] = $model
            ->where('status', 'dipanggil')
            ->findAll();

        return view('dashboard/data_sudah_dipanggil', $data);
    }
}
