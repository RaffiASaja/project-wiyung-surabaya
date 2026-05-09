<?= $this->extend('dashboard/_layout') ?>

<?= $this->section('content') ?>
<div class="page">
    <div class="topBtn">
        <input type="date" id="tanggal_uji">
        <button id="btnAmbilData">Tampilkan Data</button>
    </div>
    <div class="card">
        <div class="card-head">
            <div class="card-title">Data Belum Di Panggil</div>
        </div>

        <div class="table-toolbar">
            <div class="search">
                <span class="search-icon">⌕</span>
                <input type="text" placeholder="Cari nama / no uji / kendaraan..." data-dashboard-search>
            </div>

            <div class="filters">
                <select data-dashboard-sort aria-label="Urutkan">
                    <option value="">Default</option>
                    <option value="terbaru">Terbaru</option>
                    <option value="terlama">Terlama</option>
                </select>

                <select data-dashboard-perpage aria-label="Per halaman">
                    <option value="10">10</option>
                    <option value="20" selected>20</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        <div class="table-wrap">
            <?php if (session()->getFlashdata('success')): ?>
                <p style="color:green;">
                    <?= session()->getFlashdata('success') ?>
                </p>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <p style="color:red;">
                    <?= session()->getFlashdata('error') ?>
                </p>
            <?php endif; ?>

            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 70px;">No</th>
                        <th style="width: 180px;">Nama Pemilik</th>
                        <th style="width: 150px;">Nomor Uji</th>
                        <th style="width: 170px;">Nomor Kendaraan HEBAT</th>
                        <th>tanggal Uji</th>
                        <th>Pos</th>
                        <th style="width: 140px;">Aksi Panggilan</th>
                    </tr>
                </thead>
                <tbody data-dashboard-tbody>
                    <tr>
                        <td colspan="7">
                            <div class="table-empty">
                                <div class="table-empty__title">Memuat data...</div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="table-footer" data-dashboard-table data-kind="belum" data-colcount="7"
                data-endpoint="<?= site_url('antrian/ajax') ?>">
                <div class="table-meta" data-dashboard-meta></div>
                <div class="table-pager" data-dashboard-pager></div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>