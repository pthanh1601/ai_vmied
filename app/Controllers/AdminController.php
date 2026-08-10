<?php
namespace App\Controllers;

class AdminController
{
    protected $app;
    protected $user;

    public function __construct()
    {
        $this->app = app();
        $this->user = $this->app->request->user;

        // BẢO MẬT CẤP 1: Chặn đứng Học viên (type = 0)
        // Bất kỳ truy cập nào vào AdminController mà không phải Admin hoặc VIP sẽ bị đá về trang chủ
        if (!isset($this->user) || $this->user->type == 0) {
            header("Location: /app");
            exit;
        }
    }

    /**
     * BẢO MẬT CẤP 2: Kiểm tra loại tài khoản có phải Admin (type = 1) hay không
     */
    private function requireAdmin() {
        if ($this->user->type != 1) {
            if ($this->app->request->isAjax()) {
                echo json_encode(['status' => 'error', 'alert' => 'Bạn không có quyền thực hiện thao tác này.']);
                exit;
            }
            header("Location: /app");
            exit;
        }
    }
    
    /**
     * HÀM KIỂM TRA MỘT ACTION CÓ ĐƯỢC PHÉP TRUY CẬP HAY KHÔNG
     */
    private function checkPermission($actionCode) {
        // Super Admin (role_id rỗng/null) có toàn quyền
        if (empty($this->user->role_id)) {
            return true;
        }

        // Lấy JSON permissions của Role từ Database
        $role = $this->app->db->get("permissions", "permissions", [
            "id" => $this->user->role_id,
            "deleted" => 0
        ]);

        if (!$role) {
            return false;
        }

        $perms = json_decode($role, true) ?: [];

        // Kiểm tra xem action_code có nằm trong mảng quyền hay không
        return isset($perms[$actionCode]) || in_array($actionCode, $perms);
    }

    /**
     * HÀM BẮT BUỘC Phải có quyền mới được chạy tiếp
     * Nếu không có quyền: Thông báo lỗi (dành cho AJAX) hoặc Hiển thị giao diện Cảnh báo (dành cho URL)
     */
    private function requirePermission($actionCode) {
        $this->requireAdmin(); // Bắt buộc phải là Admin (type = 1)
        
        if (!$this->checkPermission($actionCode)) {
            if ($this->app->request->isAjax()) {
                echo json_encode(['status' => 'error', 'alert' => 'Bạn không có quyền thực hiện thao tác này.']);
                exit;
            }
            
            // Render trực tiếp giao diện thông báo KHÔNG ĐỦ QUYỀN thay vì redirect (đá về trang khác)
            http_response_code(403);
            echo view('admin/no_permission', [
                'title' => 'Truy cập bị từ chối',
                'user'  => $this->user,
                'actionCode' => $actionCode
            ]);
            exit;
        }
    }

    // =========================================================================
    // KHU VỰC 1: DÀNH RIÊNG CHO ADMIN (QUẢN TRỊ VIÊN) - TYPE 1
    // =========================================================================

    public function Vips() {
        $this->requirePermission('vips');

        $q = trim(request('q') ?? ''); 

        $where = [
            "deleted" => 0,
            "type" => 2
        ];

        if (!empty($q)) {
            $where["OR"] = [
                "name[~]"         => $q,
                "email[~]"        => $q,
                "organization[~]" => $q
            ];
        }

        $totalVips = $this->app->db->count("accounts", $where);

        $perPage = 20; 
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        
        $totalPages = ceil($totalVips / $perPage);
        if ($totalPages < 1) $totalPages = 1;
        $offset = ($page - 1) * $perPage;

        $queryConditions = [];
        foreach($where as $key => $value) {
            if ($key === 'OR') {
                $queryConditions["OR"] = [
                    "accounts.name[~]"         => $q,
                    "accounts.email[~]"        => $q,
                    "accounts.organization[~]" => $q
                ];
            } else {
                $queryConditions["accounts." . $key] = $value;
            }
        }
        $queryConditions["ORDER"] = ["accounts.date" => "DESC"];
        $queryConditions["LIMIT"] = [$offset, $perPage];

        $vips = $this->app->db->select("accounts", [
            "[>]points" => ["uuid" => "account"]
        ], [
            "accounts.id", "accounts.uuid", "accounts.name", "accounts.email", "accounts.status",
            "accounts.organization", "accounts.avatar", "accounts.date", "accounts.affiliate",
            "points.points(point)"
        ], $queryConditions);
    
        return view('admin/vips', [
            'title'      => 'Quản lý Đơn vị Liên kết (VIP)',
            'vips'       => $vips,
            'user'       => $this->user,
            'page'       => $page,
            'totalPages' => $totalPages,
            'q'          => $q
        ]);
    }
    
