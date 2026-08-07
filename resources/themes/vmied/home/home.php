<?php $this->extend('layouts/app') ?>

<?php $this->section('content') ?>
    <?= $this->insert('ai/menu-ai',["user"=>$user, 'active' => '']) ?>

    <div id="app-content" class="animate-fade-in" data-page-script="/js/history.js">
        
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <div class="container pt-3">
            <section class="py-4">
                <h3 class="fw-bold text-dark mb-4">Hoạt động gần đây</h3>

                <div class="card border shadow-sm overflow-hidden" style="border-radius: 2rem;">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light border-bottom">
                                <tr>
                                    <th class="px-4 py-3 text-secondary text-uppercase small fw-bold" style="min-width: 200px;">Tên tài liệu</th>
                                    <th class="px-4 py-3 text-secondary text-uppercase small fw-bold">Công cụ</th>
                                    <th class="px-4 py-3 text-secondary text-uppercase small fw-bold">Kết quả</th>
                                    <th class="px-4 py-3 text-secondary text-uppercase small fw-bold">Thời gian</th>
                                    <th class="px-4 py-3 text-secondary text-uppercase small fw-bold text-end">Thao tác</th>
                                </tr>
                            </thead>

                            <tbody class="border-top-0">
                                <?php if(!empty($activities)): ?>
                                    <?php foreach($activities as $act): ?>
                                    <tr class="transition-hover">
                                        <td class="px-4 py-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="d-flex align-items-center justify-content-center rounded-3 <?= $act['bg'] ?>" style="width: 40px; height: 40px;">
                                                    <i data-lucide="<?= $act['icon'] ?>" style="width: 20px; height: 20px;"></i>
                                                </div>
                                                <span class="fw-semibold text-dark"><?= $act['title'] ?></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <?php switch ($act['tool']):
                                                case 'ai_detector': ?>
                                                    <span class="badge rounded-pill d-inline-flex align-items-center gap-1 border" 
                                                          style="color: #9333ea; background-color: #faf5ff; border-color: #f3e8ff;">
                                                        <i data-lucide="bot" style="width: 12px; height: 12px;"></i> Check AI
                                                    </span>
                                                <?php break;
                                                case 'plagiarism': ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill d-inline-flex align-items-center gap-1">
                                                        <span class="bg-success rounded-circle" style="width: 6px; height: 6px;"></span> Đạo văn
                                                    </span>
                                                <?php break;
                                                case 'grammar': ?>
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill d-inline-flex align-items-center gap-1">
                                                        <i data-lucide="spell-check" style="width: 12px; height: 12px;"></i> Ngữ pháp
                                                    </span>
                                                <?php break;
                                                case 'readability': ?>
                                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill d-inline-flex align-items-center gap-1">
                                                        <i data-lucide="book-open" style="width: 12px; height: 12px;"></i> Đọc hiểu
                                                    </span>
                                                <?php break;
                                                default: ?>
                                                    <span class="badge bg-secondary-subtle text-secondary border rounded-pill">Chưa xác định</span>
                                            <?php endswitch; ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="fw-bold <?= $act['class'] ?>"><?= $act['result'] ?></span>
                                        </td>
                                        <td class="px-4 py-3 text-secondary">
                                            <?= date('H:i d/m', strtotime($act['time'])) ?>
                                        </td>
                                        <td class="px-4 py-3 text-end">
                                            <button type="button" 
                                                class="btn btn-light text-secondary hover-primary btn-sm rounded-3 p-2 border-0 btn-action transition-all"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#detailModal"
                                                data-id="<?= $act['id'] ?>">
                                                <i data-lucide="eye" style="width: 20px; height: 20px;"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-secondary">Bạn chưa thực hiện kiểm tra nào.</div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>

        <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header bg-light border-0">
                        <h5 class="fw-bold m-0 text-dark">
                            <i data-lucide="file-text" class="text-primary me-2" width="18"></i>Chi tiết tài liệu
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0" id="modal-body-content">
                        </div>
                </div>
            </div>
        </div>

    </div> <?php $this->endSection() ?>