<?php
class CalendarController extends Controller
{
    public function index(): void
    {
        requireLogin();

        $roomModel = new RoomModel();
        $reservationModel = new ReservationModel();
        $pageTitle = 'Kalender Ruangan';
        $month = isset($_GET['month']) ? max(1, min(12, (int)$_GET['month'])) : (int)date('n');
        $year = isset($_GET['year']) ? max(2024, min(2035, (int)$_GET['year'])) : (int)date('Y');
        $roomId = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;

        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));
        $firstWeekDay = (int)date('N', strtotime($startDate));
        $daysInMonth = (int)date('t', strtotime($startDate));
        $rooms = $roomModel->activeForCalendar();
        [$eventsByDate, $eventRows] = $reservationModel->calendarEvents($startDate, $endDate, $roomId);

        $this->view('calendar/index', compact('pageTitle', 'month', 'year', 'roomId', 'startDate', 'endDate', 'firstWeekDay', 'daysInMonth', 'rooms', 'eventsByDate', 'eventRows'));
    }
}
