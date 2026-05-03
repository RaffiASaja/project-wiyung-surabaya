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
                        <th style="width: 170px;">Nomor Kendaraan</th>
                        <th>Pos</th>
                        <th style="width: 120px;">Status</th>
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
                            <td><?= $row['pos'] ?? '-' ?></td>
                            <td>
                                <?php if ($row['status'] === 'dipanggil'): ?>
                                    <span style="color: orange;">Dipanggil</span>
                                <?php elseif ($row['status'] === 'selesai'): ?>
                                    <span style="color: green;">Selesai</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $row['waktu_panggil'] ?? '-' ?></td>
                            <td>
                                <!-- 🔁 Panggil lagi -->
                                <a href="/antrian/panggil-ulang/<?= $row['id'] ?>">
                                    <button style="background: #03A9F4;">📞</button>
                                </a>

                                <!-- ✅ Selesai -->
                                <a href="/antrian/selesai/<?= $row['id'] ?>"
                                    onclick="return confirm('Yakin ingin menyelesaikan antrian ini?')">
                                    <button style="background: #4CAF50;">✔</button>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <!-- <tr>
                        <td colspan="6" class="empty">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="6" class="empty">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="6" class="empty">&nbsp;</td>
                    </tr> -->
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>