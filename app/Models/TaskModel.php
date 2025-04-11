<?php

namespace App\Models;

use CodeIgniter\Database\Config;

class TaskModel
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function create($taskData): void
    {
        $fields = [];
        $values = [];

        foreach ($taskData as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }

        $taskData = (array)$taskData;
        $columns = implode(', ', array_keys($taskData));
        $placeholders = implode(', ', array_fill(0, count($taskData), '?'));
        $sql = "INSERT INTO tasks ($columns) VALUES ($placeholders)";

        $this->db->query($sql, $values);
    }

    public function getById($id): array
    {
        $sql = "SELECT * FROM tasks WHERE ID_task = ?";
        $query = $this->db->query($sql, [$id]);

        return $query->getResultArray();
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM tasks";
        $query = $this->db->query($sql);

        return $query->getResultArray();
    }

    public function getAllByIDUser($owner): array
    {
        $sql = "SELECT * FROM tasks WHERE owner = ?";
        $query = $this->db->query($sql, [$owner]);

        return $query->getResultArray();
    }

    public function updateById($taskId, $taskData): void
    {
        $fields = [];
        $values = [];
        
        foreach ($taskData as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }

        $sql = "UPDATE tasks SET " . implode(', ', $fields) . " WHERE ID_task = ?";
        $values[] = $taskId;

        $this->db->query($sql, $values);
    }

    public function deleteById($taskId): void
    {
        $sql = "DELETE FROM tasks WHERE ID_task = ?";
        $this->db->query($sql, [$taskId]);
    }
}
