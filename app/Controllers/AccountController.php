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
        
        $account->type_id = $account->type;
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
        $account->type = $account->type==0?'ThÃ nh ViÃªn':'Quáº£n trá»‹';

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
            return response()->json(['status' => 'error', 'alert' => 'Vui lÃ²ng Ä‘Äƒng nháº­p'], 401);
        }

        // 2. Validate dá»¯ liá»‡u
        $validator = app()->validate(
            [
                'name' => 'required',
                'phone' => 'required', // VÃ­ dá»¥ thÃªm validate sá»‘ Ä‘iá»‡n thoáº¡i
            ],
            [
                'name.required' => 'TÃªn khÃ´ng Ä‘Æ°á»£c Ä‘á»ƒ trá»‘ng',
                'phone.required' => 'Sá»‘ Ä‘iá»‡n thoáº¡i khÃ´ng Ä‘Æ°á»£c Ä‘á»ƒ trá»‘ng',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'alert' => $validator->first(),
            ], 400);
        }

        // 3. Chuáº©n bá»‹ dá»¯ liá»‡u update (Clean XSS)
        $updateData = [
            "name"         => app()->xss->clean(request('name')),
            "phone"        => app()->xss->clean(request('phone')),
            "organization" => app()->xss->clean(request('organization')),
        ];

        // 4. Thá»±c hiá»‡n Update vÃ o DB
        // QUAN TRá»ŒNG: Tham sá»‘ thá»© 3 lÃ  Ä‘iá»u kiá»‡n WHERE Ä‘á»ƒ khÃ´ng update nháº§m ngÆ°á»i khÃ¡c
        $result = app()->db->update("accounts", $updateData, ["uuid" => $userId]);

        if ($result) {
            return response()->json([
                'status' => 'success',
                'alert' => 'Cáº­p nháº­t thÃ´ng tin thÃ nh cÃ´ng',
            ]);
        }

        return response()->json(['status' => 'error', 'alert' => 'CÃ³ lá»—i xáº£y ra, vui lÃ²ng thá»­ láº¡i'], 500);
    }

    // HÃ€M 2: CHá»ˆ Äá»”I Máº¬T KHáº¨U
    public function ChangePassword(){
        // 1. Láº¥y ID ngÆ°á»i dÃ¹ng
        $userId = app()->request->user->uuid;
        if (!$userId) {
            return response()->json(['status' => 'error', 'alert' => 'Vui lÃ²ng Ä‘Äƒng nháº­p'], 401);
        }

        // 2. Validate dá»¯ liá»‡u
        $validator = app()->validate(
            [
                'password_old'     => 'required',
                'password'         => 'required|min:6', // Máº­t kháº©u má»›i tá»‘i thiá»ƒu 6 kÃ½ tá»±
                'password_confirm' => 'required|same:password', // Pháº£i khá»›p vá»›i máº­t kháº©u má»›i
            ],
            [
                'password_old.required'     => 'Vui lÃ²ng nháº­p máº­t kháº©u cÅ©',
                'password.required'         => 'Vui lÃ²ng nháº­p máº­t kháº©u má»›i',
                'password.min'              => 'Máº­t kháº©u má»›i pháº£i cÃ³ Ã­t nháº¥t 6 kÃ½ tá»±',
                'password_confirm.required' => 'Vui lÃ²ng nháº­p láº¡i máº­t kháº©u má»›i',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'alert' => $validator->first(),
            ], 400);
        }

        // 3. Kiá»ƒm tra máº­t kháº©u cÅ© cÃ³ Ä‘Ãºng khÃ´ng
        // Giáº£ sá»­ láº¥y user tá»« DB
        $user = app()->db->get('accounts',"*", ['uuid' => $userId]); 

        if (!$user) {
            return response()->json(['status' => 'error', 'alert' => 'TÃ i khoáº£n khÃ´ng tá»“n táº¡i'], 404);
        }

        // So sÃ¡nh máº­t kháº©u cÅ© (DÃ¹ng password_verify náº¿u máº­t kháº©u Ä‘Æ°á»£c mÃ£ hÃ³a Hash)
        if (!password_verify(request('password_old'), $user['password'])) {
            return response()->json(['status' => 'error', 'alert' => 'Máº­t kháº©u cÅ© khÃ´ng chÃ­nh xÃ¡c'], 400);
        }

        if (request('password')!==request('password_confirm')) {
            return response()->json([
                'status'  => 'error',
                'alert' => 'Máº­t kháº©u xÃ¡c nháº­n khÃ´ng giá»‘ng.'
            ], 401);
        }

        // 4. Update máº­t kháº©u má»›i (MÃƒ HÃ“A TRÆ¯á»šC KHI LÆ¯U)
        $updateData = [
            "password" => password_hash(request('password'), PASSWORD_BCRYPT),
        ];

        app()->db->update("accounts", $updateData, ["uuid" => $userId]);

        return response()->json([
            'status' => 'success',
            'alert' => 'Äá»•i máº­t kháº©u thÃ nh cÃ´ng',
        ]);
    }
    
    // public function uploadsPDF()
    // {
    //     $user = app()->request->user;
    //     $uuid = $user->uuid ;
    //     if (!$uuid) {
    //         header('Content-Type: application/json');
    //         http_response_code(401);
    //         echo json_encode(['status' => 'error', 'message' => 'Vui lÃ²ng Ä‘Äƒng nháº­p.']);
    //         exit;
    //     }

    //     if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    //         header('Content-Type: application/json');
    //         http_response_code(400);
    //         echo json_encode(['status' => 'error', 'message' => 'KhÃ´ng tÃ¬m tháº¥y file hoáº·c file bá»‹ lá»—i.']);
    //         exit;
    //     }

    //     $file = $_FILES['file'];
    //     $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    //     // Chá»‰ cho phÃ©p Ä‘á»‹nh dáº¡ng .pdf vÃ  .docx Ä‘Ãºng nhÆ° frontend yÃªu cáº§u
    //     if (!in_array($ext, ['pdf', 'docx'])) {
    //         header('Content-Type: application/json');
    //         http_response_code(400);
    //         echo json_encode(['status' => 'error', 'message' => 'Äá»‹nh dáº¡ng file khÃ´ng há»— trá»£ (Chá»‰ nháº­n .pdf, .docx).']);
    //         exit;
    //     }

    //     // Äá»‹nh nghÄ©a thÆ° má»¥c lÆ°u trá»¯ trÃªn live host (Äáº£m báº£o thÆ° má»¥c nÃ y cÃ³ quyá»n ghi - Chmod 755 hoáº·c 777)
    //     $uploadDir = 'uploads/scans/' . date('Y/m') . '/';
    //     if (!is_dir($uploadDir)) {
    //         mkdir($uploadDir, 0755, true);
    //     }

    //     $fileName = $uuid . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    //     $targetFile = $uploadDir . $fileName;

    //     if (move_uploaded_file($file['tmp_name'], $targetFile)) {
    //         $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    //         $domain = $protocol . $_SERVER['HTTP_HOST'] . '/';
    //         $fileUrl = $domain . $targetFile;

    //         header('Content-Type: application/json');
    //         echo json_encode([
    //             'status' => 'success',
    //             'message' => 'Upload file thÃ nh cÃ´ng.',
    //             'url' => $fileUrl
    //         ]);
    //         exit;
    //     } else {
    //         header('Content-Type: application/json');
    //         http_response_code(500);
    //         echo json_encode(['status' => 'error', 'message' => 'KhÃ´ng thá»ƒ lÆ°u file vÃ o thÆ° má»¥c mÃ¡y chá»§.']);
    //         exit;
    //     }
    // }
    
    
    public function uploadsPDF()
    {
        $user = app()->request->user;
        $uuid = $user->uuid ;
        if (!$uuid) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Vui lÃ²ng Ä‘Äƒng nháº­p.']);
            exit;
        }
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            header('Content-Type: application/json');
            http_response_code(400);
    
            // BÃ¡o rÃµ lÃ½ do náº¿u lá»—i do vÆ°á»£t giá»›i háº¡n upload cá»§a PHP (upload_max_filesize / post_max_size)
            $err = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
            $msg = in_array($err, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE])
                ? 'File vÆ°á»£t quÃ¡ giá»›i háº¡n upload cho phÃ©p.'
                : 'KhÃ´ng tÃ¬m tháº¥y file hoáº·c file bá»‹ lá»—i.';
    
            echo json_encode(['status' => 'error', 'message' => $msg]);
            exit;
        }
    
        $file = $_FILES['file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        // Chá»‰ cho phÃ©p Ä‘á»‹nh dáº¡ng .pdf vÃ  .docx Ä‘Ãºng nhÆ° frontend yÃªu cáº§u
        if (!in_array($ext, ['pdf', 'docx'])) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Äá»‹nh dáº¡ng file khÃ´ng há»— trá»£ (Chá»‰ nháº­n .pdf, .docx).']);
            exit;
        }
    
        // â”€â”€â”€ Giá»›i háº¡n kÃ­ch thÆ°á»›c file â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $maxSizeBytes = 20 * 1024 * 1024; // 20MB â€” chá»‰nh sá»‘ nÃ y theo nhu cáº§u
        if ($file['size'] > $maxSizeBytes) {
            $sizeMB = round($file['size'] / 1024 / 1024, 1);
            $maxMB  = $maxSizeBytes / 1024 / 1024;
            header('Content-Type: application/json');
            http_response_code(413); // Payload Too Large
            echo json_encode([
                'status'  => 'error',
                'message' => "File quÃ¡ lá»›n ({$sizeMB}MB). Vui lÃ²ng chá»n file dÆ°á»›i {$maxMB}MB.",
            ]);
            exit;
        }
        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    
        // Äá»‹nh nghÄ©a thÆ° má»¥c lÆ°u trá»¯ trÃªn live host (Äáº£m báº£o thÆ° má»¥c nÃ y cÃ³ quyá»n ghi - Chmod 755 hoáº·c 777)
        $uploadDir = 'uploads/scans/' . date('Y/m') . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileName = $uuid . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $targetFile = $uploadDir . $fileName;
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
            $domain = $protocol . $_SERVER['HTTP_HOST'] . '/';
            $fileUrl = $domain . $targetFile;
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'message' => 'Upload file thÃ nh cÃ´ng.',
                'url' => $fileUrl
            ]);
            exit;
        } else {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'KhÃ´ng thá»ƒ lÆ°u file vÃ o thÆ° má»¥c mÃ¡y chá»§.']);
            exit;
        }
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

        $logs = app()->db->select("originality_history", [
            "id", "title", "ai_score", "plag_score",
            "grammar_errors", "readability_score",   // â† thÃªm 2 cá»™t nÃ y
            "points_used", "created_at"
        ], [
            "account_uuid" => $user->uuid,
            "ORDER"        => ["created_at" => "DESC"],
            "LIMIT"        => 50
        ]) ?: [];
    
        $histories = [];
        foreach ($logs as $item) {
            $aiScore       = (float) ($item['ai_score']       ?? 0);
            $plagScore     = (float) ($item['plag_score']     ?? 0);
            $grammarErrors = (int)   ($item['grammar_errors'] ?? 0);
            $readScore     = $item['readability_score'] ?? null; // cÃ³ thá»ƒ null hoáº·c 0 tháº­t, cáº§n phÃ¢n biá»‡t
            $id = $item['id'];
    
            $resultText  = 'N/A';
            $colorClass  = 'text-primary';
            $icon        = 'file-text';
            $bgColor     = 'bg-primary-subtle text-primary';
            $serviceText = 'ChÆ°a xÃ¡c Ä‘á»‹nh';
    
            if ($plagScore > 0) {
                $resultText  = "{$plagScore}% TrÃ¹ng láº·p";
                $colorClass  = $plagScore >= 20 ? 'text-danger' : 'text-warning';
                $icon        = 'file-warning';
                $bgColor     = 'bg-danger-subtle text-danger';
                $serviceText = 'Äáº¡o vÄƒn';
            } elseif ($aiScore > 0) {
                $humanPct    = round(100 - $aiScore, 1);
                $resultText  = "{$humanPct}% Human";
                $colorClass  = $humanPct >= 80 ? 'text-success' : 'text-danger';
                $serviceText = 'Check AI';
            } elseif ($grammarErrors > 0) {
                $resultText  = "{$grammarErrors} lá»—i";
                $colorClass  = $grammarErrors > 10 ? 'text-danger' : 'text-warning';
                $icon        = 'spell-check';
                $bgColor     = 'bg-warning-subtle text-warning';
                $serviceText = 'Ngá»¯ phÃ¡p';
            } elseif ($readScore !== null) {
                $resultText  = "Äiá»ƒm {$readScore}";
                $colorClass  = 'text-info';
                $icon        = 'book-open';
                $bgColor     = 'bg-info-subtle text-info';
                $serviceText = 'Äá»c hiá»ƒu';
            } elseif ($grammarErrors === 0 && ($item['grammar_errors'] ?? null) !== null) {
                // CÃ³ báº­t check ngá»¯ phÃ¡p nhÆ°ng 0 lá»—i -> váº«n nÃªn hiá»‡n, khÃ´ng pháº£i "chÆ°a xÃ¡c Ä‘á»‹nh"
                $resultText  = 'KhÃ´ng lá»—i';
                $colorClass  = 'text-success';
                $icon        = 'spell-check';
                $bgColor     = 'bg-success-subtle text-success';
                $serviceText = 'Ngá»¯ phÃ¡p';
            }
    
            $histories[] = [
                'title'      => $item['title'] ?? 'TÃ i liá»‡u khÃ´ng tÃªn',
                'service'    => $serviceText,
                'result'     => $resultText,
                'credits'    => $item['points_used'] ?? 0,
                'created_at' => $item['created_at'],
                'icon'       => $icon,
                'bg'         => $bgColor,
                'class'      => $colorClass,
                'id'         => $id,
            ];
        }
        
        // â”€â”€ Chart data: 30 ngÃ y gáº§n nháº¥t â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
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
    
        // â”€â”€ Lá»‹ch sá»­ giao dá»‹ch (Tá»‘i Æ°u: Chá»‰ láº¥y cá»™t cáº§n, LIMIT 50) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $rawTransactions = app()->db->select("transactions", [
            "id", "type", "vmied", "amount", "created_at" 
        ], [
            "account" => $user->uuid,
            "ORDER"   => ["created_at" => "DESC"],
            "LIMIT"   => 50 // Giá»›i háº¡n giao dá»‹ch hiá»ƒn thá»‹
        ]) ?: [];
    
        $transactions = array_map(function ($t) {
            $t['type_label'] = match ($t['type'] ?? 'deposit') {
                'deposit'    => 'Náº¡p tiá»n',
                'withdraw'   => 'RÃºt tiá»n',
                'commission' => 'Hoa há»“ng',
                default      => 'KhÃ¡c'
            };
            $t['display_amount'] = $t['vmied'] ?? $t['amount'] ?? 0;
            return $t;
        }, $rawTransactions);
    
        // Giáº£i phÃ³ng bá»™ nhá»› cá»§a biáº¿n táº¡m trung gian trÆ°á»›c khi render view
        unset($logs, $rawTransactions);

        return view('account/history', [
            'user'         => $user,
            'histories'    => $histories,
            'transactions' => $transactions,
            'chartLabels'  => json_encode(array_values($chartLabels)),
            'chartData'    => json_encode(array_values($chartData)),
        ]);
    }
        
    public function HistoryDetail($id = null)
    {
        $user = app()->request->user;
    
        // Xá»­ lÃ½ lá»—i Ã©p kiá»ƒu Array thÃ nh sá»‘ 1 cá»§a PHP 8 khi Router truyá»n param
        $finalId = is_array($id) ? (int) end($id) : (int) $id;
        if ($finalId <= 0) {
            $finalId = (int) request('id');
        }
    
        $history = app()->db->get('originality_history', '*', [
            'id'           => $finalId,
            'account_uuid' => $user->uuid,
        ]);
    
        if (!$history) {
            $checkIdOnly = app()->db->get('originality_history', '*', ['id' => $finalId]);
            if ($checkIdOnly) {
                return '<div class="p-5 text-center text-danger">Lá»—i: BÃ¡o cÃ¡o tá»“n táº¡i nhÆ°ng khÃ´ng thuá»™c vá» UUID: '
                    . $user->uuid
                    . ' (UUID trong bÃ i lÃ : ' . $checkIdOnly['account_uuid'] . ')</div>';
            }
            return '<div class="p-5 text-center text-muted">Lá»—i: ID bÃ¡o cÃ¡o (' . $finalId . ') khÃ´ng tá»“n táº¡i trong database.</div>';
        }
    
        // â”€â”€ Decode táº¥t cáº£ cá»™t JSON â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $jsonCols = ['ai', 'plagiarism', 'grammar', 'readability', 'facts', 'content_optimizer', 'metadata'];
        foreach ($jsonCols as $col) {
            if (!empty($history[$col]) && is_string($history[$col])) {
                $history[$col] = json_decode($history[$col], true) ?? [];
            } elseif (empty($history[$col])) {
                $history[$col] = [];
            }
        }
    
        if (!empty($history['facts']) && !is_array(array_values($history['facts'])[0] ?? null)) {
            $history['facts'] = array_values($history['facts']);
        }
        $history['facts'] = array_values($history['facts'] ?? []);

        // â”€â”€ Phá»¥c há»“i dá»¯ liá»‡u náº¿u nÃ³ náº±m áº©n trong cá»™t metadata (Dá»¯ liá»‡u cÅ©) â”€â”€
        $metadata = $history['metadata'] ?? [];
        $resultData = $metadata['result']['results'] ?? $metadata['results'] ?? $metadata;
    
        $result = [
            'ai'               => !empty($history['ai']) ? $history['ai'] : ($resultData['ai'] ?? []),
            'plagiarism'       => !empty($history['plagiarism']) ? $history['plagiarism'] : ($resultData['plagiarism'] ?? []),
            'grammarSpelling'  => !empty($history['grammar']) ? $history['grammar'] : ($resultData['grammarSpelling'] ?? $resultData['grammar'] ?? []),
            'readability'      => !empty($history['readability']) ? $history['readability'] : ($resultData['readability'] ?? []),
            'facts'            => !empty($history['facts']) ? $history['facts'] : array_values($resultData['facts'] ?? []),
            'contentOptimizer' => !empty($history['content_optimizer']) ? $history['content_optimizer'] : ($resultData['contentOptimizer'] ?? $resultData['content_optimizer'] ?? []),
            'properties'       => !empty($history['metadata']['properties']) ? $history['metadata']['properties'] : ($resultData['properties'] ?? []),
            'credits'          => !empty($history['metadata']['credits']) ? $history['metadata']['credits'] : ($resultData['credits'] ?? []),
        ];
    
        // â”€â”€ CÃ¡c sá»‘ liá»‡u tá»•ng há»£p Ä‘á»ƒ hiá»ƒn thá»‹ nhanh â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $summary = [
            'title'             => $history['title']          ?? 'BÃ¡o cÃ¡o',
            'type'              => $history['type']           ?? 'text',
            'word_count'        => $history['word_count']     ?? 0,
            'points_used'       => $history['points_used']    ?? 0,
            'ai_score'          => $history['ai_score']       ?? null,   // % AI (0-100)
            'ai_model'          => $history['ai_model']       ?? null,
            'plag_score'        => $history['plag_score']     ?? null,   // % Ä‘áº¡o vÄƒn
            'grammar_errors'    => $history['grammar_errors'] ?? null,   // sá»‘ lá»—i
            'readability_score' => $history['readability_score'] ?? null,
            'readability_grade' => $history['readability_grade'] ?? null,
            'facts_total'       => $history['facts_total']    ?? null,
            'facts_true'        => $history['facts_true']     ?? null,
            'facts_errors'      => $history['facts_errors']   ?? null,
            'seo_score'         => $history['seo_score']      ?? null,
            'scan_url'          => $history['scan_url']       ?? null,
            'file_url'          => $history['file_url']       ?? null,
            'created_at'        => $history['created_at']     ?? null,
            'content'           => $history['content']        ?? null,
        ];
    
        return view('account/history-post', [
            'id'      => $finalId,
            'history' => $history,  
            'result'  => $result,    
            'summary' => $summary,   
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
    
        $balance = (float)(app()->db->get("wallets", "balance", [
            "account" => $accountId
        ]) ?: 0);
    
        $totalReferrals = (int)(app()->db->count("accounts", [
            "ref_by" => $user->affiliate
        ]) ?: 0);
    
        $totalEarned = (float)(app()->db->sum("transactions", "commission", [
            "account" => $user->uuid,
            "type"    => "commission",
            "status"  => 1
        ]) ?: 0);
    
        // Query lá»‹ch sá»­ hoa há»“ng JOIN accounts láº¥y email
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
    
    // ThÃªm tÃ i khoáº£n ngÃ¢n hÃ ng
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
    
        // Náº¿u set default â†’ reset cÃ¡c tÃ i khoáº£n khÃ¡c
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
            'alert'  => 'ThÃªm tÃ i khoáº£n thÃ nh cÃ´ng',
            'data'   => $data
        ]);
    }
    
    public function Payout() {
    
        $userId = app()->request->user->uuid;
        $userIdInt = app()->db->get("accounts", "id", ["uuid" => $userId]);
        if (!$userIdInt) {
            return response()->json(['status' => 'error', 'alert' => 'KhÃ´ng tÃ¬m tháº¥y tÃ i khoáº£n'], 404);
        }
        // Validate
        $validator = app()->validate(
            [
                'amount'            => 'required',
                'bank_account_uuid' => 'required',
            ],
            [
                'amount.required'            => 'Vui lÃ²ng nháº­p sá»‘ tiá»n',
                'bank_account_uuid.required' => 'Vui lÃ²ng chá»n tÃ i khoáº£n ngÃ¢n hÃ ng',
            ]
        );
    
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'alert' => $validator->first()], 400);
        }
    
        $amount = (int) request('amount');
        if ($amount < 100000) {
            return response()->json(['status' => 'error', 'alert' => 'Sá»‘ tiá»n rÃºt tá»‘i thiá»ƒu lÃ  100.000 VNÄ'], 400);
        }
    
        // Kiá»ƒm tra bank account cÃ³ thuá»™c vá» user khÃ´ng
        $bank = app()->db->get("bank_accounts", "*", [
            "uuid"    => request('bank_account_uuid'),
            "account" => $userId,
            "status"  => 1
        ]);
    
        if (!$bank) {
            return response()->json(['status' => 'error', 'alert' => 'TÃ i khoáº£n ngÃ¢n hÃ ng khÃ´ng há»£p lá»‡'], 400);
        }
    
        // Kiá»ƒm tra sá»‘ dÆ° wallet
        $wallet = app()->db->get("wallets", ["id", "balance"], ["account" => $userIdInt]);
        if (!$wallet || (float)$wallet['balance'] < $amount) {
            return response()->json(['status' => 'error', 'alert' => 'Sá»‘ dÆ° vÃ­ khÃ´ng Ä‘á»§'], 400);
        }
    
        // Trá»« tiá»n tá»« wallet
        $deducted = upsertWallet($userIdInt, $amount, '', 'withdraw');
        if (!$deducted) {
            return response()->json(['status' => 'error', 'alert' => 'KhÃ´ng thá»ƒ trá»« sá»‘ dÆ° vÃ­, vui lÃ²ng thá»­ láº¡i'], 500);
        }
    
        // Insert vÃ o transactions
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
            "note"         => "YÃªu cáº§u rÃºt tiá»n vá» " . $bank['bank_name'],
            "ip_address"   => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    
        return response()->json([
            'status' => 'success',
            'alert'  => 'YÃªu cáº§u rÃºt tiá»n Ä‘Ã£ Ä‘Æ°á»£c gá»­i! ChÃºng tÃ´i sáº½ xá»­ lÃ½ trong 1-2 ngÃ y lÃ m viá»‡c.',
        ]);
    }
    
    public function ConvertWalletToPoints() {
        $user = app()->request->user;
        $accountId = app()->db->get("accounts", "id", ["uuid" => $user->uuid]);
        $amount = (int) request('amount');
    
        if ($amount < 10000) {
            return response()->json(['status' => 'error', 'alert' => 'Sá»‘ tiá»n tá»‘i thiá»ƒu lÃ  10.000 V'], 400);
        }
    
        // Trá»« wallet
        $deducted = upsertWallet($accountId, $amount, '', 'withdraw');
        if (!$deducted) {
            return response()->json(['status' => 'error', 'alert' => 'Sá»‘ dÆ° vÃ­ khÃ´ng Ä‘á»§'], 400);
        }
    
        // Cá»™ng points dÃ¹ng upsertPoints
        $credited = upsertPoints($accountId, $amount, '', 'deposit');
        if (!$credited) {
            // Rollback láº¡i wallet náº¿u cá»™ng points tháº¥t báº¡i
            upsertWallet($accountId, $amount, '', 'commission');
            return response()->json(['status' => 'error', 'alert' => 'KhÃ´ng thá»ƒ cá»™ng Ä‘iá»ƒm, vui lÃ²ng thá»­ láº¡i'], 500);
        }
    
        return response()->json(['status' => 'success', 'alert' => 'Chuyá»ƒn Ä‘á»•i thÃ nh cÃ´ng!']);
    }

    // ===== VIP MEMBER MANAGEMENT =====
    public function Members() {
        $user = $this->app->request->user;
        
        if ($user->type != 2) {
            header("Location: /app");
            exit;
        }

        $limit  = min((int) (request('limit') ?? 1), 100);
        $page   = max((int) (request('page')  ?? 1), 1);
        $offset = ($page - 1) * $limit;
        $search = trim(request('search') ?? '');

        $conditions = [
            "accounts.deleted" => 0,
            "accounts.type" => 0,
            "accounts.ref_by" => $user->affiliate
        ];

        if (!empty($search)) {
            $conditions["OR"] = [
                "accounts.name[~]" => $search,
                "accounts.email[~]" => $search
            ];
        }

        $totalMembers = $this->app->db->count("accounts", $conditions);
        $totalPages = max(ceil($totalMembers / $limit), 1);

        $conditions["ORDER"] = ["accounts.date" => "DESC"];
        $conditions["LIMIT"] = [$offset, $limit];

        $members = $this->app->db->select("accounts", [
            "[>]points" => ["uuid" => "account"]
        ], [
            "accounts.id", "accounts.uuid", "accounts.name", "accounts.email", 
            "accounts.avatar", "accounts.date", "accounts.ref_by", "accounts.status",
            "points.points(point)"
        ], $conditions);

        return view('account/members', [
            'user' => $user,
            'members' => $members,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => $totalPages,
            'search' => $search
        ]);
    }

    public function AddMember() {
        $user = $this->app->request->user;
        if ($user->type != 2) {
            return response()->json(['status' => 'error', 'alert' => 'Không có quyền truy cập']);
        }

        $name = request('name');
        $email = request('email');
        $password = request('password') ?: '123456';

        if(empty($name) || empty($email)){
            return response()->json(['status' => 'error', 'alert' => 'Vui lòng nhập đầy đủ thông tin']);
        }

        $exist = $this->app->db->has("accounts", ["email" => $email]);
        if($exist) {
            return response()->json(['status' => 'error', 'alert' => 'Email đã tồn tại trên hệ thống']);
        }

        $uuid = bin2hex(random_bytes(16));
        
        $this->app->db->insert("accounts", [
            "uuid" => $uuid,
            "name" => $name,
            "email" => $email,
            "password" => password_hash($password, PASSWORD_DEFAULT),
            "type" => 0, // Thành viên thường
            "ref_by" => $user->affiliate, // Gắn vào VIP
            "status" => 1,
            "date" => date("Y-m-d H:i:s")
        ]);

        $this->app->db->insert("points", [
            "account" => $uuid,
            "points" => 0
        ]);

        return response()->json(['status' => 'success', 'alert' => 'Thêm thành viên thành công!']);
    }

    public function UpdateMember() {
        $user = $this->app->request->user;
        if ($user->type != 2) {
            return response()->json(['status' => 'error', 'alert' => 'Không có quyền truy cập']);
        }

        $uuid = request('uuid');
        $name = request('name');
        $email = request('email');
        $password = request('password');

        if(empty($uuid) || empty($name) || empty($email)){
            return response()->json(['status' => 'error', 'alert' => 'Vui lòng nhập đầy đủ thông tin']);
        }

        // Kiểm tra xem thành viên này có thuộc VIP này không
        $member = $this->app->db->get("accounts", "*", ["uuid" => $uuid, "ref_by" => $user->affiliate, "deleted" => 0]);
        if(!$member) {
            return response()->json(['status' => 'error', 'alert' => 'Không tìm thấy thành viên hoặc không có quyền']);
        }

        $exist = $this->app->db->has("accounts", ["email" => $email, "uuid[!]" => $uuid]);
        if($exist) {
            return response()->json(['status' => 'error', 'alert' => 'Email đã tồn tại trên hệ thống']);
        }

        $updateData = [
            "name" => $name,
            "email" => $email
        ];

        if(!empty($password)){
            $updateData["password"] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->app->db->update("accounts", $updateData, ["uuid" => $uuid]);

        return response()->json(['status' => 'success', 'alert' => 'Cập nhật thành công!']);
    }

    public function DeleteMember() {
        $user = $this->app->request->user;
        if ($user->type != 2) {
            return response()->json(['status' => 'error', 'alert' => 'Không có quyền truy cập']);
        }

        $uuid = request('uuid');
        
        $member = $this->app->db->get("accounts", "*", ["uuid" => $uuid, "ref_by" => $user->affiliate, "deleted" => 0]);
        if(!$member) {
            return response()->json(['status' => 'error', 'alert' => 'Không tìm thấy thành viên hoặc không có quyền']);
        }

        $this->app->db->update("accounts", ["deleted" => 1], ["uuid" => $uuid]);

        return response()->json(['status' => 'success', 'alert' => 'Đã xóa thành viên']);
    }

    public function ToggleMemberStatus() {
        $user = $this->app->request->user;
        if ($user->type != 2) {
            return response()->json(['status' => 'error', 'alert' => 'Không có quyền truy cập']);
        }

        $uuid = request('uuid');
        $status = request('status');

        $member = $this->app->db->get("accounts", "*", ["uuid" => $uuid, "ref_by" => $user->affiliate, "deleted" => 0]);
        if(!$member) {
            return response()->json(['status' => 'error', 'alert' => 'Không tìm thấy thành viên hoặc không có quyền']);
        }

        $this->app->db->update("accounts", ["status" => (int)$status], ["uuid" => $uuid]);

        return response()->json(['status' => 'success']);
    }
}
