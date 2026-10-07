<?php
// C:\xampp\htdocs\workshopos\core\Router.php

class Router {
    private array $routes = [];
    private string $basePath = '/workshopos';

    public function __construct(string $basePath = '/workshopos') {
        $this->basePath = rtrim($basePath, '/');
    }

    public function get(string $path, callable|array $handler): void {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void {
        $this->addRoute('POST', $path, $handler);
    }

    public function any(string $path, callable|array $handler): void {
        $this->addRoute('GET', $path, $handler);
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable|array $handler): void {
        // Convert route params like {id} or {slug} to regex capture groups
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method'  => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler,
            'path'    => $path
        ];
    }

    public function dispatch(): mixed {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $rawUri = $_SERVER['REQUEST_URI'] ?? '/';

        // Check if query string contains _url (e.g., from .htaccess fallback)
        if (isset($_GET['_url'])) {
            $uri = '/' . ltrim($_GET['_url'], '/');
        } else {
            $uri = parse_url($rawUri, PHP_URL_PATH) ?: '/';
        }

        // Strip base path (handles single or repeated base path e.g. /workshopos or /workshopos/workshopos)
        if ($this->basePath !== '') {
            while (strpos($uri, $this->basePath) === 0) {
                $uri = substr($uri, strlen($this->basePath));
                if ($uri === '' || $uri === false) {
                    $uri = '/';
                    break;
                }
            }
        }

        if ($uri === '' || $uri === false) {
            $uri = '/';
        }

        // Normalize trailing slashes unless root
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                // Extract only named parameters
                $namedParams = [];
                foreach ($matches as $key => $val) {
                    if (is_string($key)) {
                        $namedParams[$key] = $val;
                    }
                }

                $handler = $route['handler'];

                if (is_callable($handler)) {
                    // Check if handler expects positional args or array
                    $ref = is_array($handler) 
                        ? new ReflectionMethod($handler[0], $handler[1]) 
                        : new ReflectionFunction($handler);
                    
                    $paramCount = $ref->getNumberOfParameters();
                    if ($paramCount > 0) {
                        $firstParam = $ref->getParameters()[0];
                        if ($firstParam->getName() === 'params' && $paramCount === 1) {
                            return call_user_func($handler, $namedParams);
                        } else {
                            // Pass named parameter values in order
                            return call_user_func_array($handler, array_values($namedParams));
                        }
                    } else {
                        return call_user_func($handler);
                    }
                } else if (is_array($handler)) {
                    $controller = new $handler[0]();
                    $action = $handler[1];
                    return call_user_func_array([$controller, $action], array_values($namedParams));
                }
            }
        }

        // 404 Not Found
        http_response_code(404);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Endpoint not found.', 'error_code' => 'NOT_FOUND']);
        } else {
            if (defined('VIEWS_PATH') && file_exists(VIEWS_PATH . '/errors/404.php')) {
                require VIEWS_PATH . '/errors/404.php';
            } else {
                echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>404 - Page Not Found</h1><p>The requested URL was not found on this server.</p><a href="' . (defined('APP_URL') ? APP_URL : '/') . '">Return Home</a></body></html>';
            }
        }
        return null;
    }
}
