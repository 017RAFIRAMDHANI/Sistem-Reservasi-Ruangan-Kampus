<?php
class ReservationModel extends BaseModel
{
    private function reservationColumns(string $alias = 'r'): string
    {
        return "{$alias}.id, {$alias}.user_id, {$alias}.room_id, {$alias}.title, {$alias}.purpose, {$alias}.reservation_date, {$alias}.start_time, {$alias}.end_time, {$alias}.participants, {$alias}.document, {$alias}.status, {$alias}.admin_note, {$alias}.created_at, {$alias}.updated_at";
    }

    public function recentForAdmin(int $limit = 8)
    {
        return $this->db->query("SELECT " . $this->reservationColumns('r') . ", u.name AS user_name, rm.name AS room_name
            FROM reservations r
            JOIN users u ON u.id = r.user_id
            JOIN rooms rm ON rm.id = r.room_id
            ORDER BY r.created_at DESC
            LIMIT " . (int)$limit);
    }

    public function recentForUser(int $userId, int $limit = 8)
    {
        $stmt = $this->db->prepare("SELECT " . $this->reservationColumns('r') . ", rm.name AS room_name
            FROM reservations r
            JOIN rooms rm ON rm.id = r.room_id
            WHERE r.user_id = ?
            ORDER BY r.created_at DESC
            LIMIT " . (int)$limit);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function countForUser(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM reservations WHERE user_id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->bind_result($total);
        $stmt->fetch();
        $stmt->close();
        return (int)$total;
    }

    public function countForUserByStatus(int $userId, string $status): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM reservations WHERE user_id = ? AND status = ?');
        $stmt->bind_param('is', $userId, $status);
        $stmt->execute();
        $stmt->bind_result($total);
        $stmt->fetch();
        $stmt->close();
        return (int)$total;
    }

    public function adminList(string $statusFilter = '')
    {
        if ($statusFilter !== '' && in_array($statusFilter, ['pending', 'verified', 'approved', 'rejected', 'cancelled'], true)) {
            $stmt = $this->db->prepare("SELECT " . $this->reservationColumns('r') . ", u.name AS user_name, rl.name AS user_role, rm.name AS room_name, b.name AS building_name, rm.floor
                FROM reservations r
                JOIN users u ON u.id = r.user_id
                JOIN roles rl ON rl.id = u.role_id
                JOIN rooms rm ON rm.id = r.room_id
                JOIN buildings b ON b.id = rm.building_id
                WHERE r.status = ?
                ORDER BY r.created_at DESC");
            $stmt->bind_param('s', $statusFilter);
            $stmt->execute();
            return $stmt->get_result();
        }

        return $this->db->query("SELECT " . $this->reservationColumns('r') . ", u.name AS user_name, rl.name AS user_role, rm.name AS room_name, b.name AS building_name, rm.floor
            FROM reservations r
            JOIN users u ON u.id = r.user_id
            JOIN roles rl ON rl.id = u.role_id
            JOIN rooms rm ON rm.id = r.room_id
            JOIN buildings b ON b.id = rm.building_id
            ORDER BY r.created_at DESC");
    }

    public function myReservations(int $userId)
    {
        $stmt = $this->db->prepare("SELECT " . $this->reservationColumns('r') . ", rm.name AS room_name
            FROM reservations r
            JOIN rooms rm ON rm.id = r.room_id
            WHERE r.user_id = ?
            ORDER BY r.created_at DESC");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function hasConflict(int $roomId, string $reservationDate, string $startTime, string $endTime): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM reservations
            WHERE room_id = ?
            AND reservation_date = ?
            AND status IN ('pending','verified','approved')
            AND (start_time < ? AND end_time > ?)");
        $stmt->bind_param('isss', $roomId, $reservationDate, $endTime, $startTime);
        $stmt->execute();
        $stmt->bind_result($conflictCount);
        $stmt->fetch();
        $stmt->close();
        return (int)$conflictCount > 0;
    }

    public function create(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO reservations (user_id, room_id, title, purpose, reservation_date, start_time, end_time, participants, document, document_original_name, document_mime_type, document_size, document_data, status, admin_note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param(
            'iisssssisssibss',
            $data['user_id'],
            $data['room_id'],
            $data['title'],
            $data['purpose'],
            $data['reservation_date'],
            $data['start_time'],
            $data['end_time'],
            $data['participants'],
            $data['document'],
            $data['document_original_name'],
            $data['document_mime_type'],
            $data['document_size'],
            $data['document_data'],
            $data['status'],
            $data['admin_note']
        );
        $stmt->execute();
        $stmt->close();
    }

    public function findDocument(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, user_id, document, document_original_name, document_mime_type, document_size, document_data FROM reservations WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $document = $result->fetch_assoc() ?: null;
        $stmt->close();
        return $document;
    }

    public function updateStatus(int $id, string $status, string $note): void
    {
        $stmt = $this->db->prepare('UPDATE reservations SET status = ?, admin_note = ? WHERE id = ?');
        $stmt->bind_param('ssi', $status, $note, $id);
        $stmt->execute();
        $stmt->close();
    }

    public function findUserReservationStatus(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT id, status FROM reservations WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->bind_param('ii', $id, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $reservation = $result->fetch_assoc() ?: null;
        $stmt->close();
        return $reservation;
    }

    public function history(array $user)
    {
        if ($user['role'] === 'admin') {
            return $this->db->query("SELECT " . $this->reservationColumns('r') . ", u.name AS user_name, rm.name AS room_name, b.name AS building_name, rm.floor
                FROM reservations r
                JOIN users u ON u.id = r.user_id
                JOIN rooms rm ON rm.id = r.room_id
                JOIN buildings b ON b.id = rm.building_id
                ORDER BY r.reservation_date DESC, r.created_at DESC");
        }

        $stmt = $this->db->prepare("SELECT " . $this->reservationColumns('r') . ", rm.name AS room_name, b.name AS building_name, rm.floor
            FROM reservations r
            JOIN rooms rm ON rm.id = r.room_id
            JOIN buildings b ON b.id = rm.building_id
            WHERE r.user_id = ?
            ORDER BY r.reservation_date DESC, r.created_at DESC");
        $stmt->bind_param('i', $user['id']);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function calendarEvents(string $startDate, string $endDate, int $roomId = 0): array
    {
        if ($roomId > 0) {
            $stmt = $this->db->prepare("SELECT r.reservation_date, r.start_time, r.end_time, r.status, rm.name AS room_name, b.name AS building_name, rm.floor, r.title
                FROM reservations r
                JOIN rooms rm ON rm.id = r.room_id
                JOIN buildings b ON b.id = rm.building_id
                WHERE r.reservation_date BETWEEN ? AND ?
                AND r.status IN ('pending','verified','approved')
                AND r.room_id = ?
                ORDER BY r.reservation_date ASC, r.start_time ASC");
            $stmt->bind_param('ssi', $startDate, $endDate, $roomId);
        } else {
            $stmt = $this->db->prepare("SELECT r.reservation_date, r.start_time, r.end_time, r.status, rm.name AS room_name, b.name AS building_name, rm.floor, r.title
                FROM reservations r
                JOIN rooms rm ON rm.id = r.room_id
                JOIN buildings b ON b.id = rm.building_id
                WHERE r.reservation_date BETWEEN ? AND ?
                AND r.status IN ('pending','verified','approved')
                ORDER BY r.reservation_date ASC, r.start_time ASC");
            $stmt->bind_param('ss', $startDate, $endDate);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $eventsByDate = [];
        $eventRows = [];

        while ($row = $result->fetch_assoc()) {
            $eventsByDate[$row['reservation_date']][] = $row;
            $eventRows[] = $row;
        }

        $stmt->close();
        return [$eventsByDate, $eventRows];
    }

    public function reportSummary(string $startDate, string $endDate): array
    {
        $stmt = $this->db->prepare("SELECT rm.name AS room_name, b.name AS building_name, rm.floor, COUNT(*) AS total_penggunaan
            FROM reservations r
            JOIN rooms rm ON rm.id = r.room_id
            JOIN buildings b ON b.id = rm.building_id
            WHERE r.status = 'approved' AND r.reservation_date BETWEEN ? AND ?
            GROUP BY r.room_id, rm.name, b.name, rm.floor
            ORDER BY total_penggunaan DESC, rm.name ASC");
        $stmt->bind_param('ss', $startDate, $endDate);
        $stmt->execute();
        $summary = $stmt->get_result();

        $totalApproved = 0;
        $summaryRows = [];
        while ($row = $summary->fetch_assoc()) {
            $summaryRows[] = $row;
            $totalApproved += (int)$row['total_penggunaan'];
        }
        $stmt->close();

        return [$summaryRows, $totalApproved, count($summaryRows)];
    }

    public function reportDetails(string $startDate, string $endDate)
    {
        $stmt = $this->db->prepare("SELECT " . $this->reservationColumns('r') . ", u.name AS user_name, rm.name AS room_name, b.name AS building_name, rm.floor
            FROM reservations r
            JOIN users u ON u.id = r.user_id
            JOIN rooms rm ON rm.id = r.room_id
            JOIN buildings b ON b.id = rm.building_id
            WHERE r.status = 'approved' AND r.reservation_date BETWEEN ? AND ?
            ORDER BY r.reservation_date ASC, r.start_time ASC");
        $stmt->bind_param('ss', $startDate, $endDate);
        $stmt->execute();
        return $stmt->get_result();
    }
}
