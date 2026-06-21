<div class="card">
    <div class="actions">
        <a class="btn" href="room_form.php">Tambah Ruangan</a>
    </div>

    <div class="table-wrap mt-3">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Ruangan</th>
                    <th>Gedung</th>
                    <th>Lantai</th>
                    <th>Kapasitas</th>
                    <th>Status</th>
                    <th>Deskripsi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= e($row['name']); ?></td>
                        <td>
                            <strong><?= e($row['building_name']); ?></strong>
                            <div class="small-text"><?= e($row['address'] ?: '-'); ?></div>
                        </td>
                        <td><?= e($row['floor']); ?></td>
                        <td><?= e($row['capacity']); ?> orang</td>
                        <td><span class="badge <?= $row['status'] === 'aktif' ? 'success' : 'secondary'; ?>"><?= e(ucfirst($row['status'])); ?></span></td>
                        <td><?= e($row['description']); ?></td>
                        <td>
                            <div class="actions">
                                <a class="btn warning" href="room_form.php?id=<?= $row['id']; ?>">Edit</a>
                                <a class="btn danger" href="room_delete.php?id=<?= $row['id']; ?>" data-confirm="Hapus data ruangan ini?">Hapus</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
