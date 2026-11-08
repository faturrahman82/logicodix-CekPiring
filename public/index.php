<?php

/**
 * CekPiring - Front Controller
 * LOGICODIX 2026 Hackathon Competition
 */

$sessionDir = __DIR__ . '/../storage/sessions';
if (!is_dir($sessionDir)) {
    @mkdir($sessionDir, 0777, true);
}
@session_save_path($sessionDir);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoloader
spl_autoload_register(function ($class) {
    $prefixes = [
        'Core\\' => __DIR__ . '/../core/',
        'App\\'  => __DIR__ . '/../app/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

// Load helpers
require_once __DIR__ . '/../core/helpers.php';

// Load .env
\Core\Env::load(__DIR__ . '/../.env');

// Setup Router
$router = new \Core\Router();

// Load Routes
require_once __DIR__ . '/../config/routes.php';

// Dispatch Request
$router->dispatch();
