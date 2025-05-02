<?php

namespace App\Controllers\Services;

use App\Controllers\AuthController;
use CodeIgniter\RESTful\ResourceController;
use App\Controllers\UserController;
use App\Utils\Response;
use App\Utils\Request;

class UserService extends ResourceController
{
    private AuthController $authController;
    private UserController $userController;
    private Request $req;
    private Response $res;

    public function __construct()
    {
        $this->authController = new AuthController();
        $this->userController = new UserController();
        $this->req = new Request();
        $this->res = new Response();
    }

    // public function index()
    // {
    //     return $this->respond($this->userController->getAll());
    // }

    public function show($id = null)
    {
        if (!$this->req->isRequestValid("show", $this->request, null, $id)) {
            $this->res->code = 400;
            $this->res->message = "Formato invalido";

            return $this->response
                ->setJSON($this->res)
                ->setStatusCode($this->res->code);
        }

        $this->res = $this->userController->show($id);

        return $this->response
            ->setJSON($this->res)
            ->setStatusCode($this->res->code);
    }

    public function create()
    {
        $requiredProperties = ['name', 'surname', 'email', 'pass'];
        if (!$this->req->isRequestValid("create", $this->request, $requiredProperties)) {
            $this->res->code = 400;
            $this->res->message = "Formato invalido";

            return $this->response
                ->setJSON($this->res->data)
                ->setStatusCode($this->res->code);
        }

        $userData = json_decode(json_encode($this->request->getJSON(true)));
        $this->res = $this->userController->create($userData);

        if ($this->res->code != 201) {
            return $this->response
                ->setJSON($this->res->data)
                ->setStatusCode($this->res->code);
        }

        $this->res = $this->userController->showByEmail($userData->email);

        if ($this->res->code != 200) {
            return $this->response
                ->setJSON($this->res->data)
                ->setStatusCode($this->res->code)
                ->setHeader('location', base_url('/'));
        }

        $this->res = $this->authController->register($this->res->data);

        $cookie = $this->res->cookie;
        unset($this->res->cookie);
        
        return $this->response
            ->setJSON($this->res)
            ->setStatusCode($this->res->code)
            ->setCookie($cookie)
            ->setHeader('location', base_url('/workspace'));
    }

    public function update($id = null)
    {
        if (!$this->req->isRequestValid("update", $this->request, ['name', 'surname', 'email', 'pass'], $id)) {
            $this->res->code = 400;
            $this->res->message = "Formato invalido";

            return $this->response
                ->setJSON($this->res)
                ->setStatusCode($this->res->code);
        }

        $userData = json_decode(json_encode($this->request->getJSON(true)));
        $this->res = $this->userController->update($id, $userData);

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

        $this->res = $this->userController->delete($id);

        return $this->response
            ->setJSON($this->res)
            ->setStatusCode($this->res->code);
    }
}
