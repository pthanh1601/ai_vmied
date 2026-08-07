<?php
namespace App\Controllers;

use Neo\Core\Controller;
use Firebase\JWT\JWT;

class AccountController
{
    protected $app;

    public function __construct(){
        $this->app = app();
    }

    public function Account() {
        $user = app()->request->user;
        
        $account = (object) app()->db->get("accounts", [
            "[>]points" => ["uuid" => "account"]
        ],
        [
            "accounts.email",
            "accounts.name",
            "accounts.avatar",
            "accounts.phone",
            "accounts.organization",
            "accounts.type",
            "accounts.affiliate",
            "points.points(point)"
        ],
        [
            "accounts.uuid" => $user->uuid
        ]);
        
        $account->type = $account->type == 0 ? 'Thành Viên' : 'Quản trị';
        
        if (app()->request->isHtmx()) {
            return view('account/account', [
                'user' => $account,
            ]);
        }
        
        header('Location: /app/profile');
        exit;
    }

    public function Profiles() {
        $user = app()->request->user;
        $account = (object) app()->db->get("accounts",[
            "[>]points"=>["uuid"=>"account"]
        ],
        [
            "accounts.id",
            "accounts.email",
            "accounts.name",
            "accounts.avatar",
            "accounts.phone",
            "accounts.organization",
            "accounts.type",
            "accounts.affiliate",
            "points.points(point)"
        ],
        [
            "accounts.uuid"=>$user->uuid
        ]);
        $account->type = $account->type==0?'Thành Viên':'Quản trị';

        $monthlyIncome = app()->db->sum("transactions", "vmied", [
            "account" => [$account->id, $user->uuid],
            "status" => 1,
            "type" => ["deposit", "commission"],
            "created_at[<>]" => [date('Y-m-01 00:00:00'), date('Y-m-t 23:59:59')]
        ]) ?: 0;

        $totalUsed = abs(app()->db->sum("points_historys", "point", [
            "account" => $user->uuid,
            "point[<]" => 0,        
            "deleted" => 0          
        ]) ?: 0);

        $payments = app()->db->select("transactions", "*", [
            "account" => [$account->id, $user->uuid],
            "ORDER" => ["created_at" => "DESC"],
            "LIMIT" => 5
        ]) ?: [];
        
        $walletBalance = app()->db->get("wallets", "balance", [
            "account" => $account->id
        ]) ?? 0;

        return view('account/profiles', [
            'user' => $account,
            'monthlyIncome' => $monthlyIncome,
            'totalUsed' => $totalUsed,
            'walletBalance' => $walletBalance,
            'payments' => $payments
        ]);
    }

    public function UpdateInformation(){
        $userId = app()->request->user->uuid;
        if (!$userId) {
            return response()->json(['status' => 'error', 'alert' => 'Vui lòng đăng nhập'], 401);
        }

        // 2. Validate dữ liệu
        $validator = app()->validate(
            [
                'name' => 'required',
                'phone' => 'required', // Ví dụ thêm validate số điện thoại
            ],
            [
                'name.required' => 'Tên không được để trống',
                'phone.required' => 'Số điện thoại không được để trống',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'alert' => $validator->first(),
            ], 400);
        }

        // 3. Chuẩn bị dữ liệu update (Clean XSS)
        $updateData = [
            "name"         => app()->xss->clean(request('name')),
            "phone"        => app()->xss->clean(request('phone')),
            "organization" => app()->xss->clean(request('organization')),
        ];

        // 4. Thực hiện Update vào DB
        // QUAN TRỌNG: Tham số thứ 3 là điều kiện WHERE để không update nhầm người khác
        $result = app()->db->update("accounts", $updateData, ["uuid" => $userId]);

        if ($result) {
            return response()->json([
                'status' => 'success',
                'alert' => 'Cập nhật thông tin thành công',
            ]);
        }

