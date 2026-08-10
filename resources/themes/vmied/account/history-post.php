<?php
$data  = $result ?? [];
$ai    = $data['ai']              ?? [];
$plag  = $data['plagiarism']      ?? [];
$facts = $data['facts']           ?? [];
$read  = $data['readability']     ?? [];
$gram  = $data['grammarSpelling'] ?? [];   
$seo   = $data['contentOptimizer'] ?? [];  

// ── Lấy ngôn ngữ tài liệu (Mặc định 'vi') ────────────────────────────
$docLang = $history['lang'] ?? 'vi';

// ── Hàm Helper Dịch Thuật ────────────────────────────────────────────
$__ = function($key) use ($docLang) {
    $translations = [
        'vi' => [
            'original_highlight' => 'Bản gốc Highlight',
            'export_report'      => 'Xuất Báo Cáo',
            'ai_detection'       => 'Phát hiện AI',
            'plagiarism'         => 'Đạo văn',
            'grammar_errors'     => 'Lỗi ngữ pháp',
            'readability'        => 'Khả năng đọc',
            'doc_info'           => 'THÔNG TIN TÀI LIỆU',
            'doc_name'           => 'Tên tài liệu:',
            'word_count'         => 'Số từ:',
            'words'              => 'từ',
            'email'              => 'Email:',
            'ai_model'           => 'Mô hình AI:',
            'scan_time'          => 'Thời gian quét:',
            'source'             => 'Nguồn:',
            'status'             => 'Trạng thái:',
            'completed'          => 'Hoàn thành',
            'original_content'   => 'NỘI DUNG GỐC',
            'ai_analysis_sentence'=> 'PHÂN TÍCH AI TỪNG CÂU',
            'sentence_content'   => 'Nội dung câu',
            'ai_level'           => 'Mức độ AI',
            'grammar_and_spell'  => 'NGỮ PHÁP & CHÍNH TẢ',
            'grading'            => 'Xếp loại:',
            'errors'             => 'lỗi',
            'suggestions'        => 'Gợi ý:',
            'ai_score'           => 'ĐIỂM AI',
            'ai_written'         => 'AI viết',
            'human_written'      => 'Người viết',
            'human'              => 'NGƯỜI',
            'likely'             => 'Có khả năng là',
            'confidence'         => 'Độ tin cậy',
            'confident_text_1'   => 'Chúng tôi tự tin rằng văn bản được quét là',
            'confident_text_2'   => ', nhưng điều đó KHÔNG có nghĩa là',
            'confident_text_3'   => 'văn bản được tạo ra đều là',
            'created_by_ai'      => 'do AI tạo ra',
            'created_by_human'   => 'do con người viết',
            'model_used'         => 'Mô hình được sử dụng:',
            'date'               => 'Ngày:',
            'how_to_read'        => 'Làm thế nào để đọc điểm số?',
            'how_to_read_desc_1' => 'Điểm số này phản ánh mức độ tin cậy của chúng tôi rằng văn bản được quét đã được tạo ra (hoặc viết lại) bởi công cụ AI. Văn bản có điểm số \'Có khả năng là AI/Nguyên bản - Độ tin cậy 90%\' nên được hiểu là:',
            'how_to_read_desc_2' => 'Chúng tôi tin tưởng 90% rằng văn bản này do',
            'how_to_read_desc_3' => 'KHÔNG có nghĩa là 90% văn bản là do',
            'understand_highlight'=> 'Hiểu ý nghĩa phần tô sáng',
            'highlight_desc'     => 'Mức độ tin cậy của AI được thể hiện trong đoạn văn bản được đánh dấu ở khung bên trái bằng các màu sắc sau:',
            'very_likely_ai'     => 'Rất có thể là AI (≥ 70%)',
            'likely_ai'          => 'Có thể là AI (40% - 69%)',
            'human_written_hl'   => 'Người viết (< 40%)',
            'seo_optimization'   => 'TỐI ƯU SEO',
            'score'              => 'Điểm:',
            'content_structure'  => 'Cấu trúc nội dung',
            'word'               => 'Từ',
            'heading'            => 'Tiêu đề',
            'paragraph'          => 'Đoạn văn',
            'optimization_suggestions' => 'Gợi ý Tối ưu',
            'geo_suggestions'    => 'Đề xuất GEO',
            'seo_suggestions'    => 'Gợi ý SEO',
            'potential_keywords' => 'Từ khóa tiềm năng',
            'competitors'        => 'Đối thủ cạnh tranh',
            'rank'               => 'Hạng',
            'seo_score'          => 'Điểm SEO:',
            'readability_caps'   => 'ĐỌC HIỂU (READABILITY)',
            'text_stats'         => 'Thống kê văn bản',
            'unique_words'       => 'Từ độc nhất',
            'sentences'          => 'Số câu',
            'paragraphs'         => 'Số đoạn',
            'reading_time'       => 'Đọc (phút)',
            'speaking_time'      => 'Nói (phút)',
            'writing_time'       => 'Viết (phút)',
            'eval_metrics'       => 'Chỉ số đánh giá',
            'plag_sources'       => 'NGUỒN ĐẠO VĂN',
            'matching_websites'  => 'Các trang web phù hợp',
            'duplicate_phrases'  => 'Cụm từ trùng lặp',
            'page_details'       => 'Chi tiết trang (Page Details)',
            'page_modified'      => 'Trang cập nhật:',
            'page_published'     => 'Trang xuất bản:',
            'matching_phrases'   => 'Cụm từ trùng khớp',
            'duplicate_sources'  => 'nguồn trùng lặp',
            'no_plagiarism'      => 'Không phát hiện đạo văn',
            'fact_check'         => 'KIỂM TRA SỰ THẬT',
            'total'              => 'Tổng:',
            'errors_suspects'    => 'sai/nghi ngờ',
            'all_correct'        => 'Tất cả đúng',
            'rewrite_suggestion' => 'Gợi ý viết lại:',
            'references'         => 'Nguồn tham khảo',
            'original'           => 'Nguyên bản',
            'author_ai'          => 'AI',
            'author_human'       => 'con người',
            'suspected_plagiarism'=> 'Nghi ngờ đạo văn',
            'original_content_tooltip' => 'Nội dung nguyên bản',
            'grammar_spell_error' => 'lỗi chính tả/ngữ pháp',
        ],
        'en' => [
            'original_highlight' => 'Original Highlight',
            'export_report'      => 'Export Report',
            'ai_detection'       => 'AI Detection',
            'plagiarism'         => 'Plagiarism',
            'grammar_errors'     => 'Grammar Errors',
            'readability'        => 'Readability',
            'doc_info'           => 'DOCUMENT INFO',
            'doc_name'           => 'Document Name:',
            'word_count'         => 'Word Count:',
            'words'              => 'words',
            'ai_model'           => 'AI Model:',
            'email'              => 'Email:',
            'scan_time'          => 'Scan Time:',
            'source'             => 'Source:',
            'status'             => 'Status:',
            'completed'          => 'Completed',
            'original_content'   => 'ORIGINAL CONTENT',
            'ai_analysis_sentence'=> 'AI SENTENCE ANALYSIS',
            'sentence_content'   => 'Sentence Content',
            'ai_level'           => 'AI Level',
            'grammar_and_spell'  => 'GRAMMAR & SPELLING',
            'grading'            => 'Grade:',
            'errors'             => 'errors',
            'suggestions'        => 'Suggestions:',
            'ai_score'           => 'AI SCORE',
            'ai_written'         => 'AI written',
            'human_written'      => 'Human written',
            'human'              => 'HUMAN',
            'likely'             => 'Likely',
            'confidence'         => 'Confidence',
            'confident_text_1'   => 'We are confident that the scanned text is',
            'confident_text_2'   => ', but that DOES NOT mean',
            'confident_text_3'   => 'of the text was generated by',
            'created_by_ai'      => 'AI-generated',
            'created_by_human'   => 'human-written',
            'model_used'         => 'Model Used:',
            'date'               => 'Date:',
            'how_to_read'        => 'How to read the score?',
            'how_to_read_desc_1' => 'This score reflects our confidence level that the scanned text was generated (or rewritten) by an AI tool. A text with a score of \'Likely AI/Original - 90% Confidence\' should be interpreted as:',
            'how_to_read_desc_2' => 'We are 90% confident that this text is written by',
            'how_to_read_desc_3' => 'It DOES NOT mean that 90% of the text is written by',
            'understand_highlight'=> 'Understanding the highlights',
            'highlight_desc'     => 'The AI confidence level is represented in the highlighted text on the left panel by the following colors:',
            'very_likely_ai'     => 'Very likely AI (≥ 70%)',
            'likely_ai'          => 'Likely AI (40% - 69%)',
            'human_written_hl'   => 'Human written (< 40%)',
            'seo_optimization'   => 'SEO OPTIMIZATION',
            'score'              => 'Score:',
            'content_structure'  => 'Content Structure',
            'word'               => 'Word',
            'heading'            => 'Heading',
            'paragraph'          => 'Paragraph',
            'optimization_suggestions' => 'Optimization Suggestions',
            'geo_suggestions'    => 'GEO Suggestions',
            'seo_suggestions'    => 'SEO Suggestions',
            'potential_keywords' => 'Potential Keywords',
            'competitors'        => 'Competitors',
            'rank'               => 'Rank',
            'seo_score'          => 'SEO Score:',
            'readability_caps'   => 'READABILITY',
            'text_stats'         => 'Text Statistics',
            'unique_words'       => 'Unique Words',
            'sentences'          => 'Sentences',
            'paragraphs'         => 'Paragraphs',
            'reading_time'       => 'Reading (min)',
            'speaking_time'      => 'Speaking (min)',
            'writing_time'       => 'Writing (min)',
            'eval_metrics'       => 'Evaluation Metrics',
            'plag_sources'       => 'PLAGIARISM SOURCES',
            'matching_websites'  => 'Matching Websites',
            'duplicate_phrases'  => 'Duplicate Phrases',
            'page_details'       => 'Page Details',
            'page_modified'      => 'Page modified:',
            'page_published'     => 'Page published:',
            'matching_phrases'   => 'Matching Phrases',
            'duplicate_sources'  => 'duplicate sources',
            'no_plagiarism'      => 'No plagiarism detected',
            'fact_check'         => 'FACT CHECK',
            'total'              => 'Total:',
            'errors_suspects'    => 'errors/suspects',
            'all_correct'        => 'All correct',
            'rewrite_suggestion' => 'Rewrite suggestion:',
            'references'         => 'References',
            'original'           => 'Original',
            'author_ai'          => 'AI',
            'author_human'       => 'human',
            'suspected_plagiarism'=> 'Suspected plagiarism',
            'original_content_tooltip' => 'Original content',
            'grammar_spell_error' => 'grammar/spelling errors',
        ]
    ];
    return $translations[$docLang][$key] ?? $translations['vi'][$key] ?? $key;
};

