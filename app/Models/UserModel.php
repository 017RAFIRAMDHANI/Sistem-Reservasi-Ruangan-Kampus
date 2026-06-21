<?php
class UserModel extends BaseModel
{
    public function findByEmailWithRole(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT u.id, u.name, u.email, u.password, rl.name AS role, u.phone, u.nim_nidn, d.name AS department
            FROM users u
            JOIN roles rl ON rl.id = u.role_id
            LEFT JOIN departments d ON d.id = u.department_id
            WHERE u.email = ?
            LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc() ?: null;
        $stmt->close();
        return $user;
    }

    public function findSessionUser(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT u.id, u.name, u.email, rl.name AS role, u.phone, u.nim_nidn, d.name AS department
            FROM users u
            JOIN roles rl ON rl.id = u.role_id
            LEFT JOIN departments d ON d.id = u.department_id
            WHERE u.id = ?
            LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc() ?: null;
        $stmt->close();
        return $row;
    }

    public function allWithRole()
    {
        return $this->db->query("SELECT u.id, u.name, u.email, rl.name AS role, u.phone, u.nim_nidn, d.name AS department, u.created_at
            FROM users u
            JOIN roles rl ON rl.id = u.role_id
            LEFT JOIN departments d ON d.id = u.department_id
            ORDER BY u.id DESC");
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc() ?: null;
        $stmt->close();
        return $row;
    }

    public function currentDepartmentId(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT department_id FROM users WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc() ?: ['department_id' => null];
        $stmt->close();
        return $row;
    }

    public function create(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO users (name, email, password, role_id, phone, nim_nidn, department_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sssissi', $data['name'], $data['email'], $data['password'], $data['role_id'], $data['phone'], $data['nim_nidn'], $data['department_id']);
        $stmt->execute();
        $stmt->close();
    }

    public function update(int $id, array $data, ?string $hashedPassword = null): void
    {
        if ($hashedPassword !== null) {
            $stmt = $this->db->prepare('UPDATE users SET name=?, email=?, password=?, role_id=?, phone=?, nim_nidn=?, department_id=? WHERE id=?');
            $stmt->bind_param('sssissii', $data['name'], $data['email'], $hashedPassword, $data['role_id'], $data['phone'], $data['nim_nidn'], $data['department_id'], $id);
        } else {
            $stmt = $this->db->prepare('UPDATE users SET name=?, email=?, role_id=?, phone=?, nim_nidn=?, department_id=? WHERE id=?');
            $stmt->bind_param('ssissii', $data['name'], $data['email'], $data['role_id'], $data['phone'], $data['nim_nidn'], $data['department_id'], $id);
        }

        $stmt->execute();
        $stmt->close();
    }

    public function updateProfile(int $id, array $data, ?string $hashedPassword = null): void
    {
        if ($hashedPassword !== null) {
            $stmt = $this->db->prepare('UPDATE users SET name=?, email=?, phone=?, nim_nidn=?, department_id=?, password=? WHERE id=?');
            $stmt->bind_param('ssssisi', $data['name'], $data['email'], $data['phone'], $data['nim_nidn'], $data['department_id'], $hashedPassword, $id);
        } else {
            $stmt = $this->db->prepare('UPDATE users SET name=?, email=?, phone=?, nim_nidn=?, department_id=? WHERE id=?');
            $stmt->bind_param('ssssii', $data['name'], $data['email'], $data['phone'], $data['nim_nidn'], $data['department_id'], $id);
        }

        $stmt->execute();
        $stmt->close();
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }
}
