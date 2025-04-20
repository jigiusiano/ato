<?php

    namespace App\Controllers\Services;
    
    use CodeIgniter\RESTful\ResourceController;
    use App\Controllers\InvitationController;
    use App\Utils\Response;
    use App\Utils\Request;
    
    class InvitationService extends ResourceController
    {
        private InvitationController $invitationController;
        private Request $req;
        private Response $res;
    
        public function __construct()
        {
            $this->invitationController = new InvitationController();
            $this->req = new Request();
            $this->res = new Response();
        }
    
        public function create()
        {    
            $requiredProperties = ['recipient', 'task'];

            if (!$this->req->isRequestValid("create", $this->request, $requiredProperties)) {
                $this->res->code = 400;
                $this->res->message = "Formato invalido";
    
                return $this->response
                    ->setJSON($this->res)
                    ->setStatusCode($this->res->code);
            }
    
            $invitationData = json_decode(json_encode($this->request->getJSON(true)));
            $this->res = $this->invitationController->create($invitationData);
    
            return $this->response
                ->setJSON($this->res)
                ->setStatusCode($this->res->code);
        }

        public function update($id = null)
        {
            $stat = $this->request->getGet('stat');

            if (!$this->req->isRequestValid("update", $this->request, [], $id)) {
                $this->res->code = 400;
                $this->res->message = "Formato invalido";
    
                return $this->response
                    ->setJSON($this->res)
                    ->setStatusCode($this->res->code);
            }

            $this->res = $this->invitationController->update($id, $stat);
    
            return $this->response
                ->setJSON($this->res, true)
                ->setStatusCode($this->res->code);
        }
    }
    