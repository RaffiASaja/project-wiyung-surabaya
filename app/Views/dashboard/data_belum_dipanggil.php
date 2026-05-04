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
                        <th style="width: 70px;">Nomor</th>
                        <th style="width: 180px;">Nama Pemilik</th>
                        <th style="width: 150px;">Nomor Uji</th>
                        <th style="width: 170px;">Nomor Kendaraan HEBAT</th>
                        <th>Alamat</th>
                        <th>Pos</th>
                        <th style="width: 140px;">Aksi Panggilan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    <?php foreach ($antrian as $row): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $row['nama'] ?></td>
                            <td><?= $row['no_uji'] ?></td>
                            <td><?= $row['nomor_kendaraan'] ?? '-' ?></td>
                            <td><?= $row['alamat'] ?? '-' ?></td>
                            <td><?= $row['pos'] ?? '-' ?></td>
                            <!-- <td>
                                <span style="color: orange;">Belum Dipanggil</span>
                            </td> -->
                            <td>
                                <a href="/antrian/panggil/<?= $row['id'] ?>" class="btn-call"
                                    onclick="return confirm('Yakin ingin memanggil nomor ini?')">
                                    <img src="https://img.icons8.com/ios-glyphs/30/phone-disconnected.png" width="20""> Panggil
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>