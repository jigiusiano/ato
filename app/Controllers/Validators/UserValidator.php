<?php

namespace App\Controllers\Validators;

use App\Models\UserModel;
use App\Utils\Response;

class UserValidator
{
    private $userModel;
    private $res;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->res = new Response();
    }

    private function isMaxLengthValid(string $value, int $maxLength): bool
    {
        return strlen($value) <= $maxLength;
    }

    private function isMinLengthValid(string $value, int $minLength): bool
    {
        return strlen($value) >= $minLength;
    }

    private function isEmailFormatValid(string $email): bool
    {
        return preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $email);
    }

    private function emailLengthIsValid(string $email): bool
    {
        return strlen($email) <= 255;
    }

    private function emailExists(string $email): bool
    {
        return count($this->userModel->getUserByEmail($email)) > 0;
    }

    public function validateData($userData): Response
    {
        if (property_exists($userData, 'name') && !$this->isMaxLengthValid($userData->name, 255)) {
            $this->res->code = 422;
            $this->res->message = "El nombre es muy largo";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($userData, 'name') && !$this->isMinLengthValid($userData->name, 3)) {
            $this->res->code = 422;
            $this->res->message = "El nombre es muy corto";
            $this->res->areDataValid = false;

            return $this->res;;
        }

        if (property_exists($userData, 'surname') && !$this->isMaxLengthValid($userData->surname, 255)) {
            $this->res->code = 422;
            $this->res->message = "El apellido es muy largo";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($userData, 'surname') && !$this->isMinLengthValid($userData->surname, 3)) {
            $this->res->code = 422;
            $this->res->message = "El apellido es muy corto";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($userData, 'email') && !$this->isMaxLengthValid($userData->email, 255)) {
            $this->res->code = 422;
            $this->res->message = "El email es muy largo";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($userData, 'email') && !$this->isMinLengthValid($userData->email, 3)) {
            $this->res->code = 422;
            $this->res->message = "El email es muy corto";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($userData, 'email') && !$this->isEmailFormatValid($userData->email)) {
            $this->res->code = 422;
            $this->res->message = "El email es inválido";
            $this->res->areDataValid = false;

            return $this->res;
        }

        try {
            if (property_exists($userData, 'email') && $this->emailExists($userData->email)) {
                $this->res->code = 422;
                $this->res->message = "El email ya se encuentra registrado";
                $this->res->areDataValid = false;

                return $this->res;
            }
        } catch (\Throwable $th) {
            $this->res->code = 500;
            $this->res->message = "Ocurrio un error al verificar el email";
            $this->res->areDataValid = false;

            return $this->res;
        }


        if ( property_exists($userData, 'password') && !$this->isMaxLengthValid($userData->pass, 255)) {
            $this->res->code = 422;
            $this->res->message = "La contraseña es muy larga";
            $this->res->areDataValid = false;

            return $this->res;
        }

        if (property_exists($userData, 'password') && !$this->isMinLengthValid($userData->pass, 8)) {
            $this->res->code = 422;
            $this->res->message = "La contraseña es muy corta";
            $this->res->areDataValid = false;

            return $this->res;
        }

        $this->res->areDataValid = boolval(true);
        
        return $this->res;
    }
}
