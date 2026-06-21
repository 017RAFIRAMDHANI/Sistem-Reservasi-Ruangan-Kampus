<?php
class DashboardController extends Controller
{
    public function index(): void
    {
        requireLogin();

        $reservations = new ReservationModel();
        $user = currentUser();
        $pageTitle = 'Dashboard';

        if ($user['role'] === 'admin') {
            $stats = [
                'Total Ruangan' => countRows('SELECT COUNT(*) FROM rooms'),
                'Pengajuan Pending' => countRows("SELECT COUNT(*) FROM reservations WHERE status = 'pending'"),
                'Reservasi Disetujui' => countRows("SELECT COUNT(*) FROM reservations WHERE status = 'approved'"),
                'Total Pengguna' => countRows('SELECT COUNT(*) FROM users'),
            ];

            $recentReservations = $reservations->recentForAdmin(8);
        } else {
            $stats = [
                'Total Reservasi Saya' => $reservations->countForUser((int)$user['id']),
                'Status Menunggu' => $reservations->countForUserByStatus((int)$user['id'], 'pending'),
                'Reservasi Disetujui' => $reservations->countForUserByStatus((int)$user['id'], 'approved'),
                'Ruangan Tersedia' => countRows("SELECT COUNT(*) FROM rooms WHERE status = 'aktif'"),
            ];

            $recentReservations = $reservations->recentForUser((int)$user['id'], 8);
        }

        $this->view('dashboard/index', compact('pageTitle', 'user', 'stats', 'recentReservations'));
    }
}
