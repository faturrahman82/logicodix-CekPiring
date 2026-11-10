<?php

namespace Core;

class Router {
    private array $routes = [];

    public function get(string $path, array $handler): void {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, array $handler): void {
        $path = '/' . trim($path, '/');
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'controller' => $handler[0],
            'action' => $handler[1]
        ];
    }

    public function dispatch(): void {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($scriptDir !== '/' && strpos($requestUri, $scriptDir) === 0) {
            $requestUri = substr($requestUri, strlen($scriptDir));
        }

        $requestUri = '/' . trim($requestUri, '/');
        if (empty($requestUri)) {
            $requestUri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $this->matchPath($route['path'], $requestUri)) {
                $controllerClass = $route['controller'];
                $action = $route['action'];

                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                    if (method_exists($controller, $action)) {
                        $controller->$action();
                        return;
                    }
                }
                http_response_code(500);
                echo "Method {$action} tidak ditemukan pada controller {$controllerClass}.";
                return;
            }
        }

        http_response_code(404);
        view('galat', [
            'pesan_judul' => 'Halaman tidak ditemukan',
            'pesan_deskripsi' => "Rute '{$requestUri}' dengan metode '{$requestMethod}' tidak ditemukan.",
            'kembali_url' => base_url()
        ]);
    }

    private function matchPath(string $routePath, string $requestUri): bool {
        return $routePath === $requestUri;
    }
}
