<?php

namespace App\Controllers;

use App\Models\AntrianModel;
use App\Services\OfficeAntrianSync;

class Antrian extends BaseController
{
    /**
     * Normalisasi nilai `pos` untuk kebutuhan tampilan (tanpa mengubah DB).
     * Target: selalu tampil "CIS 1" / "CIS 2" untuk pos 1/2.
     */
    private function normalizePosLabel($pos): ?string
    {
        if ($pos === null) {
            return null;
        }

        $raw = trim((string) $pos);
        if ($raw === '') {
            return null;
        }

        $lower = strtolower($raw);
        if ($lower === '1') return 'CIS 1';
        if ($lower === '2') return 'CIS 2';

        if (preg_match('/\bcis\s*[-_ ]*\s*([12])\b/i', $raw, $m)) {
            return 'CIS ' . $m[1];
        }

        return $raw;
    }

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

        // kalau sudah dipanggil -> treat sebagai panggil ulang
        if (($data['status'] ?? '') === 'dipanggil') {
            return $this->panggilUlang($id);
        }
        

        $model->update($id, [
            'status' => 'dipanggil',
            'waktu_panggil' => date('Y-m-d H:i:s'),
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

        // hanya boleh untuk yang sudah dipanggil
        if (($data['status'] ?? '') !== 'dipanggil') {
            return redirect()->back()->with('error', 'Data belum dipanggil');
        }

        // OPTIONAL: cooldown biar tidak spam
        if ($data['waktu_panggil'] instanceof \CodeIgniter\I18n\Time) {
            $time = $data['waktu_panggil']->getTimestamp();
        } else {
            $time = strtotime((string) ($data['waktu_panggil'] ?? ''));
        }

        if ($time && $time > time() - 3) {
            return redirect()->back()->with('error', 'Tunggu beberapa detik untuk panggil ulang');
        }

        $model->update($id, [
            'waktu_panggil' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Dipanggil ulang');
    }

    public function selesai($id)
    {
        $model = new AntrianModel();

        $model->update($id, [
            'status' => 'selesai',
        ]);

        return redirect()->back()->with('success', 'Antrian selesai');
    }

    /**
     * Ajax list untuk dashboard (belum/sudah) + search/filter/pagination.
     * Dashboard membaca database lokal (tanpa auto-sync).
     */
    public function ajaxList()
    {
        $model = new AntrianModel();
        $debug = filter_var($this->request->getGet('debug') ?? false, FILTER_VALIDATE_BOOL);

        $type = strtolower(trim((string) ($this->request->getGet('type') ?? 'belum')));
        if (!in_array($type, ['belum', 'sudah'], true)) {
            $type = 'belum';
        }

        // Dashboard wajib ter-filter berdasarkan tanggal uji (default: hari ini).
        $tglUji = trim((string) ($this->request->getGet('tgl_uji') ?? $this->request->getGet('tanggal_uji') ?? ''));
        if ($tglUji === '') {
            $tglUji = date('Y-m-d');
        }

        $search = trim((string) ($this->request->getGet('search') ?? ''));
        $sort = strtolower(trim((string) ($this->request->getGet('sort') ?? '')));

        $perPage = (int) ($this->request->getGet('perPage') ?? 20);
        $perPage = max(1, min(100, $perPage));

        $page = (int) ($this->request->getGet('page') ?? 1);
        $page = max(1, $page);

        $builder = $model->builder();
        $builder->select('id, no_uji, nama, nomor_kendaraan, office_tgl_uji, pos, status, waktu_panggil, created_at');
        $builder->where('office_tgl_uji', $tglUji);

        if ($type === 'sudah') {
            $builder->whereIn('status', ['dipanggil', 'selesai']);
        } else {
            $builder->where('status', 'belum');
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('nama', $search)
                ->orLike('no_uji', $search)
                ->orLike('nomor_kendaraan', $search)
                ->orLike('alamat', $search)
                ->orLike('pos', $search)
                ->groupEnd();
        }

        if ($type === 'sudah') {
            $builder->orderBy("FIELD(status, 'dipanggil', 'selesai')", '', false);
            $builder->orderBy('waktu_panggil', $sort === 'terbaru' ? 'DESC' : 'ASC');
        } else {
            $builder->orderBy('created_at', $sort === 'terbaru' ? 'DESC' : 'ASC');
        }

        $countBuilder = clone $builder;
        $total = (int) $countBuilder->countAllResults();

        $pageCount = (int) max(1, (int) ceil($total / $perPage));
        if ($page > $pageCount) {
            $page = $pageCount;
        }

        $offset = ($page - 1) * $perPage;
        $data = $builder->limit($perPage, $offset)->get()->getResultArray();

        // Pastikan label pos konsisten "CIS 1/2" pada dashboard (belum/sudah).
        foreach ($data as &$row) {
            if (array_key_exists('pos', $row)) {
                $row['pos'] = $this->normalizePosLabel($row['pos']);
            }
        }
        unset($row);

        $payload = [
            'data' => $data,
            'meta' => [
                'type' => $type,
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'pageCount' => $pageCount,
            ],
            'query' => [
                'type' => $type,
                'tgl_uji' => $tglUji,
                'search' => $search,
                'sort' => $sort,
                'page' => $page,
                'perPage' => $perPage,
            ],
        ];

        if ($debug) {
            $payload['debug'] = ['note' => 'ajaxList membaca DB lokal (tidak melakukan sync)'];
        }

        return $this->response->setJSON($payload);
    }

    /**
     * Endpoint manual untuk memicu sync data kantor.
     * Bisa dipakai untuk debug: `/antrian/ambil-data-kantor?tanggal_uji=2026-05-05&debug=1`
     */
    public function ambilDataKantor()
    {
        $tanggal = trim((string) ($this->request->getGet('tgl_uji') ?? $this->request->getGet('tanggal_uji') ?? ''));
        $debug = filter_var($this->request->getGet('debug') ?? false, FILTER_VALIDATE_BOOL);

        if ($tanggal === '') {
            return $this->response->setJSON([
                'status' => false,
                'tanggal_uji' => null,
                'message' => 'Parameter wajib `tgl_uji` harus diisi (format: YYYY-MM-DD)',
            ])->setStatusCode(400);
        }

        try {
            $sync = new OfficeAntrianSync();
            $result = $sync->sync(['tgl_uji' => $tanggal], $debug);

            $ok = !isset($result['error']);

            // Cleanup otomatis saat sync (aman: hanya data lama).
            try {
                (new AntrianModel())->cleanupOldRecords();
            } catch (\Throwable) {
                // ignore cleanup failures
            }

            return $this->response->setJSON([
                'status' => $ok,
                'tanggal_uji' => $tanggal,
                'sync' => $result,
            ]);
        } catch (\Throwable $e) {
            if ($debug) {
                log_message('error', 'ambilDataKantor failed: {msg}', ['msg' => $e->getMessage()]);
            }

            return $this->response->setJSON([
                'status' => false,
                'tanggal_uji' => $tanggal,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
