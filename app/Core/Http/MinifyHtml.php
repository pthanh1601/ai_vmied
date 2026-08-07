<?php
namespace Neo\Core\Http;

class MinifyHtml {
    public function handle($app) {
        // Chỉ chạy nếu config bật
        if (($_ENV['APP_MINIFY_HTML'] ?? 'false') !== 'true') return true;
        
        // Bắt đầu Output Buffering
        ob_start(function($buffer) {
            // Regex nén HTML
            $search = [
                '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
                '/[^\S ]+\</s',     // strip whitespaces before tags, except space
                '/(\s)+/s',         // shorten multiple whitespace sequences
                '//' // Remove HTML comments
            ];
            $replace = ['>', '<', '\\1', ''];
            $buffer = preg_replace($search, $replace, $buffer);
            return $buffer;
        });
        
        return true;
    }
}