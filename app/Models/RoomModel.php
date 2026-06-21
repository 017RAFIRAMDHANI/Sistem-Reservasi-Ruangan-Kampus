<?php
class RoomModel extends BaseModel
{
    public function allWithBuilding()
    {
        return $this->db->query("SELECT rm.*, b.name AS building_name, b.address,
            CONCAT(b.name, ' - ', rm.floor) AS location_label
            FROM rooms rm
            JOIN buildings b ON b.id = rm.building_id
            ORDER BY rm.id DESC");
    }

    public function activeWithBuilding()
    {
        return $this->db->query("SELECT rm.id, rm.name, rm.capacity, rm.floor, b.name AS building_name
            FROM rooms rm
            JOIN buildings b ON b.id = rm.building_id
            WHERE rm.status = 'aktif'
            ORDER BY rm.name ASC");
    }

    public function activeForCalendar()
    {
        return $this->db->query("SELECT rm.id, rm.name, rm.floor, b.name AS building_name
            FROM rooms rm
            JOIN buildings b ON b.id = rm.building_id
            WHERE rm.status = 'aktif'
            ORDER BY rm.name ASC");
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM rooms WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $room = $result->fetch_assoc() ?: null;
        $stmt->close();
        return $room;
    }

    public function create(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO rooms (name, building_id, floor, capacity, status, description) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sisiss', $data['name'], $data['building_id'], $data['floor'], $data['capacity'], $data['status'], $data['description']);
        $stmt->execute();
        $stmt->close();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare('UPDATE rooms SET name=?, building_id=?, floor=?, capacity=?, status=?, description=? WHERE id=?');
        $stmt->bind_param('sisissi', $data['name'], $data['building_id'], $data['floor'], $data['capacity'], $data['status'], $data['description'], $id);
        $stmt->execute();
        $stmt->close();
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM rooms WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }
}
