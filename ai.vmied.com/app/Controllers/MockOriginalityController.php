<?php
namespace App\Controllers;

class MockOriginalityController {

    const COST_PER_100_WORDS = 500;

    private function calcCost(string $content): int {
        $wordCount = str_word_count($content);
        return (int) ceil($wordCount / 100) * self::COST_PER_100_WORDS;
    }

    private function getUuid(): ?string {
        return app()->request->user->uuid
            ?? (app()->session->get('account')['uuid'] ?? null);
    }

    private function getAccountId(string $uuid): ?int {
        $id = app()->db->get('accounts', 'id', ['uuid' => $uuid]);
        return $id ? (int) $id : null;
    }

    private function getPoints(string $uuid): int {
        $row = app()->db->get('points', 'points', ['account' => $uuid]);
        return (int) ($row ?? 0);
    }

    // ============================================================
    // ENDPOINTS
    // ============================================================

    public function getBalance() {
        $this->verifyKey();
        return $this->response([
            "credits"             => 9800,
            "subscriptionCredits" => 2000,
        ]);
    }

    public function scan() {
        $this->verifyKey();

        $input   = $this->getInput();
        $content = trim($input['content'] ?? '');
        $title   = $input['title'] ?? 'Mock Scan';
        $user = app()->request->user;

        // ── 1. Kiểm tra đăng nhập ────────────────────────────
        $uuid = $this->getUuid();
        if (!$uuid) {
            return $this->response([
                'status'  => 'error',
                'message' => 'Vui lòng đăng nhập để sử dụng tính năng này.',
            ], 401);
        }

        // ── 2. Kiểm tra nội dung ─────────────────────────────
        if (empty($content)) {
            return $this->response([
                'status'  => 'error',
                'message' => 'Nội dung không được để trống.',
            ], 422);
        }

        // ── 3. Tính phí & kiểm tra số dư ─────────────────────
        $cost    = $this->calcCost($content);
        $balance = $this->getPoints($uuid);

        if ($balance < $cost) {
            $wordCount = str_word_count($content);
            return $this->response([
                'status'   => 'error',
                'code'     => 'INSUFFICIENT_POINTS',
                'message'  => "Số dư không đủ. Cần {$cost}đ để kiểm tra {$wordCount} từ, bạn chỉ còn " . number_format($balance) . "đ.",
                'required' => $cost,
                'balance'  => $balance,
            ], 402);
        }

        // ── 4. SCAN ───────────────────────────────────────────
        // == MOCK (đang dùng) ==
        $sim        = $this->analyzeSimulation($title);
        $ai         = $this->mockAI($sim['ai_score'], $content);
        $plagiarism = $this->mockPlagiarism($sim['plag_score']);
        $wordCount  = str_word_count($content);

        $response = [
            'results' => [
                'properties' => [
                    'privateID'        => mt_rand(100000, 999999),
                    'id'               => 'mock_scan_' . uniqid(),
                    'title'            => $title,
                    'content'          => $content,
                    'formattedContent' => $content,
                    'wordCount'        => $wordCount,
                    'aiModelVersion'   => $input['aiModelVersion'] ?? 'turbo',
                ],
                'credits' => [
                    'used'                 => (int) ceil($wordCount / 100),
                    'base_credits'         => $balance - $cost,
                    'subscription_credits' => 0,
                ],
                'ai'               => $ai,
                'plagiarism'       => $plagiarism,
                'facts'            => $this->mockFacts(),
                'readability'      => $this->mockReadability($wordCount),
                'grammarSpelling'  => $this->mockGrammar($content),
                'contentOptimizer' => $this->mockOptimizer($wordCount),
            ],
        ];
        // == END MOCK ==

        // == API THẬT (mở comment khi cần) ==
        // $apiResult = $this->callRealAPI($content, $title, $input);
        // if (!$apiResult['success']) {
        //     return $this->response([
        //         'status'  => 'error',
        //         'message' => 'Lỗi từ Originality.ai: ' . $apiResult['message'],
        //     ], 502);
        // }
        // $response   = $apiResult['data'];
        // $ai         = $response['results']['ai']         ?? [];
        // $plagiarism = $response['results']['plagiarism'] ?? [];
        // == END API THẬT ==

        // ── 5. Gắn thông tin points ───────────────────────────
        $response['results']['pointsUsed']      = $cost;
        $response['results']['pointsRemaining'] = $balance - $cost;

        // ── 6. Trừ points dùng upsertPoints ──────────────────
        $accountId = $this->getAccountId($uuid);
        
        if ($accountId) {
            upsertPoints($accountId, $cost, 'SCAN-' . uniqid(), 'use');
        }

        // ── 7. Lưu lịch sử ───────────────────────────────────
        $this->ensureHistoryTable();
        $this->saveScanHistory($title, $content, $ai, $plagiarism, $response);

        return $this->response($response);
    }

