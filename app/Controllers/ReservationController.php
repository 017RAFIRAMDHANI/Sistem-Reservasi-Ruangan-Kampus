<?php
class ReservationController extends Controller
{
    public function adminIndex(): void
    {
        requireRole(['admin']);

        $pageTitle = 'Verifikasi dan Persetujuan Reservasi';
        $statusFilter = $_GET['status'] ?? '';
        $result = (new ReservationModel())->adminList($statusFilter);

        $this->view('reservations/admin_index', compact('pageTitle', 'statusFilter', 'result'));
    }

    public function form(): void
    {
        requireRole(['dosen', 'mahasiswa']);

        $reservationModel = new ReservationModel();
        $roomModel = new RoomModel();
        $pageTitle = 'Ajukan Reservasi';
        $user = currentUser();
        $rooms = $roomModel->activeWithBuilding();
        $startTimes = generateTimeOptions('07:00', '20:30', 30);
        $endTimes = generateTimeOptions('07:30', '21:00', 30);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $roomId = (int)($_POST['room_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $purpose = trim($_POST['purpose'] ?? '');
            $reservationDate = $_POST['reservation_date'] ?? '';
            $startTimeInput = $_POST['start_time'] ?? '';
            $endTimeInput = $_POST['end_time'] ?? '';
            $participants = (int)($_POST['participants'] ?? 0);
            $documentUpload = uploadDocument($_FILES['document'] ?? []);

            if (!$documentUpload['success']) {
                setFlash('error', $documentUpload['error'] ?: 'Dokumen pendukung gagal diunggah.');
                redirect('reservation_form.php');
            }

            $document = $documentUpload['file'];

            if ($roomId <= 0 || $title === '' || $purpose === '' || $reservationDate === '' || $startTimeInput === '' || $endTimeInput === '' || $participants <= 0) {
                setFlash('error', 'Semua data utama reservasi wajib diisi.');
                redirect('reservation_form.php');
            }

            if (!isValidTimeRange($startTimeInput, $endTimeInput, '07:00', '21:00', 30)) {
                setFlash('error', 'Jam mulai dan jam selesai harus berurutan serta hanya boleh memakai kelipatan 30 menit.');
                redirect('reservation_form.php');
            }

            if ($reservationDate < date('Y-m-d')) {
                setFlash('error', 'Tanggal reservasi tidak boleh kurang dari hari ini.');
                redirect('reservation_form.php');
            }

            $startTime = substr($startTimeInput, 0, 5) . ':00';
            $endTime = substr($endTimeInput, 0, 5) . ':00';

            if ($reservationModel->hasConflict($roomId, $reservationDate, $startTime, $endTime)) {
                setFlash('error', 'Jadwal bentrok dengan reservasi lain pada ruangan yang sama.');
                redirect('reservation_form.php');
            }

            $status = 'pending';
            $adminNote = null;

            $reservationModel->create([
                'user_id' => $user['id'],
                'room_id' => $roomId,
                'title' => $title,
                'purpose' => $purpose,
                'reservation_date' => $reservationDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'participants' => $participants,
                'document' => $document['filename'] ?? null,
                'document_original_name' => $document['original_name'] ?? null,
                'document_mime_type' => $document['mime_type'] ?? null,
                'document_size' => $document['size'] ?? null,
                'document_data' => $document['data'] ?? null,
                'status' => $status,
                'admin_note' => $adminNote,
            ]);

            setFlash('success', 'Pengajuan reservasi berhasil dikirim.');
            redirect('my_reservations.php');
        }

        $this->view('reservations/form', compact('pageTitle', 'user', 'rooms', 'startTimes', 'endTimes'));
    }

    public function action(): void
    {
        requireRole(['admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('reservations.php');
        }

        $id = (int)($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $note = trim($_POST['admin_note'] ?? '');

        $status = match ($action) {
            'verify' => 'verified',
            'approve' => 'approved',
            'reject' => 'rejected',
            default => '',
        };

        if ($id <= 0 || $status === '') {
            setFlash('error', 'Aksi tidak valid.');
            redirect('reservations.php');
        }

        (new ReservationModel())->updateStatus($id, $status, $note);

        setFlash('success', 'Status reservasi berhasil diperbarui.');
        redirect('reservations.php');
    }

    public function cancel(): void
    {
        requireRole(['dosen', 'mahasiswa']);

        $reservationModel = new ReservationModel();
        $user = currentUser();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $reservation = $reservationModel->findUserReservationStatus($id, (int)$user['id']);

        if ($reservation && in_array($reservation['status'], ['pending', 'verified', 'approved'], true)) {
            $reservationModel->updateStatus($id, 'cancelled', 'Dibatalkan oleh pengguna.');
            setFlash('success', 'Reservasi berhasil dibatalkan.');
        } else {
            setFlash('error', 'Reservasi tidak dapat dibatalkan.');
        }

        redirect('my_reservations.php');
    }

    public function mine(): void
    {
        requireRole(['dosen', 'mahasiswa']);

        $pageTitle = 'Reservasi Saya';
        $user = currentUser();
        $result = (new ReservationModel())->myReservations((int)$user['id']);

        $this->view('reservations/mine', compact('pageTitle', 'user', 'result'));
    }

    public function history(): void
    {
        requireLogin();

        $user = currentUser();
        $pageTitle = 'Riwayat Reservasi';
        $result = (new ReservationModel())->history($user);

        $this->view('reservations/history', compact('pageTitle', 'user', 'result'));
    }
    public function document(): void
    {
        requireLogin();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            http_response_code(404);
            echo 'Dokumen tidak ditemukan.';
            return;
        }

        $user = currentUser();
        $reservationModel = new ReservationModel();
        $document = $reservationModel->findDocument($id);

        if (!$document) {
            http_response_code(404);
            echo 'Dokumen tidak ditemukan.';
            return;
        }

        $isOwner = (int)$document['user_id'] === (int)$user['id'];
        $isAdmin = ($user['role'] ?? '') === 'admin';
        if (!$isOwner && !$isAdmin) {
            http_response_code(403);
            echo 'Anda tidak memiliki akses ke dokumen ini.';
            return;
        }

        $filename = $document['document_original_name'] ?: ($document['document'] ?: 'dokumen_pendukung');
        $mimeType = $document['document_mime_type'] ?: 'application/octet-stream';
        $data = $document['document_data'] ?? null;

        if (is_resource($data)) {
            $data = stream_get_contents($data);
        }

        if ($data !== null && $data !== '') {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . strlen((string)$data));
            header('Content-Disposition: inline; filename="' . addslashes($filename) . '"');
            header('X-Content-Type-Options: nosniff');
            echo $data;
            exit;
        }

        // Fallback untuk file lama yang masih tersimpan di folder uploads saat local.
        if (!empty($document['document'])) {
            $localPath = dirname(__DIR__, 2) . '/uploads/' . basename((string)$document['document']);
            if (is_file($localPath)) {
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }

                header('Content-Type: ' . $mimeType);
                header('Content-Length: ' . filesize($localPath));
                header('Content-Disposition: inline; filename="' . addslashes($filename) . '"');
                header('X-Content-Type-Options: nosniff');
                readfile($localPath);
                exit;
            }
        }

        http_response_code(404);
        echo 'Dokumen tidak ditemukan.';
    }

}
