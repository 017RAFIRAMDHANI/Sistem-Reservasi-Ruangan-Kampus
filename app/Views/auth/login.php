
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Reservasi Ruangan</title>
    <link rel="stylesheet" href="<?= e(assetUrl('css/style.css')); ?>">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <h1>Login Sistem</h1>
        <p>Masuk sebagai Admin, Dosen, atau Mahasiswa.</p>

        <?php if ($flash): ?>
            <div class="alert <?= e($flash['type']); ?>"><?= e($flash['message']); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Masukkan email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>
            <button type="submit">Login</button>
        </form>

        <!-- <div class="card mt-4 mb-0">
            <strong>Akun demo</strong>
            <div class="small-text mt-2">Admin: admin@example.com / admin123</div>
            <div class="small-text">Dosen: dosen@example.com / dosen123</div>
            <div class="small-text">Mahasiswa: mahasiswa@example.com / mahasiswa123</div>
        </div> -->
    </div>
</div>
</body>
</html>