    public function history() {
        $this->verifyKey();
        $this->ensureHistoryTable();

        $uuid = $this->getUuid();
        if (!$uuid) {
            return $this->response(['error' => 'Vui lòng đăng nhập để xem lịch sử.'], 401);
        }

        $rows = app()->db->select('originality_history', '*', [
            'account_uuid' => $uuid,
            'ORDER'        => ['created_at' => 'DESC'],
            'LIMIT'        => 100,
        ]);

        return $this->response(['history' => $rows]);
    }

    public function scanBatch() {
        $this->verifyKey();

        $input = $this->getInput();
        $batch = $input['batch'] ?? [];

        // ── 1. Kiểm tra đăng nhập ────────────────────────────
        $uuid = $this->getUuid();
        if (!$uuid) {
            return $this->response(['status' => 'error', 'message' => 'Vui lòng đăng nhập.'], 401);
        }

        if (empty($batch) || !is_array($batch)) {
            return $this->response(['status' => 'error', 'message' => 'Batch không được để trống.'], 422);
        }

        // ── 2. Tính tổng phí ──────────────────────────────────
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

        // ── 3. Xử lý từng item ────────────────────────────────
        $results = [];
        foreach ($batch as $item) {
            $title     = $item['title']   ?? 'Batch Scan';
            $content   = $item['content'] ?? '';
            $wordCount = str_word_count($content);
            $sim       = $this->analyzeSimulation($title);

            $results[] = [
                'title'     => $title,
                'content'   => $content,
                'wordCount' => $wordCount,
                'credits'   => [
                    'used'                 => (int) ceil($wordCount / 100),
                    'base_credits'         => 0,
                    'subscription_credits' => 0,
                ],
                'ai'              => $this->mockAI($sim['ai_score'], $content),
                'plagiarism'      => $this->mockPlagiarism($sim['plag_score']),
                'readability'     => $this->mockReadability($wordCount),
                'grammarSpelling' => $this->mockGrammar($content),
                'facts'           => $this->mockFacts(),
            ];
        }

        // ── 4. Trừ points dùng upsertPoints ───────────────────
        $accountId = $this->getAccountId($uuid);
        if ($accountId) {
            upsertPoints($accountId, $totalCost, 'BATCH-' . uniqid(), 'use');
        }

        // ── 5. Lưu lịch sử từng item ──────────────────────────
        $this->ensureHistoryTable();
        foreach ($batch as $idx => $item) {
            $r = $results[$idx];
            $this->saveScanHistory(
                $item['title'] ?? 'Batch Scan',
                $item['content'] ?? '',
                $r['ai'],
                $r['plagiarism'],
                ['results' => $r],
                'batch'
            );
        }

        return $this->response([
            'results'         => $results,
            'pointsUsed'      => $totalCost,
            'pointsRemaining' => $balance - $totalCost,
        ]);
    }

