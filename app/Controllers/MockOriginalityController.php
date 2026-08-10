<?php

namespace App\Controllers;

class MockOriginalityController
{
    const COST_PER_100_WORDS = 1000;
    const COST_PER_1K_TOKENS  = 250;
    const MAX_PDF_SIZE_MB    = 20;
    const COPYSCAPE_MAX_WORDS_PER_CALL = 9000;
    const COPYSCAPE_AI_MAX_WORDS_PER_CALL = 3500;

    private function calcCostFromTokens(int $tokens): int
    {
        if ($tokens <= 0) return 0;
        return (int) ceil($tokens / 1000) * self::COST_PER_1K_TOKENS;
    }

    // private function calcCost(string $content): int
    // {
    //     if (empty($content)) return 0;
    //     $words = preg_match_all('/\S+/u', strip_tags($content));
    //     return (int) ceil($words / 100) * self::COST_PER_100_WORDS;
    // }
    private function calcCost(int $wordCount): int
    {
        if ($wordCount <= 0) return 0;
        return (int) ceil($wordCount / 100) * self::COST_PER_100_WORDS;
    }

    private function countWords(string $content): int
    {
        return (int) preg_match_all('/\S+/u', strip_tags($content));
    }

    private function restoreFormatting(string $originalContent, array &$blocks): void
    {
        if (empty($blocks) || empty($originalContent)) return;

        $origChars   = preg_split('//u', $originalContent, -1, PREG_SPLIT_NO_EMPTY);
        $originalLen = count($origChars);
        $offset      = 0;

        foreach ($blocks as &$block) {
            $text = $block['text'] ?? '';
            if (empty($text)) continue;

            $textCharsStr = preg_replace('/[^\p{L}\p{N}]+/u', '', mb_strtolower($text, 'UTF-8'));
            $textCharsLen = mb_strlen($textCharsStr, 'UTF-8');
            $currentPos   = $offset;
            $matchLen     = 0;

            if ($textCharsLen > 0) {
                while ($currentPos < $originalLen && $matchLen < $textCharsLen) {
                    $charOrig = mb_strtolower($origChars[$currentPos], 'UTF-8');
                    if (preg_match('/^[\p{L}\p{N}]$/u', $charOrig)) $matchLen++;
                    $currentPos++;
                }
            } else {
                $textLen   = mb_strlen($text, 'UTF-8');
                $matchLen2 = 0;
                while ($currentPos < $originalLen && $matchLen2 < $textLen) {
                    $currentPos++; $matchLen2++;
                }
            }

            while ($currentPos < $originalLen) {
                $charOrig = mb_strtolower($origChars[$currentPos], 'UTF-8');
                if (preg_match('/^[\p{L}\p{N}]$/u', $charOrig)) break;
                $currentPos++;
            }

            $block['text'] = mb_substr($originalContent, $offset, $currentPos - $offset, 'UTF-8');
            $offset        = $currentPos;
        }

        if ($offset < $originalLen && count($blocks) > 0) {
            $blocks[count($blocks) - 1]['text'] .= mb_substr($originalContent, $offset, null, 'UTF-8');
        }
    }

