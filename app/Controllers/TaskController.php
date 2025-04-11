<?php

namespace App\Controllers;

use App\Models\TaskModel;
use App\Utils\Response;
use App\Controllers\Validators\TaskValidator;

class TaskController
{
    private TaskModel $taskModel;
    private TaskValidator $taskValidator;
    private Response $res;

    public function __construct()
    {
        $this->taskModel = new TaskModel();
        $this->taskValidator = new TaskValidator();
        $this->res = new Response();
    }

    public function getAllByIDUser($owner): Response
    {
        try {
            $tasks = $this->taskModel->getAllByIDUser($owner);

            if (count($tasks) == 0) {
                $this->res->code = 404;
                $this->res->message = "No hay tareas registradas para este usuario";

                return $this->res;
            }

            $this->res->code = 200;
            $this->res->message = "Las tareas fueron encontradas con exito";
            $this->res->data = $tasks;

            return $this->res;
        } catch (\Throwable $th) {
            echo $th->getMessage();
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al buscar las tareas";

            return $this->res;
        }
    }

    public function index(): Response
    {
        try {
            $tasks = $this->taskModel->getAll();

            if (count($tasks) == 0) {
                $this->res->code = 404;
                $this->res->message = "No hay tareas registradas";

                return $this->res;
            }

            $this->res->code = 200;
            $this->res->message = "Las tareas fueron encontradas con exito";
            $this->res->data = $tasks;

            return $this->res;
        } catch (\Throwable $th) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al buscar las tareas";

            return $this->res;
        }
    }

    public function show($id): Response
    {
        try {
            $task = $this->taskModel->getById($id);

            if (count($task) == 0) {
                $this->res->code = 404;
                $this->res->message = "La tarea no existe";

                return $this->res;
            }

            $this->res->code = 200;
            $this->res->message = "La tarea fue encontrada con exito";
            $this->res->data = $task;

            return $this->res;
        } catch (\Throwable $th) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al buscar la tarea";

            return $this->res;
        }
    }

    public function create($taskData): Response
    {
        $this->res = $this->taskValidator->validateData($taskData);

        // Si la validación falla, se devuelve el error
        if (!$this->res->areDataValid) {
            return $this->res;
        }

        try {
            $this->taskModel->create($taskData);

            $this->res->code = 201;
            $this->res->message = "La tarea fue creada con exito";

            return $this->res;
        } catch (\Throwable $th) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al crear la tarea";

            return $this->res;
        }
    }

    public function update($id, $taskData): Response
    {
        try {
            $this->res = $this->taskValidator->validateData($taskData, true);

            // Si la validación falla, se devuelve el error
            if (!$this->res->areDataValid) {
                return $this->res;
            }

            $this->taskModel->updateById(
                $id,
                $taskData
            );

            $this->res->code = 200;
            $this->res->message = "La tarea fue actualizada con exito";

            return $this->res;
        } catch (\Throwable $th) {
            echo $th->getMessage();
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al actualizar la tarea";

            return $this->res;
        }
    }

    public function delete($id): Response
    {
        try {
            $task = $this->taskModel->getById($id);

            if (count($task) == 0) {
                $this->res->code = 404;
                $this->res->message = "La tarea no existe";

                return $this->res;
            }

            $this->taskModel->deleteById(
                $id
            );

            $this->res->code = 200;
            $this->res->message = "La tarea se eliminó con exito";

            return $this->res;
        } catch (\Throwable $th) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al buscar la tarea";

            return $this->res;
        }
    }
}
