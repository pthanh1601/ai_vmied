    <?php $this->extend('layouts/app') ?>

    <?php $this->section('content') ?>
        <div class="container py-5 mt-5">
            <div class="row g-5">
                <!-- LEFT: SIDEBAR -->
                <div class="col-lg-3">
                    <div class="sticky-top" style="top: 100px;">
                        <div class="mb-4 px-2">
                            <h1 class="h4 fw-bold text-dark mb-1">Cài đặt</h1>
                            <p class="text-secondary small mb-0">Quản lý tài khoản của bạn</p>
                        </div>

                        <!-- Sử dụng Nav Pills của Bootstrap -->
                        <div class="nav flex-column nav-pills gap-2" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                            <button class="nav-link active d-flex align-items-center gap-3 py-3 px-4 rounded-4 text-start" 
                                    id="nav-general-tab" data-bs-toggle="pill" data-bs-target="#section-general" type="button" role="tab">
                                <i data-lucide="user-circle-2" width="20"></i> Thông tin chung
                            </button>
                            <button class="nav-link d-flex align-items-center gap-3 py-3 px-4 rounded-4 text-start" 
                                    id="nav-security-tab" data-bs-toggle="pill" data-bs-target="#section-security" type="button" role="tab">
                                <i data-lucide="shield-check" width="20"></i> Bảo mật
                            </button>
                            <button class="nav-link d-flex align-items-center gap-3 py-3 px-4 rounded-4 text-start" 
                                    id="nav-billing-tab" data-bs-toggle="pill" data-bs-target="#section-billing" type="button" role="tab">
                                <i data-lucide="wallet" width="20"></i> Ví & Thanh toán
                            </button>
                            <button class="nav-link d-flex align-items-center gap-3 py-3 px-4 rounded-4 text-start" 
                                    id="nav-notifications-tab" data-bs-toggle="pill" data-bs-target="#section-notifications" type="button" role="tab">
                                <i data-lucide="bell" width="20"></i> Thông báo
                            </button>
                        </div>

                        <div class="mt-4 p-4 rounded-4 bg-white border shadow-sm">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-2">New</span>
                            </div>
                            <h6 class="fw-bold mb-1">Affiliate Program</h6>
                            <p class="small text-secondary mb-3">Giới thiệu bạn bè, nhận hoa hồng trọn đời.</p>
                            <a hx-get="/app/affiliate" href="#" hx-target="#app-content" hx-push-url="true" class="text-dark fw-bold text-decoration-none small d-flex align-items-center gap-1">
                                Xem chi tiết <i data-lucide="arrow-right" width="14"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: CONTENT -->
                <div class="col-lg-9">
                    <div class="tab-content" id="v-pills-tabContent">
                        
                        <!-- 1. GENERAL INFO -->
                        <div class="tab-pane fade show active" id="section-general" role="tabpanel">
                            
                            <!-- Profile Header -->
                            <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-4 mb-5">
                                <div class="position-relative">
                                    <!-- Hiển thị Avatar hiện tại nếu có, ngược lại lấy ảnh mặc định -->
                                    <img id="avatar-preview" src="<?= !empty($user->avatar) ? htmlspecialchars($user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=0ea5e9&color=fff&size=128' ?>" 
                                         class="rounded-circle shadow bg-white border" width="100" height="100" style="object-fit: contain;">
                                    
                                    <!-- Chỉ hiện nút đổi ảnh nếu là VIP (type_id = 2) -->
                                    <?php if(isset($user->type_id) && $user->type_id == 2): ?>
                                        <label for="avatar-upload" class="position-absolute bottom-0 end-0 bg-white p-2 rounded-circle shadow border border-light cursor-pointer hover-lift" title="Đổi Logo Trường/Đơn vị">
                                            <i data-lucide="camera" width="16" class="text-dark"></i>
                                        </label>
                                    <?php endif; ?>
                                </div>
                                <div class="text-center text-md-start pt-2">
                                    <h2 class="fw-bold text-dark mb-1"><?=$user->name?></h2>
                                    <p class="text-secondary mb-3"><?=$user->email?></p>
                                    <div class="d-flex gap-2 justify-content-center justify-content-md-start">
                                        <span class="badge bg-light text-dark border fw-medium rounded-pill px-3 py-2"><?=$user->type?></span>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle fw-medium rounded-pill px-3 py-2 d-flex align-items-center gap-1">
                                            <div class="bg-success rounded-circle" style="width: 6px; height: 6px;"></div> Verified
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Card -->
                            <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 bg-white">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h4 class="fw-bold fs-5 mb-0">Thông tin cá nhân</h4>
                                    <button class="btn btn-link text-decoration-none p-0 text-secondary"><i data-lucide="edit-2" width="16"></i></button>
                                </div>
                                <div x-data="Form()">
                                <form 
                                    hx-post="/app/account/change-infomation" 
                                    hx-swap="none"
                                    enctype="multipart/form-data" 
                                    @htmx:before-request="startRequest()"
                                    @htmx:after-request="handleResponse($event); if(JSON.parse($event.detail.xhr.response).status === 'success') setTimeout(() => window.location.reload(), 1200);"
                                    class="d-grid gap-3"
                                    >
                                        
                                        <!-- Input File ẩn (Chỉ VIP mới có quyền submit Logo) -->
                                        <?php if(isset($user->type_id) && $user->type_id == 2): ?>
                                            <input type="file" name="avatar" id="avatar-upload" accept="image/*" class="d-none" onchange="document.getElementById('avatar-preview').src = window.URL.createObjectURL(this.files[0])">
                                        <?php endif; ?>
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-secondary small mb-2">Họ và tên</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-0 rounded-start-4 ps-3 text-secondary"><i data-lucide="user" width="18"></i></span>
                                                    <input type="text" name="name" class="form-control bg-light border-0 rounded-end-4 py-3 fw-medium text-dark" value="<?=$user->name?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-secondary small mb-2">Số điện thoại</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-0 rounded-start-4 ps-3 text-secondary"><i data-lucide="phone" width="18"></i></span>
                                                    <input type="text" name="phone" class="form-control bg-light border-0 rounded-end-4 py-3 fw-medium text-dark" value="<?=$user->phone ?? ''?>">
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-bold text-secondary small mb-2">Email đăng nhập</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-0 rounded-start-4 ps-3 text-secondary"><i data-lucide="mail" width="18"></i></span>
                                                    <input type="email" class="form-control bg-light border-0 py-3 fw-medium text-muted" value="<?=$user->email?>" disabled>
                                                    <span class="input-group-text bg-light border-0 rounded-end-4 text-secondary pe-3"><i data-lucide="lock" width="16"></i></span>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-bold text-secondary small mb-2">Tổ chức / Trường học</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-0 rounded-start-4 ps-3 text-secondary"><i data-lucide="building" width="18"></i></span>
                                                    <input type="text" name="organization" class="form-control bg-light border-0 rounded-end-4 py-3 fw-medium text-dark" value="<?=$user->organization ?? ''?>">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-5 d-flex gap-3 justify-content-end">
                                            <button type="submit" 
                                                :disabled="isLoading"
                                                class="btn btn-primary py-3 rounded-4 fw-bold text-white shadow mt-2"
                                                style="transition: all 0.3s;">
                                            
                                                <div x-show="isLoading" class="spinner-border spinner-border-sm text-light" role="status"></div>
                                                <span x-text="isLoading ? 'Đang xử lý...' : 'Lưu thay đổi'"></span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- 2. SECURITY -->
                        <div class="tab-pane fade" id="section-security" role="tabpanel">
                            <h4 class="fw-bold mb-4">Đăng nhập & Bảo mật</h4>
                            
                            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="fw-bold fs-6 mb-1">Đổi mật khẩu</h5>
                                        <p class="text-secondary small mb-0">Sử dụng mật khẩu mạnh để bảo vệ tài khoản của bạn.</p>
                                    </div>
                                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                        <button class="btn btn-primary rounded-pill px-4 fw-medium" type="button" data-bs-toggle="collapse" data-bs-target="#passwordForm">Thay đổi</button>
                                    </div>
                                </div>
                                <div x-data="Form()" class="collapse mt-4 pt-3 border-top" id="passwordForm">
                                    <form 
                                        hx-post="/app/account/change-password" 
                                        hx-swap="none"
                                        @htmx:before-request="startRequest()"
                                        @htmx:after-request="handleResponse($event)"
                                        class="d-grid gap-3"
                                    >
                                        <div class="row g-3" style="max-width: 400px;">
                                            <div class="col-12">
                                                <input type="password" name="password_old" class="form-control bg-light border-0 rounded-4 py-3 px-3" placeholder="Mật khẩu hiện tại">
                                            </div>
                                            <div class="col-12">
                                                <input type="password" name="password"  class="form-control bg-light border-0 rounded-4 py-3 px-3" placeholder="Mật khẩu mới">
                                            </div>
                                            <div class="col-12">
                                                <input type="password" name="password_confirm"  class="form-control bg-light border-0 rounded-4 py-3 px-3" placeholder="Xác nhận lại khẩu mới">
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" 
                                                    :disabled="isLoading"
                                                    class="btn btn-success py-3 rounded-4 fw-bold text-white shadow mt-2"
                                                    style="transition: all 0.3s;">
                                                
                                                    <div x-show="isLoading" class="spinner-border spinner-border-sm text-light" role="status"></div>
                                                    <span x-text="isLoading ? 'Đang xử lý...' : 'Lưu thay đổi'"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                                <h5 class="fw-bold fs-6 text-danger mb-2">Vùng nguy hiểm</h5>
                                <p class="text-secondary small mb-4">Xóa tài khoản là hành động vĩnh viễn và không thể khôi phục.</p>
                                <button class="btn btn-danger bg-opacity-10 border-0 fw-bold rounded-pill px-4 py-3 hover-lift">
                                    Xóa tài khoản này
                                </button>
                            </div>
                        </div>

                        <!-- 3. BILLING -->
                        <div class="tab-pane fade" id="section-billing" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="fw-bold mb-0">Ví của tôi</h4>
                                <a href="/app/payments" class="btn btn-light rounded-pill border fw-bold text-dark px-4 small">Lịch sử đầy đủ</a>
                            </div>

                            <div class="row g-4 mb-5">
                                <!-- Balance Card -->
                                <div class="col-md-7">
                                    <div class="card border-0 rounded-5 p-4 p-lg-5 h-100 bg-gradient-dark-blue text-white shadow overflow-hidden position-relative">
                                        <!-- Decorative circle using bootstrap utility + minimal css in style -->
                                        <div class="position-absolute top-0 end-0 p-5 rounded-circle bg-white opacity-10 translate-middle" style="width: 200px; height: 200px;"></div>

                                        <div class="d-flex justify-content-between position-relative z-1">
                                            <div>
                                                <p class="text-dark text-uppercase small fw-bold mb-1" style="letter-spacing: 1px;">Vmied Balance</p>
                                                <h2 class="text-gradient fw-bold mb-0"><?= number_format($user->point ?? 0, 0, ',', '.') ?> V</h2>
                                            </div>
                                            <i data-lucide="credit-card" class="text-white opacity-50" width="32"></i>
                                        </div>
                                        <div class="mt-4 d-flex gap-2 position-relative z-1">
                                            <button onclick="window.location.href='/app/payments'" class="btn btn-light fw-bold rounded-pill px-4 py-2 border-0 shadow-sm text-dark hover-lift">
                                                Nạp tiền
                                            </button>
                                            <button class="btn btn-outline-light fw-bold rounded-pill px-4 py-2 hover-lift" data-bs-toggle="modal" data-bs-target="#modalConvertWallet">
                                                Chuyển đổi
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Stats -->
                                <div class="col-md-5">
                                    <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white d-flex flex-column justify-content-center">
                                        <div class="d-flex align-items-center gap-3 mb-4">
                                            <div class="bg-success-subtle p-2 rounded-3 text-success">
                                                <i data-lucide="arrow-down-left" width="24"></i>
                                            </div>
                                            <div>
                                                <div class="small text-secondary fw-bold text-uppercase">Tháng này</div>
                                                <div class="fw-bold fs-5">+ <?= number_format($monthlyIncome, 0, ',', '.') ?> V</div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-danger-subtle p-2 rounded-3 text-danger">
                                                <i data-lucide="arrow-up-right" width="24"></i>
                                            </div>
                                            <div>
                                                <div class="small text-secondary fw-bold text-uppercase">Đã dùng</div>
                                                <div class="fw-bold fs-5">- <?= number_format($totalUsed, 0, ',', '.') ?> V</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h5 class="fw-bold fs-6 mb-3">Giao dịch gần đây</h5>
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 align-middle">
                                        <tbody>
                                            <tr>
                                                <!-- <td class="ps-4 py-3 border-0" width="60">
                                                    <div class="bg-light rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        <i data-lucide="plus" width="18" class="text-dark"></i>
                                                    </div>
                                                </td> -->
                                                <!-- <td class="py-3 border-0">
                                                    <div class="fw-bold text-dark font-size-sm">Nạp tiền Momo</div>
                                                    <div class="small text-secondary">28/12/2024 • 14:30</div>
                                                </td>
                                                <td class="pe-4 py-3 border-0 text-end">
                                                    <div class="fw-bold text-success">+ 50.000 V</div>
                                                    <div class="small text-muted">Thành công</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="ps-4 py-3 border-0" width="60">
                                                    <div class="bg-light rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        <i data-lucide="zap" width="18" class="text-dark"></i>
                                                    </div>
                                                </td>
                                                <td class="py-3 border-0">
                                                    <div class="fw-bold text-dark font-size-sm">Sử dụng AI Chat</div>
                                                    <div class="small text-secondary">27/12/2024 • 09:15</div>
                                                </td>
                                                <td class="pe-4 py-3 border-0 text-end">
                                                    <div class="fw-bold text-dark">- 500 V</div>
                                                    <div class="small text-muted">Hoàn tất</div>
                                                </td>
                                            </tr> -->
                                            <?php if (!empty($payments)): ?>
                                                <?php foreach ($payments as $payment): ?>
                                                    <?php
                                                        $statusText = 'Đang xử lý';
                                                        if ($payment['status'] == 1) $statusText = 'Thành công';
                                                        elseif ($payment['status'] == 2) $statusText = 'Thất bại';
                                                    
                                                        $isPositive = in_array($payment['type'], ['deposit', 'commission']);
                                                        $amountSign = $isPositive ? '+' : '-';
                                                        $amountColor = $isPositive ? 'text-success' : 'text-dark';
                                                        $iconName = $isPositive ? 'plus' : 'zap';
                                                        
                                                        // Tách riêng logic lấy số tiền và Đơn vị (V hoặc VNĐ)
                                                        if ($payment['type'] === 'commission') {
                                                            $displayAmount = $payment['commission']; // Lấy cột tiền hoa hồng
                                                            $unit = 'VNĐ';
                                                        } elseif ($payment['type'] === 'withdraw') {
                                                            $displayAmount = $payment['amount']; // Rút tiền thật
                                                            $unit = 'VNĐ';
                                                        } else {
                                                            $displayAmount = $payment['vmied']; // Điểm nạp / Tiêu dùng
                                                            $unit = 'V';
                                                        }
                                                    ?>
                                                    <tr>
                                                        <td class="ps-4 py-3 border-0" width="60">
                                                            <div class="bg-light rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                <i data-lucide="<?= $iconName ?>" width="18" class="text-dark"></i>
                                                            </div>
                                                        </td>
                                                        <td class="py-3 border-0">
                                                            <div class="fw-bold text-dark font-size-sm"><?= htmlspecialchars($payment['note'] ?? 'Giao dịch') ?></div>
                                                            <div class="small text-secondary"><?= date('d/m/Y • H:i', strtotime($payment['created_at'])) ?></div>
                                                        </td>
                                                        <td class="pe-4 py-3 border-0 text-end">
                                                            <div class="fw-bold <?= $amountColor ?>">
                                                                <?= $amountSign ?> <?= number_format(abs($displayAmount), 0, ',', '.') ?> V
                                                            </div>
                                                            <div class="small text-muted"><?= $statusText ?></div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td class="py-4 text-center text-muted border-0" colspan="3">Chưa có giao dịch nào gần đây.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- 4. NOTIFICATIONS -->
                        <div class="tab-pane fade" id="section-notifications" role="tabpanel">
                            <h4 class="fw-bold mb-4">Tùy chọn thông báo</h4>
                            
                            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                                <div class="d-flex align-items-center justify-content-between py-3 border-bottom border-light-subtle">
                                    <div>
                                        <h6 class="fw-bold mb-1">Cập nhật sản phẩm</h6>
                                        <p class="text-secondary small mb-0">Nhận tin tức về các tính năng AI mới.</p>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked style="height: 1.5em; width: 3em;">
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between py-3 border-bottom border-light-subtle">
                                    <div>
                                        <h6 class="fw-bold mb-1">Kết quả phân tích</h6>
                                        <p class="text-secondary small mb-0">Email khi file của bạn được xử lý xong.</p>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked style="height: 1.5em; width: 3em;">
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between py-3">
                                    <div>
                                        <h6 class="fw-bold mb-1">Khuyến mãi & Quà tặng</h6>
                                        <p class="text-secondary small mb-0">Không bỏ lỡ các ưu đãi nạp tiền.</p>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" style="height: 1.5em; width: 3em;">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal Chuyển đổi Wallet → Points -->
        <div class="modal fade" id="modalConvertWallet" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-0 pb-0 px-4 pt-4">
                        <div>
                            <h5 class="modal-title fw-bold mb-1">Chuyển đổi số dư</h5>
                            <p class="text-muted small mb-0">Wallet → Points (tỷ lệ 1:1)</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body px-4 pb-4 pt-3">
        
                        <!-- Số dư hiện tại -->
                        <div class="bg-light rounded-4 p-3 mb-4 d-flex justify-content-between align-items-center">
                            <span class="text-secondary small fw-bold">Số dư ví khả dụng</span>
                            <span class="fw-bold text-dark" id="walletBalanceDisplay">
                                <?= number_format($walletBalance ?? 0, 0, ',', '.') ?> V
                            </span>
                        </div>
        
                        <!-- Input số tiền -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small mb-2">Số tiền muốn chuyển</label>
                            <div class="input-group">
                                <input type="number" id="convertAmount" class="form-control bg-light border-0 rounded-start-4 py-3 px-3" placeholder="Nhập số tiền...">
                                <span class="input-group-text bg-light border-0 rounded-end-4 fw-bold text-secondary">V</span>
                            </div>
                            <div class="form-text">Tối thiểu 100.000 V</div>
                        </div>
        
                        <!-- Alert -->
                        <div id="convertAlert" class="alert d-none py-2 small rounded-3 mb-3"></div>
        
                        <!-- Nút xác nhận -->
                        <button class="btn btn-dark w-100 rounded-pill fw-bold py-3" id="convertBtn" onclick="submitConvert()">
                            Xác nhận chuyển đổi
                        </button>
                    </div>
                </div>
            </div>
        </div>

