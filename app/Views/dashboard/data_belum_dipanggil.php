<?= $this->extend('dashboard/_layout') ?>

<?= $this->section('content') ?>
<div class="page">
    <div class="card">
        <div class="card-head">
            <div class="card-title">Data Belum Di Panggil</div>
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
                        <th style="width: 140px;">Aksi Panggilan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Wahyu</td>
                        <td>SB 324 124 K</td>
                        <td>L 4567 W</td>
                        <td>Jl. Raya Gubeng No. 12, Surabaya</td>
                        <td><button class="action call" type="button">☎ panggil</button></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Hendra</td>
                        <td>SB 325 210 K</td>
                        <td>L 8021 W</td>
                        <td>Jl. Tunjungan No. 88, Surabaya</td>
                        <td><button class="action call" type="button">☎ panggil</button></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Herry</td>
                        <td>SB 412 552 K</td>
                        <td>L 1192 W</td>
                        <td>Jl. Dharwahsuda Indah No. 45</td>
                        <td><button class="action call" type="button">☎ panggil</button></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Andi</td>
                        <td>SB 108 903 K</td>
                        <td>L 3350 W</td>
                        <td>Jl. Muhammad No. 155, Surabaya</td>
                        <td><button class="action call" type="button">☎ panggil</button></td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Nanda</td>
                        <td>SB 550 781 K</td>
                        <td>L 9784 W</td>
                        <td>Jl. Ahmad Yani No. 202, Surabaya</td>
                        <td><button class="action call" type="button">☎ panggil</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
