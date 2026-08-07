<?php
namespace Neo\Core;

class EventDispatcher {
    protected $listeners = [];

    /**
     * Đăng ký lắng nghe sự kiện
     * @param string $event Tên sự kiện
     * @param callable $callback Hàm xử lý
     * @param int $priority Độ ưu tiên (cao chạy trước)
     */
    public function listen($event, $callback, $priority = 10) {
        $this->listeners[$event][$priority][] = $callback;
    }

    /**
     * Kích hoạt sự kiện (Action)
     * Chỉ chạy các listeners, không thay đổi dữ liệu đầu vào.
     * @param string $event Tên sự kiện
     * @param mixed ...$payload Dữ liệu truyền vào
     */
    public function fire($event, ...$payload) {
        if (!isset($this->listeners[$event])) return;

        // Sắp xếp theo priority giảm dần (số càng lớn chạy càng sớm?? - Thường priority cao chạy trước)
        // Tuy nhiên convention common: thấp chạy trước hoặc cao chạy trước. 
        // WordPress: 10 default. Thấp chạy trước.
        // Laravel: Ksort.
        // Neo: Chọn ksort (Smallest integer first - chạy trước)
        ksort($this->listeners[$event]);

        foreach ($this->listeners[$event] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                call_user_func_array($callback, $payload);
            }
        }
    }

    /**
     * Kích hoạt bộ lọc (Filter)
     * Chạy các listeners và cho phép thay đổi dữ liệu đầu vào.
     * @param string $event Tên filter
     * @param mixed $value Giá trị cần filter
     * @param mixed ...$args Các tham số phụ
     * @return mixed Giá trị sau khi filter
     */
    public function filter($event, $value, ...$args) {
        if (!isset($this->listeners[$event])) return $value;

        ksort($this->listeners[$event]);

        foreach ($this->listeners[$event] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                // Filter expects return value.
                // Call callback with ($value, ...args)
                $params = array_merge([$value], $args);
                $value = call_user_func_array($callback, $params);
            }
        }

        return $value;
    }
}
