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

        $tglUji = date('Y-m-d');
        $ujiHariIni = (new AntrianModel())
            ->where('office_tgl_uji', $tglUji)
            ->countAllResults();
        $belumDipanggil = (new AntrianModel())
            ->where('office_tgl_uji', $tglUji)
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
        return view('dashboard/data_belum_dipanggil');
    }

    public function dataSudahDipanggil()
    {
        return view('dashboard/data_sudah_dipanggil');
    }
}
