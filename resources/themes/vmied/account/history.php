<?php if (!app()->request->isHtmx()): ?>
    <?php $this->extend('layouts/app') ?>
    <?php $this->section('content') ?>
    <?= $this->insert('ai/menu-ai',["user"=>$user, 'active' => '']) ?>
<?php endif; ?>
<div id="app-content" class="animate-fade-in" data-page-script="/js/history.js">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <main class="container py-5 mt-5">

        <!-- Header -->
        <div class="mb-5 d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
            <div>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-2 mb-3">
                    <i data-lucide="history" width="14" class="me-1"></i> Dữ liệu hệ thống
                </span>
                <h1 class="display-5 fw-bolder text-dark mb-2">Thống kê & Lịch sử</h1>
                <p class="text-secondary fs-5">Theo dõi chi tiêu và hiệu quả sử dụng công cụ AI của bạn.</p>
            </div>

            <div class="bg-light p-1 rounded-4 d-inline-flex">
                <button onclick="switchTab('usage')" id="tab-usage" class="btn btn-dark rounded-3 px-4 py-2 fw-bold btn-sm shadow-sm">
                    <i data-lucide="file-clock" width="16" class="me-2"></i>Hoạt động
                </button>
                <button onclick="switchTab('transaction')" id="tab-transaction" class="btn btn-link text-secondary text-decoration-none rounded-3 px-4 py-2 fw-bold btn-sm">
                    <i data-lucide="receipt" width="16" class="me-2"></i>Giao dịch
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="glass-card p-4 rounded-4 border-start border-4 border-primary h-100 shadow-sm">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="bg-primary bg-opacity-10 p-2 rounded-3"><i data-lucide="coins" class="text-primary"></i></div>
                        <span class="badge bg-success bg-opacity-10 text-success small">+12%</span>
                    </div>
                    <p class="text-secondary small fw-bold text-uppercase mb-1">Tổng chi tiêu</p>
                    <h2 class="fw-bold mb-0">
                        <?php
                            $total = array_sum(array_column($histories, 'credits'));
                            echo number_format($total);
                        ?> <span class="fs-6 fw-normal text-secondary">V</span>
                    </h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card p-4 rounded-4 border-start border-4 border-info h-100 shadow-sm">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="bg-info bg-opacity-10 p-2 rounded-3"><i data-lucide="files" class="text-info"></i></div>
                    </div>
                    <p class="text-secondary small fw-bold text-uppercase mb-1">Văn bản đã xử lý</p>
                    <h2 class="fw-bold mb-0"><?= count($histories) ?> <span class="fs-6 fw-normal text-secondary">file</span></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card p-4 rounded-4 border-start border-4 border-success h-100 shadow-sm">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="bg-success bg-opacity-10 p-2 rounded-3"><i data-lucide="activity" class="text-success"></i></div>
                    </div>
                    <p class="text-secondary small fw-bold text-uppercase mb-1">Độ uy tín trung bình</p>
                    <h2 class="fw-bold mb-0">94.5%</h2>
                </div>
            </div>
        </div>

        <!-- Chart -->
        <div class="glass-card rounded-5 p-4 p-md-5 mb-5 shadow-sm border border-light-subtle">
            <h5 class="fw-bold text-dark mb-4">Biểu đồ sử dụng VMIED</h5>
            <div style="height: 300px; width: 100%;">
                <canvas id="usageChartBootstrap"
                    data-labels='<?= $chartLabels ?>'
                    data-values='<?= $chartData ?>'></canvas>
            </div>
        </div>

        <!-- Tab: Hoạt động -->
        <div id="content-usage">
            <div class="glass-card rounded-5 overflow-hidden shadow-sm border border-light-subtle">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-secondary small fw-bold text-uppercase">
                            <tr>
                                <th class="px-4 py-4 border-bottom-0">Tài liệu</th>
                                <th class="px-4 py-4 border-bottom-0">Dịch vụ</th>
                                <th class="px-4 py-4 border-bottom-0">Kết quả</th>
                                <th class="px-4 py-4 border-bottom-0">Chi phí</th>
                                <th class="px-4 py-4 border-bottom-0 text-end">Thời gian</th>
                                <th class="px-4 py-4 border-bottom-0">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <?php if (!empty($histories)): ?>
                                <?php foreach ($histories as $h): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="d-flex align-items-center justify-content-center rounded-3 <?= $h['bg'] ?>" style="width: 40px; height: 40px;">
                                                <i data-lucide="<?= $h['icon'] ?>" style="width: 20px; height: 20px;"></i>
                                            </div>
                                            <span class="fw-bold text-dark"><?= $h['title'] ?></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-3">
                                            <?= $h['service'] ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="fw-bold <?= $h['class'] ?>"><?= $h['result'] ?></span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="fw-medium text-danger">- <?= number_format($h['credits']) ?> V</span>
                                    </td>
                                    <td class="px-4 py-3 text-end text-secondary small">
                                        <?= date('H:i d/m/Y', strtotime($h['created_at'])) ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                           <button type="button" 
                                                class="btn btn-light text-secondary hover-primary btn-sm rounded-3 p-2 border-0 btn-action transition-all"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#detailModal"
                                                data-id="<?= $h['id'] ?>">
                                                <i data-lucide="eye" style="width: 20px; height: 20px;"></i>
                                            </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">Không có dữ liệu lịch sử.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-4 bg-light bg-opacity-50 border-top d-flex justify-content-between align-items-center">
                    <span class="small text-secondary">Hiển thị 1-<?= count($histories) ?> kết quả</span>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled"><a class="page-link rounded-circle me-1 border-0" href="#"><i data-lucide="chevron-left" width="14"></i></a></li>
                            <li class="page-item active"><a class="page-link rounded-circle me-1 border-0" href="#">1</a></li>
                            <li class="page-item"><a class="page-link rounded-circle border-0" href="#"><i data-lucide="chevron-right" width="14"></i></a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
        <!-- /Tab: Hoạt động -->

        <!-- Tab: Giao dịch -->
        <div id="content-transaction" class="d-none">
            <div class="glass-card rounded-5 overflow-hidden shadow-sm border border-light-subtle">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-secondary small fw-bold text-uppercase">
                            <tr>
                                <th class="px-4 py-4 border-bottom-0">Mã GD</th>
                                <th class="px-4 py-4 border-bottom-0">Loại giao dịch</th>
                                <th class="px-4 py-4 border-bottom-0">Số tiền</th>
                                <th class="px-4 py-4 border-bottom-0">Trạng thái</th>
                                <th class="px-4 py-4 border-bottom-0 text-end">Thời gian</th>
                                <!--<th class="px-4 py-4 border-bottom-0">Thao tác</th>-->
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <?php if (!empty($transactions)): ?>
                                <?php foreach ($transactions as $t): ?>
                                <?php
                                    // Status
                                    $statusClass = 'bg-warning bg-opacity-10 text-warning border-warning-subtle';
                                    $statusText  = 'Đang chờ xử lý';
                                    if (($t['status'] ?? 0) == 1) {
                                        $statusClass = 'bg-success bg-opacity-10 text-success border-success-subtle';
                                        $statusText  = 'Thành công';
                                    } elseif (($t['status'] ?? 0) == 2) {
                                        $statusClass = 'bg-danger bg-opacity-10 text-danger border-danger-subtle';
                                        $statusText  = 'Thất bại';
                                    }

                                    // Type
                                    $typeClass = 'bg-primary-subtle text-primary border-primary-subtle';
                                    $typeLabel = $t['type_label'] ?? 'Nạp tiền';
                                    if (($t['type'] ?? '') == 'withdraw') {
                                        $typeClass = 'bg-secondary-subtle text-secondary border-secondary-subtle';
                                    } elseif (($t['type'] ?? '') == 'commission') {
                                        $typeClass = 'bg-info-subtle text-info border-info-subtle';
                                    }

                                    // Amount
                                    $amtClass = 'text-success';
                                    $amtSign  = '+';
                                    if (($t['type'] ?? '') == 'withdraw') {
                                        $amtClass = 'text-danger';
                                        $amtSign  = '';
                                    }
                                ?>
                                <tr>
                                    <td class="px-4 py-3 font-monospace text-secondary fw-bold small">
                                        #<?= !empty($t['code']) ? htmlspecialchars($t['code']) : 'N/A' ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge <?= $typeClass ?> border rounded-pill px-3">
                                            <?= htmlspecialchars($typeLabel) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="fw-bold <?= $amtClass ?>">
                                            <?= $amtSign ?> <?= number_format($t['display_amount'] ?? $t['amount'] ?? 0) ?> V
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge <?= $statusClass ?> border rounded-pill px-3">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-end text-secondary small">
                                        <?= date('H:i d/m/Y', strtotime($t['created_at'])) ?>
                                    </td>
                                    <!--<td class="px-4 py-3 text-center">-->
                                    <!--    <button type="button" class="btn btn-light text-secondary hover-primary btn-sm rounded-3 p-2 border-0 btn-action transition-all">-->
                                    <!--        <i data-lucide="more-horizontal" width="20" height="20"></i>-->
                                    <!--    </button>-->
                                    <!--</td>-->
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">Chưa có giao dịch nào.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-4 bg-light bg-opacity-50 border-top d-flex justify-content-between align-items-center">
                    <span class="small text-secondary">Hiển thị <?= count($transactions ?? []) ?> giao dịch</span>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled"><a class="page-link rounded-circle me-1 border-0" href="#"><i data-lucide="chevron-left" width="14"></i></a></li>
                            <li class="page-item active"><a class="page-link rounded-circle me-1 border-0" href="#">1</a></li>
                            <li class="page-item"><a class="page-link rounded-circle border-0" href="#"><i data-lucide="chevron-right" width="14"></i></a></li>
                        </ul>
                    </nav>
                </div>  
            </div>
        </div>
        <!-- /Tab: Giao dịch -->

    </main>
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
    
                <div class="modal-header bg-light border-0">
                    <h5 class="fw-bold m-0 text-dark">
                        <i data-lucide="file-text" class="text-primary me-2" width="18"></i>Chi tiết tài liệu
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
    
                <div class="modal-body p-0" id="modal-body-content">
                </div>
    
            </div>
        </div>
    </div>
</div>
<?php if (!app()->request->isHtmx()): ?>
    <?php $this->endSection() ?>
<?php endif; ?>