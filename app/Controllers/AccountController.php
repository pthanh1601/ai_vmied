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
        
        // $account->type = $account->type == 0 ? 'Thành Viên' : 'Quản trị';
        $account->type_id = $account->type;
        $account->type = $account->type == 1 ? 'Quản trị' : ($account->type == 2 ? 'VIP' : 'Thành Viên');
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
        
        $account->type_id = $account->type;
        $account->type = $account->type == 1 ? 'Quản trị' : ($account->type == 2 ? 'VIP' : 'Thành Viên');

        // 1. CHUẨN HÓA LOGIC: THÁNG NÀY NHẬN ĐƯỢC BAO NHIÊU ĐIỂM (V)
        $monthlyIncome = app()->db->sum("points_historys", "point", [
            "account"  => $user->uuid,
            "point[>]" => 0, // Chỉ tính các giao dịch CỘNG điểm
            "date[<>]" => [date('Y-m-01 00:00:00'), date('Y-m-t 23:59:59')],
            "deleted"  => 0
        ]) ?: 0;

        // 2. CHUẨN HÓA LOGIC: TỔNG SỐ ĐIỂM ĐÃ SỬ DỤNG
        $totalUsed = abs(app()->db->sum("points_historys", "point", [
            "account"  => $user->uuid,
            "point[<]" => 0, // Chỉ tính các giao dịch TRỪ điểm
            "deleted"  => 0          
        ]) ?: 0);

        // 3. SỬA LỖI QUERY LỊCH SỬ GIAO DỊCH (Chỉ dùng ID)
        $payments = app()->db->select("transactions", "*", [
            "account" => $account->id, // Sửa lỗi ở đây
            "ORDER"   => ["created_at" => "DESC"],
            "LIMIT"   => 5
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
        $userSession = app()->request->user;
        $userId = $userSession->uuid;
        
        if (!$userId) {
            return response()->json(['status' => 'error', 'alert' => 'Vui lòng đăng nhập'], 401);
        }

        // Validate dữ liệu
        $validator = app()->validate(
            [
                'name' => 'required',
                'phone' => 'required', 
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

        // Chuẩn bị dữ liệu update
        $updateData = [
            "name"         => app()->xss->clean(request('name')),
            "phone"        => app()->xss->clean(request('phone')),
            "organization" => app()->xss->clean(request('organization')),
        ];

        // LOGIC ĐỔI LOGO DÀNH RIÊNG CHO VIP (type = 2)
        if ($userSession->type == 2 && isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
            $uploadDir = 'public/uploads/avatar/';
            $rootPath  = dirname(__DIR__, 2) . '/';
            $fullDir   = $rootPath . $uploadDir;

            if (!is_dir($fullDir)) {
                mkdir($fullDir, 0755, true);
            }

            // Loại bỏ ký tự đặc biệt khỏi tên file
            $safeName = preg_replace('/[^a-zA-Z0-9.\-_]/', '', basename($_FILES['avatar']['name']));
            $filename = uniqid() . '-' . $safeName;
            $destination = $fullDir . $filename;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
                $updateData['avatar'] = '/uploads/avatar/' . $filename;
            }
        }

        // Cập nhật Database
        $result = app()->db->update("accounts", $updateData, ["uuid" => $userId]);

        if ($result) {
            // Cập nhật lại Session để Logo trên thanh Menu thay đổi ngay lập tức
            $sessionAccount = app()->session->get('account');
            if ($sessionAccount) {
                $sessionAccount['name']         = $updateData['name'];
                $sessionAccount['organization'] = $updateData['organization'];
                if (isset($updateData['avatar'])) {
                    $sessionAccount['avatar']   = $updateData['avatar'];
                }
                app()->session->set('account', $sessionAccount);
            }

            return response()->json([
                'status' => 'success',
                'alert'  => 'Cập nhật thông tin thành công',
                'reload' => true // Reload lại trang để tải logo mới
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
    
    // public function uploadsPDF()
    // {
    //     $user = app()->request->user;
    //     $uuid = $user->uuid ;
    //     if (!$uuid) {
    //         header('Content-Type: application/json');
    //         http_response_code(401);
    //         echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập.']);
    //         exit;
    //     }

    //     if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    //         header('Content-Type: application/json');
    //         http_response_code(400);
    //         echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy file hoặc file bị lỗi.']);
    //         exit;
    //     }

    //     $file = $_FILES['file'];
    //     $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    //     // Chỉ cho phép định dạng .pdf và .docx đúng như frontend yêu cầu
    //     if (!in_array($ext, ['pdf', 'docx'])) {
    //         header('Content-Type: application/json');
    //         http_response_code(400);
    //         echo json_encode(['status' => 'error', 'message' => 'Định dạng file không hỗ trợ (Chỉ nhận .pdf, .docx).']);
    //         exit;
    //     }

    //     // Định nghĩa thư mục lưu trữ trên live host (Đảm bảo thư mục này có quyền ghi - Chmod 755 hoặc 777)
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
    //             'message' => 'Upload file thành công.',
    //             'url' => $fileUrl
    //         ]);
    //         exit;
    //     } else {
    //         header('Content-Type: application/json');
    //         http_response_code(500);
    //         echo json_encode(['status' => 'error', 'message' => 'Không thể lưu file vào thư mục máy chủ.']);
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
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập.']);
            exit;
        }
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            header('Content-Type: application/json');
            http_response_code(400);
    
            // Báo rõ lý do nếu lỗi do vượt giới hạn upload của PHP (upload_max_filesize / post_max_size)
            $err = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
            $msg = in_array($err, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE])
                ? 'File vượt quá giới hạn upload cho phép.'
                : 'Không tìm thấy file hoặc file bị lỗi.';
    
            echo json_encode(['status' => 'error', 'message' => $msg]);
            exit;
        }
    
        $file = $_FILES['file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        // Chỉ cho phép định dạng .pdf và .docx đúng như frontend yêu cầu
        if (!in_array($ext, ['pdf', 'docx'])) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Định dạng file không hỗ trợ (Chỉ nhận .pdf, .docx).']);
            exit;
        }
    
        // ─── Giới hạn kích thước file ──────────────────────────────
        $maxSizeBytes = 20 * 1024 * 1024; // 20MB — chỉnh số này theo nhu cầu
        if ($file['size'] > $maxSizeBytes) {
            $sizeMB = round($file['size'] / 1024 / 1024, 1);
            $maxMB  = $maxSizeBytes / 1024 / 1024;
            header('Content-Type: application/json');
            http_response_code(413); // Payload Too Large
            echo json_encode([
                'status'  => 'error',
                'message' => "File quá lớn ({$sizeMB}MB). Vui lòng chọn file dưới {$maxMB}MB.",
            ]);
            exit;
        }
        // ─────────────────────────────────────────────────────────
    
        // Định nghĩa thư mục lưu trữ trên live host (Đảm bảo thư mục này có quyền ghi - Chmod 755 hoặc 777)
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
                'message' => 'Upload file thành công.',
                'url' => $fileUrl
            ]);
            exit;
        } else {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Không thể lưu file vào thư mục máy chủ.']);
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
            "grammar_errors", "readability_score",   // ← thêm 2 cột này
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
            $readScore     = $item['readability_score'] ?? null; // có thể null hoặc 0 thật, cần phân biệt
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
            } elseif ($grammarErrors > 0) {
                $resultText  = "{$grammarErrors} lỗi";
                $colorClass  = $grammarErrors > 10 ? 'text-danger' : 'text-warning';
                $icon        = 'spell-check';
                $bgColor     = 'bg-warning-subtle text-warning';
                $serviceText = 'Ngữ pháp';
            } elseif ($readScore !== null) {
                $resultText  = "Điểm {$readScore}";
                $colorClass  = 'text-info';
                $icon        = 'book-open';
                $bgColor     = 'bg-info-subtle text-info';
                $serviceText = 'Đọc hiểu';
            } elseif ($grammarErrors === 0 && ($item['grammar_errors'] ?? null) !== null) {
                // Có bật check ngữ pháp nhưng 0 lỗi -> vẫn nên hiện, không phải "chưa xác định"
                $resultText  = 'Không lỗi';
                $colorClass  = 'text-success';
                $icon        = 'spell-check';
                $bgColor     = 'bg-success-subtle text-success';
                $serviceText = 'Ngữ pháp';
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
                'id'         => $id,
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
    
        // ── Lịch sử giao dịch (Tối ưu: Chỉ lấy cột cần, LIMIT 50) ────────────────────────────
        $rawTransactions = app()->db->select("transactions", [
            "id", "type", "vmied", "amount", "created_at" 
        ], [
            "account" => $user->uuid,
            "ORDER"   => ["created_at" => "DESC"],
            "LIMIT"   => 50 // Giới hạn giao dịch hiển thị
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
    
        // Giải phóng bộ nhớ của biến tạm trung gian trước khi render view
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
    
        // Xử lý lỗi ép kiểu Array thành số 1 của PHP 8 khi Router truyền param
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
                return '<div class="p-5 text-center text-danger">Lỗi: Báo cáo tồn tại nhưng không thuộc về UUID: '
                    . $user->uuid
                    . ' (UUID trong bài là: ' . $checkIdOnly['account_uuid'] . ')</div>';
            }
            return '<div class="p-5 text-center text-muted">Lỗi: ID báo cáo (' . $finalId . ') không tồn tại trong database.</div>';
        }
    
        // ── Decode tất cả cột JSON ────────────────────────────────────────
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

        // ── Phục hồi dữ liệu nếu nó nằm ẩn trong cột metadata (Dữ liệu cũ) ──
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
    
        // ── Các số liệu tổng hợp để hiển thị nhanh ───────────────────────
        $summary = [
            'title'             => $history['title']          ?? 'Báo cáo',
            'type'              => $history['type']           ?? 'text',
            'word_count'        => $history['word_count']     ?? 0,
            'points_used'       => $history['points_used']    ?? 0,
            'ai_score'          => $history['ai_score']       ?? null,   // % AI (0-100)
            'ai_model'          => $history['ai_model']       ?? null,
            'plag_score'        => $history['plag_score']     ?? null,   // % đạo văn
            'grammar_errors'    => $history['grammar_errors'] ?? null,   // số lỗi
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
    
    
    
    public function Members() {
        $user = $this->app->request->user;
        
        if ($user->type != 2) {
            header("Location: /app");
            exit;
        }

        $limit  = min((int) (request('limit') ?? 15), 100);
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
    
    public function MemberHistory() {
        $user = $this->app->request->user;
        
        if ($user->type != 2) {
            header("Location: /app");
            exit;
        }

        $memberUuid = request('uuid');
        
        // 1. Lấy thông tin học viên con (bao gồm ID số nguyên = 17)
        $member = $this->app->db->get("accounts", ["id", "uuid", "name", "email", "ref_by", "date"], [
            "uuid"    => $memberUuid,
            "ref_by"  => $user->affiliate,
            "type"    => 0,
            "deleted" => 0
        ]);

        if (!$member) {
            return "<div style='padding:50px; text-align:center; font-family:sans-serif;'>Lỗi: Không tìm thấy học viên hoặc bạn không có quyền xem thông tin này!</div>";
        }

        // 2. Lấy ID số nguyên của VIP đang đăng nhập (= 16)
        $vipId = $this->app->db->get("accounts", "id", ["uuid" => $user->uuid]);

        // 3. TÍNH TỔNG HOA HỒNG: 
        // Lọc những dòng giao dịch hoa hồng có account = 16 VÀ referrer = 17
        $totalCommission = $this->app->db->sum("transactions", "commission", [
            "account"  => $vipId,        // ID VIP (16) nhận tiền
            "referrer" => $member['id'], // ID Con (17) nạp tiền
            "type"     => "commission",
            "status"   => 1
        ]) ?: 0;

        // 4. Lấy lịch sử quét bài của học viên con
        $scans = $this->app->db->select("originality_history", [
            "id", "title", "type", "word_count", "points_used", "created_at",
            "ai_score", "plag_score", "grammar_errors", "readability_score"
        ], [
            "account_uuid" => $memberUuid,
            "ORDER"        => ["created_at" => "DESC"],
            "LIMIT"        => 50
        ]) ?: [];

        // 5. Lấy lịch sử nạp tiền/trừ điểm của học viên con (account = 17)
        $transactions = $this->app->db->select("transactions", [
            "id", "code", "amount", "vmied", "type", "status", "created_at", "note"
        ], [
            "account" => $member['id'], // ID Con (17)
            "ORDER"   => ["created_at" => "DESC"],
            "LIMIT"   => 50
        ]) ?: [];

        return view('account/member_history', [
            'user'            => $user,
            'member'          => $member,
            'totalCommission' => $totalCommission,
            'scans'           => $scans,
            'transactions'    => $transactions
        ]);
    }
    
}