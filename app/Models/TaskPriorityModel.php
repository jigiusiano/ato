<?php

namespace App\Models;

use CodeIgniter\Database\Config;

class TaskPriorityModel
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM task_priority";
        $query = $this->db->query($sql);

        return $query->getResultArray();
    }

    public function getById($id): array
    {
        $sql = "SELECT * FROM task_priority WHERE ID_task_priority = ?";
        $query = $this->db->query($sql, [$id]);

        return $query->getResultArray();
    }

    public function getIds(): array
    {
        $sql = "SELECT ID_priority FROM priorities";
        $query = $this->db->query($sql);

        return $query->getResultArray();
    }
}