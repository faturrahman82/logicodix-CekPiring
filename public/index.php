<?php

$sessionDir = __DIR__ . '/../storage/sessions';
if (!is_dir($sessionDir)) {
    @mkdir($sessionDir, 0777, true);
}
@session_save_path($sessionDir);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

require_once __DIR__ . '/../core/helpers.php';

\Core\Env::load(__DIR__ . '/../.env');

$debug = filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
error_reporting($debug ? E_ALL : (E_ALL & ~E_DEPRECATED & ~E_NOTICE));

$router = new \Core\Router();

require_once __DIR__ . '/../config/routes.php';

$router->dispatch();
