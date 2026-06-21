<div class="grid-4">
    <?php foreach ($stats as $label => $value): ?>
        <div class="stat-card">
            <h3><?= e($label); ?></h3>
            <div class="number"><?= e($value); ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="grid-2 mt-4">
    <div class="card">
        <h3>Menu Cepat</h3>
        <div class="actions mt-3">
            <a class="btn" href="calendar.php">Lihat Kalender</a>
            <?php if ($user['role'] === 'admin'): ?>
                <a class="btn success" href="rooms.php">Kelola Ruangan</a>
                <a class="btn warning" href="reservations.php">Cek Pengajuan</a>
            <?php else: ?>
                <a class="btn success" href="reservation_form.php">Ajukan Reservasi</a>
                <a class="btn secondary" href="my_reservations.php">Lihat Status</a>
            <?php endif; ?>
            <a class="btn light" href="profile.php">Kelola Profil</a>
        </div>
    </div>

    <div class="card">
        <h3>Informasi Singkat</h3>
        <p class="small-text">Sistem ini digunakan untuk pengajuan, verifikasi, persetujuan, dan pemantauan penggunaan ruangan. Tampilan dibuat sederhana agar mudah dipahami dan dikembangkan kembali.</p>
    </div>
</div>

<div class="card">
    <h3><?= $user['role'] === 'admin' ? 'Pengajuan Terbaru' : 'Reservasi Saya Terbaru'; ?></h3>
    <div class="table-wrap mt-3">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <?php if ($user['role'] === 'admin'): ?><th>Pemohon</th><?php endif; ?>
                    <th>Ruangan</th>
                    <th>Kegiatan</th>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($row = $recentReservations->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <?php if ($user['role'] === 'admin'): ?><td><?= e($row['user_name']); ?></td><?php endif; ?>
                        <td><?= e($row['room_name']); ?></td>
                        <td><?= e($row['title']); ?></td>
                        <td><?= e(date('d-m-Y', strtotime($row['reservation_date']))); ?></td>
                        <td><?= e(substr($row['start_time'], 0, 5)); ?> - <?= e(substr($row['end_time'], 0, 5)); ?></td>
                        <td><span class="<?= e(statusBadgeClass($row['status'])); ?>"><?= e(statusLabel($row['status'])); ?></span></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
