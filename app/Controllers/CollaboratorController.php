<?php

namespace App\Controllers;

use App\Models\CollaboratorModel;
use App\Models\TaskModel;
use App\Models\UserModel;
use App\Utils\Response;

class CollaboratorController
{
    private CollaboratorModel $collaboratorModel;
    private UserModel $userModel;
    private TaskModel $taskModel;
    private Response $res;

    public function __construct()
    {
        $this->collaboratorModel = new CollaboratorModel();
        $this->userModel = new UserModel();
        $this->taskModel = new TaskModel();
        $this->res = new Response();
    }

    public function index($id): Response
    {
        try {
            $task = $this->taskModel->getById($id);

            if (count($task) == 0) {
                $this->res->code = 404;
                $this->res->message = "La tarea no existe";

                return $this->res;
            }

            $owner = $this->userModel->getById($task[0]['owner']);

            if (count($owner) == 0) {
                $this->res->code = 404;
                $this->res->message = "El propietario de la tarea no existe";

                return $this->res;
            }

            $collaborators = [];
            $collaboratorIds = $this->collaboratorModel->getByTask($id);

            foreach ($collaboratorIds as $collaboratorId) {
                $collaborator = $this->userModel->getById($collaboratorId['collaborator']);

                if (count($collaborator) == 1) {
                    array_push($collaborators, $collaborator[0]);
                }
            }

            array_push($collaborators, $owner[0]);

            $this->res->code = 200;
            $this->res->message = "Los colaboradores fueron encontrados con exito";
            $this->res->data = $collaborators;

            return $this->res;
        } catch (\Throwable $th) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al encontrar los colaboradores";

            return $this->res;
        }
    }
}
