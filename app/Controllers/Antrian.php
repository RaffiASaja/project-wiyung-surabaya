<?php

namespace App\Controllers;

use App\Models\AntrianModel;

class Antrian extends BaseController
{
    public function index()
    {
        $antrian = new AntrianModel();

        $data = [
            'belum' => $antrian->where('status', 'belum')
                ->orderBy('created_at', 'ASC')
                ->findAll(),

            'sudah' => $antrian->whereIn('status', ['dipanggil', 'selesai'])
                ->orderBy("FIELD(status, 'dipanggil', 'selesai')", '', false)
                ->orderBy('waktu_panggil', 'ASC')
                ->findAll()
        ];

        return view('antrian/index', $data);
    }

    public function panggil($id)
    {
        $model = new AntrianModel();
        $data = $model->find($id);

        if (!$data) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        // 🔥 kalau sudah dipanggil → treat sebagai panggil ulang
        if ($data['status'] === 'dipanggil') {
            return $this->panggilUlang($id);
        }

        // 🔥 hitung jumlah aktif di pos
        $jumlah = $model->where('pos', $data['pos'])
            ->where('status', 'dipanggil')
            ->countAllResults();

        if ($jumlah >= 2) {
            return redirect()->back()->with('error', 'Pos ' . $data['pos'] . ' sudah maksimal (2 antrian)');
        }

        $model->update($id, [
            'status' => 'dipanggil',
            'waktu_panggil' => date('Y-m-d H:i:s')
        ]);

        return redirect()->back()->with('success', 'Nomor berhasil dipanggil');
    }

    public function panggilUlang($id)
    {
        $model = new AntrianModel();
        $data = $model->find($id);

        if (!$data) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        // 🔥 hanya boleh untuk yang sudah dipanggil
        if ($data['status'] !== 'dipanggil') {
            return redirect()->back()->with('error', 'Data belum dipanggil');
        }

        // 🔥 OPTIONAL: cooldown biar tidak spam
        if ($data['waktu_panggil'] && strtotime($data['waktu_panggil']) > time() - 3) {
            return redirect()->back()->with('error', 'Tunggu beberapa detik untuk panggil ulang');
        }

        // ❗ TIDAK CEK LIMIT LAGI
        $model->update($id, [
            'waktu_panggil' => date('Y-m-d H:i:s')
        ]);

        return redirect()->back()->with('success', 'Dipanggil ulang');
    }

    public function selesai($id)
    {
        $model = new AntrianModel();

        $model->update($id, [
            'status' => 'selesai'
        ]);

        return redirect()->back()->with('success', 'Antrian selesai');
    }
}