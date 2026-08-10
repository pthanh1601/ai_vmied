<?php $this->extend('layouts/admin') ?>

<?php $this->section('content') ?>
    <div class="container pt-5 mt-5 pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <h3 class="fw-bold text-dark m-0">Quản trị viên Hệ thống</h3>
            
            <div class="d-flex align-items-center gap-3">
                <!-- FORM TÌM KIẾM -->
                <form method="GET" action="/admin/admins" class="m-0">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white text-secondary border-secondary-subtle"><i data-lucide="search" width="16"></i></span>
                        <input type="text" name="q" class="form-control border-secondary-subtle shadow-sm" placeholder="Tìm tên, email..." value="<?= htmlspecialchars($q ?? '') ?>">
                        <?php if(!empty($q)): ?>
                            <a href="/admin/admins" class="btn btn-outline-secondary border-secondary-subtle" title="Xóa bộ lọc"><i data-lucide="x" width="14"></i></a>
                        <?php endif; ?>
                    </div>
                </form>

                <button class="btn btn-dark btn-rounded fw-bold shadow-sm d-flex align-items-center gap-2 px-4 py-2 hover-lift" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                    <i data-lucide="shield" width="18"></i> Cấp tài khoản Admin
                </button>
            </div>
        </div>

        <div class="card border shadow-sm overflow-hidden" style="border-radius: 2rem;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold">Tài khoản Quản trị</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold text-center">Vai trò / Nhóm Quyền</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold text-center">Trạng thái</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php if(!empty($admins)): ?>
                            <?php foreach($admins as $u): ?>
                            <tr class="transition-hover">
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative">
                                            <?php if(!empty($u['avatar'])): ?>
                                                <img src="<?= $u['avatar'] ?>" class="rounded-circle shadow-sm object-fit-cover" style="width: 45px; height: 45px;">
                                            <?php else: ?>
                                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($u['name']) ?>&background=1e293b&color=fff" class="rounded-circle shadow-sm" style="width: 45px; height: 45px;">
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($u['name']) ?></div>
                                            <div class="small text-secondary"><?= htmlspecialchars($u['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if(!empty($u['role_id'])): ?>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle px-3 py-2">
                                            <i data-lucide="tag" width="14" class="me-1"></i> <?= htmlspecialchars($u['role_name'] ?? 'Không xác định') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-dark px-3 py-2">
                                            <i data-lucide="shield-alert" width="14" class="me-1"></i> Super Admin
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="form-check form-switch d-flex justify-content-center align-items-center gap-2 m-0 p-0">
                                        <input class="form-check-input m-0" type="checkbox" role="switch" 
                                               style="cursor: pointer; width: 35px; height: 18px;"
                                               onchange="window.toggleUserStatus('<?= $u['uuid'] ?>', this.checked ? 1 : 0)"
                                               <?= (isset($u['status']) && $u['status'] == 1) ? 'checked' : '' ?>
                                               <?= ($u['uuid'] === $user->uuid) ? 'disabled' : '' ?>> <!-- Khóa nút tự tắt chính mình -->
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-end text-nowrap">
                                    <button class="btn btn-sm btn-outline-primary rounded-pill me-1" 
                                            onclick="window.openEditAdmin(this)" 
                                            data-uuid="<?= $u['uuid'] ?>" 
                                            data-name="<?= htmlspecialchars($u['name'], ENT_QUOTES) ?>" 
                                            data-email="<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>" 
                                            data-role="<?= $u['role_id'] ?>"
                                            title="Sửa thông tin">
                                        <i data-lucide="edit-2" width="16"></i> Sửa
                                    </button>
                                    
                                    <button class="btn btn-sm btn-outline-danger rounded-pill" 
                                            onclick="window.deleteUser('<?= $u['uuid'] ?>')" 
                                            <?= ($u['uuid'] === $user->uuid) ? 'disabled' : '' ?>
                                            title="Xóa Admin">
                                        <i data-lucide="trash-2" width="16"></i> Xóa
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center py-5 text-secondary">Chưa có quản trị viên nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php $queryStr = !empty($q) ? '&q=' . urlencode($q) : ''; ?>
            
            <!-- THANH PHÂN TRANG -->
            <?php if (isset($totalPages) && $totalPages > 1): ?>
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-center">
                <nav aria-label="Điều hướng trang">
                    <ul class="pagination pagination-sm mb-0 shadow-sm">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link px-3" href="?page=<?= $page - 1 ?><?= $queryStr ?>"><i data-lucide="chevron-left" width="16"></i></a>
                        </li>
                        
                        <?php 
                        $startPage = max(1, $page - 2);
                        $endPage   = min($totalPages, $page + 2);
                        
                        if ($startPage > 1) {
                            echo '<li class="page-item"><a class="page-link fw-medium" href="?page=1' . $queryStr . '">1</a></li>';
                            if ($startPage > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }

                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link fw-medium" href="?page=<?= $i ?><?= $queryStr ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; 
                        
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            echo '<li class="page-item"><a class="page-link fw-medium" href="?page='.$totalPages.$queryStr.'">'.$totalPages.'</a></li>';
                        }
                        ?>
                        
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link px-3" href="?page=<?= $page + 1 ?><?= $queryStr ?>"><i data-lucide="chevron-right" width="16"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Add Admin -->
    <div class="modal fade" id="addAdminModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form onsubmit="event.preventDefault(); window.submitForm('/admin/users/add', this);">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="fw-bold"><i data-lucide="shield-check" width="20" class="me-2 text-dark"></i>Cấp Tài Khoản Admin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="type" value="1"> <!-- TYPE 1 LÀ ADMIN -->
                        <input type="hidden" name="point" value="0">
                        <input type="hidden" name="ref_by" value="0">

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-secondary">Họ và Tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-custom" name="name" required placeholder="Nhập tên quản trị viên...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-secondary">Email Đăng nhập <span class="text-danger">*</span></label>
                            <input type="email" class="form-control form-control-custom" name="email" required placeholder="admin@example.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-secondary">Mật khẩu</label>
                            <input type="text" class="form-control form-control-custom" name="password" placeholder="Mặc định: 123456">
                        </div>
                        
                        <hr class="text-secondary opacity-25 my-4">
                        
                        <div class="mb-2">
                            <label class="form-label fw-bold text-primary mb-2">Gán Nhóm Quyền (Role)</label>
                            <select name="role_id" class="form-select form-control-custom">
                                <option value="">-- Cấp toàn quyền (Super Admin) --</option>
                                <?php if(!empty($roles)): ?>
                                    <?php foreach($roles as $r): ?>
                                        <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="form-text mt-2"><i data-lucide="info" width="14" class="me-1"></i>Nếu để trống, tài khoản này sẽ có toàn quyền truy cập hệ thống.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                        <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold w-100 py-2">Xác nhận tạo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Admin -->
    <div class="modal fade" id="editAdminModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form onsubmit="event.preventDefault(); window.submitForm('/admin/users/update', this);">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="fw-bold"><i data-lucide="edit-2" width="20" class="me-2 text-dark"></i>Sửa Thông Tin Admin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="uuid" id="edit_uuid">
                        <input type="hidden" name="type" value="1"> <!-- Vẫn là Admin -->

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-secondary">Họ Tên</label>
                            <input type="text" class="form-control form-control-custom" name="name" id="edit_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-secondary">Email</label>
                            <input type="email" class="form-control form-control-custom" name="email" id="edit_email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-secondary">Mật khẩu mới</label>
                            <input type="text" class="form-control form-control-custom" name="password" placeholder="Bỏ trống nếu không muốn đổi">
                        </div>
                        
                        <hr class="text-secondary opacity-25 my-4">
                        
                        <div class="mb-2">
                            <label class="form-label fw-bold text-primary mb-2">Thay đổi Nhóm Quyền</label>
                            <select name="role_id" id="edit_role" class="form-select form-control-custom">
                                <option value="">-- Toàn quyền (Super Admin) --</option>
                                <?php if(!empty($roles)): ?>
                                    <?php foreach($roles as $r): ?>
                                        <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                        <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold w-100 py-2">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function() {
            if (typeof lucide !== 'undefined') lucide.createIcons();
            
            window.submitForm = function(url, form) {
                fetch(url, { method: 'POST', body: new FormData(form) }).then(res => res.json()).then(data => {
                    if (data.status === 'success') NeoUI.alert(data.alert, 'success', 'Thành công', () => window.location.reload());
                    else NeoUI.alert(data.alert || 'Lỗi', 'error');
                }).catch(err => NeoUI.toast('Lỗi kết nối máy chủ', 'error'));
            };

            window.openEditAdmin = function(btn) {
                document.getElementById('edit_uuid').value = btn.dataset.uuid;
                document.getElementById('edit_name').value = btn.dataset.name;
                document.getElementById('edit_email').value = btn.dataset.email;
                
                let roleSelect = document.getElementById('edit_role');
                roleSelect.value = btn.dataset.role || ''; 
                
                new bootstrap.Modal(document.getElementById('editAdminModal')).show();
            };

            window.toggleUserStatus = function(uuid, status) {
                let fd = new FormData();
                fd.append('uuid', uuid);
                fd.append('status', status);

                fetch('/admin/users/toggle-status', {
                    method: 'POST',
                    body: fd
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        NeoUI.toast('Đã cập nhật trạng thái!', 'success');
                    } else {
                        NeoUI.alert(data.alert || 'Lỗi', 'error');
                        setTimeout(() => window.location.reload(), 1000); 
                    }
                })
                .catch(err => {
                    NeoUI.toast('Lỗi kết nối máy chủ', 'error');
                    setTimeout(() => window.location.reload(), 1000);
                });
            };

            window.deleteUser = function(uuid) {
                NeoUI.confirm('Xóa quyền Quản trị viên này? Hành động này không thể hoàn tác.', () => {
                    let fd = new FormData(); fd.append('uuid', uuid);
                    window.submitForm('/admin/users/delete', document.createElement('form').appendChild(Object.assign(document.createElement('input'),{name:'uuid',value:uuid})).parentNode);
                }, 'warning', 'Xác nhận xóa');
            };
        })();
    </script>
<?php $this->endSection() ?>