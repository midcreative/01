<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Base controller with shared rendering helpers.
 */
abstract class BaseController
{
    /**
     * Redirect to a URL and exit.
     */
    protected function redirect(string $url): never
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Send a JSON response and exit.
     *
     * @param array<mixed> $data
     */
    protected function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Get sanitized string from POST.
     */
    protected function postString(string $key, string $default = ''): string
    {
        return htmlspecialchars(trim((string) ($_POST[$key] ?? $default)));
    }

    /**
     * Get integer from POST.
     */
    protected function postInt(string $key, int $default = 0): int
    {
        return (int) ($_POST[$key] ?? $default);
    }

    /**
     * Render a view file with extracted data.
     */
    protected function render(string $viewPath, array $data = []): void
    {
        extract($data);
        if (!str_ends_with($viewPath, '.php')) {
            $viewPath .= '.php';
        }
        require __DIR__ . '/../Views/' . ltrim($viewPath, '/');
    }
}
