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
