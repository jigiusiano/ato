<?php

namespace App\Models;

use CodeIgniter\Database\Config;

class UserModel {
    private $db;

    public function __construct() {
        $this->db = \Config\Database::connect();
    }

    public function create(string $name, string $surname, string $email, string $pass): void {
        $sql = "INSERT INTO users (name, surname, email, pass) VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [$name, $surname, $email, password_hash($pass, PASSWORD_DEFAULT)]);
    }

    public function getById($id): array {
        $sql = "SELECT ID_user, name, surname, email FROM users WHERE ID_user = ?";
        $query = $this->db->query($sql, [$id]);
        
        return $query->getResultArray();
    }

    public function getUserByEmail(string $email): array {
        $sql = "SELECT * FROM users WHERE email = ?";
        $query = $this->db->query($sql, [$email]);

        return $query->getResultArray();
    }

    public function updateById($userId, $userData): void {
        $fields = [];
        $values = [];

        foreach ($userData as $key => $value) {
            $fields[] = "$key = ?";

            if ($key === 'pass') {
                $values[] = password_hash($value, PASSWORD_DEFAULT);;
            } else {
                $values[] = $value;
            }
        }

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE ID_user = ?";
        $values[] = $userId;

        $this->db->query($sql, $values);
    }

    public function deleteById($userId) {
        $sql = "DELETE FROM users WHERE ID_user = ?";
        $this->db->query($sql, [$userId]);
    }
}
