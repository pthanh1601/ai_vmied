<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
}

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// register_shutdown_function(function () {
//     $error = error_get_last();
//     if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
//         while (ob_get_level())
//             ob_end_clean();
//         echo "<h1>Fatal Error</h1><p>{$error['message']}</p>";
//     }
// });
// use Neo\Core\App;
// use Firebase\JWT\JWT;
// require __DIR__ . '/../vendor/autoload.php';
// if (file_exists(__DIR__ . '/../app/Helpers/functions.php')) {
//     require_once __DIR__ . '/../app/Helpers/functions.php';
// }
// try {
//     $app = new App(dirname(__DIR__));
//     $app->registerMiddleware('auth', function ($app) {
//         $sessionUser = app()->session->get('account');

//         if ($sessionUser) {
//             $isValidAccount = app()->db->has("accounts", [
//                 "uuid"    => $sessionUser['uuid'],
//                 "status"  => 1,
//                 "deleted" => 0
//             ]);
//             if (!$isValidAccount) {
//                 app()->session->forget('account');
//                 app()->cookie->forget('token');
//                 return handleUnauthenticated();
//             }
//             $app->request->user  = (object) $sessionUser; 
//             return true;
//         }
//         $token = app()->cookie->get('token');
//         if (!$token) return handleUnauthenticated();

//         try {
//             $key = $_ENV['APP_KEY'] ?? 'secret_key';
//             $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($key, 'HS256'));
//             $currentAgent = $_SERVER["HTTP_USER_AGENT"];
//             $loginRecord = app()->db->get("accounts_login", "*", [
//                 "account"  => $decoded->uid, 
//                 "token"    => $decoded->token,
//                 "agent"    => $currentAgent,
//                 "deleted"  => 0
//             ]);
//             if (!$loginRecord) {
//                 throw new \Exception("Session expired in database");
//             }
//             $account = app()->db->get("accounts", ["uuid", "name","email", "avatar"], [
//                 "uuid" => $decoded->uid
//             ]);

//             app()->session->set('account',[
//                 "uuid" => $account['uuid'],
//                 "name" => $account['name'],
//                 "avatar" => $account['avatar'],
//                 "email" => $account['email'],
//                 "point" => 1000,
//                 "type" => $account['type'] == 0 ? 'Thành viên' : 'Quản trị',
//             ]);

//             $app->request->user = (object) app()->session->get('account');
//             return true;

//         } catch (\Exception $e) {
//             app()->session->forget('account');
//             app()->cookie->forget('token');
            
//             return handleUnauthenticated();
//         }
//     });
//     $app->router('/', 'GET', function () use ($app) {return view('home');});
//     $app->router('/login', 'GET', ['App\Controllers\AuthController', 'index']);
//     $app->router('/login', 'POST', ['App\Controllers\AuthController', 'Login']);
//     $app->router('/register', 'POST', ['App\Controllers\AuthController', 'Register']);
//     $app->router('/logout', 'GET', ['App\Controllers\AuthController', 'Logout']);

//     $app->group(['prefix' => '/app', 'middleware' => 'auth'], function ($app) {
//          $app->router('/account', 'GET', ['App\Controllers\AuthController', 'Account']);
//     });
   
//     $app->run();

// } catch (Throwable $e) {
//     if (isset($app)) {
//         $app->abort(500, $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
//     } else {
//         echo "<h1>Critical Error</h1>" . $e->getMessage();
//     }
// }

try {
    // 1. Gọi file bootstrap để lấy instance $app
    $app = require_once __DIR__ . '/../config/bootstrap.php';

    // 2. Chạy ứng dụng
    $app->run();

} catch (Throwable $e) {
    // Xử lý lỗi cấp cao nhất (nếu bootstrap hoặc run bị lỗi)
    if (isset($app) && method_exists($app, 'abort')) {
        $app->abort(500, $e->getMessage());
    } else {
        // Fallback nếu App chưa khởi tạo được
        http_response_code(500);
        echo "<h1>Critical Error</h1>" . $e->getMessage();
    }
}