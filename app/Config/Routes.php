<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('auth', static function (RouteCollection $routes): void {
    $routes->post('login', 'AuthController::sign_in');
    $routes->post('sign-up', 'AuthController::sign_up');
   
});

$routes->group('festivals', static function (RouteCollection $routes): void {
    $routes->post('create', 'FestivalController::create');
    $routes->get('active', 'EventController::activeFestivals');
});

$routes->group('users', static function (RouteCollection $routes): void {
    $routes->get('search', 'UserController::search');
});

$routes->group('events', static function (RouteCollection $routes): void {
    $routes->post('create', 'EventController::create');
});

