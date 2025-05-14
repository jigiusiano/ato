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
$routes->get('/users/(:num)', 'Services\UserService::show/$1', ['filter' => 'auth']);
$routes->post('/users', 'Services\UserService::create');
$routes->put('/users/(:num)', 'Services\UserService::update/$1', ['filter' => 'auth']);
$routes->delete('users/(:num)', 'Services\UserService::delete/$1', ['filter' => 'auth']);

$routes->get('/tasks', 'Services\TaskService::index', ['filter' => 'auth']);
$routes->get('/tasks/(:num)', 'Services\TaskService::show/$1', ['filter' => 'auth']);
$routes->post('/tasks', 'Services\TaskService::create', ['filter' => 'auth']);
$routes->put('/tasks/(:num)', 'Services\TaskService::update/$1', ['filter' => 'auth']);
$routes->delete('tasks/(:num)', 'Services\TaskService::delete/$1', ['filter' => 'auth']);

$routes->get('/subtasks', 'Services\SubtaskService::index', ['filter' => 'auth']);
$routes->get('/subtasks/(:num)', 'Services\SubtaskService::show/$1', ['filter' => 'auth']);
$routes->post('/subtasks', 'Services\SubtaskService::create', ['filter' => 'auth']);
$routes->put('/subtasks/(:num)', 'Services\SubtaskService::update/$1', ['filter' => 'auth']);
$routes->delete('subtasks/(:num)', 'Services\SubtaskService::delete/$1', ['filter' => 'auth']);

$routes->post('/invitations', 'Services\InvitationService::create', ['filter' => 'auth']);
$routes->get('/invitations/(:num)', 'Services\InvitationService::update/$1');  

$routes->get('/collaborators', 'Services\CollaboratorService::index', ['filter' => 'auth']);