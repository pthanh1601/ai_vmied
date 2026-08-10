<?php $this->extend('layouts/admin') ?>

<?php $this->section('content') ?>
    <div class="container pt-5 mt-5 pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <h3 class="fw-bold text-dark m-0">Quản lý Nhóm Quyền</h3>
            
            <div class="d-flex align-items-center gap-3">
                <!-- FORM TÌM KIẾM -->
                <form method="GET" action="/admin/roles" class="m-0">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white text-secondary border-secondary-subtle"><i data-lucide="search" width="16"></i></span>
                        <input type="text" name="q" class="form-control border-secondary-subtle shadow-sm" placeholder="Tìm tên nhóm quyền..." value="<?= htmlspecialchars($q ?? '') ?>">
                        <?php if(!empty($q)): ?>
                            <a href="/admin/roles" class="btn btn-outline-secondary border-secondary-subtle" title="Xóa bộ lọc"><i data-lucide="x" width="14"></i></a>
                        <?php endif; ?>
                    </div>
                </form>

                <button class="btn btn-primary btn-rounded fw-bold shadow-sm d-flex align-items-center gap-2 px-4 py-2 hover-lift" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                    <i data-lucide="shield-plus" width="18"></i> Tạo Nhóm Quyền
                </button>
            </div>
        </div>

        <div class="card border shadow-sm overflow-hidden" style="border-radius: 2rem;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold" style="width: 60px;">ID</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold">Tên Nhóm Quyền</th>
                            <th class="px-4 py-3 text-secondary text-uppercase small fw-bold text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php if(!empty($roles)): ?>
                            <?php foreach($roles as $r): ?>
                            <tr class="transition-hover">
                                <td class="px-4 py-3 text-secondary fw-medium">#<?= $r['id'] ?></td>
                                <td class="px-4 py-3">
                                    <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($r['name']) ?></span>
                                </td>
                                <td class="px-4 py-3 text-end text-nowrap">
                                    <button class="btn btn-sm btn-outline-primary rounded-pill me-1" 
                                            onclick='openEditRole(<?= $r['id'] ?>, <?= json_encode(htmlspecialchars($r['name'], ENT_QUOTES)) ?>, <?= $r['permissions'] ?: "{}" ?>)' 
                                            title="Sửa quyền">
                                        <i data-lucide="edit-2" width="16"></i> Sửa Quyền
                                    </button>
                                    
                                    <button class="btn btn-sm btn-outline-danger rounded-pill" 
                                            onclick="deleteRole(<?= $r['id'] ?>)" 
                                            title="Xóa nhóm quyền">
                                        <i data-lucide="trash-2" width="16"></i> Xóa
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center py-5 text-secondary">Không tìm thấy Nhóm quyền nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php 
            // Giữ lại bộ lọc tìm kiếm trên URL 
            $queryStr = !empty($q) ? '&q=' . urlencode($q) : ''; 
            ?>
            
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

    <!-- Modal Thêm/Sửa Nhóm Quyền -->
    <div class="modal fade" id="roleModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0 shadow">
                <form id="roleForm" onsubmit="event.preventDefault(); window.submitForm(this.action, this);">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="fw-bold" id="roleModalTitle">Tạo Nhóm Quyền Mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="id" id="role_id">
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold small text-secondary">Tên Nhóm Quyền <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-custom" name="name" id="role_name" required placeholder="Ví dụ: Kế toán, Trưởng phòng...">
                        </div>
                        
                        <label class="form-label fw-bold text-primary mb-3"><i data-lucide="check-square" width="18" class="me-1"></i> Danh sách Phân quyền</label>
                        <div class="row g-3">
                            <?php if(!empty($availPerms)): ?>
                                <?php foreach($availPerms as $groupName => $perms): ?>
                                <div class="col-md-6">
                                    <div class="card card-body bg-light border-secondary-subtle h-100">
                                        <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom"><?= htmlspecialchars($groupName) ?></h6>
                                        <div class="d-flex flex-column gap-2">
                                            <?php foreach($perms as $key => $label): ?>
                                            <div class="form-check m-0">
                                                <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]" value="<?= $key ?>" id="p_<?= str_replace('.', '_', $key) ?>" style="cursor: pointer;">
                                                <label class="form-check-label text-secondary" for="p_<?= str_replace('.', '_', $key) ?>" style="cursor: pointer;">
                                                    <?= htmlspecialchars($label) ?>
                                                </label>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 text-muted fst-italic">Chưa có dữ liệu phân quyền.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold w-100 py-2">Lưu Nhóm Quyền</button>
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

            window.openEditRole = function(id, name, currentPerms) {
                document.getElementById('roleModalTitle').innerHTML = '<i data-lucide="edit-3" width="20" class="me-2 text-dark"></i>Sửa Nhóm Quyền';
                document.getElementById('roleForm').action = '/admin/roles/update';
                document.getElementById('role_id').value = id;
                document.getElementById('role_name').value = name;
                
                document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
                
                if(currentPerms && typeof currentPerms === 'object') {
                    Object.keys(currentPerms).forEach(key => {
                        let safeKey = key.replace(/\./g, '_'); 
                        let cb = document.getElementById('p_' + safeKey);
                        if(cb) cb.checked = true;
                    });
                }
                
                if (typeof lucide !== 'undefined') lucide.createIcons();
                new bootstrap.Modal(document.getElementById('roleModal')).show();
            };

            document.querySelector('[data-bs-target="#addRoleModal"]')?.addEventListener('click', function() {
                document.getElementById('roleModalTitle').innerHTML = '<i data-lucide="shield-plus" width="20" class="me-2 text-dark"></i>Tạo Nhóm Quyền Mới';
                document.getElementById('roleForm').action = '/admin/roles/add';
                document.getElementById('role_id').value = '';
                document.getElementById('role_name').value = '';
                document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
                
                if (typeof lucide !== 'undefined') lucide.createIcons();
                new bootstrap.Modal(document.getElementById('roleModal')).show();
            });

            window.deleteRole = function(id) {
                NeoUI.confirm('Xóa Nhóm quyền này? Các tài khoản đang dùng nhóm này có thể bị mất quyền.', () => {
                    let fd = new FormData(); fd.append('id', id);
                    window.submitForm('/admin/roles/delete', document.createElement('form').appendChild(Object.assign(document.createElement('input'),{name:'id',value:id})).parentNode);
                }, 'warning', 'Xác nhận xóa');
            };
        })();
    </script>
<?php $this->endSection() ?>