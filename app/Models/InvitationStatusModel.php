<?php

namespace App\Models;

use CodeIgniter\Database\Config;

class InvitationStatusModel
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getById($id): array
    {
        $sql = "SELECT * FROM invitation_statuses WHERE ID_invitation_status  = ?";
        $query = $this->db->query($sql, [$id]);

        return $query->getResultArray();
    }
}
