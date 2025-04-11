<?php

namespace App\Controllers\Services;

use CodeIgniter\RESTful\ResourceController;
use App\Controllers\SubtaskController;
use App\Utils\Response;
use App\Utils\Request;

class SubtaskService extends ResourceController
{
    private SubtaskController $subtaskController;
    private Request $req;
    private Response $res;

    public function __construct()
    {
        $this->subtaskController = new SubtaskController();
        $this->req = new Request();
        $this->res = new Response();
    }

    public function index()
    {
        $task = $this->request->getGet('task');

        if ($task) {
            if (!$this->req->isRequestValid("index", $this->request, null, $task)) {
                $this->res->code = 400;
                $this->res->message = "Formato invalido";

                return $this->response
                    ->setJSON($this->res)
                    ->setStatusCode($this->res->code);
            }

            $this->res = $this->subtaskController->getAllByIDTask($task);

            return $this->response
                ->setJSON($this->res)
                ->setStatusCode($this->res->code);
        }

        return $this->respond($this->subtaskController->index());
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

        $this->res = $this->subtaskController->show($id);

        return $this->response
            ->setJSON($this->res)
            ->setStatusCode($this->res->code);
    }

    public function create()
    {
        $requiredProperties = ['description', 'stat', 'task', 'asignee'];
        if (!$this->req->isRequestValid("create", $this->request, $requiredProperties)) {
            $this->res->code = 400;
            $this->res->message = "Formato invalido";

            return $this->response
                ->setJSON($this->res)
                ->setStatusCode($this->res->code);
        }

        $userData = json_decode(json_encode($this->request->getJSON(true)));
        $this->res = $this->subtaskController->create($userData);

        return $this->response
            ->setJSON($this->res)
            ->setStatusCode($this->res->code);
    }

    public function update($id = null)
    {
        if (!$this->req->isRequestValid("update", $this->request, ['description', 'stat', 'priority', 'expiration_date', 'cmt', 'task', 'asignee'], $id)) {
            $this->res->code = 400;
            $this->res->message = "Formato invalido";

            return $this->response
                ->setJSON($this->res)
                ->setStatusCode($this->res->code);
        }

        $taskData = json_decode(json_encode($this->request->getJSON(true)));
        $this->res = $this->subtaskController->update($id, $taskData);

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

        $this->res = $this->subtaskController->delete($id);

        return $this->response
            ->setJSON($this->res)
            ->setStatusCode($this->res->code);
    }
}
