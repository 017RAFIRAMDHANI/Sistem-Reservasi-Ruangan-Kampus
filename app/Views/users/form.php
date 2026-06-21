<div class="card">
    <form method="POST">
        <div class="grid-2">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" value="<?= e($data['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= e($data['email']); ?>" required>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>Password <?= $id > 0 ? '(kosongkan jika tidak diubah)' : ''; ?></label>
                <input type="password" name="password" <?= $id === 0 ? 'required' : ''; ?>>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role_id" required>
                    <option value="">Pilih role</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role['id']; ?>" <?= (int)$data['role_id'] === (int)$role['id'] ? 'selected' : ''; ?>>
                            <?= e($role['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid-3">
            <div class="form-group">
                <label>No HP</label>
                <input type="text" name="phone" value="<?= e($data['phone']); ?>">
            </div>
            <div class="form-group">
                <label>NIM / NIDN</label>
                <input type="text" name="nim_nidn" value="<?= e($data['nim_nidn']); ?>">
            </div>
            <div class="form-group">
                <label>Program Studi / Unit</label>
                <select name="department_id" required>
                    <option value="">Pilih prodi atau unit</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?= $department['id']; ?>" <?= (int)$data['department_id'] === (int)$department['id'] ? 'selected' : ''; ?>>
                            <?= e($department['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="actions">
            <button type="submit">Simpan</button>
            <a class="btn light" href="users.php">Kembali</a>
        </div>
    </form>
</div>
