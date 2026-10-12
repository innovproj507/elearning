<?php

declare(strict_types=1);

namespace App\Core;

use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use App\Middleware\ApiAuthMiddleware;

/**
 * Router — enrutador casero con soporte para parámetros dinámicos,
 * middlewares y separación entre rutas web y API.
 */
class Router
{
    private array $routes = [];
    private array $middlewareMap = [
        'auth'     => AuthMiddleware::class,
        'api_auth' => ApiAuthMiddleware::class,
    ];

    // ─── Registro de rutas ────────────────────────────────────────

    public function get(string $path, string $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, string $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    private function addRoute(string $method, string $path, string $handler, array $middlewares): void
    {
        // Convertir {param} en grupo de captura regex
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = compact('method', 'path', 'pattern', 'handler', 'middlewares');
    }

    // ─── Despacho ─────────────────────────────────────────────────

    public function dispatch(): void
    {
        $method  = $_SERVER['REQUEST_METHOD'];
        $uri     = $this->parseUri();

        // Soporte para _method en formularios HTML (PUT, DELETE, PATCH)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            if (!preg_match($route['pattern'], $uri, $matches)) continue;

            // Extraer solo los parámetros nombrados
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            // Ejecutar middlewares
            $this->runMiddlewares($route['middlewares'], $params);

            // Resolver controlador y método
            $this->resolveHandler($route['handler'], $params);
            return;
        }

        $this->notFound();
    }

    private function parseUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'];
        $uri = strtok($uri, '?');       // Quitar query string
        $uri = rtrim($uri, '/') ?: '/'; // Normalizar trailing slash
        return rawurldecode($uri);
    }

    private function runMiddlewares(array $middlewares, array $params): void
    {
        foreach ($middlewares as $middleware) {
            // Middleware con parámetro: "role:teacher,admin"
            if (str_contains($middleware, ':')) {
                [$name, $args] = explode(':', $middleware, 2);
                $roles = explode(',', $args);

                if ($name === 'role') {
                    (new RoleMiddleware())->handle($roles);
                }
                continue;
            }

            if (isset($this->middlewareMap[$middleware])) {
                $class = $this->middlewareMap[$middleware];
                (new $class())->handle($params);
            }
        }
    }

    private function resolveHandler(string $handler, array $params): void
    {
        // Formato: "ControllerClass@method" o "Namespace\ControllerClass@method"
        [$class, $method] = explode('@', $handler);

        // Construir el FQCN
        $namespace = str_contains($class, '\\')
            ? "App\\{$class}"
            : "App\\Controllers\\{$class}";

        if (!class_exists($namespace)) {
            throw new \RuntimeException("Controlador no encontrado: {$namespace}");
        }

        $controller = new $namespace();

        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Método '{$method}' no encontrado en {$namespace}");
        }

        $controller->$method($params);
    }

    private function notFound(): void
    {
        http_response_code(404);

        // Si es petición a la API, responder en JSON
        if (str_starts_with($_SERVER['REQUEST_URI'], '/api/')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Endpoint no encontrado']);
            return;
        }

        include BASE_PATH . '/views/errors/404.php';
    }
}
