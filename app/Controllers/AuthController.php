<?php
namespace App\Controllers;

use Neo\Core\Controller;
use Firebase\JWT\JWT;

class AuthController
{
    protected $app;

    public function __construct()
    {
        $this->app = app();
    }

    public function index() {
        $user_id = $this->app->session->get("account");
        if (isset($user_id)) {
            if (app()->request->isAjax()) {
                header('HX-Redirect: /app');
                return "Login Successful";
            }
            header('Location: /app');
            exit;
        }

        return view('login', [
            'title' => 'Đăng nhập',
            'session' => $user_id ?? '',
        ]);
    }

    public function dashboard() {
        $user = app()->request->user;
    
        $logs = app()->db->select("originality_history", "*", [
            "account_uuid" => $user->uuid,   
            "ORDER"        => ["created_at" => "DESC"],
            "LIMIT"        => 10
        ]);
    
        $activities = [];
        foreach ($logs as $item) {
            // originality_history lưu ai và plagiarism dạng JSON riêng
            $ai         = json_decode($item['ai']         ?? '{}', true);
            $plagiarism = json_decode($item['plagiarism'] ?? '{}', true);
    
            $resultText = 'N/A';
            $colorClass = 'text-primary';
            $icon       = 'file-text';
            $bgColor    = 'bg-primary-subtle text-primary';
    
            // AI score đã được lưu sẵn cột ai_score (0-100)
            $aiScore   = (float) ($item['ai_score']   ?? 0);
            $plagScore = (float) ($item['plag_score'] ?? 0);
    
            if ($aiScore > 0) {
                $humanPct   = round(100 - $aiScore, 1);
                $resultText = "{$humanPct}% Human";
                $colorClass = $humanPct >= 80 ? 'text-success' : 'text-danger';
                $icon       = 'bot';
                $bgColor    = 'bg-primary-subtle text-primary';
            }
    
            if ($plagScore > 0) {
                $resultText = "{$plagScore}% Trùng lặp";
                $colorClass = $plagScore >= 20 ? 'text-danger' : 'text-warning';
                $icon       = 'file-warning';
                $bgColor    = 'bg-danger-subtle text-danger';
            }
    
            $activities[] = [
                'id'     => $item['id'],
                'title'  => $item['title'] ?? 'Tài liệu không tên',
                'tool'   => $plagScore > 0 ? 'plagiarism' : 'ai_detector',
                'result' => $resultText,
                'class'  => $colorClass,
                'icon'   => $icon,
                'bg'     => $bgColor,
                'time'   => $item['created_at'],
            ];
        }
    
        return view('home/home', [
            'user'       => $user,
            'activities' => $activities,
        ]);
    }

    public function Login() {
        $validate = app()->validate(
            [
                'email'    => 'required|email',
                'password' => 'required'
            ],
            [],
            ['email' => 'Địa chỉ Email', 'password' => 'Mật khẩu']
        );

        if ($validate->fails()) {
            return response()->json([
                'status'  => 'error',
                'alert' => $validate->first(),
            ], 401);
        }
        $email = app()->xss->clean(request('email'));
        $password = request('password');

        $account = app()->db->get("accounts", "*", ["email" => $email]);

        if (!$account) {
            return response()->json([
                'status' => 'error', 
                'alert' => 'Tài khoản hoặc mật khẩu không đúng',
            ], 401);
        }
        if ($account['deleted'] === 1) {
            return response()->json(['status' => 'error', 'alert' => 'Tài khoản này đã bị xóa.'], 401);
        }
        if ($account['status'] === 0) {
            return response()->json(['status' => 'error', 'alert' => 'Tài khoản của bạn đang chờ Ban Quản Trị xét duyệt.'], 401);
        }
        if ($account['status'] === 2) {
            return response()->json(['status' => 'error', 'alert' => 'Tài khoản này đã bị khóa. Vui lòng liên hệ Admin.'], 401);
        }
        if (!password_verify($password, $account['password'])) {
            return response()->json([
                'status' => 'error', 
                'alert' => 'Tài khoản hoặc mật khẩu không đúng'
            ], 401);
        }
        $account['point'] = app()->db->get("points","points",["account"=>$account['uuid']]);
        $token = $this->jwt($account);

        return response()->json([
            'status' => 'success',
            'alert' => 'Đăng nhập thành công',
            "push" => true,
            "redirect" => '/app',
        ]);
    }

