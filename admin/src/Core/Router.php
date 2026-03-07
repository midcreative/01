<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Lightweight front-controller router.
 * Maps HTTP method + URI path to a controller action.
 */
final class Router
{
    /** @var array<string, callable> */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes["GET::{$path}"] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes["POST::{$path}"] = $handler;
    }

    /**
     * Dispatch the current request.
     * Supports {param} placeholders, e.g. /admin/posts/{id}
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri    = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as $route => $handler) {
            [$routeMethod, $routePath] = explode('::', $route, 2);

            if ($routeMethod !== $method) {
                continue;
            }

            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // remove full match
                $handler(...$matches);
                return;
            }
        }

        http_response_code(404);
        echo '<h1>404 — 頁面不存在</h1>';
    }
}
