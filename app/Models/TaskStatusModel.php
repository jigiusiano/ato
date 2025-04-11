<?php

namespace App\Models;

use CodeIgniter\Database\Config;

class TaskStatusModel
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM task_status";
        $query = $this->db->query($sql);

        return $query->getResultArray();
    }

    public function getById($id): array
    {
        $sql = "SELECT * FROM task_status WHERE ID_task_status = ?";
        $query = $this->db->query($sql, [$id]);

        return $query->getResultArray();
    }

    public function getIds(): array
    {
        $sql = "SELECT ID_status FROM statuses";
        $query = $this->db->query($sql);

        return $query->getResultArray();
    }
}