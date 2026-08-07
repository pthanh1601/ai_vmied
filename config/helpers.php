<?php

if (!function_exists('app')) {
    function app() {
        return \Neo\Core\App::getInstance();
    }
}

if (!function_exists('view')) {
    function view($path, $data = []) {
        return app()->render($path, $data);
    }
}

if (!function_exists('request')) {
    function request($key = null, $default = null) {
        return app()->request->input($key, $default);
    }
}

if (!function_exists('response')) {
    function response() {
        return app()->response;
    }
}

if (!function_exists('__')) {
    function __($key) {
        return app()->lang->get($key);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field() {
        $token = app()->session->token();
        return '<input type="hidden" name="_token" value="' . $token . '">';
    }
}

// [NEW] XSS Protection Helpers
if (!function_exists('e')) {
    /**
     * Escape HTML special characters
     */
    function e($value) {
        return app()->xss->escape($value);
    }
}

if (!function_exists('clean')) {
    /**
     * Sanitize Input
     */
    function clean($value) {
        return app()->xss->clean($value);
    }
}
if (!function_exists('handleUnauthenticated')) {
    function handleUnauthenticated() {
        if (app()->request->isAjax()) {
            header('HX-Redirect: /login');
            http_response_code(401);
            exit;
        }
        
        header('Location: /login');
        exit;
    }
}
if (!function_exists('uuid')) { 
    function uuid(int $version = 4): string{
        switch ($version) {
            case 7:
                $time = (int)(microtime(true) * 1000);
                $timestampBytes = substr(pack('J', $time), 2);
                $randomBytes = random_bytes(10);
                $data = $timestampBytes . $randomBytes;
                $data[6] = chr(ord($data[6]) & 0x0f | 0x70);
                $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
                break;
            
            case 4:
            default:
                $data = random_bytes(16);
                $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
                $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
                break;
        }
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
if (!function_exists('upsertPoints')) {
    function upsertPoints(int $accountId, float $amount, string $code = '', string $type = 'deposit'): bool {
        $db = app()->db;

        $accountUuid = $db->get("accounts", "uuid", ["id" => $accountId]);
        if (!$accountUuid) return false;

        $existing = $db->get("points", ["id", "points"], ["account" => $accountUuid]);

        $deductTypes = ['withdraw', 'deduct', 'use'];
        $isDeduct    = in_array($type, $deductTypes);
        $delta       = $isDeduct ? -abs($amount) : abs($amount);

        if ($existing) {
            if ($isDeduct && $existing['points'] < abs($amount)) {
                return false;
            }
            $db->update("points", [
                "points[+]" => $delta
            ], ["account" => $accountUuid]);
        } else {
            if ($isDeduct) return false;
            $db->insert("points", [
                "account" => $accountUuid,
                "points"  => $delta,
            ]);
        }

        $db->insert("points_historys", [
            "uuid"    => uuid(),
            "account" => $accountUuid,
            "type"    => $type,
            "point"   => $delta,
            "code"    => $code,
            "date"    => date('Y-m-d H:i:s'),
            "deleted" => 0,
        ]);

        return true;
    }
}
if (!function_exists('upsertWallet')) {
    function upsertWallet(int $accountId, float $amount, string $code = '', string $type = 'commission'): bool {
        $db = app()->db;
        $accountUuid = $db->get("accounts", "uuid", ["id" => $accountId]);
        if (!$accountUuid) return false;
        $existing = $db->get("wallets", ["id", "balance"], ["account" => $accountId]);
        $deductTypes = ['transfer', 'withdraw', 'deduct'];
        $isDeduct    = in_array($type, $deductTypes);
        $delta       = $isDeduct ? -abs($amount) : abs($amount);
        if ($existing) {
            if ($isDeduct && $existing['balance'] < abs($amount)) {
                return false;
            }
            $result = $db->update("wallets", [
                "balance[+]" => $delta,
                "updated_at" => date('Y-m-d H:i:s')
            ], ["account" => $accountId]);
        
            if (!$result || $result->rowCount() === 0) return false;
        
        } else {
            if ($isDeduct) return false;
        
            $result = $db->insert("wallets", [
                "account" => $accountId,
                "balance" => $delta,
            ]);
        
            if (!$result || $result->rowCount() === 0) return false;
        }

        // Ghi log lịch sử wallet
        $db->insert("wallet_historys", [
            "uuid"       => uuid(),
            "account"    => $accountId,
            "type"       => $type,
            "amount"     => $delta,
            "code"       => $code,
            "date"       => date('Y-m-d H:i:s'),
            "deleted"    => 0,
        ]);
        
        return true;
    }
}
if (!function_exists('random_secret')) { 
    function random_secret($length = 32, $type = 'mix') {
        switch ($type) {
            case 'numeric':
                $chars = '0123456789';
                break;
            case 'alpha':
                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'secure':
                $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*';
                break;
            default: // 'mix'
                $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
        }

        $str = '';
        $max = strlen($chars) - 1;

        for ($i = 0; $i < $length; $i++) {
            // Sử dụng random_int để đảm bảo không thể đoán trước được chuỗi
            $str .= $chars[random_int(0, $max)];
        }

        return $str;
    }
}
if (!function_exists('callAPI')) { 
    function callAPI($endpoint, $method = 'GET', $data = []) {
        // 1. Lấy Base URL từ file .env (Quyết định là gọi Thật hay Giả)
        $baseUrl = $_ENV['ORIGINALITY_BASE_URL']; 
        $apiKey  = $_ENV['ORIGINALITY_API_KEY'];

        // 2. Tạo URL đầy đủ
        // Ví dụ: http://localhost/mock/originality/scan
        $url = rtrim($baseUrl, '/') . $endpoint;

        // 3. Cấu hình cURL
        $curl = curl_init();
        $headers = [
            'Content-Type: application/json',
            'X-OAI-API-KEY: ' . $apiKey // Header bắt buộc [cite: 1023]
        ];

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
        ];

        if ($method === 'POST' && !empty($data)) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($curl, $options);

        // 4. Thực thi
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);

        if ($err) {
            return ['error' => "cURL Error: $err"];
        }

        return json_decode($response, true);
    }
}