<div class="card">
    <form method="GET" class="inline-form">
        <div class="form-group">
            <label>Bulan</label>
            <select name="month">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m; ?>" <?= $m === $month ? 'selected' : ''; ?>><?= date('F', mktime(0, 0, 0, $m, 1)); ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Tahun</label>
            <select name="year">
                <?php for ($y = date('Y') - 1; $y <= date('Y') + 2; $y++): ?>
                    <option value="<?= $y; ?>" <?= $y === $year ? 'selected' : ''; ?>><?= $y; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <button type="submit">Filter</button>
    </form>
</div>

<div class="grid-3">
    <div class="stat-card">
        <h3>Total Penggunaan Disetujui</h3>
        <div class="number"><?= $totalApproved; ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Ruangan Terpakai</h3>
        <div class="number"><?= $totalRoomsUsed; ?></div>
    </div>
    <div class="stat-card">
        <h3>Periode Laporan</h3>
        <div class="number" style="font-size:22px;"><?= e(date('F Y', strtotime($startDate))); ?></div>
    </div>
</div>

<div class="card mt-4">
    <h3>Rekap Penggunaan per Ruangan</h3>
    <div class="table-wrap mt-3">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Ruangan</th>
                    <th>Lokasi</th>
                    <th>Total Penggunaan</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($summaryRows): ?>
                    <?php foreach ($summaryRows as $i => $row): ?>
                        <tr>
                            <td><?= $i + 1; ?></td>
                            <td><?= e($row['room_name']); ?></td>
                            <td><?= e($row['building_name']); ?> - <?= e($row['floor']); ?></td>
                            <td><?= e($row['total_penggunaan']); ?> kali</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">Belum ada data laporan pada periode ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h3>Detail Penggunaan</h3>
    <div class="table-wrap mt-3">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Ruangan</th>
                    <th>Lokasi</th>
                    <th>Pemohon</th>
                    <th>Kegiatan</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($row = $details->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= e(date('d-m-Y', strtotime($row['reservation_date']))); ?></td>
                        <td><?= e($row['room_name']); ?></td>
                        <td><?= e($row['building_name']); ?> - <?= e($row['floor']); ?></td>
                        <td><?= e($row['user_name']); ?></td>
                        <td><?= e($row['title']); ?></td>
                        <td><?= e(substr($row['start_time'], 0, 5)); ?> - <?= e(substr($row['end_time'], 0, 5)); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
