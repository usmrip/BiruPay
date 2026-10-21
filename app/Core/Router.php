<?php
namespace App\Core;

class Router {
    private static array $routes = [];
    private static array $namedRoutes = [];

    public static function add(string $method, string $path, $handler, array $middlewares = []): void {
        $path = '/' . trim($path, '/');
        self::$routes[] = [
            'method'      => strtoupper($method),
            'path'        => $path,
            'handler'     => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public static function get(string $path, $handler, array $middlewares = []): void {
        self::add('GET', $path, $handler, $middlewares);
    }

    public static function post(string $path, $handler, array $middlewares = []): void {
        self::add('POST', $path, $handler, $middlewares);
    }

    public static function any(string $path, $handler, array $middlewares = []): void {
        self::add('GET', $path, $handler, $middlewares);
        self::add('POST', $path, $handler, $middlewares);
    }

    public static function dispatch(Request $request): void {
        $method = $request->method();
        $uri = self::resolveUri();

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            // Convert route pattern with {param} into regex
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                // Execute Middlewares
                foreach ($route['middlewares'] as $middleware) {
                    self::runMiddleware($middleware, $request);
                }

                // Extract named parameter values
                $params = array_filter($matches, '\is_string', ARRAY_FILTER_USE_KEY);

                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$class, $action] = $handler;
                    $controller = new $class();
                    $controller->$action($request, ...array_values($params));
                    return;
                }

                if (is_callable($handler)) {
                    call_user_func_array($handler, array_merge([$request], array_values($params)));
                    return;
                }
            }
        }

        // Route not found
        if ($request->isAjax() || str_starts_with($uri, '/api/')) {
            Response::json(['status' => 'error', 'message' => 'Endpoint tidak ditemukan: ' . $uri], 404);
        } else {
            Response::notFound("Endpoint '$uri' tidak ditemukan dalam sistem pembayaran.");
        }
    }

    private static function resolveUri(): string {
        if (!empty($_GET['route'])) {
            return '/' . trim($_GET['route'], '/');
        }

        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        
        // Strip out /pembayaran prefix if running inside subdirectory
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($scriptDir !== '/' && str_starts_with($uri, $scriptDir)) {
            $uri = substr($uri, strlen($scriptDir));
        }

        $uri = '/' . trim($uri, '/');
        return $uri;
    }

    private static function runMiddleware(string $middleware, Request $request): void {
        if ($middleware === 'auth') {
            if (!Session::has('user_id')) {
                Session::setFlash('warning', 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
                Response::redirect(View::url('login'));
            }
        } elseif ($middleware === 'guest') {
            if (Session::has('user_id')) {
                Response::redirect(View::url('dashboard'));
            }
        }
    }
}

