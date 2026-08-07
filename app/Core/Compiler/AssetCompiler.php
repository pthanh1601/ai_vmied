<?php
namespace Neo\Core\Compiler;

class AssetCompiler {
    public function build($basePath) {
        echo "Building Assets...\n";
        $this->processDir($basePath . '/public/css', 'css');
        $this->processDir($basePath . '/public/js', 'js');
        echo "Done!\n";
    }

    protected function processDir($dir, $type) {
        if (!is_dir($dir)) return;
        $files = glob($dir . '/*.' . $type);
        
        foreach ($files as $file) {
            // Bỏ qua file đã minified
            if (strpos($file, '.min.') !== false) continue;
            
            $content = file_get_contents($file);
            $minified = ($type === 'css') ? $this->minifyCss($content) : $this->minifyJs($content);
            
            $filename = basename($file, '.' . $type);
            $savePath = $dir . '/' . $filename . '.min.' . $type;
            
            file_put_contents($savePath, $minified);
            echo "  Minified: " . basename($file) . " -> " . basename($savePath) . "\n";
        }
    }

    protected function minifyCss($css) {
        // Loại bỏ comment, khoảng trắng thừa, newline
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        $css = str_replace([': ', ' ;', '; ', ' {', '{ ', ' }', '} '], [':', ';', ';', '{', '{', '}', '}'], $css);
        return trim($css);
    }

    protected function minifyJs($js) {
        // Simple JS Minifier (Regex basic)
        // Lưu ý: Regex JS phức tạp, đây là bản simple an toàn.
        // Xóa comment block
        $js = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $js);
        // Xóa comment dòng (cẩn thận với URL http://)
        $js = preg_replace('/\/\/(?![\S\s]*[\'"])(.*)/', '', $js); 
        $js = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $js);
        $js = preg_replace('/\s+/', ' ', $js);
        return trim($js);
    }
}