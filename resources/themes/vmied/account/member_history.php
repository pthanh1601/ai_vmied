<?php $this->extend('layouts/app') ?>

<?php $this->section('content') ?>
<div class="container pt-5 mt-5 pb-5">
    
    <!-- Tiêu đề & Nút Quay lại -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="/app/members" class="btn btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i data-lucide="arrow-left" width="20"></i>
            </a>
            <div>
                <h3 class="fw-bold text-dark m-0">Lịch sử của Học viên</h3>
                <p class="text-secondary small mb-0"><?= htmlspecialchars($member['name']) ?> (<?= htmlspecialchars($member['email']) ?>)</p>
            </div>
        </div>

        <!-- Ô THỐNG KÊ TỔNG HOA HỒNG TỪ HỌC VIÊN NÀY -->
        <div class="card border-0 shadow-sm rounded-4 px-4 py-3 bg-success bg-opacity-10 text-success border border-success-subtle">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-success text-white p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                    <i data-lucide="wallet" width="20"></i>
                </div>
                <div>
                    <div class="small fw-bold text-uppercase opacity-75" style="font-size: 11px;">Hoa Hồng Đã Nhận</div>
                    <div class="fs-5 fw-bold text-success">+ <?= number_format($totalCommission, 0, ',', '.') ?> VNĐ</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- BẢNG LỊCH SỬ QUÉT BÀI -->
        <div class="col-lg-12">
            <h5 class="fw-bold mb-3"><i data-lucide="file-text" width="20" class="text-primary me-2"></i> 50 Bài Quét Gần Nhất</h5>
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 small fw-bold text-secondary">Tài liệu</th>
                                <th class="px-4 py-3 small fw-bold text-secondary">Kết quả / Điểm</th>
                                <th class="px-4 py-3 small fw-bold text-secondary">Số từ</th>
                                <th class="px-4 py-3 small fw-bold text-secondary text-end">Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($scans)): ?>
                                <?php foreach($scans as $scan): ?>
                                    <tr>
                                        <td class="px-4 py-3 fw-medium text-dark">
                                            <?= htmlspecialchars($scan['title'] ?: 'Không có tiêu đề') ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <?php if($scan['plag_score'] > 0): ?>
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1"><i data-lucide="file-warning" width="12"></i> Đạo văn: <?= (float)$scan['plag_score'] ?>%</span>
                                            <?php elseif($scan['ai_score'] > 0): ?>
                                                <span class="badge bg-primary-subtle text-primary px-2 py-1"><i data-lucide="bot" width="12"></i> Human: <?= round(100 - $scan['ai_score'], 1) ?>%</span>
                                            <?php elseif($scan['grammar_errors'] > 0): ?>
                                                <span class="badge bg-warning-subtle text-warning px-2 py-1"><i data-lucide="spell-check" width="12"></i> Lỗi: <?= $scan['grammar_errors'] ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-secondary px-2 py-1">Khác</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 font-monospace small"><?= number_format($scan['word_count']) ?> từ</td>
                                        <td class="px-4 py-3 text-end text-secondary small"><?= date('H:i d/m/Y', strtotime($scan['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">Học viên này chưa quét bài nào.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- BẢNG LỊCH SỬ NẠP TIỀN / TRỪ ĐIỂM -->
        <div class="col-lg-12 mt-5">
            <h5 class="fw-bold mb-3"><i data-lucide="credit-card" width="20" class="text-success me-2"></i> Lịch Sử Giao Dịch</h5>
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 small fw-bold text-secondary">Mã GD</th>
                                <th class="px-4 py-3 small fw-bold text-secondary">Nội dung</th>
                                <th class="px-4 py-3 small fw-bold text-secondary text-end">Biến động</th>
                                <th class="px-4 py-3 small fw-bold text-secondary text-end">Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($transactions)): ?>
                                <?php foreach($transactions as $t): ?>
                                    <tr>
                                        <td class="px-4 py-3 font-monospace small">#<?= htmlspecialchars($t['code'] ?: $t['id']) ?></td>
                                        <td class="px-4 py-3 text-dark small"><?= htmlspecialchars($t['note'] ?: 'Giao dịch hệ thống') ?></td>
                                        <td class="px-4 py-3 text-end fw-bold">
                                            <?php 
                                                $displayAmount = $t['vmied'] ?? $t['amount']; 
                                                if(in_array($t['type'], ['deposit', 'commission'])): 
                                            ?>
                                                <span class="text-success">+<?= number_format(abs($displayAmount), 0, ',', '.') ?> V</span>
                                            <?php else: ?>
                                                <span class="text-danger">-<?= number_format(abs($displayAmount), 0, ',', '.') ?> V</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 text-end text-secondary small"><?= date('H:i d/m/Y', strtotime($t['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">Chưa có giao dịch nào.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    if (typeof lucide !== 'undefined') lucide.createIcons();
</script>
<?php $this->endSection() ?>