// ── AI % ─────────────────────────────────────────────────────────────
$aiPct   = isset($summary['ai_score']) ? (float)$summary['ai_score'] : round(($ai['confidence']['AI'] ?? 0) * 100, 2);
$origPct = round(100 - $aiPct, 2);

// ── Số liệu tổng hợp ────────────────────────────────────────────────
$wordCount      = $summary['word_count']        ?? ($history['word_count'] ?? 0);
$grammarErrors  = $summary['grammar_errors']    ?? count($gram['matches'] ?? []);
$plagScore      = $summary['plag_score']        ?? ($plag['score'] ?? 0);
$readScore      = $summary['readability_score'] ?? ($read['readability']['fleschReadingEase'] ?? null);
$readGrade      = $summary['readability_grade'] ?? ($read['readability']['fleschGradeLevel']  ?? null);
$factsTotal     = $summary['facts_total']       ?? count($facts);
$factsErrors    = $summary['facts_errors']      ?? 0;
$createdAt      = $summary['created_at']        ?? ($history['created_at'] ?? null);
$title          = $summary['title']             ?? ($history['title'] ?? 'Bản quét chi tiết');
$scanType       = $summary['type']              ?? ($history['type'] ?? 'text');

// Format ngày tháng theo ngôn ngữ
if ($docLang == 'vi') {
    $dateFormatted = $createdAt ? date('H:i, d/m/Y', strtotime($createdAt)) : date('H:i, d/m/Y');
    $dateOnlyFormatted = $createdAt ? date('d \t\h\á\n\g m \n\ă\m Y', strtotime($createdAt)) : date('d \t\h\á\n\g m \n\ă\m Y');
} else {
    $dateFormatted = $createdAt ? date('h:i A, M d, Y', strtotime($createdAt)) : date('h:i A, M d, Y');
    $dateOnlyFormatted = $createdAt ? date('M d, Y', strtotime($createdAt)) : date('M d, Y');
}

// ── Danh sách nguồn đạo văn (unique) ─────────────────────────────────
$plagiarismSources = [];
if (!empty($plag['results']) && is_array($plag['results'])) {
    foreach ($plag['results'] as $phraseResult) {
        $phrase = $phraseResult['phrase'] ?? '';
        $sources = $phraseResult['results'] ?? $phraseResult['sources'] ?? [];
        foreach ($sources as $source) {
            $link  = $source['link']  ?? $source['url']   ?? '';
            $sTitle = $source['title'] ?? $source['name'] ?? $link;
            $sScore = isset($source['scores'][0]['score']) ? round($source['scores'][0]['score'] * 100) : 0;
            $timestamps = $source['timestamps'] ?? [];
            if ($link) {
                if (!isset($plagiarismSources[$link])) {
                    $plagiarismSources[$link] = ['title' => $sTitle, 'link' => $link, 'score' => $sScore, 'timestamps' => $timestamps, 'phrases' => []];
                } elseif ($sScore > $plagiarismSources[$link]['score']) {
                    $plagiarismSources[$link]['score'] = $sScore;
                }
                if ($phrase) {
                    $plagiarismSources[$link]['phrases'][] = $phrase;
                }
            }
        }
    }
}
usort($plagiarismSources, function($a, $b) {
    return $b['score'] <=> $a['score'];
});

