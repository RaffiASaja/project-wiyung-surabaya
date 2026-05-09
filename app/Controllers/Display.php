<?php

namespace App\Controllers;

use App\Models\AntrianModel;

class Display extends BaseController
{
    /**
     * Karena kolom `pos` sekarang VARCHAR dan bisa berisi nilai seperti "CIS 1",
     * maka query display harus mengakomodasi alias tersebut.
     */
    private function posAliases(int $pos): array
    {
        $n = (string) $pos;
        return [
            // dukung data legacy yang masih angka
            $n,
            $pos, // kompatibilitas driver tertentu
            // format kantor
            "CIS $n",
            "CIS$n",
            "cis $n",
            "cis$n",
            "CIS-$n",
            "cis-$n",
            "CIS_$n",
            "cis_$n",
        ];
    }

    public function index()
    {
        $model = new AntrianModel();

        $pos1 = $model->whereIn('pos', $this->posAliases(1))
            ->where('status', 'dipanggil')
            ->orderBy('waktu_panggil', 'DESC')
            ->findAll(2);

        $pos2 = $model->whereIn('pos', $this->posAliases(2))
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

        $pos1 = $model->whereIn('pos', $this->posAliases(1))
            ->where('status', 'dipanggil')
            ->orderBy('waktu_panggil', 'DESC')
            ->findAll(2);

        $pos2 = $model->whereIn('pos', $this->posAliases(2))
            ->where('status', 'dipanggil')
            ->orderBy('waktu_panggil', 'DESC')
            ->findAll(2);

        return $this->response->setJSON([
            'pos1' => $pos1,
            'pos2' => $pos2
        ]);
    }
}
