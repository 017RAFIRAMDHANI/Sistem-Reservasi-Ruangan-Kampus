<?php
$user = currentUser();
$currentPage = isset($_GET['page']) ? basename((string)$_GET['page']) : basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) : 'Sistem Reservasi Ruangan'; ?></title>
    <link rel="stylesheet" href="<?= e(assetUrl('css/style.css')); ?>">
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <h2>Reservasi Ruangan</h2>
            <p>Sistem sederhana</p>
        </div>

        <button class="nav-toggle" type="button" aria-label="Buka menu navigasi" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <?php if ($user): ?>
            <div class="user-box">
                <strong><?= e($user['name']); ?></strong>
                <span><?= e(roleLabel($user['role'])); ?></span>
            </div>
        <?php endif; ?>

        <nav class="menu">
            <?php if ($user): ?>
                <a class="<?= $currentPage === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">Dashboard</a>
                <a class="<?= $currentPage === 'calendar.php' ? 'active' : ''; ?>" href="calendar.php">Kalender Ruangan</a>

                <?php if ($user['role'] === 'admin'): ?>
                    <a class="<?= $currentPage === 'rooms.php' ? 'active' : ''; ?>" href="rooms.php">Data Ruangan</a>
                    <a class="<?= $currentPage === 'reservations.php' ? 'active' : ''; ?>" href="reservations.php">Pengajuan Reservasi</a>
                    <a class="<?= $currentPage === 'users.php' ? 'active' : ''; ?>" href="users.php">Akun Pengguna</a>
                    <a class="<?= $currentPage === 'history.php' ? 'active' : ''; ?>" href="history.php">Riwayat Reservasi</a>
                    <a class="<?= $currentPage === 'reports.php' ? 'active' : ''; ?>" href="reports.php">Laporan Penggunaan</a>
                <?php else: ?>
                    <a class="<?= $currentPage === 'reservation_form.php' ? 'active' : ''; ?>" href="reservation_form.php">Ajukan Reservasi</a>
                    <a class="<?= $currentPage === 'my_reservations.php' ? 'active' : ''; ?>" href="my_reservations.php">Status Reservasi</a>
                    <a class="<?= $currentPage === 'history.php' ? 'active' : ''; ?>" href="history.php">Riwayat Reservasi</a>
                <?php endif; ?>

                <a class="<?= $currentPage === 'profile.php' ? 'active' : ''; ?>" href="profile.php">Profil</a>
                <a href="logout.php" onclick="return confirm('Yakin ingin logout?')">Logout</a>
            <?php else: ?>
                <a class="active" href="login.php">Login</a>
            <?php endif; ?>
        </nav>
    </aside>

    <main class="content">
        <header class="topbar">
            <h1><?= isset($pageTitle) ? e($pageTitle) : 'Sistem Reservasi Ruangan'; ?></h1>
            <div class="topbar-right"><?= date('d M Y, H:i'); ?> WIB</div>
        </header>

        <?php $flash = getFlash(); ?>
        <?php if ($flash): ?>
            <div class="alert <?= e($flash['type']); ?>">
                <?= e($flash['message']); ?>
            </div>
        <?php endif; ?>