    public function scanUrl() {
        $this->verifyKey();

        $input = $this->getInput();
        $url   = trim($input['url'] ?? '');

        // ── 1. Kiểm tra đăng nhập ────────────────────────────
        $uuid = $this->getUuid();
        if (!$uuid) {
            return $this->response(['status' => 'error', 'message' => 'Vui lòng đăng nhập.'], 401);
        }

        if (empty($url)) {
            return $this->response(['status' => 'error', 'message' => 'URL không được để trống.'], 422);
        }

        // ── 2. Mock nội dung từ URL ───────────────────────────
        $title     = 'Scan URL: ' . (parse_url($url, PHP_URL_HOST) ?? 'Unknown');
        $content   = "Đây là nội dung giả từ $url. " . str_repeat('Nội dung mẫu. ', 15);
        $wordCount = str_word_count($content);

        // ── 3. Tính phí & kiểm tra số dư ─────────────────────
        $cost    = $this->calcCost($content);
        $balance = $this->getPoints($uuid);

        if ($balance < $cost) {
            return $this->response([
                'status'   => 'error',
                'code'     => 'INSUFFICIENT_POINTS',
                'message'  => "Số dư không đủ. Cần {$cost}đ, bạn còn " . number_format($balance) . "đ.",
                'required' => $cost,
                'balance'  => $balance,
            ], 402);
        }

        // ── 4. SCAN ───────────────────────────────────────────
        $sim        = $this->analyzeSimulation($title);
        $ai         = $this->mockAI($sim['ai_score'], $content);
        $plagiarism = $this->mockPlagiarism($sim['plag_score']);

        $response = [
            'results' => [
                'properties' => [
                    'privateID'        => mt_rand(100000, 999999),
                    'id'               => 'mock_scan_' . uniqid(),
                    'title'            => $title,
                    'content'          => $content,
                    'formattedContent' => $content,
                    'wordCount'        => $wordCount,
                    'aiModelVersion'   => $input['aiModelVersion'] ?? 'turbo',
                ],
                'credits' => [
                    'used'                 => (int) ceil($wordCount / 100),
                    'base_credits'         => $balance - $cost,
                    'subscription_credits' => 0,
                ],
                'ai'              => $ai,
                'plagiarism'      => $plagiarism,
                'facts'           => $this->mockFacts(),
                'readability'     => $this->mockReadability($wordCount),
                'grammarSpelling' => $this->mockGrammar($content),
                'pointsUsed'      => $cost,
                'pointsRemaining' => $balance - $cost,
            ],
        ];

        // ── 5. Trừ points dùng upsertPoints ──────────────────
        $accountId = $this->getAccountId($uuid);
        if ($accountId) {
            upsertPoints($accountId, $cost, 'URL-' . uniqid(), 'use');
        }

        // ── 6. Lưu lịch sử ───────────────────────────────────
        $this->ensureHistoryTable();
        $this->saveScanHistory($title, $content, $ai, $plagiarism, $response, 'url');

        return $this->response($response);
    }

    public function getScanById($id) {
        $this->verifyKey();
        return $this->response(['result' => ['id' => $id, 'message' => 'Mock scan by id']]);
    }

    // ============================================================
    // DB HELPERS
    // ============================================================

