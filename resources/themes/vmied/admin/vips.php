<?php $this->extend('layouts/admin') ?>

<?php $this->section('content') ?>
    <div class="container pt-5 mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <h3 class="fw-bold text-dark m-0">Quản lý Đơn vị Liên kết (VIP)</h3>
            
            <div class="d-flex align-items-center gap-3">
                <!-- FORM TÌM KIẾM -->
                <form method="GET" action="/admin/vips" class="m-0">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white text-secondary border-secondary-subtle"><i data-lucide="search" width="16"></i></span>
                        <input type="text" name="q" class="form-control border-secondary-subtle shadow-sm" placeholder="Tìm tên, email..." value="<?= htmlspecialchars($q ?? '') ?>">
                        <?php if(!empty($q)): ?>
                            <a href="/admin/vips" class="btn btn-outline-secondary border-secondary-subtle" title="Xóa bộ lọc"><i data-lucide="x" width="14"></i></a>
                        <?php endif; ?>
                    </div>
                </form>

                <button class="btn btn-primary btn-rounded fw-bold shadow-sm d-flex align-items-center gap-2 px-4 py-2 hover-lift" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i data-lucide="building" width="18"></i> Thêm Đơn Vị
                </button>
            </div>
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
                                <td class="px-4 py-3 text-center">
                                    <?php if ($u['status'] == 0): ?>
                                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i data-lucide="clock" width="14" class="me-1"></i> Chờ duyệt</span>
                                    <?php elseif ($u['status'] == 1): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-3 py-2 rounded-pill"><i data-lucide="check-circle" width="14" class="me-1"></i> Hoạt động</span>
                                    <?php elseif ($u['status'] == 2): ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle px-3 py-2 rounded-pill"><i data-lucide="lock" width="14" class="me-1"></i> Đã khóa</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-end text-nowrap">
                                    <!-- NÚT DUYỆT (Chỉ hiện khi status = 0) -->
                                    <?php if ($u['status'] == 0): ?>
                                        <button onclick="window.approveVip('<?= $u['uuid'] ?>', '<?= htmlspecialchars($u['organization'], ENT_QUOTES) ?>')" class="btn btn-sm btn-success rounded-pill fw-bold shadow-sm px-3 me-1">
                                            <i data-lucide="check" width="16"></i> Duyệt
                                        </button>
                                    <?php endif; ?>

                                    <!-- NÚT KHÓA / MỞ KHÓA -->
                                    <?php if ($u['status'] == 1): ?>
                                        <button onclick="window.toggleUserStatus('<?= $u['uuid'] ?>', 2)" class="btn btn-sm btn-outline-warning rounded-pill px-3 me-1" title="Khóa tài khoản">
                                            <i data-lucide="lock" width="14"></i> Khóa
                                        </button>
                                    <?php elseif ($u['status'] == 2): ?>
                                        <button onclick="window.toggleUserStatus('<?= $u['uuid'] ?>', 1)" class="btn btn-sm btn-outline-info rounded-pill px-3 me-1" title="Mở khóa tài khoản">
                                            <i data-lucide="unlock" width="14"></i> Mở
                                        </button>
                                    <?php endif; ?>

                                    <!-- NÚT XEM THÀNH VIÊN -->
                                    <a href="/admin/members?vip_ref=<?= $u['affiliate'] ?>" class="btn btn-sm btn-outline-dark rounded-pill me-1" title="Xem thành viên">
                                        <i data-lucide="users" width="16"></i> TV
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
                                    
                                    <!-- NÚT XÓA -->
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

            <?php 
            $queryStr = !empty($q) ? '&q=' . urlencode($q) : ''; 
            ?>
            
            <!-- THANH PHÂN TRANG -->
            <?php if (isset($totalPages) && $totalPages > 1): ?>
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-center">
                <nav aria-label="Điều hướng trang">
                    <ul class="pagination pagination-sm mb-0 shadow-sm">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link px-3" href="?page=<?= $page - 1 ?><?= $queryStr ?>" <?= ($page <= 1) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                <i data-lucide="chevron-left" width="16"></i>
                            </a>
                        </li>
                        
                        <?php 
                        $startPage = max(1, $page - 2);
                        $endPage   = min($totalPages, $page + 2);
                        
                        if ($startPage > 1) {
                            echo '<li class="page-item"><a class="page-link fw-medium" href="?page=1' . $queryStr . '">1</a></li>';
                            if ($startPage > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }

                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link fw-medium" href="?page=<?= $i ?><?= $queryStr ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; 
                        
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link fw-medium" href="?page='.$totalPages.$queryStr.'">'.$totalPages.'</a></li>';
                        }
                        ?>
                        
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link px-3" href="?page=<?= $page + 1 ?><?= $queryStr ?>" <?= ($page >= $totalPages) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                <i data-lucide="chevron-right" width="16"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
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
                        setTimeout(() => window.location.reload(), 800);
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

            window.approveVip = function(uuid, orgName) {
                Swal.fire({
                    title: 'Duyệt Đơn vị VIP?',
                    html: `Bạn có chắc chắn muốn duyệt cho đơn vị <b>${orgName}</b>? <br><small class="text-secondary">Sau khi duyệt, đơn vị này có thể bắt đầu giới thiệu học viên.</small>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Đồng ý Duyệt',
                    cancelButtonText: 'Hủy bỏ'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let fd = new FormData();
                        fd.append('uuid', uuid);

                        fetch('/admin/vips/approve', {
                            method: 'POST',
                            body: fd
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                NeoUI.alert('Thành công', 'success', 'Đã duyệt', () => window.location.reload());
                            } else {
                                NeoUI.alert(data.alert || 'Lỗi', 'error');
                            }
                        })
                        .catch(err => NeoUI.toast('Lỗi kết nối máy chủ', 'error'));
                    }
                });
            }
        })();
    </script>
<?php $this->endSection() ?>