<?php

namespace App\Controllers;

use App\Models\TaskModel;
use App\Models\SubtaskModel;
use App\Utils\Response;
use App\Controllers\Validators\SubtaskValidator;
use App\Models\UserModel;

class SubtaskController
{
    private TaskModel $taskModel;
    private SubtaskModel $subtaskModel;
    private SubtaskValidator $subtaskValidator;
    private UserModel $userModel;
    private Response $res;

    public function __construct()
    {
        $this->taskModel = new TaskModel();
        $this->subtaskModel = new SubtaskModel();
        $this->subtaskValidator = new SubtaskValidator();
        $this->userModel = new UserModel();
        $this->res = new Response();
    }

    public function getAllByIDTask($task): Response
    {
        try {
            $subtasks = $this->subtaskModel->getAllByIDTask($task);

            if (count($subtasks) == 0) {
                $this->res->code = 404;
                $this->res->message = "No hay subtareas registradas para esta tarea";

                return $this->res;
            }

            for ($i = 0; $i < count($subtasks); $i++) {
                $userData = $this->userModel->getById($subtasks[$i]["assignee"])[0];

                if (count($userData) == 0) {
                    throw new \Exception("El usuario asignado a la subtarea no existe");
                }

                $subtasks[$i]["assignee"] = [
                    "id" => $userData["ID_user"],
                    "name" => $userData["name"] . " " . $userData["surname"]
                ];
            }

            $this->res->code = 200;
            $this->res->message = "Las subtareas fueron encontradas con exito";
            $this->res->data = $subtasks;

            return $this->res;
        } catch (\Throwable $th) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al buscar las subtareas";

            return $this->res;
        }
    }

    public function index(): Response
    {
        try {
            $tasks = $this->subtaskModel->getAll();

            if (count($tasks) == 0) {
                $this->res->code = 200;
                $this->res->data = [];
                $this->res->message = "No hay subtareas registradas";

                return $this->res;
            }

            $this->res->code = 200;
            $this->res->message = "Las subtareas fueron encontradas con exito";
            $this->res->data = $tasks;

            return $this->res;
        } catch (\Throwable $th) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al buscar las subtareas";

            return $this->res;
        }
    }

    public function show($id): Response
    {
        try {
            $subtask = $this->subtaskModel->getById($id);

            if (count($subtask) == 0) {
                $this->res->code = 404;
                $this->res->message = "La subtarea no existe";

                return $this->res;
            }

            $this->res->code = 200;
            $this->res->message = "La subtarea fue encontrada con exito";
            $this->res->data = $subtask;

            return $this->res;
        } catch (\Throwable $th) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al buscar la subtarea";

            return $this->res;
        }
    }

    public function create($subtaskData): Response
    {
        try {
            $task = $this->taskModel->getById($subtaskData->task);

            if (count($task) == 0) {
                $this->res->code = 404;
                $this->res->message = "La tarea principal no existe";

                return $this->res;
            }
        } catch (\Throwable $th) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al encontrar la tarea principal";

            return $this->res;
        }

        $this->res = $this->subtaskValidator->validateData($subtaskData, false, $task[0]);

        if (!$this->res->areDataValid) {
            return $this->res;
        }

        try {
            $this->subtaskModel->create($subtaskData);

            $this->res->code = 201;
            $this->res->message = "La subtarea fue creada con exito";

            return $this->res;
        } catch (\Throwable $th) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al crear la subtarea";

            return $this->res;
        }
    }

    public function update($id, $subtaskData): Response
    {
        try {
            $subtask = $this->subtaskModel->getById($id);

            if (count($subtask) == 0) {
                $this->res->code = 404;
                $this->res->message = "La subtarea no existe";

                return $this->res;
            }

            try {
                $task = $this->taskModel->getById($subtask[0]["task"]);
    
                if (count($task) == 0) {
                    $this->res->code = 404;
                    $this->res->message = "La tarea principal no existe";
    
                    return $this->res;
                }
            } catch (\Throwable $th) {
                $this->res->code = 500;
                $this->res->message = "Ocurrio un error al encontrar la tarea principal";
    
                return $this->res;
            }
        } catch (\Throwable $th) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al encontrar la subtarea";

            return $this->res;
        }

        try {
            $this->res = $this->subtaskValidator->validateData($subtaskData, true, $task[0]);

            if (!$this->res->areDataValid) {
                return $this->res;
            }

            $this->subtaskModel->updateById(
                $id,
                $subtaskData
            );

            $this->res->code = 200;
            $this->res->message = "La subtarea fue actualizada con exito";

            return $this->res;
        } catch (\Throwable $th) {
            echo $th->getMessage();
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al actualizar la subtarea";

            return $this->res;
        }
    }

    public function delete($id): Response
    {
        try {
            $subtask = $this->subtaskModel->getById($id);

            if (count($subtask) == 0) {
                $this->res->code = 404;
                $this->res->message = "La subtarea no existe";

                return $this->res;
            }

            $this->subtaskModel->deleteById(
                $id
            );

            $this->res->code = 200;
            $this->res->message = "La subtarea se eliminó con exito";

            return $this->res;
        } catch (\Throwable $th) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al buscar la subtarea";

            return $this->res;
        }
    }
}
