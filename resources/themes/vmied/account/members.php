<?php $this->extend('layouts/app') ?>

<?php $this->section('content') ?>
    <div class="container pt-5 mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <h3 class="fw-bold text-dark m-0">Học viên / Giảng viên</h3>
            
            <div class="d-flex align-items-center gap-3">
                <!-- BỘ LỌC DÀNH CHO ADMIN -->
                <?php if($user->type == 1 && !empty($vipList)): ?>
                <form method="GET" action="/admin/members" class="m-0">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white text-secondary border-secondary-subtle"><i data-lucide="filter" width="16"></i></span>
                        <select name="vip_ref" class="form-select border-secondary-subtle fw-medium text-secondary shadow-sm" onchange="this.form.submit()" style="min-width: 220px;">
                            <option value="">Tất cả các Trường/Đơn vị</option>
                            <?php foreach($vipList as $vip): ?>
                                <option value="<?= $vip['affiliate'] ?>" <?= ($currentFilter == $vip['affiliate']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($vip['organization'] ?: $vip['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
                <?php endif; ?>
                
                <form method="GET" action="/app/members" class="m-0">
                    <div class="input-group shadow-sm bg-white" style="border-radius: 50rem; overflow: hidden; border: 1px solid var(--bs-border-color-translucent);">
                        <span class="input-group-text bg-transparent border-0 text-secondary pe-1 ps-3 py-2"><i data-lucide="search" width="18"></i></span>
                        <input type="text" name="search" class="form-control border-0 bg-transparent py-2" placeholder="Tìm tên, email..." value="<?= htmlspecialchars($search ?? '') ?>" style="min-width: 220px; box-shadow: none !important; outline: none;">
                        <button type="submit" class="btn btn-light border-0 fw-bold px-4 py-2 text-secondary" style="background: #f8f9fa;">Tìm</button>
                    </div>
                </form>

                <button class="btn btn-primary btn-rounded fw-bold shadow-sm d-flex align-items-center gap-2 px-4 py-2 hover-lift" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i data-lucide="user-plus" width="18"></i> Thêm thành viên
                </button>
            </div>
        </div>

        <div class="card border shadow-sm overflow-hidden" style="border-radius: 2rem;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold">Thành viên</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold">Liên kết VIP (Ref)</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold text-end">Số dư VMIED</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold text-center">Trạng thái</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php if(!empty($members)): ?>
                            <?php foreach($members as $u): ?>
                            <tr class="transition-hover">
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative">
                                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($u['name']) ?>&background=0ea5e9&color=fff" class="rounded-circle shadow-sm" style="width: 40px; height: 40px;">
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($u['name']) ?></div>
                                            <div class="small text-secondary"><?= htmlspecialchars($u['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-secondary">
                                    <?php if(!empty($u['ref_by'])): ?>
                                        <span class="badge bg-light text-dark border border-secondary-subtle px-2 py-1"><i data-lucide="link" width="12" class="me-1"></i> <?= htmlspecialchars($u['ref_by']) ?></span>
                                    <?php else: ?>
                                        <span class="small text-muted fst-italic">Tự do</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <span class="fw-bold text-dark"><?= number_format($u['point'] ?? 0) ?> V</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="form-check form-switch d-flex justify-content-center align-items-center gap-2 m-0 p-0">
                                        <input class="form-check-input m-0" type="checkbox" role="switch" 
                                               style="cursor: pointer; width: 35px; height: 18px;"
                                               onchange="window.toggleUserStatus('<?= $u['uuid'] ?>', this.checked ? 1 : 0)"
                                               <?= (isset($u['status']) && $u['status'] == 1) ? 'checked' : '' ?>>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <a href="/app/members/history?uuid=<?= $u['uuid'] ?>" class="btn btn-sm btn-outline-dark rounded-pill me-1" title="Xem lịch sử quét và nạp tiền">
                                        <i data-lucide="history" width="16"></i> Lịch sử
                                    </a>
                                    <button class="btn btn-sm btn-outline-primary rounded-pill me-1" onclick="window.openEditUser(this)" data-uuid="<?= $u['uuid'] ?>" data-name="<?= htmlspecialchars($u['name'], ENT_QUOTES) ?>" data-email="<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>" title="Sửa">
                                        <i data-lucide="edit-2" width="16"></i> Sửa
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger rounded-pill" onclick="window.deleteUser('<?= $u['uuid'] ?>')" title="Xóa">
                                        <i data-lucide="trash-2" width="16"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-5 text-secondary">Chưa có thành viên nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
              <?php if (isset($totalPages) && $totalPages > 1): ?>
            <div class="p-4 bg-light bg-opacity-50 border-top d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <span class="small text-secondary">
                    Đang xem trang <?= $page ?> / <?= $totalPages ?> (Tối đa <?= $limit ?> thành viên/trang)
                </span>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link rounded-circle me-1 border-0 shadow-sm" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search ?? '') ?>">
                                <i data-lucide="chevron-left" width="14"></i>
                            </a>
                        </li>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link rounded-circle me-1 border-0 shadow-sm <?= ($i == $page) ? 'bg-primary' : '' ?>" href="?page=<?= $i ?>&search=<?= urlencode($search ?? '') ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link rounded-circle border-0 shadow-sm" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search ?? '') ?>">
                                <i data-lucide="chevron-right" width="14"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Add Member -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form onsubmit="event.preventDefault(); window.submitForm('/app/members/add', this);">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="fw-bold">Thêm Thành Viên Mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="type" value="0">
                        <input type="hidden" name="point" value="0">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-secondary">Họ và Tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-custom" name="name" required placeholder="Nhập tên thành viên...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-secondary">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control form-control-custom" name="email" required placeholder="email@example.com">
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold small text-secondary">Mật khẩu</label>
                            <input type="text" class="form-control form-control-custom" name="password" placeholder="Mặc định: 123456">
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold w-100 py-2">Tạo thành viên</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Member -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form onsubmit="event.preventDefault(); window.submitForm('/app/members/update', this);">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="fw-bold">Chỉnh sửa Thành Viên</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="uuid" id="edit_uuid">
                        <input type="hidden" name="type" value="0">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-secondary">Họ Tên</label>
                            <input type="text" class="form-control form-control-custom" name="name" id="edit_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-secondary">Email</label>
                            <input type="email" class="form-control form-control-custom" name="email" id="edit_email" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold small text-secondary">Mật khẩu mới</label>
                            <input type="text" class="form-control form-control-custom" name="password" placeholder="Bỏ trống nếu không đổi">
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold w-100 py-2">Lưu thay đổi</button>
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

            window.openEditUser = function(btn) {
                document.getElementById('edit_uuid').value = btn.dataset.uuid;
                document.getElementById('edit_name').value = btn.dataset.name;
                document.getElementById('edit_email').value = btn.dataset.email;
                new bootstrap.Modal(document.getElementById('editUserModal')).show();
            };

            window.deleteUser = function(uuid) {
                NeoUI.confirm('Xóa tài khoản này? Hành động này không thể hoàn tác.', () => {
                    let fd = new FormData(); fd.append('uuid', uuid);
                    window.submitForm('/app/members/delete', document.createElement('form').appendChild(Object.assign(document.createElement('input'),{name:'uuid',value:uuid})).parentNode);
                }, 'warning', 'Xác nhận xóa');
            };

            window.toggleUserStatus = function(uuid, status) {
                let fd = new FormData();
                fd.append('uuid', uuid);
                fd.append('status', status);

                fetch('/app/members/toggle-status', {
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
        })();
    </script>
<?php $this->endSection() ?>