    private function estimateCostFromUrl(string $url): int
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY         => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; OriginalityScanner/1.0)',
        ]);
        curl_exec($ch);
        $size = (int) curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);
        $words = (int) ceil(max($size, 1000) / 5);
        return (int) ceil($words / 100) * self::COST_PER_100_WORDS;
    }

    private function getUuid(): ?string
    {
        return app()->request->user->uuid
            ?? (app()->session->get('account')['uuid'] ?? null);
    }

    private function getAccountId(string $uuid): ?int
    {
        $id = app()->db->get('accounts', 'id', ['uuid' => $uuid]);
        return $id ? (int) $id : null;
    }

    private function getPoints(string $uuid): int
    {
        return (int) (app()->db->get('points', 'points', ['account' => $uuid]) ?? 0);
    }

    private function deductPoints(string $uuid, int $cost, string $ref): void
    {
        $accountId = $this->getAccountId($uuid);
        if ($accountId && $cost > 0) {
            upsertPoints($accountId, $cost, $ref . '-' . uniqid(), 'use');
        }
    }

    // ============================================================
    // OPENAI FILES API HELPERS
    // ============================================================

    private function uploadToOpenAIFiles(string $filePath): array
    {
        $apiKey = $_ENV['GPT_KEY'] ?? getenv('GPT_KEY') ?? '';
        if (empty($apiKey)) return ['success' => false, 'message' => 'GPT_KEY chưa được cấu hình'];

        if (!is_file($filePath) || !is_readable($filePath))
            return ['success' => false, 'message' => 'File không tồn tại: ' . $filePath];

        if (filesize($filePath) > self::MAX_PDF_SIZE_MB * 1024 * 1024)
            return ['success' => false, 'message' => 'File vượt quá ' . self::MAX_PDF_SIZE_MB . 'MB'];

        $cfile = new \CURLFile($filePath, 'application/pdf', basename($filePath));
        $ch    = curl_init('https://api.openai.com/v1/files');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => ['purpose' => 'user_data', 'file' => $cfile],
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey],
        ]);
        $raw    = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err    = curl_error($ch);
        curl_close($ch);

        if ($err) return ['success' => false, 'message' => 'cURL error: ' . $err];

        $data = json_decode($raw, true);
        if ($status !== 200 || empty($data['id']))
            return ['success' => false, 'message' => $data['error']['message'] ?? "HTTP {$status}"];

        return ['success' => true, 'file_id' => $data['id']];
    }

    private function deleteOpenAIFile(string $fileId): void
    {
        $apiKey = $_ENV['GPT_KEY'] ?? getenv('GPT_KEY') ?? '';
        if (empty($apiKey) || empty($fileId)) return;
        $ch = curl_init('https://api.openai.com/v1/files/' . urlencode($fileId));
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey],
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    private function resolveUploadedFilePath(string $url): ?string
    {
        $urlPath  = parse_url($url, PHP_URL_PATH);
        $fileName = basename($urlPath);
        if (empty($fileName)) return null;

        // Lấy subfolder tương đối sau /uploads/scans/ (vd: 2026/06)
        $afterScans = preg_replace('#^.*/uploads/scans/#', '', $urlPath);
        $subDir     = ltrim(dirname($afterScans), './');

        $roots = [
            '/www/wwwroot/ai.vmied.com/public/uploads/scans/',
            rtrim(app()->basePath(), '/') . '/public/uploads/scans/',
        ];

        foreach ($roots as $root) {
            $candidates = [
                $root . ($subDir && $subDir !== '.' ? $subDir . '/' : '') . $fileName,
                $root . $fileName,
            ];
            foreach ($candidates as $p) {
                if (is_file($p) && is_readable($p)) return $p;
            }
        }
        return null;
    }

    // ============================================================
    // ENDPOINTS
    // ============================================================

    public function getBalance()
    {
        $this->verifyKey();
        $uuid = $this->getUuid();
        if (!$uuid) return $this->response(['error' => 'Vui lòng đăng nhập.'], 401);
        return $this->response(['credits' => $this->getPoints($uuid), 'subscriptionCredits' => 0]);
    }

    public function uploadPdf()
    {
        $this->verifyKey();
        $uuid = $this->getUuid();
        if (!$uuid)
            return $this->response(['status' => 'error', 'message' => 'Vui lòng đăng nhập.'], 401);

        if (empty($_FILES['file']))
            return $this->response(['status' => 'error', 'message' => 'Không tìm thấy file.'], 422);

        $file = $_FILES['file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $phpErrors = [
                UPLOAD_ERR_INI_SIZE   => 'File vượt upload_max_filesize trong php.ini.',
                UPLOAD_ERR_FORM_SIZE  => 'File vượt MAX_FILE_SIZE trong form.',
                UPLOAD_ERR_PARTIAL    => 'File chỉ upload một phần.',
                UPLOAD_ERR_NO_FILE    => 'Không có file nào được upload.',
                UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm.',
                UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file.',
                UPLOAD_ERR_EXTENSION  => 'Extension PHP chặn upload.',
            ];
            return $this->response(['status' => 'error', 'message' => $phpErrors[$file['error']] ?? 'Upload thất bại'], 422);
        }

        if ($file['size'] > self::MAX_PDF_SIZE_MB * 1024 * 1024)
            return $this->response(['status' => 'error', 'message' => 'File quá lớn. Tối đa ' . self::MAX_PDF_SIZE_MB . 'MB.'], 422);

        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($mimeType !== 'application/pdf')
            return $this->response(['status' => 'error', 'message' => 'Chỉ chấp nhận file PDF. Phát hiện: ' . $mimeType], 422);

        // Tạo subfolder năm/tháng
        $subDir    = date('Y/m') . '/';
        $uploadDir = '/www/wwwroot/ai.vmied.com/public/uploads/scans/' . $subDir;

        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        if (!is_writable($uploadDir))
            return $this->response(['status' => 'error', 'message' => 'Thư mục upload không có quyền ghi: ' . $uploadDir], 500);

        $safeUuid = preg_replace('/[^a-zA-Z0-9\-]/', '', $uuid);
        $fileName = $safeUuid . '_' . time() . '_' . bin2hex(random_bytes(6)) . '.pdf';
        $destPath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destPath))
            return $this->response(['status' => 'error', 'message' => 'Không thể lưu file lên server.'], 500);

        $publicUrl = 'https://ai.vmied.com/uploads/scans/' . $subDir . $fileName;
        error_log('[OriginalityUpload] uuid=' . $uuid . ' file=' . $fileName . ' size=' . $file['size']);

        return $this->response(['status' => 'ok', 'url' => $publicUrl, 'fileName' => $fileName, 'size' => $file['size']]);
    }

    // ============================================================
    // SCAN
    // ============================================================

    public function scan()
    {
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', '300');
        set_time_limit(300);
 
        $this->verifyKey();
 
        $input   = $this->getInput();
        $content = trim($input['content'] ?? '');
        $title   = $input['title'] ?? 'Scan';
        $fileUrl = trim($input['file_url'] ?? '');
 
        // ── Lưu cờ check ngay từ đầu vì $input bị unset() giữa hàm ──
        $checkAiFlag          = (bool) ($input['check_ai']          ?? true);
        $checkPlagiarismFlag  = (bool) ($input['check_plagiarism']  ?? true);
        $checkGrammarFlag     = (bool) ($input['check_grammar']     ?? true);
        $checkReadabilityFlag = (bool) ($input['check_readability'] ?? true);
 
        $uuid = $this->getUuid();
        if (!$uuid)
            return $this->response(['status' => 'error', 'message' => 'Vui lòng đăng nhập.'], 401);
 
        $hasPdfFile = !empty($fileUrl)
            && filter_var($fileUrl, FILTER_VALIDATE_URL)
            && $this->isOwnUploadedPdf($fileUrl, $uuid);
 
        // ==============================================================
        // BƯỚC 1: pdftotext — extract text, chốt wordCount
        // ==============================================================
        $pdfIsImageOnly = false;
 
        if ($hasPdfFile) {
            $diskPath = $this->resolveUploadedFilePath($fileUrl);
            if ($diskPath && is_file($diskPath)) {
                $content = $this->normalizePdfText(
                    trim(shell_exec('pdftotext ' . escapeshellarg($diskPath) . ' - 2>/dev/null') ?? '')
                );
                if (empty($content)) {
                    return $this->response(['status' => 'error', 'message' => 'Tài liệu là file ảnh (Scan). Vui lòng chuyển đổi sang dạng văn bản.'], 422);
                }
            } else {
                return $this->response(['status' => 'error', 'message' => 'Không tìm thấy file trên máy chủ.'], 404);
            }
        }
 
        if (!$hasPdfFile && empty($content))
            return $this->response(['status' => 'error', 'message' => 'Nội dung không được để trống.'], 422);
 
        $activeCheckCount = $this->countActiveChecks($input);
        
        if (!$hasPdfFile && !empty($content)) {
            // Lọc văn bản từ text form hoặc .docx (frontend bóc)
            $content = $this->sanitizeContent($content);
        } elseif ($hasPdfFile && !empty($content)) {
            // Lọc văn bản bóc từ pdftotext
            $content = $this->sanitizeContent($content);
        }
 
        // Chốt wordCount duy nhất cho toàn bộ flow
        $wordCount = !empty($content) ? $this->countWords($content) : 0;
 
        $cost    = $this->calcCost($wordCount) * $activeCheckCount;
        $balance = $this->getPoints($uuid);
 
        if ($balance < $cost) {
            $detail = $hasPdfFile
                ? "Cần ước tính {$cost}đ để kiểm tra file PDF"
                : "Cần {$cost}đ để kiểm tra {$wordCount} từ";
            return $this->response([
                'status'   => 'error',
                'code'     => 'INSUFFICIENT_POINTS',
                'message'  => "{$detail}, bạn còn " . number_format($balance) . "đ.",
                'required' => $cost,
                'balance'  => $balance,
            ], 402);
        }
 
        // ==============================================================
        // BƯỚC 2: Gọi API — truyền $content (đã có cho cả PDF lẫn text)
        // callAPI sẽ: upload PDF lên OpenAI cho grammar, gọi Copyscape AI,
        // gọi Copyscape Plagiarism — tất cả trong 1 hàm.
        // ==============================================================
        $apiResult = $this->callAPI(
            $hasPdfFile ? $fileUrl : null,
            null,
            $content,   // luôn truyền $content — callAPI dùng để Copyscape, readability
            $title,
            $input
        );
 
        if (!$apiResult['success']) {
            unset($content, $input);
            return $this->response([
                'status'  => 'error',
                'message' => 'Lỗi: ' . $apiResult['message'],
            ], 400);
        }
 
        $results = $this->unwrapV3($apiResult['data']);
        unset($apiResult, $input);
 
        $actualCost  = $cost;
        $tokensUsed  = (int) ($results['tokensUsed'] ?? 0);
        // 1. Tiền Token GPT
        $gptCostVnd = $this->calcCostFromTokens($tokensUsed);

        // 2. Tiền Copyscape AI + Plagiarism (Tỷ giá tạm tính: 25.400đ / 1 USD)
        $exchangeRate = 25400; 
        $copyscapeAiUsd   = (float) ($results['ai']['cost_usd'] ?? 0);
        $copyscapePlagUsd = (float) ($results['plagiarism']['cost_usd'] ?? 0);
        
        $copyscapeCostVnd = (int) round(($copyscapeAiUsd + $copyscapePlagUsd) * $exchangeRate);

        // 3. TỔNG CHI PHÍ THỰC TẾ VỐN API
        $capitalCost = $copyscapeCostVnd;

        $profit = $actualCost - $capitalCost;
 
        error_log(sprintf(
            '[SAAS-FINANCE] uuid=%s words=%d tokens=%d sell=%dđ cost=%dđ profit=%dđ',
            $uuid, $wordCount, $tokensUsed, $actualCost, $capitalCost, $profit
        ));
 
        // restoreFormatting chỉ chạy khi text mode (cần $content gốc)
        if (!$hasPdfFile && isset($results['ai']['blocks']) && is_array($results['ai']['blocks'])) {
            $this->restoreFormatting($content, $results['ai']['blocks']);
        }
 
        if (!isset($results['properties'])) $results['properties'] = [];
        $results['properties']['content']          = $content;
        $results['properties']['formattedContent'] = $content;
        $results['properties']['wordCount']        = $wordCount;
 
        $results['pointsUsed']      = $actualCost;
        $results['pointsRemaining'] = max(0, $balance - $actualCost);
        $results['capitalCost']     = $capitalCost;
 
        $this->deductPoints($uuid, $actualCost, $hasPdfFile ? 'PDF' : 'SCAN');
        $this->ensureTable();
        $this->saveHistory(
            $title,
            $content, // Thay đổi: Luôn truyền $content vào để detectLang có thể đọc và lưu
            null,
            $hasPdfFile ? $fileUrl : null,
            $hasPdfFile ? 'pdf' : 'text',
            $results, $activeCheckCount, $capitalCost,
            $checkAiFlag, $checkPlagiarismFlag, $checkGrammarFlag, $checkReadabilityFlag
        );
        $results['scanId']     = (int) app()->db->id();
        $results['newBalance'] = $this->getPoints($uuid);
 
        $sessionAccount = app()->session->get('account');
        if ($sessionAccount) {
            $sessionAccount['point'] = $results['newBalance'];
            app()->session->set('account', $sessionAccount);
            app()->request->user = (object) $sessionAccount;
        }
 
        unset($content);
        return $this->response($results);
    }

    public function history()
    {
        $this->verifyKey();
        $this->ensureTable();
        $uuid = $this->getUuid();
        if (!$uuid) return $this->response(['error' => 'Vui lòng đăng nhập.'], 401);

        $rows = app()->db->select('originality_history', [
            'id', 'type', 'title', 'scan_url', 'file_url',
            'word_count', 'points_used',
            'ai_score', 'plag_score', 'grammar_errors',
            'readability_score', 'facts_errors', 'seo_score',
            'status', 'created_at',
        ], [
            'account_uuid' => $uuid,
            'ORDER'        => ['created_at' => 'DESC'],
            'LIMIT'        => 100,
        ]);

        return $this->response(['history' => $rows ?? []]);
    }

    public function getScanById($id)
    {
        $this->verifyKey();
        $uuid = $this->getUuid();
        if (!$uuid) return $this->response(['error' => 'Vui lòng đăng nhập.'], 401);

        $row = app()->db->get('originality_history', '*', ['id' => (int) $id, 'account_uuid' => $uuid]);
        if (!$row) return $this->response(['error' => 'Không tìm thấy bản ghi.'], 404);

        foreach (['ai', 'plagiarism', 'grammar', 'readability', 'facts', 'content_optimizer', 'metadata'] as $col) {
            if (isset($row[$col]) && is_string($row[$col])) $row[$col] = json_decode($row[$col], true);
        }

        return $this->response(['result' => $row]);
    }


    // ============================================================
    // CORE API CALL — parallel curl_multi (tất cả GPT calls song song)
    // ============================================================

    private function callAPI(?string $url, ?string $fileUrl, ?string $content, string $title, array $input): array
    {
        $apiKey = $_ENV['GPT_KEY'] ?? getenv('GPT_KEY') ?? '';
        if (empty($apiKey)) return ['success' => false, 'message' => 'GPT_KEY chưa được cấu hình trong .env'];
 
        $checkAi          = (bool) ($input['check_ai']              ?? true);
        $checkPlagiarism  = (bool) ($input['check_plagiarism']       ?? true);
        $checkFacts       = (bool) ($input['check_facts']            ?? false);
        $checkReadability = (bool) ($input['check_readability']      ?? true);
        $checkGrammar     = (bool) ($input['check_grammar']          ?? true);
        $checkSeo         = (bool) ($input['check_contentOptimizer'] ?? false);
 
        $totalTokensUsed = 0;
 
        // ── Upload PDF lên OpenAI (cho grammar PDF mode) ─────────────
        $openaiFileId  = null;
        $isPdfFileMode = false;
 
        if (!empty($url)) {
            $diskPath = $this->resolveUploadedFilePath($url);
            if ($diskPath !== null) {
                error_log('[callAPI] PDF on disk, uploading to OpenAI: ' . $diskPath);
                $uploadResult = $this->uploadToOpenAIFiles($diskPath);
                if ($uploadResult['success']) {
                    $openaiFileId  = $uploadResult['file_id'];
                    $isPdfFileMode = true;
                    error_log('[callAPI] OpenAI file_id=' . $openaiFileId);
                } else {
                    error_log('[callAPI] OpenAI upload failed: ' . $uploadResult['message']);
                    // Fallback: vẫn dùng $content text (đã có từ scan())
                }
            }
        }
 
        if (empty($content))
            return ['success' => false, 'message' => 'Không có nội dung để phân tích.'];
 
        // $content đã có cho cả text mode lẫn PDF mode (từ pdftotext trong scan())
        $textSlice = mb_substr($content, 0, 12000, 'UTF-8'); // dùng cho GPT
        $wordCount = $this->countWords($content);
 
        // ── Helper: build user message cho GPT ───────────────────────
        $buildUserMsg = function (string $instruction) use ($isPdfFileMode, $openaiFileId, $textSlice, $content): mixed {
            if ($isPdfFileMode && $openaiFileId) {
                return [
                    ['type' => 'text', 'text' => $instruction],
                    ['type' => 'file', 'file' => ['file_id' => $openaiFileId]],
                ];
            }
            return $instruction . "\nText:\n" . $textSlice;
        };
 
        // ── Helper: build curl handle GPT ────────────────────────────
        $buildHandle = function (string $systemPrompt, mixed $userMsg, string $model = 'gpt-5.4-mini') use ($apiKey): \CurlHandle {
            $payload = json_encode([
                'model'                 => $model,
                'temperature'           => 0,
                'max_completion_tokens' => 3000,
                'messages'              => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $userMsg],
                ],
                'response_format' => ['type' => 'json_object'],
            ], JSON_UNESCAPED_UNICODE);
 
            $ch = curl_init('https://api.openai.com/v1/chat/completions');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_TIMEOUT        => 90,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey,
                ],
            ]);
            return $ch;
        };
 
        // ── Helper: parse GPT response ────────────────────────────────
        $parseResponse = function (string $raw) use (&$totalTokensUsed): ?array {
            if (empty($raw)) return null;
            $resp = json_decode($raw, true);
            if (!empty($resp['usage']['total_tokens'])) {
                $totalTokensUsed += (int) $resp['usage']['total_tokens'];
            }
            if (empty($resp['choices'][0]['message']['content'])) return null;
            $text = $resp['choices'][0]['message']['content'];
            $text = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
            $text = preg_replace('/\s*```$/i', '', $text);
            $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text);
            $parsed = json_decode($text, true);
            return (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) ? $parsed : null;
        };
 
        $results = [];
 
        // ==============================================================
        // BƯỚC 1: Copyscape AI Detection (tuần tự, chunks)
        // ==============================================================
        if ($checkAi) {
            $results['ai'] = $this->callCopyscapeAI($content);
        }
 
        // ==============================================================
        // BƯỚC 2: Copyscape Plagiarism (tuần tự, chunks)
        // ==============================================================
        if ($checkPlagiarism) {
            $results['plagiarism'] = $this->callCopyscapePlagiarism($content);
        }
 
        // ==============================================================
        // BƯỚC 3: GPT Grammar + Facts + SEO + Readability (song song)
        // ==============================================================
        $handles = [];
 
        if ($checkFacts) {
            $handles['facts'] = $buildHandle(
                'Bạn là công cụ kiểm chứng sự thật chuyên về nội dung tiếng Việt. Chỉ trả về JSON, không markdown.',
                $buildUserMsg(
                    'Xác định và kiểm chứng các tuyên bố thực tế trong văn bản.
 
Trả về JSON CHÍNH XÁC:
{"facts":[{"fact":"Câu nguyên văn chứa tuyên bố cần kiểm chứng","truthfulness":"85%","explanation":"Giải thích tại sao đúng/sai/không xác minh được","links":["https://vi.wikipedia.org/wiki/..."]}]}
 
Quy tắc:
- key phải là "fact" (không phải "claim")
- "truthfulness" là string phần trăm: "100%"=đúng, "0%"=sai, "50%"=không xác minh
- "links" là mảng URL string, để [] nếu không có nguồn
- Chỉ kiểm chứng tuyên bố về sự kiện, số liệu, lịch sử, khoa học
- Nếu không có tuyên bố nào: {"facts": []}'
                )
            );
        }
 
        if ($checkGrammar) {
            $systemGrammar = <<<'PROMPT'
Bạn là công cụ kiểm tra ngữ pháp và chính tả đa ngôn ngữ, cực kỳ nghiêm ngặt.
Chỉ trả về JSON hợp lệ, TUYỆT ĐỐI không markdown.
 
BƯỚC 1: Tự nhận diện ngôn ngữ chính của văn bản (vi, en, ...).
BƯỚC 2: Tìm lỗi ngữ pháp, chính tả, dấu câu.
 
QUAN TRỌNG - RÀNG BUỘC HIỂN THỊ UI (PHẢI TUÂN THỦ 100%):
1. "sentence": TUYỆT ĐỐI CHỈ TRÍCH XUẤT ĐÚNG 1 CÂU chứa lỗi (từ chữ cái viết hoa đầu câu đến dấu chấm kết thúc câu đó). KHÔNG ĐƯỢC trích xuất nhiều câu hoặc cả đoạn văn. Phải chép nguyên văn.
2. "error_text": đúng cụm từ/từ bị lỗi CHÉP NGUYÊN VĂN từ văn bản gốc. KHÔNG bao gồm các từ đúng xung quanh nếu không cần thiết.
3. "replacements" -> "value": Chuỗi đề xuất sửa đổi TUYỆT ĐỐI KHÔNG ĐƯỢC GIỐNG HỆT "error_text". Nếu giống, hệ thống sẽ bị lỗi.
 
Cấu trúc JSON bắt buộc:
{"matches":[{"message":"Mô tả lỗi cụ thể","shortMessage":"Loại lỗi","error_text":"từ/cụm sai","replacements":[{"value":"từ đã sửa đúng"}],"sentence":"Đúng 1 câu chứa lỗi","type":{"typeName":"Other"},"rule":{"id":"AI_GRAMMAR","description":"AI Grammar","issueType":"grammar","category":{"id":"GRAMMAR","name":"Ngữ pháp"}}}],"warnings":{"incompleteResults":false},"language":"en","score":85.0,"grade":"B"}
 
Quy tắc điểm: 90-100=A, 80-89=B, 70-79=C, 60-69=D, <60=F.
Nếu không có lỗi: trả về matches rỗng.
PROMPT;
            $grammarUserMsg = ($isPdfFileMode && $openaiFileId)
                ? [
                    [
                        'type' => 'text',
                        'text' => "Đây là văn bản đã được trích xuất sẵn từ file PDF đính kèm (dùng làm CHUẨN THAM CHIẾU):\n\n"
                            . "----- TEXT GỐC -----\n"
                            . $textSlice
                            . "\n----- HẾT TEXT -----\n\n"
                            . "Bạn có thể xem file PDF đính kèm để hiểu bố cục, bảng biểu, ngữ cảnh nhằm phát hiện lỗi chính xác hơn. "
                            . "NHƯNG khi trả kết quả, trường \"error_text\" và \"sentence\" BẮT BUỘC phải COPY NGUYÊN VĂN, "
                            . "chính xác từng ký tự từ đúng TEXT GỐC ở trên — TUYỆT ĐỐI KHÔNG được tự viết lại. "
                            . "Nếu không tìm thấy đoạn lỗi khớp y hệt trong TEXT GỐC, hãy bỏ qua lỗi đó.",
                    ],
                    ['type' => 'file', 'file' => ['file_id' => $openaiFileId]],
                  ]
                : "Kiểm tra ngữ pháp và chính tả cho văn bản sau:\n\n" . $textSlice;
 
            $handles['grammar'] = $buildHandle($systemGrammar, $grammarUserMsg, 'gpt-5.4-mini');
        }
 
        if ($checkSeo) {
            $optimizerQuery = trim($input['optimizerQuery'] ?? 'SEO');
            $handles['seo'] = $buildHandle(
                'Bạn là chuyên gia tối ưu SEO cho nội dung tiếng Việt. Chỉ trả về JSON, không markdown.',
                $buildUserMsg(
                    'Phân tích tối ưu SEO cho từ khóa: "' . $optimizerQuery . '". Trả về JSON:
{"keyword_seeds":[{"keyword":"<từ khóa>","min":1,"max":5,"current":0}],"word_count_range":{"min":800,"max":2000,"current":0},"heading_count_range":{"min":5,"max":15,"current":0},"paragraph_count_range":{"min":10,"max":25,"current":0},"suggestions":["<gợi ý SEO>"],"geo_suggestions":["<gợi ý địa lý>"],"content_score":0.0,"competitors":[{"url":"https://example.vn","title":"<tiêu đề>","snippet":"<mô tả>","search_engine_rank":1,"content_score":75.0}]}'
                )
            );
        }
 
        // ── Chạy GPT handles song song ────────────────────────────────
        if (!empty($handles)) {
            $mh      = curl_multi_init();
            $rawData = [];
            foreach ($handles as $key => $ch) {
                curl_multi_add_handle($mh, $ch);
                $rawData[$key] = ['handle' => $ch];
            }
 
            $running = null;
            do {
                $status = curl_multi_exec($mh, $running);
                if ($running) curl_multi_select($mh, 1.0);
            } while ($running > 0 && $status === CURLM_OK);
 
            $responses = [];
            foreach ($rawData as $key => $item) {
                $ch              = $item['handle'];
                $responses[$key] = curl_multi_getcontent($ch);
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
            }
            curl_multi_close($mh);
 
            // Debug log
            foreach ($responses as $key => $response) {
                file_put_contents(
                    __DIR__ . '/gpt-debug.log',
                    "\n=== {$key} ===\n" . $response . "\n",
                    FILE_APPEND
                );
            }
 
            // Parse Facts
            if ($checkFacts) {
                $r = $parseResponse($responses['facts'] ?? '');
                $results['facts'] = $r['facts'] ?? [];
            }
 
            // Parse Grammar
            if ($checkGrammar) {
                $r = $parseResponse($responses['grammar'] ?? '');
                $results['grammarSpelling'] = ($r && isset($r['matches']))
                    ? $r
                    : ['matches' => [], 'warnings' => ['incompleteResults' => true], 'score' => 0, 'grade' => 'F'];
            }
 
            // Parse SEO
            if ($checkSeo) {
                $results['contentOptimizer'] = $parseResponse($responses['seo'] ?? '') ?? [];
            }
        }
 
        // ==============================================================
        // BƯỚC 4: Readability — tính PHP thuần, không cần API
        // ==============================================================
        $results['readability'] = $checkReadability
            ? $this->calculateReadability($content)
            : $this->emptyReadabilityResult();
 
        // ── Build properties ──────────────────────────────────────────
        $results['properties'] = [
            'privateID'        => rand(10000000, 99999999),
            'id'               => 'gpt-scan',
            'title'            => $title,
            'excludedUrls'     => [],
            'publicLink'       => '',
            'content'          => $content ?? '',
            'formattedContent' => $content ?? '',
            'wordCount'        => $wordCount,
        ];
        $results['credits']    = ['used' => (int) ceil(max($wordCount, 1) / 100)];
        $results['tokensUsed'] = $totalTokensUsed;
 
        error_log(sprintf(
            '[callAPI] mode=%s wordCount=%d tokens=%d file_id=%s checks=ai:%d,plag:%d,facts:%d,read:%d,gram:%d,seo:%d',
            $isPdfFileMode ? 'pdf' : 'text',
            $wordCount, $totalTokensUsed, $openaiFileId ?? 'none',
            (int)$checkAi, (int)$checkPlagiarism, (int)$checkFacts,
            (int)$checkReadability, (int)$checkGrammar, (int)$checkSeo
        ));
 
        if ($openaiFileId !== null) $this->deleteOpenAIFile($openaiFileId);
 
        return ['success' => true, 'data' => ['results' => $results]];
    }


    // ============================================================
    // UNWRAP — chuẩn hóa response về đúng format Originality.ai
    // ============================================================

    private function unwrapV3(array $data): array
    {
        $r    = $data['results'] ?? $data;
        $data = null;

        // Normalize facts
        $rawFacts        = is_array($r['facts'] ?? null) ? $r['facts'] : [];
        $normalizedFacts = array_values(array_filter(array_map(function ($fact) {
            if (!is_array($fact)) return null;
            // Filter links: chỉ giữ URL thật, bỏ "[1]", "[2]" placeholder
            $links = [];
            $rawLinks = is_array($fact['links'] ?? null) ? $fact['links']
                : (is_array($fact['sources'] ?? null)
                    ? array_map(fn($s) => is_string($s) ? $s : ($s['url'] ?? $s['link'] ?? ''), $fact['sources'])
                    : []);
            foreach ($rawLinks as $l) {
                if (is_string($l) && filter_var($l, FILTER_VALIDATE_URL)) $links[] = $l;
            }
            return [
                'fact'         => $fact['fact']         ?? ($fact['claim'] ?? ''),
                'truthfulness' => $fact['truthfulness'] ?? '0%',
                'explanation'  => $fact['explanation']  ?? '',
                'links'        => $links,
            ];
        }, $rawFacts)));

        // Normalize plagiarism sources
        $plagiarism = $r['plagiarism'] ?? [];
        if (!empty($plagiarism['results']) && is_array($plagiarism['results'])) {
            $plagiarism['results'] = array_map(function ($pr) {
                $srcs = $pr['results'] ?? $pr['sources'] ?? [];
                return [
                    'phrase'  => $pr['phrase'] ?? '',
                    'results' => array_map(fn($s) => [
                        'link'       => $s['link']  ?? $s['url']  ?? '',
                        'title'      => $s['title'] ?? $s['name'] ?? $s['link'] ?? $s['url'] ?? 'Nguồn không rõ',
                        'scores'     => is_array($s['scores'] ?? null) && count($s['scores'])
                            ? $s['scores']
                            : [['score' => 0.5, 'sentence' => '']],
                        'timestamps' => $s['timestamps'] ?? [],
                    ], $srcs),
                ];
            }, $plagiarism['results']);
        }

        $result = [
            'ai'               => $r['ai']               ?? [],
            'plagiarism'       => $plagiarism,
            'grammarSpelling'  => $r['grammarSpelling']  ?? [],
            'readability'      => $r['readability']      ?? [],
            'facts'            => $normalizedFacts,
            'contentOptimizer' => $r['contentOptimizer'] ?? [],
            'properties'       => $r['properties']       ?? [],
            'credits'          => $r['credits']          ?? [],
            'tokensUsed'       => $r['tokensUsed']        ?? 0,
        ];
        unset($r);
        return $result;
    }

    // ============================================================
    // DB HELPERS
    // ============================================================

    private function ensureTable(): void
    {
        app()->db->query("CREATE TABLE IF NOT EXISTS `originality_history` (
            `id`                INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
            `account_uuid`      VARCHAR(100)     NOT NULL,
            `type`              VARCHAR(50)      NOT NULL DEFAULT 'text',
            `title`             VARCHAR(255)     DEFAULT NULL,
            `content`           LONGTEXT         DEFAULT NULL,
            `scan_url`          VARCHAR(2048)    DEFAULT NULL,
            `file_url`          VARCHAR(2048)    DEFAULT NULL,
            `file_blob`         LONGBLOB         DEFAULT NULL,
            `ai`                LONGTEXT         DEFAULT NULL,
            `plagiarism`        LONGTEXT         DEFAULT NULL,
            `grammar`           LONGTEXT         DEFAULT NULL,
            `readability`       LONGTEXT         DEFAULT NULL,
            `facts`             LONGTEXT         DEFAULT NULL,
            `content_optimizer` LONGTEXT         DEFAULT NULL,
            `metadata`          LONGTEXT         DEFAULT NULL,
            `word_count`        INT              DEFAULT NULL,
            `points_used`       INT              DEFAULT NULL,
            `ai_score`          FLOAT            DEFAULT NULL,
            `ai_model`          VARCHAR(50)      DEFAULT NULL,
            `plag_score`        FLOAT            DEFAULT NULL,
            `grammar_errors`    INT              DEFAULT NULL,
            `readability_score` FLOAT            DEFAULT NULL,
            `readability_grade` FLOAT            DEFAULT NULL,
            `facts_errors`      INT              DEFAULT NULL,
            `facts_total`       INT              DEFAULT NULL,
            `facts_true`        INT              DEFAULT NULL,
            `seo_score`         FLOAT            DEFAULT NULL,
            `tokens_used`       INT              NOT NULL DEFAULT 0,
            `status`            VARCHAR(20)      NOT NULL DEFAULT 'done',
            `created_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_account` (`account_uuid`),
            INDEX `idx_created` (`created_at`),
            INDEX `idx_type`    (`type`),
            INDEX `idx_ai_score`   (`ai_score`),
            INDEX `idx_plag_score` (`plag_score`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        // Migration: thêm cột còn thiếu nếu có
        static $migrated = false;
        if ($migrated) return;
        $migrated = true;

        $existing = array_column(
            app()->db->query("SHOW COLUMNS FROM `originality_history`")->fetchAll(\PDO::FETCH_ASSOC),
            'Field'
        );

        $migrations = [
            'content'           => "ALTER TABLE `originality_history` ADD COLUMN `content` LONGTEXT DEFAULT NULL AFTER `title`",
            'scan_url'          => "ALTER TABLE `originality_history` ADD COLUMN `scan_url` VARCHAR(2048) DEFAULT NULL AFTER `content`",
            'file_url'          => "ALTER TABLE `originality_history` ADD COLUMN `file_url` VARCHAR(2048) DEFAULT NULL AFTER `scan_url`",
            'file_blob'         => "ALTER TABLE `originality_history` ADD COLUMN `file_blob` LONGBLOB DEFAULT NULL AFTER `file_url`",
            'grammar'           => "ALTER TABLE `originality_history` ADD COLUMN `grammar` LONGTEXT DEFAULT NULL AFTER `plagiarism`",
            'readability'       => "ALTER TABLE `originality_history` ADD COLUMN `readability` LONGTEXT DEFAULT NULL AFTER `grammar`",
            'facts'             => "ALTER TABLE `originality_history` ADD COLUMN `facts` LONGTEXT DEFAULT NULL AFTER `readability`",
            'content_optimizer' => "ALTER TABLE `originality_history` ADD COLUMN `content_optimizer` LONGTEXT DEFAULT NULL AFTER `facts`",
            'metadata'          => "ALTER TABLE `originality_history` ADD COLUMN `metadata` LONGTEXT DEFAULT NULL AFTER `content_optimizer`",
            'word_count'        => "ALTER TABLE `originality_history` ADD COLUMN `word_count` INT DEFAULT NULL AFTER `metadata`",
            'points_used'       => "ALTER TABLE `originality_history` ADD COLUMN `points_used` INT DEFAULT NULL AFTER `word_count`",
            'ai_score'          => "ALTER TABLE `originality_history` ADD COLUMN `ai_score` FLOAT DEFAULT NULL",
            'ai_model'          => "ALTER TABLE `originality_history` ADD COLUMN `ai_model` VARCHAR(50) DEFAULT NULL",
            'plag_score'        => "ALTER TABLE `originality_history` ADD COLUMN `plag_score` FLOAT DEFAULT NULL",
            'grammar_errors'    => "ALTER TABLE `originality_history` ADD COLUMN `grammar_errors` INT DEFAULT NULL",
            'readability_score' => "ALTER TABLE `originality_history` ADD COLUMN `readability_score` FLOAT DEFAULT NULL",
            'readability_grade' => "ALTER TABLE `originality_history` ADD COLUMN `readability_grade` FLOAT DEFAULT NULL",
            'facts_errors'      => "ALTER TABLE `originality_history` ADD COLUMN `facts_errors` INT DEFAULT NULL",
            'facts_total'       => "ALTER TABLE `originality_history` ADD COLUMN `facts_total` INT DEFAULT NULL",
            'facts_true'        => "ALTER TABLE `originality_history` ADD COLUMN `facts_true` INT DEFAULT NULL",
            'seo_score'         => "ALTER TABLE `originality_history` ADD COLUMN `seo_score` FLOAT DEFAULT NULL",
            'tokens_used'       => "ALTER TABLE `originality_history` ADD COLUMN `tokens_used` INT NOT NULL DEFAULT 0",
        ];

        foreach ($migrations as $col => $sql) {
            if (!in_array($col, $existing)) {
                try { app()->db->query($sql); } catch (\Throwable $e) { /* bỏ qua nếu cột đã tồn tại */ }
            }
        }
    }

    private function saveHistory(
        string $title, ?string $content, ?string $scanUrl, ?string $fileUrl, string $type,
        array $results, int $checksCount, int $capitalCost,
        bool $checkAi = true, bool $checkPlagiarism = true,
        bool $checkGrammar = true, bool $checkReadability = true
    ): void {
        $uuid = $this->getUuid();
        if (!$uuid) return;
    
        // Tính facts errors/true
        $facts       = is_array($results['facts'] ?? null) ? $results['facts'] : [];
        $factsErrors = 0;
        $factsTrue   = 0;
        foreach ($facts as $f) {
            $pct = (int) filter_var(trim($f['truthfulness'] ?? '0%'), FILTER_SANITIZE_NUMBER_INT);
            if ($pct >= 70) $factsTrue++; else $factsErrors++;
        }
    
        // AI score từ confidence — chỉ tính khi có bật check AI
        $aiScore = null;
        if ($checkAi && isset($results['ai']['confidence']['AI'])) {
            $aiScore = round((float) $results['ai']['confidence']['AI'] * 100, 2);
        }
        
        $lang = $this->detectLang($content);
    
        $insertData = [
            'account_uuid'        => $uuid,
            'type'                => $type,
            'title'               => $title,
            'lang'                => $lang,
            'content'             => $content,
            'scan_url'            => $scanUrl,
            'file_url'            => $fileUrl,
            // 'file_blob'        => BỎ HOÀN TOÀN TRƯỜNG NÀY ĐỂ TRÁNH TRÀN RAM DBN
            'ai'                  => json_encode($results['ai']               ?? null, JSON_UNESCAPED_UNICODE),
            'plagiarism'          => json_encode($results['plagiarism']       ?? null, JSON_UNESCAPED_UNICODE),
            'grammar'             => json_encode($results['grammarSpelling']  ?? null, JSON_UNESCAPED_UNICODE),
            'readability'         => json_encode($results['readability']      ?? null, JSON_UNESCAPED_UNICODE),
            'facts'               => json_encode($facts,                             JSON_UNESCAPED_UNICODE),
            'content_optimizer'   => json_encode($results['contentOptimizer'] ?? null, JSON_UNESCAPED_UNICODE),
            'metadata'            => json_encode([
                'properties' => $results['properties'] ?? null,
                'credits'    => $results['credits']    ?? null,
            ], JSON_UNESCAPED_UNICODE),
            'word_count'          => $results['properties']['wordCount'] ?? 0,
            'points_used'         => $results['pointsUsed']              ?? 0,
            'tokens_used'         => $results['tokensUsed']              ?? 0,
    
            'ai_score'            => $aiScore,
            'ai_model'            => $checkAi ? ($results['ai']['aiModel'] ?? null) : null,
            'plag_score'          => $checkPlagiarism ? ($results['plagiarism']['score'] ?? 0) : null,
            'grammar_errors'      => $checkGrammar ? count($results['grammarSpelling']['matches'] ?? []) : null,
            'readability_score'   => $checkReadability ? ($results['readability']['readability']['fleschReadingEase'] ?? null) : null,
            'readability_grade'   => $checkReadability ? ($results['readability']['readability']['fleschGradeLevel']  ?? null) : null,
    
            'facts_errors'        => $factsErrors,
            'facts_total'         => count($facts),
            'facts_true'          => $factsTrue,
            'seo_score'           => $results['contentOptimizer']['content_score'] ?? ($results['contentOptimizer']['score'] ?? 0),
            'status'              => 'done',
            'checks_count'        => $checksCount,
            'capitalCost'         => $capitalCost,
        ];
    
        // Thêm trực tiếp không cần bắt catch max_allowed_packet nữa
        app()->db->insert('originality_history', $insertData);
    }

    // ============================================================
    // PRIVATE HELPERS
    // ============================================================

    private function isOwnUploadedPdf(string $url, string $uuid): bool
    {
        $safeUuid = preg_replace('/[^a-zA-Z0-9\-]/', '', $uuid);
        $filename = basename(parse_url($url, PHP_URL_PATH));
        return strpos($filename, $safeUuid . '_') === 0;
    }

    private function getInput(): array
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        return array_merge($_POST, $input);
    }

    private function verifyKey(): void {}

    private function response(array $data, int $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    public function estimateCost()
    {
        $this->verifyKey();
    
        $uuid = $this->getUuid();
        if (!$uuid) {
            return $this->response(['status' => 'error', 'message' => 'Vui lòng đăng nhập.'], 401);
        }
    
        $input   = $this->getInput();
        $content = trim($input['content'] ?? '');
        $fileUrl = trim($input['file_url'] ?? '');
    
        $balance = $this->getPoints($uuid);
    
        $hasPdf = !empty($fileUrl)
            && filter_var($fileUrl, FILTER_VALIDATE_URL)
            && $this->isOwnUploadedPdf($fileUrl, $uuid);
    
        $activeCheckCount = $this->countActiveChecks($input);
    
        if ($activeCheckCount <= 0) {
            return $this->response(['status' => 'error', 'message' => 'Vui lòng chọn ít nhất một chức năng kiểm tra.'], 422);
        }
    
        if ($hasPdf) {
            // $diskPath = $this->resolveUploadedFilePath($fileUrl);
            // $extractedText = '';
            
            // if ($diskPath && is_file($diskPath)) {
            //     // Ép bóc text từ file PDF để đếm bằng PHP
            //     $extractedText = trim(shell_exec('pdftotext ' . escapeshellarg($diskPath) . ' - 2>/dev/null') ?? '');
            // }

            // if (!empty($extractedText)) {
            //     $wordCount = $this->countWords($extractedText);
            //     $cost      = $this->calcCost($wordCount) * $activeCheckCount;
            //     $note      = 'Đếm chính xác từ nội dung PDF';
                
            // } else {
            //     // Fallback nếu file PDF dạng ảnh không bóc được text
            //     $wordCount = 0;
            //     $cost      = $this->estimateCostFromUrl($fileUrl);
            //     $note      = 'Ước tính từ dung lượng PDF (Không bóc được text)';
            // }
            
            $diskPath = $this->resolveUploadedFilePath($fileUrl);
            $extractedText = '';
            
            if ($diskPath && is_file($diskPath)) {
                $extractedText = trim(shell_exec('pdftotext ' . escapeshellarg($diskPath) . ' - 2>/dev/null') ?? '');
            }

            // CHẶN ĐỨNG FILE ẢNH TẠI ĐÂY
            if (empty($extractedText)) {
                return $this->response(['status' => 'error', 'message' => 'Tài liệu rỗng hoặc là file ảnh (Scan). Hệ thống không đọc được chữ. Vui lòng chuyển đổi sang dạng văn bản trước.'], 422);
            }

            $wordCount = $this->countWords($extractedText);
            $cost      = $this->calcCost($wordCount) * $activeCheckCount;
            $note      = 'Đếm chính xác từ nội dung PDF';
        
        } elseif (!empty($content)) {
            $wordCount = $this->countWords($content);
            $cost      = $this->calcCost($wordCount) * $activeCheckCount;
            $note      = 'Ước tính theo số từ';
        } else {
            return $this->response(['status' => 'error', 'message' => 'Không có nội dung để kiểm tra.'], 422);
        }
        
        return $this->response([
            'status'      => 'success',
            'cost'        => $cost,
            'balance'     => $balance,
            'enough'      => $balance >= $cost,
            'wordCount'   => $wordCount,
            'checksCount' => $activeCheckCount,
            'note'        => $note,
        ]);
    }
    
    // ── Helper: ước tính token cho content text ──────────────────
    private function estimateTokensFromContent(string $content, int $checkCount): int
    {
        $wordCount = max(1, $this->countWords($content));
    
        return (int) ceil(
            $wordCount
            * $checkCount
            * $this->getHistoricalTokenRatio()
        );
    }
    
    // ── Helper: ước tính token cho PDF (dựa trên size ước lượng) ──
    private function estimateTokensFromUrl(string $url, int $checkCount): int
    {
        $diskPath = $this->resolveUploadedFilePath($url);
    
        if (!$diskPath || !is_file($diskPath)) {
            return (int) ceil(
                1000 * $checkCount * $this->getHistoricalTokenRatio()
            );
        }
    
        $fileSizeMb = max(
            0.1,
            filesize($diskPath) / 1024 / 1024
        );
    
        // Ước tính:
        // PDF text thường ~ 6.000 - 10.000 từ / MB
        // Chọn mức an toàn ở giữa
        $estimatedWords = (int) ceil($fileSizeMb * 8000);
    
        return (int) ceil(
            $estimatedWords
            * $checkCount
            * $this->getHistoricalTokenRatio()
        );
    }
    
    private function countActiveChecks(array $input): int
    {
        $checks = [
            'check_ai'               => true,
            'check_plagiarism'       => true,
            'check_facts'            => false,
            'check_readability'      => true,
            'check_grammar'          => true,
            'check_contentOptimizer' => false,
        ];
        $count = 0;
        foreach ($checks as $key => $default) {
            if ((bool) ($input[$key] ?? $default)) $count++;
        }
        return max($count, 1);
    }
    private function getHistoricalTokenRatio(): float
    {
        $row = app()->db->query("
            SELECT
                SUM(tokens_used) /
                NULLIF(SUM(word_count * checks_count), 0) AS ratio
            FROM originality_history
            WHERE
                tokens_used > 0
                AND word_count > 0
                AND checks_count > 0
        ")->fetch();
    
        $ratio = (float)($row['ratio'] ?? 0);
    
        return $ratio > 0 ? $ratio : 3.5;
    }
    
    private function callOriginalityAPI(string $content, bool $checkPlag, bool $checkFacts): array
    {
        $apiKey = $_ENV['ORIGINALITY_KEY'] ?? getenv('ORIGINALITY_KEY') ?? '';
        if (empty($apiKey)) return ['success' => false, 'message' => 'ORIGINALITY_KEY chưa cấu hình'];
    
        $payload = json_encode([
            'content'        => mb_substr($content, 0, 50000, 'UTF-8'), // giới hạn an toàn
            'title'          => 'Scan-' . time(),
            'aiModelVersion' => '1',
            'storeScan'      => 'true',
            'plag'           => $checkPlag  ? '1' : '0',
            'fact'           => $checkFacts ? '1' : '0',
        ], JSON_UNESCAPED_UNICODE);
    
        $ch = curl_init('https://api.originality.ai/api/v1/scan/ai');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'X-OAI-API-KEY: ' . $apiKey,
            ],
        ]);
        $raw    = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err    = curl_error($ch);
        curl_close($ch);
    
        if ($err) return ['success' => false, 'message' => 'cURL error: ' . $err];
        if ($status !== 200) return ['success' => false, 'message' => "Originality.ai HTTP {$status}"];
    
        $data = json_decode($raw, true);
        if (empty($data) || json_last_error() !== JSON_ERROR_NONE)
            return ['success' => false, 'message' => 'Originality.ai response không hợp lệ'];
    
        return ['success' => true, 'data' => $data];
    }

    // ============================================================
    // COPYSCAPE — plagiarism check (đồng bộ, Full Comparison)
    // ============================================================

    /**
     * Gọi Copyscape Premium Text Plagiarism Search API (đồng bộ).
     * Dùng Full Comparison (c=10) để có % khớp chính xác (allpercentmatched),
     * tương đương vai trò của "score" trước đây lấy từ Copyleaks.
     *
     * Trả về cùng format ['score' => float, 'results' => [...]] mà
     * scan()/saveHistory()/unwrapV3() đang mong đợi, để không phải đổi
     * chỗ nào khác trong luồng xử lý hoặc cấu trúc DB.
     */
    private function callCopyscapePlagiarism(string $content): array
    {
        $logFile = __DIR__ . '/copyscape-debug.log';
        $log = function (string $msg) use ($logFile) {
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
        };
    
        $content = trim($content);
        if (empty($content)) return ['score' => 0, 'results' => []];
    
        $username = $_ENV['COPYSCAPE_USER'] ?? getenv('COPYSCAPE_USER') ?? '';
        $apiKey   = $_ENV['COPYSCAPE_KEY']  ?? getenv('COPYSCAPE_KEY')  ?? '';
        if (empty($username) || empty($apiKey)) {
            $log('Thiếu COPYSCAPE_USER hoặc COPYSCAPE_KEY');
            return ['score' => 0, 'results' => []];
        }
    
        // ── Chia theo từ, mỗi chunk ≤ COPYSCAPE_MAX_WORDS_PER_CALL từ ──
        $chunks = $this->splitIntoChunksByWords($content, self::COPYSCAPE_MAX_WORDS_PER_CALL);
        $log('Plagiarism chunks: ' . count($chunks) . ' (tổng ' . $this->countWords($content) . ' từ)');
    
        $allResults  = [];
        $totalWords  = 0;
        $weightedSum = 0.0; // để tính điểm trung bình có trọng số
        $totalCostUsd = 0.0;
    
        foreach ($chunks as $chunkIndex => $chunk) {
            $chunkWords = $this->countWords($chunk);
    
            $payload = [
                'u' => $username, 'k' => $apiKey,
                'o' => 'csearch', 'e' => 'UTF-8',
                't' => $chunk,
                'c' => 10,        // Full Comparison, tối đa 10 nguồn/chunk
                'f' => 'json',
            ];
    
            $ch = curl_init('https://www.copyscape.com/api/');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_TIMEOUT        => 60,
            ]);
            $raw  = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err  = curl_error($ch);
            curl_close($ch);
    
            if ($err) {
                $log("Chunk {$chunkIndex} cURL error: {$err}");
                continue;
            }
    
            $data = json_decode($raw, true);
            if (isset($data['cost'])) {
                $totalCostUsd += (float) $data['cost'];
            }
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                $log("Chunk {$chunkIndex} response không hợp lệ HTTP={$code}");
                continue;
            }
    
            if (!empty($data['error'])) {
                $log("Chunk {$chunkIndex} API lỗi: " . $data['error']);
                continue;
            }
    
            // Điểm chunk này
            $chunkScore = isset($data['allpercentmatched'])
                ? (float) $data['allpercentmatched']
                : (function() use ($data) {
                    $best = 0.0;
                    foreach ($data['result'] ?? [] as $item) {
                        if (isset($item['percentmatched'])) $best = max($best, (float)$item['percentmatched']);
                    }
                    return $best;
                })();
    
            $weightedSum += $chunkScore * $chunkWords;
            $totalWords  += $chunkWords;
    
            $log(sprintf('Chunk %d: %d từ, score=%.2f%%, querywords=%d, cost=$%s, sources=%d',
                $chunkIndex + 1, $chunkWords, $chunkScore,
                (int)($data['querywords'] ?? 0),
                (string)($data['cost'] ?? '0'),
                count($data['result'] ?? [])
            ));
    
            // Gộp kết quả nguồn — loại trùng URL
            foreach ($data['result'] ?? [] as $item) {
                $url = $item['url'] ?? '';
                // Nếu URL đã có từ chunk trước, chỉ cập nhật score nếu cao hơn
                $existing = array_search($url, array_column(array_column($allResults, 'results'), 'link'));
                
                $allResults[] = [
                    'phrase'  => mb_substr($item['textmatched'] ?? $item['textsnippet'] ?? '', 0, 200),
                    'results' => [[
                        'link'   => $url,
                        'title'  => $item['title'] ?? 'Nguồn không rõ',
                        'scores' => [[
                            'score'    => isset($item['percentmatched'])
                                ? round((float)$item['percentmatched'] / 100, 4) : 0.0,
                            'sentence' => mb_substr($item['textmatched'] ?? $item['textsnippet'] ?? '', 0, 300),
                        ]],
                        'timestamps' => [],
                    ]],
                ];
            }
        }
    
        // Điểm tổng = trung bình có trọng số theo số từ mỗi chunk
        $finalScore = $totalWords > 0 ? round($weightedSum / $totalWords, 2) : 0.0;
    
        // Loại trùng URL — giữ lại kết quả có score cao nhất nếu URL xuất hiện nhiều chunk
        $deduped = [];
        $seenUrls = [];
        foreach ($allResults as $r) {
            $url = $r['results'][0]['link'] ?? '';
            if ($url === '') { $deduped[] = $r; continue; }
            if (!isset($seenUrls[$url])) {
                $seenUrls[$url] = count($deduped);
                $deduped[] = $r;
            } else {
                // Cập nhật score nếu chunk mới có score cao hơn
                $existingIdx = $seenUrls[$url];
                $existingScore = $deduped[$existingIdx]['results'][0]['scores'][0]['score'] ?? 0;
                $newScore = $r['results'][0]['scores'][0]['score'] ?? 0;
                if ($newScore > $existingScore) {
                    $deduped[$existingIdx] = $r;
                }
            }
        }
    
        // Sắp xếp theo score giảm dần
        usort($deduped, fn($a, $b) =>
            ($b['results'][0]['scores'][0]['score'] ?? 0) <=> ($a['results'][0]['scores'][0]['score'] ?? 0)
        );
    
        return ['score' => $finalScore, 'results' => $deduped, 'cost_usd' => $totalCostUsd,];
    }

    // Thêm method này vào class
    private function getCopyleaksToken(): ?string
    {
        // Cache token trong session để tránh login lại mỗi request
        $cached = app()->session->get('copyleaks_token');
        if ($cached && $cached['expires'] > time() + 300) {
            return $cached['token'];
        }
    
        $email  = $_ENV['COPYLEAKS_EMAIL']  ?? getenv('COPYLEAKS_EMAIL')  ?? '';
        $apiKey = $_ENV['COPYLEAKS_KEY']    ?? getenv('COPYLEAKS_KEY')    ?? '';
        if (empty($email) || empty($apiKey)) return null;
    
        $ch = curl_init('https://id.copyleaks.com/v3/account/login/api');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(['email' => $email, 'key' => $apiKey]),
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        ]);
        $raw  = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        if ($code !== 200) return null;
        $data = json_decode($raw, true);
        if (empty($data['access_token'])) return null;
    
        // Token valid 48h, cache lại
        app()->session->set('copyleaks_token', [
            'token'   => $data['access_token'],
            'expires' => time() + 48 * 3600,
        ]);
        return $data['access_token'];
    }
    
    private function callCopyleaksPlagiarism(string $content): array
    {
        $logFile = __DIR__ . '/copyleaks-debug.log';
        $log = function(string $msg) use ($logFile) {
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
        };
        $token = $this->getCopyleaksToken();
        if (!$token) {
            $log('Không lấy được token');
            return ['score' => 0, 'results' => []];
        }
    
        $scanId  = 'sc' . substr(md5(uniqid()), 0, 10); // max 36 chars, chỉ [a-z0-9]
        $base64  = base64_encode($content);
        $sandbox = filter_var($_ENV['COPYLEAKS_SANDBOX'] ?? getenv('COPYLEAKS_SANDBOX') ?? 'true', FILTER_VALIDATE_BOOLEAN);
    
        // Webhook giả — Copyleaks bắt buộc có nhưng ta dùng polling nên không cần xử lý
        $webhookUrl = rtrim(($_ENV['APP_URL'] ?? getenv('APP_URL') ?? 'https://ai.vmied.com'), '/')
                    . '/api/copyleaks/webhook/{STATUS}';
    
        $payload = json_encode([
            'base64'     => $base64,
            'filename'   => 'document.txt',
            'properties' => [
                'sandbox'  => $sandbox,
                'scanning' => ['internet' => true],
                'webhooks' => ['status' => $webhookUrl],
            ],
        ], JSON_UNESCAPED_UNICODE);
    
        // BƯỚC 1: Submit scan
        $ch = curl_init('https://api.copyleaks.com/v3/scans/submit/file/' . $scanId);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
        ]);
        $raw  = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);
    
        if ($err || $code !== 201) {
            $log('Submit failed HTTP=' . $code . ' err=' . $err . ' raw=' . mb_substr($raw ?? '', 0, 500));
            return ['score' => 0, 'results' => []];
        }
    
        $log('Submitted scanId=' . $scanId . ' sandbox=' . ($sandbox ? 'true' : 'false'));
    
        // BƯỚC 2: Poll kết quả (tối đa 60s)
        $maxWait  = 60;
        $interval = 3;
        $waited   = 0;
        $result   = null;
    
        while ($waited < $maxWait) {
            sleep($interval);
            $waited += $interval;
    
            $ch = curl_init('https://api.copyleaks.com/v3/downloads/' . $scanId . '/results');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token],
            ]);
            $raw  = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
    
            if ($code === 200) {
                $result = json_decode($raw, true);
                break;
            }
    
            // 404 = chưa xong, tiếp tục chờ
            $log('Poll scanId=' . $scanId . ' HTTP=' . $code . ' raw=' . mb_substr($raw ?? '', 0, 300) . ' waited=' . $waited . 's');

        }
    
        if (!$result) {
            $log('Timeout sau ' . $maxWait . 's, scanId=' . $scanId);
            return ['score' => 0, 'results' => []];
        }
    
        // Map response → format app
        $score    = (float) ($result['results']['score']['aggregatedScore'] ?? 0);
        $internet = $result['results']['internet'] ?? [];
    
        $results = array_map(fn($item) => [
            'phrase'  => mb_substr($item['introduction'] ?? $item['title'] ?? '', 0, 200),
            'results' => [[
                'link'       => $item['url']   ?? '',
                'title'      => $item['title'] ?? 'Nguồn không rõ',
                'scores'     => [['score' => round(($item['matchedWords'] ?? 0) / max($item['totalWords'] ?? 1, 1), 4), 'sentence' => '']],
                'timestamps' => [],
            ]],
        ], $internet);
    
        $log('Done scanId=' . $scanId . ' score=' . $score . ' sources=' . count($results));
        return ['score' => $score, 'results' => $results];
    }
    
    private function submitCopyleaks(string $content): ?string
    {
        if (empty(trim($content))) return null;
    
        $token = $this->getCopyleaksToken();
        if (!$token) return null;
    
        $scanId     = 'sc' . substr(md5(uniqid()), 0, 10);
        $sandbox    = filter_var($_ENV['COPYLEAKS_SANDBOX'] ?? getenv('COPYLEAKS_SANDBOX') ?? 'true', FILTER_VALIDATE_BOOLEAN);
        $appUrl     = rtrim($_ENV['APP_URL'] ?? getenv('APP_URL') ?? 'https://ai.vmied.com', '/');
        $webhookUrl = $appUrl . '/api/copyleaks/webhook/{STATUS}';
    
        $payload = json_encode([
            'base64'     => base64_encode($content),
            'filename'   => 'document.txt',
            'properties' => [
                'sandbox'  => $sandbox,
                'scanning' => ['internet' => true],
                'webhooks' => ['status' => $webhookUrl],
            ],
        ], JSON_UNESCAPED_UNICODE);
    
        $ch = curl_init('https://api.copyleaks.com/v3/scans/submit/file/' . $scanId);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
        ]);
        $raw  = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        if ($code !== 201) {
            error_log('[Copyleaks] Submit failed HTTP=' . $code . ' raw=' . mb_substr($raw ?? '', 0, 300));
            return null;
        }
    
        error_log('[Copyleaks] Submitted scanId=' . $scanId);
        return $scanId;
    }
    
    public function copyleaksWebhook($statusParam)
    {
        
        $status = is_array($statusParam) ? ($statusParam['status'] ?? $statusParam[0] ?? 'completed') : (string)$statusParam;
        
        file_put_contents(
            __DIR__ . '/copyleaks-webhook.log',
            date('[Y-m-d H:i:s] ') . 'HIT status=' . $status . ' ip=' . ($_SERVER['REMOTE_ADDR'] ?? '?') . "\n",
            FILE_APPEND
        );
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);
    
        error_log('[CopyleaksWebhook] status=' . $status . ' raw=' . mb_substr($raw, 0, 300));
    
        if ($status !== 'completed') {
            http_response_code(200); echo 'ok'; exit;
        }
    
        $copyleaksScanId = $data['scannedDocument']['scanId'] ?? null;
        if (!$copyleaksScanId) {
            http_response_code(200); echo 'ok'; exit;
        }
    
        $score    = (float) ($data['results']['score']['aggregatedScore'] ?? 0);
        $internet = $data['results']['internet'] ?? [];
    
        $plagResults = array_map(fn($item) => [
            'phrase'  => mb_substr($item['introduction'] ?? $item['title'] ?? '', 0, 200),
            'results' => [[
                'link'       => $item['url']   ?? '',
                'title'      => $item['title'] ?? 'Nguồn không rõ',
                'scores'     => [['score' => round(($item['matchedWords'] ?? 0) / max($item['totalWords'] ?? 1, 1), 4), 'sentence' => '']],
                'timestamps' => [],
            ]],
        ], $internet);
    
        $plagiarismJson = json_encode(
            ['score' => $score, 'results' => $plagResults],
            JSON_UNESCAPED_UNICODE
        );
    
        app()->db->update('originality_history', [
            'plagiarism' => $plagiarismJson,
            'plag_score' => $score,
            'status'     => 'done',
        ], ['copyleaks_scan_id' => $copyleaksScanId]);
    
        error_log('[CopyleaksWebhook] Updated copyleaks_scan_id=' . $copyleaksScanId . ' score=' . $score);
    
        http_response_code(200); echo 'ok'; exit;
    }
    
    public function getPlagiarism($id)
    {
        $this->verifyKey();
        $uuid = $this->getUuid();
        if (!$uuid) return $this->response(['error' => 'Vui lòng đăng nhập.'], 401);
        
        $row = app()->db->get('originality_history',
            ['status', 'plagiarism', 'plag_score'],
            ['id' => $id, 'account_uuid' => $uuid]
        );
    
        if (!$row) return $this->response(['error' => 'Không tìm thấy.'], 404);
    
        $isDone = ($row['status'] === 'done') && !empty($row['plagiarism']);
    
        return $this->response([
            'pending'    => !$isDone,
            'plagiarism' => $isDone
                ? json_decode($row['plagiarism'], true)
                : ['score' => null, 'results' => []],
        ]);
    }
    
    // ============================================================
    // COPYSCAPE — AI Detector (thay thế GPT AI-detection)
    // ============================================================
    private function callCopyscapeAI(string $content): array
    {
        $logFile = __DIR__ . '/copyscape-ai-debug.log';
        $log = function (string $msg) use ($logFile) {
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
        };
    
        $content = trim($content);
        if (empty($content)) return $this->emptyAiResult();
    
        $username = $_ENV['COPYSCAPE_USER'] ?? getenv('COPYSCAPE_USER') ?? '';
        $apiKey   = $_ENV['COPYSCAPE_KEY']  ?? getenv('COPYSCAPE_KEY')  ?? '';
        if (empty($username) || empty($apiKey)) {
            $log('Thiếu COPYSCAPE_USER/COPYSCAPE_KEY');
            return $this->emptyAiResult();
        }
    
        if (class_exists('Normalizer')) {
            $n = \Normalizer::normalize($content, \Normalizer::FORM_C);
            if ($n !== false) $content = $n;
        }
    
        // ── Chia chunks ──────────────────────────────────────────────
        $chunks = $this->splitIntoChunksByWords($content, self::COPYSCAPE_AI_MAX_WORDS_PER_CALL);
        $log('AI chunks: ' . count($chunks) . ' (tổng ' . $this->countWords($content) . ' từ)');
    
        $allBlocks   = [];
        $totalWords  = 0;
        $weightedSum = 0.0;
        $totalCostUsd = 0.0;
    
        foreach ($chunks as $chunkIndex => $chunk) {
            $chunkWords = $this->countWords($chunk);
    
            $ch = curl_init('https://www.copyscape.com/api/');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => [
                    'u' => $username, 'k' => $apiKey,
                    'o' => 'aicheck', 'e' => 'UTF-8',
                    't' => $chunk, 'f' => 'json',
                ],
                CURLOPT_TIMEOUT => 60,
            ]);
            $raw  = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err  = curl_error($ch);
            curl_close($ch);
    
            if ($err) { $log("Chunk {$chunkIndex} cURL error: {$err}"); continue; }
    
            $data = json_decode($raw, true);
            if (isset($data['cost'])) {
                $totalCostUsd += (float) $data['cost'];
            }
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                $log("Chunk {$chunkIndex} response không hợp lệ HTTP={$code}");
                continue;
            }
            if (!empty($data['error'])) {
                $log("Chunk {$chunkIndex} API lỗi: " . $data['error']);
                continue;
            }
    
            $chunkAiScore = (float)($data['aiscore'] ?? 0);
            $weightedSum += $chunkAiScore * $chunkWords;
            $totalWords  += $chunkWords;
    
            $log(sprintf('Chunk %d: %d từ, aiScore=%.4f, querywords=%d, cost=$%s',
                $chunkIndex + 1, $chunkWords, $chunkAiScore,
                (int)($data['querywords'] ?? 0), (string)($data['cost'] ?? '0')
            ));
    
            // Thu thập blocks từ chunk này
            $segments = is_array($data['segments'] ?? null) ? $data['segments'] : [];
            foreach ($segments as $seg) {
                $allBlocks[] = [
                    'text'   => $seg['text'] ?? '',
                    'result' => [
                        'fake'   => round((float)($seg['aiscore'] ?? 0), 4),
                        'real'   => round(1 - (float)($seg['aiscore'] ?? 0), 4),
                        'status' => 'success',
                    ],
                ];
            }
        }
    
        if ($totalWords === 0) return $this->emptyAiResult();
    
        $globalScore = round($weightedSum / $totalWords, 4);
    
        // Merge blocks (giữ nguyên pipeline hiện có)
        $allBlocks = $this->mergeBrokenWordBlocks($allBlocks);
        $allBlocks = $this->mergeFillerBlocks($allBlocks);
        $allBlocks = $this->mergeBlocksBySentence($allBlocks);
        $allBlocks = $this->mergeShortBlocks($allBlocks);
    
        return [
            'aiModel'        => 'copyscape-ai',
            'classification' => ['AI' => $globalScore >= 0.5 ? 1 : 0, 'Original' => $globalScore >= 0.5 ? 0 : 1],
            'confidence'     => ['AI' => $globalScore, 'Original' => round(1 - $globalScore, 4)],
            'blocks'         => $allBlocks,
            'cost_usd'       => $totalCostUsd,
        ];
    }
    
    private function emptyAiResult(): array
    {
        return [
            'aiModel' => 'copyscape-ai',
            'classification' => ['AI' => 0, 'Original' => 1],
            'confidence' => ['AI' => 0.0, 'Original' => 1.0],
            'blocks' => [],
        ];
    }
    
    /**
     * Chuẩn hoá text trích từ pdftotext: gộp các dòng bị PDF wrap giữa chừng
     * thành 1 dòng logic, chỉ giữ line-break thật (đoạn văn mới / dòng trống).
     */
    private function normalizePdfText(string $text): string
    {
        if (empty($text)) return $text;
        
        if (class_exists('Normalizer')) {
            $normalized = \Normalizer::normalize($text, \Normalizer::FORM_C);
            if ($normalized !== false) $text = $normalized;
        }
    
        // Chuẩn hoá line ending
        $text = str_replace(["\r\n", "\r"], "\n", $text);
    
        // Tách theo đoạn văn thật (2+ dòng trống liên tiếp = ranh giới đoạn)
        $paragraphs = preg_split('/\n\s*\n+/u', $text);
    
        $result = [];
        foreach ($paragraphs as $para) {
            // Trong 1 đoạn: nối các dòng lại bằng khoảng trắng,
            // vì \n đơn lẻ ở đây chỉ là chỗ PDF wrap chữ, không phải ranh giới câu
            $lines = preg_split('/\n/u', $para);
            $lines = array_map('trim', $lines);
            $lines = array_filter($lines, fn($l) => $l !== '');
    
            $joined = implode(' ', $lines);
            // Gộp khoảng trắng thừa
            $joined = preg_replace('/\s+/u', ' ', $joined);
    
            if ($joined !== '') $result[] = trim($joined);
        }
    
        return implode("\n\n", $result);
    }
    
    
    private function isNoOpCorrection(string $content, string $errorText, string $replacement): bool
    {
        $errorText   = trim($errorText);
        $replacement = trim($replacement);
        if ($errorText === '' || $replacement === '') return false;
    
        $errLower  = mb_strtolower($errorText, 'UTF-8');
        $replLower = mb_strtolower($replacement, 'UTF-8');
    
        if ($replLower === $errLower) return true; // giống hệt (dù prompt cấm nhưng phòng hờ)
    
        // Trường hợp "bất" -> "bất toàn": replacement bắt đầu bằng đúng error_text
        if (str_starts_with($replLower, $errLower)) {
            $suffix = trim(mb_substr($replacement, mb_strlen($errorText, 'UTF-8'), null, 'UTF-8'));
            if ($suffix === '') return true;
    
            // Tìm error_text trong content gốc, xem ngay sau nó có đúng $suffix không
            $pos = mb_stripos($content, $errorText, 0, 'UTF-8');
            if ($pos !== false) {
                $after = trim(mb_substr(
                    $content,
                    $pos + mb_strlen($errorText, 'UTF-8'),
                    mb_strlen($suffix, 'UTF-8') + 5,
                    'UTF-8'
                ));
                if (mb_stripos($after, $suffix, 0, 'UTF-8') === 0) {
                    return true; // suffix vốn đã có sẵn ngay sau -> false positive, không phải lỗi thật
                }
            }
        }
        return false;
    }
    
    
    private function calculateReadability(string $content): array
    {
        $content = trim(strip_tags($content));
        if (empty($content)) {
            return $this->emptyReadabilityResult();
        }
    
        // Phát hiện ngôn ngữ đơn giản: tỉ lệ ký tự có dấu tiếng Việt
        $isVietnamese = (bool) preg_match('/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/iu', $content);
    
        // Tách câu (theo dấu . ! ? xuống dòng đôi)
        $sentences = preg_split('/(?<=[.!?])\s+(?=[A-ZÀ-Ỹ])|\n\s*\n/u', $content, -1, PREG_SPLIT_NO_EMPTY);
        $sentences = array_values(array_filter(array_map('trim', $sentences), fn($s) => $s !== ''));
        $sentenceCount = max(count($sentences), 1);
    
        // Tách từ
        preg_match_all('/\p{L}+/u', $content, $wordMatches);
        $words = $wordMatches[0];
        $wordCount = max(count($words), 1);
        $uniqueWordCount = count(array_unique(array_map('mb_strtolower', $words)));
    
        // Đếm âm tiết
        $totalSyllables = 0;
        $wordsWith3Syllables = 0;
        foreach ($words as $w) {
            $syl = $isVietnamese
                ? $this->countVietnameseSyllables($w)     // tiếng Việt: mỗi "từ" (tách bằng khoảng trắng) ~ 1 âm tiết
                : $this->countEnglishSyllables($w);         // tiếng Anh: đếm nhóm nguyên âm
            $totalSyllables += $syl;
            if ($syl >= 3) $wordsWith3Syllables++;
        }
    
        $letterCount = mb_strlen(preg_replace('/[^\p{L}]/u', '', $content), 'UTF-8');
        $avgSyllablesPerWord = $totalSyllables / $wordCount;
        $wordsPerSentence     = $wordCount / $sentenceCount;
    
        // Câu dài nhất
        usort($sentences, fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));
        $longestSentence = $sentences[0] ?? '';
    
        $paragraphCount = max(count(preg_split('/\n\s*\n+/u', trim($content))), 1);
    
        // ── Công thức readability (chuẩn cho tiếng Anh; áp dụng gần đúng cho tiếng Việt) ──
        $flesch      = 206.835 - (1.015 * $wordsPerSentence) - (84.6 * $avgSyllablesPerWord);
        $fleschGrade = (0.39 * $wordsPerSentence) + (11.8 * $avgSyllablesPerWord) - 15.59;
        $gunningFog  = 0.4 * ($wordsPerSentence + 100 * ($wordsWith3Syllables / $wordCount));
        $ari         = (4.71 * ($letterCount / $wordCount)) + (0.5 * $wordsPerSentence) - 21.43;
        $colemanLiau = (0.0588 * ($letterCount / $wordCount * 100)) - (0.296 * ($sentenceCount / $wordCount * 100)) - 15.8;
    
        return [
            'text_stats' => [
                'letterCount' => $letterCount,
                'sentenceCount' => $sentenceCount,
                'uniqueWordCount' => $uniqueWordCount,
                'syllableCount' => $totalSyllables,
                'totalSyllables' => $totalSyllables,
                'averageSyllablesPerWord' => round($avgSyllablesPerWord, 2),
                'wordsWithThreeSyllables' => $wordsWith3Syllables,
                'percentWordsWithThreeSyllables' => round($wordsWith3Syllables / $wordCount * 100, 1),
                'longestSentence' => mb_substr($longestSentence, 0, 300),
                'paragraphCount' => $paragraphCount,
                'averageSpeakingTime' => round($wordCount / 130, 1),
                'averageReadingTime'  => round($wordCount / 200, 1),
                'averageWritingTime'  => round($wordCount / 40, 1),
            ],
            'readability' => [
                'fleschReadingEase'         => round($flesch, 1),
                'fleschGradeLevel'          => round($fleschGrade, 1),
                'gunningFoxIndex'           => round($gunningFog, 1),
                'automatedReadabilityIndex' => round($ari, 1),
                'colemanLiauIndex'          => round($colemanLiau, 1),
                'smogIndex' => 0.0, 'powersSumnerKearl' => 0.0, 'forcastGradeLevel' => 0.0,
                'daleChallReadabilityGrade' => 0.0, 'spacheReadabilityGrade' => 0.0, 'linsearWriteGrade' => 0.0,
            ],
            'sentences' => [],
        ];
    }
    
    private function countEnglishSyllables(string $word): int
    {
        $word = strtolower(preg_replace('/[^a-z]/i', '', $word));
        if ($word === '') return 0;
        if (strlen($word) <= 3) return 1;
        $word = preg_replace('/(?:[^laeiouy]es|ed|[^laeiouy]e)$/', '', $word);
        $word = preg_replace('/^y/', '', $word);
        preg_match_all('/[aeiouy]{1,2}/', $word, $m);
        return max(count($m[0]), 1);
    }
    
    private function countVietnameseSyllables(string $word): int
    {
        // Tiếng Việt: mỗi token cách nhau bởi khoảng trắng = 1 âm tiết
        return 1;
    }
    
    private function emptyReadabilityResult(): array
    {
        return [
            'text_stats' => array_fill_keys([
                'letterCount','sentenceCount','uniqueWordCount','syllableCount','totalSyllables',
                'averageSyllablesPerWord','wordsWithThreeSyllables','percentWordsWithThreeSyllables',
                'longestSentence','paragraphCount','averageSpeakingTime','averageReadingTime','averageWritingTime',
            ], 0),
            'readability' => array_fill_keys([
                'fleschReadingEase','fleschGradeLevel','gunningFoxIndex','smogIndex','powersSumnerKearl',
                'forcastGradeLevel','colemanLiauIndex','automatedReadabilityIndex','daleChallReadabilityGrade',
                'spacheReadabilityGrade','linsearWriteGrade',
            ], 0.0),
            'sentences' => [],
        ];
    }
    
    private function mergeBrokenWordBlocks(array $blocks): array
    {
        if (count($blocks) < 2) return $blocks;
    
        $merged = [];
        foreach ($blocks as $block) {
            $text = $block['text'] ?? '';
            if ($text === '') continue;
    
            if (!empty($merged)) {
                $lastKey  = array_key_last($merged);
                $prevText = $merged[$lastKey]['text'];
                $lastChar  = mb_substr($prevText, -1, 1, 'UTF-8');
                $firstChar = mb_substr($text, 0, 1, 'UTF-8');
    
                // Ký tự cuối của block trước & ký tự đầu block này đều là chữ/số
                // => Copyscape đang cắt giữa 1 từ, không phải ranh giới thật.
                if (preg_match('/[\p{L}\p{N}]/u', $lastChar) && preg_match('/[\p{L}\p{N}]/u', $firstChar)) {
                    $prevLen  = mb_strlen($prevText, 'UTF-8');
                    $curLen   = mb_strlen($text, 'UTF-8');
                    $totalLen = $prevLen + $curLen;
    
                    $merged[$lastKey]['text'] .= $text;
    
                    $prevFake = $merged[$lastKey]['result']['fake'] ?? 0;
                    $curFake  = $block['result']['fake'] ?? 0;
                    $newFake  = $totalLen > 0 ? (($prevFake * $prevLen) + ($curFake * $curLen)) / $totalLen : $prevFake;
    
                    $merged[$lastKey]['result']['fake'] = round($newFake, 4);
                    $merged[$lastKey]['result']['real'] = round(1 - $newFake, 4);
                    continue;
                }
            }
            $merged[] = $block;
        }
        return array_values($merged);
    }
    
    private function mergeBlocksBySentence(array $blocks): array
    {
        if (count($blocks) < 2) return $blocks;

        $merged = [];
        foreach ($blocks as $block) {
            $text = $block['text'] ?? '';
            if ($text === '') continue;

            if (!empty($merged)) {
                $lastKey  = array_key_last($merged);
                // Chuẩn hoá khoảng trắng
                $prevNormalized = preg_replace('/[\x{00A0}\x{200B}\s]+$/u', ' ', $merged[$lastKey]['text']);
                $prevTrimmed = rtrim($prevNormalized);
                
                // Kiểm tra xem có kết thúc bằng dấu chấm, hỏi, than... không
                $endsSentence = (bool) preg_match('/[.!?…:][\'"”\)]?$/u', $prevTrimmed);

                // ==============================================================
                // BỘ LỌC THÔNG MINH: BỎ QUA CÁC TỪ VIẾT TẮT HỌC THUẬT
                // ==============================================================
                if ($endsSentence) {
                    // Danh sách từ viết tắt Anh/Việt phổ biến không phải là cuối câu
                    $abbrPattern = '/\b(?:et al|e\.g|i\.e|etc|vs|fig|vol|ed|dr|prof|mr|mrs|ms|inc|ltd|gs|ts|ths|pgs|tp|nxb|tr)\.[\'"”\)]?$/iu';
                    
                    if (preg_match($abbrPattern, $prevTrimmed)) {
                        $endsSentence = false; // Ép buộc gộp dòng, không được cắt!
                    }
                }

                if (!$endsSentence) {
                    $prevText = $merged[$lastKey]['text'];
                    $prevLen  = mb_strlen($prevText, 'UTF-8');
                    $curLen   = mb_strlen($text, 'UTF-8');
                    $totalLen = $prevLen + $curLen;

                    // Gộp text
                    $merged[$lastKey]['text'] .= $text;

                    // Tính lại điểm AI trung bình có trọng số cho câu đã gộp
                    $prevFake = $merged[$lastKey]['result']['fake'] ?? 0;
                    $curFake  = $block['result']['fake'] ?? 0;
                    $newFake  = $totalLen > 0
                        ? (($prevFake * $prevLen) + ($curFake * $curLen)) / $totalLen
                        : $prevFake;

                    $merged[$lastKey]['result']['fake'] = round($newFake, 4);
                    $merged[$lastKey]['result']['real'] = round(1 - $newFake, 4);
                    continue; // Chuyển sang block tiếp theo
                }
            }
            $merged[] = $block;
        }
        return array_values($merged);
    }
        
    private function mergeShortBlocks(array $blocks, int $minChars = 15): array
    {
        if (count($blocks) < 2) return $blocks;
    
        $merged = [];
        foreach ($blocks as $block) {
            $text = $block['text'] ?? '';
            if (trim($text) === '') continue;
    
            if (!empty($merged)) {
                $lastKey = array_key_last($merged);
                $prevTrimmed = trim($merged[$lastKey]['text']);
    
                // Block TRƯỚC quá ngắn → luôn gộp nó với block hiện tại,
                // bất kể dấu câu ở cuối là gì.
                if (mb_strlen($prevTrimmed, 'UTF-8') < $minChars) {
                    $prevText = $merged[$lastKey]['text'];
                    $prevLen  = mb_strlen($prevText, 'UTF-8');
                    $curLen   = mb_strlen($text, 'UTF-8');
                    $totalLen = $prevLen + $curLen;
    
                    $merged[$lastKey]['text'] .= $text;
    
                    $prevFake = $merged[$lastKey]['result']['fake'] ?? 0;
                    $curFake  = $block['result']['fake'] ?? 0;
                    $newFake  = $totalLen > 0
                        ? (($prevFake * $prevLen) + ($curFake * $curLen)) / $totalLen
                        : $prevFake;
    
                    $merged[$lastKey]['result']['fake'] = round($newFake, 4);
                    $merged[$lastKey]['result']['real'] = round(1 - $newFake, 4);
                    continue;
                }
            }
            $merged[] = $block;
        }
    
        // Nếu block CUỐI CÙNG vẫn còn quá ngắn (không có block sau để gộp vào),
        // gộp ngược nó vào block liền trước.
        if (count($merged) >= 2) {
            $lastKey = array_key_last($merged);
            $lastTrimmed = trim($merged[$lastKey]['text']);
            if (mb_strlen($lastTrimmed, 'UTF-8') < $minChars) {
                $last = $merged[$lastKey];
                unset($merged[$lastKey]);
                $prevKey = array_key_last($merged);
    
                $prevLen  = mb_strlen($merged[$prevKey]['text'], 'UTF-8');
                $curLen   = mb_strlen($last['text'], 'UTF-8');
                $totalLen = $prevLen + $curLen;
    
                $merged[$prevKey]['text'] .= $last['text'];
                $prevFake = $merged[$prevKey]['result']['fake'] ?? 0;
                $curFake  = $last['result']['fake'] ?? 0;
                $newFake  = $totalLen > 0 ? (($prevFake*$prevLen)+($curFake*$curLen))/$totalLen : $prevFake;
                $merged[$prevKey]['result']['fake'] = round($newFake, 4);
                $merged[$prevKey]['result']['real'] = round(1 - $newFake, 4);
            }
        }
    
        return array_values($merged);
    }
    
    /**
     * Kiểm tra 1 block có phải "filler" hay không — tức là chuỗi gần như chỉ gồm
     * dấu chấm dẫn dòng (mục lục), dấu gạch ngang, khoảng trắng... không có nội
     * dung chữ/số thật đáng để chấm điểm AI riêng.
     */
    private function isFillerBlock(string $text): bool
    {
        $trimmed = trim($text);
        if ($trimmed === '') return true;
    
        $alnumCount = mb_strlen(preg_replace('/[^\p{L}\p{N}]+/u', '', $trimmed), 'UTF-8');
        $totalCount = mb_strlen($trimmed, 'UTF-8');
    
        // Dưới 20% ký tự là chữ/số → coi là dòng filler (chấm dẫn, gạch ngang, ...)
        return $totalCount > 0 && ($alnumCount / $totalCount) < 0.2;
    }
    
    /**
     * Gộp các block "filler" (dòng chấm dẫn mục lục, dòng để trống viết tay...)
     * vào block liền trước (hoặc liền sau nếu là block đầu tiên), bất kể dấu câu
     * ở cuối là gì — vì bản thân các dòng này không phải câu có nghĩa.
     */
    private function mergeFillerBlocks(array $blocks): array
    {
        if (count($blocks) < 2) return $blocks;
    
        $merged = [];
        foreach ($blocks as $block) {
            $text = $block['text'] ?? '';
            if (trim($text) === '') continue;
    
            if (!empty($merged) && $this->isFillerBlock($text)) {
                $lastKey  = array_key_last($merged);
                $prevText = $merged[$lastKey]['text'];
                $prevLen  = mb_strlen($prevText, 'UTF-8');
                $curLen   = mb_strlen($text, 'UTF-8');
                $totalLen = $prevLen + $curLen;
    
                $merged[$lastKey]['text'] .= $text;
    
                $prevFake = $merged[$lastKey]['result']['fake'] ?? 0;
                $curFake  = $block['result']['fake'] ?? 0;
                $newFake  = $totalLen > 0
                    ? (($prevFake * $prevLen) + ($curFake * $curLen)) / $totalLen
                    : $prevFake;
    
                $merged[$lastKey]['result']['fake'] = round($newFake, 4);
                $merged[$lastKey]['result']['real'] = round(1 - $newFake, 4);
                continue;
            }
            $merged[] = $block;
        }
    
        // Nếu block ĐẦU TIÊN là filler (không có block trước để gộp vào),
        // gộp nó về phía sau (block thứ 2).
        if (count($merged) >= 2 && $this->isFillerBlock($merged[array_key_first($merged)]['text'])) {
            $firstKey  = array_key_first($merged);
            $first     = $merged[$firstKey];
            unset($merged[$firstKey]);
            $merged    = array_values($merged);
            $merged[0]['text'] = $first['text'] . $merged[0]['text'];
        }
    
        return array_values($merged);
    }
    
    /**
     * Chia văn bản dài thành nhiều đoạn, mỗi đoạn ≤ maxWords từ, cắt tại
     * ranh giới đoạn văn (dòng trống) để không bao giờ cắt giữa câu/từ.
     */
    private function splitIntoChunksByWords(string $content, int $maxWords): array
    {
        $paragraphs = preg_split('/\n\s*\n+/u', $content);
        $chunks = [];
        $current = '';
        $currentWords = 0;
    
        foreach ($paragraphs as $para) {
            $para = trim($para);
            if ($para === '') continue;
            $paraWords = $this->countWords($para);
    
            // Đoạn văn đơn lẻ dài hơn maxWords → cắt cứng theo từ
            if ($paraWords > $maxWords) {
                if ($current !== '') { $chunks[] = $current; $current = ''; $currentWords = 0; }
                $words = preg_split('/\s+/u', $para, -1, PREG_SPLIT_NO_EMPTY);
                foreach (array_chunk($words, $maxWords) as $wordChunk) {
                    $chunks[] = implode(' ', $wordChunk);
                }
                continue;
            }
    
            if ($currentWords + $paraWords > $maxWords && $current !== '') {
                $chunks[] = $current;
                $current = $para;
                $currentWords = $paraWords;
            } else {
                $current = $current === '' ? $para : $current . "\n\n" . $para;
                $currentWords += $paraWords;
            }
        }
        if (trim($current) !== '') $chunks[] = $current;
    
        return $chunks ?: [$content]; // fallback: trả nguyên nếu rỗng
    }
    
    private function sanitizeContent(string $text): string
    {
        if (empty(trim($text))) return $text;

        // 1. CHẶN ĐỨNG TÀI LIỆU THAM KHẢO (Nguồn gây đạo văn ảo nặng nhất)
        // Tìm các từ khóa này, nhưng chỉ chém bỏ nếu nó nằm ở 30% dung lượng cuối của file
        // (để tránh cắt nhầm nếu sinh viên lỡ gõ chữ "tài liệu tham khảo" ở phần mở bài)
        $refKeywords = [
            'Tài liệu tham khảo', 
            'Danh mục tài liệu tham khảo', 
            'References', 
            'Bibliography', 
            'Works Cited'
        ];
        $pattern = '/\n\s*(?:[IVX\d]+\.?\s*)?(' . implode('|', $refKeywords) . ')\s*\n/iu';
        
        if (preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE)) {
            // Lấy vị trí của từ khóa xuất hiện cuối cùng trong bài
            $lastMatch = end($matches[0]);
            $matchPos = $lastMatch[1];
            $totalLen = strlen($text);
            
            // Nếu từ khóa nằm ở 30% cuối bài -> Cắt bỏ toàn bộ chữ phía sau nó
            if ($matchPos > ($totalLen * 0.70)) {
                $text = substr($text, 0, $matchPos);
            }
        }

        // 2. XÓA MỤC LỤC BỊ BÓC VỠ
        // Dấu hiệu nhận biết: Câu có một dãy dấu chấm dài và kết thúc bằng số trang
        // VD: Lời mở đầu ...................................... 1
        $text = preg_replace('/^.*\.{5,}\s*\d+\s*$/m', '', $text);

        // 3. XÓA HEADER/FOOTER & SỐ TRANG RƠI RỚT
        // Bỏ các dòng chỉ chứa đúng chữ số
        $text = preg_replace('/^\s*\d+\s*$/m', '', $text);
        
        // 4. LÀM SẠCH KHOẢNG TRẮNG DƯ THỪA
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }
    
    private function detectLang(?string $content): string
    {
        $content = (string) $content; // Chuyển null thành chuỗi rỗng để không bị lỗi
        $isVietnamese = (bool) preg_match(
            '/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/iu',
            $content
        );
        return $isVietnamese ? 'vi' : 'en';
    }
}