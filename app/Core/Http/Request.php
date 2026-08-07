<?php 

namespace Neo\Core\Http;

/**
 * Lớp Request xử lý các dữ liệu đầu vào và cho phép gắn thêm các thuộc tính tùy ý.
 */
class Request
{
    /**
     * @var array Mảng lưu trữ các thuộc tính được gắn thêm (ví dụ: user, role, permissions...)
     */
    protected $attributes = [];

    /**
     * Magic Method: Cho phép gán bất cứ thuộc tính nào.
     * Ví dụ: $request->user = $data; hoặc $request->isAdmin = true;
     */
    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Magic Method: Cho phép truy cập các thuộc tính đã gán.
     * Ví dụ: echo $request->user->name;
     */
    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Magic Method: Kiểm tra sự tồn tại của thuộc tính.
     */
    public function __isset($name)
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Phương thức tiện ích để lấy thông tin user (nếu có)
     * Cách dùng: app()->request->user()
     */
    public function user()
    {
        return $this->attributes['user'] ?? null;
    }

    /**
     * Lấy dữ liệu đầu vào từ GET, POST hoặc JSON Body.
     */
    public function input($k = null, $d = null)
    {
        $dt = array_merge($_GET, $_POST);
        
        $contentType = $_SERVER["CONTENT_TYPE"] ?? "";
        if (strpos($contentType, "application/json") !== false || strpos($contentType, "json") !== false) {
            $json = json_decode(file_get_contents("php://input"), true);
            if (is_array($json)) {
                $dt = array_merge($dt, $json);
            }
        }

        if (!$k) return $dt;

        foreach (explode(".", $k) as $s) {
            if (isset($dt[$s])) {
                $dt = $dt[$s];
            } else {
                return $d;
            }
        }
        return $dt;
    }

    /**
     * Trả về tất cả dữ liệu input.
     */
    public function all()
    {
        return $this->input();
    }

    public function method()
    {
        return $_SERVER["REQUEST_METHOD"];
    }

    public function isMethod($m)
    {
        return $this->method() === strtoupper($m);
    }

    public function isAjax() {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }
        return $this->isHtmx();
    }

    public function isHtmx() {
        return isset($_SERVER['HTTP_HX_REQUEST']) && $_SERVER['HTTP_HX_REQUEST'] === 'true';
    }

    /**
     * Lấy dữ liệu đã được làm sạch XSS thông qua App instance.
     */
    public function clean($key = null, $default = null)
    {
        $input = $this->input($key, $default);
        // Giả sử App::getInstance()->xss->clean() tồn tại
        return \Neo\Core\App::getInstance()->xss->clean($input);
    }
}