<?php $this->extend('layouts/admin') ?>

<?php $this->section('content') ?>
    <div class="container pt-5 mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <h3 class="fw-bold text-dark m-0">Học viên / Giảng viên</h3>
            
            <div class="d-flex align-items-center gap-3 flex-wrap">
                
                <form method="GET" action="/admin/members" class="m-0 d-flex gap-2">
                    <!-- BỘ LỌC DÀNH CHO ADMIN -->
                    <?php if($user->type == 1 && !empty($vipList)): ?>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white text-secondary border-secondary-subtle"><i data-lucide="filter" width="16"></i></span>
                            <select name="vip_ref" class="form-select border-secondary-subtle fw-medium text-secondary shadow-sm" onchange="this.form.submit()" style="min-width: 200px;">
                                <option value="">Tất cả các Trường/Đơn vị</option>
                                <?php foreach($vipList as $vip): ?>
                                    <option value="<?= $vip['affiliate'] ?>" <?= ($currentFilter == $vip['affiliate']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($vip['organization'] ?: $vip['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <!-- Giữ lại vip_ref nếu là form của VIP tự filter -->
                        <input type="hidden" name="vip_ref" value="<?= htmlspecialchars($currentFilter ?? '') ?>">
                    <?php endif; ?>

                    <!-- FORM TÌM KIẾM -->
                    <div class="input-group input-group-sm" style="min-width: 220px;">
                        <span class="input-group-text bg-white text-secondary border-secondary-subtle"><i data-lucide="search" width="16"></i></span>
                        <input type="text" name="q" class="form-control border-secondary-subtle shadow-sm" placeholder="Tìm tên, email..." value="<?= htmlspecialchars($q ?? '') ?>">
                        <?php if(!empty($q) || !empty($currentFilter)): ?>
                            <a href="/admin/members" class="btn btn-outline-secondary border-secondary-subtle" title="Xóa bộ lọc"><i data-lucide="refresh-cw" width="14"></i></a>
                        <?php endif; ?>
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
                                    <!-- NÚT KHÓA / MỞ KHÓA -->
                                    <?php if ($u['status'] == 1 || $u['status'] == 0): ?>
                                        <button onclick="window.toggleUserStatus('<?= $u['uuid'] ?>', 2)" class="btn btn-sm btn-outline-warning rounded-pill px-3 me-1" title="Khóa tài khoản">
                                            <i data-lucide="lock" width="14"></i> Khóa
                                        </button>
                                    <?php elseif ($u['status'] == 2): ?>
                                        <button onclick="window.toggleUserStatus('<?= $u['uuid'] ?>', 1)" class="btn btn-sm btn-outline-info rounded-pill px-3 me-1" title="Mở khóa tài khoản">
                                            <i data-lucide="unlock" width="14"></i> Mở
                                        </button>
                                    <?php endif; ?>

                                    <a href="/admin/members/history?uuid=<?= $u['uuid'] ?>" class="btn btn-sm btn-outline-dark rounded-pill me-1" title="Lịch sử quét">
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
                            <tr><td colspan="5" class="text-center py-5 text-secondary">Không tìm thấy thành viên nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php 
            $queryStr = '';
            if (!empty($currentFilter)) $queryStr .= '&vip_ref=' . htmlspecialchars($currentFilter);
            if (!empty($q)) $queryStr .= '&q=' . urlencode($q);
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

    <!-- Modal Add Member -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form onsubmit="event.preventDefault(); window.submitForm('/admin/users/add', this);">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="fw-bold">Thêm Thành Viên Mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="type" value="0">
                        <input type="hidden" name="point" value="0">
                        
                        <input type="hidden" name="ref_by" value="<?= htmlspecialchars($currentFilter ?? 0) ?>">

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
                <form onsubmit="event.preventDefault(); window.submitForm('/admin/users/update', this);">
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
                    window.submitForm('/admin/users/delete', document.createElement('form').appendChild(Object.assign(document.createElement('input'),{name:'uuid',value:uuid})).parentNode);
                }, 'warning', 'Xác nhận xóa');
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
        })();
    </script>
<?php $this->endSection() ?>