<?php
namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    public function handle($app)
    {
        // 1. Check Session trước
        $sessionUser = $app->session->get('account');

        if ($sessionUser) {
            // Kiểm tra user trong DB xem có bị khóa/xóa không
            $isValidAccount = $app->db->has("accounts", [
                "uuid"    => $sessionUser['uuid'],
                "status"  => 1,
                "deleted" => 0
            ]);

            if (!$isValidAccount) {
                $this->logout($app);
                return handleUnauthenticated(); // Hàm này nằm trong helpers
            }
            
            // 👉 THÊM: Lấy số dư mới nhất từ DB để không bao giờ bị sai số
            $currentPoint = $app->db->get("points", "points", ["account" => $sessionUser['uuid']]);
            $sessionUser['point'] = $currentPoint ?? 0;
            
            // 👉 THÊM: Cập nhật lại session để giữ cho nó luôn fresh
            $app->session->set('account', $sessionUser);
            
            // Gán user vào request để Controller dùng
            $app->request->user = (object) $sessionUser; 
            return true; // Cho qua
        }

        // 2. Check Token (Remember Me)
        $token = $app->cookie->get('token');
        if (!$token) return handleUnauthenticated();

        try {
            $key = $_ENV['APP_KEY'] ?? 'secret_key';
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            
            // Check bảo mật Agent (chống trộm token)
            $currentAgent = $_SERVER["HTTP_USER_AGENT"];
            $loginRecord = $app->db->get("accounts_login", "*", [
                "account"  => $decoded->uid, 
                "token"    => $decoded->token,
                "agent"    => $currentAgent,
                "deleted"  => 0
            ]);

            if (!$loginRecord) {
                throw new \Exception("Session expired or invalid");
            }

            // Lấy thông tin mới nhất từ DB
            $account = $app->db->get("accounts", ["uuid", "name", "email", "avatar", "type"], [
                "uuid" => $decoded->uid
            ]);

            if (!$account) throw new \Exception("Account not found");
            
            // 👉 THÊM: Ở code cũ của bạn đoạn này quên lấy 'point' từ DB, nếu user đăng nhập bằng Cookie sẽ bị mất số dư!
            $point = $app->db->get("points", "points", ["account" => $decoded->uid]);

            // Tự động set lại session
            $userData = [
                "uuid"   => $account['uuid'],
                "name"   => $account['name'],
                "avatar" => $account['avatar'],
                "email"  => $account['email'],
                "type"   => $account['type'] == 0 ? 'Thành viên' : 'Quản trị',
                "point"  => $point ?? 0, // 👉 THÊM DÒNG NÀY VÀO MẢNG
            ];
            
            $app->session->set('account', $userData);
            $app->request->user = (object) $userData;
            
            return true;

        } catch (\Exception $e) {
            $this->logout($app);
            return handleUnauthenticated();
        }
    }

    protected function logout($app) {
        $app->session->forget('account');
        $app->cookie->forget('token');
    }
}