<?php $this->extend('layouts/admin') ?>

<?php $this->section('content') ?>
<div class="container pt-5 mt-5 pb-5">
    <div class="card border-0 shadow-sm rounded-4 p-5 text-center mx-auto" style="max-width: 550px;">
        <div class="mb-4">
            <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                <i data-lucide="shield-alert" width="40" height="40"></i>
            </div>
        </div>
        
        <h4 class="fw-bold text-dark mb-2">Truy Cập Bị Từ Chối!</h4>
        <p class="text-secondary mb-4">
            Tài khoản của bạn không được cấp quyền truy cập tính năng này 
            <?php if(!empty($actionCode)): ?>
                <code class="d-block mt-1 text-danger">Mã quyền: <?= htmlspecialchars($actionCode) ?></code>
            <?php endif; ?>
        </p>

        <div class="d-flex justify-content-center gap-2">
            <button onclick="window.history.back()" class="btn btn-light border rounded-pill px-4 fw-medium">
                <i data-lucide="arrow-left" width="16" class="me-1"></i> Quay lại
            </button>
            <a href="/admin/members" class="btn btn-primary rounded-pill px-4 fw-bold">
                Trang chủ Quản trị
            </a>
        </div>
    </div>
</div>
<?php $this->endSection() ?>