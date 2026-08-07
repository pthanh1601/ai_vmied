<?php
ini_set('memory_limit', '512M');
set_time_limit(300);

// ── Unpack data ────────────────────────────────────────────────────────
$result  = $result  ?? $data ?? [];
$summary = $summary ?? [];
$history = $history ?? [];
$user    = $user    ?? null;

$ai    = $result['ai']              ?? [];
$plag  = $result['plagiarism']      ?? [];
$gram  = $result['grammarSpelling'] ?? [];
$facts = $result['facts']           ?? [];
$read  = $result['readability']     ?? [];
$seo   = $result['contentOptimizer'] ?? [];

// ── Bộ dịch Đa ngôn ngữ riêng cho PDF ──────────────────────────────────
$pdfLang = $history['lang'] ?? 'vi';
$pdfTranslations = [
    'vi' => [
        'brand' => 'AI VMIED CHECKER',
        'brand_sub' => 'Hệ thống phân tích AI & kiểm tra tính nguyên bản',
        'scan_date' => 'Ngày quét:',
        'report_id' => 'Mã báo cáo:',
        'report_label' => 'Báo cáo',
        'test_results' => 'Kết quả kiểm tra nội dung',
        'doc_info' => 'THÔNG TIN TÀI LIỆU',
        'doc_name' => 'Tên tài liệu',
        'check_type' => 'Loại kiểm tra',
        'word_count' => 'Số từ',
        'email' => 'Email',
        'words' => 'từ',
        'scan_time' => 'Thời gian kiểm tra',
        'report_time' => 'Thời gian tạo báo cáo',
        'ai_results' => 'KẾT QUẢ KIỂM TRA AI',
        'ai_prob' => 'Khả năng AI',
        'human_prob' => 'Khả năng con người',
        'ai_rate' => 'Tỉ lệ khả năng AI',
        'plag_results' => 'KẾT QUẢ KIỂM TRA TRÙNG LẶP',
        'plag_user' => 'Câu (đoạn) người dùng phản hồi',
        'plag_uncheck' => 'Phần trăm câu (đoạn) hệ thống không kiểm tra',
        'plag_unique' => 'Phần trăm câu (đoạn) không trùng lặp',
        'plag_dup' => 'Phần trăm câu (đoạn) trùng lặp',
        'plag_rate' => 'Tỉ lệ trùng lặp',
        'plag_sources' => 'Nguồn trùng lặp tiêu biểu',
        'plag_no_sources' => 'Không phát hiện nguồn trùng lặp đáng kể',
        'plag_note' => '(*) Kết quả trùng lặp phụ thuộc vào dữ liệu hệ thống tại thời điểm kiểm tra.',
        'grammar_read' => 'NGỮ PHÁP & ĐỘ DỄ ĐỌC',
        'grammar_std' => 'Chuẩn ngữ pháp',
        'errors' => 'lỗi',
        'readability' => 'Độ dễ đọc',
        'index_explain' => 'GIẢI THÍCH CHỈ SỐ',
        'index' => 'Chỉ số',
        'meaning' => 'Ý nghĩa',
        'ai_prob_desc' => 'Trình phát hiện AI chính xác cho ChatGPT, GPT-4o, Gemini, Claude, Llama... Điểm số là độ tin cậy: 90% nghĩa là "tự tin 90% văn bản do AI tạo ra", không phải "90% văn bản là AI".',
        'plag_desc' => 'Tỉ lệ trùng lặp nội dung với các nguồn đã đối chiếu.',
        'typical_sources' => 'Nguồn tiêu biểu:',
        'grammar_desc' => 'Tính trên số lỗi / 100 từ. Không có lỗi nào → đạt 100%.',
        'ai_analysis' => 'PHÂN TÍCH AI TỪNG CÂU',
        'ai_analysis_note' => 'Mỗi câu được phân tích độc lập. Màu đỏ ≥70% — cam 40–69% — xanh <40% khả năng AI.',
        'sentence_num' => '#',
        'sentence_content' => 'Nội dung câu',
        'ai_level' => 'Mức độ AI',
        'plag_details' => 'CHI TIẾT TRÙNG LẶP NỘI DUNG',
        'dup_paragraph' => 'Đoạn văn trùng lặp',
        'ref_source' => 'Nguồn đối chiếu',
        'fact_check' => 'Kiểm chứng sự thật',
        'claims' => 'mệnh đề',
        'suspects' => 'sai/nghi ngờ',
        'all_correct' => 'Tất cả đúng',
        'true' => '✓ Đúng',
        'false' => '✗ Sai',
        'unverified' => '? Chưa xác minh',
        'unknown' => 'Không rõ',
        'confidence' => 'Tin cậy:',
        'rewrite' => 'Gợi ý viết lại:',
        'ref_sources' => 'Nguồn đối chiếu:',
        'grammar_spell' => 'Ngữ pháp & chính tả',
        'errors_detected' => 'lỗi phát hiện',
        'grade' => 'Xếp loại:',
        'grammar' => 'Ngữ pháp',
        'suggestion' => '💡 Gợi ý:',
        'no_content' => 'Không có nội dung.'
    ],
    'en' => [
        'brand' => 'AI VMIED CHECKER',
        'brand_sub' => 'AI Detection & Originality Check System',
        'scan_date' => 'Scan Date:',
        'report_id' => 'Report ID:',
        'report_label' => 'Report',
        'test_results' => 'Content Scan Results',
        'doc_info' => 'DOCUMENT INFORMATION',
        'doc_name' => 'Document Name',
        'check_type' => 'Scan Type',
        'word_count' => 'Word Count',
        'email' => 'Email',
        'words' => 'words',
        'scan_time' => 'Scan Time',
        'report_time' => 'Report Generated At',
        'ai_results' => 'AI DETECTION RESULTS',
        'ai_prob' => 'AI Probability',
        'human_prob' => 'Human Probability',
        'ai_rate' => 'AI Probability Rate',
        'plag_results' => 'PLAGIARISM CHECK RESULTS',
        'plag_user' => 'User Responded Sentences',
        'plag_uncheck' => 'Unchecked Sentences Percentage',
        'plag_unique' => 'Unique Sentences Percentage',
        'plag_dup' => 'Duplicate Sentences Percentage',
        'plag_rate' => 'Plagiarism Rate',
        'plag_sources' => 'Typical Duplicate Sources',
        'plag_no_sources' => 'No significant duplicate sources detected',
        'plag_note' => '(*) Plagiarism results depend on system data at the time of scanning.',
        'grammar_read' => 'GRAMMAR & READABILITY',
        'grammar_std' => 'Grammar Score',
        'errors' => 'errors',
        'readability' => 'Readability',
        'index_explain' => 'METRIC EXPLANATION',
        'index' => 'Metric',
        'meaning' => 'Meaning',
        'ai_prob_desc' => 'Accurate AI detector for ChatGPT, GPT-4o, Gemini, Claude... The score is confidence: 90% means "90% confident the text is AI-generated", not "90% of the text is AI".',
        'plag_desc' => 'Percentage of duplicated content against compared sources.',
        'typical_sources' => 'Typical sources:',
        'grammar_desc' => 'Calculated based on errors / 100 words. Zero errors → 100%.',
        'ai_analysis' => 'AI SENTENCE ANALYSIS',
        'ai_analysis_note' => 'Each sentence is analyzed independently. Red ≥70% — Orange 40–69% — Green <40% AI probability.',
        'sentence_num' => '#',
        'sentence_content' => 'Sentence Content',
        'ai_level' => 'AI Level',
        'plag_details' => 'PLAGIARISM DETAILS',
        'dup_paragraph' => 'Duplicate Paragraph',
        'ref_source' => 'Reference Source',
        'fact_check' => 'Fact Check',
        'claims' => 'claims',
        'suspects' => 'errors/suspects',
        'all_correct' => 'All correct',
        'true' => '✓ True',
        'false' => '✗ False',
        'unverified' => '? Unverified',
        'unknown' => 'Unknown',
        'confidence' => 'Confidence:',
        'rewrite' => 'Rewrite suggestion:',
        'ref_sources' => 'Reference sources:',
        'grammar_spell' => 'Grammar & Spelling',
        'errors_detected' => 'errors detected',
        'grade' => 'Grade:',
        'grammar' => 'Grammar',
        'suggestion' => '💡 Suggestion:',
        'no_content' => 'No content.'
    ]
];
$__p = function($key) use ($pdfTranslations, $pdfLang, $__) {
    // Ưu tiên local PDF dictionary, nếu không có thì gọi $__ của Controller
    return $pdfTranslations[$pdfLang][$key] ?? (isset($__) ? $__($key) : $key); 
};

