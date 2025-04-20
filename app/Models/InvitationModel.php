<?php

namespace App\Models;

use CodeIgniter\Database\Config;

class InvitationModel
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function create(string $recipient, string $task): void
    {
        $sql = "INSERT INTO invitations (recipient, task) VALUES (?, ?)";
        $this->db->query($sql, [$recipient, $task]);
    }

    public function getById($id): array
    {
        $sql = "SELECT * FROM invitations WHERE ID_invitation = ?";
        $query = $this->db->query($sql, [$id]);

        return $query->getResultArray();
    }

    public function getByUserAndTask($recipient, $task): array
    {
        $sql = "SELECT * FROM invitations WHERE recipient = ? AND task = ?";
        $query = $this->db->query($sql, [$recipient, $task]);

        return $query->getResultArray();
    }

    public function processInvitation(string $id, string $status)
    {
        $this->db->transStart();

        $this->db->query(
            "UPDATE invitations SET stat = ? WHERE ID_invitation = ?",
            [$status, $id]
        );

        $invitation = $this->getById($id);

        if (!$invitation) {
            throw new \Exception("No se encontró la invitación con ID: $id");
        }

        $this->db->query(
            "INSERT INTO collaborators (collaborator, task) VALUES (?, ?)",
            [$invitation[0]["recipient"], $invitation[0]["task"]]
        );

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \Exception("Error al actualizar la invitación y agregar el colaborador.");
        }
    }
}
