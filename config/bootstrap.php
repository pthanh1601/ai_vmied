<?php
    use Neo\Core\App;

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    register_shutdown_function(function () {
        $error = error_get_last();
        if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            while (ob_get_level()) ob_end_clean();
            echo "<h1>Fatal Error</h1><p>{$error['message']}</p>";
        }
    });

    require_once __DIR__ . '/../vendor/autoload.php';

    if (file_exists(__DIR__ . '/helpers.php')) {
        require_once __DIR__ . '/helpers.php';
    }

    $app = new App(dirname(__DIR__));

    $app->registerMiddleware('auth', [\App\Middleware\AuthMiddleware::class, 'handle']);

    $app->loadRoutesFrom(__DIR__ . '/../routers');

    return $app;