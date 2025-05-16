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

    public function getAllByIDTask($task, $user): array
    {
        $sql = "SELECT 
                    st.ID_subtask,
                    st.description,
                    st.expiration_date,
                    st.cmt,
                    st.assignee,
                    st.stat,
                    st.priority
                FROM tasks t
                JOIN subtasks st ON t.ID_task = st.task
                WHERE t.owner = ?
                AND t.ID_task = ?;
        ";

        $query = $this->db->query($sql, [$user, $task]);

        return $query->getResultArray();
    }

    public function getAllByIDUserInvitedByTask($task, $user): array
    {
        $sql = "SELECT 
                    st.ID_subtask,
                    st.description,
                    st.expiration_date,
                    st.cmt,
                    st.assignee,
                    st.stat,
                    st.priority,
                    st.task
                FROM collaborators c
                JOIN subtasks st ON c.task = st.task
                WHERE c.collaborator = ?
                AND st.assignee = ?
                AND c.task = ?;
        ";

        $query = $this->db->query($sql, [$user, $user, $task]);

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
