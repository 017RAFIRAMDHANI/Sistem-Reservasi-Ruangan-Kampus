<div class="card">
    <div class="alert warning">Gunakan jam mulai dan jam selesai dengan kelipatan 30 menit, misalnya 13:00 sampai 16:00. Waktu seperti 16:15 tidak diperbolehkan.</div>

    <form method="POST" enctype="multipart/form-data">
        <div class="grid-2">
            <div class="form-group">
                <label>Ruangan</label>
                <select name="room_id" required>
                    <option value="">Pilih ruangan</option>
                    <?php while ($room = $rooms->fetch_assoc()): ?>
                        <option value="<?= $room['id']; ?>">
                            <?= e($room['name']); ?> - <?= e($room['building_name']); ?> - <?= e($room['floor']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Nama Kegiatan</label>
                <input type="text" name="title" required>
            </div>
        </div>

        <div class="form-group">
            <label>Tujuan / Keperluan</label>
            <textarea name="purpose" required></textarea>
        </div>

        <div class="grid-3">
            <div class="form-group">
                <label>Tanggal</label>
                <input type="date" name="reservation_date" min="<?= date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label>Jam Mulai</label>
                <select name="start_time" id="start_time" data-start-time required>
                    <option value="">Pilih jam mulai</option>
                    <?php foreach ($startTimes as $timeValue => $timeLabel): ?>
                        <option value="<?= e($timeValue); ?>"><?= e($timeLabel); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Jam Selesai</label>
                <select name="end_time" id="end_time" data-end-time required>
                    <option value="">Pilih jam selesai</option>
                    <?php foreach ($endTimes as $timeValue => $timeLabel): ?>
                        <option value="<?= e($timeValue); ?>"><?= e($timeLabel); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>Jumlah Peserta</label>
                <input type="number" name="participants" min="1" required>
            </div>
            <div class="form-group">
                <label>Dokumen Pendukung (pdf/jpg/png, maks. 3 MB)</label>
                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png">
                <div class="small-text">File disimpan ke database agar tetap bisa dibuka saat project berjalan di Vercel.</div>
            </div>
        </div>

        <div class="actions">
            <button type="submit">Kirim Pengajuan</button>
            <a class="btn light" href="my_reservations.php">Kembali</a>
        </div>
    </form>
</div>
