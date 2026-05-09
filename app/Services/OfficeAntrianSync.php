<?php

namespace App\Services;

use App\Models\AntrianModel;
use CodeIgniter\I18n\Time;

class OfficeAntrianSync
{
    public function __construct(
        private readonly OfficeApiClient $client = new OfficeApiClient(),
        private readonly AntrianModel $model = new AntrianModel(),
    ) {
    }

    /**
     * Sync office API records into local `antrian` table.
     *
     * - Identity unique: `office_id_hasil_uji` (preferred) or `no_uji`.
     * - Never overwrites local `status` and `waktu_panggil` (so pemanggilan tetap dari sistem lokal).
     *
     * @return array{inserted:int, updated:int, skipped:int, error?:string}
     */
    public function sync(array $query = [], bool $debug = false): array
    {
        if (!$this->client->isEnabled()) {
            return ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'error' => 'OFFICE_API disabled'];
        }

        $tglUji = trim((string) ($query['tgl_uji'] ?? $query['tanggal_uji'] ?? ''));
        if ($tglUji === '') {
            return ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'error' => 'Parameter wajib `tgl_uji` kosong'];
        }
        // Normalize key
        $query['tgl_uji'] = $tglUji;

        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        try {
            $result = $this->client->fetchAntrian($query, $debug);
            $items = $result['items'] ?? $result ?? [];

            if (!is_array($items) || $items === []) {
                $msg = 'Office API response kosong (0 items) untuk tgl_uji=' . $tglUji;
                if ($debug) {
                    log_message('warning', $msg);
                    log_message('warning', 'Office API raw keys: {keys}', [
                        'keys' => is_array($result['raw'] ?? null) ? array_keys($result['raw']) : [],
                    ]);
                }
                return ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'error' => $msg];
            }

            if ($debug) {
                log_message('info', 'Office API raw (preview): {preview}', [
                    'preview' => (string) ($result['meta']['raw_preview'] ?? ''),
                ]);
                log_message('info', 'Office API meta: {meta}', [
                    'meta' => $result['meta'] ?? [],
                ]);
            }

            foreach ($items as $row) {
                if (!is_array($row)) {
                    $skipped++;
                    continue;
                }

                $normalized = $this->normalizeOfficeRow($row);
                if ($normalized === null) {
                    $skipped++;
                    continue;
                }

                $officeIdHasilUji = $normalized['office_id_hasil_uji'];
                $noUji = $normalized['no_uji'];
                $noKendaraan = (string) ($normalized['nomor_kendaraan'] ?? '');

                // Identity resolution
                $existing = null;
                if ($officeIdHasilUji !== null && $officeIdHasilUji !== '') {
                    $existing = $this->model->where('office_id_hasil_uji', (string) $officeIdHasilUji)->first();
                } elseif ($noUji !== '') {
                    // no_uji dianggap unik per tanggal uji
                    $existing = $this->model
                        ->where('office_tgl_uji', $tglUji)
                        ->where('no_uji', $noUji)
                        ->first();
                }

                $payload = $normalized;
                $payload['office_payload'] = json_encode($row, JSON_UNESCAPED_UNICODE);
                $payload['office_tgl_uji'] = $tglUji;
                $payload['office_synced_at'] = date('Y-m-d H:i:s');

                // Minimal required fields (incremental identity)
                if (($payload['office_id_hasil_uji'] ?? null) === null && ($payload['no_uji'] ?? '') === '') {
                    $skipped++;
                    continue;
                }

                if ($existing) {
                    // Do not overwrite status/waktu_panggil from local system.
                    unset($payload['status'], $payload['waktu_panggil']);

                    // Don't blank-out nomor_kendaraan when API doesn't provide it.
                    if (($payload['nomor_kendaraan'] ?? null) === null && !empty($existing['nomor_kendaraan'])) {
                        unset($payload['nomor_kendaraan']);
                    }

                    // Don't blank-out nama when API doesn't provide it.
                    if (($payload['nama'] ?? '') === '-' && !empty($existing['nama'])) {
                        unset($payload['nama']);
                    }

                    $this->model->update((int) $existing['id'], $payload);
                    $updated++;
                } else {
                    $payload['status'] = 'belum';
                    $this->model->insert($payload);
                    $inserted++;
                }
            }

            if ($debug) {
                log_message('info', 'Office sync ok: inserted={inserted}, updated={updated}, skipped={skipped}', [
                    'inserted' => $inserted,
                    'updated' => $updated,
                    'skipped' => $skipped,
                ]);
            }

            return ['inserted' => $inserted, 'updated' => $updated, 'skipped' => $skipped];
        } catch (\Throwable $e) {
            log_message('error', 'Office sync failed: {msg}', ['msg' => $e->getMessage()]);
            if ($debug) {
                log_message('error', 'Office sync context: {ctx}', [
                    'ctx' => [
                        'tgl_uji' => $tglUji,
                        'query' => $query,
                    ],
                ]);
            }
            return [
                'inserted' => 0,
                'updated' => 0,
                'skipped' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Map flexible office payload -> local schema.
     *
     * Minimal fields from API (variants supported):
     * - nama_pemilik
     * - no_uji
     * - no_kendaraan / nomor_kendaraan
     * - no_antrian
     * - id_daftar
     * - id_hasil_uji
     * - posisi
     * - jdatang
     * - jselesai
     *
     * @return array<string,mixed>|null
     */
    private function normalizeOfficeRow(array $row): ?array
    {
        // Allow case-insensitive keys (some APIs use NO_UJI / No_Uji, etc.)
        $rowLower = array_change_key_case($row, CASE_LOWER);

        $get = static function (array $src, array $keys): mixed {
            foreach ($keys as $k) {
                if (array_key_exists($k, $src) && $src[$k] !== null && trim((string) $src[$k]) !== '') {
                    return $src[$k];
                }
            }
            return null;
        };

        $officeIdDaftar = $get($rowLower, ['id_daftar', 'iddaftar', 'id_daftar_uji', 'id_daftar_kendaraan']);
        $officeIdHasilUji = $get($rowLower, ['id_hasil_uji', 'idhasil_uji', 'id_hasil', 'id_hasiluji']);
        $officeIdKendaraan = $get($rowLower, ['id_kendaraan', 'idkendaraan']);
        $officeNoAntrian = $get($rowLower, ['no_antrian', 'noantrian', 'nomor_antrian', 'antrian']);

        $nama = $get($rowLower, ['nama_pemilik', 'namapemilik', 'nama', 'pemilik']);
        $noUji = $get($rowLower, ['no_uji', 'nouji']);
        $kendaraan = $get($rowLower, ['no_kendaraan', 'nomor_kendaraan', 'nokendaraan', 'nopol', 'no_polisi']);
        $posisi = $get($rowLower, ['posisi', 'pos', 'loket', 'counter']);

        $jdatang = $get($rowLower, ['jdatang', 'jam_datang', 'jamdatang', 'datang']);
        $jselesai = $get($rowLower, ['jselesai', 'jam_selesai', 'jamselesai', 'selesai']);

        // Normalize stringy values
        $noUji = $noUji !== null ? trim((string) $noUji) : '';
        $nama = $nama !== null ? trim((string) $nama) : '-';
        $kendaraan = $kendaraan !== null ? trim((string) $kendaraan) : '';
        $posisi = $posisi !== null ? trim((string) $posisi) : '1';

        return [
            'nama' => $nama !== '' ? $nama : '-',
            'no_uji' => $noUji,
            'nomor_kendaraan' => $kendaraan !== '' ? $kendaraan : null,
            'alamat' => null,
            'pos' => $posisi !== '' ? $posisi : '1',
            'office_no_antrian' => $officeNoAntrian !== null ? (string) $officeNoAntrian : null,
            'office_id_hasil_uji' => $officeIdHasilUji !== null ? (string) $officeIdHasilUji : null,
            'office_id_daftar' => $officeIdDaftar !== null ? (string) $officeIdDaftar : null,
            'office_id_kendaraan' => $officeIdKendaraan !== null ? (string) $officeIdKendaraan : null,
            'office_jdatang' => $this->normalizeDateTime($jdatang),
            'office_jselesai' => $this->normalizeDateTime($jselesai),
        ];
    }

    private function normalizeDateTime(mixed $value): ?string
    {
        if ($value === null) return null;
        $s = trim((string) $value);
        if ($s === '') return null;

        // Office sample: "2026-05-05 12:54:31"
        // Keep as-is when it looks like datetime.
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $s)) {
            return substr($s, 0, 19);
        }

        try {
            return Time::parse($s)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }
}
