<?php
namespace App\Middleware;

class AdminMiddleware
{
    public function handle()
    {
        $app = app();
        
        // 1. Lấy thông tin User từ Request, nếu không có thì bốc từ Session ra
        $user = $app->request->user ?? null;
        if (!$user) {
            $sessionAccount = $app->session->get('account');
            if ($sessionAccount) {
                $user = (object) $sessionAccount;
                $app->request->user = $user; // Gán lại vào request để tí nữa Controller dùng
            }
        }

        // Nếu vẫn không có -> Chưa đăng nhập thật -> Đuổi về trang login
        if (!$user || !isset($user->uuid)) {
            if ($app->request->isAjax()) {
                echo json_encode(['status' => 'error', 'redirect' => '/login']);
                exit;
            }
            header('Location: /login');
            exit;
        }

        // 2. GIẢI QUYẾT LỖI TYPE BỊ BIẾN THÀNH CHUỖI "Quản trị"
        // Chọc thẳng vào DB lấy ra đúng con số type (0, 1, 2)
        $realType = $app->db->get("accounts", "type", ["uuid" => $user->uuid]);
        
        // Ép lại type chuẩn bằng số vào request user để Controller dễ dàng phân luồng
        $app->request->user->type = (int) $realType;

        // 3. Kiểm tra quyền (CHỈ CHO PHÉP ADMIN (1))
        if ($realType != 1) {
            if ($app->request->isAjax()) {
                echo json_encode(['status' => 'error', 'alert' => 'Bạn không có quyền truy cập khu vực này.']);
                exit;
            }
            header('Location: /app');
            exit;
        }

        // Hợp lệ -> Cho phép đi tiếp vào giao diện Admin
        return true;
    }
}