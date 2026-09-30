<?php
namespace App\Core;

/**
 * 輕量級 MVC 請求路由器
 */
class Router
{
    private array $routes = [];

    public function get(string $path, $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, $handler): void
    {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'path'    => '/' . trim($path, '/'),
            'handler' => $handler
        ];
    }

    public function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // 去除 Query String
        $parsedUrl = parse_url($uri, PHP_URL_PATH);

        // 如果有傳遞 ?r=xxx (Fallback 模式)
        if (isset($_GET['r'])) {
            $path = '/' . trim($_GET['r'], '/');
        } else {
            // 依部署設定修剪網站子目錄前綴（本機 /ESG、正式環境 /xenia6912 等）。
            $path = $parsedUrl;
            $appConfig = require dirname(__DIR__, 2) . '/config/app.php';
            $baseUrl = rtrim((string)($appConfig['base_url'] ?? ''), '/');
            $prefixes = array_filter([$baseUrl . '/public', $baseUrl, '/ESG/public', '/ESG']);
            foreach ($prefixes as $p) {
                if (str_starts_with($path, $p)) {
                    $path = substr($path, strlen($p));
                    break;
                }
            }
            $path = '/' . trim($path, '/');
            if ($path === '//' || $path === '') {
                $path = '/';
            }
        }

        // 搜尋匹配的路由
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $this->matchPath($route['path'], $path, $matches)) {
                // CSRF Token 自動驗證 (除免登入供應商問卷外)
                if ($requestMethod === 'POST' && !str_starts_with($path, '/suppliers/public-survey')) {
                    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
                    if (!Csrf::validate($token)) {
                        http_response_code(403);
                        die('403 Forbidden: CSRF Token 驗證失敗，請重新載入頁面後再試。');
                    }
                }

                $handler = $route['handler'];
                if (is_callable($handler)) {
                    call_user_func_array($handler, $matches);
                    return;
                }

                if (is_array($handler)) {
                    [$class, $method] = $handler;
                    $controller = new $class();
                    call_user_func_array([$controller, $method], $matches);
                    return;
                }
            }
        }

        // 404 Not Found
        http_response_code(404);
        View::render('layouts/404', ['path' => $path], null);
    }

    private function matchPath(string $routePath, string $requestPath, &$matches): bool
    {
        $routeRegex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $routePath);
        $routeRegex = '#^' . $routeRegex . '$#';

        if (preg_match($routeRegex, $requestPath, $m)) {
            $matches = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
            return true;
        }
        return false;
    }
}