        return response()->json(['status' => 'error', 'alert' => 'Có lỗi xảy ra, vui lòng thử lại'], 500);
    }

    // HÀM 2: CHỈ ĐỔI MẬT KHẨU
    public function ChangePassword(){
        // 1. Lấy ID người dùng
        $userId = app()->request->user->uuid;
        if (!$userId) {
            return response()->json(['status' => 'error', 'alert' => 'Vui lòng đăng nhập'], 401);
        }

        // 2. Validate dữ liệu
        $validator = app()->validate(
            [
                'password_old'     => 'required',
                'password'         => 'required|min:6', // Mật khẩu mới tối thiểu 6 ký tự
                'password_confirm' => 'required|same:password', // Phải khớp với mật khẩu mới
            ],
            [
                'password_old.required'     => 'Vui lòng nhập mật khẩu cũ',
                'password.required'         => 'Vui lòng nhập mật khẩu mới',
                'password.min'              => 'Mật khẩu mới phải có ít nhất 6 ký tự',
                'password_confirm.required' => 'Vui lòng nhập lại mật khẩu mới',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'alert' => $validator->first(),
            ], 400);
        }

        // 3. Kiểm tra mật khẩu cũ có đúng không
        // Giả sử lấy user từ DB
        $user = app()->db->get('accounts',"*", ['uuid' => $userId]); 

        if (!$user) {
            return response()->json(['status' => 'error', 'alert' => 'Tài khoản không tồn tại'], 404);
        }

        // So sánh mật khẩu cũ (Dùng password_verify nếu mật khẩu được mã hóa Hash)
        if (!password_verify(request('password_old'), $user['password'])) {
            return response()->json(['status' => 'error', 'alert' => 'Mật khẩu cũ không chính xác'], 400);
        }

        if (request('password')!==request('password_confirm')) {
            return response()->json([
                'status'  => 'error',
                'alert' => 'Mật khẩu xác nhận không giống.'
            ], 401);
        }

        // 4. Update mật khẩu mới (MÃ HÓA TRƯỚC KHI LƯU)
        $updateData = [
            "password" => password_hash(request('password'), PASSWORD_BCRYPT),
        ];

        app()->db->update("accounts", $updateData, ["uuid" => $userId]);

        return response()->json([
            'status' => 'success',
            'alert' => 'Đổi mật khẩu thành công',
        ]);
    }

    public function Payments() {
        $user = app()->request->user;
        
        $transactions = app()->db->select("transactions", "*", [
            "account" => $user->uuid,
            "ORDER" => ["created_at" => "DESC"],
            "LIMIT" => 5
        ]);

        return view('account/payments', [
            'user' => $user,
            'transactions' => $transactions ?: []
        ]);
    }

    public function History() {
        $user = app()->request->user;
    
        // ── Lịch sử sử dụng AI ───────────────────────────
        $logs = app()->db->select("originality_history", "*", [
            "account_uuid" => $user->uuid,
            "ORDER"        => ["created_at" => "DESC"]
        ]);
    
        $histories = [];
        foreach ($logs as $item) {
            $aiScore   = (float) ($item['ai_score']   ?? 0);
            $plagScore = (float) ($item['plag_score'] ?? 0);
            $id = $item['id'];
    
            $resultText  = 'N/A';
            $colorClass  = 'text-primary';
            $icon        = 'file-text';
            $bgColor     = 'bg-primary-subtle text-primary';
            $serviceText = 'Chưa xác định';
    
            if ($plagScore > 0) {
                $resultText  = "{$plagScore}% Trùng lặp";
                $colorClass  = $plagScore >= 20 ? 'text-danger' : 'text-warning';
                $icon        = 'file-warning';
                $bgColor     = 'bg-danger-subtle text-danger';
                $serviceText = 'Đạo văn';
            } elseif ($aiScore > 0) {
                $humanPct    = round(100 - $aiScore, 1);
                $resultText  = "{$humanPct}% Human";
                $colorClass  = $humanPct >= 80 ? 'text-success' : 'text-danger';
                $serviceText = 'Check AI';
            }
    
            $histories[] = [
                'title'      => $item['title'] ?? 'Tài liệu không tên',
                'service'    => $serviceText,
                'result'     => $resultText,
                'credits'    => $item['points_used'] ?? 0,
                'created_at' => $item['created_at'],
                'icon'       => $icon,
                'bg'         => $bgColor,
                'class'      => $colorClass,
                'id' => $id ,
            ];
        }
    
        // ── Chart data: 30 ngày gần nhất ─────────────────
        $chartLabels = [];
        $chartData   = [];
        for ($i = 29; $i >= 0; $i--) {
            $chartLabels[] = date('d/m', strtotime("-$i days"));
            $key           = date('Y-m-d', strtotime("-$i days"));
            $chartData[$key] = 0;
        }
    
        foreach ($logs as $item) {
            $day = date('Y-m-d', strtotime($item['created_at']));
            if (isset($chartData[$day])) {
                $chartData[$day] += (float) ($item['points_used'] ?? 0);
            }
        }
    
        // ── Lịch sử giao dịch ────────────────────────────
        $rawTransactions = app()->db->select("transactions", "*", [
            "account" => $user->uuid,
            "ORDER"   => ["created_at" => "DESC"]
        ]) ?: [];
    
        $transactions = array_map(function ($t) {
            $t['type_label'] = match ($t['type'] ?? 'deposit') {
                'deposit'    => 'Nạp tiền',
                'withdraw'   => 'Rút tiền',
                'commission' => 'Hoa hồng',
                default      => 'Khác'
            };
            $t['display_amount'] = $t['vmied'] ?? $t['amount'] ?? 0;
            return $t;
        }, $rawTransactions);
    
        if (app()->request->isHtmx()) {
            return view('account/history', [
                'user'         => $user,
                'histories'    => $histories,
                'transactions' => $transactions,
                'chartLabels'  => json_encode(array_values($chartLabels)),
                'chartData'    => json_encode(array_values($chartData)),
            ]);
        }
    
        header('Location: /app');
        exit;
    }
    
    public function HistoryDetail($id) {
        $user = app()->request->user;
    
        $history = app()->db->get("originality_history", "*", [
            "id" => (int)$id,
        ]);
    
        if (!$history) {
            http_response_code(404);
            echo '<div class="text-center py-5 text-danger">Không tìm thấy dữ liệu</div>';
            exit;
        }
    
        $result = json_decode($history['metadata'], true);
    
        return view('account/history-post', [
            'history' => $history,
            'result'  => $result['result'] ?? $result,
        ]);
    }
    
    public function Affiliate() {
        $user = app()->request->user;
    
        $accountId = app()->db->get("accounts", "id", ["uuid" => $user->uuid]);
    
        $bankAccounts = app()->db->select("bank_accounts", "*", [
            "account" => $user->uuid,
            "status"  => 1,
            "ORDER"   => ["is_default" => "DESC", "created_at" => "DESC"]
        ]);
    
        $payouts = app()->db->select("transactions", "*", [
            "account" => $user->uuid,
            "type"    => "withdraw",
            "ORDER"   => ["created_at" => "DESC"]
        ]);
    
        $balance = app()->db->get("wallets", "balance", [
            "account" => $accountId
        ]) ?? 0;
    
        $totalReferrals = app()->db->count("accounts", [
            "ref_by" => $user->affiliate
        ]) ?? 0;
    
        $totalEarned = app()->db->sum("transactions", "commission", [
            "account" => $user->uuid,
            "type"    => "commission",
            "status"  => 1
        ]) ?? 0;
    
        // Query lịch sử hoa hồng JOIN accounts lấy email
        $referrals = app()->db->select("transactions", [
            "[>]accounts" => ["account" => "id"]
        ], [
            "transactions.id",
            "transactions.amount",
            "transactions.commission",
            "transactions.status",
            "transactions.created_at",
            "accounts.email(ref_email)"
        ], [
            "transactions.referrer" => $accountId,
            "transactions.type"     => "commission",
            "ORDER"                 => ["transactions.created_at" => "DESC"],
            "LIMIT"                 => 50
        ]) ?: [];
    
        return view('account/affiliate', [
            'user'         => $user,
            'bankAccounts' => $bankAccounts ?: [],
            'payouts'      => $payouts ?: [],
            'referrals'    => $referrals,
            'stats'        => [
                'balance'         => $balance,
                'total_referrals' => $totalReferrals,
                'total_earned'    => $totalEarned,
            ]
        ]);
    }
    
    // Thêm tài khoản ngân hàng
    public function AddBankAccount() {
        $userId = app()->request->user->uuid;
    
        $validator = app()->validate([
            'bank_code'      => 'required',
            'bank_name'      => 'required',
            'account_number' => 'required',
            'account_name'   => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'alert' => $validator->first()], 400);
        }
    
        // Nếu set default → reset các tài khoản khác
        if (request('is_default')) {
            app()->db->update("bank_accounts", ["is_default" => 0], ["account" => $userId]);
        }
    
        $data = [
            "uuid"           => uuid(),
            "account"        => $userId,
            "bank_code"      => app()->xss->clean(request('bank_code')),
            "bank_name"      => app()->xss->clean(request('bank_name')),
            "account_number" => app()->xss->clean(request('account_number')),
            "account_name"   => strtoupper(app()->xss->clean(request('account_name'))),
            "is_default"     => request('is_default') ? 1 : 0,
        ];
    
        app()->db->insert("bank_accounts", $data);
    
        return response()->json([
            'status' => 'success',
            'alert'  => 'Thêm tài khoản thành công',
            'data'   => $data
        ]);
    }
    
    public function Payout() {
    
        $userId = app()->request->user->uuid;
        $userIdInt = app()->db->get("accounts", "id", ["uuid" => $userId]);
        if (!$userIdInt) {
            return response()->json(['status' => 'error', 'alert' => 'Không tìm thấy tài khoản'], 404);
        }
        // Validate
        $validator = app()->validate(
            [
                'amount'            => 'required',
                'bank_account_uuid' => 'required',
            ],
            [
                'amount.required'            => 'Vui lòng nhập số tiền',
                'bank_account_uuid.required' => 'Vui lòng chọn tài khoản ngân hàng',
            ]
        );
    
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'alert' => $validator->first()], 400);
        }
    
        $amount = (int) request('amount');
        if ($amount < 100000) {
            return response()->json(['status' => 'error', 'alert' => 'Số tiền rút tối thiểu là 100.000 VNĐ'], 400);
        }
    
        // Kiểm tra bank account có thuộc về user không
        $bank = app()->db->get("bank_accounts", "*", [
            "uuid"    => request('bank_account_uuid'),
            "account" => $userId,
            "status"  => 1
        ]);
    
        if (!$bank) {
            return response()->json(['status' => 'error', 'alert' => 'Tài khoản ngân hàng không hợp lệ'], 400);
        }
    
        // Kiểm tra số dư wallet
        $wallet = app()->db->get("wallets", ["id", "balance"], ["account" => $userIdInt]);
        if (!$wallet || (float)$wallet['balance'] < $amount) {
            return response()->json(['status' => 'error', 'alert' => 'Số dư ví không đủ'], 400);
        }
    
        // Trừ tiền từ wallet
        $deducted = upsertWallet($userIdInt, $amount, '', 'withdraw');
        if (!$deducted) {
            return response()->json(['status' => 'error', 'alert' => 'Không thể trừ số dư ví, vui lòng thử lại'], 500);
        }
    
        // Insert vào transactions
        app()->db->insert("transactions", [
            "uuid"         => uuid(),
            "type"         => "withdraw",
            "account"      => $userId,
            "amount"       => $amount,
            "vmied"        => -$amount,
            "commission"   => 0,
            "method"       => "bank_transfer",
            "bank_code"    => $bank['bank_code'],
            "bank_account" => $bank['account_number'],
            "bank_owner"   => $bank['account_name'],
            "status"       => 0,
            "note"         => "Yêu cầu rút tiền về " . $bank['bank_name'],
            "ip_address"   => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    
        return response()->json([
            'status' => 'success',
            'alert'  => 'Yêu cầu rút tiền đã được gửi! Chúng tôi sẽ xử lý trong 1-2 ngày làm việc.',
        ]);
    }
    
    public function ConvertWalletToPoints() {
        $user = app()->request->user;
        $accountId = app()->db->get("accounts", "id", ["uuid" => $user->uuid]);
        $amount = (int) request('amount');
    
        if ($amount < 10000) {
            return response()->json(['status' => 'error', 'alert' => 'Số tiền tối thiểu là 10.000 V'], 400);
        }
    
        // Trừ wallet
        $deducted = upsertWallet($accountId, $amount, '', 'withdraw');
        if (!$deducted) {
            return response()->json(['status' => 'error', 'alert' => 'Số dư ví không đủ'], 400);
        }
    
        // Cộng points dùng upsertPoints
        $credited = upsertPoints($accountId, $amount, '', 'deposit');
        if (!$credited) {
            // Rollback lại wallet nếu cộng points thất bại
            upsertWallet($accountId, $amount, '', 'commission');
            return response()->json(['status' => 'error', 'alert' => 'Không thể cộng điểm, vui lòng thử lại'], 500);
        }
    
        return response()->json(['status' => 'success', 'alert' => 'Chuyển đổi thành công!']);
    }
}