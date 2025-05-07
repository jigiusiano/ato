<?php

namespace App\Controllers\Services;

use App\Controllers\AuthController;
use App\Controllers\CollaboratorController;
use CodeIgniter\RESTful\ResourceController;
use App\Utils\Response;
use App\Utils\Request;

class CollaboratorService extends ResourceController
{
    private AuthController $authController;
    private CollaboratorController $collaboratorController;
    private Request $req;
    private Response $res;

    public function __construct()
    {
        $this->authController = new AuthController();
        $this->collaboratorController = new CollaboratorController();
        $this->req = new Request();
        $this->res = new Response();
    }

    public function index()
    {
        $task = $this->request->getGet('ID_task');

        if ($task) {
            if (!$this->req->isRequestValid("index", $this->request, null, $task)) {
                $this->res->code = 400;
                $this->res->message = "Formato invalido";
    
                return $this->response
                    ->setJSON($this->res)
                    ->setStatusCode($this->res->code);
            }
        }

        $this->res = $this->collaboratorController->index($task);

        return $this->response
            ->setJSON($this->res)
            ->setStatusCode($this->res->code);
    }
}