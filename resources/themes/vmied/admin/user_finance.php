<?php $this->extend('layouts/admin') ?>

<?php $this->section('content') ?>
<div class="container pt-5 mt-5 pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h3 class="fw-bold text-dark m-0">Quản lý Thanh toán & Hoa hồng VIP</h3>
            <p class="text-secondary small mb-0">Theo dõi toàn bộ dòng tiền nạp, chia sẻ hoa hồng và yêu cầu rút tiền hệ thống.</p>
        </div>

        <!-- FORM TÌM KIẾM -->
        <form method="GET" action="/admin/user-finance" class="m-0">
            <input type="hidden" name="tab" value="<?= htmlspecialchars($currentTab) ?>">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-secondary-subtle"><i data-lucide="search" width="16"></i></span>
                <input type="text" name="q" class="form-control border-secondary-subtle shadow-sm" placeholder="Tìm theo tên, email, mã..." value="<?= htmlspecialchars($q ?? '') ?>">
                <?php if(!empty($q)): ?>
                    <a href="/admin/user-finance?tab=<?= $currentTab ?>" class="btn btn-outline-secondary border-secondary-subtle"><i data-lucide="x" width="14"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Thẻ Tổng quan -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-primary text-white">
                <div class="text-white-50 small font-monospace text-uppercase fw-bold mb-1">Tổng Doanh Thu Nạp Tiền</div>
                <h2 class="fw-bold m-0"><?= number_format($totalDepositSum) ?> <span class="fs-6 font-monospace fw-normal">VNĐ</span></h2>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-warning text-dark">
                <div class="text-dark opacity-75 small font-monospace text-uppercase fw-bold mb-1">Tổng Số Đơn Vị VIP</div>
                <h2 class="fw-bold m-0"><?= number_format($totalVipsCount) ?> <span class="fs-6 font-monospace fw-normal">Đối tác</span></h2>
            </div>
        </div>
    </div>

    <!-- Tab điều hướng -->
    <div class="card border shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-light border-bottom p-3">
            <ul class="nav nav-pills card-header-pills">
                <li class="nav-item">
                    <a class="nav-link fw-bold px-3 py-2 <?= $currentTab === 'transactions' ? 'active' : '' ?>" href="/admin/user-finance?tab=transactions">
                        <i data-lucide="receipt" width="16" class="me-1"></i> Tất cả GD Nạp tiền
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold px-3 py-2 <?= $currentTab === 'commissions' ? 'active' : '' ?>" href="/admin/user-finance?tab=commissions">
                        <i data-lucide="wallet" width="16" class="me-1"></i> Lịch sử Hoa hồng VIP
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold px-3 py-2 <?= $currentTab === 'withdrawals' ? 'active' : '' ?>" href="/admin/user-finance?tab=withdrawals">
                        <i data-lucide="arrow-up-right" width="16" class="me-1"></i> Lịch sử Rút tiền VIP
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body p-0">
            <!-- DỮ LIỆU NẠP TIỀN -->
            <?php if($currentTab === 'transactions'): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 small fw-bold text-secondary">Mã GD</th>
                                <th class="px-4 py-3 small fw-bold text-secondary">Tài khoản nạp</th>
                                <th class="px-4 py-3 small fw-bold text-secondary">Số tiền</th>
                                <th class="px-4 py-3 small fw-bold text-secondary">Trạng thái</th>
                                <th class="px-4 py-3 small fw-bold text-secondary text-end">Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($transactions)): ?>
                                <?php foreach($transactions as $t): ?>
                                <tr>
                                    <td class="px-4 py-3 font-monospace fw-bold">#<?= htmlspecialchars($t['code'] ?? $t['id']) ?></td>
                                    <td class="px-4 py-3">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($t['user_name'] ?? 'N/A') ?></div>
                                        <div class="small text-secondary"><?= htmlspecialchars($t['user_email'] ?? '') ?></div>
                                    </td>
                                    <td class="px-4 py-3 fw-bold text-success">+<?= number_format($t['amount'] ?? 0) ?> VNĐ</td>
                                    <td class="px-4 py-3">
                                        <?php if(($t['status'] ?? 0) == 1): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Thành công</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">Chờ xử lý</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-end small text-secondary"><?= $t['created_at'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-5 text-secondary">Chưa có dữ liệu nạp tiền.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <!-- DỮ LIỆU HOA HỒNG VIP -->
            <?php elseif($currentTab === 'commissions'): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 small fw-bold text-secondary">ID</th>
                                <th class="px-4 py-3 small fw-bold text-secondary">Tài khoản VIP</th>
                                <th class="px-4 py-3 small fw-bold text-secondary">Số tiền nhận</th>
                                <th class="px-4 py-3 small fw-bold text-secondary">Nội dung</th>
                                <th class="px-4 py-3 small fw-bold text-secondary text-end">Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($affiliateHistory)): ?>
                                <?php foreach($affiliateHistory as $w): ?>
                                <tr>
                                    <td class="px-4 py-3 font-monospace">#<?= $w['id'] ?></td>
                                    <td class="px-4 py-3">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($w['user_name'] ?? 'N/A') ?></div>
                                        <div class="small text-secondary"><?= htmlspecialchars($w['organization'] ?? $w['user_email']) ?></div>
                                    </td>
                                    <td class="px-4 py-3 fw-bold text-success">+<?= number_format($w['amount'] ?? 0) ?> VNĐ</td>
                                    <td class="px-4 py-3 small">
                                        Mã GD: <span class="font-monospace fw-bold text-dark"><?= htmlspecialchars($w['code'] ?? 'N/A') ?></span>
                                        <br><span class="text-secondary opacity-75" style="font-size: 11px;">Loại: <?= htmlspecialchars($w['type'] ?? 'Hoa hồng') ?></span>
                                    </td>
                                    <!-- Đã sửa dòng dưới đây, xóa bỏ "></td>" bị dư -->
                                    <td class="px-4 py-3 text-end small text-secondary"><?= $w['date'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-5 text-secondary">Chưa có lịch sử hoa hồng.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <!-- DỮ LIỆU RÚT TIỀN VIP -->
            <?php elseif($currentTab === 'withdrawals'): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 small fw-bold text-secondary">Mã Rút</th>
                                <th class="px-4 py-3 small fw-bold text-secondary">Đối tác VIP</th>
                                <th class="px-4 py-3 small fw-bold text-secondary">Số tiền rút</th>
                                <th class="px-4 py-3 small fw-bold text-secondary">Ngân hàng</th>
                                <th class="px-4 py-3 small fw-bold text-secondary">Trạng thái</th>
                                <th class="px-4 py-3 small fw-bold text-secondary text-end">Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($withdrawHistory)): ?>
                                <?php foreach($withdrawHistory as $wd): ?>
                                <tr>
                                    <td class="px-4 py-3 font-monospace">#<?= $wd['id'] ?></td>
                                    <td class="px-4 py-3">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($wd['user_name'] ?? 'N/A') ?></div>
                                        <div class="small text-secondary"><?= htmlspecialchars($wd['organization'] ?? $wd['user_email']) ?></div>
                                    </td>
                                    <td class="px-4 py-3 fw-bold text-danger">-<?= number_format($wd['amount'] ?? 0) ?> VNĐ</td>
                                    <td class="px-4 py-3 small">
                                        <strong><?= htmlspecialchars($wd['bank_name'] ?? 'N/A') ?></strong><br>
                                        <span class="font-monospace text-secondary"><?= htmlspecialchars($wd['bank_account'] ?? '') ?></span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php if(($wd['status'] ?? 0) == 1): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Đã duyệt</span>
                                        <?php elseif(($wd['status'] ?? 0) == 2): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Từ chối</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">Đang chờ duyệt</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-end small text-secondary"><?= $wd['created_at'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center py-5 text-secondary">Chưa có yêu cầu rút tiền.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- PHÂN TRANG -->
        <?php if (isset($totalPages) && $totalPages > 1): ?>
        <div class="card-footer bg-white border-top p-3 d-flex justify-content-center">
            <?php $queryStr = '&tab=' . $currentTab . (!empty($q) ? '&q=' . urlencode($q) : ''); ?>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?><?= $queryStr ?>"><i data-lucide="chevron-left" width="16"></i></a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?><?= $queryStr ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?><?= $queryStr ?>"><i data-lucide="chevron-right" width="16"></i></a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    if (typeof lucide !== 'undefined') lucide.createIcons();
</script>
<?php $this->endSection() ?>