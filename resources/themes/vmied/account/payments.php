<?php $this->extend('layouts/app') ?>

<?php $this->section('content') ?>
<main class="container py-5 mt-5">
<style>
.method-active, .amount-active {
    border-color: #0d6efd !important;
    background-color: #e7f1ff !important;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25) !important;
    transition: all 0.2s ease-in-out;
}
.amount-active {
    color: #0d6efd !important;
}
.btn-pkg {
    transition: all 0.2s ease-in-out;
}
</style>
        
        <div class="row g-5">
            <!-- LEFT COLUMN: Payment Configuration -->
            <div class="col-lg-8">
                <div class="mb-5">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-2 mb-3">
                        <i data-lucide="shield-check" width="14" class="me-1"></i> Thanh toán an toàn
                    </span>
                    <h1 class="display-5 fw-bolder text-dark mb-2">Nạp VMIED</h1>
                    <p class="text-secondary fs-5">Nạp tiền nhanh chóng, tự động quy đổi 1:1.</p>
                </div>

                <!-- 1. Select Amount -->
                <section class="mb-5">
                    <h5 class="fw-bold text-dark mb-4 d-flex align-items-center gap-2">
                        <span class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center fs-6 shadow-sm" style="width: 32px; height: 32px;">1</span>
                        Chọn gói nạp
                    </h5>
                <div class="glass-card rounded-5 p-4 p-md-5">
                    <div class="row g-3 mb-4">
                        <!-- Pre-defined Amounts -->
                        <div class="col-6 col-md-4">
                            <button onclick="setAmount(this.dataset.value, this)" class="btn btn-pkg w-100 py-3 rounded-4 fw-bold border bg-white shadow-sm h-100" data-value="20000">
                                <span class="d-block fs-5 mb-1">20k</span>
                                <span class="d-block small text-secondary fw-normal">VNĐ</span>
                            </button>
                        </div>
                        <div class="col-6 col-md-4">
                            <button onclick="setAmount(this.dataset.value, this)" class="btn btn-pkg w-100 py-3 rounded-4 fw-bold border bg-white shadow-sm h-100" data-value="50000">
                                <span class="d-block fs-5 mb-1">50k</span>
                                <span class="d-block small text-secondary fw-normal">VNĐ</span>
                            </button>
                        </div>
                        <div class="col-6 col-md-4">
                            <button onclick="setAmount(this.dataset.value, this)" class="btn btn-pkg w-100 py-3 rounded-4 fw-bold border bg-white shadow-sm h-100" data-value="100000">
                                <span class="d-block fs-5 mb-1">100k</span>
                                <span class="d-block small text-secondary fw-normal">VNĐ</span>
                            </button>
                        </div>
                        <div class="col-6 col-md-4">
                            <button onclick="setAmount(this.dataset.value, this)" class="btn btn-pkg w-100 py-3 rounded-4 fw-bold border bg-white shadow-sm h-100" data-value="200000">
                                <span class="d-block fs-5 mb-1">200k</span>
                                <span class="d-block small text-secondary fw-normal">VNĐ</span>
                            </button>
                        </div>
                        <div class="col-6 col-md-4">
                            <button onclick="setAmount(this.dataset.value, this)" class="btn btn-pkg w-100 py-3 rounded-4 fw-bold border bg-white shadow-sm h-100" data-value="500000">
                                <span class="d-block fs-5 mb-1">500k</span>
                                <span class="d-block small text-secondary fw-normal">VNĐ</span>
                            </button>
                        </div>
                        <div class="col-6 col-md-4">
                            <button onclick="setAmount(this.dataset.value, this)" class="btn btn-pkg w-100 py-3 rounded-4 fw-bold border bg-white shadow-sm h-100" data-value="1000000">
                                <span class="d-block fs-5 mb-1">1 Triệu</span>
                                <span class="d-block small text-secondary fw-normal">VNĐ</span>
                            </button>
                        </div>
                
                        <!-- Custom Input -->
                        <div class="col-12 mt-2">
                            <div class="input-group-custom d-flex align-items-center">
                                <div class="px-3 text-secondary"><i data-lucide="pencil" width="18"></i></div>
                                <input type="number" id="custom-amount" oninput="setAmount(parseInt(this.value) || 0, this)" class="form-control border-0 shadow-none fw-bold fs-5 ps-0 text-dark" placeholder="Nhập số tiền khác...">
                                <span class="fw-bold text-secondary pe-3">VNĐ</span>
                            </div>
                        </div>
                    </div>
                
                    <!-- Conversion Display -->
                    <div class="bg-primary bg-opacity-10 rounded-4 p-4 border border-primary-subtle d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white text-primary rounded-circle p-3 shadow-sm">
                                <i data-lucide="arrow-right-left" width="20"></i>
                            </div>
                            <div>
                                <p class="text-primary fw-bold text-uppercase small mb-1 ls-1">Quy đổi</p>
                                <p class="text-dark small mb-0 fw-medium opacity-75">Tỷ lệ 1:1 • Không phí ẩn</p>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="small text-secondary fw-bold text-uppercase mb-1">Nhận được</div>
                            <div class="fs-3 fw-bolder text-dark lh-1" id="vmied-receive">50.000 V</div>
                        </div>
                    </div>
                </div>
                </section>

                <!-- 2. Select Method -->
                <section>
                    <h5 class="fw-bold text-dark mb-4 d-flex align-items-center gap-2">
                        <span class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center fs-6 shadow-sm" style="width: 32px; height: 32px;">2</span>
                        Phương thức thanh toán
                    </h5>

                    <div class="glass-card rounded-5 p-4 p-md-5">
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <button onclick="selectMethod('vnpay', this)" class="btn btn-method active w-100 p-4 rounded-4 border bg-white d-flex flex-column align-items-center gap-3 h-100">
                                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                        <i data-lucide="credit-card" width="24" class="text-primary"></i>
                                    </div>
                                    <span class="fw-bold text-dark">Cổng VNPAY</span>
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button onclick="selectMethod('qr', this)" class="btn btn-method w-100 p-4 rounded-4 border bg-white d-flex flex-column align-items-center gap-3 h-100">
                                    <div class="bg-light p-3 rounded-circle">
                                        <i data-lucide="qr-code" width="24" class="text-dark"></i>
                                    </div>
                                    <span class="fw-bold text-dark">Chuyển khoản QR</span>
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button onclick="selectMethod('momo', this)" class="btn btn-method w-100 p-4 rounded-4 border bg-white d-flex flex-column align-items-center gap-3 h-100">
                                    <div class="bg-pink-50 p-3 rounded-circle">
                                        <i data-lucide="smartphone" width="24" class="text-danger"></i>
                                    </div>
                                    <span class="fw-bold text-dark">Ví Momo</span>
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button onclick="selectMethod('card', this)" class="btn btn-method w-100 p-4 rounded-4 border bg-white d-flex flex-column align-items-center gap-3 h-100">
                                    <div class="bg-blue-50 p-3 rounded-circle">
                                        <i data-lucide="globe" width="24" class="text-primary"></i>
                                    </div>
                                    <span class="fw-bold text-dark">Thẻ quốc tế</span>
                                </button>
                            </div>
                        </div>

                        <!-- Detail: VNPAY -->
                        <div id="detail-vnpay" class="payment-detail bg-white rounded-4 p-4 border border-light-subtle shadow-sm">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                    <i data-lucide="shield-check" width="24" class="text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold text-dark">Thanh toán an toàn qua cổng VNPAY</h6>
                                    <p class="mb-0 text-secondary small">Hỗ trợ ATM nội địa, thẻ quốc tế Visa/MasterCard và quét mã VNPAY-QR.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Detail: QR Code -->
                        <div id="detail-qr" class="payment-detail d-none bg-white rounded-4 p-4 border border-light-subtle shadow-sm">
                            <div class="row g-4 align-items-center">
                                <div class="col-sm-auto text-center mx-auto mx-sm-0">
                                    <div class="bg-white p-2 rounded-4 shadow border d-inline-block position-relative overflow-hidden">
                                        <div class="bg-dark text-white d-flex align-items-center justify-content-center rounded-3" style="width: 160px; height: 160px;">
                                            <i data-lucide="qr-code" width="80" height="80"></i>
                                        </div>
                                        <div class="position-absolute bottom-0 start-0 w-100 bg-success text-white text-uppercase fw-bold text-center py-1" style="font-size: 10px;">Quét để thanh toán</div>
                                    </div>
                                </div>
                                <div class="col-sm">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <p class="text-secondary small fw-bold text-uppercase mb-1">Ngân hàng</p>
                                            <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                                <img src="https://img.mservice.com.vn/momo_app_v2/img/Vietcombank.png" class="rounded-circle border" width="24" height="24" onerror="this.style.display='none'">
                                                Vietcombank <span class="badge bg-light text-dark border">VCB</span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <p class="text-secondary small fw-bold text-uppercase mb-1">Số tài khoản</p>
                                            <div class="input-group">
                                                <input type="text" class="form-control bg-light border-0 fw-bold fs-5 font-monospace text-dark" value="0071000123456" readonly>
                                                <button class="btn btn-light border-0 text-primary" onclick="alert('Đã sao chép')"><i data-lucide="copy" width="18"></i></button>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <p class="text-secondary small fw-bold text-uppercase mb-1">Chủ tài khoản</p>
                                            <p class="fw-bold text-dark mb-0 bg-light px-3 py-2 rounded-3 d-inline-block">VIEN NCPT GIAO DUC VIET MY</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 p-3 bg-warning bg-opacity-10 text-warning-emphasis rounded-3 border border-warning-subtle d-flex gap-3 align-items-start">
                                <i data-lucide="alert-triangle" width="20" class="flex-shrink-0 mt-1"></i>
                                <div class="small">
                                    <strong>Lưu ý:</strong> Hệ thống tự động xử lý. Vui lòng ghi đúng nội dung chuyển khoản để được cộng tiền sau 1-3 phút.
                                </div>
                            </div>
                        </div>

                        <!-- Detail: Momo -->
                        <div id="detail-momo" class="payment-detail d-none bg-danger bg-opacity-10 rounded-4 p-5 border border-danger-subtle text-center">
                            <div class="bg-white text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-4 shadow-sm" style="width: 80px; height: 80px;">
                                <i data-lucide="smartphone" width="40"></i>
                            </div>
                            <h4 class="fw-bold text-danger mb-2">Thanh toán qua Ví Momo</h4>
                            <p class="text-danger opacity-75 small mb-4 col-md-8 mx-auto">Hệ thống sẽ mở ứng dụng Momo trên điện thoại hoặc hiển thị mã QR Momo trên máy tính.</p>
                            <button class="btn btn-danger fw-bold rounded-pill px-5 py-3 shadow-lg hover-scale">
                                Mở ứng dụng Momo <i data-lucide="external-link" width="16" class="ms-1"></i>
                            </button>
                        </div>

                        <!-- Detail: Card -->
                        <div id="detail-card" class="payment-detail d-none bg-white rounded-4 p-4 border border-light-subtle shadow-sm">
                            <div class="row g-4 justify-content-center">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label text-secondary small fw-bold text-uppercase">Số thẻ</label>
                                        <div class="input-group-custom d-flex align-items-center">
                                            <span class="px-3 text-secondary"><i data-lucide="credit-card" width="18"></i></span>
                                            <input type="text" class="form-control border-0 shadow-none font-monospace fs-5 ps-0" placeholder="0000 0000 0000 0000">
                                            <div class="px-2 d-flex gap-1 opacity-50">
                                                <div class="bg-dark rounded-1" style="width: 20px; height: 12px;"></div>
                                                <div class="bg-warning rounded-1" style="width: 20px; height: 12px;"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <label class="form-label text-secondary small fw-bold text-uppercase">Hết hạn</label>
                                            <input type="text" class="form-control form-control-lg bg-light border-0 rounded-3 font-monospace fs-6" placeholder="MM/YY">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label text-secondary small fw-bold text-uppercase">CVC</label>
                                            <div class="position-relative">
                                                <input type="text" class="form-control form-control-lg bg-light border-0 rounded-3 font-monospace fs-6" placeholder="123">
                                                <i data-lucide="help-circle" width="16" class="position-absolute top-50 end-0 translate-middle-y me-3 text-secondary opacity-50"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label class="form-label text-secondary small fw-bold text-uppercase">Chủ thẻ</label>
                                        <input type="text" class="form-control form-control-lg bg-light border-0 rounded-3 text-uppercase fs-6" placeholder="TEN IN TREN THE">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- RIGHT COLUMN: Sticky Summary -->
            <div class="col-lg-4">
                <div class="sticky-top" style="top: 100px; z-index: 10;">
                    
                    <!-- Summary Card -->
                    <div class="glass-card rounded-5 p-4 mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom border-secondary border-opacity-10">
                            <h5 class="fw-bold text-dark mb-0">Hóa đơn</h5>
                            <span class="badge bg-light text-secondary border">Mã #INV-001</span>
                        </div>
                        
                        <div class="vstack gap-3 mb-4">
                            <div class="d-flex justify-content-between text-sm">
                                <span class="text-secondary fw-medium">Gói nạp</span>
                                <span class="fw-bold text-dark" id="summary-amount">50.000 ₫</span>
                            </div>
                            <div class="d-flex justify-content-between text-sm">
                                <span class="text-secondary fw-medium">Phí giao dịch</span>
                                <span class="fw-bold text-success bg-success bg-opacity-10 px-2 rounded-1" style="font-size: 11px;">MIỄN PHÍ</span>
                            </div>
                            <div class="d-flex justify-content-between text-sm">
                                <span class="text-secondary fw-medium">Khuyến mãi</span>
                                <span class="fw-bold text-secondary">- 0 ₫</span>
                            </div>
                        </div>

                        <div class="bg-dark rounded-4 p-4 text-white shadow-lg mb-4 position-relative overflow-hidden">
                            <div class="position-absolute top-0 end-0 p-5 bg-white opacity-10 rounded-circle translate-middle"></div>
                            <div class="position-relative z-1">
                                <div class="text-white-50 small text-uppercase fw-bold mb-1">Tổng thanh toán</div>
                                <div class="display-6 fw-bold" id="summary-total">50.000 ₫</div>
                            </div>
                        </div>

                        <button onclick="processDeposit()" class="btn btn-primary w-100 py-3 rounded-4 fw-bold shadow-lg d-flex align-items-center justify-content-center gap-2 hover-scale transition-all">
                            Xác nhận thanh toán <i data-lucide="arrow-right" width="20"></i>
                        </button>
                        
                        <div class="text-center mt-3 d-flex align-items-center justify-content-center gap-2 opacity-50">
                            <i data-lucide="lock" width="12"></i>
                            <span style="font-size: 11px;">Mã hóa SSL 256-bit an toàn</span>
                        </div>
                    </div>

                    <!-- Support Mini -->
                    <div class="bg-white bg-opacity-50 rounded-4 p-3 border border-white d-flex align-items-center gap-3 shadow-sm">
                        <div class="bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center">
                            <i data-lucide="phone" width="16"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="small fw-bold text-dark">Hỗ trợ 24/7</div>
                            <div class="text-secondary" style="font-size: 11px;">Hotline: 1900 1234</div>
                        </div>
                        <a href="#" class="btn btn-sm btn-white border shadow-sm fw-bold rounded-pill px-3">Chat</a>
                    </div>

                </div>
            </div>

        </div>
        
        <!-- Recent Transactions -->
        <div class="mt-5 pt-5 border-top border-secondary border-opacity-10">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h4 class="fw-bold text-dark mb-0">Giao dịch gần đây</h4>
                <a href="/app/historys?tab=transaction" hx-boost="true" hx-target="#app-content" hx-select="#app-content" hx-swap="outerHTML show:window:top" class="btn btn-link text-decoration-none fw-bold small">Xem tất cả <i data-lucide="chevron-right" width="14"></i></a>
            </div>
            
            <div class="glass-card rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-secondary small fw-bold text-uppercase">
                            <tr>
                                <th class="px-4 py-3 border-bottom-0">ID</th>
                                <th class="px-4 py-3 border-bottom-0">Số tiền</th>
                                <th class="px-4 py-3 border-bottom-0">Loại giao dịch</th>
                                <th class="px-4 py-3 border-bottom-0">Trạng thái</th>
                                <th class="px-4 py-3 border-bottom-0 text-end">Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($transactions)): ?>
                                <?php foreach($transactions as $t): ?>
                                <?php 
                                    $statusClass = 'bg-warning bg-opacity-10 text-warning border-warning-subtle';
                                    $statusText = 'Đang chờ xử lý';
                                    if (($t['status'] ?? 0) == 1) {
                                        $statusClass = 'bg-success bg-opacity-10 text-success border-success-subtle';
                                        $statusText = 'Thành công';
                                    } elseif (($t['status'] ?? 0) == 2) {
                                        $statusClass = 'bg-danger bg-opacity-10 text-danger border-danger-subtle';
                                        $statusText = 'Thất bại';
                                    }
                                    $typeClass = 'bg-primary-subtle text-primary';
                                    $typeText = 'Nạp tiền';
                                    $amountClass = 'text-success';
                                    $amountSign = '+';
                                    switch ($t['type'] ?? 'deposit') {
                                        case 'withdraw':
                                            $typeClass = 'bg-secondary-subtle text-secondary';
                                            $typeText = 'Rút tiền';
                                            $amountClass = 'text-dark';
                                            $amountSign = '-';
                                            break;
                                        case 'commission':
                                            $typeClass = 'bg-info-subtle text-info';
                                            $typeText = 'Hoa hồng';
                                            break;
                                    }
                                ?>
                                <tr>
                                    <td class="px-4 py-3 text-secondary font-monospace small">#<?= !empty($t['code']) ? $t['code'] : 'N/A' ?></td>
                                    <td class="px-4 py-3 fw-bold <?= $amountClass ?>"><?= $amountSign ?> <?= number_format($t['display_amount']) ?> ₫</td>
                                    <td class="px-4 py-3">
                                        <span class="badge <?= $typeClass ?> border rounded-pill px-3"><?= $typeText ?></span>
                                    </td>
                                    <td class="px-4 py-3"><span class="badge <?= $statusClass ?> rounded-pill px-3"><?= $statusText ?></span></td>
                                    <td class="px-4 py-3 text-end text-secondary small"><?= date('H:i d/m/Y', strtotime($t['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">Chưa có giao dịch nào.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

</main>
<script src="/js/payments.js?v=12" data-page-script></script>
<?php $this->endSection() ?>