// ── Số liệu tổng quan ────────────────────────────────────────────────
$aiPct         = $summary['ai_score']          ?? round(($ai['confidence']['AI'] ?? 0) * 100);
$origPct       = 100 - $aiPct;
$plagScore     = $summary['plag_score']        ?? ($plag['score'] ?? 0);
$gramErrors    = $summary['grammar_errors']    ?? count($gram['matches'] ?? []);
$readScore     = $summary['readability_score'] ?? ($read['readability']['fleschReadingEase'] ?? null);
$readGrade     = $summary['readability_grade'] ?? ($read['readability']['fleschGradeLevel']  ?? null);
$wordCount     = $summary['word_count']        ?? 0;
$factsTotal    = $summary['facts_total']       ?? count($facts);
$factsErrors   = $summary['facts_errors']      ?? 0;
$seoScore      = $summary['seo_score']         ?? ($seo['score'] ?? $seo['content_score'] ?? 0);
$title         = $summary['title']             ?? ($history['title'] ?? $__p('report_label'));
$createdAt     = $summary['created_at']        ?? ($history['created_at'] ?? null);
$email         = $summary['email']             ?? ($user->email ?? 'Không xác định');

// Xử lý định dạng ngày tháng theo ngôn ngữ
$dateScanned   = $createdAt ? ($pdfLang === 'vi' ? date('d/m/Y H:i', strtotime($createdAt)) : date('M d, Y h:i A', strtotime($createdAt))) : '—';
$dateGenerated = $pdfLang === 'vi' ? date('d-m-Y, H:i:s') : date('M d, Y, h:i A');

