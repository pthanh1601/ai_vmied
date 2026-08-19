<?php
namespace App\Controllers;

class PaymentController
{
    protected $app;

    public function __construct() {
        $this->app = app();
    }

    // =========================================================
    // TRANG NẠP TIỀN
    // =========================================================
    public function Payments() {
        $user = app()->request->user;

        $accountId = app()->db->get("accounts", "id", ["uuid" => $user->uuid]);

        $transactions = app()->db->select("transactions", "*", [
            "account" => $accountId,
            "ORDER"   => ["created_at" => "DESC"],
            "LIMIT"   => 5
        ]);

        if ($transactions) {
            foreach ($transactions as &$tx) {
                switch ($tx['type']) {
                    case 'deposit':
                        $tx['type_label']      = 'Nạp tiền';
                        $tx['display_amount']  = $tx['amount'];
                        break;
                    case 'withdraw':
                        $tx['type_label']      = 'Rút tiền';
                        $tx['display_amount']  = $tx['amount'];
                        break;
                    case 'commission':
                        $tx['type_label']      = 'Hoa hồng';
                        $tx['display_amount']  = $tx['commission']; 
                        break;
                    default:
                        $tx['type_label']      = 'Khác';
                        $tx['display_amount']  = $tx['amount'];
                }
            }
        }

        return view('account/payments', [
            'user'         => $user,
            'transactions' => $transactions ?: []
        ]);
    }

    // =========================================================
    // TẠO LỆNH NẠP TIỀN
    // =========================================================
    public function Deposit() {
        $user   = app()->request->user;
        $amount = (int) request('amount');
        $method = request('method', 'qr');

        if ($amount < 10000) {
            return response()->json([
                'status' => 'error',
                'alert'  => 'Số tiền nạp tối thiểu là 10.000 VNĐ'
            ]);
        }

        $account   = app()->db->get("accounts", ["id", "ref_by"], ["uuid" => $user->uuid]);
        $accountId = $account['id'];
        $code      = 'INV-' . strtoupper(substr(uniqid(), -6));

        try {
            // Tạo lệnh nạp tiền status=0
            app()->db->insert("transactions", [
                "code"    => $code,
                "uuid"    => uuid(),
                "account" => $accountId,
                "type"    => "deposit",
                "amount"  => $amount,
                "vmied"   => $amount,
                "method"  => $method,
                "status"  => 0,
                "note"    => "Tạo lệnh nạp tiền"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'alert'  => 'Có lỗi xảy ra, vui lòng thử lại'
            ]);
        }

        // if ((getenv('AUTO_APPROVE_DEPOSIT') ?: 'true') === 'true') {
        //     $this->processDepositSuccess($code);
        
                if ($method !== 'vnpay') {
            return response()->json([
                'status' => 'error',
                'alert'  => 'Phương thức thanh toán này hiện chưa được hỗ trợ, vui lòng chọn VNPAY!'
            ]);
        }
        // Nếu là VNPAY, sinh URL và chuyển hướng ngay
        if ($method === 'vnpay') {
            $vnpayService = new \App\Services\VnpayService();
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ipAddress = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
            }
            $vnpUrl = $vnpayService->createPaymentUrl($code, $amount, 'Nap tien VMIED ' . $code, trim($ipAddress));
            
            return response()->json([
                'status'      => 'success',
                'alert'       => 'Đang chuyển hướng sang VNPAY...',
                'redirectUrl' => $vnpUrl
            ]);
        }
        // ✅ Lấy số dư mới sau khi cộng điểm
        $newBalance = (int) (app()->db->get('points', 'points', ['account' => $user->uuid]) ?? 0);
        
        // 👉 THÊM ĐOẠN NÀY ĐỂ CẬP NHẬT SESSION & REQUEST USER TRỰC TIẾP
        $sessionAccount = app()->session->get('account');
        if ($sessionAccount) {
            $sessionAccount['point'] = $newBalance;
            app()->session->set('account', $sessionAccount);
            app()->request->user = (object) $sessionAccount;
        }

