<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->get('/users/(:num)', 'Services\UserService::show/$1');     // Obtener un usuario por ID (GET /users/5)
$routes->post('/users', 'Services\UserService::create');            // Crear un nuevo usuario (POST /users)
$routes->put('/users/(:num)', 'Services\UserService::update/$1');   // Actualizar un usuario por ID (PUT /users/5)
$routes->delete('users/(:num)', 'Services\UserService::delete/$1'); // Eliminar un usuario por ID (DELETE /users/5)

$routes->get('/tasks', 'Services\TaskService::index');              // Obtener todas las tareas (GET /tasks)
$routes->get('/tasks/(:num)', 'Services\TaskService::show/$1');     // Obtener una tarea por ID (GET /tasks/5)
$routes->post('/tasks', 'Services\TaskService::create');            // Crear una nueva tarea (POST /tasks)
$routes->put('/tasks/(:num)', 'Services\TaskService::update/$1');   // Actualizar una tarea por ID (PUT /tasks/5)
$routes->delete('tasks/(:num)', 'Services\TaskService::delete/$1'); // Eliminar una tarea por ID (DELETE /tasks/5)

$routes->get('/subtasks', 'Services\SubtaskService::index');              // Obtener todos las subtareas (GET /subtasks)
$routes->get('/subtasks/(:num)', 'Services\SubtaskService::show/$1');     // Obtener una subtarea por ID (GET /subtasks/5)
$routes->post('/subtasks', 'Services\SubtaskService::create');            // Crear una nueva subtarea (POST /subtasks)
$routes->put('/subtasks/(:num)', 'Services\SubtaskService::update/$1');   // Actualizar una subtarea por ID (PUT /subtasks/5)
$routes->delete('subtasks/(:num)', 'Services\SubtaskService::delete/$1'); // Eliminar una subtarea por ID (DELETE /subtasks/5)

$routes->post('/invitations', 'Services\InvitationService::create');            // Crear una nueva invitacion (POST /subtasks)
$routes->put('/invitations/(:num)', 'Services\InvitationService::update/$1');   // Actualizar una invitacion por ID (PUT /subtasks/5)