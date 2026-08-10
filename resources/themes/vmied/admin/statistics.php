<?php $this->extend('layouts/admin') ?>

<?php $this->section('content') ?>
    <div class="container pt-5 mt-5 pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-dark m-0">Thống kê Tài chính & Vận hành</h3>
            <button class="btn btn-light bg-white border shadow-sm rounded-pill fw-medium d-flex align-items-center gap-2 px-3" onclick="window.location.reload()">
                <i data-lucide="refresh-cw" width="16"></i> Làm mới
            </button>
        </div>

        <!-- DÒNG 1: THỐNG KÊ DOANH THU & CHI PHÍ DỊCH VỤ (SaaS) -->
        <h5 class="fw-bold text-secondary mb-3 mt-2"><i data-lucide="pie-chart" width="18" class="me-1 mb-1"></i> Hiệu quả kinh doanh AI</h5>
        <div class="row g-4 mb-5">
            <!-- Doanh thu tính của khách -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 100%);">
                    <div class="card-body p-4 text-white position-relative z-1">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="mb-1 text-white-50 fw-bold text-uppercase small" style="letter-spacing: 0.5px;">Tổng thu từ khách</p>
                                <h2 class="fw-bolder mb-0"><?= number_format($totalRevenue ?? 0) ?> ₫</h2>
                            </div>
                            <div class="bg-white bg-opacity-25 p-2 rounded-3">
                                <i data-lucide="arrow-down-left" width="24" class="text-white"></i>
                            </div>
                        </div>
                        <p class="mb-0 small text-white-50">Tổng số điểm (VND) đã trừ từ ví người dùng qua các lượt quét.</p>
                    </div>
                    <i data-lucide="coins" class="position-absolute text-white opacity-10" style="width: 120px; height: 120px; right: -20px; bottom: -20px; transform: rotate(-15deg);"></i>
                </div>
            </div>

            <!-- Chi phí trả cho API -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                    <div class="card-body p-4 text-white position-relative z-1">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="mb-1 text-white-50 fw-bold text-uppercase small" style="letter-spacing: 0.5px;">Chi phí API (Gốc)</p>
                                <h2 class="fw-bolder mb-0"><?= number_format($totalCost ?? 0) ?> ₫</h2>
                            </div>
                            <div class="bg-white bg-opacity-25 p-2 rounded-3">
                                <i data-lucide="arrow-up-right" width="24" class="text-white"></i>
                            </div>
                        </div>
                        <p class="mb-0 small text-white-50">Tổng tiền vốn phải trả cho các bên cung cấp API (OpenAI, Copyleaks).</p>
                    </div>
                    <i data-lucide="cpu" class="position-absolute text-white opacity-10" style="width: 120px; height: 120px; right: -20px; bottom: -20px; transform: rotate(-15deg);"></i>
                </div>
            </div>

            <!-- Lợi nhuận -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <div class="card-body p-4 text-white position-relative z-1">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="mb-1 text-white-50 fw-bold text-uppercase small" style="letter-spacing: 0.5px;">Lợi nhuận dịch vụ</p>
                                <h2 class="fw-bolder mb-0"><?= number_format($totalProfit ?? 0) ?> ₫</h2>
                            </div>
                            <div class="bg-white bg-opacity-25 p-2 rounded-3">
                                <i data-lucide="trending-up" width="24" class="text-white"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <?php 
                                $rev = $totalRevenue ?? 0;
                                $profit = $totalProfit ?? 0;
                                $profitMargin = $rev > 0 ? round(($profit / $rev) * 100, 1) : 0;
                            ?>
                            <span class="badge bg-white text-success fw-bold">Biên LN: <?= $profitMargin ?>%</span>
                            <span class="small text-white-50">(Tiền thu trừ đi Chi phí API)</span>
                        </div>
                    </div>
                    <i data-lucide="banknote" class="position-absolute text-white opacity-10" style="width: 120px; height: 120px; right: -20px; bottom: -20px; transform: rotate(-15deg);"></i>
                </div>
            </div>
        </div>

        <!-- DÒNG 2: DÒNG TIỀN NẠP VÀ VẬN HÀNH -->
        <h5 class="fw-bold text-secondary mb-3"><i data-lucide="activity" width="18" class="me-1 mb-1"></i> Dòng tiền & Vận hành</h5>
        <div class="row g-4 mb-5">
            
            <!-- Tổng nạp thực tế -->
            <div class="col-md-6">
                <div class="card border shadow-sm rounded-4 h-100 bg-white">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle d-flex align-items-center justify-content-center">
                                <i data-lucide="wallet" width="24"></i>
                            </div>
                            <div>
                                <p class="text-secondary fw-bold text-uppercase small mb-0">Dòng tiền nạp thực tế</p>
                                <h3 class="fw-bold text-dark mb-0"><?= number_format($totalDeposit ?? 0) ?> ₫</h3>
                            </div>
                        </div>
                        <p class="text-muted small mb-0 mt-3 pt-3 border-top">Tổng số tiền mặt (VND) khách đã chuyển khoản thành công vào hệ thống.</p>
                    </div>
                </div>
            </div>

            <!-- Tổng lượt quét -->
            <div class="col-md-6">
                <div class="card border shadow-sm rounded-4 h-100 bg-white">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle d-flex align-items-center justify-content-center">
                                <i data-lucide="file-search" width="24"></i>
                            </div>
                            <div>
                                <p class="text-secondary fw-bold text-uppercase small mb-0">Tổng lượt kiểm tra</p>
                                <h3 class="fw-bold text-dark mb-0"><?= number_format($totalScans ?? 0) ?> <span class="fs-6 text-muted fw-normal">lượt</span></h3>
                            </div>
                        </div>
                        <p class="text-muted small mb-0 mt-3 pt-3 border-top">Số lượng tài liệu đã quét AI & Đạo văn thành công trên toàn hệ thống.</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- BẢNG CHI TIẾT LỊCH SỬ QUÉT -->
        <h5 class="fw-bold text-secondary mb-3"><i data-lucide="list" width="18" class="me-1 mb-1"></i> Lịch sử Quét Toàn Hệ Thống</h5>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-secondary small text-uppercase">
                            <tr>
                                <th class="px-4 py-3" style="border-top-left-radius: 1rem;">Thời gian</th>
                                <th class="py-3">Thành viên</th>
                                <th class="py-3">Tài liệu / Dịch vụ</th>
                                <th class="py-3 text-center">Số từ</th>
                                <th class="py-3 text-end text-danger">Chi phí API (Gốc)</th>
                                <th class="px-4 py-3 text-end text-success" style="border-top-right-radius: 1rem;">Đã trừ User</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($histories)): ?>
                                <?php foreach ($histories as $h): ?>
                                <tr>
                                    <td class="px-4 py-3 border-bottom-0 border-top">
                                        <span class="d-block fw-medium text-dark"><?= date('d/m/Y', strtotime($h['created_at'])) ?></span>
                                        <span class="small text-muted"><?= date('H:i:s', strtotime($h['created_at'])) ?></span>
                                    </td>
                                    <td class="py-3 border-bottom-0 border-top">
                                        <span class="d-block fw-bold text-dark"><?= htmlspecialchars($h['name']) ?></span>
                                        <span class="small text-muted"><?= htmlspecialchars($h['email']) ?></span>
                                    </td>
                                    <td class="py-3 border-bottom-0 border-top">
                                        <span class="d-block fw-medium text-dark text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($h['title']) ?>">
                                            <?= htmlspecialchars($h['title'] ?: 'Tài liệu không tên') ?>
                                        </span>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary mt-1">
                                            <?= strtoupper($h['type'] ?? 'TEXT') ?>
                                        </span>
                                    </td>
                                    <td class="py-3 text-center fw-medium border-bottom-0 border-top">
                                        <?= number_format($h['word_count'] ?? 0) ?>
                                    </td>
                                    <td class="py-3 text-end fw-bold text-danger border-bottom-0 border-top">
                                        <!-- Chi phí vốn gọi API (cột capitalCost) -->
                                        $<?= number_format($h['capitalCost'] ?? 0, 4) ?>
                                    </td>
                                    <td class="px-4 py-3 text-end fw-bolder text-success border-bottom-0 border-top">
                                        <!-- Số VMIED thực tế đã trừ của User (cột points_used) -->
                                        <?= number_format($h['points_used'] ?? 0) ?> <span class="small fw-normal">V</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted border-bottom-0 border-top">
                                        <i data-lucide="inbox" class="mb-2 opacity-50" width="32" height="32"></i>
                                        <p class="mb-0">Chưa có lịch sử quét nào trên hệ thống.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (isset($totalPages) && $totalPages > 1): ?>
                <div class="card-footer bg-white border-top p-3 d-flex justify-content-center">
                    <nav aria-label="Điều hướng trang">
                        <ul class="pagination pagination-sm mb-0">
                            <!-- Nút Quay lại (Trang trước) -->
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link px-3" href="?page=<?= $page - 1 ?>" <?= ($page <= 1) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                    <i data-lucide="chevron-left" width="16"></i>
                                </a>
                            </li>
                            
                            <!-- Xử lý hiển thị số trang -->
                            <?php 
                            $startPage = max(1, $page - 2);
                            $endPage   = min($totalPages, $page + 2);
                            
                            // Nếu cách xa trang đầu, hiện trang 1 và dấu ...
                            if ($startPage > 1) {
                                echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                                if ($startPage > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                            }
        
                            // In các trang ở giữa
                            for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a class="page-link fw-medium" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; 
                            
                            // Nếu cách xa trang cuối, hiện dấu ... và trang cuối
                            if ($endPage < $totalPages) {
                                if ($endPage < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="?page='.$totalPages.'">'.$totalPages.'</a></li>';
                            }
                            ?>
                            
                            <!-- Nút Đi tiếp (Trang sau) -->
                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                <a class="page-link px-3" href="?page=<?= $page + 1 ?>" <?= ($page >= $totalPages) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                    <i data-lucide="chevron-right" width="16"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();
    </script>
<?php $this->endSection() ?>