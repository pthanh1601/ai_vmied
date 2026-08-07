<?php
namespace Neo\Core\Cache;

class FileCache {
    protected $cacheDir;

    public function __construct($basePath) {
        $this->cacheDir = $basePath . '/storage/cache';
        if (!is_dir($this->cacheDir)) mkdir($this->cacheDir, 0777, true);
    }

    // Lưu cache (duration tính bằng phút)
    public function put($key, $data, $minutes = 60) {
        $file = $this->getFilePath($key);
        $payload = [
            'expires' => time() + ($minutes * 60),
            'data' => $data
        ];
        file_put_contents($file, serialize($payload));
    }

    public function get($key, $default = null) {
        $file = $this->getFilePath($key);
        if (!file_exists($file)) return $default;

        $content = file_get_contents($file);
        $payload = unserialize($content);

        if (time() > $payload['expires']) {
            $this->forget($key);
            return $default;
        }

        return $payload['data'];
    }

    public function forget($key) {
        $file = $this->getFilePath($key);
        if (file_exists($file)) unlink($file);
    }
    
    // Cache closure result: Nếu có thì lấy, chưa có thì chạy hàm callback rồi lưu
    public function remember($key, $minutes, $callback) {
        $value = $this->get($key);
        if ($value !== null) return $value;

        $value = call_user_func($callback);
        $this->put($key, $value, $minutes);
        return $value;
    }

    protected function getFilePath($key) {
        return $this->cacheDir . '/' . md5($key) . '.cache';
    }
}