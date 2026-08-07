<?php

namespace App\Controllers;

class MockOriginalityController
{
    const COST_PER_100_WORDS = 1000;
    const MAX_PDF_SIZE_MB    = 20;

    // ============================================================
    // COST & AUTH HELPERS
    // ============================================================

    /**
     * Tính cost lean — dùng preg_match_all thay vì str_word_count(strip_tags())
     * để tránh tốn RAM với chuỗi lớn.
     */
    private function calcCost(string $content): int
    {
        if (empty($content)) return 0;
        $words = preg_match_all('/\S+/u', strip_tags($content));
        return (int) ceil($words / 100) * self::COST_PER_100_WORDS;
    }

    private function countWords(string $content): int
    {
        return (int) preg_match_all('/\S+/u', strip_tags($content));
    }

    /**
     * Khôi phục khoảng trắng và dấu xuống dòng gốc vào các blocks AI
     * do API thường lược bỏ khoảng trắng thừa để tiết kiệm dung lượng.
     */
    private function restoreFormatting(string $originalContent, array &$blocks): void
    {
        if (empty($blocks) || empty($originalContent)) {
            return;
        }

        $origChars = preg_split('//u', $originalContent, -1, PREG_SPLIT_NO_EMPTY);
        $originalLen = count($origChars);
        $offset = 0;

        foreach ($blocks as $idx => &$block) {
            $text = $block['text'] ?? '';
            if (empty($text)) continue;

            // Lấy các ký tự chữ/số để so sánh, bỏ qua khoảng trắng/xuống dòng
            $textCharsStr = preg_replace('/[^\p{L}\p{N}]+/u', '', mb_strtolower($text, 'UTF-8'));
            $textCharsLen = mb_strlen($textCharsStr, 'UTF-8');

            $currentPos = $offset;
            $matchLen = 0;

            // Cắt nội dung theo đúng số lượng chữ/số mà API trả về
            if ($textCharsLen > 0) {
                while ($currentPos < $originalLen && $matchLen < $textCharsLen) {
                    $charOrig = mb_strtolower($origChars[$currentPos], 'UTF-8');
                    if (preg_match('/^[\p{L}\p{N}]$/u', $charOrig)) {
                        $matchLen++;
                    }
                    $currentPos++;
                }
            } else {
                $textLen = mb_strlen($text, 'UTF-8');
                $matchLen2 = 0;
                while ($currentPos < $originalLen && $matchLen2 < $textLen) {
                    $currentPos++;
                    $matchLen2++;
                }
            }

            // Gom toàn bộ khoảng trắng, dấu xuống dòng liền kề vào khối này (không bỏ sót 1 ký tự nào)
            while ($currentPos < $originalLen) {
                $charOrig = mb_strtolower($origChars[$currentPos], 'UTF-8');
                if (preg_match('/^[\p{L}\p{N}]$/u', $charOrig)) {
                    break;
                }
                $currentPos++;
            }

            $restoredText = mb_substr($originalContent, $offset, $currentPos - $offset, 'UTF-8');
            $block['text'] = $restoredText;
            $offset = $currentPos;
        }

        // Gom nốt phần dư (khoảng trắng/xuống dòng ở cuối file) vào câu cuối
        if ($offset < $originalLen && count($blocks) > 0) {
            $suffix = mb_substr($originalContent, $offset, null, 'UTF-8');
            $blocks[count($blocks) - 1]['text'] .= $suffix;
        }
    }

    /** Ước tính cost từ kích thước file byte — ~5 byte/từ */
    private function estimateCostFromBytes(int $bytes): int
    {
        $words = (int) ceil($bytes / 5);
        return (int) ceil($words / 100) * self::COST_PER_100_WORDS;
    }

    /** HEAD request để lấy Content-Length, fallback 1 đơn vị */
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

        return $size > 0
            ? $this->estimateCostFromBytes($size)
            : self::COST_PER_100_WORDS;
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
    // ENDPOINTS
    // ============================================================

    public function getBalance()
    {
        $this->verifyKey();
        $uuid = $this->getUuid();
        if (!$uuid) return $this->response(['error' => 'Vui lòng đăng nhập.'], 401);

        return $this->response([
            'credits'             => $this->getPoints($uuid),
            'subscriptionCredits' => 0,
        ]);
    }

    public function uploadPdf()
    {
        $this->verifyKey();

        $uuid = $this->getUuid();
        if (!$uuid) {
            return $this->response(['status' => 'error', 'message' => 'Vui lòng đăng nhập.'], 401);
        }

        if (empty($_FILES['file'])) {
            return $this->response(['status' => 'error', 'message' => 'Không tìm thấy file trong request.'], 422);
        }

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
            return $this->response([
                'status'  => 'error',
                'message' => $phpErrors[$file['error']] ?? 'Upload thất bại (mã ' . $file['error'] . ')',
            ], 422);
        }

        if ($file['size'] > self::MAX_PDF_SIZE_MB * 1024 * 1024) {
            return $this->response([
                'status'  => 'error',
                'message' => 'File quá lớn. Tối đa ' . self::MAX_PDF_SIZE_MB . 'MB.',
            ], 422);
        }

        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($mimeType !== 'application/pdf') {
            return $this->response([
                'status'  => 'error',
                'message' => 'Chỉ chấp nhận file PDF. Phát hiện: ' . $mimeType,
            ], 422);
        }

        $uploadDir = '/www/wwwroot/ai.vmied.com/public/uploads/scans/';

        if (!is_writable($uploadDir)) {
            return $this->response([
                'status'  => 'error',
                'message' => 'Thư mục upload không có quyền ghi: ' . $uploadDir,
            ], 500);
        }

