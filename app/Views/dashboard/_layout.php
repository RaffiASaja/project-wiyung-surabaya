<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Dashboard') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
</head>

<body>
    <div class="app">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-top">
                    <img class="brand-shield" src="<?= base_url('assets/img/shield.svg') ?>" alt="">
                    <div class="brand-text">
                        <div class="brand-name">admin LTE</div>
                    </div>
                </div>
                <div class="brand-bottom">
                    <img class="brand-city" src="<?= base_url('assets/img/logo-upiukkb.svg') ?>" alt="">
                    <div class="brand-subtext">
                        <div>Pemerintah</div>
                        <div><b>Kota Surabaya</b></div>
                    </div>
                </div>
            </div>

            <nav class="menu">
                <a class="menu-item <?= ($active ?? '') === 'dashboard' ? 'active' : '' ?>"
                    href="<?= site_url('dashboard') ?>">
                    <span class="icon">⌂</span>
                    <span>Dashboard</span>
                </a>

                <?php $isMasterActive = in_array(($active ?? ''), ['belum', 'sudah'], true); ?>
                <div class="menu-group <?= $isMasterActive ? 'open' : '' ?>" data-menu-group>
                    <div class="menu-item group-title <?= $isMasterActive ? 'active' : '' ?>" data-menu-toggle>
                        <span class="icon">⛃</span>
                        <span>Master Data</span>
                        <span class="caret">›</span>
                    </div>
                    <div class="submenu" data-submenu>
                        <a class="submenu-item <?= ($active ?? '') === 'belum' ? 'active' : '' ?>"
                            href="<?= site_url('master-data/belum-dipanggil') ?>">
                            <span class="dot">○</span>
                            <span>Data belum dipanggil</span>
                        </a>
                        <a class="submenu-item <?= ($active ?? '') === 'sudah' ? 'active' : '' ?>"
                            href="<?= site_url('master-data/sudah-dipanggil') ?>">
                            <span class="dot">○</span>
                            <span>Data sudah dipanggil</span>
                        </a>
                    </div>
                </div>

                <a class="menu-item" href="<?= site_url('logout') ?>">
                    <span class="icon">⇥</span>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>

        <section class="main">
            <header class="topbar">
                <div class="topbar-left">
                    <button class="burger" type="button" aria-label="Menu">☰</button>
                    <div class="topbar-title">Admin Panel - UPUBKB WIYUNG</div>
                </div>
                <div class="topbar-right">
                    <?php
                    $username = session('username');
                    $initial = $username ? strtoupper(substr($username, 0, 1)) : '?';
                    ?>

                    <div class="user"><?= esc($initial) ?></div>
                </div>
            </header>

            <div class="content">
                <?= $this->renderSection('content') ?>
            </div>
        </section>
    </div>

    <script>
        (function () {
            var group = document.querySelector('[data-menu-group]');
            var toggle = document.querySelector('[data-menu-toggle]');
            if (!group || !toggle) return;

            try {
                var saved = localStorage.getItem('menu.master.open');
                if (saved === '1') group.classList.add('open');
                if (saved === '0') group.classList.remove('open');
            } catch (e) { }

            toggle.addEventListener('click', function () {
                group.classList.toggle('open');
                try {
                    localStorage.setItem('menu.master.open', group.classList.contains('open') ? '1' : '0');
                } catch (e) { }
            });
        })();
    </script>
</body>

</html>