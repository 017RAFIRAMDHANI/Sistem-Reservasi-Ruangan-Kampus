<div class="card">
    <div class="actions">
        <a class="btn" href="reservation_form.php">Ajukan Reservasi Baru</a>
    </div>

    <div class="table-wrap mt-3">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Ruangan</th>
                    <th>Kegiatan</th>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Peserta</th>
                    <th>Dokumen</th>
                    <th>Status</th>
                    <th>Catatan Admin</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= e($row['room_name']); ?></td>
                        <td><?= e($row['title']); ?></td>
                        <td><?= e(date('d-m-Y', strtotime($row['reservation_date']))); ?></td>
                        <td><?= e(substr($row['start_time'], 0, 5)); ?> - <?= e(substr($row['end_time'], 0, 5)); ?></td>
                        <td><?= e($row['participants']); ?></td>
                        <td>
                            <?php if ($row['document']): ?>
                                <a class="btn light" target="_blank" href="<?= e(documentUrl($row['id'])); ?>">Lihat File</a>
                            <?php else: ?>
                                <span class="small-text">Tidak ada</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="<?= e(statusBadgeClass($row['status'])); ?>"><?= e(statusLabel($row['status'])); ?></span></td>
                        <td><?= e($row['admin_note'] ?: '-'); ?></td>
                        <td>
                            <?php if (canCancelReservation($row)): ?>
                                <a class="btn danger" href="reservation_cancel.php?id=<?= $row['id']; ?>" data-confirm="Batalkan pengajuan reservasi ini?">Batalkan</a>
                            <?php else: ?>
                                <span class="small-text">Tidak ada aksi</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
