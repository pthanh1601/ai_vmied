<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Vmied</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="/css/app.css?ver=1.1" rel="stylesheet">
</head>
<body>
    <div class="position-fixed inset-0 pe-none" style="z-index: -1;">
        <div class="bg-ambience-1"></div>
        <div class="bg-ambience-2"></div>
    </div>

    <nav class="glass-nav position-fixed top-0 mb-4 w-100 start-0" style="z-index: 1020;">
        <div class="container">
            <div class="px-3 px-md-4 h-20 d-flex align-items-center justify-content-between" style="height: 80px;">
                <div class="d-flex align-items-center gap-3 gap-md-4">
                    <button class="btn btn-link text-dark p-0 d-md-none d-flex align-items-center justify-content-center" 
                            type="button" 
                            data-bs-toggle="offcanvas" 
                            data-bs-target="#mobileSidebar"
                            style="width: 40px; height: 40px;">
                        <i data-lucide="menu"></i>
                    </button>

                    <a class="d-flex align-items-center gap-3 text-decoration-none" style="cursor: pointer;" href="/app/ai">
                        <?php if (isset($user) && !empty($user->organization)): ?>
                            <!-- HIỂN THỊ DÀNH CHO TRƯỜNG VIP HOẶC HỌC VIÊN/GIẢNG VIÊN THUỘC TRƯỜNG -->
                            <?php if (!empty($user->avatar)): ?>
                                <img src="<?= htmlspecialchars($user->avatar) ?>" alt="Logo" style="height: 45px; object-fit: contain; border-radius: 6px;">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center text-white fw-bold fs-4 shadow-sm rounded-3" 
                                     style="width: 45px; height: 45px; background: linear-gradient(135deg, var(--brand-600, #0ea5e9), var(--accent-600, #6366f1));">
                                    <?= mb_substr($user->organization, 0, 1, 'UTF-8') ?>
                                </div>
                            <?php endif; ?>
                            
                            <span class="fw-bolder fs-4 text-dark d-none d-sm-block m-0" style="letter-spacing: -0.3px; line-height: 1;">
                                <?= htmlspecialchars($user->organization) ?>
                            </span>
                    
                        <?php else: ?>
                            <!-- HIỂN THỊ MẶC ĐỊNH (TÀI KHOẢN TỰ DO HOẶC ADMIN) -->
                            <div class="d-flex align-items-center justify-content-center text-white fw-bold fs-4 shadow-sm rounded-3" 
                                 style="width: 45px; height: 45px; background: linear-gradient(135deg, var(--brand-600, #0ea5e9), var(--accent-600, #6366f1));">
                                V
                            </div>
                            <span class="fw-bolder fs-4 text-dark d-none d-sm-block m-0" style="letter-spacing: -0.3px; line-height: 1;">
                                AI Vmied
                            </span>
                        <?php endif; ?>
                    </a>

                    <div hx-boost="true" hx-target="#app-content" hx-select="#app-content" hx-swap="outerHTML show:window:top" class="d-none d-md-flex align-items-center gap-1 bg-light bg-opacity-50 p-1 rounded-3">
                        <a href="/app/ai" class="nav-link-custom">Dashboard</a>
                        <a href="/app/historys" class="nav-link-custom">Lịch sử</a>
                        <a href="/app/payments" class="nav-link-custom">Nạp tiền</a>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3 gap-md-4">
                    <!-- Nút ví → mở modal nạp tiền -->
                    <div class="d-none d-sm-flex align-items-center gap-3 bg-dark text-white ps-3 pe-1 py-1 rounded-pill shadow-sm cursor-pointer hover-scale"
                         data-bs-toggle="modal"
                         data-bs-target="#modalNapTien"
                         style="cursor: pointer;">
                        <div class="d-flex flex-column align-items-start lh-1 me-2">
                            <span class="fw-medium text-white-50" style="font-size: 10px;">Số dư ví</span>
                            <span class="fw-bold small"><span class="user-point-display"><?=$user->point?></span><span class="text-info">V</span></span>
                        </div>
                        <button type="button" class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center p-0" style="width: 32px; height: 32px;">
                            <i data-lucide="plus" style="width: 16px;"></i>
                        </button>
                    </div>

                    <div class="vr d-none d-sm-block text-secondary opacity-25"></div>
                    
                    <button class="btn border-0 d-flex align-items-center gap-3 cursor-pointer p-0" hx-get="/app/account" hx-swap="none">
                        <div class="text-end d-none d-lg-block line-height-sm">
                            <p class="mb-0 fw-bold text-dark small"><?=$user->name?></p>
                                          <p class="mb-0 fw-bold text-secondary text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;"><?= match((int)$user->type) { 1 => 'Quản trị', 2 => 'VIP', default => 'Thành viên' } ?></p>
                        </div>
                        <div class="position-relative">
                            <img src="https://ui-avatars.com/api/?name=<?=$user->name?>&background=0ea5e9&color=fff" alt="User" class="rounded-circle border border-2 border-white shadow-sm" style="width: 40px; height: 40px;">
                            <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-white rounded-circle" style="width: 12px; height: 12px;"></span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Sidebar -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
        <div class="offcanvas-header pb-0">
            <div class="d-flex align-items-center gap-2">
                <?php if (isset($user) && isset($user->role_id) && $user->role_id == 2 && !empty($user->avatar)): ?>
                    <img src="<?= htmlspecialchars($user->avatar) ?>" alt="Logo" style="height: 48px; object-fit: contain;">
                    <h5 class="offcanvas-title fw-bold" id="mobileSidebarLabel"><?= htmlspecialchars($user->organization ?: 'VIP Partner') ?></h5>
                <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center text-white fw-bold shadow-sm rounded-3" 
                         style="width: 32px; height: 32px; background: linear-gradient(135deg, var(--brand-600, #0ea5e9), var(--accent-600, #6366f1));">
                        V
                    </div>
                    <h5 class="offcanvas-title fw-bold" id="mobileSidebarLabel">AI Vmied</h5>
                <?php endif; ?>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column justify-content-between">
            
            <div class="d-flex flex-column gap-2 mt-4">
                <?php if (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin') === 0): ?>
                    
                    <?php if (isset($user) && $user->type == 1): ?>
                        <!-- Menu của ADMIN -->
                        <p class="text-uppercase text-secondary fw-bold small mb-1 ps-2 mt-2">Khu vực Quản lý</p>
                        <a href="/admin/vips" class="nav-link-custom" data-bs-dismiss="offcanvas">
                            <i data-lucide="building"></i> Đơn vị Liên kết
                        </a>
                        <a href="/admin/members" class="nav-link-custom" data-bs-dismiss="offcanvas">
                            <i data-lucide="users"></i> Học viên / Giảng viên
                        </a>
                        <a href="/admin/statistics" class="nav-link-custom" data-bs-dismiss="offcanvas">
                            <i data-lucide="bar-chart-2"></i> Thống kê
                        </a>
                        <a href="/admin/user-finance?uuid=<?= $user->uuid ?>" class="nav-link-custom" data-bs-dismiss="offcanvas">
                            <i data-lucide="wallet"></i> Tài chính & Hoa hồng
                        </a>

                        <p class="text-uppercase text-secondary fw-bold small mb-1 ps-2 mt-3">Quản trị Hệ thống</p>
                        <a href="/admin/admins" class="nav-link-custom" data-bs-dismiss="offcanvas">
                             <i data-lucide="shield"></i> Tài khoản Quản trị
                        </a>
                        <a href="/admin/roles" class="nav-link-custom" data-bs-dismiss="offcanvas">
                             <i data-lucide="lock"></i> Nhóm quyền
                        </a>
                        
                    <?php elseif (isset($user) && $user->type == 2): ?>
                        <!-- Menu của VIP -->
                        <p class="text-uppercase text-secondary fw-bold small mb-1 ps-2 mt-2">Khu vực Quản lý</p>
                        <a href="/admin/members" class="nav-link-custom" data-bs-dismiss="offcanvas">
                            <i data-lucide="users"></i> Học viên / Giảng viên
                        </a>
                    <?php endif; ?>

                <?php else: ?>
                    <a href="/app" class="nav-link-custom" data-bs-dismiss="offcanvas">
                        <i data-lucide="layout-dashboard"></i> Dashboard
                    </a>
                    <a href="/app/historys" class="nav-link-custom" data-bs-dismiss="offcanvas">
                        <i data-lucide="history"></i> Lịch sử
                    </a>
                    <a href="/app/payments" class="nav-link-custom" data-bs-dismiss="offcanvas">
                        <i data-lucide="credit-card"></i> Nạp tiền
                    </a>
                <?php endif; ?>
            </div>

            <div class="bg-light p-3 rounded-3 mt-auto">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary small fw-medium">Số dư hiện tại</span>
                    <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                </div>
                <h4 class="fw-bold mb-3"><span class="user-point-display"><?=$user->point?></span> <span class="fs-6 text-secondary">V</span></h4>
                <!-- Nút Nạp ngay mobile → đóng offcanvas rồi mở modal -->
                <button type="button"
                    class="btn btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2"
                    onclick="bootstrap.Offcanvas.getInstance(document.getElementById('mobileSidebar')).hide(); setTimeout(() => new bootstrap.Modal(document.getElementById('modalNapTien')).show(), 300);">
                    <i data-lucide="plus-circle" width="18"></i> Nạp ngay
                </button>
            </div>
        </div>
    </div>

    <style>
        .admin-sidebar {
            width: 250px;
            z-index: 1010;
            top: 80px;
        }
        .admin-main-content {
            margin-left: 250px;
        }
        @media (max-width: 767.98px) {
            .admin-sidebar { display: none !important; }
            .admin-main-content { margin-left: 0; }
        }
        .admin-nav-item {
            transition: all 0.2s;
        }
        .admin-nav-item:not(.bg-primary):hover {
            background-color: #f8f9fa;
        }
    </style>

    <div class="d-flex w-100 h-100" style="padding-top: 80px;">
        <!-- Left Sidebar (Desktop only) -->
        <div class="d-flex flex-column bg-white shadow-sm h-100 position-fixed start-0 pb-4 admin-sidebar">
            <div class="p-3 d-flex flex-column gap-2">
                
                <?php if (isset($user) && $user->type == 1): ?>
                    <!-- Menu của ADMIN -->
                    <p class="text-uppercase text-secondary fw-bold small mb-2 ps-2">Khu vực Quản lý</p>
                    <a href="/admin/vips" class="nav-link-custom admin-nav-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/vips') === 0 ? 'bg-primary text-white' : 'text-dark' ?> rounded-3 p-2 text-decoration-none d-flex align-items-center gap-3">
                        <i data-lucide="building" style="width: 20px;"></i> 
                        <span class="fw-medium">Đơn vị Liên kết</span>
                    </a>
                    
                    <a href="/admin/members" class="nav-link-custom admin-nav-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/members') === 0 ? 'bg-primary text-white' : 'text-dark' ?> rounded-3 p-2 text-decoration-none d-flex align-items-center gap-3">
                        <i data-lucide="users" style="width: 20px;"></i> 
                        <span class="fw-medium">Học viên / Giảng viên</span>
                    </a>
                    
                    <a href="/admin/statistics" class="nav-link-custom admin-nav-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/statistics') === 0 ? 'bg-primary text-white' : 'text-dark' ?> rounded-3 p-2 text-decoration-none d-flex align-items-center gap-3">
                        <i data-lucide="bar-chart-2" style="width: 20px;"></i> 
                        <span class="fw-medium">Thống kê</span>
                    </a>
                    <!-- NÚT TÀI CHÍNH DÀNH CHO ADMIN DESKTOP -->
                    <a href="/admin/user-finance?uuid=<?= $user->uuid ?>" class="nav-link-custom admin-nav-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/user-finance') === 0 ? 'bg-primary text-white' : 'text-dark' ?> rounded-3 p-2 text-decoration-none d-flex align-items-center gap-3">
                        <i data-lucide="wallet" style="width: 20px;"></i> 
                        <span class="fw-medium">Tài chính & Hoa hồng</span>
                    </a>

                    <p class="text-uppercase text-secondary fw-bold small mb-2 ps-2 mt-4">Quản trị Hệ thống</p>
                    <a href="/admin/admins" class="nav-link-custom admin-nav-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/admins') === 0 ? 'bg-primary text-white' : 'text-dark' ?> rounded-3 p-2 text-decoration-none d-flex align-items-center gap-3">
                        <i data-lucide="shield" style="width: 20px;"></i> 
                        <span class="fw-medium">Tài khoản Quản trị</span>
                    </a>
                    
                    <a href="/admin/roles" class="nav-link-custom admin-nav-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/roles') === 0 ? 'bg-primary text-white' : 'text-dark' ?> rounded-3 p-2 text-decoration-none d-flex align-items-center gap-3">
                        <i data-lucide="lock" style="width: 20px;"></i> 
                        <span class="fw-medium">Nhóm quyền</span>
                    </a>

                <?php elseif (isset($user) && $user->type == 2): ?>
                    <!-- Menu của VIP -->
                    <p class="text-uppercase text-secondary fw-bold small mb-2 ps-2">Khu vực Quản lý</p>
                    <a href="/admin/members" class="nav-link-custom admin-nav-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/members') === 0 ? 'bg-primary text-white' : 'text-dark' ?> rounded-3 p-2 text-decoration-none d-flex align-items-center gap-3">
                        <i data-lucide="users" style="width: 20px;"></i> 
                        <span class="fw-medium">Học viên / Giảng viên</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 admin-main-content" id="app-content">
            <div class="container-fluid p-3 p-md-4 h-100">
                <?= $this->yield('content') ?>
            </div>
        </div>
    </div>

    <!-- Scripts load TRƯỚC modal để NapTien / NeoUI sẵn sàng -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://unpkg.com/topbar@3.0.0/topbar.min.js"></script>
    <script src="/js/app.js"></script>

    <!-- =============================================
         MODAL NẠP TIỀN
         ============================================= -->
    <div class="modal fade" id="modalNapTien" tabindex="-1" aria-labelledby="modalNapTienLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden;">

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
                    <div class="row g-4">

                        <!-- LEFT: Chọn gói + Phương thức -->
                        <div class="col-lg-7">

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
                                        onclick="NapTien.selectAmount(<?= $pkg['value'] ?>, this)">
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
                                    oninput="NapTien.customAmount(this.value)">
                                <span class="input-group-text bg-white border-0 pe-3 fw-bold text-secondary">VNĐ</span>
                            </div>

                            <!-- Phương thức -->
                            <p class="text-secondary small fw-bold text-uppercase mb-2 mt-1">
                                <i data-lucide="credit-card" width="13" class="me-1"></i> Phương thức
                            </p>
                            <div class="row g-2 mb-3">
                                <div class="col-4">
                                    <button type="button" onclick="NapTien.selectMethod('qr', this)"
                                        class="btn btn-method active w-100 p-3 rounded-4 border bg-white d-flex flex-column align-items-center gap-2">
                                        <i data-lucide="qr-code" width="20"></i>
                                        <span style="font-size:12px;" class="fw-bold">Chuyển khoản</span>
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button type="button" onclick="NapTien.selectMethod('momo', this)"
                                        class="btn btn-method w-100 p-3 rounded-4 border bg-white d-flex flex-column align-items-center gap-2">
                                        <i data-lucide="smartphone" width="20" class="text-danger"></i>
                                        <span style="font-size:12px;" class="fw-bold">Ví MoMo</span>
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button type="button" onclick="NapTien.selectMethod('card', this)"
                                        class="btn btn-method w-100 p-3 rounded-4 border bg-white d-flex flex-column align-items-center gap-2">
                                        <i data-lucide="credit-card" width="20" class="text-primary"></i>
                                        <span style="font-size:12px;" class="fw-bold">Thẻ quốc tế</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Detail: QR -->
                            <div id="modal-detail-qr" class="method-detail bg-light rounded-4 p-3 border">
                                <div class="d-flex gap-3 align-items-start">
                                    <div class="bg-white p-2 rounded-3 border shadow-sm text-center flex-shrink-0">
                                        <i data-lucide="qr-code" width="64" height="64" class="text-dark d-block"></i>
                                        <div class="text-success fw-bold mt-1" style="font-size:9px;letter-spacing:.5px;">QUÉT ĐỂ THANH TOÁN</div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="text-secondary small fw-bold text-uppercase mb-1">Vietcombank</p>
                                        <div class="input-group input-group-sm mb-2 rounded-3 overflow-hidden border bg-white">
                                            <input type="text" class="form-control bg-white border-0 fw-bold font-monospace" value="0071000123456" readonly>
                                            <button type="button" class="btn btn-white border-0 text-primary bg-white px-3"
                                                onclick="navigator.clipboard.writeText('0071000123456'); NeoUI.toast('Đã sao chép số tài khoản', 'success')">
                                                <i data-lucide="copy" width="14"></i>
                                            </button>
                                        </div>
                                        <p class="text-dark fw-bold small mb-2 bg-white px-2 py-1 rounded-2 d-inline-block border">VIEN NCPT GIAO DUC VIET MY</p>
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
                                        <input type="text" class="form-control border-0 shadow-none font-monospace" placeholder="0000 0000 0000 0000">
                                    </div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label text-secondary small fw-bold text-uppercase mb-1">Hết hạn</label>
                                        <input type="text" class="form-control bg-light border-0 rounded-3 font-monospace" placeholder="MM/YY">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-secondary small fw-bold text-uppercase mb-1">CVC</label>
                                        <input type="text" class="form-control bg-light border-0 rounded-3 font-monospace" placeholder="123">
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label text-secondary small fw-bold text-uppercase mb-1">Chủ thẻ</label>
                                    <input type="text" class="form-control bg-light border-0 rounded-3 text-uppercase" placeholder="TEN IN TREN THE">
                                </div>
                            </div>

                        </div>

                        <!-- RIGHT: Summary -->
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
                                        <span class="fw-bold text-info" id="modal-label-vmied">50.000 V</span>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="button" onclick="NapTien.submit()"
                                        class="btn btn-primary w-100 py-3 rounded-4 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-lg mb-3">
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

                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- /MODAL NẠP TIỀN -->

    <style>
    .btn-pkg, .btn-method {
        transition: all .15s ease;
    }
    .btn-pkg:hover, .btn-method:hover {
        border-color: #0d6efd !important;
    }
    .btn-pkg.active, .btn-method.active {
        border-color: #0d6efd !important;
        background-color: #e7f1ff !important;
        box-shadow: 0 0 0 3px rgba(13,110,253,.12) !important;
    }
    .btn-pkg.active span, .btn-method.active span, .btn-method.active i {
        color: #0d6efd !important;
    }
    </style>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        function loadPageScript() {
            if (typeof lucide !== 'undefined') lucide.createIcons();

            const el = document.querySelector('[data-page-script]');
            if (!el) return; 

            const src = el.getAttribute('data-page-script');
            const alreadyLoaded = document.querySelector(`script[data-page-script-tag="${src}"]`);

            if (!alreadyLoaded) {
                const s = document.createElement('script');
                s.src = src;
                s.setAttribute('data-page-script-tag', src);
                s.onload = function () {
                    if (typeof window.__pageInit__ === 'function') {
                        window.__pageInit__();
                    }
                };
                document.body.appendChild(s);
            } else {
                if (typeof window.__pageInit__ === 'function') {
                    window.__pageInit__();
                }
            }
        }

        document.addEventListener('DOMContentLoaded', loadPageScript);
        document.addEventListener('htmx:afterSwap', loadPageScript);

        document.getElementById('mobileSidebar').addEventListener('shown.bs.offcanvas', function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>

</body>
</html>