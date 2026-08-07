<?php
$data  = $result ?? [];
$ai    = $data['ai']              ?? [];
$plag  = $data['plagiarism']      ?? [];
$facts = $data['facts']           ?? [];
$read  = $data['readability']     ?? [];
$gram  = $data['grammarSpelling'] ?? [];
$seo   = $data['contentOptimizer'] ?? [];

// ── AI % ─────────────────────────────────────────────────────────────
$aiPct   = $summary['ai_score'] ?? round(($ai['confidence']['AI'] ?? 0) * 100);
$origPct = 100 - $aiPct;

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
?>

<div class="p-4">
    
    <!-- Header thông tin cơ bản -->
    <div class="mb-4 pb-3 border-bottom">
        <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($title) ?></h5>
        <div class="d-flex align-items-center gap-2 text-muted small flex-wrap">
            <?php if ($createdAt): ?>
            <span><i data-lucide="clock" width="14" class="me-1"></i><?= date('H:i d/m/Y', strtotime($createdAt)) ?></span>
            <?php endif; ?>
            <span class="badge bg-light text-secondary border"><?= strtoupper($scanType) ?></span>
            <?php if ($wordCount): ?>
            <span><?= number_format($wordCount) ?> từ</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Thống kê chính -->
    <div class="row g-2 mb-4">
        <div class="col-6 col-sm-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100" style="background: #f8f9fa;">
                <div class="small fw-bold text-muted mb-1">AI Detection</div>
                <div class="h5 fw-black mb-0 <?= $aiPct >= 70 ? 'text-danger' : ($aiPct >= 40 ? 'text-warning' : 'text-success') ?>">
                    <?= $aiPct ?>%
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100" style="background: #f8f9fa;">
                <div class="small fw-bold text-muted mb-1">Đạo văn</div>
                <div class="h5 fw-black mb-0 <?= $plagScore >= 30 ? 'text-danger' : ($plagScore >= 10 ? 'text-warning' : 'text-success') ?>">
                    <?= $plagScore ?>%
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100" style="background: #f8f9fa;">
                <div class="small fw-bold text-muted mb-1">Lỗi ngữ pháp</div>
                <div class="h5 fw-black mb-0 <?= $grammarErrors > 10 ? 'text-danger' : ($grammarErrors > 0 ? 'text-warning' : 'text-success') ?>">
                    <?= $grammarErrors ?>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100" style="background: #f8f9fa;">
                <div class="small fw-bold text-muted mb-1">Readability</div>
                <div class="h5 fw-black mb-0 text-primary">
                    <?= $readScore !== null ? $readScore : '—' ?>
                </div>
                <?php if ($readGrade): ?>
                <div class="extra-small text-muted">Grade <?= $readGrade ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Phân tích AI theo câu -->
    <?php if (!empty($ai['blocks'])): ?>
    <div class="mb-4 pb-3 border-bottom">
        <h6 class="fw-bold text-primary mb-3 small">
            <i data-lucide="cpu" width="14" class="me-2"></i>PHÂN TÍCH AI TỪNG CÂU
        </h6>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="bg-light small text-uppercase text-secondary">
                    <tr>
                        <th class="text-center" width="8%">#</th>
                        <th>Nội dung câu</th>
                        <th class="text-center" width="18%">Mức độ AI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($ai['blocks'], 0, 10) as $i => $block):
                        $fakePct = round(($block['result']['fake'] ?? 0) * 100);
                        $color   = $fakePct >= 70 ? 'danger' : ($fakePct >= 40 ? 'warning' : 'success');
                    ?>
                    <tr style="font-size: 0.85rem;">
                        <td class="text-center fw-bold text-secondary"><?= $i + 1 ?></td>
                        <td class="py-2"><?= htmlspecialchars(substr($block['text'] ?? '', 0, 80)) ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= $color ?> rounded-pill"><?= $fakePct ?>%</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Lỗi ngữ pháp -->
    <?php if (!empty($gram['matches'])): ?>
    <div class="mb-4 pb-3 border-bottom">
        <h6 class="fw-bold text-primary mb-3 small">
            <i data-lucide="spell-check" width="14" class="me-2"></i>NGỮ PHÁP & CHÍNH TẢ
        </h6>
        <div class="d-flex gap-2 mb-2 flex-wrap">
            <?php if (isset($gram['grade'])): ?>
            <span class="badge bg-secondary rounded-pill small">Xếp loại: <?= htmlspecialchars($gram['grade']) ?></span>
            <?php endif; ?>
            <span class="badge bg-warning text-dark rounded-pill small"><?= $grammarErrors ?> lỗi</span>
        </div>
        <div style="max-height: 200px; overflow-y: auto;">
            <?php foreach (array_slice($gram['matches'], 0, 5) as $match): ?>
            <div class="mb-2 p-2 border rounded-2 small bg-white" style="font-size: 0.8rem;">
                <div class="fw-bold text-danger"><?= htmlspecialchars($match['shortMessage'] ?? 'Lỗi chính tả') ?></div>
                <?php if (!empty($match['message'])): ?>
                <div class="text-muted mt-1 small"><?= htmlspecialchars($match['message']) ?></div>
                <?php endif; ?>
                <?php if (!empty($match['replacements']) && is_array($match['replacements'])): 
                    $suggestions = array_filter(array_map(
                        fn($r) => htmlspecialchars($r['value'] ?? ''),
                        array_slice($match['replacements'], 0, 3)
                    ));
                ?>
                <div class="mt-2 text-success small">
                    <strong>💡 Gợi ý:</strong> <?= implode(', ', $suggestions) ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Kiểm tra sự thật -->
    <?php if (!empty($facts)): ?>
    <div class="mb-4 pb-3 border-bottom">
        <h6 class="fw-bold text-primary mb-3 small">
            <i data-lucide="shield-check" width="14" class="me-2"></i>KIỂM TRA SỰ THẬT
        </h6>
        <div class="d-flex gap-2 mb-2 flex-wrap">
            <span class="badge bg-secondary rounded-pill small">Tổng: <?= $factsTotal ?></span>
            <?php if ($factsErrors > 0): ?>
            <span class="badge bg-danger rounded-pill small"><?= $factsErrors ?> sai/nghi ngờ</span>
            <?php else: ?>
            <span class="badge bg-success rounded-pill small">Tất cả đúng</span>
            <?php endif; ?>
        </div>
        <div style="max-height: 250px; overflow-y: auto;">
            <?php foreach (array_slice($facts, 0, 8) as $fact):
                $claim       = $fact['claim']          ?? '';
                $explanation = $fact['explanation']    ?? '';
                $classification = strtolower(trim($fact['classification'] ?? 'unknown'));

                $clsColor = match($classification) {
                    'true'       => 'success',
                    'false'      => 'danger',
                    'unverified' => 'warning',
                    default      => 'secondary',
                };
                $clsLabel = match($classification) {
                    'true'       => '✓ Đúng',
                    'false'      => '✗ Sai',
                    'unverified' => '? Chưa xác minh',
                    default      => 'Không rõ',
                };
            ?>
            <div class="mb-2 p-2 border rounded-2 small bg-white <?= $classification === 'false' ? 'border-danger border-opacity-50' : '' ?>" style="font-size: 0.8rem;">
                <?php if ($claim): ?>
                <div class="fw-bold mb-1"><?= htmlspecialchars(substr($claim, 0, 100)) ?></div>
                <?php endif; ?>
                <div class="mb-1">
                    <span class="badge bg-<?= $clsColor ?> small"><?= $clsLabel ?></span>
                </div>
                <?php if ($explanation): ?>
                <div class="text-muted"><?= htmlspecialchars(substr($explanation, 0, 120)) ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- SEO -->
    <?php if (!empty($seo)): ?>
    <div class="mb-4 pb-3 border-bottom">
        <h6 class="fw-bold text-primary mb-3 small d-flex justify-content-between align-items-center">
            <span><i data-lucide="trending-up" width="14" class="me-2"></i>TỐI ƯU SEO</span>
            <span class="badge bg-primary small">Điểm: <?= $seo['score'] ?? $seo['content_score'] ?? 'N/A' ?></span>
        </h6>
        <div class="d-grid gap-2">
            <?php foreach (array_slice($seo['keyword_seeds'] ?? [], 0, 5) as $kw): ?>
                <div class="p-2 bg-light rounded-2" style="font-size: 0.8rem;">
                    <div class="d-flex justify-content-between fw-bold mb-1">
                        <span><?= htmlspecialchars($kw['keyword'] ?? '') ?></span>
                        <span class="<?= ($kw['current'] ?? 0) >= ($kw['min'] ?? 0) ? 'text-success' : 'text-danger' ?>">
                            <?= $kw['current'] ?? 0 ?>/<?= $kw['min'] ?? 0 ?>
                        </span>
                    </div>
                    <div class="progress" style="height: 3px;">
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
    <?php endif; ?>

    <!-- Readability -->
    <?php if (!empty($read['readability'])): ?>
    <div class="mb-4">
        <h6 class="fw-bold text-primary mb-3 small d-flex justify-content-between align-items-center">
            <span><i data-lucide="bar-chart-2" width="14" class="me-2"></i>ĐỌC HIỂU (READABILITY)</span>
            <span class="badge bg-info text-white small">Điểm: <?= $read['readability']['fleschReadingEase'] ?? 'N/A' ?></span>
        </h6>
        <div class="row g-2 text-center small">
            <div class="col-4">
                <div class="bg-light p-2 rounded-2">
                    <div class="extra-small text-muted">Từ độc nhất</div>
                    <div class="fw-bold text-dark"><?= $read['text_stats']['uniqueWordCount'] ?? 0 ?></div>
                </div>
            </div>
            <div class="col-4">
                <div class="bg-light p-2 rounded-2">
                    <div class="extra-small text-muted">Số câu</div>
                    <div class="fw-bold text-dark"><?= $read['text_stats']['sentenceCount'] ?? 0 ?></div>
                </div>
            </div>
            <div class="col-4">
                <div class="bg-light p-2 rounded-2">
                    <div class="extra-small text-muted">Số đoạn</div>
                    <div class="fw-bold text-dark"><?= $read['text_stats']['paragraphCount'] ?? 0 ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Footer: link chi tiết -->
    <div class="text-center pt-3 border-top">
        <a href="/app/report/ai?id=<?= (int)($history['id'] ?? 0) ?>" 
           class="btn btn-sm btn-outline-primary rounded-2 small"
           target="_blank">
            <i data-lucide="external-link" width="12" class="me-1"></i>Xem chi tiết
        </a>
    </div>

</div>
