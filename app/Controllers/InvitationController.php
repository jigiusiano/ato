<?php

namespace App\Controllers;

use App\Models\InvitationModel;
use App\Utils\Response;
use App\Controllers\Validators\InvitationValidator;
use App\Models\UserModel;

class InvitationController
{
    private InvitationModel $invitationModel;
    private InvitationValidator $invitationValidator;
    private UserModel $userModel;
    private Response $res;

    public function __construct()
    {
        $this->invitationModel = new InvitationModel();
        $this->invitationValidator = new InvitationValidator();
        $this->userModel = new UserModel();
        $this->res = new Response();
    }

    public function create($invitationData): Response
    {
        $this->res = $this->invitationValidator->validateData($invitationData);

        // Si la validación falla, se devuelve el error
        if (!$this->res->areDataValid) {
            return $this->res;
        }

        try {
            $userData = $this->userModel->getUserByEmail($invitationData->recipient);
        } catch (\Throwable $th) {
            echo $th;
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al realizar la invitación";

            return $this->res;
        }

        try {
            $this->invitationModel->create(
                $userData[0]["ID_user"],
                $invitationData->task
            );

            $this->res->code = 201;
            $this->res->message = "La invitación se ha hecho con éxito";

            return $this->res;
        } catch (\Throwable $th) {
            echo $th;
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al realizar la invitación";

            return $this->res;
        }
    }

    public function update(string $id, string $stat): Response
    {
        try {
            $invitation = $this->invitationModel->getById($id);

            if (count($invitation) == 0) {
                $this->res->code = 404;
                $this->res->message = "La invitación no existe";

                return $this->res;
            }

            $this->res = $this->invitationValidator->validateData($stat);

            // Si la validación falla, se devuelve el error
            if (!$this->res->areDataValid) {
                return $this->res;
            }

            try {
                $this->invitationModel->processInvitation($id, $stat);

                $this->res->code = 200;
                $this->res->message = "Operación exitosa";

                return $this->res;
            } catch (\Throwable $th) {
                echo $th;
                $this->res->code = 500;
                $this->res->message = "Ocurrio un error al actualizar el estado de la invitación";

                return $this->res;
            }
        } catch (\Throwable $th) {
            echo $th;
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al buscar el usuario";

            return $this->res;
        }
    }
}
