<?php
$currentActive = $active ?? '';
$isAllInOne = ($currentActive === 'ai'); // Chỉ kích hoạt chức năng Checkbox khi ở trang All-in-One

$cards = [
    ['id' => 'check_ai', 'link' => '/app/ai', 'title' => 'AI Detection', 'desc' => 'Phát hiện ChatGPT, Gemini, Claude...', 'icon' => 'bot', 'color' => 'primary', 'active_key' => 'ai'],
    ['id' => 'check_plagiarism', 'link' => '/app/ai', 'title' => 'Check Đạo văn', 'desc' => 'Quét trùng lặp từ 10M+ nguồn', 'icon' => 'copy', 'color' => 'warning', 'active_key' => 'plagiarism'],
    ['id' => 'check_grammar', 'link' => '/app/ai', 'title' => 'Check Ngữ pháp', 'desc' => 'Sửa lỗi chính tả & cấu trúc câu', 'icon' => 'spell-check', 'color' => 'success', 'active_key' => 'grammar'],
    ['id' => 'check_readability', 'link' => '/app/ai', 'title' => 'Readability', 'desc' => 'Đánh giá độ dễ đọc', 'icon' => 'book-open', 'color' => 'info', 'active_key' => 'readability'],
    // ['id' => 'check_facts', 'link' => '/app/ai', 'title' => 'Kiểm tra Sự thật', 'desc' => 'Sự kiện & số liệu', 'icon' => 'alert-triangle', 'color' => 'danger', 'active_key' => 'facts'],
    // ['id' => 'check_contentOptimizer', 'link' => '/app/ai', 'title' => 'SEO Optimizer', 'desc' => 'Tối ưu nội dung', 'icon' => 'search', 'color' => 'primary', 'active_key' => 'seo'],
];
?>
<div class="container pt-5 mt-5 py-4 space-y-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-end gap-3">
        <div>
            <h1 class="display-6 fw-bold text-dark mb-1">
                Xin chào, <span class="text-gradient"><?=$user->name?></span> 👋
            </h1>
            <p class="text-secondary small mb-0">Chọn tính năng quét bạn muốn sử dụng.</p>
        </div>
    </div>
</div>

<!-- ===== 6 CARD LỰA CHỌN ===== -->
<div class="container" <?= $isAllInOne ? 'x-data="AIConfig()"' : '' ?> id="ai-scan-config-bar">
    <div class="row g-4 py-3">
        <?php foreach ($cards as $c): ?>
            <?php 
                $color = $c['color'];
                $isCardActive = ($currentActive === $c['active_key']);
                $textColorClass = $color === 'warning' ? 'text-dark' : 'text-white';
            ?>
            <div class="col-12 col-sm-6 col-lg-3">
                <?php if ($isAllInOne): ?>
                    <button class="card h-100 rounded-4 shadow-sm overflow-hidden hover-lift w-100 text-start transition-all"
                            :class="isChecked('<?= $c['id'] ?>') ? 'border-2 border-<?= $color ?> bg-<?= $color ?>-subtle' : 'border border-light bg-white'"
                            @click.stop="toggle('<?= $c['id'] ?>')">
                <?php else: ?>
                    <a href="<?= $c['link'] ?>" hx-boost="true" hx-target="#app-content" hx-select="#app-content" hx-swap="outerHTML show:window:top"
                       class="card h-100 rounded-4 shadow-sm overflow-hidden hover-lift w-100 text-start text-decoration-none transition-all <?= $isCardActive ? "border-2 border-{$color} bg-{$color}-subtle" : "border border-light bg-white" ?>">
                <?php endif; ?>

                    <div class="position-absolute top-0 end-0 bg-<?= $color ?>-subtle rounded-circle" style="width: 6rem; height: 6rem; margin-top: -3rem; margin-right: -3rem;"></div>
                    
                    <?php if ($isAllInOne): ?>
                    <div x-show="isChecked('<?= $c['id'] ?>')" style="display: none;"
                         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-50" x-transition:enter-end="opacity-100 scale-100" 
                         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-50" 
                         class="position-absolute top-0 end-0 mt-3 me-3 z-2">
                        <div class="bg-<?= $color ?> <?= $textColorClass ?> rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 24px; height: 24px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                    </div>
                    <?php elseif ($isCardActive): ?>
                    <div class="position-absolute top-0 end-0 mt-3 me-3 z-2">
                        <div class="bg-<?= $color ?> <?= $textColorClass ?> rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 24px; height: 24px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="card-body position-relative z-1 p-4">
                        <?php if ($isAllInOne): ?>
                        <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-3 p-3 transition-colors"
                             :class="isChecked('<?= $c['id'] ?>') ? 'bg-<?= $color ?> <?= $textColorClass ?> shadow' : 'bg-<?= $color ?>-subtle text-<?= $color ?>'">
                        <?php else: ?>
                        <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-3 p-3 transition-colors <?= $isCardActive ? "bg-{$color} {$textColorClass} shadow" : "bg-{$color}-subtle text-{$color}" ?>">
                        <?php endif; ?>
                            <i data-lucide="<?= $c['icon'] ?>" style="width: 28px; height: 28px;"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1"><?= $c['title'] ?></h5>
                        <p class="text-secondary small mb-0"><?= $c['desc'] ?></p>
                    </div>

                <?php if ($isAllInOne): ?>
                    </button>
                <?php else: ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.hover-lift {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.hover-lift:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 45px rgba(0, 0, 0, 0.13) !important;
}
    .transition-all {
        transition: all 0.3s ease;
    }
    .transition-colors {
        transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
    }
</style>