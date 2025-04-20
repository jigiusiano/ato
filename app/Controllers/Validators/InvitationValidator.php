<?php

namespace App\Controllers\Validators;

use App\Models\TaskModel;
use App\Models\UserModel;
use App\Models\InvitationModel;
use App\Models\InvitationStatusModel;
use App\Utils\Response;

class InvitationValidator
{
    private $taskModel;
    private $userModel;
    private $invitationModel;
    private $invitationStatusModel;
    private $res;

    public function __construct()
    {
        $this->taskModel = new TaskModel();
        $this->userModel = new UserModel();
        $this->invitationModel = new InvitationModel();
        $this->invitationStatusModel = new InvitationStatusModel();
        $this->res = new Response();
    }

    private function isMaxLengthValid(string $value, int $maxLength): bool
    {
        return strlen($value) <= $maxLength;
    }

    private function isMinLengthValid(string $value, int $minLength): bool
    {
        return strlen($value) >= $minLength;
    }

    private function taskExists(string $task): bool
    {
        return count($this->taskModel->getById($task)) > 0;
    }

    private function isEmailFormatValid(string $email): bool
    {
        return preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $email);
    }

    private function emailExists(string $email): bool
    {
        return count($this->userModel->getUserByEmail($email)) > 0;
    }

    private function invitationExists(string $email, string $task): bool
    {
        $userData = $this->userModel->getUserByEmail($email);

        return count($this->invitationModel->getByUserAndTask($userData[0]["ID_user"], $task)) > 0;
    }

    private function invitationStatusExists(string $stat): bool
    {
        return count($this->invitationStatusModel->getById($stat)) > 0;
    }

    public function validateData($invitationData): Response
    {
        if (property_exists($invitationData, 'recipient') && !$this->isMaxLengthValid($invitationData->recipient, 255)) {
            $this->res->code = 422;
            $this->res->message = "El email es muy largo";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($invitationData, 'recipient') && !$this->isMinLengthValid($invitationData->recipient, 3)) {
            $this->res->code = 422;
            $this->res->message = "El email es muy corto";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($invitationData, 'recipient') && !$this->isEmailFormatValid($invitationData->recipient)) {
            $this->res->code = 422;
            $this->res->message = "El email es inválido";
            $this->res->areDataValid = false;

            return $this->res;
        }

        try {
            if (property_exists($invitationData, 'task') && !$this->taskExists($invitationData->task)) {
                $this->res->code = 422;
                $this->res->message = "La tarea no existe";
                $this->res->areDataValid = false;

                return $this->res;
            }
        } catch (\Throwable $th) {
            echo $th;
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al verificar la tarea";
            $this->res->areDataValid = false;

            return $this->res;
        }

        try {
            if (property_exists($invitationData, 'recipient') && !$this->emailExists($invitationData->recipient)) {
                $this->res->code = 422;
                $this->res->message = "El usuario no se encuentra registrado";
                $this->res->areDataValid = false;

                return $this->res;
            }
        } catch (\Throwable $th) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al verificar el email";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($invitationData, 'recipient') && property_exists($invitationData, 'task')) {
            try {
                if ($this->invitationExists($invitationData->recipient, $invitationData->task)) {
                    $this->res->code = 422;
                    $this->res->message = "La invitación ya fue enviada";
                    $this->res->areDataValid = false;
    
                    return $this->res;
                }
            } catch (\Throwable $th) {
                echo $th;
                $this->res->code = 500;
                $this->res->message = "Ocurrio un error al verificar la invitación";
                $this->res->areDataValid = false;
    
                return $this->res;
            }
        } else {
            if (is_string($invitationData) && !$this->invitationStatusExists($invitationData)) {
                $this->res->code = 422;
                    $this->res->message = "El estado de la invitación es inválido";
                    $this->res->areDataValid = false;
    
                    return $this->res;
            }
        }

        $this->res->areDataValid = boolval(true);
        
        return $this->res;
    }
}
