<?php $this->extend('layouts/admin') ?>

<?php $this->section('content') ?>
    <div class="container pt-5 mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-dark m-0">Quản lý Đơn vị Liên kết (VIP)</h3>
            <button class="btn btn-primary btn-rounded fw-bold shadow-sm d-flex align-items-center gap-2 px-4 py-2 hover-lift" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i data-lucide="building" width="18"></i> Thêm Đơn Vị
            </button>
        </div>

        <div class="card border shadow-sm overflow-hidden" style="border-radius: 2rem;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold">Trường / Đơn vị</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold">Người quản lý</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold text-center">Mã LK</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold text-end">Số dư Quản lý</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold text-center">Trạng thái</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php if(!empty($vips)): ?>
                            <?php foreach($vips as $u): ?>
                            <tr class="transition-hover">
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if(!empty($u['avatar'])): ?>
                                            <img src="<?= $u['avatar'] ?>" class="rounded bg-white border" style="width: 45px; height: 45px; object-fit: contain;">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center rounded bg-secondary-subtle text-secondary" style="width: 45px; height: 45px;">
                                                <i data-lucide="building" style="width: 20px; height: 20px;"></i>
                                            </div>
                                        <?php endif; ?>
                                        <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($u['organization']) ?: '<em class="text-muted fw-normal">Chưa cập nhật tên</em>' ?></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-secondary">
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($u['name']) ?></div>
                                    <div class="small"><?= htmlspecialchars($u['email']) ?></div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle fs-6 px-3 py-2"><?= $u['affiliate'] ?></span>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <span class="fw-bold text-success fs-5"><?= number_format($u['point'] ?? 0) ?> ₫</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="form-check form-switch d-flex justify-content-center align-items-center gap-2 m-0 p-0">
                                        <input class="form-check-input m-0" type="checkbox" role="switch" 
                                               style="cursor: pointer; width: 35px; height: 18px;"
                                               onchange="window.toggleUserStatus('<?= $u['uuid'] ?>', this.checked ? 1 : 0)"
                                               <?= (isset($u['status']) && $u['status'] == 1) ? 'checked' : '' ?>>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-end text-nowrap">
                                    <!-- NÚT XEM THÀNH VIÊN -->
                                    <a href="/admin/members?vip_ref=<?= $u['affiliate'] ?>" class="btn btn-sm btn-outline-info rounded-pill me-1" title="Xem thành viên">
                                        <i data-lucide="users" width="16"></i> Thành viên
                                    </a>
                                
                                    <!-- NÚT SỬA -->
                                    <button class="btn btn-sm btn-outline-primary rounded-pill me-1" 
                                            onclick="window.openEditUser(this)" 
                                            data-uuid="<?= $u['uuid'] ?>" 
                                            data-name="<?= htmlspecialchars($u['name'], ENT_QUOTES) ?>" 
                                            data-email="<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>" 
                                            data-org="<?= htmlspecialchars($u['organization'], ENT_QUOTES) ?>" 
                                            data-avatar="<?= htmlspecialchars($u['avatar'] ?? '', ENT_QUOTES) ?>" 
                                            data-point="<?= $u['point'] ?>" title="Sửa">
                                        <i data-lucide="edit-2" width="16"></i> Sửa
                                    </button>
                                    
                                    <!-- NÚT XÓA (Tùy chọn) -->
                                    <button class="btn btn-sm btn-outline-danger rounded-pill" onclick="window.deleteUser('<?= $u['uuid'] ?>')" title="Xóa">
                                        <i data-lucide="trash-2" width="16"></i> Xóa
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-5 text-secondary">Chưa có đơn vị liên kết nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Add VIP -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form onsubmit="event.preventDefault(); window.submitForm('/admin/users/add', this);" enctype="multipart/form-data">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="fw-bold">Thêm Đơn vị Liên Kết</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="type" value="2">
                        <input type="hidden" name="point" value="0">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tên Đơn vị / Trường *</label>
                            <input type="text" class="form-control form-control-custom" name="organization" required placeholder="Vd: Đại học Quốc Gia...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Logo Đơn vị</label>
                            <input type="file" class="form-control form-control-custom" name="avatar" accept="image/*" style="padding-left: 1rem;">
                        </div>
                        <hr class="text-secondary opacity-25">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tên Quản trị viên *</label>
                            <input type="text" class="form-control form-control-custom" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Đăng nhập *</label>
                            <input type="email" class="form-control form-control-custom" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mật khẩu</label>
                            <input type="text" class="form-control form-control-custom" name="password" placeholder="Mặc định: 123456">
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Tạo Đơn vị</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit VIP -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form onsubmit="event.preventDefault(); window.submitForm('/admin/users/update', this);" enctype="multipart/form-data">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="fw-bold">Sửa Đơn vị Liên Kết</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="uuid" id="edit_uuid">
                        <input type="hidden" name="type" value="2">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tên Đơn vị</label>
                            <input type="text" class="form-control form-control-custom" name="organization" id="edit_org" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Logo Trường</label>
                            <div class="d-flex align-items-center gap-3 mb-2 p-2 bg-light rounded border" id="edit_avatar_container" style="display:none;">
                                <img id="edit_avatar_preview" src="" class="rounded border bg-white" style="width: 48px; height: 48px; object-fit: contain;">
                                <div class="form-check m-0">
                                    <input class="form-check-input" type="checkbox" name="delete_avatar" value="1" id="del_ava">
                                    <label class="form-check-label text-danger small fw-medium" for="del_ava">Xóa logo hiện tại</label>
                                </div>
                            </div>
                            <input type="file" class="form-control form-control-custom" name="avatar" accept="image/*" style="padding-left: 1rem;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Số dư phí quản lý</label>
                            <input type="number" class="form-control form-control-custom" name="point" id="edit_point">
                        </div>
                        <hr class="text-secondary opacity-25">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tên Quản trị viên</label>
                            <input type="text" class="form-control form-control-custom" name="name" id="edit_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Đăng nhập</label>
                            <input type="email" class="form-control form-control-custom" name="email" id="edit_email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mật khẩu mới</label>
                            <input type="text" class="form-control form-control-custom" name="password" placeholder="Bỏ trống nếu không đổi">
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Lưu thay đổi</button>
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
                document.getElementById('edit_org').value = btn.dataset.org;
                document.getElementById('edit_point').value = btn.dataset.point;
                
                let avaBox = document.getElementById('edit_avatar_container');
                let delCheck = document.getElementById('del_ava');
                delCheck.checked = false;

                if (btn.dataset.avatar) {
                    document.getElementById('edit_avatar_preview').src = btn.dataset.avatar;
                    avaBox.classList.add('d-flex'); avaBox.classList.remove('d-none');
                } else {
                    avaBox.classList.remove('d-flex'); avaBox.classList.add('d-none');
                }
                new bootstrap.Modal(document.getElementById('editUserModal')).show();
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
                NeoUI.confirm('Xóa tài khoản này? Hành động này không thể hoàn tác.', () => {
                    let fd = new FormData(); 
                    fd.append('uuid', uuid);
                    
                    fetch('/admin/users/delete', {
                        method: 'POST',
                        body: fd
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            NeoUI.alert('Xóa thành công', 'success', 'Thành công', () => window.location.reload());
                        } else {
                            NeoUI.alert(data.alert || 'Có lỗi xảy ra', 'error');
                        }
                    })
                    .catch(err => NeoUI.toast('Lỗi kết nối máy chủ', 'error'));
                }, 'warning', 'Xác nhận xóa');
            };
        })();
    </script>
<?php $this->endSection() ?>