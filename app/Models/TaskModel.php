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

    public function getAllByIDUser($owner, $archived): array
    {
        $sql = "SELECT * FROM tasks WHERE owner = ? AND archived = ?";
        $query = $this->db->query($sql, [$owner, $archived]);

        return $query->getResultArray();
    }

    public function getAllByIDUserInvited($user, $archived): array
    {
        $sql = "SELECT 
                    t.ID_task,
                    t.subject,
                    t.description,
                    t.expiration_date,
                    t.reminder_date,
                    t.color,
                    t.owner,
                    t.archived,
                    t.stat,
                    t.priority
                FROM collaborators c
                JOIN tasks t ON c.task = t.ID_task
                WHERE c.collaborator = ? AND t.archived = ?;
        ";

        $query = $this->db->query($sql, [$user, $archived]);

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
