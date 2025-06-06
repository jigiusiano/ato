<?php

namespace App\Controllers\Services;

use CodeIgniter\RESTful\ResourceController;
use App\Controllers\TaskController;
use App\Utils\Response;
use App\Utils\Request;

class TaskService extends ResourceController
{
    private TaskController $taskController;
    private Request $req;
    private Response $res;

    public function __construct()
    {
        $this->taskController = new TaskController();
        $this->req = new Request();
        $this->res = new Response();
    }

    public function index()
    {
        $user = $this->request->getGet('user');
        $archived = $this->request->getGet('archived') ?? false;
        $reminders = $this->request->getGet('reminders') === 'true';

        if ($archived == "true") {
            $archived = 1;
        } else {
            $archived = 0;
        }

        if (!$this->req->isRequestValid("index", $this->request, null, $user)) {
            $this->res->code = 400;
            $this->res->message = "Formato inválido";
            return $this->response
                ->setJSON($this->res)
                ->setStatusCode($this->res->code);
        }

        $this->res = $this->taskController->getAllByIDUser($user, $archived, $reminders);

        return $this->response
            ->setJSON($this->res)
            ->setStatusCode($this->res->code);
    }

    public function show($id = null)
    {
        if (!$this->req->isRequestValid("show", $this->request, null, $id)) {
            $this->res->code = 400;
            $this->res->message = "Formato invalido";

            return $this->response
                ->setJSON($this->res)
                ->setStatusCode($this->res->code);
        }

        $this->res = $this->taskController->show($id);

        return $this->response
            ->setJSON($this->res)
            ->setStatusCode($this->res->code);
    }

    public function create()
    {

        $requiredProperties = ['subject', 'description', 'priority', 'expiration_date', 'color', 'owner'];
        if (!$this->req->isRequestValid("create", $this->request, $requiredProperties)) {
            $this->res->code = 400;
            $this->res->message = "Formato invalido";

            return $this->response
                ->setJSON($this->res)
                ->setStatusCode($this->res->code);
        }

        $userData = json_decode(json_encode($this->request->getJSON(true)));
        $this->res = $this->taskController->create($userData);

        return $this->response
            ->setJSON($this->res)
            ->setStatusCode($this->res->code);
    }

    public function update($id = null)
    {
        if (!$this->req->isRequestValid("update", $this->request, ['subject', 'description', 'priority', 'expiration_date', 'color'], $id, ['reminder_date', 'archived'])) {
            $this->res->code = 400;
            $this->res->message = "Formato invalido";

            return $this->response
                ->setJSON($this->res)
                ->setStatusCode($this->res->code);
        }

        $taskData = json_decode(json_encode($this->request->getJSON(true)));
        $this->res = $this->taskController->update($id, $taskData);

        return $this->response
            ->setJSON($this->res, true)
            ->setStatusCode($this->res->code);
    }

    public function delete($id = null)
    {
        if (!$this->req->isRequestValid("delete", $this->request, null, $id)) {
            $this->res->code = 400;
            $this->res->message = "Formato invalido";

            return $this->response
                ->setJSON($this->res)
                ->setStatusCode($this->res->code);
        }

        $this->res = $this->taskController->delete($id);

        return $this->response
            ->setJSON($this->res)
            ->setStatusCode($this->res->code);
    }
}
