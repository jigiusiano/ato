<?php

namespace App\Controllers\Services;

use App\Controllers\AuthController;
use CodeIgniter\RESTful\ResourceController;
use App\Utils\Response;
use App\Utils\Request;

class AuthService extends ResourceController
{
    private AuthController $authController;
    private Request $req;
    private Response $res;

    public function __construct()
    {
        $this->authController = new AuthController();
        $this->req = new Request();
        $this->res = new Response();
    }

    public function login()
    {
        $requiredProperties = ['email', 'pass'];
        if (!$this->req->isRequestValid("login", $this->request, $requiredProperties)) {
            $this->res->code = 400;
            $this->res->message = "Formato invalido";

            return $this->response
                ->setJSON($this->res)
                ->setStatusCode($this->res->code);
        }

        $userData = json_decode(json_encode($this->request->getJSON(true)));
        $this->res = $this->authController->login($userData);

        if (isset($this->res->cookie)) {
            $cookie = $this->res->cookie;
            unset($this->res->cookie);
            
            return $this->response
                ->setJSON($this->res)
                ->setStatusCode($this->res->code)
                ->setcookie($cookie);
        } else {
            return $this->response
                ->setJSON($this->res)
                ->setStatusCode($this->res->code);
        }
    }

    public function logout()
    {
        $this->res = $this->authController->logout();

        if (isset($this->res->cookie)) {
            $cookie = $this->res->cookie;
            $this->res->cookie = null;

            return $this->response
                ->setJSON($this->res)
                ->setStatusCode($this->res->code)
                ->setcookie($cookie);
        } else {
            return $this->response
                ->setJSON($this->res)
                ->setStatusCode($this->res->code);
        }
    }
}
