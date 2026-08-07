<?php namespace Neo\Core\Http;
class Session
{
    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            // [SECURITY] Secure Session Settings
            session_set_cookie_params([
                'lifetime' => 7200,
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'] ?? '',
                'secure' => isset($_SERVER['HTTPS']), // True if HTTPS
                'httponly' => true, // JS cannot access
                'samesite' => 'Lax' // CSRF Protection
            ]);
            session_start();
        }
        if (empty($_SESSION["_token"])) {
            $_SESSION["_token"] = bin2hex(random_bytes(32));
        }
    }
    public function token()
    {
        return $_SESSION["_token"];
    }
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }
    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    public function forget($key)
    {
        unset($_SESSION[$key]);
    }
    public function flash($key, $value)
    {
        $_SESSION["_flash"][$key] = $value;
    }
    public function getFlash($key)
    {
        $value = $_SESSION["_flash"][$key] ?? null;
        if (isset($_SESSION["_flash"][$key])) {
            unset($_SESSION["_flash"][$key]);
        }
        return $value;
    }
}
