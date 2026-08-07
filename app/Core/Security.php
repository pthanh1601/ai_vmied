<?php
namespace Neo\Core;

class Security
{
    /**
     * Escape output string for HTML context
     * Để ngăn chặn XSS khi hiển thị dữ liệu ra view
     *
     * @param string $string
     * @return string
     */
    public function escape($string)
    {
        if (is_null($string)) {
            return '';
        }
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Clean input data (Basic Sanitization)
     * Loại bỏ các ký tự nguy hiểm khỏi input đầu vào
     *
     * @param mixed $input
     * @return mixed
     */
    public function clean($input)
    {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                unset($input[$key]);
                $input[$this->clean($key)] = $this->clean($value);
            }
        } else {
            // Strip Null Bytes
            $input = str_replace(chr(0), '', $input);
            
            // Basic tags stripping (Optional: use HTMLPurifier for advanced needs)
            $input = strip_tags($input);
            
            // XSS Prevention for standard input
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        }

        return $input;
    }

    /**
     * Allow using the object as a function
     * Example: $app->xss($string)
     */
    public function __invoke($string)
    {
        return $this->escape($string);
    }
}