        $safeUuid = preg_replace('/[^a-zA-Z0-9\-]/', '', $uuid);
        $fileName = $safeUuid . '_' . time() . '_' . bin2hex(random_bytes(6)) . '.pdf';
        $destPath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return $this->response([
                'status'  => 'error',
                'message' => 'Không thể lưu file lên server.',
            ], 500);
        }

        $publicUrl = 'https://ai.vmied.com/uploads/scans/' . $fileName;

        error_log('[OriginalityUpload] uuid=' . $uuid . ' file=' . $fileName . ' size=' . $file['size']);

        return $this->response([
            'status'   => 'ok',
            'url'      => $publicUrl,
            'fileName' => $fileName,
            'size'     => $file['size'],
        ]);
    }

    // ============================================================
    // SCAN TEXT
    // ============================================================

    public function scan()
    {
        ini_set('memory_limit', '256M');

        $this->verifyKey();

        $input   = $this->getInput();
        $content = trim($input['content'] ?? '');
        $title   = $input['title'] ?? 'Scan';

        $uuid = $this->getUuid();
        if (!$uuid) {
            return $this->response(['status' => 'error', 'message' => 'Vui lòng đăng nhập.'], 401);
        }

        if (empty($content)) {
            return $this->response(['status' => 'error', 'message' => 'Nội dung không được để trống.'], 422);
        }

        $cost    = $this->calcCost($content);
        $balance = $this->getPoints($uuid);

        if ($balance < $cost) {
            $wordCount = $this->countWords($content);
            return $this->response([
                'status'   => 'error',
                'code'     => 'INSUFFICIENT_POINTS',
                'message'  => "Số dư không đủ. Cần {$cost}đ để kiểm tra {$wordCount} từ, bạn còn " . number_format($balance) . "đ.",
                'required' => $cost,
                'balance'  => $balance,
            ], 402);
        }

        $apiResult = $this->callAPI(null, null, $content, $title, $input);

        if (!$apiResult['success']) {
            unset($content, $input);
            return $this->response([
                'status'  => 'error',
                'message' => 'Lỗi ' . $apiResult['message'],
                'debug'   => $apiResult['debug'] ?? null,
            ], 502);
        }

        $results = $this->unwrapV3($apiResult['data']);
        unset($apiResult, $input);

        // Khôi phục format (khoảng trắng, xuống dòng) cho AI blocks để UI hiển thị chuẩn
        if (isset($results['ai']['blocks']) && is_array($results['ai']['blocks'])) {
            $this->restoreFormatting($content, $results['ai']['blocks']);
        }

        if (!isset($results['properties'])) $results['properties'] = [];
        $results['properties']['content'] = $content;
        $results['properties']['formattedContent'] = $content;

        $results['pointsUsed']      = $cost;
        $results['pointsRemaining'] = $balance - $cost;

        $this->deductPoints($uuid, $cost, 'SCAN');
        $this->ensureTable();

        $this->saveHistory($title, null, null, null, 'text', $results);
        $results['scanId'] = (int) app()->db->id();

        unset($content);

        return $this->response($results);
    }

    public function scanUrl()
    {
        // FIX: Tăng memory limit cho request nặng
        ini_set('memory_limit', '256M');

        $this->verifyKey();

        $input = $this->getInput();
        $url   = trim($input['url'] ?? '');

        $uuid = $this->getUuid();
        if (!$uuid) {
            return $this->response(['status' => 'error', 'message' => 'Vui lòng đăng nhập.'], 401);
        }

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->response(['status' => 'error', 'message' => 'URL không hợp lệ hoặc bị bỏ trống.'], 422);
        }

        $isPdfUpload   = $this->isOwnUploadedPdf($url, $uuid);
        $estimatedCost = $this->estimateCostFromUrl($url);
        $balance       = $this->getPoints($uuid);

        if ($balance < $estimatedCost) {
            return $this->response([
                'status'   => 'error',
                'code'     => 'INSUFFICIENT_POINTS',
                'message'  => "Số dư không đủ. Ước tính cần {$estimatedCost}đ, bạn còn " . number_format($balance) . "đ.",
                'required' => $estimatedCost,
                'balance'  => $balance,
            ], 402);
        }

        $title = $input['title'] ?? ($isPdfUpload
            ? 'PDF: ' . basename(parse_url($url, PHP_URL_PATH))
            : 'URL: ' . (parse_url($url, PHP_URL_HOST) ?? 'Unknown'));

        $apiResult = $this->callAPI($url, null, null, $title, $input);
        unset($input);

        if (!$apiResult['success']) {
            return $this->response([
                'status'  => 'error',
                'message' => 'Lỗi ' . $apiResult['message'],
                'debug'   => $apiResult['debug'] ?? null,
            ], 502);
        }

        $results = $this->unwrapV3($apiResult['data']);
        unset($apiResult);

        $wordCount  = (int) ($results['properties']['wordCount'] ?? 0);
        $actualCost = $wordCount > 0
            ? (int) ceil($wordCount / 100) * self::COST_PER_100_WORDS
            : $estimatedCost;

        $results['pointsUsed']      = $actualCost;
        $results['pointsRemaining'] = $balance - $actualCost;

        $this->deductPoints($uuid, $actualCost, $isPdfUpload ? 'PDF' : 'URL');
        $this->ensureTable();
        $this->saveHistory(
            $title,
            null,
            $url,
            $isPdfUpload ? $url : null,
            $isPdfUpload ? 'pdf' : 'url',
            $results
        );

        return $this->response($results);
    }

    // ============================================================
    // SCAN BATCH
    // ============================================================

    public function scanBatch()
    {
        // FIX: Tăng memory limit — batch xử lý nhiều item
        ini_set('memory_limit', '256M');

        $this->verifyKey();

        $input = $this->getInput();
        $batch = $input['batch'] ?? [];

        $uuid = $this->getUuid();
        if (!$uuid) {
            return $this->response(['status' => 'error', 'message' => 'Vui lòng đăng nhập.'], 401);
        }

        if (empty($batch) || !is_array($batch)) {
            return $this->response(['status' => 'error', 'message' => 'Batch không được để trống.'], 422);
        }

        // FIX: Tính totalCost bằng vòng lặp thay vì array_map — tránh copy toàn bộ batch vào closure
        $totalCost = 0;
        foreach ($batch as $item) {
            $totalCost += $this->calcCost($item['content'] ?? '');
        }

        $balance = $this->getPoints($uuid);

        if ($balance < $totalCost) {
            return $this->response([
                'status'   => 'error',
                'code'     => 'INSUFFICIENT_POINTS',
                'message'  => "Số dư không đủ. Batch cần {$totalCost}đ, bạn còn " . number_format($balance) . "đ.",
                'required' => $totalCost,
                'balance'  => $balance,
            ], 402);
        }

        $results    = [];
        $errors     = [];
        $actualCost = 0;

        $this->ensureTable();

        foreach ($batch as $idx => $item) {
            $itemContent = trim($item['content'] ?? '');
            $itemTitle   = $item['title'] ?? ('Batch #' . ($idx + 1));

            if (empty($itemContent)) {
                $errors[] = ['index' => $idx, 'title' => $itemTitle, 'message' => 'Nội dung trống'];
                continue;
            }

            $itemCost  = $this->calcCost($itemContent);
            $apiResult = $this->callAPI(null, null, $itemContent, $itemTitle, $input);

            if (!$apiResult['success']) {
                $errors[] = ['index' => $idx, 'title' => $itemTitle, 'message' => $apiResult['message']];
                unset($apiResult, $itemContent);
                continue;
            }

            $r = $this->unwrapV3($apiResult['data']);
            unset($apiResult);

            // Ghi đè content gốc để giữ nguyên định dạng xuống dòng
            if (isset($r['properties'])) {
                $r['properties']['content'] = $itemContent;
                $r['properties']['formattedContent'] = $itemContent;
            }

            $results[]   = $r;
            $actualCost += $itemCost;

            // Lưu lại content vào DB để xem lại không bị mất xuống dòng
            $this->saveHistory($itemTitle, $itemContent, null, null, 'batch', $r);
            unset($r, $itemContent);

            // FIX: Gọi GC sau mỗi item để tránh memory tích luỹ
            gc_collect_cycles();
        }

        $this->deductPoints($uuid, $actualCost, 'BATCH');

        return $this->response([
            'results'         => $results,
            'errors'          => $errors,
            'pointsUsed'      => $actualCost,
            'pointsRemaining' => $balance - $actualCost,
        ]);
    }

    // ============================================================
    // ASYNC JOB — chống timeout cho PDF lớn
    // ============================================================

    /**
     * POST /mock/originality/scan-pdf
     * Trả về job_id ngay, worker xử lý nền.
     */
    public function scanPdf()
    {
        $this->verifyKey();
        $this->ensureJobTable();

        $input = $this->getInput();
        $url   = trim($input['url'] ?? '');

        $uuid = $this->getUuid();
        if (!$uuid) {
            return $this->response(['status' => 'error', 'message' => 'Vui lòng đăng nhập.'], 401);
        }

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->response(['status' => 'error', 'message' => 'URL không hợp lệ.'], 422);
        }

        if (!$this->isOwnUploadedPdf($url, $uuid)) {
            return $this->response(['status' => 'error', 'message' => 'URL không được phép.'], 403);
        }

        $estimatedCost = $this->estimateCostFromUrl($url);
        $balance       = $this->getPoints($uuid);

        if ($balance < $estimatedCost) {
            return $this->response([
                'status'   => 'error',
                'code'     => 'INSUFFICIENT_POINTS',
                'message'  => "Số dư không đủ. Ước tính cần {$estimatedCost}đ, bạn còn " . number_format($balance) . "đ.",
                'required' => $estimatedCost,
                'balance'  => $balance,
            ], 402);
        }

        $jobId = bin2hex(random_bytes(16));
        $title = $input['title'] ?? 'PDF: ' . basename(parse_url($url, PHP_URL_PATH));

        app()->db->insert('originality_jobs', [
            'job_id'       => $jobId,
            'account_uuid' => $uuid,
            'status'       => 'pending',
            'type'         => 'pdf',
            'title'        => $title,
            'file_url'     => $url,
            'scan_options' => json_encode([
                'check_ai'               => (bool) ($input['check_ai']               ?? true),
                'check_plagiarism'       => (bool) ($input['check_plagiarism']       ?? true),
                'check_facts'            => (bool) ($input['check_facts']            ?? true),
                'check_readability'      => (bool) ($input['check_readability']      ?? true),
                'check_grammar'          => (bool) ($input['check_grammar']          ?? true),
                'check_contentOptimizer' => (bool) ($input['check_contentOptimizer'] ?? false),
                'aiModelVersion'         => $input['aiModelVersion'] ?? 'multilang',
            ], JSON_UNESCAPED_UNICODE),
        ]);

        unset($input);

        $this->dispatchWorkerViaCurl($jobId);

        return $this->response([
            'status'  => 'queued',
            'job_id'  => $jobId,
            'message' => 'Đang xử lý, vui lòng kiểm tra trạng thái.',
        ]);
    }

    /**
     * POST /internal/originality/process-job
     * Chỉ gọi nội bộ — xử lý PDF async.
     */
    public function processJob()
    {
        ini_set('memory_limit', '256M');
        set_time_limit(300);

        $input  = $this->getInput();
        $secret = $input['secret'] ?? '';
        $jobId  = $input['job_id'] ?? '';

        if ($secret !== ($_ENV['INTERNAL_SECRET'] ?? '') || empty($jobId)) {
            http_response_code(403);
            exit;
        }

        $this->ensureJobTable();
        $this->ensureTable();

        $job = app()->db->get('originality_jobs', '*', ['job_id' => $jobId]);
        if (!$job || $job['status'] !== 'pending') {
            exit;
        }

        // Lock job tránh chạy 2 lần
        app()->db->update('originality_jobs', ['status' => 'processing'], ['job_id' => $jobId]);

        $options   = json_decode($job['scan_options'] ?? '{}', true);
        $apiResult = $this->callAPI($job['file_url'], null, null, $job['title'], $options);
        unset($options);

        if (!$apiResult['success']) {
            app()->db->update('originality_jobs', [
                'status'    => 'error',
                'error_msg' => substr($apiResult['message'], 0, 500),
            ], ['job_id' => $jobId]);
            exit;
        }

        $results   = $this->unwrapV3($apiResult['data']);
        unset($apiResult);

        $wordCount = (int) ($results['properties']['wordCount'] ?? 0);
        $cost      = $wordCount > 0
            ? (int) ceil($wordCount / 100) * self::COST_PER_100_WORDS
            : self::COST_PER_100_WORDS;

        $uuid = $job['account_uuid'];

        $this->deductPoints($uuid, $cost, 'PDF');
        $this->saveHistory($job['title'], null, $job['file_url'], $job['file_url'], 'pdf', $results);
        $scanId = (int) app()->db->id();

        $results['pointsUsed'] = $cost;
        $results['scanId']     = $scanId;

        app()->db->update('originality_jobs', [
            'status' => 'done',
            'result' => json_encode($results, JSON_UNESCAPED_UNICODE),
        ], ['job_id' => $jobId]);

        unset($results);
        exit;
    }

    /**
     * GET /mock/originality/job-status/{jobId}
     */
    public function jobStatus($jobId)
    {
        $this->verifyKey();
        $this->ensureJobTable();

        $uuid = $this->getUuid();
        if (!$uuid) return $this->response(['error' => 'Vui lòng đăng nhập.'], 401);

        $job = app()->db->get('originality_jobs', '*', [
            'job_id'       => $jobId,
            'account_uuid' => $uuid,
        ]);

        if (!$job) return $this->response(['error' => 'Không tìm thấy job.'], 404);

        $res = ['status' => $job['status']];

        if ($job['status'] === 'done') {
            $res['result'] = json_decode($job['result'], true);
        } elseif ($job['status'] === 'error') {
            $res['message'] = $job['error_msg'];
        }

        return $this->response($res);
    }

    // ============================================================
    // HISTORY
    // ============================================================

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

        $row = app()->db->get('originality_history', '*', [
            'id'           => (int) $id,
            'account_uuid' => $uuid,
        ]);

        if (!$row) return $this->response(['error' => 'Không tìm thấy bản ghi.'], 404);

        foreach (['ai', 'plagiarism', 'grammar', 'readability', 'facts', 'content_optimizer', 'metadata'] as $col) {
            if (isset($row[$col]) && is_string($row[$col])) {
                $row[$col] = json_decode($row[$col], true);
            }
        }

        return $this->response(['result' => $row]);
    }

    // ============================================================
    // ORIGINALITY API CALL
    // ============================================================
    
