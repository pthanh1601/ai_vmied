<div class="modal fade" id="modalNapTien" tabindex="-1" aria-labelledby="modalNapTienLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden;">

            <!-- Header -->
            <div class="modal-header border-0 px-4 pt-4 pb-0 align-items-start">
                <div>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-1 mb-2 d-inline-flex align-items-center gap-1">
                        <i data-lucide="shield-check" width="12"></i> Thanh toán an toàn
                    </span>
                    <h5 class="fw-bolder text-dark mb-0" id="modalNapTienLabel">Nạp VMIED</h5>
                    <p class="text-secondary small mb-0">Tỷ lệ quy đổi 1:1 • Không phí ẩn</p>
                </div>
                <button type="button" class="btn-close ms-auto mt-1" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 pt-3 pb-4">
                <?php if (session()->has('deposit_error')): ?>
                <div class="alert alert-danger alert-dismissible rounded-4 mb-3 d-flex align-items-center gap-2 small" role="alert">
                    <i data-lucide="alert-circle" width="16"></i>
                    <?= session('deposit_error') ?>
                    <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                <?php if (session()->has('deposit_success')): ?>
                <div class="alert alert-success alert-dismissible rounded-4 mb-3 d-flex align-items-center gap-2 small" role="alert">
                    <i data-lucide="check-circle" width="16"></i>
                    <?= session('deposit_success') ?>
                    <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <form action="/app/account/deposit" method="POST" id="formNapTien">
                    <?= csrf_field() /* hoặc: <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"> */ ?>

                    <!-- Hidden: giá trị được JS ghi vào -->
                    <input type="hidden" name="amount"  id="input-amount"  value="50000">
                    <input type="hidden" name="method"  id="input-method"  value="qr">

                    <div class="row g-4">

                        <!-- LEFT: Chọn gói + Phương thức -->
                        <div class="col-lg-7">

                            <!-- Chọn gói -->
                            <p class="text-secondary small fw-bold text-uppercase mb-2">
                                <i data-lucide="wallet" width="13" class="me-1"></i> Chọn gói nạp
                            </p>
                            <div class="row g-2 mb-3">
                                <?php
                                $packages = [
                                    ['value' => 20000,   'label' => '20k'],
                                    ['value' => 50000,   'label' => '50k'],
                                    ['value' => 100000,  'label' => '100k'],
                                    ['value' => 200000,  'label' => '200k'],
                                    ['value' => 500000,  'label' => '500k'],
                                    ['value' => 1000000, 'label' => '1 Triệu'],
                                ];
                                foreach ($packages as $pkg): ?>
                                <div class="col-4">
                                    <button type="button"
                                        class="btn btn-pkg w-100 py-2 rounded-4 fw-bold border bg-white shadow-sm <?= $pkg['value'] === 50000 ? 'active' : '' ?>"
                                        data-value="<?= $pkg['value'] ?>"
                                        onclick="napTienSelectAmount(<?= $pkg['value'] ?>, this)">
                                        <span class="d-block fs-6"><?= $pkg['label'] ?></span>
                                        <span class="d-block" style="font-size:10px;color:#aaa;font-weight:400;">VNĐ</span>
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Custom Amount -->
                            <div class="input-group mb-3 rounded-4 border overflow-hidden shadow-sm bg-white">
                                <span class="input-group-text bg-white border-0 ps-3 text-secondary">
                                    <i data-lucide="pencil" width="15"></i>
                                </span>
                                <input type="number" id="modal-custom-amount"
                                    class="form-control border-0 shadow-none fw-bold fs-6 ps-1 text-dark"
                                    placeholder="Nhập số tiền khác..."
                                    min="10000" step="1000"
                                    oninput="napTienCustomAmount(this.value)">
                                <span class="input-group-text bg-white border-0 pe-3 fw-bold text-secondary">VNĐ</span>
                            </div>

                            <!-- Phương thức -->
                            <p class="text-secondary small fw-bold text-uppercase mb-2 mt-1">
                                <i data-lucide="credit-card" width="13" class="me-1"></i> Phương thức
                            </p>
                            <div class="row g-2 mb-3">
                                <div class="col-4">
                                    <button type="button" onclick="napTienSelectMethod('qr', this)"
                                        class="btn btn-method active w-100 p-3 rounded-4 border bg-white d-flex flex-column align-items-center gap-2">
                                        <i data-lucide="qr-code" width="20"></i>
                                        <span style="font-size:12px;" class="fw-bold">Chuyển khoản</span>
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button type="button" onclick="napTienSelectMethod('momo', this)"
                                        class="btn btn-method w-100 p-3 rounded-4 border bg-white d-flex flex-column align-items-center gap-2">
                                        <i data-lucide="smartphone" width="20" class="text-danger"></i>
                                        <span style="font-size:12px;" class="fw-bold">Ví MoMo</span>
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button type="button" onclick="napTienSelectMethod('card', this)"
                                        class="btn btn-method w-100 p-3 rounded-4 border bg-white d-flex flex-column align-items-center gap-2">
                                        <i data-lucide="credit-card" width="20" class="text-primary"></i>
                                        <span style="font-size:12px;" class="fw-bold">Thẻ quốc tế</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Detail: QR / Chuyển khoản -->
                            <div id="modal-detail-qr" class="method-detail bg-light rounded-4 p-3 border">
                                <div class="d-flex gap-3 align-items-start">
                                    <div class="bg-white p-2 rounded-3 border shadow-sm text-center flex-shrink-0">
                                        <i data-lucide="qr-code" width="64" height="64" class="text-dark d-block"></i>
                                        <div class="text-success fw-bold mt-1" style="font-size:9px; letter-spacing:.5px;">QUÉT ĐỂ THANH TOÁN</div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="text-secondary small fw-bold text-uppercase mb-1">Vietcombank</p>
                                        <div class="input-group input-group-sm mb-2 rounded-3 overflow-hidden border bg-white">
                                            <input type="text" class="form-control bg-white border-0 fw-bold font-monospace" value="0071000123456" readonly>
                                            <button type="button" class="btn btn-white border-0 text-primary bg-white px-3"
                                                onclick="navigator.clipboard.writeText('0071000123456').then(()=>this.innerHTML='<i data-lucide=\'check\' width=\'14\'></i>').catch(()=>{})">
                                                <i data-lucide="copy" width="14"></i>
                                            </button>
                                        </div>
                                        <p class="text-dark fw-bold small mb-2 bg-white px-2 py-1 rounded-2 d-inline-block border">VIEN NCPT GIAO DUC VIET MY</p>
                                        <div>
                                            <p class="text-secondary small fw-bold text-uppercase mb-1">Nội dung CK</p>
                                            <div class="input-group input-group-sm rounded-3 overflow-hidden border bg-white">
                                                <input type="text" class="form-control bg-white border-0 fw-bold font-monospace" id="noi-dung-ck" value="NAP <?= strtoupper($user->username ?? 'USER') ?>" readonly>
                                                <button type="button" class="btn btn-white border-0 text-primary bg-white px-3"
                                                    onclick="navigator.clipboard.writeText(document.getElementById('noi-dung-ck').value)">
                                                    <i data-lucide="copy" width="14"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 p-2 bg-warning bg-opacity-10 text-warning-emphasis rounded-3 border border-warning-subtle d-flex gap-2 align-items-start">
                                    <i data-lucide="alert-triangle" width="14" class="flex-shrink-0 mt-1"></i>
                                    <span style="font-size:11px;"><strong>Ghi đúng nội dung chuyển khoản</strong> để hệ thống tự động cộng tiền sau 1–3 phút.</span>
                                </div>
                            </div>

                            <!-- Detail: MoMo -->
                            <div id="modal-detail-momo" class="method-detail d-none bg-danger bg-opacity-10 rounded-4 p-4 border border-danger-subtle text-center">
                                <i data-lucide="smartphone" width="36" class="text-danger mb-2 d-block mx-auto"></i>
                                <p class="fw-bold text-danger mb-1">Thanh toán qua Ví MoMo</p>
                                <p class="text-danger opacity-75 small mb-3">Ứng dụng MoMo sẽ mở sau khi xác nhận.</p>
                            </div>

                            <!-- Detail: Card -->
                            <div id="modal-detail-card" class="method-detail d-none bg-white rounded-4 p-3 border shadow-sm">
                                <div class="mb-3">
                                    <label class="form-label text-secondary small fw-bold text-uppercase mb-1">Số thẻ</label>
                                    <div class="input-group rounded-3 overflow-hidden border">
                                        <span class="input-group-text bg-light border-0"><i data-lucide="credit-card" width="15"></i></span>
                                        <input type="text" name="card_number" class="form-control border-0 shadow-none font-monospace" placeholder="0000 0000 0000 0000">
                                    </div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label text-secondary small fw-bold text-uppercase mb-1">Hết hạn</label>
                                        <input type="text" name="card_expiry" class="form-control bg-light border-0 rounded-3 font-monospace" placeholder="MM/YY">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-secondary small fw-bold text-uppercase mb-1">CVC</label>
                                        <input type="text" name="card_cvc" class="form-control bg-light border-0 rounded-3 font-monospace" placeholder="123">
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label text-secondary small fw-bold text-uppercase mb-1">Chủ thẻ</label>
                                    <input type="text" name="card_name" class="form-control bg-light border-0 rounded-3 text-uppercase" placeholder="TEN IN TREN THE">
                                </div>
                            </div>

                        </div>

                        <!-- RIGHT: Summary + Submit -->
                        <div class="col-lg-5">
                            <div class="bg-dark rounded-4 p-4 text-white d-flex flex-column justify-content-between" style="min-height: 320px;">
                                <div>
                                    <p class="text-white-50 small fw-bold text-uppercase mb-3 d-flex align-items-center gap-2">
                                        <i data-lucide="receipt" width="14"></i> Hóa đơn
                                    </p>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-white-50 small">Gói nạp</span>
                                        <span class="fw-bold small" id="modal-label-amount">50.000 ₫</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-white-50 small">Phí giao dịch</span>
                                        <span class="fw-bold small text-success">MIỄN PHÍ</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-white-50 small">Phương thức</span>
                                        <span class="fw-bold small" id="modal-label-method">Chuyển khoản</span>
                                    </div>
                                    <hr class="border-white border-opacity-10 my-3">
                                    <div class="d-flex justify-content-between align-items-end mb-1">
                                        <span class="text-white-50 small fw-bold text-uppercase">Tổng</span>
                                        <span class="fs-4 fw-bolder" id="modal-label-total">50.000 ₫</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-white-50 small fw-bold text-uppercase">Nhận được</span>
                                        <span class="fw-bold text-info" id="modal-label-vmied">50.000 <small class="opacity-75">V</small></span>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-4 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-lg mb-3">
                                        Xác nhận thanh toán
                                        <i data-lucide="arrow-right" width="18"></i>
                                    </button>
                                    <div class="text-center d-flex align-items-center justify-content-center gap-1 opacity-40">
                                        <i data-lucide="lock" width="11"></i>
                                        <span style="font-size:10px;">Mã hóa SSL 256-bit</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div><!-- /row -->
                </form>

            </div><!-- /modal-body -->
        </div>
    </div>
