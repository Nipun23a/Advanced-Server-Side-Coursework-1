<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ============================================================================
// PUBLIC ROUTES — No authentication required
// ============================================================================

$routes->get('/', 'AuthController::showLogin');

$routes->group('auth', function ($routes) {
    $routes->get('register',              'AuthController::showRegister');
    $routes->post('register',             'AuthController::register');

    $routes->get('verify-notice',         'AuthController::showVerifyNotice');
    $routes->get('verify-email',          'AuthController::verifyEmail');
    $routes->post('resend-verification',  'AuthController::resendVerification');

    $routes->get('login',                 'AuthController::showLogin');
    $routes->post('login',                'AuthController::login');
    $routes->get('logout',                'AuthController::logout');

    $routes->get('forgot-password',       'AuthController::showForgotPassword');
    $routes->post('forgot-password',      'AuthController::forgotPassword');
    $routes->get('reset-password',        'AuthController::showResetPassword');
    $routes->post('reset-password',       'AuthController::resetPassword');
});

// ============================================================================
// PROTECTED ROUTES — Require login + verified email
// Dashboard is role-neutral; controller handles the per-role redirect.
// ============================================================================

$routes->group('', ['filter' => ['auth', 'verified']], function ($routes) {
    $routes->get('dashboard', 'DashboardController::index');

    // Profile — accessible to every authenticated role
    $routes->get('profile',  'ProfileController::index');
    $routes->post('profile', 'ProfileController::save');

    $routes->post('profile/degrees',                    'ProfileController::saveDegree');
    $routes->post('profile/degrees/delete/(:num)',      'ProfileController::deleteDegree/$1');

    $routes->post('profile/certificates',               'ProfileController::saveCertificate');
    $routes->post('profile/certificates/delete/(:num)', 'ProfileController::deleteCertificate/$1');

    $routes->post('profile/licenses',                   'ProfileController::saveLicense');
    $routes->post('profile/licenses/delete/(:num)',     'ProfileController::deleteLicense/$1');

    $routes->post('profile/courses',                    'ProfileController::saveCourse');
    $routes->post('profile/courses/delete/(:num)',      'ProfileController::deleteCourse/$1');

    $routes->post('profile/employment',                 'ProfileController::saveEmployment');
    $routes->post('profile/employment/delete/(:num)',   'ProfileController::deleteEmployment/$1');
});

// ---- University Staff (admin) — CW2 analytics, alumni browser, export ----

$routes->group('', ['filter' => ['auth:admin', 'verified']], function ($routes) {

    // Analytics
    $routes->group('analytics', function ($routes) {
        $routes->get('/',                        'AnalyticsController::index');
        $routes->get('skills-gap',               'AnalyticsController::skillsGap');
        $routes->get('employment-sectors',       'AnalyticsController::employmentSectors');
        $routes->get('job-titles',               'AnalyticsController::jobTitles');
        $routes->get('top-employers',            'AnalyticsController::topEmployers');
        $routes->get('certification-trends',     'AnalyticsController::certificationTrends');
        $routes->get('license-distribution',     'AnalyticsController::licenseDistribution');
        $routes->get('career-pathways',          'AnalyticsController::careerPathways');
        $routes->get('graduation-outcomes',      'AnalyticsController::graduationOutcomes');
        // JSON proxies for dashboard mini-charts
        $routes->get('skills-gap-json',          'AnalyticsController::skillsGapJson');
        $routes->get('employment-sectors-json',  'AnalyticsController::employmentSectorsJson');
        $routes->get('certification-trends-json','AnalyticsController::certificationTrendsJson');
    });

    // Alumni browser
    $routes->group('alumni', function ($routes) {
        $routes->get('/',      'AlumniController::index');
        $routes->get('(:num)', 'AlumniController::show/$1');
    });

    // Export
    $routes->group('export', function ($routes) {
        $routes->get('/',    'ExportController::index');
        $routes->get('csv',  'ExportController::exportCsv');
        $routes->get('pdf',  'ExportController::exportPdf');
    });
});

// ---- Alumni — CW1 bidding and sponsorship ----

$routes->group('', ['filter' => ['auth:alumni', 'verified']], function ($routes) {

    // Bidding
    $routes->get('bidding',                  'BiddingController::index');
    $routes->post('bidding/place',           'BiddingController::placeBid');
    $routes->post('bidding/update/(:num)',   'BiddingController::updateBid/$1');
    $routes->post('bidding/cancel/(:num)',   'BiddingController::cancelBid/$1');

    // Sponsorship
    $routes->get('sponsorship/offers',                   'SponsorshipController::offers');
    $routes->post('sponsorship/offers/respond/(:num)',   'SponsorshipController::respond/$1');
});

// ---- Developer — API key management pages + JSON endpoints ----

$routes->group('', ['filter' => ['auth:developer', 'verified']], function ($routes) {
    $routes->get('developer/api-keys', 'DeveloperController::apiKeys');
    $routes->get('developer/api-docs', 'DeveloperController::apiDocs');
    $routes->get('developer/usage',    'DeveloperController::usage');
});

// ============================================================================
// DEVELOPER API ROUTES — JSON endpoints, require developer role
// ============================================================================

$routes->group('api/developer', ['filter' => ['auth:developer', 'verified']], function ($routes) {
    $routes->get('internal-secret',           'Api\DeveloperApiController::secretStatus');
    $routes->post('internal-secret',          'Api\DeveloperApiController::saveSecret');
    $routes->post('internal-secret/generate', 'Api\DeveloperApiController::generateSecret');

    $routes->get('api-keys',                  'Api\DeveloperApiController::index');
    $routes->post('api-keys',                 'Api\DeveloperApiController::create');
    $routes->delete('api-keys/(:num)',         'Api\DeveloperApiController::revoke/$1');
    $routes->get('api-keys/(:num)/stats',     'Api\DeveloperApiController::stats/$1');
});
