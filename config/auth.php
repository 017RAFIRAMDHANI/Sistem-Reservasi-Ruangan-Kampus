<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';

date_default_timezone_set('Asia/Jakarta');

spl_autoload_register(function (string $class): void {
    $baseDir = dirname(__DIR__) . '/app/';
    $paths = [
        $baseDir . 'Core/' . $class . '.php',
        $baseDir . 'Controllers/' . $class . '.php',
        $baseDir . 'Models/' . $class . '.php',
    ];

    foreach ($paths as $path) {
        if (is_file($path)) {
            require_once $path;
            return;
        }
    }
});

function db(): DBConnection {
    return getDB();
}

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}


function isVercelRuntime(): bool {
    $host = $_SERVER['HTTP_HOST'] ?? '';

    return envValue('VERCEL', '0') === '1'
        || str_contains($host, '.vercel.app')
        || str_contains($host, 'vercel.app');
}

function appBasePath(): string {
    $configured = envValue('APP_BASE_PATH');
    if ($configured !== null) {
        return rtrim('/' . trim($configured, '/'), '/');
    }

    if (isVercelRuntime()) {
        return '';
    }

    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    if ($scriptName === '' || $scriptName === '/') {
        return '';
    }

    $directory = str_replace('\\', '/', dirname($scriptName));
    if ($directory === '.' || $directory === '/' || $directory === '\\') {
        return '';
    }

    return rtrim($directory, '/');
}

function assetUrl(string $path): string {
    return appBasePath() . '/assets/' . ltrim($path, '/');
}

function uploadUrl(string $filename): string {
    return appBasePath() . '/uploads/' . ltrim($filename, '/');
}

function documentUrl(int|string $reservationId): string {
    return appBasePath() . '/document.php?id=' . urlencode((string)$reservationId);
}

function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function getFlash(): ?array {
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function isLoggedIn(): bool {
    return isset($_SESSION['user']);
}

function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        setFlash('error', 'Silakan login terlebih dahulu.');
        redirect('login.php');
    }
}