</div>


<!-- ===== CSS ===== -->
<style>
.btn-pkg, .btn-method {
    transition: all .15s ease;
}
.btn-pkg:hover, .btn-method:hover {
    border-color: #0d6efd !important;
}
.btn-pkg.active, .btn-method.active {
    border-color: #0d6efd !important;
    background-color: #e8f0fe !important;
    box-shadow: 0 0 0 3px rgba(13,110,253,.12) !important;
}
.btn-pkg.active span,
.btn-method.active span,
.btn-method.active i {
    color: #0d6efd !important;
}
.method-detail {
    transition: opacity .2s ease;
}
</style>


<!-- ===== JS ===== -->
<script>
(function () {
    const methodNames = { qr: 'Chuyển khoản', momo: 'Ví MoMo', card: 'Thẻ quốc tế' };

    function fmt(v) {
        return Number(v).toLocaleString('vi-VN') + ' ₫';
    }

    function updateSummary() {
        const amount = parseInt(document.getElementById('input-amount').value) || 0;
        const method = document.getElementById('input-method').value;

        document.getElementById('modal-label-amount').textContent = fmt(amount);
        document.getElementById('modal-label-total').textContent  = fmt(amount);
        document.getElementById('modal-label-method').textContent = methodNames[method] || method;
        document.getElementById('modal-label-vmied').innerHTML    =
            Number(amount).toLocaleString('vi-VN') + ' <small class="opacity-75">V</small>';
    }

    window.napTienSelectAmount = function (val, btn) {
        document.getElementById('input-amount').value = val;
        document.getElementById('modal-custom-amount').value = '';
        document.querySelectorAll('.btn-pkg').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        updateSummary();
    };

    window.napTienCustomAmount = function (val) {
        const v = parseInt(val) || 0;
        document.getElementById('input-amount').value = v;
        document.querySelectorAll('.btn-pkg').forEach(b => b.classList.remove('active'));
        updateSummary();
    };

    window.napTienSelectMethod = function (method, btn) {
        document.getElementById('input-method').value = method;
        document.querySelectorAll('.btn-method').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        ['qr', 'momo', 'card'].forEach(m => {
            document.getElementById('modal-detail-' + m)
                ?.classList.toggle('d-none', m !== method);
        });
        updateSummary();
        if (typeof lucide !== 'undefined') lucide.createIcons();
    };

    // Validate trước khi submit
    document.getElementById('formNapTien')?.addEventListener('submit', function (e) {
        const amount = parseInt(document.getElementById('input-amount').value) || 0;
        if (amount < 10000) {
            e.preventDefault();
            const inp = document.getElementById('modal-custom-amount');
            inp.classList.add('is-invalid');
            inp.focus();
            inp.placeholder = 'Tối thiểu 10.000 ₫';
            setTimeout(() => inp.classList.remove('is-invalid'), 2500);
        }
    });

    // Mở lại modal nếu server trả về lỗi
    <?php if (session()->has('deposit_error')): ?>
    document.addEventListener('DOMContentLoaded', function () {
        var modal = new bootstrap.Modal(document.getElementById('modalNapTien'));
        modal.show();
    });
    <?php endif; ?>

    // Re-init lucide khi modal hiển thị xong
    document.getElementById('modalNapTien')?.addEventListener('shown.bs.modal', function () {
        if (typeof lucide !== 'undefined') lucide.createIcons();
        updateSummary();
    });
})();
</script>