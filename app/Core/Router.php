<?php
namespace Neo\Core;

class Router {
    protected $app;
    protected $routes = [];
    protected $groupStack = [];
    protected $globalMiddlewares = []; // [NEW] Global middlewares

    public function __construct($app) {
        $this->app = $app;
        
        // [AUTO] Register default global middlewares
        $this->addGlobalMiddleware('security_headers');
    }

    public function addGlobalMiddleware($name) {
        $this->globalMiddlewares[] = $name;
    }

    public function group($attributes, $callback) {
        if (is_string($attributes)) $attributes = ['prefix' => $attributes];
        $this->groupStack[] = $attributes;
        call_user_func($callback, $this->app);
        array_pop($this->groupStack);
    }

    public function add($path, $method, $callback) {
        $prefix = '';
        $groupMiddlewares = [];

        foreach ($this->groupStack as $group) {
            // Lấy prefix và xóa gạch chéo 2 đầu
            $p = trim($group['prefix'] ?? '', '/');
            if ($p !== '') {
                $prefix .= '/' . $p;
            }
            
            if (isset($group['middleware'])) {
                $mw = $group['middleware'];
                $groupMiddlewares = array_merge($groupMiddlewares, is_array($mw) ? $mw : [$mw]);
            }
        }

        // Làm sạch path truyền vào
        $cleanPath = trim($path, '/');
        
        // Kết hợp prefix và path
        $finalPath = $prefix . ($cleanPath !== '' ? '/' . $cleanPath : '');
        
        // Đảm bảo nếu path trống hoàn toàn thì nó là '/'
        if ($finalPath === '') {
            $finalPath = '/';
        }

        $route = new Route($finalPath, $method, $callback);
        
        if (!empty($groupMiddlewares)) {
            $route->middleware($groupMiddlewares);
        }

        $this->routes[] = $route;
        return $route;
    }

    public function dispatch() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if ($uri === '') $uri = '/';
        $uri = '/' . trim($uri, '/');
        if ($uri === '//') $uri = '/';
        
        $method = $_SERVER['REQUEST_METHOD'];

        // [NEW] Run Global Middlewares
        foreach ($this->globalMiddlewares as $mwName) {
            $this->runMiddleware($mwName);
        }

        foreach ($this->routes as $route) {
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route->path);
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $uri, $matches) && $route->method === $method) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Check Middleware
                foreach ($route->middlewares as $mwName) {
                    if ($this->runMiddleware($mwName) === false) return;
                }

                $callback = $route->callback;
                if (is_array($callback)) {
                    $controller = new $callback[0]();
                    return call_user_func([$controller, $callback[1]], $params);
                }
                return call_user_func($callback, $params);
            }
        }

        // [UPDATE] Gọi trang lỗi 404 đẹp
        return $this->app->abort(404);
    }
    protected function runMiddleware($mwName) {
        $handler = $this->app->getMiddleware($mwName);
        if ($handler) {
            if (is_array($handler) && is_string($handler[0])) {
                $handler[0] = new $handler[0]();
            }
            if (call_user_func($handler, $this->app) === false) return false;
        }
        return true;
    }
}