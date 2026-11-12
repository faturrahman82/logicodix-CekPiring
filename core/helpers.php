<?php

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string {
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        
        $url = $scheme . '://' . $host . $baseDir;
        if (!empty($path)) {
            $url .= '/' . ltrim($path, '/');
        }
        return $url;
    }
}

if (!function_exists('asset_url')) {
    function asset_url(string $path = ''): string {
        $cleanPath = ltrim($path, '/');
        $fullPath = __DIR__ . '/../public/assets/' . $cleanPath;
        $version = file_exists($fullPath) ? '?v=' . filemtime($fullPath) : '';
        return base_url('assets/' . $cleanPath) . $version;
    }
}

if (!function_exists('view')) {
    function view(string $viewName, array $data = []): void {
        extract($data);
        $viewFile = __DIR__ . '/../app/Views/' . str_replace('.', '/', $viewName) . '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "View [{$viewName}] tidak ditemukan di {$viewFile}";
        }
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void {
        if (!preg_match('#^https?://#i', $url)) {
            $url = base_url(ltrim($url, '/'));
        }
        header("Location: {$url}");
        exit;
    }
}

if (!function_exists('json_response')) {
    function json_response(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

if (!function_exists('e')) {
    function e(?string $value): string {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('ensure_session_started')) {
    function ensure_session_started(): void {
        if (session_status() === PHP_SESSION_NONE) {
            $sessionDir = __DIR__ . '/../storage/sessions';
            if (is_dir($sessionDir) && is_writable($sessionDir)) {
                session_save_path($sessionDir);
            }
            session_start();
        }
    }
}

if (!function_exists('session_get')) {
    function session_get(string $key, $default = null) {
        ensure_session_started();
        return $_SESSION[$key] ?? $default;
    }
}

if (!function_exists('session_set')) {
    function session_set(string $key, $value): void {
        ensure_session_started();
        $_SESSION[$key] = $value;
    }
}
