<?= $this->extend('dashboard/_layout') ?>

<?= $this->section('content') ?>
<div class="page">
    <div class="page-header">
        <div></div>
        <a class="btn" href="#">Tampilkan Data</a>
    </div>

    <div class="card">
        <div class="card-head">
            <div class="card-title">Daftar Antrean Kendaraan</div>
            <div class="search">
                <span class="search-icon">⌕</span>
                <input type="text" placeholder="Search...">
            </div>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nomor</th>
                        <th>Nama Pemilik</th>
                        <th>Nomor Uji</th>
                        <th>Nomor Kendaraan</th>
                        <th>Alamat</th>
                        <th>Aksi Panggilan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="6" class="empty">Belum ada data</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