        return response()->json([
            'status'   => 'success',
            'alert'    => 'Nạp tiền thành công!',
            // 'redirect' => '/app/historys',
            'redirect' => '/app/historys?tab=transaction',
            'newBalance' => $newBalance,
        ]);
    }

    // =========================================================
    // XỬ LÝ SAU KHI THANH TOÁN THÀNH CÔNG
    // Dùng cho: auto approve, admin approve, VNPay/Momo callback
    //
    // Tích hợp VNPay sau này:
    //   public function VnpayCallback() {
    //       // validate chữ ký VNPay...
    //       $code = request('vnp_TxnRef');
    //       if (request('vnp_ResponseCode') === '00') {
    //           $this->processDepositSuccess($code);
    //       }
    //       return response()->json(['RspCode' => '00']);
    //   }
    // =========================================================
    
    // XỬ LÝ VNPAY RETURN (Khi user quay về web từ VNPAY)
    // =========================================================
    public function VnpayReturn() {
        $inputData = $_GET;
        $vnpayService = new \App\Services\VnpayService();

        // Chuẩn bị khung HTML cơ bản load sẵn SweetAlert2
        $htmlTop = '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script><style>body{background:#f8f9fa;font-family:sans-serif;}</style></head><body>';
        $htmlBottom = '</body></html>';

        if (isset($inputData['vnp_SecureHash'])) {
            $isValid = $vnpayService->verifyHash($inputData);
            if ($isValid && isset($inputData['vnp_ResponseCode']) && $inputData['vnp_ResponseCode'] == '00') {
                // Thanh toán thành công, cộng điểm cho user
                $this->processDepositSuccess($inputData['vnp_TxnRef']);
                
                // Cập nhật lại session để giao diện hiển thị điểm mới
                $sessionAccount = app()->session->get('account');
                if ($sessionAccount) {
                    $newBalance = (int) (app()->db->get('points', 'points', ['account' => $sessionAccount['uuid']]) ?? 0);
                    $sessionAccount['point'] = $newBalance;
                    app()->session->set('account', $sessionAccount);
                }
                
                // Hiển thị thông báo đẹp và chuyển hướng
                echo $htmlTop . "<script>
                    Swal.fire({
                        title: 'Thành công!',
                        text: 'Giao dịch VNPAY thành công. Điểm đã được cộng vào tài khoản!',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '/app/historys?tab=transaction';
                    });
                </script>" . $htmlBottom;
                exit;
            }
        }
        
        // Nếu thất bại hoặc sai chữ ký
        echo $htmlTop . "<script>
            Swal.fire({
                title: 'Thất bại',
                text: 'Giao dịch VNPAY không thành công hoặc chữ ký không hợp lệ.',
                icon: 'error',
                confirmButtonText: 'Quay lại'
            }).then(() => {
                window.location.href = '/app/historys?tab=transaction';
            });
        </script>" . $htmlBottom;
        exit;
    }

    // =========================================================
    // XỬ LÝ VNPAY IPN (Webhook Server-to-Server)
    // =========================================================
    public function VnpayIpn() {
        $inputData = $_GET;
        $vnpayService = new \App\Services\VnpayService();

        if (isset($inputData['vnp_SecureHash'])) {
            $isValid = $vnpayService->verifyHash($inputData);
            if ($isValid) {
                $orderCode = $inputData['vnp_TxnRef'];
                
                // Lấy đơn hàng từ DB
                $transaction = app()->db->get("transactions", "*", ["code" => $orderCode, "type" => "deposit"]);

                if ($transaction) {
                    if ($transaction['amount'] == ($inputData['vnp_Amount'] / 100)) {
                        if ($transaction['status'] == 0) {
                            if ($inputData['vnp_ResponseCode'] == '00') {
                                // Giao dịch thành công -> Gọi hàm xử lý cộng điểm
                                $this->processDepositSuccess($orderCode);
                            } else {
                                // Giao dịch thất bại
                                app()->db->update("transactions", [
                                    "status" => 2,
                                    "note"   => "Giao dịch VNPAY thất bại"
                                ], ["code" => $orderCode]);
                            }
                            return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
                        } else {
                            return response()->json(['RspCode' => '02', 'Message' => 'Order already confirmed']);
                        }
                    } else {
                        return response()->json(['RspCode' => '04', 'Message' => 'invalid amount']);
                    }
                } else {
                    return response()->json(['RspCode' => '01', 'Message' => 'Order not found']);
                }
            } else {
                return response()->json(['RspCode' => '97', 'Message' => 'Invalid signature']);
            }
        }
        return response()->json(['RspCode' => '99', 'Message' => 'Unknown error']);
    }
    
   // =========================================================
    // XỬ LÝ SAU KHI THANH TOÁN THÀNH CÔNG (AUTO / VNPAY / MOMO)
    // =========================================================
    public function processDepositSuccess(string $code): bool {
        try {
            app()->db->action(function($db) use ($code) {
                $transaction = $db->get("transactions", [
                    "id", "account", "amount", "vmied", "method"
                ], [
                    "code"   => $code,
                    "type"   => "deposit",
                    "status" => 0
                ]);
    
                if (!$transaction) return;
    
                $accountId = $transaction['account'];
                $amount    = $transaction['amount'];
                $vmied     = $transaction['vmied'];
    
                // 1. Cập nhật trạng thái lệnh nạp thành công
                $db->update("transactions", [
                    "status" => 1,
                    "note"   => "Thanh toán thành công"
                ], ["code" => $code]);
    
                // 2. Cộng điểm (V) vào tài khoản cho sinh viên/học viên
                upsertPoints($accountId, $vmied, $code, 'deposit');
    
                // =====================================================
                // 3. XỬ LÝ CHIA HOA HỒNG (PHÍ QUẢN LÝ 10% CHO VIP)
                // =====================================================
                $account   = $db->get("accounts", ["ref_by", "name"], ["id" => $accountId]);
                $refByCode = $account['ref_by'] ?? null;
    
                if ($refByCode) {
                    // Lấy tuyến trên và kiểm tra xem có phải là VIP đang hoạt động không
                    $refBy = $db->get("accounts", ["id", "type", "status"], ["affiliate" => $refByCode]);
    
                    if ($refBy && $refBy['type'] == 2 && $refBy['status'] == 1) {
                        
                        // Tính toán 10% hoa hồng
                        $commissionRate = 10; 
                        $commission     = round($amount * $commissionRate / 100);
    
                        // Ghi lại giao dịch VÀO TÀI KHOẢN CỦA VIP (để VIP xem sao kê)
                        $db->insert("transactions", [
                            "code"       => 'COM-' . strtoupper(substr(uniqid(), -6)),
                            "uuid"       => uuid(),
                            "account"    => $refBy['id'],       // TRƯỜNG NHẬN TIỀN
                            "referrer"   => $accountId,         // TỪ SINH VIÊN NÀY
                            "type"       => "commission",
                            "amount"     => $amount,            // Số tiền nạp gốc
                            "vmied"      => 0,                  // Không cộng point
                            "commission" => $commission,        // Tiền thật nhận được (10%)
                            "method"     => $transaction['method'],
                            "status"     => 1,
                            "note"       => "Phí quản lý 10% từ học viên " . ($account['name'] ?? '')
                        ]);
    
                        // Cộng tiền thật vào ví rút tiền (Bảng wallets) cho VIP
                        upsertWallet($refBy['id'], $commission, $code, 'commission');
                    }
                }
            });
    
        } catch (\Exception $e) {
            error_log("Lỗi xử lý nạp tiền: " . $e->getMessage());
            return false;
        }
    
        return true;
    }
}