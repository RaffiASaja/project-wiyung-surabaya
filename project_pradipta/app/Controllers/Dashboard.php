<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index(): string
    {
        helper(['url']);

        return view('dashboard/index', [
            'title' => 'Dashboard',
            'active' => 'dashboard',
        ]);
    }

    public function dataBelumDipanggil(): string
    {
        helper(['url']);

        return view('dashboard/data_belum_dipanggil', [
            'title' => 'Data belum dipanggil',
            'active' => 'belum',
        ]);
    }

    public function dataSudahDipanggil(): string
    {
        helper(['url']);

        return view('dashboard/data_sudah_dipanggil', [
            'title' => 'Data sudah dipanggil',
            'active' => 'sudah',
        ]);
    }
}
