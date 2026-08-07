<?php
namespace App\Middleware;

use Neo\Core\App;

class SecurityHeadersMiddleware
{
    public function handle(App $app)
    {
        // 1. Anti-Clickjacking
        // Ngăn chặn trang web bị nhúng vào iframe của web khác
        header('X-Frame-Options: SAMEORIGIN');

        // 2. XSS Protection (Legacy Browsers)
        // Kích hoạt bộ lọc XSS của trình duyệt
        header('X-XSS-Protection: 1; mode=block');

        // 3. Prevent MIME Sniffing
        // Bắt buộc trình duyệt tuân thủ Content-Type đã khai báo
        header('X-Content-Type-Options: nosniff');

        // 4. Referrer Policy
        // Chỉ gửi domain khi user click link sang web khác (Bảo vệ riêng tư)
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // 5. Content Security Policy (Basic)
        // Đây là lớp bảo vệ mạnh nhất. Cấu hình cơ bản cho phép script từ cùng domain.
        // Cần nới lỏng nếu dùng CDN hoặc script ngoài (Google Analytics, v.v...)
        // header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' https: data:;");

        // 6. HSTS (HTTP Strict Transport Security)
        // Bắt buộc dùng HTTPS (Chỉ bật khi đã có SSL thực tế)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }

        return true;
    }
}
