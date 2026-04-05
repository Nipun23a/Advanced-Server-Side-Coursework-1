<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'AuthController::showLogin');
$routes->get('dashboard', 'DashboardController::index', ['filter' => ['auth', 'verified']]);
$routes->get('bidding', 'BiddingController::index', ['filter' => ['auth', 'verified']]);
$routes->post('bidding/place', 'BiddingController::placeBid', ['filter' => ['auth', 'verified']]);
$routes->post('bidding/update/(:num)', 'BiddingController::updateBid/$1', ['filter' => ['auth', 'verified']]);
$routes->post('bidding/cancel/(:num)', 'BiddingController::cancelBid/$1', ['filter' => ['auth', 'verified']]);
$routes->get('sponsorship/offers', 'SponsorshipController::offers', ['filter' => ['auth', 'verified']]);
$routes->post('sponsorship/offers/respond/(:num)', 'SponsorshipController::respond/$1', ['filter' => ['auth', 'verified']]);
$routes->get('profile', 'ProfileController::index', ['filter' => ['auth', 'verified']]);
$routes->post('profile', 'ProfileController::save', ['filter' => ['auth', 'verified']]);
$routes->post('profile/degrees', 'ProfileController::saveDegree', ['filter' => ['auth', 'verified']]);
$routes->post('profile/degrees/delete/(:num)', 'ProfileController::deleteDegree/$1', ['filter' => ['auth', 'verified']]);
$routes->post('profile/certificates', 'ProfileController::saveCertificate', ['filter' => ['auth', 'verified']]);
$routes->post('profile/certificates/delete/(:num)', 'ProfileController::deleteCertificate/$1', ['filter' => ['auth', 'verified']]);
$routes->post('profile/licenses', 'ProfileController::saveLicense', ['filter' => ['auth', 'verified']]);
$routes->post('profile/licenses/delete/(:num)', 'ProfileController::deleteLicense/$1', ['filter' => ['auth', 'verified']]);
$routes->post('profile/courses', 'ProfileController::saveCourse', ['filter' => ['auth', 'verified']]);
$routes->post('profile/courses/delete/(:num)', 'ProfileController::deleteCourse/$1', ['filter' => ['auth', 'verified']]);
$routes->post('profile/employment', 'ProfileController::saveEmployment', ['filter' => ['auth', 'verified']]);
$routes->post('profile/employment/delete/(:num)', 'ProfileController::deleteEmployment/$1', ['filter' => ['auth', 'verified']]);

// Authentication Routes
$routes->group('auth', function ($routes) {
    // Registration
    $routes->get('register', 'AuthController::showRegister');
    $routes->post('register', 'AuthController::register');

    // Email Verification
    $routes->get('verify-notice', 'AuthController::showVerifyNotice');
    $routes->get('verify-email', 'AuthController::verifyEmail');
    $routes->post('resend-verification', 'AuthController::resendVerification');

    // Login / Logout
    $routes->get('login', 'AuthController::showLogin');
    $routes->post('login', 'AuthController::login');
    $routes->get('logout', 'AuthController::logout');

    // Password Reset
    $routes->get('forgot-password', 'AuthController::showForgotPassword');
    $routes->post('forgot-password', 'AuthController::forgotPassword');
    $routes->get('reset-password', 'AuthController::showResetPassword');
    $routes->post('reset-password', 'AuthController::resetPassword');
});

$routes->group('api/developer', ['filter' => ['auth', 'verified']], function($routes) {
    $routes->get('api-keys', 'Api\DeveloperApiController::index');
    $routes->post('api-keys', 'Api\DeveloperApiController::create');
    $routes->delete('api-keys/(:num)', 'Api\DeveloperApiController::revoke/$1');
    $routes->get('api-keys/(:num)/stats', 'Api\DeveloperApiController::stats/$1');
});

$routes->group('developer', ['filter' => ['auth', 'verified']], function($routes) {
    $routes->get('api-keys', 'DeveloperController::apiKeys');
    $routes->get('api-docs', 'DeveloperController::apiDocs');
    $routes->get('usage', 'DeveloperController::usage');
});
