<?php

namespace App\Controllers\Validators;

use App\Models\TaskModel;
use App\Models\SubtaskModel;
use App\Models\TaskPriorityModel;
use App\Models\TaskStatusModel;
use App\Models\UserModel;
use App\Utils\Response;

class SubtaskValidator
{
    private SubtaskModel $subtaskModel;
    private TaskPriorityModel $taskPriorityModel;
    private TaskStatusModel $taskStatusModel;
    private TaskModel $taskModel;
    private UserModel $userModel;
    private Response $res;

    public function __construct()
    {
        $this->subtaskModel = new SubtaskModel();
        $this->taskPriorityModel = new TaskPriorityModel();
        $this->taskStatusModel = new TaskStatusModel();
        $this->taskModel = new TaskModel();
        $this->userModel = new UserModel();
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

    private function isDateFormatValid(string $date): bool
    {
        $date = explode('T', $date);
        if (count($date) != 2) {
            return false;   
        }

        $date = $date[0] . ' ' . $date[1];

        $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $date);
        return $dateTime && $dateTime->format('Y-m-d H:i') === $date;
    }

    private function isExpirationDateValid(string $subtaskExpirationDate, string $taskExpirationDate): bool
    {
        $subtaskDate = explode('T', $subtaskExpirationDate);
        if (count($subtaskDate) != 2) {
            return false;   
        }

        $subtaskDate = $subtaskDate[0] . ' ' . $subtaskDate[1];

        $subtaskDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $subtaskDate);
        $taskDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $taskExpirationDate);

        return $subtaskDateTime <= $taskDateTime;
    }

    private function isPriorityValid(int $priority): bool
    {
        $validPriorities = $this->taskPriorityModel->getIds();
        return in_array($priority, array_column($validPriorities, 'ID_priority'));
    }

    private function isStatValid(int $stat): bool
    {
        $validStats = $this->taskStatusModel->getIds();
        return in_array($stat, array_column($validStats, 'ID_status'));
    }
    
    private function isTaskValid(int $task): bool
    {
        return count($this->taskModel->getById($task)) > 0;
    }

    private function isAssigneeValid(int $assignee): bool
    {
        return count($this->userModel->getById($assignee)) > 0;
    }

    public function validateData($subtaskData, bool $isUpdate = false, $task = null): Response
    {
        if (property_exists($subtaskData, 'description') && !$this->isMaxLengthValid($subtaskData->description, 255)) {
            $this->res->code = 422;
            $this->res->message = "La descripcion es muy larga";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($subtaskData, 'description') && !$this->isMinLengthValid($subtaskData->description, 3)) {
            $this->res->code = 422;
            $this->res->message = "La descripcion es muy corta";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($subtaskData, 'priority') && !$this->isPriorityValid($subtaskData->priority)) {
            $this->res->code = 422;
            $this->res->message = "La prioridad no es valida";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($subtaskData, 'stat') && !$this->isStatValid($subtaskData->stat)) {
            $this->res->code = 422;
            $this->res->message = "El estado no es valido";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($subtaskData, 'expiration_date') && !$this->isDateFormatValid($subtaskData->expiration_date)) {
            $this->res->code = 422;
            $this->res->message = "La fecha de expiracion no es valida";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($subtaskData, 'expiration_date') && !$this->isExpirationDateValid($subtaskData->expiration_date, $task['expiration_date'])) {
            $this->res->code = 422;
            $this->res->message = "La fecha de expiracion debe ser menor o igual a la fecha de expiracion de la tarea principal";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($subtaskData, 'cmt') && !$this->isMaxLengthValid($subtaskData->cmt, 255)) {
            $this->res->code = 422;
            $this->res->message = "El comentario es muy largo";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($subtaskData, 'cmt') && !$this->isMinLengthValid($subtaskData->cmt, 3)) {
            $this->res->code = 422;
            $this->res->message = "El comentario es muy corto";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($subtaskData, 'task') && !$this->isTaskValid($subtaskData->task) && !$isUpdate) {
            $this->res->code = 422;
            $this->res->message = "La tarea no existe";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($subtaskData, 'task') && $isUpdate) {
            unset($subtaskData->owner);

            $this->res->code = 422;
            $this->res->message = "No se puede cambiar la tarea de la subtarea";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($subtaskData, 'assignee') && !$this->isAssigneeValid($subtaskData->assignee)) {
            $this->res->code = 422;
            $this->res->message = "El responsable no existe";
            $this->res->areDataValid = false;

            return $this->res;
        }

        $this->res->areDataValid = boolval(true);

        return $this->res;
    }
}
