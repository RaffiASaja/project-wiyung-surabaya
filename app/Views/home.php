<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home</title>
</head>
<body>
    <div style="padding: 24px; font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;">
        <h1 style="margin: 0 0 8px;">Halaman Utama</h1>
        <div style="margin: 0 0 16px; color: #444;">Login sebagai: <b><?= esc((string) session('username')) ?></b></div>
        <a href="<?= site_url('logout') ?>">Logout</a>
    </div>
</body>
</html>
