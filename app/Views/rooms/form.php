<div class="card">
    <form method="POST">
        <div class="grid-2">
            <div class="form-group">
                <label>Nama Ruangan</label>
                <input type="text" name="name" value="<?= e($room['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Gedung</label>
                <select name="building_id" required>
                    <option value="">Pilih gedung</option>
                    <?php foreach ($buildings as $building): ?>
                        <option value="<?= $building['id']; ?>" <?= (int)$room['building_id'] === (int)$building['id'] ? 'selected' : ''; ?>>
                            <?= e($building['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid-3">
            <div class="form-group">
                <label>Lantai</label>
                <input type="text" name="floor" value="<?= e($room['floor']); ?>" placeholder="Contoh: Lantai 2" required>
            </div>
            <div class="form-group">
                <label>Kapasitas</label>
                <input type="number" name="capacity" min="1" value="<?= e($room['capacity']); ?>" required>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="aktif" <?= $room['status'] === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="nonaktif" <?= $room['status'] === 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="description"><?= e($room['description']); ?></textarea>
        </div>

        <div class="actions">
            <button type="submit">Simpan</button>
            <a class="btn light" href="rooms.php">Kembali</a>
        </div>
    </form>
</div>