    public function Statistics() {
        $this->requirePermission('statistics');

        $totalDeposit = $this->app->db->sum("transactions", "amount", [
            "status" => 1, 
            "type" => "deposit"
        ]);

        $stats = $this->app->db->query("
            SELECT 
                COUNT(id) as total_scans,
                SUM(points_used) as total_revenue,
                SUM(capitalCost) as total_cost
            FROM originality_history
            WHERE status = 'done'
        ")->fetch();

        $totalRevenue = $stats['total_revenue'] ?? 0;
        $totalCost    = $stats['total_cost'] ?? 0;   
        $totalProfit  = $totalRevenue - $totalCost;  
        $totalScans   = $stats['total_scans'] ?? 0;

        $perPage = 20; 
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        
        $totalPages = ceil($totalScans / $perPage);
        if ($totalPages < 1) $totalPages = 1;
        $offset = ($page - 1) * $perPage;

        $histories = $this->app->db->select("originality_history", [
            "[>]accounts" => ["account_uuid" => "uuid"]
        ], [
            "originality_history.id",
            "originality_history.title",
            "originality_history.type",
            "originality_history.word_count",
            "originality_history.capitalCost",
            "originality_history.points_used",
            "originality_history.created_at",
            "accounts.name",
            "accounts.email"
        ], [
            "ORDER" => ["originality_history.created_at" => "DESC"],
            "LIMIT" => [$offset, $perPage]
        ]);

        return view('admin/statistics', [
            'title'        => 'Thống kê Tài chính & Vận hành',
            'user'         => $this->user,
            'totalDeposit' => $totalDeposit ?? 0,
            'totalRevenue' => $totalRevenue,
            'totalCost'    => $totalCost,
            'totalProfit'  => $totalProfit,
            'totalScans'   => $totalScans,
            'histories'    => $histories ?: [],
            'page'         => $page,
            'totalPages'   => $totalPages
        ]);
    }

    public function SetVip() {
        $this->requirePermission('vips.edit');

        $uuid = request('uuid');
        $type = request('type'); 
        $organization = request('organization');
        
        $updateData = [
            'type' => (int)$type,
            'organization' => $organization
        ];

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
            'alert'  => 'Đã cập nhật tài khoản thành công',
            'reload' => true
        ]);
    }

    public function AddUser() {
        $type = request('type');
        if ($type == 2) $this->requirePermission('vips.add');
        elseif ($type == 1) $this->requirePermission('admins.add');
        else $this->requirePermission('members.add');

        $name = app()->xss->clean(request('name'));
        $email = app()->xss->clean(request('email'));
        $password = request('password');
        $organization = request('organization') ?? '';
        $point = (int) request('point');
        
        $roleId = request('role_id');
        $roleId = ($roleId !== '' && $roleId !== null) ? (int) $roleId : null;
        
        $refBy = request('ref_by') ?? 0;

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
            "uuid"         => uuid(),
            "name"         => $name,
            "email"        => $email,
            "password"     => password_hash($password, PASSWORD_DEFAULT),
            "type"         => (int)$type,
            "organization" => $organization,
            "status"       => 1,
            "deleted"      => 0,
            "avatar"       => $avatar,
            "affiliate"    => random_secret(8, 'numeric'),
            "ref_by"       => $refBy,
            "role_id"      => $roleId
        ];

        $finalStatus = false;
        
        $this->app->db->action(function($db) use ($insertData, $point, &$finalStatus) {
            $accountQuery = $db->insert("accounts", $insertData);
            if (!$accountQuery || $accountQuery->rowCount() === 0) return false;
            
            $accountId = $db->id();
            
            $pointQuery = $db->insert("points", [
                "account" => $insertData['uuid'],
                "points"  => $point
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
        $type = request('type');
        if ($type == 2) $this->requirePermission('vips.edit');
        elseif ($type == 1) $this->requirePermission('admins.edit');
        else $this->requirePermission('members.edit');

        $uuid = request('uuid');
        $name = app()->xss->clean(request('name'));
        $email = app()->xss->clean(request('email'));
        $password = request('password');
        $organization = request('organization') ?? '';
        $point = request('point');
        $roleId = request('role_id');

        $updateData = [
            'name'         => $name,
            'email'        => $email,
            'type'         => (int)$type,
            'organization' => $organization
        ];
        
        if ($roleId !== null) {
            $updateData['role_id'] = ($roleId !== '') ? (int) $roleId : null;
        }

        if (!empty($password)) {
            $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        
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

        if ($point !== null && $point !== '') {
            $this->app->db->update("points", ["points" => (int)$point], ["account" => $uuid]);
        }

        return response()->json([
            'status' => 'success',
            'alert'  => 'Đã cập nhật tài khoản thành công',
            'reload' => true
        ]);
    }

    public function DeleteUser() {
        $uuid = request('uuid');
        if (empty($uuid)) {
            return response()->json(['status' => 'error', 'alert' => 'Thiếu thông tin người dùng.']);
        }

        // Lấy thông tin tài khoản bị xóa để check đúng quyền
        $targetUser = $this->app->db->get("accounts", ["type"], ["uuid" => $uuid]);
        if ($targetUser) {
            if ($targetUser['type'] == 2) $this->requirePermission('vips.delete');
            elseif ($targetUser['type'] == 1) $this->requirePermission('admins.delete');
            else $this->requirePermission('members.delete');
        } else {
            $this->requirePermission('members.delete');
        }

        $this->app->db->update("accounts", ["deleted" => 1], ["uuid" => $uuid]);

        return response()->json([
            'status' => 'success',
            'alert'  => 'Đã xóa tài khoản thành công',
            'reload' => true
        ]);
    }
    
    public function ToggleStatus() {
        $this->requirePermission('members.status');

        $uuid = request('uuid');
        $status = (int) request('status'); 
    
        if (empty($uuid)) {
            return response()->json(['status' => 'error', 'alert' => 'Thiếu thông tin người dùng.']);
        }
    
        $this->app->db->update("accounts", ["status" => $status], ["uuid" => $uuid]);
    
        return response()->json([
            'status' => 'success',
            'alert'  => 'Đã cập nhật trạng thái tài khoản'
        ]);
    }


    // =========================================================================
    // KHU VỰC 2: CHỈ DÀNH RIÊNG CHO ADMIN (TYPE 1)
    // =========================================================================

    public function Members() {
        // Bắt buộc có quyền 'members' (Bên trong đã check requireAdmin, chặn hoàn toàn type 2 & type 0)
        $this->requirePermission('members');

        $filterVipRef = request('vip_ref'); 
        $q = trim(request('q') ?? ''); 
    
        $where = [
            "deleted" => 0,
            "type" => 0
        ];
    
        // Lọc danh sách học viên theo từng Đơn vị/VIP (nếu Admin chọn bộ lọc)
        if (!empty($filterVipRef)) {
            $where["ref_by"] = $filterVipRef;
        }

        // Lọc theo từ khóa tìm kiếm (Tên hoặc Email)
        if (!empty($q)) {
            $where["OR"] = [
                "name[~]"  => $q,
                "email[~]" => $q
            ];
        }

        $totalMembers = $this->app->db->count("accounts", $where);

        $perPage = 20; 
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        
        $totalPages = ceil($totalMembers / $perPage);
        if ($totalPages < 1) $totalPages = 1;
        $offset = ($page - 1) * $perPage;

        $queryConditions = [];
        foreach($where as $key => $value) {
            if ($key === 'OR') {
                $queryConditions["OR"] = [
                    "accounts.name[~]"  => $q,
                    "accounts.email[~]" => $q
                ];
            } else {
                $queryConditions["accounts." . $key] = $value;
            }
        }
        $queryConditions["ORDER"] = ["accounts.date" => "DESC"];
        $queryConditions["LIMIT"] = [$offset, $perPage]; 
    
        $members = $this->app->db->select("accounts", [
            "[>]points" => ["uuid" => "account"]
        ], [
            "accounts.id", "accounts.uuid", "accounts.name", "accounts.email", 
            "accounts.avatar", "accounts.date", "accounts.ref_by", "accounts.status",
            "points.points(point)"
        ], $queryConditions);
    
        // Lấy danh sách VIP để làm thẻ <select> bộ lọc cho Admin
        $vipList = $this->app->db->select("accounts", ["name", "organization", "affiliate"], ["type" => 2, "deleted" => 0]);
    
        return view('admin/members', [
            'title'         => 'Quản lý Học viên / Giảng viên',
            'members'       => $members,
            'user'          => $this->user,
            'vipList'       => $vipList,
            'currentFilter' => $filterVipRef,
            'page'          => $page,
            'totalPages'    => $totalPages,
            'q'             => $q
        ]);
    }
    
    public function MemberHistory() {
        // Bắt buộc có quyền xem lịch sử (Chặn hoàn toàn type 2 & type 0)
        $this->requirePermission('members.history');
        
        $memberUuid = request('uuid');
    
        $member = $this->app->db->get("accounts", ["uuid", "name", "ref_by"], ["uuid" => $memberUuid, "type" => 0]);
        if (!$member) {
            return "Người dùng không tồn tại.";
        }
    
        $history = $this->app->db->select("originality_history", [
            "id", "title", "type", "word_count", "points_used", "created_at",
            "ai_score", "plag_score", "grammar_errors", "readability_score"
        ], [
            "account_uuid" => $memberUuid,
            "ORDER" => ["created_at" => "DESC"]
        ]);
    
        return view('admin/member_history', [
            'title'   => 'Lịch sử quét của ' . $member['name'],
            'history' => $history,
            'user'    => $this->user
        ]);
    }

    // =========================================================================
    // QUẢN LÝ QUẢN TRỊ VIÊN (ADMINS - TYPE 1)
    // =========================================================================

    public function Admins() {
        $this->requirePermission('admins');

        $q = trim(request('q') ?? ''); 

        $where = [
            "deleted" => 0,
            "type"    => 1
        ];

        if (!empty($q)) {
            $where["OR"] = [
                "name[~]"  => $q,
                "email[~]" => $q
            ];
        }

        $totalAdmins = $this->app->db->count("accounts", $where);

        $perPage = 20; 
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        
        $totalPages = ceil($totalAdmins / $perPage);
        if ($totalPages < 1) $totalPages = 1;
        $offset = ($page - 1) * $perPage;

        $queryConditions = [];
        foreach($where as $key => $value) {
            if ($key === 'OR') {
                $queryConditions["OR"] = [
                    "accounts.name[~]"  => $q,
                    "accounts.email[~]" => $q
                ];
            } else {
                $queryConditions["accounts." . $key] = $value;
            }
        }
        $queryConditions["ORDER"] = ["accounts.date" => "DESC"];
        $queryConditions["LIMIT"] = [$offset, $perPage]; 

        $admins = $this->app->db->select("accounts", [
            "[>]permissions" => ["role_id" => "id"] 
        ], [
            "accounts.id", "accounts.uuid", "accounts.name", "accounts.email", 
            "accounts.avatar", "accounts.date", "accounts.status", "accounts.role_id",
            "permissions.name(role_name)" 
        ], $queryConditions);

        $roles = $this->app->db->select("permissions", ["id", "name"], ["deleted" => 0]);

        return view('admin/admins', [
            'title'      => 'Quản lý Quản trị viên',
            'admins'     => $admins,
            'roles'      => $roles,
            'user'       => $this->user,
            'page'       => $page,
            'totalPages' => $totalPages,
            'q'          => $q
        ]);
    }
    
    // =========================================================================
    // QUẢN LÝ NHÓM QUYỀN (ROLES / PERMISSIONS)
    // =========================================================================

    public function Roles() {
        $this->requirePermission('roles');

        $roles = $this->app->db->select("permissions", "*", [
            "deleted" => 0,
            "ORDER"   => ["id" => "ASC"]
        ]);

        $availablePermissions = [
            'Quản lý Đơn vị Liên kết (VIP)' => [
                'vips'         => 'Xem danh sách Đơn vị VIP',
                'vips.add'     => 'Thêm Đơn vị VIP mới',
                'vips.edit'    => 'Chỉnh sửa Đơn vị VIP',
                'vips.delete'  => 'Xóa Đơn vị VIP',
            ],
            'Quản lý Học viên / Giảng viên' => [
                'members'         => 'Xem danh sách Học viên',
                'members.add'     => 'Thêm Học viên mới',
                'members.edit'    => 'Chỉnh sửa Học viên',
                'members.delete'  => 'Xóa Học viên',
                'members.history' => 'Xem lịch sử quét bài của Học viên',
                'members.status'  => 'Mở/Khóa tài khoản Học viên',
            ],
            'Thống kê Tài chính' => [
                'statistics'   => 'Xem thống kê doanh thu & chi phí API',
            ],
            'Quản trị Hệ thống & Phân quyền' => [
                'admins'          => 'Xem danh sách Quản trị viên',
                'admins.add'      => 'Cấp tài khoản Quản trị viên',
                'admins.edit'     => 'Sửa tài khoản Quản trị viên',
                'admins.delete'   => 'Xóa Quản trị viên',
                'roles'           => 'Xem danh sách Nhóm quyền',
                'roles.add'       => 'Tạo Nhóm quyền mới',
                'roles.edit'      => 'Chỉnh sửa Nhóm quyền',
                'roles.delete'    => 'Xóa Nhóm quyền',
            ]
        ];

        return view('admin/roles', [
            'title'      => 'Quản lý Nhóm Quyền',
            'roles'      => $roles,
            'user'       => $this->user,
            'availPerms' => $availablePermissions
        ]);
    }

    public function AddRole() {
        $this->requirePermission('roles.add');

        $name = app()->xss->clean(request('name'));
        
        $permsArray = request('permissions') ?? [];
        $permsJson = !empty($permsArray) ? json_encode(array_combine($permsArray, $permsArray)) : '{}';

        if (empty($name)) {
            return response()->json(['status' => 'error', 'alert' => 'Vui lòng nhập tên nhóm quyền.']);
        }

        $this->app->db->insert("permissions", [
            "name"        => $name,
            "permissions" => $permsJson,
            "status"      => 'A',
            "active"      => uuid(),
            "deleted"     => 0
        ]);

        return response()->json(['status' => 'success', 'alert' => 'Tạo Nhóm quyền thành công', 'reload' => true]);
    }

    public function UpdateRole() {
        $this->requirePermission('roles.edit');

        $id = (int) request('id');
        $name = app()->xss->clean(request('name'));
        
        $permsArray = request('permissions') ?? [];
        $permsJson = !empty($permsArray) ? json_encode(array_combine($permsArray, $permsArray)) : '{}';

        $this->app->db->update("permissions", [
            "name"        => $name,
            "permissions" => $permsJson
        ], [
            "id" => $id
        ]);

        return response()->json(['status' => 'success', 'alert' => 'Cập nhật Nhóm quyền thành công', 'reload' => true]);
    }

    public function DeleteRole() {
        $this->requirePermission('roles.delete');

        $id = (int) request('id');
        
        $this->app->db->update("permissions", ["deleted" => 1], ["id" => $id]);

        return response()->json(['status' => 'success', 'alert' => 'Đã xóa nhóm quyền', 'reload' => true]);
    }
    
    // =========================================================================
    // QUẢN LÝ TÀI CHÍNH, LỊCH SỬ THANH TOÁN & HOA HỒNG VIP TOÀN HỆ THỐNG
    // =========================================================================

    public function UserFinanceHistory() {
        // Kiểm tra quyền Admin
        $this->requirePermission('statistics'); // Hoặc quyền tài chính/statistics

        $tab = request('tab') ?? 'transactions'; // 'transactions', 'commissions', 'withdrawals'
        $q   = trim(request('q') ?? '');

        $perPage = 20;
        $page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset  = ($page - 1) * $perPage;

        $transactions     = [];
        $affiliateHistory = [];
        $withdrawHistory  = [];
        $totalItems       = 0;

        // ---------------------------------------------------------------------
        // TAB 1: LỊCH SỬ NẠP TIỀN CỦA TẤT CẢ TÀI KHOẢN
        // ---------------------------------------------------------------------
        if ($tab === 'transactions') {
            $where = ["[>]accounts" => ["account" => "uuid"]];
            $conditions = ["transactions.id[>]" => 0];

            if (!empty($q)) {
                $conditions["OR"] = [
                    "accounts.name[~]"  => $q,
                    "accounts.email[~]" => $q,
                    "transactions.code[~]" => $q
                ];
            }

            $totalItems = $this->app->db->count("transactions", $where, "transactions.id", $conditions);
            $totalPages = ceil($totalItems / $perPage) ?: 1;

            $conditions["ORDER"] = ["transactions.created_at" => "DESC"];
            $conditions["LIMIT"] = [$offset, $perPage];

            $transactions = $this->app->db->select("transactions", [
                "[>]accounts" => ["account" => "uuid"]
            ], [
                "transactions.id", "transactions.code", "transactions.amount", 
                "transactions.type", "transactions.status", "transactions.created_at",
                "accounts.name(user_name)", "accounts.email(user_email)", "accounts.uuid(user_uuid)"
            ], $conditions);
        }

        // ---------------------------------------------------------------------
        // TAB 2: BIẾN ĐỘNG VÍ HOA HỒNG TẤT CẢ VIP (WALLET_HISTORYS)
        // ---------------------------------------------------------------------
        elseif ($tab === 'commissions') {
            $conditions = ["wallet_historys.id[>]" => 0];

            if (!empty($q)) {
                $conditions["OR"] = [
                    "accounts.name[~]"  => $q,
                    "accounts.email[~]" => $q
                ];
            }

            $totalItems = $this->app->db->count("wallet_historys", [
                "[>]accounts" => ["account" => "id"]
            ], "wallet_historys.id", $conditions);
            $totalPages = ceil($totalItems / $perPage) ?: 1;

            // Sửa created_at thành date
            $conditions["ORDER"] = ["wallet_historys.date" => "DESC"];
            $conditions["LIMIT"] = [$offset, $perPage];

            $affiliateHistory = $this->app->db->select("wallet_historys", [
                "[>]accounts" => ["account" => "id"]
            ], [
                // Xóa description, thay bằng code và type. Thay created_at bằng date.
                "wallet_historys.id", "wallet_historys.amount", 
                "wallet_historys.code", "wallet_historys.type", "wallet_historys.date",
                "accounts.name(user_name)", "accounts.email(user_email)", "accounts.organization"
            ], $conditions);
        }

        // ---------------------------------------------------------------------
        // TAB 3: YÊU CẦU RÚT TIỀN HOA HỒNG TẤT CẢ VIP (WITHDRAWALS)
        // ---------------------------------------------------------------------
        elseif ($tab === 'withdrawals') {
            // Kiểm tra an toàn xem bảng withdrawals có tồn tại không bằng lệnh SHOW TABLES thuần
            $tableExists = $this->app->db->query("SHOW TABLES LIKE 'withdrawals'")->fetchAll();
            if (count($tableExists) > 0) {
                $conditions = ["withdrawals.id[>]" => 0];

                if (!empty($q)) {
                    $conditions["OR"] = [
                        "accounts.name[~]"  => $q,
                        "accounts.email[~]" => $q
                    ];
                }

                $totalItems = $this->app->db->count("withdrawals", [
                    "[>]accounts" => ["account" => "uuid"]
                ], "withdrawals.id", $conditions);
                $totalPages = ceil($totalItems / $perPage) ?: 1;

                $conditions["ORDER"] = ["withdrawals.created_at" => "DESC"];
                $conditions["LIMIT"] = [$offset, $perPage];

                $withdrawHistory = $this->app->db->select("withdrawals", [
                    "[>]accounts" => ["account" => "uuid"]
                ], [
                    "withdrawals.id", "withdrawals.amount", "withdrawals.bank_name",
                    "withdrawals.bank_account", "withdrawals.status", "withdrawals.created_at",
                    "accounts.name(user_name)", "accounts.email(user_email)", "accounts.organization"
                ], $conditions);
            }
        }

        // Thống kê nhanh tổng quan
        $totalDepositSum = $this->app->db->sum("transactions", "amount", [
            "status" => 1,
            "type"   => "deposit"
        ]) ?? 0;
        $totalVipsCount  = $this->app->db->count("accounts", ["type" => 2, "deleted" => 0]);

        return view('admin/user_finance', [
            'title'            => 'Lịch sử Thanh toán & Hoa hồng Toàn hệ thống',
            'user'             => $this->user,
            'currentTab'       => $tab,
            'transactions'     => $transactions ?: [],
            'affiliateHistory' => $affiliateHistory ?: [],
            'withdrawHistory'  => $withdrawHistory ?: [],
            'totalDepositSum'  => $totalDepositSum,
            'totalVipsCount'   => $totalVipsCount,
            'page'             => $page,
            'totalPages'       => $totalPages ?? 1,
            'q'                => $q
        ]);
    }
    
    public function ApproveVip() {
        $this->requirePermission('vips.edit');

        $uuid = request('uuid');
        if (empty($uuid)) {
            return response()->json(['status' => 'error', 'alert' => 'Thiếu thông tin tài khoản.']);
        }

        // Cập nhật trạng thái thành 1 (Đã duyệt)
        $this->app->db->update("accounts", ["status" => 1], [
            "uuid" => $uuid,
            "type" => 2 // Đảm bảo chỉ duyệt tài khoản VIP
        ]);

        return response()->json([
            'status' => 'success',
            'alert'  => 'Đã xét duyệt Đơn vị VIP thành công!',
            'reload' => true
        ]);
    }
}