<script>
function submitConvert() {
    const amount = parseInt(document.getElementById('convertAmount').value);
    const btn = document.getElementById('convertBtn');

    hideConvertAlert();

    if (!amount || amount < 10000) {
        showConvertAlert('danger', 'Số tiền tối thiểu là 10.000 V');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';

    fetch('/app/wallet', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json', 
            'X-Requested-With': 'XMLHttpRequest' 
        },
        body: JSON.stringify({ amount })
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            showConvertAlert('success', data.alert);
            setTimeout(() => location.reload(), 1500);
        } else {
            showConvertAlert('danger', data.alert);
            btn.disabled = false;
            btn.innerHTML = 'Xác nhận chuyển đổi';
        }
    })
    .catch(() => {
        showConvertAlert('danger', 'Có lỗi xảy ra, vui lòng thử lại');
        btn.disabled = false;
        btn.innerHTML = 'Xác nhận chuyển đổi';
    });
}

function showConvertAlert(type, msg) {
    const el = document.getElementById('convertAlert');
    el.className = `alert alert-${type} py-2 small rounded-3 mb-3`;
    el.textContent = msg;
}

function hideConvertAlert() {
    document.getElementById('convertAlert').className = 'alert d-none py-2 small rounded-3 mb-3';
}
</script>

<?php $this->endSection() ?>

    <?php $this->endSection() ?>