<?php 
namespace Neo\Core\Http;

class Cookie
{
    protected $defaults = [
        'lifetime' => 86400 * 30, // 30 ngày
        'path'     => '/',
        'domain'   => '',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ];

    public function __construct()
    {
        $this->defaults['domain'] = $_SERVER['HTTP_HOST'] ?? '';
        $this->defaults['secure'] = isset($_SERVER['HTTPS']);
    }

    /**
     * Thiết lập Cookie
     */
    public function set($name, $value, $expiry = null, $options = [])
    {
        $opts = array_merge($this->defaults, $options);
        $expireTime = ($expiry === null) ? time() + $opts['lifetime'] : time() + $expiry;

        setcookie($name, $value, [
            'expires'  => $expireTime,
            'path'     => $opts['path'],
            'domain'   => $opts['domain'],
            'secure'   => $opts['secure'],
            'httponly' => $opts['httponly'],
            'samesite' => $opts['samesite']
        ]);
    }

    /**
     * Lấy giá trị Cookie
     */
    public function get($name, $default = null)
    {
        return $_COOKIE[$name] ?? $default;
    }

    /**
     * Kiểm tra Cookie có tồn tại không
     */
    public function has($name)
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * Xóa Cookie
     */
    public function forget($name)
    {
        if ($this->has($name)) {
            // Đặt thời gian hết hạn về quá khứ để trình duyệt tự xóa
            $this->set($name, '', -3600);
            unset($_COOKIE[$name]);
        }
    }
}