<?= $this->extend('dashboard/_layout') ?>

<?= $this->section('content') ?>
<div class="page">
    <div class="card">
        <div class="card-head">
            <div class="card-title">Data Sudah Di Panggil</div>
            <div class="search">
                <span class="search-icon">⌕</span>
                <input type="text" placeholder="Search...">
            </div>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 70px;">Nomor</th>
                        <th style="width: 180px;">Nama Pemilik</th>
                        <th style="width: 150px;">Nomor Uji</th>
                        <th style="width: 170px;">Nomor Kendaraan</th>
                        <th>Alamat</th>
                        <th style="width: 120px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Wahyu</td>
                        <td>SB 324 124 K</td>
                        <td>L 4567 W</td>
                        <td>Jl. Raya Gubeng No. 12, Surabaya</td>
                        <td>
                            <button class="action icon blue" type="button">☎</button>
                            <button class="action icon red" type="button">✓</button>
                        </td>
                    </tr>
                    <tr><td colspan="6" class="empty">&nbsp;</td></tr>
                    <tr><td colspan="6" class="empty">&nbsp;</td></tr>
                    <tr><td colspan="6" class="empty">&nbsp;</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