// ── Facts: chỉ lấy sai/unverified ────────────────────────────────────
$falseFacts = array_values(array_filter($facts, function($f) {
    return in_array(strtolower($f['classification'] ?? ''), ['false', 'unverified'], true);
}));

// ── Xử lý Highlight Nội Dung Gốc ─────────────────────────────────────
$blocks = $ai['blocks'] ?? [];
if (empty($blocks) && !empty($summary['content'])) {
    preg_match_all('/[^.!?\n]+[.!?\n]*/', $summary['content'], $matches);
    $sentences = $matches[0] ?? [$summary['content']];
    foreach ($sentences as $s) {
        $blocks[] = ['text' => $s];
    }
}

if (!empty($blocks)) {
    foreach ($blocks as &$b) {
        $b['htmlText']      = htmlspecialchars($b['text'] ?? '');
        $b['grammarErrors'] = [];
        $b['highlights']    = [];
        $b['plagiarism']    = null;
    }
    unset($b);

    $grammarMatches = $gram['matches'] ?? [];
    foreach ($grammarMatches as $match) {
        $sentence   = trim(strtolower(preg_replace('/\s+/', ' ', $match['sentence'] ?? '')));
        $errWordRaw = trim($match['error_text'] ?? '');
        $errWord    = mb_strtolower($errWordRaw, 'UTF-8');
        if (!$errWord) continue;
    
        foreach ($blocks as $idx => &$b) {
            $bText = trim(strtolower(preg_replace('/\s+/', ' ', $b['text'] ?? '')));
            if (!str_contains($bText, $errWord)) continue;
    
            $b['grammarErrors'][] = $match;
            $b['highlights'][] = $errWordRaw;
        }
        unset($b);
    }

    foreach ($blocks as &$b) {
        if (!empty($b['highlights'])) {
            $uniqueWords = array_unique($b['highlights']);
            usort($uniqueWords, function($x, $y) { return mb_strlen($y) - mb_strlen($x); });
            foreach ($uniqueWords as $w) {
                $escaped = preg_quote(htmlspecialchars($w), '/');
                $b['htmlText'] = preg_replace_callback('/(<[^>]+>)|(' . $escaped . ')/ui', function ($m) {
                    if (!empty($m[1])) return $m[1];
                    if (!empty($m[2])) return '<span class="text-danger fw-bold text-decoration-underline" style="text-decoration-style: wavy; background-color: rgba(220,53,69,0.1); padding: 0 2px; border-radius: 3px;">' . $m[2] . '</span>';
                    return $m[0];
                }, $b['htmlText']);
            }
        }
    }
    unset($b);

    $plagResults = $plag['results'] ?? [];
    if (!empty($plagResults)) {
        foreach ($plagResults as $pr) {
            $phrase = trim(strtolower($pr['phrase'] ?? $pr['match'] ?? $pr['text'] ?? ''));
            if (!$phrase) continue;
            $cleanPhrase = preg_replace('/\s+/', ' ', $phrase);
            $mapped = false;
            foreach ($blocks as $idx => &$b) {
                $bText = trim(preg_replace('/\s+/', ' ', strtolower($b['text'] ?? '')));
                if (str_contains($bText, mb_substr($cleanPhrase, 0, 40)) || str_contains($cleanPhrase, mb_substr($bText, 0, 40))) {
                    if (empty($b['plagiarism'])) { $b['plagiarism'] = $pr; $mapped = true; }
                }
            }
            unset($b);
            if (!$mapped) {
                $target = count($blocks) > 1 ? 1 : 0;
                $blocks[$target]['plagiarism'] = $pr;
            }
        }
    }
}
?>