private function callGrammarGPT(string $content): array
{
    $apiKey = $_ENV['GPT_KEY'] ?? getenv('GPT_KEY') ?? '';
    if (empty($apiKey)) {
        return ['success' => false, 'message' => 'GPT_KEY chưa được cấu hình trong .env'];
    }

    $systemPrompt = <<<'PROMPT'
You are a professional grammar checker. Analyze the provided text and return ONLY a valid JSON object with no markdown, no explanation.

The JSON must follow this exact structure:
{
  "matches": [
    {
      "message": "Description of the grammar/spelling issue",
      "shortMessage": "Short label",
      "replacements": [{"value": "suggested fix"}],
      "offset": <character offset in original text, integer>,
      "length": <length of the problematic token, integer>,
      "context": {
        "text": "<~40 chars around the issue>",
        "offset": <offset within context text, integer>,
        "length": <length within context text, integer>
      },
      "sentence": "<full sentence containing the issue>",
      "type": {"typeName": "Other"},
      "rule": {
        "id": "GPT_GRAMMAR_CHECK",
        "description": "Grammar issue detected by GPT",
        "issueType": "grammar",
        "category": {"id": "GRAMMAR", "name": "Grammar"}
      },
      "ignoreForIncompleteSentence": false,
      "contextForSureMatch": 0
    }
  ],
  "warnings": {"incompleteResults": false},
  "language": "<detected language name>",
  "score": <0-100 float, grammar quality score>,
  "grade": "<A/B/C/D/F>"
}

Rules:
- If no issues found, return "matches": []
- score 90-100 = A, 80-89 = B, 70-79 = C, 60-69 = D, below 60 = F
- offset must be the exact character position in the ORIGINAL text (0-indexed)
- Return ONLY the JSON, no markdown fences
PROMPT;

    $payload = json_encode([
        'model'           => 'gpt-4.1-nano',
        'temperature'     => 0,
        'messages'        => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => "Check grammar for the following text:\n\n" . $content],
        ],
        'response_format' => ['type' => 'json_object'],
    ], JSON_UNESCAPED_UNICODE);

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
    ]);

    $raw    = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err    = curl_error($ch);
    curl_close($ch);
    unset($payload);

    error_log(sprintf('[GrammarGPT] status=%d', $status));

    if ($err) {
        return ['success' => false, 'message' => 'cURL error: ' . $err];
    }

    $response = json_decode($raw, true);
    unset($raw);

    if ($status !== 200 || empty($response)) {
        $msg = $response['error']['message'] ?? "HTTP {$status}";
        return ['success' => false, 'message' => $msg];
    }

    $jsonText    = $response['choices'][0]['message']['content'] ?? '';
    unset($response);

    // Clean control characters trước khi parse
    $jsonText = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $jsonText) ?? $jsonText;

    $grammarData = json_decode($jsonText, true);
    unset($jsonText);

    if (json_last_error() !== JSON_ERROR_NONE || !isset($grammarData['matches'])) {
        return ['success' => false, 'message' => 'GPT trả về JSON không hợp lệ'];
    }

    return ['success' => true, 'data' => $grammarData];
}

