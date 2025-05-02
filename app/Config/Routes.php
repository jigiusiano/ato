<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'ViewController::login');
$routes->get('/login', 'ViewController::login');
$routes->get('/register', 'ViewController::register');
$routes->get('/workspace', 'ViewController::workspace');
$routes->get('/profile', 'ViewController::profile');

$routes->post('/auth', 'Services\AuthService::login');
$routes->post('/deauth', 'Services\AuthService::logout');     
$routes->get('/users/(:num)', 'Services\UserService::show/$1', ['filter' => 'auth']);     // Obtener un usuario por ID (GET /users/5)
$routes->post('/users', 'Services\UserService::create');            // Crear un nuevo usuario (POST /users)
$routes->put('/users/(:num)', 'Services\UserService::update/$1', ['filter' => 'auth']);   // Actualizar un usuario por ID (PUT /users/5)
$routes->delete('users/(:num)', 'Services\UserService::delete/$1', ['filter' => 'auth']); // Eliminar un usuario por ID (DELETE /users/5)

$routes->get('/tasks', 'Services\TaskService::index', ['filter' => 'auth']);              // Obtener todas las tareas (GET /tasks)
$routes->get('/tasks/(:num)', 'Services\TaskService::show/$1', ['filter' => 'auth']);     // Obtener una tarea por ID (GET /tasks/5)
$routes->post('/tasks', 'Services\TaskService::create', ['filter' => 'auth']);            // Crear una nueva tarea (POST /tasks)
$routes->put('/tasks/(:num)', 'Services\TaskService::update/$1', ['filter' => 'auth']);   // Actualizar una tarea por ID (PUT /tasks/5)
$routes->delete('tasks/(:num)', 'Services\TaskService::delete/$1', ['filter' => 'auth']); // Eliminar una tarea por ID (DELETE /tasks/5)

$routes->get('/subtasks', 'Services\SubtaskService::index', ['filter' => 'auth']);              // Obtener todos las subtareas (GET /subtasks)
$routes->get('/subtasks/(:num)', 'Services\SubtaskService::show/$1', ['filter' => 'auth']);     // Obtener una subtarea por ID (GET /subtasks/5)
$routes->post('/subtasks', 'Services\SubtaskService::create', ['filter' => 'auth']);            // Crear una nueva subtarea (POST /subtasks)
$routes->put('/subtasks/(:num)', 'Services\SubtaskService::update/$1', ['filter' => 'auth']);   // Actualizar una subtarea por ID (PUT /subtasks/5)
$routes->delete('subtasks/(:num)', 'Services\SubtaskService::delete/$1', ['filter' => 'auth']); // Eliminar una subtarea por ID (DELETE /subtasks/5)

$routes->post('/invitations', 'Services\InvitationService::create', ['filter' => 'auth']);              // Crear una nueva invitacion (POST /invitations)
$routes->put('/invitations/(:num)', 'Services\InvitationService::update/$1', ['filter' => 'auth']);                           // Actualizar una invitacion por ID (PUT /invitation/5)