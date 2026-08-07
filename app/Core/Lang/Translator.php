<?php
namespace Neo\Core\Lang;

class Translator {
    protected $locale;
    protected $messages = [];
    protected $basePath;

    public function __construct($basePath) {
        $this->basePath = $basePath;
        // Ưu tiên Session -> Config -> Default 'en'
        $this->locale = $_SESSION['locale'] ?? $_ENV['APP_LOCALE'] ?? 'en';
    }

    public function setLocale($locale) {
        $this->locale = $locale;
        $_SESSION['locale'] = $locale;
    }

    public function getLocale() {
        return $this->locale;
    }

    public function get($key) {
        // Syntax: file.key (e.g., messages.welcome)
        $parts = explode('.', $key);
        $file = array_shift($parts);
        $path = $this->basePath . "/resources/lang/{$this->locale}/{$file}.php";

        // Load file nếu chưa load
        if (!isset($this->messages[$file])) {
            if (file_exists($path)) {
                $this->messages[$file] = require $path;
            } else {
                return $key; // Không tìm thấy file
            }
        }

        // Traverse array
        $value = $this->messages[$file];
        foreach ($parts as $part) {
            if (isset($value[$part])) {
                $value = $value[$part];
            } else {
                return $key; // Không tìm thấy key
            }
        }

        return $value;
    }
}