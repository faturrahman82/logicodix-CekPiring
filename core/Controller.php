<?php

namespace Core;

abstract class Controller {
    protected function render(string $viewName, array $data = []): void {
        view($viewName, $data);
    }

    protected function json(array $data, int $statusCode = 200): void {
        json_response($data, $statusCode);
    }

    protected function redirect(string $path): void {
        redirect($path);
    }
}
