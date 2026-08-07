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
                        $tx['display_amount']  = $tx['commission']; // Hiện số hoa hồng
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

        // -------------------------------------------------------
        // CHẾ ĐỘ AUTO APPROVE (test/demo)
        // Sau này tích hợp VNPay: xóa dòng này,
        // thay bằng redirect sang VNPay payment URL
        // -------------------------------------------------------
        if ((getenv('AUTO_APPROVE_DEPOSIT') ?: 'true') === 'true') {
            $this->processDepositSuccess($code);
        }

        return response()->json([
            'status'   => 'success',
            'alert'    => 'Nạp tiền thành công!',
            'redirect' => '/app/historys'
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
    
                $db->update("transactions", [
                    "status" => 1,
                    "note"   => "Thanh toán thành công"
                ], ["code" => $code]);
    
                upsertPoints($accountId, $vmied, $code, 'deposit');
    
                $account   = $db->get("accounts", ["ref_by"], ["id" => $accountId]);
                $refByCode = $account['ref_by'] ?? null;
    
                if ($refByCode) {
                    $refBy = $db->get("accounts", ["id"], ["affiliate" => $refByCode]);
    
                    if ($refBy) {
                        $commissionRate = (int) (getenv('COMMISSION') ?: 20);
                        $commission     = round($amount * $commissionRate / 100);
    
                        $db->insert("transactions", [
                            "code"       => 'COM-' . strtoupper(substr(uniqid(), -6)),
                            "uuid"       => uuid(),
                            "account"    => $accountId,
                            "referrer"   => $refBy['id'],
                            "type"       => "commission",
                            "amount"     => $amount,
                            "vmied"      => $commission,
                            "commission" => $commission,
                            "method"     => $transaction['method'],
                            "status"     => 1,
                            "note"       => "Hoa hồng {$commissionRate}% từ giao dịch {$code}"
                        ]);
    
                        upsertWallet($refBy['id'], $commission, $code, 'commission');
                    }
                }
            });
    
        } catch (\Exception $e) {
            return false;
        }
    
        return true;
    }
}