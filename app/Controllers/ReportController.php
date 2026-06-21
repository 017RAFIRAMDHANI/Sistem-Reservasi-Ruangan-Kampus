<?php
class ReportController extends Controller
{
    public function index(): void
    {
        requireRole(['admin']);

        $reservationModel = new ReservationModel();
        $pageTitle = 'Laporan Penggunaan Ruangan';
        $month = isset($_GET['month']) ? max(1, min(12, (int)$_GET['month'])) : (int)date('n');
        $year = isset($_GET['year']) ? max(2024, min(2035, (int)$_GET['year'])) : (int)date('Y');
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        [$summaryRows, $totalApproved, $totalRoomsUsed] = $reservationModel->reportSummary($startDate, $endDate);
        $details = $reservationModel->reportDetails($startDate, $endDate);

        $this->view('reports/index', compact('pageTitle', 'month', 'year', 'startDate', 'endDate', 'summaryRows', 'totalApproved', 'totalRoomsUsed', 'details'));
    }
}
