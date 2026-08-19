<?php
namespace App\Services;

class VnpayService
{
    private $vnp_TmnCode;
    private $vnp_HashSecret;
    private $vnp_Url;
    private $vnp_Returnurl;

    public function __construct()
    {
        $this->vnp_TmnCode    = $_ENV['VNPAY_TMN_CODE'] ?? getenv('VNPAY_TMN_CODE') ?? '';
        $this->vnp_HashSecret = $_ENV['VNPAY_HASH_SECRET'] ?? getenv('VNPAY_HASH_SECRET') ?? '';
        $this->vnp_Url        = $_ENV['VNPAY_URL'] ?? getenv('VNPAY_URL') ?? '';
        $this->vnp_Returnurl  = $_ENV['VNPAY_RETURN_URL'] ?? getenv('VNPAY_RETURN_URL') ?? '';
    }

    /**
     * Tạo URL thanh toán VNPAY
     */
    public function createPaymentUrl($orderId, $amount, $orderInfo, $ipAddress)
    {
        $vnp_TxnRef = $orderId; 
        $vnp_OrderInfo = $orderInfo;
        $vnp_OrderType = 'other';
        $vnp_Amount = $amount * 100; // Số tiền x100 theo format của VNPAY
        $vnp_Locale = 'vn';
        $vnp_BankCode = 'NCB'; // Hardcode tạm NCB vì tài khoản VMYTE011 chưa được VNPAY mở cổng chọn chung
        $vnp_IpAddr = $ipAddress;

        // Đảm bảo thời gian tạo là múi giờ Việt Nam
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $startTime = date("YmdHis");
        $expire = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));

        $inputData = array(
            "vnp_Version"    => "2.1.0",
            "vnp_TmnCode"    => $this->vnp_TmnCode,
            "vnp_Amount"     => $vnp_Amount,
            "vnp_Command"    => "pay",
            "vnp_CreateDate" => $startTime,
            "vnp_CurrCode"   => "VND",
            "vnp_IpAddr"     => $vnp_IpAddr,
            "vnp_Locale"     => $vnp_Locale,
            "vnp_OrderInfo"  => $vnp_OrderInfo,
            "vnp_OrderType"  => $vnp_OrderType,
            "vnp_ReturnUrl"  => $this->vnp_Returnurl,
            "vnp_TxnRef"     => $vnp_TxnRef,
            "vnp_ExpireDate" => $expire
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $this->vnp_Url . "?" . $query;
        if (isset($this->vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $this->vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnp_Url;
    }

    /**
     * Kiểm tra Hash cho Return URL / IPN URL
     */
    public function verifyHash($inputData)
    {
        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        $inputData = array_filter($inputData, function($key) {
            return substr($key, 0, 4) == "vnp_";
        }, ARRAY_FILTER_USE_KEY);

        unset($inputData['vnp_SecureHash']);
        unset($inputData['vnp_SecureHashType']);

        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $this->vnp_HashSecret);
        return $secureHash === $vnp_SecureHash;
    }
}
