<?php
namespace App\Controllers;

class AdminController
{
    protected $app;

    public function __construct()
    {
        $this->app = app();
    }

    public function Vips() {
        $user = $this->app->request->user;
        
        // Nếu là VIP, đẩy sang trang Members, không cho xem danh sách VIP
        if ($user->type == 2) {
            header("Location: /admin/members");
            exit;
        }
    
        $vips = $this->app->db->select("accounts", [
            "[>]points" => ["uuid" => "account"]
        ], [
            "accounts.id", "accounts.uuid", "accounts.name", "accounts.email", "accounts.status",
            "accounts.organization", "accounts.avatar", "accounts.date", "accounts.affiliate",
            "points.points(point)"
        ], [
            "accounts.deleted" => 0,
            "accounts.type" => 2, // Chỉ lấy VIP
            "ORDER" => ["accounts.date" => "DESC"]
        ]);
    
        return view('admin/vips', [
            'title' => 'Quản lý Đơn vị Liên kết (VIP)',
            'vips' => $vips,
            'user'  => $user
        ]);
    }
    
    public function Members() {
        $user = $this->app->request->user;
        $filterVipRef = request('vip_ref'); // Lấy mã giới thiệu từ URL nếu Admin muốn lọc
    
        $conditions = [
            "accounts.deleted" => 0,
            "accounts.type" => 0, // Chỉ lấy tài khoản thường
            "ORDER" => ["accounts.date" => "DESC"]
        ];
    
        // PHÂN QUYỀN HIỂN THỊ
        if ($user->type == 2) {
            // Nếu là VIP: Chỉ lấy những user có ref_by bằng affiliate của VIP này
            $conditions["accounts.ref_by"] = $user->affiliate;
        } elseif ($user->type == 1 && !empty($filterVipRef)) {
            // Nếu là Admin và có chọn bộ lọc: Lọc theo mã VIP
            $conditions["accounts.ref_by"] = $filterVipRef;
        }
    
        $members = $this->app->db->select("accounts", [
            "[>]points" => ["uuid" => "account"]
        ], [
            "accounts.id", "accounts.uuid", "accounts.name", "accounts.email", 
            "accounts.avatar", "accounts.date", "accounts.ref_by", "accounts.status",
            "points.points(point)"
        ], $conditions);
    
        // Dành cho Admin: Lấy thêm danh sách VIP để làm thẻ <select> bộ lọc
        $vipList = [];
        if ($user->type == 1) {
            $vipList = $this->app->db->select("accounts", ["name", "organization", "affiliate"], ["type" => 2, "deleted" => 0]);
        }
    
        return view('admin/members', [
            'title' => 'Quản lý Học viên / Giảng viên',
            'members' => $members,
            'user'  => $user,
            'vipList' => $vipList,
            'currentFilter' => $filterVipRef
        ]);
    }
    
    public function MemberHistory() {
        $user = $this->app->request->user;
        $memberUuid = request('uuid');
    
        // Lấy thông tin member
        $member = $this->app->db->get("accounts", ["uuid", "name", "ref_by"], ["uuid" => $memberUuid, "type" => 0]);
        if (!$member) {
            return "Người dùng không tồn tại.";
        }
    
        // Bảo mật: Nếu là VIP, chỉ cho xem lịch sử của member thuộc tuyến dưới
        if ($user->type == 2 && $member['ref_by'] != $user->affiliate) {
            return "Bạn không có quyền xem lịch sử của người này.";
        }
    
        $history = $this->app->db->select("originality_history", [
            "id", "title", "type", "word_count", "points_used", "created_at",
            "ai_score", "plag_score", "grammar_errors", "readability_score"
        ], [
            "account_uuid" => $memberUuid,
            "ORDER" => ["created_at" => "DESC"]
        ]);
    
        return view('admin/member_history', [
            'title' => 'Lịch sử quét của ' . $member['name'],
            'history' => $history,
            'user' => $user
        ]);
    }
    
    public function Statistics() {
        $user = $this->app->request->user;

        // Bảo mật: Chỉ Admin mới được xem thống kê tài chính
        if ($user->type != 1) {
            header("Location: /admin/members");
            exit;
        }

        // 1. Tổng tiền mặt khách đã nạp thành công
        $totalDeposit = $this->app->db->sum("transactions", "amount", [
            "status" => 1, 
            "type" => "deposit"
        ]);

        // 2. Thống kê Doanh thu và Chi phí vốn (Chỉ từ Copyscape)
        $stats = $this->app->db->query("
            SELECT 
                COUNT(id) as total_scans,
                SUM(points_used) as total_revenue,
                SUM(capitalCost) as total_cost
            FROM originality_history
            WHERE status = 'done'
        ")->fetch();

        $totalRevenue = $stats['total_revenue'] ?? 0; // Tiền thu của khách
        $totalCost    = $stats['total_cost'] ?? 0;    // Tiền trả cho Copyscape
        $totalProfit  = $totalRevenue - $totalCost;   // Lợi nhuận
        
        return view('admin/statistics', [
            'title' => 'Thống kê Tài chính & Vận hành',
            'user'  => $user,
            'totalDeposit' => $totalDeposit ?? 0,
            'totalRevenue' => $totalRevenue,
            'totalCost'    => $totalCost,
            'totalProfit'  => $totalProfit,
            'totalScans'   => $stats['total_scans'] ?? 0
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
    
    public function ToggleStatus() {
        $uuid = request('uuid');
        $status = (int) request('status'); // 1: Hoạt động, 0: Vô hiệu hóa
    
        if (empty($uuid)) {
            return response()->json(['status' => 'error', 'alert' => 'Thiếu thông tin người dùng.']);
        }
    
        $this->app->db->update("accounts", ["status" => $status], ["uuid" => $uuid]);
    
        return response()->json([
            'status' => 'success',
            'alert' => 'Đã cập nhật trạng thái tài khoản'
        ]);
    }
}