private function callAPI(
    ?string $url,
    ?string $fileUrl,
    ?string $content,
    string  $title,
    array   $input
): array {
    $apiKey = $_ENV['GPT_KEY'] ?? getenv('GPT_KEY') ?? '';
    if (empty($apiKey)) {
        return ['success' => false, 'message' => 'GPT_KEY chưa được cấu hình trong .env'];
    }

    $checkAi          = (bool) ($input['check_ai']               ?? true);
    $checkPlagiarism  = (bool) ($input['check_plagiarism']        ?? true);
    $checkFacts       = (bool) ($input['check_facts']             ?? true);
    $checkReadability = (bool) ($input['check_readability']       ?? true);
    $checkGrammar     = (bool) ($input['check_grammar']           ?? true);
    $checkSeo         = (bool) ($input['check_contentOptimizer']  ?? false);

    // ── Nếu scan URL/PDF: tải nội dung về trước ─────────────────────
    if (!empty($url) && empty($content)) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; Scanner/1.0)',
        ]);
        $rawContent = curl_exec($ch);
        $curlErr    = curl_error($ch);
        curl_close($ch);

        if ($curlErr || empty($rawContent)) {
            return ['success' => false, 'message' => 'Không thể tải nội dung từ URL: ' . ($curlErr ?: 'empty response')];
        }

        if (str_contains($rawContent, '<html') || str_contains($rawContent, '<body')) {
            $content = function_exists('wp_strip_all_tags') ? wp_strip_all_tags($rawContent) : strip_tags($rawContent);
        } else {
            $content = $rawContent;
        }
        unset($rawContent);
    }

    if (empty($content)) {
        return ['success' => false, 'message' => 'Không có nội dung để phân tích.'];
    }

    $textSlice = mb_substr($content, 0, 12000, 'UTF-8');
    $wordCount = $this->countWords($content);

    // ── Helper: gọi GPT một lần, trả về array đã parse ──────────────
    $callGpt = function (string $systemPrompt, string $userMsg) use ($apiKey): ?array {
        $payload = json_encode([
            'model'           => 'gpt-4.1-mini',
            'temperature'     => 0,
            'max_tokens'      => 2048,
            'messages'        => [
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
        $raw    = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        unset($payload);

        if ($status !== 200 || empty($raw)) return null;

        $resp = json_decode($raw, true);
        unset($raw);
        if (empty($resp['choices'][0]['message']['content'])) return null;

        $text = $resp['choices'][0]['message']['content'];
        unset($resp);

        // Strip fences
        $text = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
        $text = preg_replace('/\s*```$/i', '', $text);

        // Loại bỏ control characters không hợp lệ trong JSON
        // Giữ lại \t(0x09) \n(0x0A) \r(0x0D), xoá hết còn lại trong 0x00-0x1F
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text);

        $parsed = json_decode($text, true);
        unset($text);

        return (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) ? $parsed : null;
    };

    // ── Gọi từng check riêng ─────────────────────────────────────────
    $results = [];

    if ($checkAi) {
        $r = $callGpt(
            'You are an AI detection engine. Return ONLY a JSON object, no markdown. Detect whether the text is AI-written or human-written.',
            'Analyze this text and return JSON with this exact structure:
{"aiModel":"gpt-full","classification":{"AI":1,"Original":0},"confidence":{"AI":0.95,"Original":0.05},"blocks":[{"text":"<sentence>","result":{"fake":0.95,"real":0.05,"status":"success"}}]}
Each block is one sentence. confidence.AI + confidence.Original must equal 1.0. Same for fake+real per block.
Text:
' . $textSlice
        );
        $results['ai'] = $r ?? ['aiModel' => 'gpt-full', 'classification' => ['AI' => 0, 'Original' => 1], 'confidence' => ['AI' => 0.0, 'Original' => 1.0], 'blocks' => []];
    }

    if ($checkPlagiarism) {
        $r = $callGpt(
            'You are a plagiarism detection engine. Return ONLY a JSON object, no markdown.',
            'Analyze this text for plagiarism based on your training data knowledge. Return JSON:
{"score":0,"results":[{"phrase":"<copied phrase>","results":[{"link":"<source url or empty>","title":"<source title>","scores":[{"score":0.9,"sentence":"<matched sentence>"}]}]}]}
score: 0=original, 100=fully plagiarised. If no plagiarism found return {"score":0,"results":[]}.
Text:
' . $textSlice
        );
        $results['plagiarism'] = $r ?? ['score' => 0, 'results' => []];
    }

    if ($checkFacts) {
        $r = $callGpt(
            'You are a fact-checking engine. Return ONLY a JSON object, no markdown.',
            'Identify and verify factual claims in this text. Return JSON:
{"facts":[{"fact":"<claim>","truthfulness":"80%","explanation":"<reason>","links":[]}]}
If no verifiable facts, return {"facts":[]}.
Text:
' . $textSlice
        );
        $results['facts'] = $r['facts'] ?? [];
    }

    if ($checkReadability) {
        $r = $callGpt(
            'You are a readability analysis engine. Return ONLY a JSON object, no markdown.',
            'Compute readability metrics for this text. Return JSON with exactly this structure:
{"text_stats":{"letterCount":0,"sentenceCount":0,"uniqueWordCount":0,"syllableCount":0,"totalSyllables":0,"averageSyllablesPerWord":0.0,"wordsWithThreeSyllables":0,"percentWordsWithThreeSyllables":0.0,"longestSentence":"","paragraphCount":0,"averageSpeakingTime":0.0,"averageReadingTime":0.0,"averageWritingTime":0.0},"readability":{"fleschReadingEase":0.0,"fleschGradeLevel":0.0,"gunningFoxIndex":0.0,"smogIndex":0.0,"powersSumnerKearl":0.0,"forcastGradeLevel":0.0,"colemanLiauIndex":0.0,"automatedReadabilityIndex":0.0,"daleChallReadabilityGrade":0.0,"spacheReadabilityGrade":0.0,"linsearWriteGrade":0.0},"sentences":[{"phrase":"<sentence>","cleanPhrase":"<lowercase>","isVeryHard":false,"isHard":false,"wordsOver13Chars":[],"wordsOver4Syllables":[],"adverbs":[]}]}
Text:
' . $textSlice
        );
        $results['readability'] = $r ?? [];
    }

    if ($checkGrammar) {
        $grammarResult = $this->callGrammarGPT($content);
        $results['grammarSpelling'] = $grammarResult['success']
            ? $grammarResult['data']
            : ['matches' => [], 'warnings' => ['incompleteResults' => true], 'score' => 0, 'grade' => 'F'];
    }

    if ($checkSeo) {
        $optimizerQuery = trim($input['optimizerQuery'] ?? 'SEO');
        $r = $callGpt(
            'You are an SEO content optimizer. Return ONLY a JSON object, no markdown.',
            'Analyze SEO optimization for query: "' . $optimizerQuery . '". Return JSON:
{"keyword_seeds":[{"keyword":"<kw>","min":1,"max":3,"current":0}],"word_count_range":{"min":500,"max":1500,"current":0},"heading_count_range":{"min":5,"max":15,"current":0},"paragraph_count_range":{"min":10,"max":20,"current":0},"suggestions":["<tip1>","<tip2>"],"content_score":0.0}
Text:
' . $textSlice
        );
        $results['contentOptimizer'] = $r ?? [];
    }

    $results['properties'] = [
        'wordCount'        => $wordCount,
        'id'               => 'gpt-scan',
        'title'            => $title,
        'content'          => $content,
        'formattedContent' => $content,
    ];
    $results['credits'] = ['used' => (int) ceil($wordCount / 100)];

    error_log(sprintf('[GPT-callAPI] mode=%s wordCount=%d checks=ai:%d,plag:%d,facts:%d,read:%d,gram:%d,seo:%d',
        !empty($url) ? 'url' : 'text', $wordCount,
        (int)$checkAi, (int)$checkPlagiarism, (int)$checkFacts,
        (int)$checkReadability, (int)$checkGrammar, (int)$checkSeo
    ));

    return ['success' => true, 'data' => ['results' => $results]];
}
    // private function callAPI(
    //     ?string $url,
    //     ?string $fileUrl,
    //     ?string $content,
    //     string  $title,
    //     array   $input
    // ): array {
    //     $apiKey = $_ENV['ORIGINALITY_API_KEY'] ?? getenv('ORIGINALITY_API_KEY') ?? '';

    //     if (empty($apiKey)) {
    //         return ['success' => false, 'message' => 'ORIGINALITY_API_KEY chưa được cấu hình trong .env'];
    //     }

    //     $checkSeo = (bool) ($input['check_contentOptimizer'] ?? false);

    //     $body = [
    //         'title'                  => (string) $title,
    //         'check_ai'               => (bool) ($input['check_ai']               ?? true),
    //         'check_plagiarism'       => (bool) ($input['check_plagiarism']       ?? true),
    //         'check_facts'            => (bool) ($input['check_facts']            ?? true),
    //         'check_readability'      => (bool) ($input['check_readability']      ?? true),
    //         'check_grammar'          => (bool) ($input['check_grammar']          ?? true),
    //         'check_contentOptimizer' => $checkSeo,
    //         'storeScan'              => true,
    //         'aiModelVersion'         => $input['aiModelVersion'] ?? 'multilang',
    //     ];

    //     if (!empty($url)) {
    //         $body['url'] = $url;
    //     } else {
    //         $body['content'] = $content;
    //     }

    //     if ($checkSeo) {
    //         $query = trim($input['optimizerQuery'] ?? '');
    //         if (empty($query)) $query = 'SEO';

    //         $body['optimizerQuery']            = $query;
    //         $body['optimizerCountry']          = (string) ($input['optimizerCountry']          ?? 'United States');
    //         $body['optimizerDevice']           = (string) ($input['optimizerDevice']           ?? 'Desktop');
    //         $body['optimizerPublishingDomain'] = (string) ($input['optimizerPublishingDomain'] ?? '');
    //     }

    //     $excluded = $input['excludedUrls'] ?? $input['excludedUrl'] ?? null;
    //     if (!empty($excluded)) {
    //         $body['excludedUrls'] = (array) $excluded;
    //     }

    //     $payload = json_encode($body, JSON_UNESCAPED_UNICODE);
    //     unset($body);

    //     $ch = curl_init('https://api.originality.ai/api/v3/scan');
    //     curl_setopt_array($ch, [
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_POST           => true,
    //         CURLOPT_POSTFIELDS     => $payload,
    //         CURLOPT_TIMEOUT        => 150,
    //         CURLOPT_HTTPHEADER     => [
    //             'Content-Type: application/json',
    //             'Accept: application/json',
    //             'X-OAI-API-KEY: ' . $apiKey,
    //         ],
    //         CURLOPT_BUFFERSIZE     => 16384,
    //     ]);

    //     $raw    = curl_exec($ch);
    //     $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //     $err    = curl_error($ch);
    //     curl_close($ch);

    //     unset($payload);
    //     error_log(sprintf(
    //         '[OriginalityAPI-v3] mode=%s status=%d',
    //         !empty($url) ? 'url' : 'text',
    //         $status
    //     ));

    //     if ($err) {
    //         return [
    //             'success' => false,
    //             'message' => 'cURL error: ' . $err,
    //             'debug'   => ['curl_error' => $err, 'http_status' => $status],
    //         ];
    //     }

    //     $data = json_decode($raw, true);
    //     // FIX: Giải phóng raw response ngay sau decode
    //     unset($raw);

    //     if ($status !== 200 || empty($data)) {
    //         $msg = $data['message'] ?? $data['error'] ?? "HTTP {$status}";
    //         return [
    //             'success' => false,
    //             'message' => $msg,
    //             'debug'   => ['http_status' => $status],
    //         ];
    //     }

    //     return ['success' => true, 'data' => $data];
    // }

    private function unwrapV3(array $data): array
    {
        $r    = $data['results'] ?? $data;
        $data = null; // FIX: Giải phóng $data gốc trước khi build return

        $result = [
            'ai'               => $r['ai']               ?? [],
            'plagiarism'       => $r['plagiarism']       ?? [],
            'grammarSpelling'  => $r['grammarSpelling']  ?? [],
            'readability'      => $r['readability']      ?? [],
            'facts'            => array_values((array) ($r['facts'] ?? [])),
            'contentOptimizer' => $r['contentOptimizer'] ?? [],
            'properties'       => $r['properties']       ?? [],
            'credits'          => $r['credits']          ?? [],
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
            `id`                  INT UNSIGNED        AUTO_INCREMENT PRIMARY KEY,
            `account_uuid`        VARCHAR(100)        NOT NULL,
            `type`                VARCHAR(50)         NOT NULL DEFAULT 'text'
                                  COMMENT 'text | url | pdf | batch',
            `title`               VARCHAR(255)        DEFAULT NULL,
            `scan_url`            VARCHAR(2048)       DEFAULT NULL,
            `file_url`            VARCHAR(2048)       DEFAULT NULL,
            `ai`                  JSON                DEFAULT NULL,
            `plagiarism`          JSON                DEFAULT NULL,
            `grammar`             JSON                DEFAULT NULL,
            `readability`         JSON                DEFAULT NULL,
            `facts`               JSON                DEFAULT NULL,
            `content_optimizer`   JSON                DEFAULT NULL,
            `metadata`            JSON                DEFAULT NULL,
            `word_count`          INT                 DEFAULT NULL,
            `points_used`         INT                 DEFAULT NULL,
            `ai_score`            FLOAT               DEFAULT NULL,
            `ai_model`            VARCHAR(50)         DEFAULT NULL,
            `plag_score`          FLOAT               DEFAULT NULL,
            `grammar_errors`      INT                 DEFAULT NULL,
            `readability_score`   FLOAT               DEFAULT NULL,
            `readability_grade`   FLOAT               DEFAULT NULL,
            `facts_errors`        INT                 DEFAULT NULL,
            `facts_total`         INT                 DEFAULT NULL,
            `facts_true`          INT                 DEFAULT NULL,
            `seo_score`           FLOAT               DEFAULT NULL,
            `status`              VARCHAR(20)         NOT NULL DEFAULT 'done',
            `created_at`          TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_account`   (`account_uuid`),
            INDEX `idx_created`   (`created_at`),
            INDEX `idx_type`      (`type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        static $migrated = false;
        if ($migrated) return;
        $migrated = true;

        $existing = array_column(
            app()->db->query("SHOW COLUMNS FROM `originality_history`")->fetchAll(\PDO::FETCH_ASSOC),
            'Field'
        );

        $migrations = [
            'scan_url'          => "ALTER TABLE `originality_history` ADD COLUMN `scan_url` VARCHAR(2048) DEFAULT NULL AFTER `title`",
            'file_url'          => "ALTER TABLE `originality_history` ADD COLUMN `file_url` VARCHAR(2048) DEFAULT NULL AFTER `scan_url`",
            'file_blob'         => "ALTER TABLE `originality_history` ADD COLUMN `file_blob` LONGBLOB DEFAULT NULL AFTER `file_url`",
            'grammar'           => "ALTER TABLE `originality_history` ADD COLUMN `grammar` JSON DEFAULT NULL AFTER `plagiarism`",
            'readability'       => "ALTER TABLE `originality_history` ADD COLUMN `readability` JSON DEFAULT NULL AFTER `grammar`",
            'facts'             => "ALTER TABLE `originality_history` ADD COLUMN `facts` JSON DEFAULT NULL AFTER `readability`",
            'content_optimizer' => "ALTER TABLE `originality_history` ADD COLUMN `content_optimizer` JSON DEFAULT NULL AFTER `facts`",
            'metadata'          => "ALTER TABLE `originality_history` ADD COLUMN `metadata` JSON DEFAULT NULL AFTER `content_optimizer`",
            'word_count'        => "ALTER TABLE `originality_history` ADD COLUMN `word_count` INT DEFAULT NULL AFTER `metadata`",
            'points_used'       => "ALTER TABLE `originality_history` ADD COLUMN `points_used` INT DEFAULT NULL AFTER `word_count`",
            'ai_score'          => "ALTER TABLE `originality_history` ADD COLUMN `ai_score` FLOAT DEFAULT NULL AFTER `points_used`",
            'ai_model'          => "ALTER TABLE `originality_history` ADD COLUMN `ai_model` VARCHAR(50) DEFAULT NULL AFTER `ai_score`",
            'plag_score'        => "ALTER TABLE `originality_history` ADD COLUMN `plag_score` FLOAT DEFAULT NULL AFTER `ai_model`",
            'grammar_errors'    => "ALTER TABLE `originality_history` ADD COLUMN `grammar_errors` INT DEFAULT NULL AFTER `plag_score`",
            'readability_score' => "ALTER TABLE `originality_history` ADD COLUMN `readability_score` FLOAT DEFAULT NULL AFTER `grammar_errors`",
            'readability_grade' => "ALTER TABLE `originality_history` ADD COLUMN `readability_grade` FLOAT DEFAULT NULL AFTER `readability_score`",
            'facts_errors'      => "ALTER TABLE `originality_history` ADD COLUMN `facts_errors` INT DEFAULT NULL AFTER `readability_grade`",
            'facts_total'       => "ALTER TABLE `originality_history` ADD COLUMN `facts_total` INT DEFAULT NULL AFTER `facts_errors`",
            'facts_true'        => "ALTER TABLE `originality_history` ADD COLUMN `facts_true` INT DEFAULT NULL AFTER `facts_total`",
            'seo_score'         => "ALTER TABLE `originality_history` ADD COLUMN `seo_score` FLOAT DEFAULT NULL AFTER `facts_true`",
        ];

        foreach ($migrations as $col => $sql) {
            if (!in_array($col, $existing)) {
                app()->db->query($sql);
            }
        }
    }

    private function ensureJobTable(): void
    {
        app()->db->query("CREATE TABLE IF NOT EXISTS `originality_jobs` (
            `id`                  INT UNSIGNED        AUTO_INCREMENT PRIMARY KEY,
            `job_id`              VARCHAR(50)         NOT NULL,
            `account_uuid`        VARCHAR(100)        NOT NULL,
            `status`              VARCHAR(20)         NOT NULL DEFAULT 'pending',
            `type`                VARCHAR(20)         NOT NULL DEFAULT 'pdf',
            `title`               VARCHAR(255)        DEFAULT NULL,
            `file_url`            VARCHAR(2048)       DEFAULT NULL,
            `scan_options`        JSON                DEFAULT NULL,
            `result`              JSON                DEFAULT NULL,
            `error_msg`           TEXT                DEFAULT NULL,
            `created_at`          TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`          TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_job`   (`job_id`),
            INDEX `idx_account`   (`account_uuid`),
            INDEX `idx_status`    (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    private function saveHistory(
        string $title,
        ?string $content,
        ?string $scanUrl,
        ?string $fileUrl,
        string $type,
        array $results
    ): void {
        $uuid = $this->getUuid();
        if (!$uuid) return;

        $facts = $results['facts'] ?? [];
        $factsErrors = 0;
        $factsTrue = 0;
        foreach ($facts as $f) {
            $cls = strtolower($f['classification'] ?? '');
            if (in_array($cls, ['false', 'unverified'])) $factsErrors++;
            if ($cls === 'true') $factsTrue++;
        }

        $fileBlob = null;
        if (!empty($fileUrl)) {
            $fileName = basename(parse_url($fileUrl, PHP_URL_PATH));
            $basePath = rtrim(app()->basePath(), '/');
            $paths = [
                $basePath . '/public/uploads/scans/' . $fileName,
                $basePath . '/public/uploads/' . $fileName
            ];
            foreach ($paths as $p) {
                if (is_file($p)) {
                    $fileBlob = file_get_contents($p);
                    break;
                }
            }
        }

        $insertData = [
            'account_uuid'        => $uuid,
            'type'                => $type,
            'title'               => $title,
            'scan_url'            => $scanUrl,
            'file_url'            => $fileUrl,
            'file_blob'           => $fileBlob,
            'ai'                  => json_encode($results['ai'] ?? null, JSON_UNESCAPED_UNICODE),
            'plagiarism'          => json_encode($results['plagiarism'] ?? null, JSON_UNESCAPED_UNICODE),
            'grammar'             => json_encode($results['grammarSpelling'] ?? null, JSON_UNESCAPED_UNICODE),
            'readability'         => json_encode($results['readability'] ?? null, JSON_UNESCAPED_UNICODE),
            'facts'               => json_encode($facts, JSON_UNESCAPED_UNICODE),
            'content_optimizer'   => json_encode($results['contentOptimizer'] ?? null, JSON_UNESCAPED_UNICODE),
            'metadata'            => json_encode([
                'properties' => $results['properties'] ?? null,
                'credits'    => $results['credits'] ?? null,
            ], JSON_UNESCAPED_UNICODE),
            'word_count'          => $results['properties']['wordCount'] ?? 0,
            'points_used'         => $results['pointsUsed'] ?? 0,
            'ai_score'            => isset($results['ai']['confidence']['AI']) ? round($results['ai']['confidence']['AI'] * 100, 2) : 0,
            'ai_model'            => $results['ai']['aiModel'] ?? null,
            'plag_score'          => $results['plagiarism']['score'] ?? 0,
            'grammar_errors'      => count($results['grammarSpelling']['matches'] ?? []),
            'readability_score'   => $results['readability']['readability']['fleschReadingEase'] ?? 0,
            'readability_grade'   => $results['readability']['readability']['fleschGradeLevel'] ?? 0,
            'facts_errors'        => $factsErrors,
            'facts_total'         => count($facts),
            'facts_true'          => $factsTrue,
            'seo_score'           => $results['contentOptimizer']['score'] ?? 0,
            'status'              => 'done',
        ];

        try {
            app()->db->insert('originality_history', $insertData);
        } catch (\PDOException $e) {
            if (str_contains(strtolower($e->getMessage()), 'max_allowed_packet')) {
                $insertData['file_blob'] = null; // Tránh lỗi sập SQL nếu file lớn hơn cấu hình MySQL
                app()->db->insert('originality_history', $insertData);
            } else {
                throw $e;
            }
        }
    }

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

    private function verifyKey(): void
    {
    }

    private function dispatchWorkerViaCurl(string $jobId): void
    {
        $secret = $_ENV['INTERNAL_SECRET'] ?? '';
        $url = 'https://ai.vmied.com/internal/originality/process-job';
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(['job_id' => $jobId, 'secret' => $secret]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 1,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    private function response(array $data, int $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}