<?php

namespace App\Models;

use CodeIgniter\Database\Config;

class CollaboratorModel
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getByTask($id): array
    {
        $sql = "SELECT * FROM collaborators WHERE task  = ?";
        $query = $this->db->query($sql, [$id]);

        return $query->getResultArray();
    }
}
