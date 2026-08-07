<?php
namespace Neo\Core;

use Dotenv\Dotenv;
use Neo\Core\Http\Request;
use Neo\Core\Http\Response;
use Neo\Core\Http\Session;
use Neo\Core\Http\Cookie;
use Neo\Core\Validation\Validator;
use Neo\Core\Lang\Translator;
use Neo\Core\Cache\FileCache;
use Neo\Core\Compiler\AssetCompiler;

/**
 * Class App
 * Core Application Class - Optimized for Performance & Security
 * * @property-read \Neo\Core\Router $router
 * @property-read \Neo\Core\Http\Request $request
 * @property-read \Neo\Core\Http\Response $response
 * @property-read \Neo\Core\Database $db
 * @property-read \Neo\Core\Http\Session $session
 * @property-read \Neo\Core\Http\Cookie $cookie
 * @property-read \Neo\Core\Validation\Validator $validator
 * @property-read \Neo\Core\Lang\Translator $lang
 * @property-read \Neo\Core\Cache\FileCache|\Neo\Core\Cache\RedisCache $cache
 * @property-read \Neo\Core\Security $xss
 * @property-read \Neo\Core\Mail $mail
 * @property-read \Neo\Core\View $view
 * @property-read \Neo\Core\WebSocket $ws
 * @property-read \Neo\Core\Queue $queue
 * @property-read \Neo\Core\Compiler\AssetCompiler $compiler
 * @property-read \Neo\Core\PluginManager $pluginManager
 */
class App
{
    /** @var App */
    protected static $instance;
    
    protected $basePath;
    
    // Registry: Chứa các công thức khởi tạo (Closure)
    protected $registry = [];
    
    // Instances: Chứa các object đã được khởi tạo
    protected $instances = [];
    
    protected $middlewareRegistry = [];

    // Trạng thái Debug (Lấy từ .env)
    protected $isDebug = false;

    /**
     * Khởi tạo Application
     * @param string|null $basePath
     */
    public function __construct($basePath = null)
    {
        // 1. Singleton Guard: Chặn việc tạo mới nếu đã tồn tại
        if (self::$instance) {
            throw new \RuntimeException("Application has already been initialized!");
        }

        self::$instance = $this;
        $this->basePath = $basePath ? rtrim($basePath, '\/') : null;

        // 2. Load Environment (.env) & Cấu hình hiển thị lỗi
        $this->bootEnvironment();

        // 3. Đăng ký Services (Lazy Loading - Chỉ khai báo, chưa chạy new)
        $this->registerServices();

        // 4. Khởi tạo các thành phần cốt lõi bắt buộc (Core)
        $this->instances['router'] = new Router($this);
        
        // Boot Theme (View)
        $this->bootTheme();
        
        // Boot Plugin Manager
        $this->instances['pluginManager'] = new PluginManager($this);

        // 5. Đăng ký Middleware
        $this->registerCoreMiddleware();

        // 6. Boot Plugins
        $this->bootPlugins();
    }