    public function Register() {
        $validate = app()->validate(
            [
                'name'     => 'required|min:2',
                'email'    => 'required|email',
                'password' => 'required|min:6'
            ],
            [],
            ['name' => 'Họ và tên', 'email' => 'Địa chỉ Email', 'password' => 'Mật khẩu']
        );
        if ($validate->fails()) {
            return response()->json([
                'status'  => 'error',
                'alert' => $validate->first()
            ]);
        }
    
        $email    = app()->xss->clean(request('email'));
        $name     = app()->xss->clean(request('name'));
        $password = request('password');
    
        $refCode = app()->xss->clean(request('ref') ?? '');
    
        $account = app()->db->get("accounts", ["email", "deleted", "status"], ["email" => $email]);
        if ($account) {
            if ($account['status'] === 0) {
                return response()->json(['status' => 'error', 'alert' => 'Tài khoản này đã bị vô hiệu hóa.']);
            }
            if ($account['deleted'] === 0) {
                return response()->json(['status' => 'error', 'alert' => 'Email này đã được sử dụng.']);
            }
        }
    
        $referrer = null;
        if (!empty($refCode)) {
            $referrer = app()->db->get("accounts", ["id", "uuid", "affiliate"], [
                "affiliate" => $refCode, 
                "type"      => 2,  // <-- BẮT BUỘC TUYẾN TRÊN PHẢI LÀ VIP
                "deleted[!]"=> 1, 
                "status"    => 1
            ]);
        }
    
        $finalStatus = false;
        $insertData  = [];
    
        app()->db->action(function($db) use ($name, $email, $password, $referrer, &$insertData, &$finalStatus) {
    
            $insertData = [
                "type"      => 0,
                "uuid"      => uuid(),
                "name"      => $name,
                "email"     => $email,
                "password"  => password_hash($password, PASSWORD_DEFAULT),
                "status"    => 1,
                "avatar"    => '',
                "affiliate" => random_secret(8, 'numeric'),
                "ref_by"    => $referrer['affiliate'] ?? 0,
            ];
    
            $accountQuery = $db->insert("accounts", $insertData);
            if (!$accountQuery || $accountQuery->rowCount() === 0) {
                return false;
            }
    
            $accountId        = $db->id();
            $insertData['id'] = $accountId;
    
            $pointAmount = $_ENV['POINT'] ?? 0;
            
            $pointQuery  = $db->insert("points", [
                "account" => $insertData['uuid'],
                "points"  => $pointAmount,
            ]);
            if (!$pointQuery || $pointQuery->rowCount() === 0) {
                return false;
            }
            $insertData['point'] = $pointAmount;
            
            $walletQuery = $db->insert("wallets", [
                "account" => $accountId,
                "balance" => 0,
            ]);
            if (!$walletQuery || $walletQuery->rowCount() === 0) {
                return false;
            }
    
            $finalStatus = true;
            return true;
        });
    
        if ($finalStatus === true) {
            $token = $this->jwt($insertData);
            return response()->json([
                'status'   => 'success',
                'alert'    => 'Đăng ký thành công',
                "push"     => true,
                "redirect" => '/app',
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'alert'  => 'Có lỗi xảy ra trong quá trình đăng ký. Vui lòng thử lại.'
            ]);
        }
    }

    protected function jwt($account) {
        
        $typeInt      = (int) $account['type'];
        $organization = $account['organization'] ?? '';
        $avatar       = $account['avatar'] ?? '';

        // NẾU LÀ HỌC VIÊN (TYPE 0) THUỘC TRƯỜNG VIP -> LẤY LOGO VÀ TÊN TRƯỜNG CỦA TUYẾN TRÊN
        if ($typeInt === 0 && !empty($account['ref_by'])) {
            $vipDb = app()->db->get("accounts", ["organization", "avatar", "type", "status"], [
                "affiliate" => trim($account['ref_by'])
            ]);
            
            if ($vipDb && $vipDb['type'] == 2 && $vipDb['status'] == 1) {
                $organization = $vipDb['organization'] ?? '';
                $avatar       = $vipDb['avatar'] ?? ''; // Gán Logo trường cho học viên
            }
        }
        $roleId = $account['role_id'] ?? null;
        $permissions = [];

        if ($typeInt === 1) {
            if (!empty($roleId)) {
                // Nếu Admin có gán Role -> Lấy chuỗi JSON permissions từ bảng permissions
                $roleData = app()->db->get("permissions", "permissions", [
                    "id" => $roleId,
                    "deleted" => 0
                ]);
                if ($roleData) {
                    $permissions = json_decode($roleData, true) ?: [];
                }
            } else {
                // Nếu role_id bằng null -> Super Admin (Cấp full tất cả quyền)
                $permissions = ['*']; 
            }
        }
        
        app()->session->set('account', [
            "uuid"         => $account['uuid'],
            "name"         => $account['name'],
            "avatar"       => $avatar,
            "email"        => $account['email'],
            "point"        => $account['point'] ?? 0,
            "affiliate"    => $account['affiliate'],
            "type"         => $typeInt,      // Số: 0 (Học viên), 1 (Admin), 2 (VIP)
            "organization" => $organization, // Tên Trường
            "role_id"      => $roleId,       // ID Nhóm quyền
            "permissions"  => $permissions   // Mảng quyền chi tiết
        ]);

        $key = $_ENV['APP_KEY'] ?? 'secret_key';
        $payload = [
            'iss'       => 'ai-vmied',
            'iat'       => time(),
            'exp'       => time() + 3600,
            'uid'       => $account['uuid'],
            'name'      => $account['name'],
            "affiliate" => $account["affiliate"],
            "token"     => random_secret(),
            "ip"        => $_SERVER['REMOTE_ADDR'],
            "agent"     => $_SERVER["HTTP_USER_AGENT"],
        ];
        $jwt = \Firebase\JWT\JWT::encode($payload, $key, 'HS256');

        $getLogins = app()->db->get("accounts_login","*",[
            "account"  => $account['uuid'],
            "agent"     => $payload['agent'],
            "deleted"   => 0,
        ]);

        $accounts_logs = [
            "account"   => $payload['uid'],
            "ip"        =>  $payload['ip'],
            "token"     =>  $payload['token'],
            "agent"     =>  $payload["agent"],
        ];
        if($getLogins){
            app()->db->update("accounts_login",$accounts_logs,["id"=>$getLogins['id']]);
        }
        else {
            app()->db->insert("accounts_login",$accounts_logs);
        }

        app()->cookie->set('token', $jwt, 3600);
        
        return $jwt;
    }

