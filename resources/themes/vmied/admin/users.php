<?php $this->extend('layouts/admin') ?>

<?php $this->section('content') ?>
    <div class="container pt-5 mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-dark m-0">Quản lý người dùng</h3>
            <button class="btn btn-primary btn-rounded fw-bold shadow-sm d-flex align-items-center gap-2 px-4 py-2 hover-lift" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i data-lucide="user-plus" width="18"></i> Thêm người dùng
            </button>
        </div>

        <div class="card border shadow-sm overflow-hidden" style="border-radius: 2rem;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold">Tài khoản</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold">Email</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold">Loại TK</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold">Tên đơn vị (Trường)</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold">Số dư (Point)</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php if(!empty($users)): ?>
                            <?php foreach($users as $u): ?>
                            <tr class="transition-hover">
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if(!empty($u['avatar'])): ?>
                                            <img src="<?= $u['avatar'] ?>" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center rounded-circle bg-secondary-subtle text-secondary" style="width: 40px; height: 40px;">
                                                <i data-lucide="user" style="width: 20px; height: 20px;"></i>
                                            </div>
                                        <?php endif; ?>
                                        <span class="fw-semibold text-dark"><?= $u['name'] ?></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-secondary"><?= htmlspecialchars($u['email'] ?? '') ?></td>
                                <td class="px-4 py-3">
                                    <?php if($u['type'] == 1): ?>
                                        <span class="badge bg-danger">Admin</span>
                                    <?php elseif($u['type'] == 2): ?>
                                        <span class="badge bg-primary">VIP Affiliate</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Member</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-secondary">
                                    <?= htmlspecialchars($u['organization'] ?? '') ?: '<em class="text-muted">Chưa có</em>' ?>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="fw-bold text-success"><?= number_format($u['point'] ?? 0) ?></span>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <button class="btn btn-sm btn-outline-primary rounded-pill me-1" 
                                        onclick="window.openEditUser(this)"
                                        data-uuid="<?= $u['uuid'] ?>"
                                        data-name="<?= htmlspecialchars($u['name'] ?? '', ENT_QUOTES) ?>"
                                        data-email="<?= htmlspecialchars($u['email'] ?? '', ENT_QUOTES) ?>"
                                        data-type="<?= $u['type'] ?>"
                                        data-point="<?= $u['point'] ?? 0 ?>"
                                        data-org="<?= htmlspecialchars($u['organization'] ?? '', ENT_QUOTES) ?>"
                                        data-avatar="<?= htmlspecialchars($u['avatar'] ?? '', ENT_QUOTES) ?>"
                                        title="Sửa">
                                        <i data-lucide="edit-2" width="16"></i> Sửa
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger rounded-pill"
                                        onclick="window.deleteUser('<?= $u['uuid'] ?>')"
                                        title="Xóa">
                                        <i data-lucide="trash-2" width="16"></i> Xóa
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-secondary">Chưa có người dùng nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Add User -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form id="formAddUser" onsubmit="event.preventDefault(); window.submitAddUser(this);" method="POST" enctype="multipart/form-data">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="fw-bold">Thêm Người dùng mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Họ và Tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-custom" name="name" required placeholder="Nhập họ và tên...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control form-control-custom" name="email" required placeholder="Nhập địa chỉ email...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mật khẩu</label>
                            <input type="text" class="form-control form-control-custom" name="password" placeholder="Mặc định: 123456">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Loại tài khoản</label>
                                <select class="form-select form-control-custom" name="type" id="add_type" onchange="document.getElementById('addVipFields').style.display = (this.value == '2' ? 'block' : 'none');">
                                    <option value="0">Thành viên thường</option>
                                    <option value="2">Tài khoản Liên kết VIP</option>
                                    <option value="1">Quản trị viên (Admin)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Số dư (Point)</label>
                                <input type="number" class="form-control form-control-custom" name="point" value="0">
                            </div>
                        </div>

                        <div id="addVipFields" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Tên Trường/Đơn vị</label>
                                <input type="text" class="form-control form-control-custom" name="organization" placeholder="Nhập tên trường...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Logo Trường/Đơn vị</label>
                                <input type="file" class="form-control form-control-custom" name="avatar" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Tạo tài khoản</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form id="formEditUser" onsubmit="event.preventDefault(); window.submitEditUser(this);" method="POST" enctype="multipart/form-data">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="fw-bold">Chỉnh sửa Tài Khoản</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="uuid" id="edit_uuid">
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Họ và Tên</label>
                            <input type="text" class="form-control form-control-custom" name="name" id="edit_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control form-control-custom" name="email" id="edit_email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mật khẩu mới</label>
                            <input type="text" class="form-control form-control-custom" name="password" placeholder="Bỏ trống nếu không muốn đổi">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Loại tài khoản</label>
                                <select class="form-select form-control-custom" name="type" id="edit_type" onchange="document.getElementById('editVipFields').style.display = (this.value == '2' ? 'block' : 'none');">
                                    <option value="0">Thành viên thường</option>
                                    <option value="2">Tài khoản Liên kết VIP</option>
                                    <option value="1">Quản trị viên (Admin)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Số dư (Point)</label>
                                <input type="number" class="form-control form-control-custom" name="point" id="edit_point">
                            </div>
                        </div>

                        <div id="editVipFields" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Tên Trường/Đơn vị</label>
                                <input type="text" class="form-control form-control-custom" name="organization" id="edit_org" placeholder="Nhập tên trường...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Logo Trường</label>
                                <div id="edit_avatar_container" class="mb-2" style="display: none;">
                                    <div class="d-flex align-items-center gap-3 p-2 border rounded bg-light">
                                        <img id="edit_avatar_preview" src="" class="rounded-circle border" style="width: 48px; height: 48px; object-fit: cover;">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input" type="checkbox" name="delete_avatar" value="1" id="delete_avatar_check">
                                            <label class="form-check-label text-danger" for="delete_avatar_check">
                                                Xóa logo hiện tại
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <input type="file" class="form-control form-control-custom" name="avatar" accept="image/*">
                                <small class="text-muted">Upload logo mới sẽ ghi đè avatar hiện tại.</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            window.submitAddUser = function(form) {
                fetch('/admin/users/add', {
                    method: 'POST',
                    body: new FormData(form)
                }).then(res => res.json()).then(data => {
                    if (data.status === 'success') {
                        NeoUI.alert('Thêm người dùng thành công', 'success', 'Thành công', () => window.location.reload());
                    } else {
                        NeoUI.alert(data.alert || 'Có lỗi xảy ra', 'error');
                    }
                }).catch(err => NeoUI.alert('Lỗi mạng. Vui lòng thử lại!', 'error'));
            };

            window.submitEditUser = function(form) {
                fetch('/admin/users/update', {
                    method: 'POST',
                    body: new FormData(form)
                }).then(res => res.json()).then(data => {
                    if (data.status === 'success') {
                        NeoUI.alert('Cập nhật thành công', 'success', 'Thành công', () => window.location.reload());
                    } else {
                        NeoUI.alert(data.alert || 'Có lỗi xảy ra', 'error');
                    }
                }).catch(err => NeoUI.alert('Lỗi mạng. Vui lòng thử lại!', 'error'));
            };

            window.openEditUser = function(btn) {
                document.getElementById('edit_uuid').value = btn.dataset.uuid;
                document.getElementById('edit_name').value = btn.dataset.name;
                document.getElementById('edit_email').value = btn.dataset.email;
                document.getElementById('edit_point').value = btn.dataset.point;
                document.getElementById('edit_type').value = btn.dataset.type;
                document.getElementById('edit_org').value = btn.dataset.org;
                
                const avatarContainer = document.getElementById('edit_avatar_container');
                const avatarPreview = document.getElementById('edit_avatar_preview');
                const deleteAvatarCheck = document.getElementById('delete_avatar_check');
                
                deleteAvatarCheck.checked = false;
                
                if (btn.dataset.avatar) {
                    avatarPreview.src = btn.dataset.avatar;
                    avatarContainer.style.display = 'block';
                } else {
                    avatarPreview.src = '';
                    avatarContainer.style.display = 'none';
                }
                
                if (btn.dataset.type == '2') {
                    document.getElementById('editVipFields').style.display = 'block';
                } else {
                    document.getElementById('editVipFields').style.display = 'none';
                }
                const editModalEl = document.getElementById('editUserModal');
                if(editModalEl) {
                    new bootstrap.Modal(editModalEl).show();
                }
            };

            window.deleteUser = function(uuid) {
                NeoUI.confirm('Bạn có chắc chắn muốn xóa tài khoản này không?', function() {
                    const formData = new FormData();
                    formData.append('uuid', uuid);
                    fetch('/admin/users/delete', {
                        method: 'POST',
                        body: formData
                    }).then(res => res.json()).then(data => {
                        if (data.status === 'success') {
                            NeoUI.alert('Xóa thành công', 'success', 'Thành công', () => window.location.reload());
                        } else {
                            NeoUI.alert(data.alert || 'Có lỗi xảy ra', 'error');
                        }
                    }).catch(err => NeoUI.alert('Lỗi mạng. Vui lòng thử lại!', 'error'));
                }, 'warning', 'Xác nhận xóa');
            };
        })();
    </script>
<?php $this->endSection() ?>
