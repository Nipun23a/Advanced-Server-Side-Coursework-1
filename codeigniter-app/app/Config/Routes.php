<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'AuthController::showLogin');

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