<div class="p-4" style="background-color: #f8f9fa;" x-data="{
    init() {
        setTimeout(() => {
            document.querySelectorAll('#history-content-blocks [data-bs-toggle=\'tooltip\']').forEach(el => {
                const instance = bootstrap.Tooltip.getInstance(el);
                if (instance) instance.dispose();
                new bootstrap.Tooltip(el, { html: true, container: 'body' });
            });
        }, 200);
    }
}">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bolder text-dark mb-1"><?= htmlspecialchars($title) ?></h5>
            <div class="d-flex align-items-center gap-2 text-muted small">
                <?php if ($createdAt): ?>
                <span><i data-lucide="clock" width="14" class="me-1"></i><?= $dateFormatted ?></span>
                <?php endif; ?>
                <span class="badge bg-light text-secondary border"><?= strtoupper($scanType) ?></span>
                <?php if ($wordCount): ?>
                <span><?= number_format($wordCount) ?> <?= $__('words') ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="/app/report/ai?id=<?= (int)($history['id'] ?? 0) ?>&type=highlight" target="_blank"
               class="btn btn-outline-primary btn-sm fw-bold rounded-2 px-3 d-flex align-items-center gap-2 text-decoration-none">
                <i data-lucide="file-text" width="16"></i> <?= $__('original_highlight') ?>
            </a>
            <a href="/app/report/ai?id=<?= (int)($history['id'] ?? 0) ?>" target="_blank"
               class="btn btn-dark btn-sm fw-bold rounded-2 px-3 d-flex align-items-center gap-2 text-decoration-none">
                <i data-lucide="file-down" width="16"></i> <?= $__('export_report') ?>
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <?php if (!empty($ai)): ?>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 text-center h-100">
                <div class="small fw-bold text-muted mb-1"><?= $__('ai_detection') ?></div>
                <div class="h3 fw-black mb-0 <?= $aiPct >= 70 ? 'text-danger' : ($aiPct >= 40 ? 'text-warning' : 'text-success') ?>">
                    <?= $aiPct ?>%
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($plag)): ?>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 text-center h-100">
                <div class="small fw-bold text-muted mb-1"><?= $__('plagiarism') ?></div>
                <div class="h3 fw-black mb-0 <?= $plagScore >= 30 ? 'text-danger' : ($plagScore >= 10 ? 'text-warning' : 'text-success') ?>">
                    <?= $plagScore ?>%
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($gram)): ?>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 text-center h-100">
                <div class="small fw-bold text-muted mb-1"><?= $__('grammar_errors') ?></div>
                <div class="h3 fw-black mb-0 <?= $grammarErrors > 10 ? 'text-danger' : ($grammarErrors > 0 ? 'text-warning' : 'text-success') ?>">
                    <?= $grammarErrors ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($read)): ?>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 text-center h-100">
                <div class="small fw-bold text-muted mb-1"><?= $__('readability') ?></div>
                <div class="h3 fw-black mb-0 text-primary">
                    <?= $readScore !== null ? $readScore : '—' ?>
                </div>
                <?php if ($readGrade): ?>
                <div class="extra-small text-muted">Grade <?= $readGrade ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="row g-4">

        <div class="col-lg-8">

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom p-4">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i data-lucide="info" class="me-2"></i><?= $__('doc_info') ?>
                    </h6>
                </div>
                <div class="card-body p-4">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-secondary w-30"><?= $__('doc_name') ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($title) ?></td>
                        </tr>
                        <tr>
                            <td class="text-secondary"><?= $__('word_count') ?></td>
                            <td class="fw-bold"><?= number_format($wordCount) ?> <?= $__('words') ?></td>
                        </tr>
                        <?php 
                        /*
                        if (!empty($summary['ai_model'])): ?>
                        <tr>
                            <td class="text-secondary"><?= $__('ai_model') ?></td>
                            <td><?= htmlspecialchars($summary['ai_model']) ?></td>
                        </tr>
                        <?php endif; 
                        */
                        ?>
                        <tr>
                            <td class="text-secondary"><?= $__('email') ?></td>
                            <td class="fw-bold"><?= htmlspecialchars(app()->request->user->email ?? 'Không xác định') ?></td>
                        </tr>
                        <?php if ($createdAt): ?>
                        <tr>
                            <td class="text-secondary"><?= $__('scan_time') ?></td>
                            <td><?= $dateFormatted ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($summary['scan_url'])): ?>
                        <tr>
                            <td class="text-secondary"><?= $__('source') ?></td>
                            <td class="text-break">
                                <a href="<?= htmlspecialchars($summary['scan_url']) ?>" target="_blank" class="text-decoration-none small">
                                    <?= htmlspecialchars($summary['scan_url']) ?>
                                </a>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td class="text-secondary"><?= $__('status') ?></td>
                            <td><span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-3"><?= $__('completed') ?></span></td>
                        </tr>
                    </table>
                </div>
            </div>

            <?php if (!empty($blocks)): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom p-4">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i data-lucide="file-text" class="me-2"></i><?= $__('original_content') ?>
                    </h6>
                </div>
                <div class="card-body p-4" id="history-content-blocks">
                    <div class="fs-6 lh-lg" style="white-space: pre-wrap; max-height: 400px; overflow-y: auto;">
                        <?php foreach ($blocks as $block): 
                            $classes = ['hl-block', 'p-1', 'rounded', 'd-inline', 'lh-lg', 'cursor-pointer'];
                            if (isset($block['result']['fake'])) {
                                $fake = $block['result']['fake'];
                                if ($fake > 0.8) $classes[] = 'bg-danger bg-opacity-25';
                                elseif ($fake > 0.5) $classes[] = 'bg-warning bg-opacity-25';
                                elseif ($fake > 0.3) $classes[] = 'bg-warning bg-opacity-10';
                                else $classes[] = 'bg-success bg-opacity-10';
                            }

                            if (!empty($block['plagiarism'])) $classes[] = 'border-bottom border-3 border-warning fw-medium';

                            $tooltipParts = [];
                            if (isset($block['result']['fake'])) {
                                $fakePct = number_format($block['result']['fake'] * 100, 1);
                                $tooltipParts[] = "<strong>AI: {$fakePct}%</strong>";
                            }
                            if (!empty($block['grammarErrors'])) $tooltipParts[] = '<span class="text-danger">✗ ' . count($block['grammarErrors']) . ' ' . $__('grammar_spell_error') . '</span>';
                            if (!empty($block['plagiarism'])) $tooltipParts[] = '<span class="text-warning">⚠ ' . $__('suspected_plagiarism') . '</span>';
                            $tooltipHtml = implode('<br>', $tooltipParts) ?: $__('original_content_tooltip');
                        ?>
                        <span class="<?= implode(' ', $classes) ?>" data-bs-toggle="tooltip" data-bs-html="true" data-bs-title="<?= htmlspecialchars($tooltipHtml) ?>"><?= $block['htmlText'] . ' ' ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php elseif (!empty($summary['content'])): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom p-4">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i data-lucide="file-text" class="me-2"></i><?= $__('original_content') ?>
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="p-3 bg-light rounded-3 small text-secondary" style="white-space: pre-wrap; max-height: 300px; overflow-y: auto;">
                        <?= htmlspecialchars($summary['content']) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($ai['blocks'])): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom p-4">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i data-lucide="cpu" class="me-2"></i><?= $__('ai_analysis_sentence') ?>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase text-secondary" style="font-size:0.85rem">
                                <tr>
                                    <th class="text-center" width="5%">#</th>
                                    <th><?= $__('sentence_content') ?></th>
                                    <th class="text-center" width="15%"><?= $__('ai_level') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ai['blocks'] as $i => $block):
                                    $fakePct = round(($block['result']['fake'] ?? 0) * 100);
                                    $color   = $fakePct >= 70 ? 'danger' : ($fakePct >= 40 ? 'warning' : 'success');
                                ?>
                                <tr>
                                    <td class="text-center fw-bold text-secondary"><?= $i + 1 ?></td>
                                    <td class="py-3 px-3"><?= htmlspecialchars($block['text'] ?? '') ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $color ?> rounded-pill"><?= $fakePct ?>%</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($gram['matches'])): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom p-4">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i data-lucide="spell-check" class="me-2"></i><?= $__('grammar_and_spell') ?>
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex gap-2 mb-3 flex-wrap">
                        <?php if (isset($gram['grade'])): ?>
                        <span class="badge bg-secondary rounded-pill"><?= $__('grading') ?> <?= htmlspecialchars($gram['grade']) ?></span>
                        <?php endif; ?>
                        <span class="badge bg-warning text-dark rounded-pill"><?= $grammarErrors ?> <?= $__('errors') ?></span>
                    </div>
                    <?php foreach ($gram['matches'] as $match): ?>
                    <div class="mb-2 p-3 border rounded-3 small bg-white">
                        <div class="fw-bold text-danger"><?= htmlspecialchars($match['shortMessage'] ?? $__('grammar_errors')) ?></div>
                        <?php if (!empty($match['message'])): ?>
                        <div class="text-muted mt-1"><?= htmlspecialchars($match['message']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($match['context']['text'])): ?>
                        <div class="mt-1 font-monospace text-secondary p-1 bg-light rounded" style="font-size:0.85rem">
                            "<?= htmlspecialchars($match['context']['text']) ?>"
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($match['replacements']) && is_array($match['replacements'])): 
                            $suggestions = array_filter(array_map(
                                fn($r) => htmlspecialchars($r['value'] ?? ''),
                                array_slice($match['replacements'], 0, 5)
                            ));
                        ?>
                        <div class="mt-2 text-success" style="font-size:0.85rem">
                            <strong>💡 <?= $__('suggestions') ?></strong> <?= implode(', ', $suggestions) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <div class="col-lg-4">

            <?php if (!empty($ai)): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom p-4">
                    <h6 class="fw-bold mb-0"><?= $__('ai_score') ?></h6>
                </div>
                <div class="card-body p-4 text-center">
                    <?php
                        $maxPct = max($aiPct, $origPct);
                        $maxLabel = $aiPct >= $origPct ? $__('author_ai') : $__('human');
                        $maxColorClass = $aiPct >= $origPct ? 'text-danger' : 'text-success';
                    ?>
                    <div class="chart-container mb-4 position-relative d-inline-block">
                        <svg width="260" height="260" viewBox="0 0 42 42" style="transform: rotate(-90deg);">
                            <circle class="donut-ring" cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#34c38f" stroke-width="4.5"></circle>
                            <circle class="donut-segment" cx="21" cy="21" r="15.91549430918954" fill="transparent" 
                                    stroke="#f46a6a" stroke-width="<?= $aiPct > 0 ? 4.5 : 0 ?>"
                                    stroke-dasharray="<?= $aiPct ?> <?= 100 - $aiPct ?>"
                                    stroke-dashoffset="25" stroke-linecap="butt"></circle>
                        </svg>
                        <div class="position-absolute top-50 start-50 translate-middle w-100 text-center">
                            <div class="display-6 fw-bold <?= $maxColorClass ?>"><?= $maxPct ?>%</div>
                            <div class="small text-secondary fw-bold text-uppercase"><?= $maxLabel ?></div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-center gap-4 mb-4">
                        <div class="d-flex align-items-center gap-2">
                            <span style="width: 12px; height: 12px; border-radius: 3px; background-color: #f46a6a; display: inline-block;"></span>
                            <span class="small fw-bold text-secondary"><?= $aiPct ?>% <?= $__('ai_written') ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span style="width: 12px; height: 12px; border-radius: 3px; background-color: #34c38f; display: inline-block;"></span>
                            <span class="small fw-bold text-secondary"><?= $origPct ?>% <?= $__('human_written') ?></span>
                        </div>
                    </div>

                    <?php
                        $isAi = $aiPct >= 50;
                        $conf = $isAi ? $aiPct : $origPct;
                        $authorLabel = $isAi ? $__('author_ai') : $__('author_human');
                        $authorLabelFull = $isAi ? $__('created_by_ai') : $__('created_by_human');
                        $likelyType = $isAi ? 'AI' : $__('original');
                    ?>
                    <h6 class="fw-bold <?= $isAi ? 'text-danger' : 'text-success' ?> mb-2">
                        <?= $__('likely') ?> <?= $likelyType ?> - <?= $__('confidence') ?> <?= $conf ?>%.
                    </h6>
                    <p class="small text-secondary mb-3">
                        <?= $__('confident_text_1') ?> <?= $authorLabelFull ?><?= $__('confident_text_2') ?> <?= $conf ?>% <?= $__('confident_text_3') ?> <?= $authorLabel ?>.
                    </p>

                    <!-- Removed Mô hình and Ngày -->
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 small text-secondary">
                    <div class="mb-3">
                        <h6 class="fw-bold text-dark mb-1"><i data-lucide="info" class="me-1 text-primary" style="width:16px;height:16px;"></i> <?= $__('how_to_read') ?></h6>
                        <p class="mb-0 lh-base">
                            <?= $__('how_to_read_desc_1') ?>
                            <br>• <?= $__('how_to_read_desc_2') ?> <?= $authorLabel ?>.
                            <br>• <?= $__('how_to_read_desc_3') ?> <?= $authorLabel ?>.
                        </p>
                    </div>
               
                    <div>
                        <h6 class="fw-bold text-dark mb-1"><i data-lucide="highlighter" class="me-1 text-primary" style="width:16px;height:16px;"></i> <?= $__('understand_highlight') ?></h6>
                        <p class="mb-2"><?= $__('highlight_desc') ?></p>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <div style="width:16px;height:16px;background:#f87171;border-radius:4px;"></div>
                            <span><?= $__('very_likely_ai') ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <div style="width:16px;height:16px;background:#fbbf24;border-radius:4px;"></div>
                            <span><?= $__('likely_ai') ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:16px;height:16px;background:#4ade80;border-radius:4px;"></div>
                            <span><?= $__('human_written_hl') ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($seo)): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-primary"><i data-lucide="search" class="me-2" style="width: 18px; height: 18px;"></i><?= $__('seo_optimization') ?></h6>
                    <span class="badge bg-primary"><?= $__('score') ?> <?= isset($seo['score']) ? round($seo['score'], 1) : (isset($seo['content_score']) ? round($seo['content_score'], 1) : 'N/A') ?></span>
                </div>
                <div class="card-body p-4">
                    
                    <div class="d-flex flex-column gap-2">
                        <!-- Cấu trúc nội dung -->
                        <div class="border rounded-3 overflow-hidden" x-data="{ open: true }">
                            <button @click="open = !open" class="w-100 d-flex justify-content-between align-items-center bg-light border-0 p-3 text-start">
                                <span class="fw-bold small text-muted text-uppercase"><?= $__('content_structure') ?></span>
                                <i data-lucide="chevron-down" style="width: 16px; height: 16px;" class="transition-transform" :style="open ? 'transform: rotate(180deg)' : 'transform: rotate(0deg)'"></i>
                            </button>
                            <div x-show="open" x-collapse>
                                <div class="p-3 border-top bg-white">
                                    <div class="row g-2">
                                        <div class="col-4">
                                            <div class="p-2 bg-light rounded-3 text-center border">
                                                <div class="extra-small text-muted mb-1"><?= $__('word') ?></div>
                                                <div class="fw-bold text-dark"><?= $seo['word_count_range']['current'] ?? 0 ?></div>
                                                <div class="extra-small text-secondary"><?= $seo['word_count_range']['min'] ?? 0 ?> - <?= $seo['word_count_range']['max'] ?? 0 ?></div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2 bg-light rounded-3 text-center border">
                                                <div class="extra-small text-muted mb-1"><?= $__('heading') ?></div>
                                                <div class="fw-bold text-dark"><?= $seo['heading_count_range']['current'] ?? 0 ?></div>
                                                <div class="extra-small text-secondary"><?= $seo['heading_count_range']['min'] ?? 0 ?> - <?= $seo['heading_count_range']['max'] ?? 0 ?></div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2 bg-light rounded-3 text-center border">
                                                <div class="extra-small text-muted mb-1"><?= $__('paragraph') ?></div>
                                                <div class="fw-bold text-dark"><?= $seo['paragraph_count_range']['current'] ?? 0 ?></div>
                                                <div class="extra-small text-secondary"><?= $seo['paragraph_count_range']['min'] ?? 0 ?> - <?= $seo['paragraph_count_range']['max'] ?? 0 ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gợi ý Tối ưu -->
                        <?php if (!empty($seo['suggestions']) || !empty($seo['geo_suggestions'])): ?>
                        <div class="border rounded-3 overflow-hidden" x-data="{ open: false }">
                            <button @click="open = !open" class="w-100 d-flex justify-content-between align-items-center bg-light border-0 p-3 text-start">
                                <span class="fw-bold small text-muted text-uppercase"><?= $__('optimization_suggestions') ?></span>
                                <i data-lucide="chevron-down" style="width: 16px; height: 16px;" class="transition-transform" :style="open ? 'transform: rotate(180deg)' : 'transform: rotate(0deg)'"></i>
                            </button>
                            <div x-show="open" x-collapse>
                                <div class="p-3 border-top bg-white">
                                    <?php if (!empty($seo['geo_suggestions'])): ?>
                                    <div class="mb-3">
                                        <h6 class="fw-bold extra-small text-muted mb-2 text-uppercase"><?= $__('geo_suggestions') ?></h6>
                                        <ul class="mb-0 ps-3 small text-secondary">
                                            <?php foreach ($seo['geo_suggestions'] as $sugg): ?>
                                                <li class="mb-1"><?= htmlspecialchars($sugg) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($seo['suggestions'])): ?>
                                    <div>
                                        <h6 class="fw-bold extra-small text-muted mb-2 text-uppercase"><?= $__('seo_suggestions') ?></h6>
                                        <ul class="mb-0 ps-3 small text-secondary">
                                            <?php foreach ($seo['suggestions'] as $sugg): ?>
                                                <li class="mb-1"><?= htmlspecialchars($sugg) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Từ khóa tiềm năng -->
                        <?php if (!empty($seo['keyword_seeds'])): ?>
                        <div class="border rounded-3 overflow-hidden" x-data="{ open: false }">
                            <button @click="open = !open" class="w-100 d-flex justify-content-between align-items-center bg-light border-0 p-3 text-start">
                                <span class="fw-bold small text-muted text-uppercase"><?= $__('potential_keywords') ?></span>
                                <i data-lucide="chevron-down" style="width: 16px; height: 16px;" class="transition-transform" :style="open ? 'transform: rotate(180deg)' : 'transform: rotate(0deg)'"></i>
                            </button>
                            <div x-show="open" x-collapse>
                                <div class="p-3 border-top bg-white">
                                    <div class="custom-scroll pe-2" style="max-height: 250px; overflow-y: auto;">
                                        <div class="d-grid gap-2">
                                            <?php foreach ($seo['keyword_seeds'] as $kw): ?>
                                                <div class="p-2 bg-light rounded-3 border">
                                                    <div class="d-flex justify-content-between extra-small fw-bold mb-1">
                                                        <span class="text-truncate" style="max-width: 70%;" title="<?= htmlspecialchars($kw['keyword'] ?? '') ?>"><?= htmlspecialchars($kw['keyword'] ?? '') ?></span>
                                                        <span class="<?= ($kw['current'] ?? 0) >= ($kw['min'] ?? 0) ? 'text-success' : 'text-danger' ?>">
                                                            <?= $kw['current'] ?? 0 ?> / <?= $kw['min'] ?? 0 ?>-<?= $kw['max'] ?? 0 ?>
                                                        </span>
                                                    </div>
                                                    <div class="progress" style="height: 4px;">
                                                        <?php 
                                                            $max = $kw['max'] ?? 1; 
                                                            if($max == 0) $max = 1;
                                                            $pct = min(100, (($kw['current'] ?? 0) / $max) * 100); 
                                                        ?>
                                                        <div class="progress-bar <?= ($kw['current'] ?? 0) >= ($kw['min'] ?? 0) ? 'bg-success' : 'bg-primary' ?>"
                                                             style="width:<?= $pct ?>%"></div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Đối thủ cạnh tranh -->
                        <?php if (!empty($seo['competitors'])): ?>
                        <div class="border rounded-3 overflow-hidden" x-data="{ open: false }">
                            <button @click="open = !open" class="w-100 d-flex justify-content-between align-items-center bg-light border-0 p-3 text-start">
                                <span class="fw-bold small text-muted text-uppercase"><?= $__('competitors') ?></span>
                                <i data-lucide="chevron-down" style="width: 16px; height: 16px;" class="transition-transform" :style="open ? 'transform: rotate(180deg)' : 'transform: rotate(0deg)'"></i>
                            </button>
                            <div x-show="open" x-collapse>
                                <div class="p-3 border-top bg-white">
                                    <div class="custom-scroll pe-2" style="max-height: 400px; overflow-y: auto;">
                                        <div class="d-flex flex-column gap-2">
                                            <?php foreach ($seo['competitors'] as $comp): ?>
                                                <div class="p-3 border rounded-3 bg-light">
                                                    <div class="d-flex justify-content-between align-items-start mb-1 gap-2">
                                                        <a href="<?= htmlspecialchars($comp['url'] ?? '#') ?>" target="_blank" class="fw-bold text-primary small text-decoration-none">
                                                            <?= htmlspecialchars($comp['title'] ?? $comp['url'] ?? '') ?>
                                                        </a>
                                                        <span class="badge bg-secondary flex-shrink-0"><?= $__('rank') ?> #<?= $comp['search_engine_rank'] ?? 0 ?></span>
                                                    </div>
                                                    <a href="<?= htmlspecialchars($comp['url'] ?? '#') ?>" target="_blank" class="extra-small text-muted mb-2 text-truncate d-block text-decoration-none hover-primary">
                                                        <?= htmlspecialchars($comp['url'] ?? '') ?>
                                                    </a>
                                                    <div class="extra-small text-secondary mb-2">
                                                        <?= htmlspecialchars($comp['snippet'] ?? '') ?>
                                                    </div>
                                                    <div class="extra-small fw-bold <?= ($comp['content_score'] ?? 0) >= 50 ? 'text-success' : 'text-warning' ?>">
                                                        <?= $__('seo_score') ?> <?= round((float)($comp['content_score'] ?? 0), 1) ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($read['readability'])): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><?= $__('readability_caps') ?></h6>
                    <span class="badge bg-info text-white"><?= $__('score') ?> <?= $read['readability']['fleschReadingEase'] ?? 'N/A' ?></span>
                </div>
                <div class="card-body p-4">
                    <!-- Text Statistics -->
                    <h6 class="extra-small fw-bold text-muted mb-2"><?= $__('text_stats') ?></h6>
                    <div class="row g-2 text-center mb-3">
                        <div class="col-4">
                            <div class="bg-light p-2 rounded-3">
                                <div class="extra-small text-muted"><?= $__('unique_words') ?></div>
                                <div class="h6 fw-black text-dark m-0"><?= $read['text_stats']['uniqueWordCount'] ?? 0 ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-light p-2 rounded-3">
                                <div class="extra-small text-muted"><?= $__('sentences') ?></div>
                                <div class="h6 fw-black text-dark m-0"><?= $read['text_stats']['sentenceCount'] ?? 0 ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-light p-2 rounded-3">
                                <div class="extra-small text-muted"><?= $__('paragraphs') ?></div>
                                <div class="h6 fw-black text-dark m-0"><?= $read['text_stats']['paragraphCount'] ?? 0 ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-light p-2 rounded-3">
                                <div class="extra-small text-muted"><?= $__('reading_time') ?></div>
                                <div class="h6 fw-black text-dark m-0"><?= ceil($read['text_stats']['averageReadingTime'] ?? 0) ?>p</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-light p-2 rounded-3">
                                <div class="extra-small text-muted"><?= $__('speaking_time') ?></div>
                                <div class="h6 fw-black text-dark m-0"><?= ceil($read['text_stats']['averageSpeakingTime'] ?? 0) ?>p</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-light p-2 rounded-3">
                                <div class="extra-small text-muted"><?= $__('writing_time') ?></div>
                                <div class="h6 fw-black text-dark m-0"><?= ceil($read['text_stats']['averageWritingTime'] ?? 0) ?>p</div>
                            </div>
                        </div>
                    </div>

                    <!-- Metrics -->
                    <h6 class="extra-small fw-bold text-muted mb-2"><?= $__('eval_metrics') ?></h6>
                    <div class="d-flex flex-column gap-1">
                        <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded-3">
                            <span class="extra-small fw-bold">Flesch-Kincaid Reading Ease</span>
                            <span class="badge bg-primary"><?= $read['readability']['fleschReadingEase'] ?? 'N/A' ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded-3">
                            <span class="extra-small fw-bold">Flesch-Kincaid Grade Level</span>
                            <span class="badge bg-secondary">Grade <?= $read['readability']['fleschGradeLevel'] ?? 'N/A' ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded-3">
                            <span class="extra-small fw-bold">Gunning Fog Index</span>
                            <span class="badge bg-secondary"><?= $read['readability']['gunningFoxIndex'] ?? 'N/A' ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded-3">
                            <span class="extra-small fw-bold">Dale-Chall Readability</span>
                            <span class="badge bg-secondary">Grade <?= $read['readability']['daleChallReadabilityGrade'] ?? 'N/A' ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded-3">
                            <span class="extra-small fw-bold">Smog Index</span>
                            <span class="badge bg-secondary"><?= $read['readability']['smogIndex'] ?? 'N/A' ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($plag)): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><?= $__('plag_sources') ?></h6>
                    <?php if ($plagScore > 0): ?>
                    <span class="badge bg-warning text-dark"><?= $plagScore ?>%</span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($plag['results'])): ?>
                        <!-- Các trang web phù hợp -->
                        <div class="px-3 py-2 bg-light border-bottom fw-bold text-secondary text-uppercase small d-flex align-items-center gap-2">
                            <i data-lucide="globe" style="width: 16px; height: 16px;"></i> <?= $__('matching_websites') ?> (<?= count($plagiarismSources) ?>)
                        </div>
                        <div class="d-flex flex-column">
                            <?php $srcIndex = 0; foreach ($plagiarismSources as $src): $srcIndex++; 
                                $modified = $src['timestamps']['date_modified'] ?? null;
                                $published = $src['timestamps']['date_published'] ?? null;
                                if ($modified) $modified = substr($modified, 0, 10);
                                if ($published) $published = substr($published, 0, 10);
                            ?>
                                <div class="border-bottom" x-data="{ open: false }">
                                    <button @click="open = !open" class="w-100 d-flex justify-content-between align-items-start bg-transparent border-0 p-3 text-start hover-light">
                                        <div class="d-flex gap-3" style="min-width: 0; flex: 1;">
                                            <div class="fs-4 fw-black text-secondary opacity-50 mt-1"><?= $srcIndex ?></div>
                                            <div style="min-width: 0; flex: 1;">
                                                <div class="fw-bold text-dark mb-1 text-truncate" :class="open ? '' : 'text-truncate'"><?= htmlspecialchars($src['link']) ?></div>
                                                <span class="badge bg-danger"><?= $src['score'] ?>%</span>
                                            </div>
                                        </div>
                                        <i data-lucide="chevron-down" style="width: 16px; height: 16px;" class="text-muted transition-transform mt-1 flex-shrink-0" :style="open ? 'transform: rotate(180deg)' : 'transform: rotate(0deg)'"></i>
                                    </button>
                                    <div x-show="open" x-collapse>
                                        <div class="p-3 pt-0 ms-5 ps-2 border-start mb-3">
                                            <div class="mb-3">
                                                <span class="extra-small fw-bold text-uppercase text-secondary">Website</span>
                                                <a href="<?= htmlspecialchars($src['link']) ?>" target="_blank" class="d-block small text-primary text-decoration-none hover-primary"><?= htmlspecialchars($src['link']) ?></a>
                                            </div>
                                            
                                            <?php if ($modified || $published): ?>
                                                <div class="mb-3 p-2 bg-light rounded-3 small">
                                                    <div class="fw-bold mb-1"><?= $__('page_details') ?></div>
                                                    <?php if ($modified): ?>
                                                        <div class="text-secondary"><?= $__('page_modified') ?> <span class="text-dark"><?= htmlspecialchars($modified) ?></span></div>
                                                    <?php endif; ?>
                                                    <?php if ($published): ?>
                                                        <div class="text-secondary"><?= $__('page_published') ?> <span class="text-dark"><?= htmlspecialchars($published) ?></span></div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                        
                                            <div>
                                                <span class="extra-small fw-bold text-uppercase text-secondary"><?= $__('matching_phrases') ?> (<?= count($src['phrases']) ?>)</span>
                                                <div class="d-flex flex-column gap-2 mt-1">
                                                    <?php foreach ($src['phrases'] as $pIndex => $phrase): ?>
                                                        <div class="p-2 bg-light rounded-3 border small text-secondary">
                                                            <span class="fw-bold text-dark me-1"><?= $pIndex + 1 ?>.</span>
                                                            <?= htmlspecialchars($phrase) ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Cụm từ trùng lặp -->
                        <div class="px-3 py-2 bg-light border-bottom fw-bold text-secondary text-uppercase small d-flex align-items-center gap-2">
                            <i data-lucide="align-left" style="width: 16px; height: 16px;"></i> <?= $__('duplicate_phrases') ?> (<?= count($plag['results']) ?>)
                        </div>

                        <?php $plagIndex = 0; foreach ($plag['results'] as $phraseResult): $plagIndex++;
                            $phrase = $phraseResult['phrase'] ?? '';
                            $sources = $phraseResult['results'] ?? $phraseResult['sources'] ?? [];
                            $sourceCount = count($sources);
                        ?>
                        <div class="border-bottom" x-data="{ open: false }">
                            <button @click="open = !open" class="w-100 d-flex justify-content-between align-items-start bg-transparent border-0 p-3 text-start hover-light">
                                <div class="d-flex gap-3" style="min-width: 0; flex: 1;">
                                    <div class="fs-4 fw-black text-secondary opacity-50 mt-1"><?= $plagIndex ?></div>
                                    <div style="min-width: 0; flex: 1;">
                                        <div class="fw-bold text-dark mb-2 lh-base fs-6" :class="open ? '' : 'text-truncate'"><?= htmlspecialchars($phrase) ?></div>
                                        <span class="badge bg-warning text-dark"><?= $sourceCount ?> <?= $__('duplicate_sources') ?></span>
                                    </div>
                                </div>
                                <i data-lucide="chevron-down" style="width: 16px; height: 16px;" class="text-muted transition-transform mt-1 flex-shrink-0" :style="open ? 'transform: rotate(180deg)' : 'transform: rotate(0deg)'"></i>
                            </button>

                            <div x-show="open" x-collapse>
                                <div class="p-3 pt-0 ms-5 ps-2 border-start mb-3">
                                    <div class="d-flex flex-column gap-3 mt-2">
                                        <?php foreach ($sources as $src):
                                            $sUrl   = $src['url']   ?? $src['link']  ?? '';
                                            $sTitle = $src['title'] ?? $src['name']  ?? $sUrl;
                                            $sScore = isset($src['scores'][0]['score']) ? round($src['scores'][0]['score'] * 100) : 0;
                                            $sMatch = $src['scores'][0]['sentence'] ?? '';
                                        ?>
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="badge bg-danger"><?= $sScore ?>%</span>
                                                <a href="<?= htmlspecialchars($sUrl) ?>" target="_blank" class="fw-bold text-primary text-decoration-none text-truncate" title="<?= htmlspecialchars($sTitle) ?>">
                                                    <?= htmlspecialchars($sTitle) ?>
                                                </a>
                                            </div>
                                            <a href="<?= htmlspecialchars($sUrl) ?>" target="_blank" class="extra-small text-muted text-truncate d-block mb-1 text-decoration-none hover-primary">
                                                <?= htmlspecialchars($sUrl) ?>
                                            </a>
                                            <?php if ($sMatch): ?>
                                            <div class="p-2 bg-light rounded-3 small text-secondary fst-italic border">
                                                "<?= htmlspecialchars($sMatch) ?>"
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <div class="p-4">
                        <p class="text-success small mb-0 fw-bold">✓ <?= $__('no_plagiarism') ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($facts)): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i data-lucide="shield-check" class="me-2"></i><?= $__('fact_check') ?>
                    </h6>
                    <div class="d-flex gap-2">
                        <span class="badge bg-secondary rounded-pill"><?= $__('total') ?> <?= $factsTotal ?></span>
                        <?php if ($factsErrors > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?= $factsErrors ?> <?= $__('errors_suspects') ?></span>
                        <?php else: ?>
                        <span class="badge bg-success rounded-pill"><?= $__('all_correct') ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php $factIndex = 0; foreach ($facts as $fact): $factIndex++;
                        $claim       = $fact['claim']          ?? '';
                        $explanation = $fact['explanation']    ?? '';
                        $classification = strtolower(trim($fact['classification'] ?? 'unknown'));
                        $confidence  = $fact['confidence']     ?? null;

                        $clsColor = match($classification) {
                            'true'       => 'success',
                            'false'      => 'danger',
                            'unverified' => 'warning',
                            default      => 'secondary',
                        };

                        if ($confidence !== null) {
                            $confFloat = (float) $confidence;
                            $confPct   = $confFloat <= 1 ? round($confFloat * 100) : round($confFloat);
                        } else {
                            $confPct = null;
                        }
                        
                        $rawClass = ucfirst($fact['classification'] ?? 'Unknown');
                        $badgeText = ($confPct !== null ? $confPct . '% ' : '') . 'Potentially ' . $rawClass;
                    ?>
                    <div class="border-bottom" x-data="{ open: false }">
                        <button @click="open = !open" class="w-100 d-flex justify-content-between align-items-start bg-transparent border-0 p-3 text-start hover-light">
                            <div class="d-flex gap-3" style="min-width: 0; flex: 1;">
                                <div class="fs-4 fw-black text-secondary opacity-50 mt-1"><?= $factIndex ?></div>
                                <div style="min-width: 0; flex: 1;">
                                    <?php if ($claim): ?>
                                    <div class="fw-bold text-dark mb-2 lh-base fs-6" :class="open ? '' : 'text-truncate'"><?= htmlspecialchars($claim) ?></div>
                                    <?php endif; ?>
                                    <span class="badge bg-<?= $clsColor ?>"><?= htmlspecialchars($badgeText) ?></span>
                                </div>
                            </div>
                            <i data-lucide="chevron-down" style="width: 16px; height: 16px;" class="text-muted transition-transform mt-1 flex-shrink-0" :style="open ? 'transform: rotate(180deg)' : 'transform: rotate(0deg)'"></i>
                        </button>

                        <div x-show="open" x-collapse>
                            <div class="p-3 pt-0 ms-5 ps-2 border-start mb-3">
                                <?php if ($explanation): ?>
                                <div class="small text-muted mb-2"><?= htmlspecialchars($explanation) ?></div>
                                <?php endif; ?>

                                <?php 
                                $rewrite = $fact['rewrite'] ?? '';
                                if ($rewrite): ?>
                                <div class="mt-2 p-2 rounded-2" style="font-size:0.85rem;color:#0056b3;background:#f0f7ff;border-left:3px solid #0056b3;">
                                    <strong>💡 <?= $__('rewrite_suggestion') ?></strong><br>
                                    <span class="fst-italic">"<?= htmlspecialchars($rewrite) ?>"</span>
                                </div>
                                <?php endif; ?>

                                <?php
                                $sources = $fact['sources'] ?? [];
                                if (!empty($sources)): ?>
                                <div class="mt-3 pt-2 border-top border-secondary border-opacity-25">
                                    <div class="extra-small fw-bold text-muted mb-2"><?= $__('references') ?> (<?= count($sources) ?>)</div>
                                    <div class="d-flex flex-column gap-2">
                                        <?php foreach (array_slice($sources, 0, 5) as $src):
                                            $sUrl   = $src['url']   ?? $src['link']  ?? '';
                                            $sTitle = $src['title'] ?? $src['name']  ?? $sUrl;
                                            $sDate  = $src['date']  ?? '';
                                        ?>
                                        <?php if ($sUrl): ?>
                                        <div class="d-flex align-items-start gap-2">
                                            <i data-lucide="link" class="text-primary mt-1 flex-shrink-0" style="width: 14px; height: 14px;"></i>
                                            <div class="text-truncate">
                                                <a href="<?= htmlspecialchars($sUrl) ?>" target="_blank"
                                                   class="extra-small text-primary text-decoration-none fw-medium d-block text-truncate" title="<?= htmlspecialchars($sTitle) ?>">
                                                    <?= htmlspecialchars($sTitle) ?>
                                                </a>
                                                <?php if ($sDate): ?>
                                                <div class="extra-small text-muted mt-1"><?= htmlspecialchars($sDate) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<style>
.transition-transform {
    transition: transform 0.3s ease;
}
.hover-light:hover {
    background-color: var(--bs-light) !important;
}
.cursor-pointer {
    cursor: pointer !important;
}
</style>