<?php

    namespace App\Controllers\Services;
    
    use CodeIgniter\RESTful\ResourceController;
    use App\Controllers\UserController;
    use App\Utils\Response;
    use App\Utils\Request;
    
    class UserService extends ResourceController
    {
        //protected $format = 'json';
        private UserController $userController;
        private Request $req;
        private Response $res;
    
        public function __construct()
        {
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
                    ->setJSON($this->res)
                    ->setStatusCode($this->res->code);
            }
    
            $userData = json_decode(json_encode($this->request->getJSON(true)));
            $this->res = $this->userController->create($userData);
    
            return $this->response
                ->setJSON($this->res)
                ->setStatusCode($this->res->code);
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
    