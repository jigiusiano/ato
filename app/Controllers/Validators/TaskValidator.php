<?php

namespace App\Controllers\Validators;

use App\Models\TaskModel;
use App\Models\TaskPriorityModel;
use App\Models\TaskStatusModel;
use App\Models\UserModel;
use App\Utils\Response;

class TaskValidator
{
    private TaskModel $taskModel;
    private TaskPriorityModel $taskPriorityModel;
    private TaskStatusModel $taskStatusModel;
    private UserModel $userModel;
    private Response $res;

    public function __construct()
    {
        $this->taskModel = new TaskModel();
        $this->taskPriorityModel = new TaskPriorityModel();
        $this->taskStatusModel = new TaskStatusModel();
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

    private function isReminderDateValid(string $expiration_date, string $reminder_date): bool
    {

        $expirationDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $expiration_date);
        $reminderDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $reminder_date);

        return $reminderDateTime <= $expirationDateTime;
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

    private function isColorValid(string $color): bool
    {
        return preg_match("/^#[0-9A-Fa-f]{6}$/", $color);
    }

    private function isOwnerValid(int $owner): bool
    {
        return count($this->userModel->getById($owner)) > 0;
    }

    private function isArchivedValid(bool $archived): bool
    {
        return in_array($archived, [0, 1]);
    }

    public function validateData($taskData, bool $isUpdate = false): Response
    {
        if (property_exists($taskData, 'subject') && !$this->isMaxLengthValid($taskData->subject, 255)) {
            $this->res->code = 422;
            $this->res->message = "El asunto es muy largo";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($taskData, 'subject') && !$this->isMinLengthValid($taskData->subject, 3)) {
            $this->res->code = 422;
            $this->res->message = "El asunto es muy corto";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($taskData, 'description') && !$this->isMaxLengthValid($taskData->description, 255)) {
            $this->res->code = 422;
            $this->res->message = "La descripcion es muy larga";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($taskData, 'description') && !$this->isMinLengthValid($taskData->description, 3)) {
            $this->res->code = 422;
            $this->res->message = "La descripcion es muy corta";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($taskData, 'priority') && !$this->isPriorityValid($taskData->priority)) {
            $this->res->code = 422;
            $this->res->message = "La prioridad no es valida";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($taskData, 'stat') && !$this->isStatValid($taskData->stat)) {
            $this->res->code = 422;
            $this->res->message = "El estado no es valido";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($taskData, 'expiration_date') && !$this->isDateFormatValid($taskData->expiration_date)) {
            $this->res->code = 422;
            $this->res->message = "La fecha de expiracion no es valida";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($taskData, 'reminder_date') && !$this->isDateFormatValid($taskData->reminder_date)) {
            $this->res->code = 422;
            $this->res->message = "La fecha de recordatorio no es valida";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($taskData, 'reminder_date') && !$this->isReminderDateValid($taskData->expiration_date, $taskData->reminder_date)) {
            $this->res->code = 422;
            $this->res->message = "La fecha de recordatorio no puede ser mayor a la fecha de expiracion";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($taskData, 'color') && !$this->isColorValid($taskData->color)) {
            $this->res->code = 422;
            $this->res->message = "El color no es valido";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($taskData, 'owner') && !$this->isOwnerValid($taskData->owner) && !$isUpdate) {
            $this->res->code = 422;
            $this->res->message = "El dueño no existe";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($taskData, 'owner') && $isUpdate) {
            unset($taskData->owner);

            $this->res->code = 422;
            $this->res->message = "No se puede cambiar el dueño";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($taskData, 'archived') && !$this->isArchivedValid($taskData->archived)) {
            $this->res->code = 422;
            $this->res->message = "La propiedad archived no es valida";
            $this->res->areDataValid = false;

            return $this->res;
        }

        $this->res->areDataValid = boolval(true);

        return $this->res;
    }
}
