<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/auth.css') ?>">
</head>

<body>
    <main class="auth">
        <section class="auth-card">
            <div class="auth-logo">
                <img src="<?= base_url('assets/img/logo_dishub.png') ?>" alt="Logo">
            </div>

            <h1 class="auth-title">UPIUKKB WIYUNG</h1>
            <p class="auth-subtitle">Pemerintah Kota Surabaya</p>

            <?php $errors = session('errors'); ?>
            <?php if (!empty($errors) && is_array($errors)): ?>
                <div class="auth-errors" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <div><?= esc($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="post" action="<?= site_url('login') ?>" autocomplete="off">
                <?= csrf_field() ?>

                <div class="field">
                    <input type="text" name="username" placeholder="Username" value="<?= old('username') ?>">
                </div>
                <?php if (isset($errors['username'])): ?>
                    <small style="color:red;">
                        <?= esc($errors['username']) ?>
                    </small>
                <?php endif; ?>

                <div class="field">
                    <input type="password" name="password" placeholder="Password">
                </div>

                <button class="btn" type="submit">Login</button>
            </form>
        </section>
    </main>
</body>

</html>