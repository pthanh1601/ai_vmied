<?php
namespace Neo\Core;

use Neo\Core\App;

class View {
    protected $basePath;
    protected $namespaces = [];
    
    // State của View
    protected $layout = null;
    protected $sections = [];
    protected $currentSection = null;

    public function __construct($basePath) {
        $this->basePath = $basePath;
    }

    public function addNamespace($name, $path) {
        $this->namespaces[$name] = $path;
    }

    public function extend($layout) {
        $this->layout = $layout;
    }

    public function section($name) {
        $this->currentSection = $name;
        ob_start();
    }

    public function endSection() {
        if ($this->currentSection) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }

    public function yield($name) {
        return $this->sections[$name] ?? '';
    }

    public function component($name, $data = []) {
        // Kiểm tra nếu chưa có prefix 'components/' thì tự thêm vào
        // Điều này giúp code cũ (gọi $this->component('header')) vẫn chạy đúng
        $path = strpos($name, 'components/') === 0 ? $name : 'components/' . $name;
        
        // Render với cờ isComponent = true (để không load layout)
        return $this->render($path, $data, true);
    }

    /**
     * Hàm chèn một file view con (Partial View) vào view hiện tại
     * Dùng cho các thành phần lặp lại: Menu, Sidebar, Footer...
     */
    public function insert($path, $data = []) {
        // Gọi render với isComponent = true để:
        // 1. Không load lại Layout cha (tránh lặp vô hạn html, body...)
        // 2. Vẫn hưởng các logic global data (session, errors...)
        return $this->render($path, $data, true);
    }
    
    /**
     * Hàm render chính - Xử lý Logic HTMX/Ajax & Layout đệ quy
     */
    public function render($path, $data = [], $isComponent = false) {
        $app = App::getInstance();
        
        // 1. Inject Global Data
        $data = array_merge($data, [
            'errors'  => $app->session->getFlash('errors') ?? [],
            'old'     => $app->session->getFlash('old') ?? [],
            'session' => $app->session,
            'request' => $app->request
        ]);

        // 2. Render View File
        // Sau bước này, nếu file view có $this->extend(), biến $this->layout sẽ có giá trị.
        // Biến $content sẽ chứa HTML trần (hoặc rỗng nếu toàn bộ code nằm trong section)
        $content = $this->renderFile($path, $data);

        // Lưu lại layout được yêu cầu bởi view này (vì lát nữa ta sẽ reset $this->layout)
        $requestedLayout = $this->layout;

        // 3. Logic chọn nội dung (FIX lỗi mất giao diện)
        if ($requestedLayout) {
            // Case A: Đây là View Con (có extend layout)
            // Nội dung chính nằm trong section('content'), $content ở trên thường là rỗng.
            $finalContent = isset($this->sections['content']) ? $this->sections['content'] : $content;
        } else {
            // Case B: Đây là Layout (Master) hoặc View độc lập
            // Nội dung chính là $content vừa render ra.
            $finalContent = $content;
        }

        // 4. Quyết định: Có render Layout bọc ngoài không?
        if ($this->shouldRenderLayout($requestedLayout, $isComponent)) {
            
            // Reset layout để tránh lặp vô hạn khi đệ quy render master
            $this->layout = null; 

            // Đẩy nội dung view con vào section 'content' để Layout cha dùng yield('content')
            $this->sections['content'] = $finalContent;

            $result = $this->render($requestedLayout, $data);
            return $this->minifyHtml($result);
        }

        // 5. Trả về kết quả cuối cùng
        return $this->minifyHtml($finalContent);
    }

    /**
     * Helper: Include file và lấy nội dung
     */
    protected function renderFile($path, $data) {
        $viewPath = $this->resolvePath($path);
        
        if (!file_exists($viewPath)) {
            // Fallback debug
            if ($path === 'errors/404') return "<h1>404 Not Found</h1>";
            throw new \Exception("View file not found: " . $viewPath);
        }

        extract($data);
        ob_start();
        include $viewPath;
        return ob_get_clean();
    }

    /**
     * Helper: Logic quyết định Layout (Brain)
     */
    protected function shouldRenderLayout($layout, $isComponent) {
        // Không có layout hoặc là component -> False
        if (!$layout || $isComponent) {
            return false;
        }

        $app = App::getInstance();

        // Check HTMX Headers
        $isHtmx = isset($_SERVER['HTTP_HX_REQUEST']) && $_SERVER['HTTP_HX_REQUEST'] === 'true';
        $isBoosted = isset($_SERVER['HTTP_HX_BOOSTED']) && $_SERVER['HTTP_HX_BOOSTED'] === 'true';
        
        // Check Ajax (bao gồm cả thư viện cũ)
        $isAjax = $app->request->isAjax() || (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        );

        // Rule 1: HTMX Partial (Update div) -> KHÔNG Layout
        if ($isHtmx && !$isBoosted) {
            return false;
        }

        // Rule 2: Ajax thường -> KHÔNG Layout
        if ($isAjax && !$isHtmx) {
            return false;
        }

        // Rule 3: Full Page hoặc HTMX Boost -> CÓ Layout
        return true;
    }

    protected function resolvePath($path) {
        // Xử lý Namespace Plugin: "PluginName::ViewName"
        if (strpos($path, '::') !== false) {
            list($namespace, $file) = explode('::', $path);
            
            if (isset($this->namespaces[$namespace])) {
                // Ưu tiên override trong resources/themes
                $override = $this->basePath . '/resources/themes/default/plugins/' . $namespace . '/' . $file . '.php';
                if (file_exists($override)) return $override;

                // Lấy trong plugin gốc
                $original = $this->namespaces[$namespace] . '/' . $file . '.php';
                if (file_exists($original)) return $original;
            }
        }
        
        // Mặc định
        return $this->basePath . '/' . $path . '.php';
    }
    protected function minifyHtml($html) {
        if (($_ENV['APP_ENV'] ?? 'local') !== 'production') {
            return $html;
        }
        $search = [
            '/\>[^\S ]+/s',     // Xóa khoảng trắng sau thẻ đóng >
            '/[^\S ]+\</s',     // Xóa khoảng trắng trước thẻ mở <
            '/(\s)+/s',         // Gộp nhiều khoảng trắng
            '//' // Xóa comment HTML
        ];

        $replace = [
            '>', 
            '<', 
            '\\1', 
            ''
        ];

        return preg_replace($search, $replace, $html);
    }
}