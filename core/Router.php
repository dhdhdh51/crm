<?php
namespace Core;

class Router {
    private array $routes = [];
    private array $middlewareGroups = [];

    public function get(string $path, string $handler, array $middleware = []): void {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, string $handler, array $middleware = []): void {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function group(array $middleware, callable $callback): void {
        $this->middlewareGroups[] = $middleware;
        $callback($this);
        array_pop($this->middlewareGroups);
    }

    private function addRoute(string $method, string $path, string $handler, array $middleware): void {
        $allMiddleware = array_merge(...$this->middlewareGroups, $middleware);
        $this->routes[] = [
            'method'     => $method,
            'pattern'    => $this->compile($path),
            'handler'    => $handler,
            'middleware' => $allMiddleware,
        ];
    }

    /** Convert /leads/{id}/edit → named regex */
    private function compile(string $path): string {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(string $method, string $uri): void {
        // Strip query string
        $uri = strtok($uri, '?');
        $uri = '/' . trim($uri, '/');
        if ($uri === '') $uri = '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            if (!preg_match($route['pattern'], $uri, $matches)) continue;

            // Run middleware
            foreach ($route['middleware'] as $mw) {
                $mwClass = "App\\Middleware\\{$mw}";
                (new $mwClass())->handle();
            }

            // Dispatch handler
            [$controllerName, $action] = explode('@', $route['handler']);
            $class = "App\\Controllers\\{$controllerName}";

            if (!class_exists($class)) {
                $this->abort(500, "Controller {$class} not found.");
                return;
            }

            $controller = new $class();
            if (!method_exists($controller, $action)) {
                $this->abort(500, "Method {$action} not found in {$class}.");
                return;
            }

            // Extract named route params
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $controller->$action(...array_values($params));
            return;
        }

        $this->abort(404, 'Page not found.');
    }

    private function abort(int $code, string $message): void {
        http_response_code($code);
        if ($code === 404) {
            // Try rendering a 404 view
            $view = ROOT . '/app/views/errors/404.php';
            if (file_exists($view)) { include $view; return; }
        }
        echo "<h1>Error {$code}</h1><p>" . htmlspecialchars($message) . "</p>";
    }
}
