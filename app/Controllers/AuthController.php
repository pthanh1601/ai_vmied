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
        if ($account['status'] === 0 || $account['deleted'] === 1) {
            return response()->json([
                'status'  => 'error',
                'alert' => 'Tài khoản này đã bị vô hiệu hóa hoặc đã bị xóa.'
            ], 401);
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
            $referrer = app()->db->get("accounts", ["id", "uuid", "affiliate"], ["affiliate" => $refCode, "deleted[!]" => 1, "status" => 1]);
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

        app()->session->set('account',[
            "uuid" => $account['uuid'],
            "name" => $account['name'],
            "avatar" => $account['avatar'],
            "email" => $account['email'],
            "point" => $account['point'],
            "affiliate" => $account['affiliate'],
            "type" => $account['type'] == 0 ? 'Thành viên' : 'Quản trị',
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
}