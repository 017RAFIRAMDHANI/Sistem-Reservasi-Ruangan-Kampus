<div class="card">
    <form method="POST">
        <div class="grid-2">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" value="<?= e($user['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= e($user['email']); ?>" required>
            </div>
        </div>

        <div class="grid-3">
            <div class="form-group">
                <label>No HP</label>
                <input type="text" name="phone" value="<?= e($user['phone'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>NIM / NIDN</label>
                <input type="text" name="nim_nidn" value="<?= e($user['nim_nidn'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Program Studi / Unit</label>
                <select name="department_id" required>
                    <option value="">Pilih prodi atau unit</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?= $department['id']; ?>" <?= (int)$currentData['department_id'] === (int)$department['id'] ? 'selected' : ''; ?>>
                            <?= e($department['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>Role</label>
                <input type="text" value="<?= e(roleLabel($user['role'])); ?>" disabled>
            </div>
            <div class="form-group">
                <label>Password Baru (opsional)</label>
                <input type="password" name="password" placeholder="Isi jika ingin mengubah password">
            </div>
        </div>

        <div class="actions">
            <button type="submit">Simpan Perubahan</button>
        </div>
    </form>
</div>
