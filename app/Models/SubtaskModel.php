<?php

namespace App\Models;

use CodeIgniter\Database\Config;

class SubtaskModel
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function create($subtaskData): void
    {
        $fields = [];
        $values = [];

        foreach ($subtaskData as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }

        $subtaskData = (array)$subtaskData;
        $columns = implode(', ', array_keys($subtaskData));
        $placeholders = implode(', ', array_fill(0, count($subtaskData), '?'));
        $sql = "INSERT INTO subtasks ($columns) VALUES ($placeholders)";

        $this->db->query($sql, $values);
    }

    public function getById($id): array
    {
        $sql = "SELECT * FROM subtasks WHERE ID_subtask = ?";
        $query = $this->db->query($sql, [$id]);

        return $query->getResultArray();
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM subtasks";
        $query = $this->db->query($sql);

        return $query->getResultArray();
    }

    public function getAllByIDTask($task): array
    {
        $sql = "SELECT * FROM subtasks WHERE task = ?";
        $query = $this->db->query($sql, [$task]);

        return $query->getResultArray();
    }

    public function updateById($subtaskId, $subtaskData): void
    {
        $fields = [];
        $values = [];
        
        foreach ($subtaskData as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }

        $sql = "UPDATE subtasks SET " . implode(', ', $fields) . " WHERE ID_subtask = ?";
        $values[] = $subtaskId;

        $this->db->query($sql, $values);
    }

    public function deleteById($subtaskId): void
    {
        $sql = "DELETE FROM subtasks WHERE ID_subtask = ?";
        $this->db->query($sql, [$subtaskId]);
    }
}