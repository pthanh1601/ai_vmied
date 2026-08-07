<div class="offcanvas offcanvas-load offcanvas-end" tabindex="-1" id="profileDrawer" aria-labelledby="profileDrawerLabel" style="width: 320px;">
    <div class="offcanvas-header border-bottom p-4">
        <h5 class="offcanvas-title fw-bold" id="profileDrawerLabel">Tài khoản</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0 d-flex flex-column">
        <!-- Profile Info -->
        <div class="p-4 text-center border-bottom">
            <div class="position-relative d-inline-block mb-3">
                <img src="https://ui-avatars.com/api/?name=<?=$user->name?>&background=0ea5e9&color=fff&size=128" class="rounded-circle shadow-sm p-1 bg-white" width="80" height="80">
                <span class="position-absolute bottom-0 end-0 bg-success border border-4 border-white rounded-circle" style="width: 20px; height: 20px;"></span>
            </div>
            <h5 class="fw-bold text-dark mb-1"><?=$user->name?></h5>
            <p class="text-secondary small mb-3"><?=$user->email?></p>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2 text-uppercase" style="font-size: 10px; letter-spacing: 1px;"><?=$user->type?></span>
        </div>

        <!-- Stats -->
        <div class="p-4 bg-light">
            <div class="bg-dark text-white rounded-4 p-4 shadow position-relative overflow-hidden cursor-pointer" onclick="alert('Mở modal nạp tiền')">
                <div class="position-absolute top-0 end-0 mt-n2 me-n2 bg-primary rounded-circle opacity-25" style="width: 60px; height: 60px; filter: blur(20px);"></div>
                <p class="small text-white-50 mb-1 fw-medium">Số dư hiện tại</p>
                <div class="d-flex align-items-end justify-content-between">
                    <span class="h3 fw-bold mb-0"><?=$user->point?><span class="text-info fs-6">V</span></span>
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i data-lucide="plus" style="width: 16px;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu -->
        <div class="flex-grow-1 p-2 overflow-auto">
            <div class="list-group list-group-flush" hx-boost="true" hx-target="#app-content" hx-select="#app-content" hx-swap="outerHTML show:window:top" >
                <a href="/app/profiles" data-bs-dismiss="offcanvas" class="list-group-item list-group-item-action border-0 rounded-3 py-3 px-3 d-flex align-items-center gap-3 text-secondary fw-medium hover-bg-light">
                    <i data-lucide="user" style="width: 18px;"></i> Thông tin cá nhân
                </a>
                <a href="/app/affiliate" data-bs-dismiss="offcanvas"  class="list-group-item list-group-item-action border-0 rounded-3 py-3 px-3 d-flex align-items-center gap-3 text-secondary fw-medium hover-bg-light">
                    <i data-lucide="gift" style="width: 18px;"></i> Giới thiệu bàn bè
                </a>
                <a href="/app/historys" data-bs-dismiss="offcanvas"  class="list-group-item list-group-item-action border-0 rounded-3 py-3 px-3 d-flex align-items-center gap-3 text-secondary fw-medium hover-bg-light">
                    <i data-lucide="history" style="width: 18px;"></i> Lịch sử giao dịch
                </a>
                <a href="/app/support" data-bs-dismiss="offcanvas"  class="list-group-item list-group-item-action border-0 rounded-3 py-3 px-3 d-flex align-items-center gap-3 text-secondary fw-medium hover-bg-light">
                    <i data-lucide="help-circle" style="width: 18px;"></i> Trợ giúp & Hỗ trợ
                </a>
            </div>
        </div>

        <!-- Logout -->
        <div class="p-4 border-top">
            <button hx-get="/logout" class="btn btn-outline-danger w-100 fw-bold d-flex align-items-center justify-content-center gap-2 py-2">
                <i data-lucide="log-out" style="width: 16px;"></i> Đăng xuất
            </button>
        </div>
    </div>
</div>