    /**
     * Load biến môi trường và cấu hình hiển thị lỗi
     */
    protected function bootEnvironment()
    {
        if (file_exists($this->basePath . '/.env')) {
            $dotenv = Dotenv::createImmutable($this->basePath);
            $dotenv->load();
        }

        // Chuyển đổi string 'true' thành boolean
        $this->isDebug = isset($_ENV['APP_DEBUG']) && ($_ENV['APP_DEBUG'] === 'true' || $_ENV['APP_DEBUG'] === true);

        if ($this->isDebug) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        } else {
            // Production: Ẩn lỗi tuyệt đối để bảo mật
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            error_reporting(0);
        }
    }

    /**
     * Đăng ký các service vào Container (Lazy Loading)
     * Sử dụng function() thay vì fn() để tương thích mọi phiên bản PHP
     */
    protected function registerServices()
    {
        // Các Service cơ bản
        $this->bind('events', function() { return new EventDispatcher(); });
        $this->bind('session', function() { return new Session(); });
        $this->bind('cookie', function() { return new Cookie(); });
        $this->bind('request', function() { return new Request(); });
        $this->bind('response', function() { return new Response(); });
        $this->bind('validator', function() { return new Validator(); });
        $this->bind('compiler', function() { return new AssetCompiler(); });
        $this->bind('mail', function() { return new Mail(); });
        $this->bind('xss', function() { return new Security(); });

        // Translator cần basePath
        $this->bind('lang', function($c) { return new Translator($c->basePath()); });

        // Database: Chỉ kết nối khi thực sự cần
        $this->bind('db', function() { return new Database(); });

        // Cache: Check driver từ env
        $this->bind('cache', function($c) {
            $driver = $_ENV['CACHE_DRIVER'] ?? 'file';
            if ($driver === 'redis') {
                return new \Neo\Core\Cache\RedisCache();
            }
            return new \Neo\Core\Cache\FileCache($c->basePath());
        });

        // WebSocket & Push
        $this->bind('ws', function() { 
            return new WebSocket($_ENV['WS_URL'] ?? 'wss://ws.postman-echo.com/raw'); 
        });
        $this->bind('push', function() { return new WebPush(); });

        // Queue: Cần DB, $c->db sẽ tự động kích hoạt lazy load của Database
        $this->bind('queue', function($c) { return new Queue($c->db); });
    }

    protected function bootTheme()
    {
        $themeName = $_ENV['APP_THEME'] ?? 'default';
        $themePath = $this->basePath . '/resources/themes/' . $themeName;
        
        if (!is_dir($themePath)) {
            $themePath = $this->basePath . '/resources/themes/default';
        }
        
        $this->instances['view'] = new View($themePath);
    }

    protected function registerCoreMiddleware()
    {
        $this->registerMiddleware('csrf', [\Neo\Core\Http\CsrfVerifier::class, 'handle']);
        $this->registerMiddleware('throttle', [\App\Middleware\ThrottleMiddleware::class, 'handle']);
        $this->registerMiddleware('auth.api', [\App\Middleware\AuthApiMiddleware::class, 'handle']);
        $this->registerMiddleware('security_headers', [\App\Middleware\SecurityHeadersMiddleware::class, 'handle']);
    }

    // =========================================================================
    // MAGIC METHODS (CORE OF LAZY LOADING)
    // =========================================================================

    /**
     * Magic Getter: Tự động khởi tạo service khi được gọi tên
     */
    public function __get($key)
    {
        // 1. Nếu đã khởi tạo rồi -> Trả về ngay
        if (isset($this->instances[$key])) {
            return $this->instances[$key];
        }

        // 2. Nếu chưa khởi tạo nhưng có công thức -> Thực thi công thức
        if (isset($this->registry[$key])) {
            $this->instances[$key] = call_user_func($this->registry[$key], $this);
            return $this->instances[$key];
        }

        return null;
    }

    /**
     * Bind một service mới vào container
     */
    public function bind($name, $closure)
    {
        $this->registry[$name] = $closure;
    }

    // Bảo mật: Chặn Clone
    private function __clone() {}
    
    // Bảo mật: Chặn Unserialize
    public function __wakeup() {}

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    public static function getInstance()
    {
        return self::$instance;
    }

    public function basePath()
    {
        return $this->basePath;
    }

    protected function bootPlugins()
    {
        // Gọi thông qua instance để đảm bảo consistency
        $this->instances['pluginManager']->loadPlugins($this->basePath . '/plugins');
    }

    public function registerMiddleware($n, $c)
    {
        $this->middlewareRegistry[$n] = $c;
    }
    
    public function getMiddleware($n)
    {
        return $this->middlewareRegistry[$n] ?? null;
    }
    
    public function router($p, $m, $c)
    {
        return $this->router->add($p, $m, $c);
    }
    
    public function group($a, $c)
    {
        $this->router->group($a, $c);
    }
    
    public function render($p, $d = [])
    {
        return $this->view->render($p, $d);
    }

    /**
     * Lấy service instance (Tương thích ngược)
     */
    public function make($name)
    {
        return $this->$name; 
    }

    public function validate($r, $messages = [], $attributes = [])
    {
        return $this->validator->make($this->request->input(), $r, $messages, $attributes);
    }

    /**
     * Xử lý lỗi và hiển thị trang Error an toàn
     */
    public function abort($code = 404, $message = '')
    {
        http_response_code($code);

        $defaultMessages = [
            404 => 'Không tìm thấy trang yêu cầu.',
            403 => 'Bạn không có quyền truy cập trang này.',
            500 => 'Lỗi hệ thống nội bộ.',
            419 => 'Phiên làm việc hết hạn (Lỗi CSRF). Vui lòng tải lại trang.',
        ];

        if (empty($message)) {
            $message = $defaultMessages[$code] ?? 'Đã xảy ra lỗi không xác định.';
        }

        // Response JSON cho Ajax
        if ($this->request->isAjax()) {
            $jsonMsg = ($code === 500 && !$this->isDebug) ? 'Internal Server Error' : $message;
            $this->response->json(['error' => true, 'code' => $code, 'message' => $jsonMsg], $code);
        }

        try {
            echo $this->view->render("errors/{$code}", [
                'code' => $code,
                'message' => $message,
                'title' => "Error $code"
            ]);
        } catch (\Exception $e) {
            // [SECURITY LOGGING]
            // Ghi log lỗi thật vào file hệ thống
            error_log("[NeoApp Error] Code: $code | User Msg: $message | View Error: " . $e->getMessage());

            if ($this->isDebug) {
                // Môi trường Dev: Hiện chi tiết
                echo "<h1>Error $code</h1>";
                echo "<p><b>Message:</b> $message</p>";
                echo "<hr><p><b>View Rendering Failed:</b> " . $e->getMessage() . "</p>";
            } else {
                // Môi trường Prod: Ẩn chi tiết
                echo "<h1>Something went wrong ($code)</h1>";
                echo "<p>Please contact administrator if the problem persists.</p>";
            }
        }
        exit;
    }

    public function run()
    {
        // Event Dispatcher sẽ tự động được khởi tạo tại đây nhờ Lazy Loading
        $this->events->fire('app.start', $this);
        
        echo $this->router->dispatch();
        
        $this->events->fire('app.end', $this);
    }

    public function loadRoutesFrom($path)
    {
        $files = glob($path . '/*.php');
        $app = $this; // Để trong file route có thể dùng biến $app
        if ($files) {
            foreach ($files as $file) {
                require_once $file;
            }
        }
    }
}