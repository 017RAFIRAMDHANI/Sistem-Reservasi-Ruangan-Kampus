<div class="card">
    <div class="table-wrap">
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
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <?php if ($user['role'] === 'admin'): ?><td><?= e($row['user_name']); ?></td><?php endif; ?>
                        <td>
                            <strong><?= e($row['room_name']); ?></strong>
                            <div class="small-text"><?= e($row['building_name']); ?> - <?= e($row['floor']); ?></div>
                        </td>
                        <td><?= e($row['title']); ?></td>
                        <td><?= e(date('d-m-Y', strtotime($row['reservation_date']))); ?></td>
                        <td><?= e(substr($row['start_time'], 0, 5)); ?> - <?= e(substr($row['end_time'], 0, 5)); ?></td>
                        <td><span class="<?= e(statusBadgeClass($row['status'])); ?>"><?= e(statusLabel($row['status'])); ?></span></td>
                        <td><?= e($row['admin_note'] ?: '-'); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
