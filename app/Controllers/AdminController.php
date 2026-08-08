<?php
namespace App\Controllers;

class AdminController
{
    protected $app;

    public function __construct()
    {
        $this->app = app();
    }

    public function Users() {
        $users = $this->app->db->select("accounts", [
            "[>]points" => ["uuid" => "account"]
        ], [
            "accounts.id", "accounts.uuid", "accounts.name", "accounts.email", 
            "accounts.type", "accounts.organization", "accounts.avatar", "accounts.date",
            "points.points(point)"
        ], [
            "accounts.deleted" => 0,
            "ORDER" => ["accounts.date" => "DESC"]
        ]);

        return view('admin/users', [
            'title' => 'Quản lý người dùng',
            'users' => $users,
            'user'  => $this->app->request->user
        ]);
    }

    public function Statistics() {
        return view('admin/statistics', [
            'title' => 'Thống kê',
            'user'  => $this->app->request->user
        ]);
    }

    public function SetVip() {
        $uuid = request('uuid');
        $type = request('type'); // 2: VIP Affiliate, 0: Normal, 1: Admin
        $organization = request('organization');
        
        $updateData = [
            'type' => (int)$type,
            'organization' => $organization
        ];

        // Process avatar upload if any
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
            $uploadDir = 'public/uploads/avatar/';
            if (!is_dir(dirname(__DIR__, 2) . '/' . $uploadDir)) {
                mkdir(dirname(__DIR__, 2) . '/' . $uploadDir, 0755, true);
            }
            $filename = uniqid() . '-' . basename($_FILES['avatar']['name']);
            $destination = dirname(__DIR__, 2) . '/' . $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
                $updateData['avatar'] = '/uploads/avatar/' . $filename;
            }
        }
        
        $this->app->db->update("accounts", $updateData, ["uuid" => $uuid]);

        return response()->json([
            'status' => 'success',
            'alert' => 'Đã cập nhật tài khoản thành công',
            'reload' => true
        ]);
    }

    public function AddUser() {
        $name = app()->xss->clean(request('name'));
        $email = app()->xss->clean(request('email'));
        $password = request('password');
        $type = request('type');
        $organization = request('organization') ?? '';
        $point = (int) request('point');

        if (empty($name) || empty($email)) {
            return response()->json(['status' => 'error', 'alert' => 'Vui lòng nhập đầy đủ tên và email.']);
        }

        if (empty($password)) {
            $password = '123456';
        }

        $checkExist = $this->app->db->has("accounts", ["email" => $email]);
        if ($checkExist) {
            return response()->json(['status' => 'error', 'alert' => 'Email này đã tồn tại trên hệ thống.']);
        }

        $avatar = '';
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
            $uploadDir = 'public/uploads/avatar/';
            if (!is_dir(dirname(__DIR__, 2) . '/' . $uploadDir)) {
                mkdir(dirname(__DIR__, 2) . '/' . $uploadDir, 0755, true);
            }
            $filename = uniqid() . '-' . basename($_FILES['avatar']['name']);
            $destination = dirname(__DIR__, 2) . '/' . $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
                $avatar = '/uploads/avatar/' . $filename;
            }
        }

        $insertData = [
            "uuid" => uuid(),
            "name" => $name,
            "email" => $email,
            "password" => password_hash($password, PASSWORD_DEFAULT),
            "type" => (int)$type,
            "organization" => $organization,
            "status" => 1,
            "deleted" => 0,
            "avatar" => $avatar,
            "affiliate" => random_secret(8, 'numeric'),
            "ref_by" => 0
        ];

        $finalStatus = false;
        
        $this->app->db->action(function($db) use ($insertData, $point, &$finalStatus) {
            $accountQuery = $db->insert("accounts", $insertData);
            if (!$accountQuery || $accountQuery->rowCount() === 0) return false;
            
            $accountId = $db->id();
            
            $pointQuery = $db->insert("points", [
                "account" => $insertData['uuid'],
                "points" => $point
            ]);
            if (!$pointQuery || $pointQuery->rowCount() === 0) return false;
            
            $walletQuery = $db->insert("wallets", [
                "account" => $accountId,
                "balance" => 0
            ]);
            if (!$walletQuery || $walletQuery->rowCount() === 0) return false;
            
            $finalStatus = true;
            return true;
        });

        if ($finalStatus) {
            return response()->json(['status' => 'success', 'alert' => 'Thêm người dùng thành công', 'reload' => true]);
        }
        
        return response()->json(['status' => 'error', 'alert' => 'Có lỗi xảy ra, vui lòng thử lại!']);
    }

    public function UpdateUser() {
        $uuid = request('uuid');
        $name = app()->xss->clean(request('name'));
        $email = app()->xss->clean(request('email'));
        $password = request('password');
        $type = request('type');
        $organization = request('organization') ?? '';
        $point = request('point');

        $updateData = [
            'name' => $name,
            'email' => $email,
            'type' => (int)$type,
            'organization' => $organization
        ];

        if (!empty($password)) {
            $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        
        // Process avatar upload if any
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
            $uploadDir = 'public/uploads/avatar/';
            if (!is_dir(dirname(__DIR__, 2) . '/' . $uploadDir)) {
                mkdir(dirname(__DIR__, 2) . '/' . $uploadDir, 0755, true);
            }
            $filename = uniqid() . '-' . basename($_FILES['avatar']['name']);
            $destination = dirname(__DIR__, 2) . '/' . $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
                $updateData['avatar'] = '/uploads/avatar/' . $filename;
            }
        } elseif (request('delete_avatar') == '1') {
            $updateData['avatar'] = '';
        }

        $this->app->db->update("accounts", $updateData, ["uuid" => $uuid]);

        // Cập nhật số dư point nếu có truyền lên
        if ($point !== null && $point !== '') {
            $this->app->db->update("points", ["points" => (int)$point], ["account" => $uuid]);
        }

        return response()->json([
            'status' => 'success',
            'alert' => 'Đã cập nhật tài khoản thành công',
            'reload' => true
        ]);
    }

    public function DeleteUser() {
        $uuid = request('uuid');
        if (empty($uuid)) {
            return response()->json(['status' => 'error', 'alert' => 'Thiếu thông tin người dùng.']);
        }

        $this->app->db->update("accounts", ["deleted" => 1], ["uuid" => $uuid]);

        return response()->json([
            'status' => 'success',
            'alert' => 'Đã xóa tài khoản thành công',
            'reload' => true
        ]);
    }
}