// ── Màu AI ───────────────────────────────────────────────────────────
$aiColor = $aiPct >= 70 ? '#dc3545' : ($aiPct >= 40 ? '#fd7e14' : '#28a745');

// ── Nguồn đạo văn unique ─────────────────────────────────────────────
$plagSources = [];
foreach (($plag['results'] ?? []) as $pr) {
    foreach (($pr['results'] ?? $pr['sources'] ?? []) as $src) {
        $link = $src['link'] ?? $src['url'] ?? '';
        if ($link) $plagSources[$link] = $src['title'] ?? parse_url($link, PHP_URL_HOST) ?? $link;
    }
}

if (!function_exists('vm_pie_chart_url')) {
    function vm_pie_chart_url(array $data, array $colors): string
    {
        $config = [
            'type' => 'pie',
            'data' => [
                'labels'   => array_fill(0, count($data), ''),
                'datasets' => [[
                    'data'            => $data,
                    'backgroundColor' => $colors,
                    'borderColor'     => '#ffffff',
                    'borderWidth'     => 2,
                ]],
            ],
            'options' => [
                'plugins' => [
                    'legend' => ['display' => false],
                    'datalabels' => [
                        'color'    => '#333333',
                        'font'     => ['size' => 15, 'weight' => 'bold'],
                        'anchor'   => 'end',
                        'align'    => 'end',
                        'offset'   => 6,
                        'formatter' => "function(v){ return v > 0 ? v + '%' : ''; }",
                    ],
                ],
            ],
        ];
        return 'https://quickchart.io/chart?w=280&h=230&v=4&c=' . urlencode(json_encode($config));
    }
}

// AI: 2 lát — Khả năng AI / Khả năng con người
$aiChartUrl = vm_pie_chart_url([$aiPct, $origPct], [$aiColor, '#e9ecef']);

// Đạo văn: 4 lát — đúng cấu trúc & màu như báo cáo mẫu
$plagResponded   = 0; 
$plagNotChecked  = 0; 
$plagNotDup      = max(0, 100 - $plagScore - $plagResponded - $plagNotChecked);
$plagChartUrl = vm_pie_chart_url(
    [$plagResponded, $plagNotChecked, $plagNotDup, $plagScore],
    ['#4C9AFF', '#F87171', '#F5A623', '#7BE495']
);

// ── Facts phân loại ──────────────────────────────────────────────────
$falseFacts = array_values(array_filter($facts, fn($f) =>
    in_array(strtolower($f['classification'] ?? ''), ['false', 'unverified'], true)
));

// ── Điểm ngữ pháp (chuẩn hoá trên 100 từ) ──────────────────────────────
$wordsPer100  = max(1, $wordCount / 100);
$grammarScore = max(0, round(100 - ($gramErrors / $wordsPer100)));

