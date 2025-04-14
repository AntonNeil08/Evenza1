<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Apply CORS filter to all routes
$routes->group('', ['filter' => 'cors'], static function (RouteCollection $routes): void {
    
    // Test route
    $routes->get('test-hi', 'TestController::sayHi');

    // Authentication Routes
    $routes->group('auth', static function (RouteCollection $routes): void {
        $routes->post('login', 'AuthController::login');
        $routes->post('generate-jwt', 'AuthController::generateJWT');
        $routes->post('verify-jwt', 'AuthController::verifyJWT');
        $routes->post('generate-otp', 'AuthController::generateOTP');
        $routes->post('verify-otp', 'AuthController::verifyOTP');
        $routes->post('resend-otp', 'AuthController::resendOTP');
    });

    // Admin Routes
    $routes->group('admin', static function (RouteCollection $routes): void {
        $routes->get('list', 'AdminController::adminsList');
        $routes->post('update/(:segment)', 'AdminController::updateAdmin/$1');
    });

    // Dean Routes
    $routes->group('dean', static function (RouteCollection $routes): void {
        $routes->get('list', 'DeanController::deansList');
        $routes->post('update/(:segment)', 'DeanController::updateDean/$1');
    });

    // Faculty Routes
    $routes->group('faculty', static function (RouteCollection $routes): void {
        $routes->get('list', 'FacultyController::facultyList');
        $routes->post('update/(:segment)', 'FacultyController::updateFaculty/$1');
    });

    // Coordinator Routes
    $routes->group('coordinator', static function (RouteCollection $routes): void {
        $routes->get('list', 'CoordinatorController::coordinatorsList');
        $routes->post('update/(:segment)', 'CoordinatorController::updateCoordinator/$1');
    });

    // Student Routes
    $routes->group('student', static function (RouteCollection $routes): void {
        $routes->get('list', 'StudentController::studentsList');
        $routes->get('section/(:segment)', 'StudentController::getStudentsBySection/$1'); // Fetch students by section
        $routes->post('update/(:segment)', 'StudentController::updateStudent/$1');
        $routes->post('switch-regularity/(:segment)', 'StudentController::switchStudentRegularity/$1');
    });

    // Global User Management (For all user-related operations)
    $routes->group('user', static function (RouteCollection $routes): void {
        $routes->post('create', 'UserController::createUser'); // Create any user
        $routes->post('change-password', 'UserController::changePassword');
        $routes->post('reset-password', 'UserController::resetPassword');
        $routes->post('toggle-status/(:segment)', 'UserController::toggleUserStatus/$1'); // Activate/Deactivate User
        $routes->post('toggle-privileged-faculty/(:segment)', 'UserController::togglePrivilegedFaculty/$1'); // Regular ↔ Privileged Faculty
    });

    // Academic Management Routes
    $routes->group('academic', static function (RouteCollection $routes): void {
        // Department Routes
        $routes->get('departments', 'AcademicController::departmentsList');
        $routes->post('departments/create', 'AcademicController::createDepartment');

        // Program Routes
        $routes->get('programs', 'AcademicController::programsList');
        $routes->post('programs/create', 'AcademicController::createProgram');

        // Year Level Routes
        $routes->get('year-levels', 'AcademicController::yearLevelsList');
        $routes->post('year-levels/create', 'AcademicController::createYearLevel');

        // Section Routes
        $routes->get('sections', 'AcademicController::sectionsList');
        $routes->post('sections/create', 'AcademicController::createSection');

        // Subject Routes
        $routes->get('subjects', 'AcademicController::subjectsList');
        $routes->post('subjects/create', 'AcademicController::createSubject');
        $routes->post('subjects/update/(:segment)', 'AcademicController::updateSubject/$1');
        $routes->post('subjects/delete/(:segment)', 'AcademicController::deleteSubject/$1');

        // Soft Delete for Any Entity
        $routes->post('delete/(:segment)/(:segment)', 'AcademicController::deleteEntity/$1/$2');
    });

    // Global OPTIONS Handler (Handles all preflight requests dynamically)
    $routes->options('{any}', static function () {
        $response = service('response');
        return $response->setStatusCode(204)
                        ->setHeader('Access-Control-Allow-Origin', '*')
                        ->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                        ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    });
});