function requireRole(array $roles): void {
    requireLogin();
    $user = currentUser();

    if (!$user || !in_array($user['role'], $roles, true)) {
        setFlash('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        redirect('dashboard.php');
    }
}

function refreshSessionUser(int $userId): void {
    $userModel = new UserModel();
    $row = $userModel->findSessionUser($userId);

    if ($row) {
        $_SESSION['user'] = $row;
    }
}

function countRows(string $sql): int {
    $result = db()->query($sql);
    if (!$result) {
        return 0;
    }

    $row = $result->fetch_row();
    return (int)($row[0] ?? 0);
}

function statusBadgeClass(string $status): string {
    return match ($status) {
        'pending' => 'badge warning',
        'verified' => 'badge info',
        'approved' => 'badge success',
        'rejected' => 'badge danger',
        'cancelled' => 'badge secondary',
        default => 'badge secondary',
    };
}

function statusLabel(string $status): string {
    return match ($status) {
        'pending' => 'Menunggu',
        'verified' => 'Terverifikasi',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'cancelled' => 'Dibatalkan',
        default => ucfirst($status),
    };
}

function roleLabel(string $role): string {
    return match ($role) {
        'admin' => 'Admin',
        'dosen' => 'Dosen',
        'mahasiswa' => 'Mahasiswa',
        default => ucfirst($role),
    };
}

function canCancelReservation(array $reservation): bool {
    return in_array($reservation['status'], ['pending', 'verified', 'approved'], true);
}

function generateTimeOptions(string $start = '07:00', string $end = '21:00', int $intervalMinutes = 30, bool $includeEnd = true): array {
    $options = [];
    $current = strtotime($start);
    $endTimestamp = strtotime($end);

    while (($includeEnd && $current <= $endTimestamp) || (!$includeEnd && $current < $endTimestamp)) {
        $time = date('H:i', $current);
        $options[$time] = $time;
        $current = strtotime('+' . $intervalMinutes . ' minutes', $current);
    }

    return $options;
}

function isValidBoundaryTime(string $time, string $minTime = '07:00', string $maxTime = '21:00', int $intervalMinutes = 30): bool {
    $normalizedTime = substr($time, 0, 5);

    if (!preg_match('/^\d{2}:\d{2}$/', $normalizedTime)) {
        return false;
    }

    [$hour, $minute] = array_map('intval', explode(':', $normalizedTime));
    if ($minute % $intervalMinutes !== 0) {
        return false;
    }

    $timestamp = strtotime($normalizedTime);
    return $timestamp !== false
        && $timestamp >= strtotime($minTime)
        && $timestamp <= strtotime($maxTime);
}

function isValidTimeRange(string $startTime, string $endTime, string $minTime = '07:00', string $maxTime = '21:00', int $intervalMinutes = 30): bool {
    if (!isValidBoundaryTime($startTime, $minTime, $maxTime, $intervalMinutes) || !isValidBoundaryTime($endTime, $minTime, $maxTime, $intervalMinutes)) {
        return false;
    }

    $startTimestamp = strtotime(substr($startTime, 0, 5));
    $endTimestamp = strtotime(substr($endTime, 0, 5));
    if ($startTimestamp === false || $endTimestamp === false || $endTimestamp <= $startTimestamp) {
        return false;
    }

    return (($endTimestamp - $startTimestamp) % ($intervalMinutes * 60)) === 0;
}

function uploadMaxBytes(): int {
    return (int)envValue('UPLOAD_MAX_BYTES', '3145728');
}

function uploadDocument(array $file): array {
    if (!isset($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [
            'success' => true,
            'file' => null,
            'error' => null,
        ];
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'file' => null,
            'error' => 'Dokumen pendukung gagal diunggah. Coba pilih file lain.',
        ];
    }

    $allowedExt = ['pdf', 'jpg', 'jpeg', 'png'];
    $allowedMime = [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ];

    $originalName = basename((string)$file['name']);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExt, true)) {
        return [
            'success' => false,
            'file' => null,
            'error' => 'Format dokumen harus PDF, JPG, JPEG, atau PNG.',
        ];
    }

    $size = (int)($file['size'] ?? 0);
    $maxBytes = uploadMaxBytes();
    if ($size <= 0 || $size > $maxBytes) {
        return [
            'success' => false,
            'file' => null,
            'error' => 'Ukuran dokumen maksimal ' . round($maxBytes / 1024 / 1024, 1) . ' MB.',
        ];
    }

    $tmpName = (string)($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        return [
            'success' => false,
            'file' => null,
            'error' => 'File upload tidak valid.',
        ];
    }

    $mimeType = '';
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = (string)$finfo->file($tmpName);
    }
    if ($mimeType === '') {
        $mimeType = (string)($file['type'] ?? 'application/octet-stream');
    }

    if (!in_array($mimeType, $allowedMime, true)) {
        return [
            'success' => false,
            'file' => null,
            'error' => 'Tipe file tidak valid. Gunakan PDF, JPG, JPEG, atau PNG.',
        ];
    }

    $data = file_get_contents($tmpName);
    if ($data === false) {
        return [
            'success' => false,
            'file' => null,
            'error' => 'Dokumen tidak dapat dibaca oleh server.',
        ];
    }

    $storedName = uniqid('doc_', true) . '.' . $ext;

    /*
     * Catatan:
     * Di Vercel, filesystem function tidak cocok untuk file upload permanen.
     * Karena itu file disimpan ke kolom BYTEA di PostgreSQL/Neon.
     * Untuk local, kita tetap coba simpan salinan ke folder uploads agar alur lama tetap jalan.
     */
    $uploadDir = dirname(__DIR__) . '/uploads/';
    if (!isVercelRuntime()) {
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }
        if (is_dir($uploadDir) && is_writable($uploadDir)) {
            @copy($tmpName, $uploadDir . $storedName);
        }
    }

    return [
        'success' => true,
        'file' => [
            'filename' => $storedName,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'size' => $size,
            'data' => $data,
        ],
        'error' => null,
    ];
}

function getRoleOptions(): array {
    return (new OptionModel())->roles();
}

function getDepartmentOptions(): array {
    return (new OptionModel())->departments();
}

function getBuildingOptions(): array {
    return (new OptionModel())->buildings();
}
