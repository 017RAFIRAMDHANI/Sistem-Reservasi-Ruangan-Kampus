<?php
class BaseModel
{
    protected DBConnection $db;

    public function __construct()
    {
        $this->db = db();
    }
}