    private function ensureHistoryTable(): void {
        app()->db->query("CREATE TABLE IF NOT EXISTS originality_history (
            id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            account_uuid   VARCHAR(100)  NOT NULL,
            type           VARCHAR(50)   NOT NULL DEFAULT 'text',
            title          VARCHAR(255)  NULL,
            content        LONGTEXT      NULL,
            ai             JSON          NULL,
            plagiarism     JSON          NULL,
            metadata       JSON          NULL,
            word_count     INT           NULL,
            points_used    INT           NULL,
            ai_score       FLOAT         NULL COMMENT '% AI (0-100)',
            plag_score     FLOAT         NULL COMMENT '% Plagiarism (0-100)',
            grammar_errors INT           NULL COMMENT 'So loi ngu phap',
            status         VARCHAR(20)   NOT NULL DEFAULT 'done',
            created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $alterCols = [
            'points_used'    => "ALTER TABLE originality_history ADD COLUMN points_used INT NULL AFTER word_count",
            'ai_score'       => "ALTER TABLE originality_history ADD COLUMN ai_score FLOAT NULL AFTER points_used",
            'plag_score'     => "ALTER TABLE originality_history ADD COLUMN plag_score FLOAT NULL AFTER ai_score",
            'grammar_errors' => "ALTER TABLE originality_history ADD COLUMN grammar_errors INT NULL AFTER plag_score",
            'status'         => "ALTER TABLE originality_history ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'done' AFTER grammar_errors",
        ];
        foreach ($alterCols as $col => $sql) {
            $exists = app()->db->query("SHOW COLUMNS FROM originality_history LIKE '$col'")->fetchAll();
            if (empty($exists)) app()->db->query($sql);
        }
    }

    private function saveScanHistory(
        string $title,
        string $content,
        ?array $ai,
        ?array $plagiarism,
        ?array $response,
        string $type = 'text'
    ): void {
        $uuid = $this->getUuid();
        if (!$uuid) return;

        $wordCount     = str_word_count($content);
        $pointsUsed    = $this->calcCost($content);
        $aiScore       = isset($ai['confidence']['AI'])
                            ? round($ai['confidence']['AI'] * 100, 1)
                            : null;
        $plagScore     = isset($plagiarism['score'])
                            ? (float) $plagiarism['score']
                            : null;
        $grammarErrors = isset($response['results']['grammarSpelling']['matches'])
                            ? count($response['results']['grammarSpelling']['matches'])
                            : null;

        app()->db->insert('originality_history', [
            'account_uuid'   => $uuid,
            'type'           => $type,
            'title'          => $title,
            'content'        => $content,
            'ai'             => json_encode($ai),
            'plagiarism'     => json_encode($plagiarism),
            'metadata'       => json_encode([
                'scanAt' => date('Y-m-d H:i:s'),
                'result' => $response,
            ]),
            'word_count'     => $wordCount,
            'points_used'    => $pointsUsed,
            'ai_score'       => $aiScore,
            'plag_score'     => $plagScore,
            'grammar_errors' => $grammarErrors,
            'status'         => 'done',
        ]);
    }

    // ============================================================
    // DATA GENERATORS
    // ============================================================

    private function mockAI(float $aiScore, string $content): array {
        $origScore = 1 - $aiScore;
        $parts     = preg_split('/(?<=[.?!])(\s+)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $blocks    = [];

        foreach ($parts as $part) {
            if (trim($part) === '') {
                if (!empty($blocks)) {
                    $blocks[count($blocks) - 1]['text'] .= $part;
                }
            } else {
                $isHighFake = ($aiScore > 0.5);
                $fake       = $isHighFake ? (mt_rand(80, 100) / 100) : (mt_rand(0, 20) / 100);
                $blocks[]   = [
                    'text'   => $part,
                    'result' => [
                        'fake'   => $fake,
                        'real'   => 1 - $fake,
                        'status' => 'success',
                    ],
                ];
            }
        }

        return [
            'aiModel'        => 'turbo',
            'classification' => [
                'AI'       => ($aiScore > 0.5) ? 1 : 0,
                'Original' => ($origScore > 0.5) ? 1 : 0,
            ],
            'confidence' => [
                'AI'       => $aiScore,
                'Original' => $origScore,
            ],
            'blocks' => $blocks,
        ];
    }

    private function mockPlagiarism(float $score): array {
        if ($score < 0.1) return ['score' => 0, 'results' => []];
        return [
            'score'   => round($score * 100, 1),
            'results' => [[
                'phrase'  => 'Đây là đoạn văn bị phát hiện trùng lặp trên Internet...',
                'results' => [[
                    'link'   => 'https://wikipedia.org/wiki/AI',
                    'title'  => 'Nguồn Wikipedia (Trùng khớp 100%)',
                    'scores' => [['score' => 1, 'sentence' => 'Original sentence on wiki.']],
                ]],
            ]],
        ];
    }

    private function mockReadability(int $wordCount = 350): array {
        $sentenceCount      = max(1, (int) ($wordCount / 20));
        $avgReadingTime     = round($wordCount / 238, 1); // ~238 wpm
        $avgSpeakingTime    = round($wordCount / 125, 1); // ~125 wpm
        $avgWritingTime     = round($wordCount / 25, 1);  // ~25 wpm
        $letterCount        = $wordCount * 5;
        $uniqueWordCount    = (int) ($wordCount * 0.55);
        $syllableCount      = (int) ($wordCount * 1.5);

        return [
            'text_stats' => [
                'letterCount'                    => $letterCount,
                'sentenceCount'                  => $sentenceCount,
                'wordCount'                      => $wordCount,
                'uniqueWordCount'                => $uniqueWordCount,
                'syllableCount'                  => $syllableCount,
                'totalSyllables'                 => $syllableCount + mt_rand(0, 30),
                'averageSyllablesPerWord'        => 1.53,
                'wordsWithThreeSyllables'        => (int) ($wordCount * 0.14),
                'percentWordsWithThreeSyllables' => 13.66,
                'longestSentence'                => 'This is a sample of the longest sentence found in the content.',
                'paragraphCount'                 => max(1, (int) ($wordCount / 80)),
                'averageSpeakingTime'            => $avgSpeakingTime,
                'averageReadingTime'             => $avgReadingTime,
                'averageWritingTime'             => $avgWritingTime,
            ],
            'readability' => [
                'fleschReadingEase'         => 58.4,
                'fleschGradeLevel'          => 9.8,
                'gunningFoxIndex'           => 12.8,
                'smogIndex'                 => 12,
                'powersSumnerKearl'         => 5.7,
                'forcastGradeLevel'         => 9.9,
                'colemanLiauIndex'          => 10.4,
                'automatedReadabilityIndex' => 8.9,
                'daleChallReadabilityGrade' => 6.1,
                'spacheReadabilityGrade'    => 5.0,
                'linsearWriteGrade'         => -0.3,
                'grade'                     => 'B',
            ],
            'sentences' => [],
        ];
    }

    private function mockGrammar(string $content = ''): array {
        // Tạo 1 lỗi mẫu có đầy đủ fields như API thật
        $sampleSentence = 'This is a sample sentence with a mitsake.';
        $sampleText     = substr($content, 0, 50) ?: $sampleSentence;

        return [
            'matches' => [[
                'message'      => 'Possible spelling mistake found.',
                'shortMessage' => 'Spelling mistake',
                'replacements' => [['value' => 'mistake']],
                'offset'       => 38,
                'length'       => 8,
                'context'      => [
                    'text'   => '...' . $sampleText . '...',
                    'offset' => 43,
                    'length' => 8,
                ],
                'sentence'                   => $sampleSentence,
                'type'                       => ['typeName' => 'UnknownWord'],
                'rule'                       => [
                    'id'          => 'MORFOLOGIK_RULE_EN_US',
                    'description' => 'Possible spelling mistake',
                    'issueType'   => 'misspelling',
                    'category'    => [
                        'id'   => 'TYPOS',
                        'name' => 'Possible Typo',
                    ],
                ],
                'ignoreForIncompleteSentence' => false,
                'contextForSureMatch'         => 0,
            ]],
            'warnings' => ['incompleteResults' => false],
            'score'    => 3.069,
            'grade'    => 'A+',
        ];
    }

    private function mockFacts(): array {
        return [[
            'fact'         => 'Mặt trời mọc ở hướng Tây.',
            'truthfulness' => '0%',
            'explanation'  => 'Sai sự thật. Mặt trời mọc ở hướng Đông.',
            'links'        => [],  // API trả array index số, để rỗng cho mock
        ]];
    }

    private function mockOptimizer(int $wordCount = 350): array {
        return [
            'keyword_seeds' => [
                ['keyword' => 'viết bài chuẩn seo', 'min' => 5,  'max' => 10, 'current' => 3],
                ['keyword' => 'kiểm tra đạo văn',   'min' => 10, 'max' => 20, 'current' => 15],
                ['keyword' => 'nội dung',            'min' => 8,  'max' => 15, 'current' => 6],
            ],
            'heading_count_range'   => ['min' => 5,           'max' => 10,          'current' => 0],
            'paragraph_count_range' => ['min' => 8,           'max' => 15,          'current' => 0],
            'word_count_range'      => ['min' => 500,         'max' => 900,         'current' => $wordCount],
            'suggestions'           => [
                'Thêm tiêu đề phụ (H2, H3) để cải thiện khả năng đọc.',
                'Tăng mật độ từ khóa chính trong nội dung.',
                'Chia nội dung thành nhiều đoạn ngắn hơn.',
            ],
            'competitors'   => [],
            'content_score' => 45.5,
        ];
    }

    // ============================================================
    // MISC HELPERS
    // ============================================================

    private function callRealAPI(string $content, string $title, array $input): array {
        $apiKey         = $_ENV['ORIGINALITY_API_KEY'] ?? getenv('ORIGINALITY_API_KEY');
        $checkOptimizer = (bool) ($input['check_contentOptimizer'] ?? false);

        $body = [
            'content'                   => $content,
            'title'                     => $title,
            'check_ai'                  => (bool) ($input['check_ai']          ?? true),
            'check_plagiarism'          => (bool) ($input['check_plagiarism']  ?? true),
            'check_facts'               => (bool) ($input['check_facts']       ?? true),
            'check_readability'         => (bool) ($input['check_readability'] ?? true),
            'check_grammar'             => (bool) ($input['check_grammar']     ?? true),
            'check_contentOptimizer'    => $checkOptimizer,
            'optimizerQuery'            => $input['optimizerQuery']            ?? '',
            'optimizerCountry'          => $input['optimizerCountry']          ?? 'United States',
            'optimizerDevice'           => $input['optimizerDevice']           ?? 'desktop',
            'optimizerPublishingDomain' => $input['optimizerPublishingDomain'] ?? '',
            'storeScan'                 => true,
            'excludedUrls'              => $input['excludedUrls'] ?? [],
            'aiModelVersion'            => $input['aiModelVersion'] ?? 'turbo',
        ];

        $ch = curl_init('https://api.originality.ai/api/v1/scan');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($body),
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'X-OAI-API-KEY: ' . $apiKey,
            ],
        ]);

        $raw    = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err    = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['success' => false, 'message' => 'cURL error: ' . $err];
        }

        $data = json_decode($raw, true);

        if ($status !== 200 || empty($data)) {
            $msg = $data['message'] ?? $data['error'] ?? "HTTP $status";
            return ['success' => false, 'message' => $msg];
        }

        return ['success' => true, 'data' => $data];
    }

    private function analyzeSimulation(string $title): array {
        $t    = strtolower($title);
        $ai   = 0.02;
        $plag = 0.0;
        if (strpos($t, 'gpt') !== false || strpos($t, 'ai') !== false) $ai   = 0.99;
        if (strpos($t, 'copy') !== false)                               $plag = 0.85;
        return ['ai_score' => $ai, 'plag_score' => $plag];
    }

    private function verifyKey(): void {
        // Thêm xác thực API key tại đây nếu cần
    }

    private function getInput(): array {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    private function response(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}