if (!function_exists('vm_ring')) {
    function vm_ring(float $percent, string $color, string $label, string $center, string $sub = ''): string
    {
        $percent = max(0, min(100, $percent));
        $r = 40; $cx = 48; $cy = 48;
        $circumference = 2 * M_PI * $r;
        $dash = round(($percent / 100) * $circumference, 2);
        $gap  = round($circumference - $dash, 2);

        return '
        <div class="vm-ring-wrap">
            <svg width="96" height="96" viewBox="0 0 96 96">
                <circle cx="' . $cx . '" cy="' . $cy . '" r="' . $r . '" fill="none" stroke="#EDEFF4" stroke-width="9"/>
                <circle cx="' . $cx . '" cy="' . $cy . '" r="' . $r . '" fill="none" stroke="' . $color . '" stroke-width="9"
                        stroke-dasharray="' . $dash . ' ' . $gap . '" stroke-linecap="round"
                        transform="rotate(-90 ' . $cx . ' ' . $cy . ')"/>
                <text x="48" y="53" text-anchor="middle" font-size="18" font-weight="bold" fill="#222">' . htmlspecialchars($center) . '</text>
            </svg>
            <div class="vm-ring-label">' . htmlspecialchars($label) . '</div>
            ' . ($sub !== '' ? '<div class="vm-ring-sub">' . htmlspecialchars($sub) . '</div>' : '') . '
        </div>';
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $pdfLang ?>">
<head>
<meta charset="UTF-8">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
@page { size: A4; margin: 20mm; }

body { font-family: "DejaVu Sans", sans-serif; font-size: 10.5px; line-height: 1.5; color: #1a1a2e; background: #fff; word-wrap: break-word; overflow-wrap: break-word; }
.hdr { display: table; width: 100%; background-color: #f8f9fa; border: 1px solid #e9ecef; border-left: 4px solid #0056b3; padding: 12px 16px; border-radius: 6px; margin-bottom: 12px; }
.hdr-l, .hdr-r { display: table-cell; vertical-align: middle; }
.hdr-r { text-align: right; }
.brand { font-size: 14px; font-weight: bold; color: #0056b3; letter-spacing: 1px; text-transform: uppercase; }
.brand-sub { font-size: 8.5px; color: #6c757d; margin-top: 3px; text-transform: uppercase; }
.report-date { font-size: 9.5px; color: #495057; font-weight: bold; margin-bottom: 2px; }
.page-content { padding: 0 20px; }
.report-title-box { background: linear-gradient(135deg, #0056b3 0%, #0070c0 100%); color: #fff; padding: 12px 16px; border-radius: 6px; margin-bottom: 12px; text-align: center; }
.report-title-box .label { font-size: 9.5px; opacity: 0.85; text-transform: uppercase; letter-spacing: 1.5px; }
.report-title-box h1 { font-size: 16px; font-weight: bold; margin-top: 4px; text-transform: uppercase; }
.sec-hdr { background: #0056b3; color: #fff; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; padding: 5px 10px; border-radius: 4px; margin: 14px 0 8px 0; display: block; }
.doc-info-tbl { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
.doc-info-tbl td { padding: 5px 8px; font-size: 10px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
.doc-info-tbl .lbl { width: 170px; color: #6c757d; }
.doc-info-tbl .val { font-weight: bold; color: #1a1a2e; }
.gauge-row { width: 100%; border-collapse: collapse; margin-top: 4px; margin-bottom: 4px; table-layout: fixed; }
.gauge-row td { text-align: center; vertical-align: top; border: 1px solid #e9ecef; border-radius: 6px; padding: 10px 4px 12px 4px; background: #fff; }
.vm-ring-wrap { text-align: center; }
.vm-ring-label { font-size: 10px; font-weight: bold; color: #495057; margin-top: 5px; text-transform: uppercase; }
.vm-ring-sub { font-size: 8.5px; color: #6c757d; margin-top: 2px; }
.explain-tbl { width: 100%; border-collapse: collapse; margin-top: 4px; }
.explain-tbl th { background: #e9ecef; color: #495057; text-align: left; font-size: 9px; text-transform: uppercase; padding: 6px 8px; border: 1px solid #dee2e6; }
.explain-tbl td { font-size: 9.5px; padding: 6px 8px; border: 1px solid #dee2e6; vertical-align: top; color: #495057; }
.pie-block { border: 1px solid #dee2e6; border-radius: 6px; padding: 12px; margin-bottom: 12px; background: #fff; }
.pie-layout { display: table; width: 100%; }
.pie-img-cell { display: table-cell; width: 240px; text-align: center; vertical-align: middle; }
.pie-img-cell img { width: 220px; height: auto; }
.pie-legend-cell { display: table-cell; vertical-align: middle; padding-left: 14px; }
.pie-legend-item { font-size: 9.5px; margin-bottom: 6px; color: #333; }
.pie-dot { display: inline-block; width: 10px; height: 10px; border-radius: 2px; margin-right: 6px; vertical-align: middle; }
.pie-stat-tbl { width: 100%; border-collapse: collapse; margin-top: 10px; }
.pie-stat-tbl td { padding: 5px 8px; font-size: 10px; border: 1px solid #eee; }
.pie-stat-tbl .lbl { width: 190px; color: #6c757d; background: #fafafa; }
.pie-stat-tbl .val { font-weight: bold; color: #1a1a2e; }
.pie-stat-tbl .val a { color: #0056b3; font-weight: normal; text-decoration: none; }
.tbl { width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 10px; table-layout: fixed; }
.tbl thead { display: table-header-group; }
.tbl tr { page-break-inside: avoid; }
.tbl td, .tbl th { border: 1px solid #dee2e6; padding: 7px 8px; vertical-align: top; word-wrap: break-word; overflow-wrap: break-word; }
.tbl th { background: #e9ecef; color: #495057; text-align: left; font-size: 9px; text-transform: uppercase; }
.tbl tr:nth-child(even) td { background: #fafafa; }
.badge { display: inline-block; padding: 2px 7px; border-radius: 10px; font-size: 9px; font-weight: bold; color: #fff; }
.bd-danger { background: #dc3545; }
.bd-warning { background: #fd7e14; }
.bd-success { background: #28a745; }
.bd-info { background: #17a2b8; }
.bd-muted { background: #6c757d; }
.bd-dark { background: #343a40; }
.item-box { border: 1px solid #dee2e6; border-radius: 5px; padding: 8px 10px; margin-bottom: 8px; background: #fff; page-break-inside: avoid; word-wrap: break-word; }
.item-box .i-title { font-weight: bold; font-size: 10.5px; color: #1a1a2e; margin-bottom: 4px; }
.item-box .i-msg { font-size: 9.5px; color: #495057; margin-bottom: 4px; }
.item-box .i-ctx { font-family: monospace; font-size: 9px; background: #f0f0f0; padding: 3px 6px; border-radius: 3px; color: #555; margin: 4px 0; display: block; }
.item-box .i-tip { font-size: 9.5px; color: #28a745; margin-top: 4px; }
.item-box .i-rewrite { font-size: 9.5px; color: #0056b3; background: #f0f7ff; border-left: 3px solid #0056b3; padding: 4px 8px; margin-top: 6px; font-style: italic; }
.item-box .i-sources { font-size: 9px; color: #555; margin-top: 6px; border-top: 1px dashed #dee2e6; padding-top: 6px; }
.item-box .i-sources a { color: #0056b3; text-decoration: none; word-break: break-all; }
.page-break { page-break-before: always; }
</style>
</head>
<body>

<?php
$globalHeader = '
<div class="hdr">
    <div class="hdr-l">
        <div class="brand">' . $__p('brand') . '</div>
        <div class="brand-sub">' . $__p('brand_sub') . '</div>
    </div>
    <div class="hdr-r">
        ' . ($createdAt ? '<div class="report-date">' . $__p('scan_date') . ' ' . $dateScanned . '</div>' : '') . '
        <div class="report-date">' . $__p('report_id') . ' #' . (int)($history['id'] ?? 0) . '</div>
    </div>
</div>';
?>
<?php
$globalHeader = '
<div class="hdr">
    <div class="hdr-l">
        <div class="brand">' . $__p('brand') . '</div>
        <div class="brand-sub">' . $__p('brand_sub') . '</div>
    </div>
    <div class="hdr-r">
        ' . ($createdAt ? '<div class="report-date">' . $__p('scan_date') . ' ' . $dateScanned . '</div>' : '') . '
    </div>
</div>';
?>

<!-- TRANG 1: TỔNG QUAN -->
<?= $globalHeader ?>
<div class="page-content">

<div class="report-title-box">
    <div class="label"><?= $__p('report_label') ?></div>
    <h1><?= $__p('test_results') ?></h1>
</div>

<span class="sec-hdr" style="margin-top:0;"><?= $__p('doc_info') ?></span>
<table class="doc-info-tbl">
    <tr>
        <td class="lbl"><?= $__p('doc_name') ?></td>
        <td class="val"><?= htmlspecialchars($title) ?></td>
    </tr>
    <tr>
        <td class="lbl"><?= trim($__p('report_id'), ':') ?></td>
        <td class="val">#<?= (int)($history['id'] ?? 0) ?></td>
    </tr>
    <tr>
        <td class="lbl"><?= $__p('email') ?></td>
        <td class="val"><?= htmlspecialchars($email) ?></td>
    </tr>
    <tr>
        <td class="lbl"><?= $__p('check_type') ?></td>
        <td class="val"><?= htmlspecialchars(ucfirst($summary['type'] ?? 'text')) ?></td>
    </tr>
    <tr>
        <td class="lbl"><?= $__p('word_count') ?></td>
        <td class="val"><?= number_format((int)$wordCount, 0, ',', '.') ?> <?= $__p('words') ?></td>
    </tr>
    <tr>
        <td class="lbl"><?= $__p('scan_time') ?></td>
        <td class="val"><?= $dateScanned ?></td>
    </tr>
    <tr>
        <td class="lbl"><?= $__p('report_time') ?></td>
        <td class="val"><?= $dateGenerated ?></td>
    </tr>
</table>

<span class="sec-hdr"><?= $__p('ai_results') ?></span>
<div class="pie-block">
    <div class="pie-layout">
        <div class="pie-img-cell">
            <img src="<?= htmlspecialchars($aiChartUrl) ?>" alt="Biểu đồ AI">
        </div>
        <div class="pie-legend-cell">
            <div class="pie-legend-item"><span class="pie-dot" style="background:<?= $aiColor ?>;"></span><?= $__p('ai_prob') ?> — <?= $aiPct ?>%</div>
            <div class="pie-legend-item"><span class="pie-dot" style="background:#e9ecef;"></span><?= $__p('human_prob') ?> — <?= $origPct ?>%</div>
        </div>
    </div>
    <table class="pie-stat-tbl">
        <tr>
            <td class="lbl"><?= $__p('ai_rate') ?></td>
            <td class="val"><?= $aiPct ?>%</td>
        </tr>
        
    </table>
</div>

<span class="sec-hdr"><?= $__p('plag_results') ?></span>
<div class="pie-block">
    <div class="pie-layout">
        <div class="pie-img-cell">
            <img src="<?= htmlspecialchars($plagChartUrl) ?>" alt="Biểu đồ trùng lặp">
        </div>
        <div class="pie-legend-cell">
            <div class="pie-legend-item"><span class="pie-dot" style="background:#4C9AFF;"></span><?= $__p('plag_user') ?></div>
            <div class="pie-legend-item"><span class="pie-dot" style="background:#F87171;"></span><?= $__p('plag_uncheck') ?></div>
            <div class="pie-legend-item"><span class="pie-dot" style="background:#F5A623;"></span><?= $__p('plag_unique') ?></div>
            <div class="pie-legend-item"><span class="pie-dot" style="background:#7BE495;"></span><?= $__p('plag_dup') ?></div>
        </div>
    </div>
    <table class="pie-stat-tbl">
        <tr>
            <td class="lbl"><?= $__p('plag_rate') ?></td>
            <td class="val"><?= $plagScore ?>%</td>
        </tr>
        <tr>
            <td class="lbl"><?= $__p('plag_sources') ?></td>
            <td class="val">
                <?php if (!empty($plagSources)): ?>
                    [<?php $i=0; foreach (array_slice($plagSources, 0, 3, true) as $link => $srcTitle): $i++; ?><a href="<?= htmlspecialchars($link) ?>"><?= htmlspecialchars(parse_url($link, PHP_URL_HOST) ?? $srcTitle) ?></a><?= $i < min(3, count($plagSources)) ? ', ' : '' ?><?php endforeach; ?>]
                <?php else: ?>
                    <?= $__p('plag_no_sources') ?>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <p style="font-size:9px;color:#888;margin-top:8px;"><?= $__p('plag_note') ?></p>
</div>

<span class="sec-hdr"><?= $__p('grammar_read') ?></span>
<table class="gauge-row">
    <tr>
        <td style="width:50%;">
            <?= vm_ring(
                $grammarScore,
                $grammarScore >= 80 ? '#28a745' : ($grammarScore >= 50 ? '#fd7e14' : '#dc3545'),
                $__p('grammar_std'),
                $grammarScore . '%',
                $gramErrors . ' ' . $__p('errors')
            ) ?>
        </td>
        <td style="width:50%;">
            <?php if ($readScore !== null): ?>
                <?= vm_ring(
                    (float)$readScore,
                    (float)$readScore >= 50 ? '#28a745' : '#fd7e14',
                    $__p('readability'),
                    round((float)$readScore) . '%',
                    $readGrade !== null ? 'Grade ' . $readGrade : ''
                ) ?>
            <?php else: ?>
                <?= vm_ring(0, '#d1d5db', $__p('readability'), '—') ?>
            <?php endif; ?>
        </td>
    </tr>
</table>

<span class="sec-hdr"><?= $__p('index_explain') ?></span>
<table class="explain-tbl">
    <tr>
        <th style="width:20%;"><?= $__p('index') ?></th>
        <th><?= $__p('meaning') ?></th>
    </tr>
    <tr>
        <td><strong><?= $__p('ai_prob') ?></strong></td>
        <td><?= $__p('ai_prob_desc') ?></td>
    </tr>
    <tr>
        <td><strong><?= $__p('plag_rate') ?></strong></td>
        <td>
            <?= $__p('plag_desc') ?>
            <?php if (!empty($plagSources)): ?>
                <?= $__p('typical_sources') ?>
                <?php foreach (array_slice($plagSources, 0, 2) as $link => $srcTitle): ?>
                    <a href="<?= htmlspecialchars($link) ?>"><?= htmlspecialchars(mb_strimwidth($link, 0, 40, '...')) ?></a><?= ' ' ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <td><strong><?= $__p('grammar_std') ?></strong></td>
        <td><?= $__p('grammar_desc') ?></td>
    </tr>
    <tr>
        <td><strong><?= $__p('readability') ?></strong></td>
        <td>
            Flesch Reading Ease: <?= $readScore !== null ? $readScore : '—' ?> ·
            Flesch-Kincaid Grade: <?= $readGrade !== null ? $readGrade : '—' ?> ·
            Gunning Fog: <?= $read['readability']['gunningFoxIndex'] ?? '—' ?> ·
            Dale-Chall Grade: <?= $read['readability']['daleChallReadabilityGrade'] ?? '—' ?> ·
            Smog Index: <?= $read['readability']['smogIndex'] ?? '—' ?>
        </td>
    </tr>
</table>

</div>

<!-- TRANG 2: PHÂN TÍCH CHI TIẾT TỪNG CÂU -->
<?php if (!empty($ai['blocks'])): ?>
<div class="page-break"></div>
<?= $globalHeader ?>
<div class="page-content">

<span class="sec-hdr" style="margin-top:0;"><?= $__p('ai_analysis') ?></span>
<p style="font-size:9.5px;color:#6c757d;margin-bottom:10px;">
    <?= $__p('ai_analysis_note') ?>
</p>

<table class="tbl">
    <thead>
        <tr>
            <th style="width:6%;text-align:center;"><?= $__p('sentence_num') ?></th>
            <th style="width:74%;"><?= $__p('sentence_content') ?></th>
            <th style="width:20%;text-align:center;"><?= $__p('ai_level') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($ai['blocks'] as $i => $block):
            $fp  = round(($block['result']['fake'] ?? 0) * 100);
            $bc  = $fp >= 70 ? 'bd-danger' : ($fp >= 40 ? 'bd-warning' : 'bd-success');
            $bgr = $fp >= 70 ? '#fff5f5' : ($fp >= 40 ? '#fffbf0' : '#f0fff4');
        ?>
        <tr style="background:<?= $bgr ?>;">
            <td style="text-align:center;font-weight:bold;color:#6c757d;"><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($block['text'] ?? '') ?></td>
            <td style="text-align:center;"><span class="badge <?= $bc ?>"><?= $fp ?>%</span></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</div>
<?php endif; ?>

<!-- TRANG 3: ĐẠO VĂN CHI TIẾT -->
<?php if (!empty($plag['results']) && is_array($plag['results'])): ?>
<div class="page-break"></div>
<?= $globalHeader ?>
<div class="page-content">

<span class="sec-hdr" style="margin-top:0;"><?= $__p('plag_details') ?></span>

<table class="tbl">
    <thead>
        <tr>
            <th style="width:40%;"><?= $__p('dup_paragraph') ?></th>
            <th style="width:60%;"><?= $__p('ref_source') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($plag['results'] as $pr):
            $phrase  = $pr['phrase'] ?? $pr['text'] ?? '';
            $sources = $pr['results'] ?? $pr['sources'] ?? [];
            if (empty($phrase) || empty($sources)) continue;
        ?>
        <tr>
            <td>
                <div style="font-style:italic;color:#495057;">"<?= htmlspecialchars($phrase) ?>"</div>
            </td>
            <td>
                <?php foreach ($sources as $src):
                    $link    = $src['link']    ?? $src['url']     ?? '';
                    $snippet = $src['text']    ?? $src['snippet'] ?? '';
                    $srcTitle = $src['title']  ?? parse_url($link, PHP_URL_HOST) ?? $link;
                ?>
                <div style="margin-bottom:6px;padding-bottom:6px;border-bottom:1px dashed #eee;">
                    <?php if ($link): ?>
                    <div style="font-weight:bold;font-size:9.5px;color:#0056b3;word-break:break-all;">
                        🔗 <?= htmlspecialchars($srcTitle) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($snippet): ?>
                    <div style="color:#dc3545;font-style:italic;font-size:9px;margin-top:2px;">
                        "<?= htmlspecialchars(mb_strimwidth($snippet, 0, 150, '...')) ?>"
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</div>
<?php endif; ?>

<!-- TRANG 4: NGỮ PHÁP & SỰ THẬT -->
<?php if (!empty($gram['matches']) || !empty($facts)): ?>
<div class="page-break"></div>
<?= $globalHeader ?>
<div class="page-content">

<!-- Kiểm chứng sự thật -->
<?php if (!empty($facts)): ?>
<span class="sec-hdr">
    <?= $__p('fact_check') ?> — <?= $factsTotal ?> <?= $__p('claims') ?>
    <?= $factsErrors > 0 ? "· {$factsErrors} " . $__p('suspects') : '· ' . $__p('all_correct') ?>
</span>

<?php foreach ($facts as $fact):
    $claim       = $fact['claim']          ?? '';
    $explanation = $fact['explanation']    ?? '';
    $cls         = strtolower(trim($fact['classification'] ?? 'unknown'));
    $confidence  = $fact['confidence']     ?? null;
    $rewrite     = $fact['rewrite']        ?? '';
    $sources     = $fact['sources']        ?? [];

    $clsColor = match($cls) { 'true' => '#28a745', 'false' => '#dc3545', 'unverified' => '#fd7e14', default => '#6c757d' };
    $clsLabel = match($cls) { 'true' => $__p('true'), 'false' => $__p('false'), 'unverified' => $__p('unverified'), default => $__p('unknown') };
    $clsBadge = match($cls) { 'true' => 'bd-success', 'false' => 'bd-danger', 'unverified' => 'bd-warning', default => 'bd-muted' };

    $confPct = null;
    if ($confidence !== null) {
        $cf = (float) $confidence;
        $confPct = $cf <= 1 ? round($cf * 100) : round($cf);
    }
    $borderColor = match($cls) { 'false' => '#dc3545', 'unverified' => '#fd7e14', default => '#dee2e6' };
?>
<div class="item-box" style="border-left:3px solid <?= $borderColor ?>;">
    <?php if ($claim): ?>
    <div class="i-title"><?= htmlspecialchars($claim) ?></div>
    <?php endif; ?>

    <div style="margin-bottom:5px;">
        <span class="badge <?= $clsBadge ?>"><?= $clsLabel ?></span>
        <?php if ($confPct !== null): ?>
        <span class="badge bd-dark" style="margin-left:4px;"><?= $__p('confidence') ?> <?= $confPct ?>%</span>
        <?php endif; ?>
    </div>

    <?php if ($explanation): ?>
    <div class="i-msg"><?= htmlspecialchars($explanation) ?></div>
    <?php endif; ?>

    <?php if ($rewrite): ?>
    <div class="i-rewrite"><strong><?= $__p('rewrite') ?></strong> "<?= htmlspecialchars($rewrite) ?>"</div>
    <?php endif; ?>

    <?php if (!empty($sources)): ?>
    <div class="i-sources">
        <strong><?= $__p('ref_sources') ?></strong><br>
        <?php foreach (array_slice($sources, 0, 3) as $src):
            $su = $src['url'] ?? $src['link'] ?? '';
            $st = $src['title'] ?? parse_url($su, PHP_URL_HOST) ?? $su;
            if ($su):
        ?>
        · <a href="<?= htmlspecialchars($su) ?>"><?= htmlspecialchars($st) ?></a><br>
        <?php endif; endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>

<!-- Ngữ pháp -->
<?php if (!empty($gram['matches'])): ?>
<span class="sec-hdr" style="margin-top:16px;"><?= $__p('grammar_spell') ?> — <?= $gramErrors ?> <?= $__p('errors_detected') ?></span>
<?php if (isset($gram['grade'])): ?>
<p style="font-size:9.5px;color:#6c757d;margin-bottom:8px;">
    <?= $__p('grade') ?> <strong><?= htmlspecialchars($gram['grade']) ?></strong>
</p>
<?php endif; ?>

<?php foreach ($gram['matches'] ?? [] as $match):
    $sentence    = $match['sentence']     ?? '';
    $errorText   = trim($match['error_text'] ?? '');
    $message     = $match['message']      ?? ($match['shortMessage'] ?? '');
    $suggestions = array_column($match['replacements'] ?? [], 'value');

    $highlightedSentence = htmlspecialchars($sentence, ENT_QUOTES, 'UTF-8');
    if ($errorText !== '') {
        $escapedErr = htmlspecialchars($errorText, ENT_QUOTES, 'UTF-8');
        $pos = mb_stripos($highlightedSentence, $escapedErr, 0, 'UTF-8');
        if ($pos !== false) {
            $before  = mb_substr($highlightedSentence, 0, $pos, 'UTF-8');
            $matched = mb_substr($highlightedSentence, $pos, mb_strlen($escapedErr, 'UTF-8'), 'UTF-8');
            $after   = mb_substr($highlightedSentence, $pos + mb_strlen($escapedErr, 'UTF-8'), null, 'UTF-8');
            $highlightedSentence = $before
                . '<span style="color:#dc3545;font-weight:bold;text-decoration:underline;background:rgba(220,53,69,0.1);padding:0 2px;border-radius:3px;">'
                . $matched . '</span>' . $after;
        }
    }
?>
<div class="item-box">
    <div class="i-title"><?= $__p('grammar') ?></div>
    <div class="i-msg"><?= htmlspecialchars($message) ?></div>
    <div class="i-ctx"><?= $highlightedSentence ?></div>
    <?php if (!empty($suggestions)): ?>
    <div class="i-tip"><?= $__p('suggestion') ?> <?= htmlspecialchars(implode(', ', $suggestions)) ?></div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>

</div>
<?php endif; ?>
</body>
</html>