    public function Logout() {

        app()->session->forget("account");
        app()->cookie->forget('token');

        if (app()->request->isAjax()) {
            return response()->json([
                'redirect' => '/login',
                'status'   => 'success',
                'toast'    => 'Bạn đã đăng xuất thành công!'
            ]);
        }
        header('Location: /login');
        exit;
    }
    
    public function RegisterVip() {
        $validate = app()->validate(
            [
                'name'         => 'required|min:2',
                'email'        => 'required|email',
                'password'     => 'required|min:6',
                'organization' => 'required'
            ],
            [],
            ['name' => 'Người đại diện', 'email' => 'Email', 'password' => 'Mật khẩu', 'organization' => 'Tên Trường/Đơn vị']
        );
    
        if ($validate->fails()) {
            return response()->json(['status'  => 'error', 'alert' => $validate->first()]);
        }
    
        $email    = app()->xss->clean(request('email'));
        $name     = app()->xss->clean(request('name'));
        $org      = app()->xss->clean(request('organization'));
        $password = request('password');
    
        $account = app()->db->get("accounts", ["email"], ["email" => $email]);
        if ($account) {
            return response()->json(['status' => 'error', 'alert' => 'Email này đã được sử dụng.']);
        }
    
        // Xử lý upload logo
        $avatar = '';
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
            $uploadDir = 'public/uploads/avatar/';
            
            // Tìm đúng đường dẫn vật lý trên server (Lùi 2 cấp từ App/Controllers ra thư mục gốc)
            $rootPath = dirname(__DIR__, 2) . '/';
            $fullDir  = $rootPath . $uploadDir;

            if (!is_dir($fullDir)) {
                mkdir($fullDir, 0755, true);
            }

            // Lấy tên file gốc thay thế các ký tự đặc biệt/khoảng trắng để tránh lỗi URL 404
            $safeName = preg_replace('/[^a-zA-Z0-9.\-_]/', '', basename($_FILES['avatar']['name']));
            $filename = uniqid() . '-' . $safeName;
            $destination = $fullDir . $filename;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
                $avatar = '/uploads/avatar/' . $filename;
            }
        }
    
        $finalStatus = false;
        app()->db->action(function($db) use ($name, $email, $password, $org, $avatar, &$finalStatus) {
            $insertData = [
                "type"         => 2, // Tài khoản VIP
                "uuid"         => uuid(),
                "name"         => $name,
                "email"        => $email,
                "password"     => password_hash($password, PASSWORD_DEFAULT),
                "status"       => 0, // Chờ Admin duyệt
                "avatar"       => $avatar,
                "organization" => $org,
                "affiliate"    => random_secret(8, 'numeric'),
                "ref_by"       => 0,
            ];
    
            $accountQuery = $db->insert("accounts", $insertData);
            if (!$accountQuery) return false;
            
            $accountId = $db->id();
            $db->insert("points", ["account" => $insertData['uuid'], "points" => 0]);
            $db->insert("wallets", ["account" => $accountId, "balance" => 0]);
    
            $finalStatus = true;
            return true;
        });
    
        if ($finalStatus) {
            return response()->json([
                'status' => 'success',
                'alert'  => 'Đăng ký Đơn vị thành công! Vui lòng chờ Ban Quản Trị xét duyệt.',
                'redirect' => '/login' // Không đăng nhập ngay vì cần chờ duyệt
            ]);
        }
    
        return response()->json(['status' => 'error', 'alert'  => 'Có lỗi xảy ra, vui lòng thử lại.']);
    }
}