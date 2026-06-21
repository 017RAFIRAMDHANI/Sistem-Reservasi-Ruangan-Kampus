<?php
class OptionModel extends BaseModel
{
    public function roles(): array
    {
        $result = $this->db->query('SELECT id, name, label FROM roles ORDER BY id ASC');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function departments(): array
    {
        $result = $this->db->query('SELECT id, name FROM departments ORDER BY name ASC');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function buildings(): array
    {
        $result = $this->db->query('SELECT id, name, address FROM buildings ORDER BY name ASC');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}
