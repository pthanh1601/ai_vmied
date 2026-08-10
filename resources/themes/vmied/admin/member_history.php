<?php $this->extend('layouts/admin') ?>

<?php $this->section('content') ?>
<div class="container pt-5 mt-5">
    <div class="mb-4">
        <a href="javascript:void(0);" 
           onclick="window.history.length > 1 ? window.history.back() : window.location.href='/admin/members'" 
           class="btn btn-light rounded-pill border fw-bold text-secondary mb-3 d-inline-flex align-items-center gap-2">
            <i data-lucide="arrow-left" width="16"></i> Quay lại danh sách
        </a>
        <h3 class="fw-bold text-dark m-0"><?= $title ?></h3>
    </div>

    <div class="card border shadow-sm" style="border-radius: 1.5rem;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light border-bottom">
                    <tr>
                        <th class="px-4 py-3 text-secondary small fw-bold">Tài liệu</th>
                        <th class="px-4 py-3 text-secondary small fw-bold text-center">AI</th>
                        <th class="px-4 py-3 text-secondary small fw-bold text-center">Đạo văn</th>
                        <th class="px-4 py-3 text-secondary small fw-bold text-center">Ngữ pháp</th>
                        <th class="px-4 py-3 text-secondary small fw-bold text-center">Đọc hiểu</th>
                        <th class="px-4 py-3 text-secondary small fw-bold text-center">Chi phí</th>
                        <th class="px-4 py-3 text-secondary small fw-bold text-end">Thời gian</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if(!empty($history)): ?>
                        <?php foreach($history as $item): ?>
                        <tr>
                            <td class="px-4 py-3">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($item['title'] ?: 'Bản quét không tên') ?></div>
                                <div class="small text-muted"><span class="badge bg-light text-secondary border me-1"><?= strtoupper($item['type']) ?></span> <?= number_format($item['word_count']) ?> từ</div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if($item['ai_score'] !== null): ?>
                                    <span class="badge <?= $item['ai_score'] >= 50 ? 'bg-danger' : 'bg-success' ?>"><?= $item['ai_score'] ?>%</span>
                                <?php else: ?> - <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if($item['plag_score'] !== null): ?>
                                    <span class="badge <?= $item['plag_score'] > 0 ? 'bg-warning text-dark' : 'bg-success' ?>"><?= $item['plag_score'] ?>%</span>
                                <?php else: ?> - <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if($item['grammar_errors'] !== null): ?>
                                    <span class="badge <?= $item['grammar_errors'] > 0 ? 'bg-danger' : 'bg-success' ?>"><?= $item['grammar_errors'] ?> lỗi</span>
                                <?php else: ?> - <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?= $item['readability_score'] ? '<span class="text-info fw-bold">'.$item['readability_score'].'</span>' : '-' ?>
                            </td>
                            <td class="px-4 py-3 text-center fw-bold text-danger">
                                <?= number_format($item['points_used']) ?> ₫
                            </td>
                            <td class="px-4 py-3 text-end text-secondary small">
                                <?= date('H:i d/m/Y', strtotime($item['created_at'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-5 text-secondary">Người dùng này chưa có bản quét nào.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
<?php $this->endSection() ?>