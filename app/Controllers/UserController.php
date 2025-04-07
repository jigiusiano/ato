<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Utils\Response;
use App\Controllers\Validators\UserValidator;

class UserController
{
    private UserModel $userModel;
    private UserValidator $userValidator;
    private Response $res;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userValidator = new UserValidator();
        $this->res = new Response();
    }

    public function show($id): Response
    {
        try {
            $user = $this->userModel->getById($id);

            if (count($user) == 0) {
                $this->res->code = 404;
                $this->res->message = "El usuario no existe";

                return $this->res;
            }

            $this->res->code = 200;
            $this->res->message = "El usuario fue encontrado con exito";
            $this->res->data = $user;

            return $this->res;
        } catch (\Throwable $th) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al buscar el usuario";

            return $this->res;
        }
    }

    public function create($userData): Response
    {
        $this->res = $this->userValidator->validateData($userData);

        // Si la validación falla, se devuelve el error
        if (!$this->res->areDataValid) {
            return $this->res;
        }

        try {
            $this->userModel->create(
                $userData->name,
                $userData->surname,
                $userData->email,
                $userData->pass
            );

            $this->res->code = 201;
            $this->res->message = "El usuario se creo con éxito";

            return $this->res;
        } catch (\Throwable $th) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al crear el usuario";

            return $this->res;
        }
    }

    public function update($id, $userData): Response
    {
        try {
            $user = $this->userModel->getById($id);

            if (count($user) == 0) {
                $this->res->code = 404;
                $this->res->message = "El usuario no existe";

                return $this->res;
            }

            $this->res = $this->userValidator->validateData($userData);

            // Si la validación falla, se devuelve el error
            if (!$this->res->areDataValid) {
                return $this->res;
            }

            try {
                $this->userModel->updateById(
                    $id,
                    $userData
                );

                $this->res->code = 200;
                $this->res->message = "El usuario se actualió con exito";

                return $this->res;
            } catch (\Throwable $th) {
                echo $th;
                $this->res->code = 500;
                $this->res->message = "Ocurrio un error al actualizar el usuario";

                return $this->res;
            }
        } catch (\Throwable $th) {
            echo $th;
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al buscar el usuario";

            return $this->res;
        }
    }

    public function delete($id): Response
    {
        try {
            $user = $this->userModel->getById($id);

            if (count($user) == 0) {
                $this->res->code = 404;
                $this->res->message = "El usuario no existe";

                return $this->res;
            }

            $this->userModel->deleteById(
                $id
            );

            $this->res->code = 200;
            $this->res->message = "El usuario se eliminó con exito";

            return $this->res;
        } catch (\Throwable $th) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al buscar el usuario";

            return $this->res;
        }
    }
}
