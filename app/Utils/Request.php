<?php

namespace App\Utils;

use CodeIgniter\HTTP\IncomingRequest as Req;

class Request
{
    public function __construct() {}

    public function isRequestValid(string $operation, Req $request, array|null $requiredProperties = null, int|null $id = null, array|null $optionalProperties = []): bool
    {
        try {
            $data = $request->getJSON(true);
        } catch (\Throwable $th) {
            return false;
        }

        switch ($operation) {
            case 'login':
                foreach ($requiredProperties as $property) {
                    if (!array_key_exists($property, $data)) {
                        return false;
                    }
                }
                break;
            case 'index':
            case 'show':
                if (!is_int($id)) {
                    return false;
                }
                break;
            case 'create':
                $allowedProperties = array_merge($requiredProperties, $optionalProperties);

                foreach ($data as $property => $value) {
                    if (!in_array($property, $allowedProperties, true)) {
                        return false;
                    }
                }
                break;
            case 'update':

                if (!is_int($id)) {
                    return false;
                }

                $allowedProperties = array_merge($requiredProperties, $optionalProperties);

                foreach ($data as $property => $value) {
                    if (!in_array($property, $allowedProperties, true)) {
                        return false;
                    }
                }
                break;
            case 'delete':
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
