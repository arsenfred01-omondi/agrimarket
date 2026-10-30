<?php
declare(strict_types=1);

/**
 * Application configuration.
 * Override DB settings with environment variables in production.
 */
const APP_NAME     = 'FredAgriMarket';
const DELIVERY_FEE = 150;

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'fredagrimarket');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

date_default_timezone_set('Africa/Nairobi');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}
