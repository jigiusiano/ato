<?php

namespace App\Utils;

use CodeIgniter\HTTP\IncomingRequest as Req;

class Request
{
    public function __construct() {}

    public function isRequestValid(string $operation, Req $request, array|null $requiredProperties = null, int|null $id = null): bool
    {
        // Verifico que sea un json
        try {
            $data = $request->getJSON(true);
        } catch (\Throwable $th) {
            return false;
        }

        switch ($operation) {
            case 'index':
                # code...
                break;
            case 'show':
                // Verifico que el id sea un numero y entero
                if (!(is_numeric($id) && is_int($id))) {
                    return false;
                }
                break;
            case 'create':
                // Verifico que tenga todas las propiedades requeridas en el request
                foreach ($requiredProperties as $property) {
                    if (!array_key_exists($property, $data)) {
                        return false;
                    }
                }
                break;
            case 'update':
                $foundProperty = false;

                // Verifico que el id sea un numero y entero
                if (!(is_numeric($id) && is_int($id))) {
                    return false;
                }

                foreach ($data as $property => $value) {
                    if (!in_array($property, $requiredProperties)) {
                        return false;
                    }
                }

                foreach ($requiredProperties as $property) {
                    if (array_key_exists($property, $data)) {
                        $foundProperty = true;
                        break;
                    }
                }

                if (!$foundProperty) {
                    return false;
                }
                break;
            case 'delete':
                // Verifico que el id sea un numero y entero
                if (!(is_numeric($id) && is_int($id))) {
                    return false;
                }
                break;
            default:
                break;
        }

        return true;
    }
}
