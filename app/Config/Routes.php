<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

//$routes->get('/users', 'Services\UserService::index');              // Obtener todos los usuarios (GET /users)
$routes->get('/users/(:num)', 'Services\UserService::show/$1');     // Obtener un usuario por ID (GET /users/5)
$routes->post('/users', 'Services\UserService::create');            // Crear un nuevo usuario (POST /users)
$routes->put('/users/(:num)', 'Services\UserService::update/$1');   // Actualizar un usuario por ID (PUT /users/5)
$routes->delete('users/(:num)', 'Services\UserService::delete/$1'); // Eliminar un usuario por ID (DELETE /users/5)

$routes->get('/tasks', 'Services\TaskService::index');              // Obtener todos los tareas (GET /tasks)
$routes->get('/tasks/(:num)', 'Services\TaskService::show/$1');     // Obtener un tarea por ID (GET /tasks/5)
$routes->post('/tasks', 'Services\TaskService::create');            // Crear un nuevo tarea (POST /tasks)
$routes->put('/tasks/(:num)', 'Services\TaskService::update/$1');   // Actualizar un tarea por ID (PUT /tasks/5)
$routes->delete('tasks/(:num)', 'Services\TaskService::delete/$1'); // Eliminar un tarea por ID (DELETE /tasks/5)