<?php

namespace App\Controllers;

use App\Models\SubtaskModel;
use App\Utils\Response;
use App\Controllers\Validators\SubtaskValidator;

class SubtaskController
{
    private SubtaskModel $subtaskModel;
    private SubtaskValidator $subtaskValidator;
    private Response $res;

    public function __construct()
    {
        $this->subtaskModel = new SubtaskModel();
        $this->subtaskValidator = new SubtaskValidator();
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

            $this->res->code = 200;
            $this->res->message = "Las subtareas fueron encontradas con exito";
            $this->res->data = $subtasks;

            return $this->res;
        } catch (\Throwable $th) {
            echo $th->getMessage();
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
                $this->res->code = 404;
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
        $this->res = $this->subtaskValidator->validateData($subtaskData);

        // Si la validación falla, se devuelve el error
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
            $this->res = $this->subtaskValidator->validateData($subtaskData, true);

            // Si la validación falla, se devuelve el error
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
