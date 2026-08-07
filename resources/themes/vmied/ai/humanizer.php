<?php $this->extend('layouts/app') ?>

<?php $this->section('content') ?>

<?= $this->insert('ai/menu-ai',["user"=>$user,'active'=>'humanizer']) ?>
<section class="container py-4">
    <div class="card shadow-lg border overflow-hidden position-relative" style="border-radius: 2.5rem; min-height: 600px;">
        
        <div id="workspace-loading" class="d-none position-absolute top-0 start-0 w-100 h-100 z-3 bg-white bg-opacity-75 d-flex flex-column align-items-center justify-content-center" style="backdrop-filter: blur(4px);">
            <div class="position-relative mb-4">
                <div class="spinner-border text-primary" style="width: 5rem; height: 5rem; border-width: 4px;" role="status"></div>
                <div class="position-absolute top-50 start-50 translate-middle text-primary">
                    <i data-lucide="sparkles" style="width: 32px; height: 32px;"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-2">Đang phân tích...</h3>
            <p class="text-secondary small text-center" style="max-width: 300px;">Vui lòng không đóng trình duyệt. Quá trình này có thể mất vài giây.</p>
        </div>

        <div class="row g-0 h-100">
            
            <div class="col-lg-8 d-flex flex-column bg-white border-end h-100">
                
                <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center bg-white">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-info"><i data-lucide="wand-2" style="width: 20px; height: 20px;"></i></span>
                        <h5 id="workspace-title" class="fw-bold text-dark m-0">Humanizer AI</h5>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-light text-secondary border rounded-pill fw-normal px-3 py-2" id="word-count">0 từ</span>
                        <button class="btn btn-link text-secondary p-0 text-decoration-none hover-danger" title="Xóa" onclick="document.getElementById('main-editor').value = '';">
                            <i data-lucide="trash-2" style="width: 18px; height: 18px;"></i>
                        </button>
                    </div>
                </div>

                <div class="flex-grow-1">
                    <textarea id="main-editor" class="form-control h-100 w-100 border-0 shadow-none p-4 fs-5 text-secondary" style="resize: none; outline: none;" placeholder="Dán văn bản cần kiểm tra vào đây..."></textarea>
                </div>

                <div class="px-4 py-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-3 bg-white">
                    <button class="btn btn-light text-secondary fw-semibold d-flex align-items-center gap-2 rounded-3">
                        <i data-lucide="upload" style="width: 16px; height: 16px;"></i> Tải file (.docx)
                    </button>
                    <button onclick="runAnalysis()" class="btn btn-dark hover-primary text-white fw-bold py-2 px-4 rounded-3 d-flex align-items-center gap-2 shadow w-100 w-sm-auto justify-content-center">
                        <i data-lucide="sparkles" style="width: 16px; height: 16px;"></i> Phân tích ngay
                    </button>
                </div>
            </div>

            <div class="col-lg-4 bg-body-tertiary d-flex flex-column h-100 border-start">
                
                <div class="p-4 border-bottom">
                    <h6 class="fw-bold text-dark mb-3 small text-uppercase d-flex align-items-center gap-2">
                        <i data-lucide="sliders-horizontal" style="width: 14px; height: 14px;"></i> Cấu hình nâng cao
                    </h6>
                    
                    <div class="d-grid gap-3">
                        <div class="bg-white p-3 rounded-3 border shadow-sm">
                            <label class="form-label text-secondary fw-bold small mb-2">Chế độ kiểm tra</label>
                            <select id="config-mode" class="form-select form-select-sm border-0 bg-transparent shadow-none fw-semibold text-dark p-0">
                                <option value="standard">Tiêu chuẩn (Standard)</option>
                                <option value="deep">Chuyên sâu (Deep Scan)</option>
                                <option value="fast">Tốc độ cao (Fast)</option>
                            </select>
                        </div>

                        <div class="bg-white p-3 rounded-3 border shadow-sm">
                            <label class="form-label text-secondary fw-bold small mb-2">Ngôn ngữ nguồn</label>
                            <select class="form-select form-select-sm border-0 bg-transparent shadow-none fw-semibold text-dark p-0">
                                <option>Tiếng Việt</option>
                                <option>English</option>
                                <option>Tự động nhận diện</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex-grow-1 p-4 overflow-auto">
                    <h6 class="fw-bold text-dark mb-3 small text-uppercase">Kết quả phân tích</h6>

                    <div id="result-placeholder" class="h-100 d-flex flex-column align-items-center justify-content-center text-center opacity-50 pb-5">
                        <div class="bg-secondary-subtle rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i data-lucide="bar-chart-2" class="text-secondary" style="width: 32px; height: 32px;"></i>
                        </div>
                        <p class="small fw-medium text-secondary">Chưa có dữ liệu.</p>
                    </div>

                    <div id="result-content" class="d-none d-flex flex-column gap-3">
                        <div class="bg-white p-4 rounded-4 border shadow-sm text-center position-relative overflow-hidden">
                            <span class="text-secondary small fw-bold text-uppercase">Điểm tin cậy</span>
                            <div class="display-5 fw-bold text-dark my-1" id="score-value">92%</div>
                            <div class="badge bg-success-subtle text-success fw-bold px-2 py-1 rounded">An toàn</div>
                            <div class="position-absolute bottom-0 start-0 w-100" style="height: 4px; background: linear-gradient(to right, #f87171, #facc15, #22c55e);"></div>
                        </div>

                        <div class="bg-white rounded-4 border shadow-sm p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="small fw-bold text-secondary">Chi tiết</span>
                                <span class="small fw-bold text-dark">3 vấn đề</span>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <div class="p-2 bg-light rounded border text-secondary small d-flex align-items-center">
                                    <span class="d-inline-block rounded-circle bg-danger me-2" style="width: 8px; height: 8px;"></span>
                                    Câu nghi vấn AI (Đoạn 1)
                                </div>
                                <div class="p-2 bg-light rounded border text-secondary small d-flex align-items-center">
                                    <span class="d-inline-block rounded-circle bg-warning me-2" style="width: 8px; height: 8px;"></span>
                                    Từ ngữ lặp lại nhiều
                                </div>
                            </div>
                        </div>

                        <div class="row g-2 mt-1">
                            <div class="col-6">
                                <button class="btn btn-outline-secondary w-100 fw-bold small d-flex align-items-center justify-content-center gap-1 rounded-3 py-2">
                                    <i data-lucide="download" style="width: 14px; height: 14px;"></i> PDF
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-primary w-100 fw-bold small d-flex align-items-center justify-content-center gap-1 rounded-3 py-2 shadow-sm">
                                    <i data-lucide="share-2" style="width: 14px; height: 14px;"></i> Share
                                </button>
                            </div>
                        </div>
                    </div> </div>
            </div>
        </div>
    </div>
</section>
<?php $this->endSection() ?>