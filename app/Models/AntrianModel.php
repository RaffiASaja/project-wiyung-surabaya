<?php

namespace App\Models;

use CodeIgniter\Model;

class AntrianModel extends Model
{
    protected $table            = 'antrian';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = [
        'no_uji',
        'nama',
        'nomor_kendaraan',
        'alamat',
        'pos',
        'status',
        'waktu_panggil',
        'office_no_antrian',
        'office_id_hasil_uji',
        'office_id_daftar',
        'office_id_kendaraan',
        'office_jdatang',
        'office_jselesai',
        'office_tgl_uji',
        'office_synced_at',
        'office_payload',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Cleanup otomatis agar DB lokal tidak penuh.
     *
     * Aturan:
     * - status selesai lebih dari 7 hari -> hapus
     * - semua data lebih dari 30 hari -> hapus
     *
     * Menggunakan `office_tgl_uji` jika ada, fallback ke `created_at` (DATE()).
     *
     * @return array{deleted_selesai:int, deleted_all:int}
     */
    public function cleanupOldRecords(): array
    {
        $deletedSelesai = 0;
        $deletedAll = 0;

        $cutoffSelesai = date('Y-m-d', strtotime('-7 days'));
        $cutoffAll = date('Y-m-d', strtotime('-30 days'));

        $tbl = $this->db->table($this->table);

        // 1) status selesai lebih dari 7 hari
        $tbl->where('status', 'selesai');
        $tbl->where(
            '(office_tgl_uji < ? OR (office_tgl_uji IS NULL AND DATE(created_at) < ?))',
            [$cutoffSelesai, $cutoffSelesai]
        );
        $tbl->delete();
        $deletedSelesai = (int) $this->db->affectedRows();

        // 2) semua data lebih dari 30 hari
        $tbl2 = $this->db->table($this->table);
        $tbl2->where(
            '(office_tgl_uji < ? OR (office_tgl_uji IS NULL AND DATE(created_at) < ?))',
            [$cutoffAll, $cutoffAll]
        );
        $tbl2->delete();
        $deletedAll = (int) $this->db->affectedRows();

        return [
            'deleted_selesai' => $deletedSelesai,
            'deleted_all' => $deletedAll,
        ];
    }
}
