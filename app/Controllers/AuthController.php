<?php

namespace App\Controllers;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

use App\Models\UserModel;
use App\Utils\Response;

class AuthController extends ResourceController implements FilterInterface
{
    private UserModel $userModel;
    private Response $res;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->res = new Response();
    }

    public function register($userData): Response
    {
        $key = getenv('JWT_SECRET');
        $payload = [
            'iat' => time(),
            'exp' => time() + (int) getenv('JWT_TIME_TO_LIVE'), // Expiración
            'data' => [
                'id' => $userData["ID_user"],
                'email' => $userData["email"]
            ]
        ];

        $token = JWT::encode($payload, $key, 'HS256');

        // Configurar cookie segura
        $cookie = [
            'name' => 'jwt_token',
            'value' => $token,
            'expire' => (int) getenv('JWT_TIME_TO_LIVE'),
            'domain' => '',
            'path' => '/',
            'secure' => true, // Solo HTTPS
            'httponly' => true, // No accesible desde JavaScript
            'samesite' => 'Strict' // Protección contra CSRF
        ];

        $this->res->code = 201;
        $this->res->message = "Login exitoso";
        $this->res->data = [
            'user' => [
                'id' => $userData["ID_user"],
            ],
        ];
        $this->res->cookie = $cookie;

        return $this->res;
    }

    public function login($userData)
    {
        $user = $this->userModel->getUserByEmail($userData->email);
        if (count($user) == 0 || !password_verify($userData->pass, $user[0]['pass'])) {
            $this->res->code = 401;
            $this->res->message = "Credenciales inválidas";

            return $this->res;
        }

        $key = getenv('JWT_SECRET');
        $payload = [
            'iat' => time(),
            'exp' => time() + (int) getenv('JWT_TIME_TO_LIVE'), // Expiración
            'data' => [
                'id' => $user[0]['ID_user'],
                'email' => $user[0]['email']
            ]
        ];

        $token = JWT::encode($payload, $key, 'HS256');

        // Configurar cookie segura
        $cookie = [
            'name' => 'jwt_token',
            'value' => $token,
            'expire' => (int) getenv('JWT_TIME_TO_LIVE'),
            'domain' => '',
            'path' => '/',
            'secure' => true, // Solo HTTPS
            'httponly' => true, // No accesible desde JavaScript
            'samesite' => 'Strict' // Protección contra CSRF
        ];

        $this->res->code = 200;
        $this->res->message = "Login exitoso";
        $this->res->data = [
            'user' => [
                'id' => $user[0]['ID_user'],
            ],
        ];
        $this->res->cookie = $cookie;

        return $this->res;
    }

    public function logout()
    {
        $cookie = [
            'name' => 'jwt_token',
            'value' => '',
            'domain' => '',
            'path' => '/',
            'secure' => true, // Solo HTTPS
            'httponly' => true, // No accesible desde JavaScript
            'samesite' => 'Strict' // Protección contra CSRF
        ];

        $this->res->code = 200;
        $this->res->message = "Logout exitoso";
        $this->res->cookie = $cookie;

        return $this->res;
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        helper('cookie');

        $token = get_cookie('jwt_token');

        if (!$token) {
            $this->res->code = 401;
            $this->res->message = "Acceso no autorizado.";
            
            return service('response')
                ->setJSON($this->res)
                ->setStatusCode($this->res->code);
        }

        try {
            $key = getenv('JWT_SECRET');
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            $request->user = $decoded->data;
        } catch (Exception $e) {
            $this->res->code = 401;
            $this->res->message = "Acceso no autorizado..";
            
            return service('response')
                ->setJSON($this->res)
                ->setStatusCode($this->res->code);
        }

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}