<?= $this->extend('dashboard/_layout') ?>

<?= $this->section('content') ?>
<?php if (session()->getFlashdata('success')): ?>
        <div class="alert-success" style="
        background: #4CAF50;
        color: white;
        padding: 10px;
        margin: 10px;
        border-radius: 5px;
    ">
            <?= session()->getFlashdata('success') ?>
        </div>
<?php endif; ?>
<div class="page">
    <?php
        $ujiHariIni = $ujiHariIni ?? 10;
        $belumDipanggil = $belumDipanggil ?? 10;
        $informasiUrl = $informasiUrl ?? '#';
    ?>

    <div class="dash-title">Dashboard</div>

    <div class="dash-cards">
        <div class="dash-card dash-card--blue">
            <div class="dash-card__head">Data Uji Hari Ini</div>
            <div class="dash-card__body">
                <div class="dash-card__metric">
                    <div class="dash-card__value"><?= esc($ujiHariIni) ?></div>
                    <div class="dash-card__label">Data</div>
                </div>
                <div class="dash-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 64 64" focusable="false">
                        <ellipse cx="32" cy="14" rx="20" ry="8"></ellipse>
                        <path d="M12 14v30c0 4.4 9 8 20 8s20-3.6 20-8V14" fill="none" stroke="currentColor" stroke-width="6" stroke-linejoin="round"></path>
                        <path d="M12 24c0 4.4 9 8 20 8s20-3.6 20-8" fill="none" stroke="currentColor" stroke-width="6" stroke-linejoin="round"></path>
                        <path d="M12 34c0 4.4 9 8 20 8s20-3.6 20-8" fill="none" stroke="currentColor" stroke-width="6" stroke-linejoin="round"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="dash-card dash-card--green">
            <div class="dash-card__head">Jumlah Belum di Panggil</div>
            <div class="dash-card__body">
                <div class="dash-card__metric">
                    <div class="dash-card__value"><?= esc($belumDipanggil) ?></div>
                    <div class="dash-card__label">Data</div>
                </div>
                <div class="dash-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 64 64" focusable="false">
                        <circle cx="24" cy="26" r="10"></circle>
                        <circle cx="44" cy="30" r="8"></circle>
                        <path d="M8 56c0-10.5 7.5-19 16.7-19h-1.4C34.5 37 42 45.5 42 56" fill="none" stroke="currentColor" stroke-width="6" stroke-linecap="round"></path>
                        <path d="M36 56c0-7.7 5.2-14 11.6-14H46c6.8 0 12 6.3 12 14" fill="none" stroke="currentColor" stroke-width="6" stroke-linecap="round"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="dash-card dash-card--lime">
            <div class="dash-card__head">Informasi</div>
            <div class="dash-card__body dash-card__body--center">
                <div class="dash-card__icon dash-card__icon--big" aria-hidden="true">
                    <svg viewBox="0 0 64 64" focusable="false">
                        <rect x="14" y="8" width="36" height="48" rx="6" fill="none" stroke="currentColor" stroke-width="6"></rect>
                        <circle cx="32" cy="24" r="3"></circle>
                        <path d="M32 30v16" fill="none" stroke="currentColor" stroke-width="6" stroke-linecap="round"></path>
                    </svg>
                </div>
            </div>
            <a class="dash-card__link" href="<?= esc($informasiUrl) ?>">Selengkapnya</a>
        </div>
    </div>
</div>
<script>
setTimeout(() => {
    const alertBox = document.querySelector('.alert-success');
    if (alertBox) {
        alertBox.style.display = 'none';
    }
}, 3000);
</script>
<?= $this->endSection() ?>
