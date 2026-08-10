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
            $account = $app->db->get("accounts",[
                "[>]points" => ["uuid" => "account"]
            ], [
                "accounts.uuid", 
                "accounts.name", 
                "accounts.email", 
                "accounts.avatar", 
                "accounts.type",
                "accounts.affiliate",
                "accounts.ref_by",
                "points.points(point)",
            ], 
            [
                "accounts.uuid" => $decoded->uid
            ]);

            if (!$account) throw new \Exception("Account not found");

            // Tự động set lại session
            $userData = [
                "uuid"   => $account['uuid'],
                "name"   => $account['name'],
                "avatar" => $account['avatar'],
                "email"  => $account['email'],
                "point"  => $account['point'] ?? 0,
                "affiliate" => $account['affiliate'] ?? null,
                "ref_by" => $account['ref_by'] ?? null,
                "type"   => $account['type'] == 0 ? 'Thành viên' : 'Quản trị',
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