<?php $this->extend('layouts/app') ?>
<?php $this->section('content') ?>
<?= $this->insert('ai/menu-ai', ["user"=>$user, 'active'=>'ai']) ?>

<section class="container py-4" 
         x-data="AIChecker()" 
         x-cloak 
         data-ai-endpoint="/app/ai/scan">

    <div class="card shadow-lg border-0 rounded-4 overflow-hidden position-relative" style="min-height: 800px;">
       
        <!-- Loading overlay -->
        <div x-show="isLoading" x-cloak
             class="position-absolute top-0 start-0 w-100 h-100 z-3 bg-white bg-opacity-75"
             style="backdrop-filter: blur(8px);">
            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center">
                <div class="spinner-border text-primary mb-3" style="width: 4rem; height: 4rem; border-width: 4px;"></div>
                <h3 class="fw-black text-dark tracking-tight">AI Đang Tổng Hợp Báo Cáo...</h3>
                <p class="text-secondary fw-medium">Đạo văn • Ngữ pháp • Sự thật • SEO</p>
            </div>
        </div>

        <div class="row g-0 h-100">
            <!-- ===== CỘT TRÁI: Editor ===== -->
            <div class="col-lg-8 d-flex flex-column bg-white border-end h-100">
               
                <!-- Header toolbar -->
                <div class="px-4 py-3 border-bottom bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                                <i data-lucide="layout-template"></i>
                            </div>
                            <div>
                                <h5 class="fw-black m-0 text-dark lh-1" 
                                    x-text="isAnalyzed ? 'Báo cáo Văn bản' : 'Trạm Kiểm duyệt Nội dung'"></h5>
                                <span class="extra-small text-muted fw-bold">V3 API</span>
                            </div>
                        </div>
                        <span class="badge bg-white text-dark shadow-sm border px-3 py-2 rounded-pill fw-bold"
                              x-show="_scanMode !== 'pdf-file'"
                              x-text="wordCount + ' từ'"></span>
                        <span class="badge bg-white text-dark shadow-sm border px-3 py-2 rounded-pill fw-bold"
                              x-show="_scanMode === 'pdf-file'" x-cloak>
                            <i data-lucide="file-text" class="size-4 me-1"></i> File PDF
                        </span>
                    </div>

                    <!-- Cấu hình Model & Từ khóa SEO -->
                    <div class="d-flex flex-wrap align-items-center gap-3 mt-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="small fw-bold text-muted">MÔ HÌNH:</span>
                            <select class="form-select form-select-sm border-0 bg-white shadow-sm rounded-pill w-auto fw-bold"
                                    x-model="params.aiModelVersion" :disabled="isAnalyzed">
                                <option value="turbo">⚡ Turbo Fast</option>
                                <option value="lite">🎯 Standard Lite</option>
                                <option value="multilang">🌏 Đa ngôn ngữ</option>
                            </select>
                        </div>

                        <!-- NHẬP TỪ KHÓA SEO (Chỉ hiện khi bật Tối ưu SEO) -->
                        <div class="d-flex align-items-center gap-2" x-show="params.check_contentOptimizer" x-cloak>
                            <span class="small fw-bold text-muted">TỪ KHÓA SEO:</span>
                            <input type="text" class="form-control form-control-sm border-0 bg-white shadow-sm rounded-pill fw-bold px-3"
                                   placeholder="Nhập từ khóa (vd: Mobile Hotspot)..." 
                                   x-model="params.optimizerQuery" 
                                   :disabled="isAnalyzed"
                                   style="min-width: 250px;">
                        </div>
                    </div>
                </div>

                <!-- Vùng editor / kết quả highlight -->
                <div class="flex-grow-1 p-4 custom-scroll overflow-auto" style="max-height: 600px;">
                    <textarea x-show="!isAnalyzed && _scanMode !== 'pdf-file'"
                              x-model="params.content"
                              class="form-control w-100 border-0 shadow-none fs-5 text-secondary"
                              style="resize: none; min-height:500px; max-height:600px;"
                              placeholder="Bắt đầu gõ hoặc dán nội dung của bạn vào đây..."></textarea>

                    <!-- PDF File mode: ẩn textarea, hiển thị thông báo gửi file gốc cho AI -->
                    <div x-show="!isAnalyzed && _scanMode === 'pdf-file'" x-cloak
                         class="h-100 d-flex flex-column align-items-center justify-content-center text-center"
                         style="min-height:500px;">
                        <div class="bg-primary bg-opacity-10 p-4 rounded-circle text-primary mb-3">
                            <i data-lucide="file-text" class="size-16"></i>
                        </div>
                        <h5 class="fw-black text-dark mb-1" x-text="uploadFileName"></h5>
                        <p class="text-secondary fw-medium mb-3" style="max-width: 420px;">
                            File PDF gốc sẽ được gửi trực tiếp cho AI để phân tích (AI, đạo văn, ngữ pháp, đọc hiểu, SEO...),
                            không cần trích xuất văn bản trước.
                        </p>
                        <button @click="clearFile()" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                            <i data-lucide="x" class="size-4 me-1"></i> Chọn file khác
                        </button>
                    </div>

                    <div x-show="isAnalyzed" class="fs-5 lh-lg" style="white-space: pre-wrap;">
                        <template x-for="block in results?.ai?.blocks">
                            <span :class="getBlockClass(block)"
                                  @click="openDetailModal(block)"
                                  data-bs-toggle="tooltip" 
                                  data-bs-html="true"
                                  :title="getTooltip(block)"
                                  x-html="(block.htmlText || block.text) + (/\s$/.test(block.text || '') ? '' : ' ')"></span>
                        </template>
                    </div>
                </div>

                <!-- Footer actions -->
                <div class="p-3 border-top bg-white d-flex justify-content-between align-items-center shadow-sm">
                    <div class="d-flex align-items-center gap-3">
                        <div x-show="!isAnalyzed" class="d-flex align-items-center gap-2">
                            <input type="file" x-ref="docxInput" class="d-none" accept=".pdf,.doc,.docx,.rtf,.odt,.txt" @change="handleFileChange($event)">
                            
                            <button @click="$refs.docxInput.click()" class="btn btn-outline-secondary btn-sm border-dashed px-3">
                                <i data-lucide="upload" class="size-4 me-1"></i> Tải file
                            </button>
                            
                            <div x-show="uploadFileName" class="small bg-light border rounded px-2 py-1 d-flex align-items-center gap-2">
                                <span x-text="uploadFileName" class="text-truncate" style="max-width: 120px;"></span>
                                <i data-lucide="x" class="size-3 text-danger cursor-pointer" @click="clearFile()"></i>
                            </div>
                        </div>
                
                        <a href="/app/historys" 
                           hx-boost="true" 
                           hx-target="#app-content" 
                           hx-select="#app-content" 
                           hx-swap="outerHTML show:window:top"
                           class="text-muted small fw-medium text-decoration-none hover-dark">
                            <i data-lucide="history" class="size-4 me-1"></i> Lịch sử
                        </a>
                    </div>
                
                    <div class="d-flex align-items-center gap-2">
                        <template x-if="isAnalyzed">
                            <div class="dropdown">
                                <button class="btn btn-light border btn-sm px-3 fw-bold dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i data-lucide="settings" class="size-4 me-1"></i> Tùy chọn
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <button @click="editAgain()" class="dropdown-item d-flex align-items-center gap-2">
                                            <i data-lucide="refresh-cw" class="size-4"></i> Quét lại
                                        </button>
                                    </li>
                                    <li>
                                        <a :href="'/app/report/ai?id=' + scanId" target="_blank" class="dropdown-item d-flex align-items-center gap-2">
                                            <i data-lucide="download" class="size-4"></i> Tải PDF
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a :href="'/app/report/original?id=' + scanId" target="_blank" class="dropdown-item d-flex align-items-center gap-2">
                                            <i data-lucide="file-text" class="size-4"></i> Xuất bản gốc
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </template>
                    
                        <button x-show="!isAnalyzed"
                                @click="previewCost()"
                                :disabled="isLoading || isPreviewLoading"
                                class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2">
                            <template x-if="isPreviewLoading">
                                <span class="spinner-border spinner-border-sm me-1"></span>
                            </template>
                            <template x-if="!isPreviewLoading">
                                <span>KIỂM TRA NGAY <i data-lucide="zap" class="size-4"></i></span>
                            </template>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ===== CỘT PHẢI: Kết quả ===== -->
            <div class="col-lg-4 bg-light">
                <div class="p-4 custom-scroll h-100 overflow-auto" style="max-height: 800px;">
                   
                    <template x-if="!isAnalyzed">
                        <div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                            <i data-lucide="bar-chart-3" class="size-16 text-primary mb-3"></i>
                            <h6 class="fw-bold text-dark">Chưa có dữ liệu</h6>
                            <p class="small text-center">Bấm "Kiểm tra ngay" để xem báo cáo chi tiết.</p>
                        </div>
                    </template>
            
                    <template x-if="results">
                        <div class="d-flex flex-column gap-3 pb-5">
                            <template x-if="params.check_ai && results.ai">
                                <div class="card border-0 shadow-sm rounded-4 bg-white">
                                    <div class="card-header bg-white border-bottom p-4">
                                        <h6 class="fw-bold mb-0 text-dark">ĐIỂM AI</h6>
                                    </div>
                                    <div class="card-body p-4 text-center">
                                        <div class="chart-container mb-4 position-relative d-inline-block">
                                            <svg width="260" height="260" viewBox="0 0 42 42" style="transform: rotate(-90deg);">
                                                <circle class="donut-ring" cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#34c38f" stroke-width="4.5"></circle>
                                                <circle class="donut-segment" cx="21" cy="21" r="15.91549430918954" fill="transparent" 
                                                        stroke="#f46a6a" :stroke-width="results.ai.confidence.AI > 0 ? 4.5 : 0" 
                                                        :stroke-dasharray="parseFloat((results.ai.confidence.AI * 100).toFixed(2)) + ' ' + (100 - parseFloat((results.ai.confidence.AI * 100).toFixed(2)))" 
                                                        stroke-dashoffset="25" stroke-linecap="butt" style="transition: stroke-dasharray 0.5s ease;"></circle>
                                            </svg>
                                            <div class="position-absolute top-50 start-50 translate-middle w-100 text-center">
                                                <div class="display-6 fw-bold" 
                                                     :class="results.ai.confidence.AI >= 0.5 ? 'text-danger' : 'text-success'" 
                                                     x-text="parseFloat((Math.max(results.ai.confidence.AI, results.ai.confidence.Original) * 100).toFixed(2)) + '%'"></div>
                                                <div class="small text-secondary fw-bold" x-text="results.ai.confidence.AI >= 0.5 ? 'AI VIẾT' : 'NGƯỜI VIẾT'"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-center gap-4 mb-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <span style="width: 12px; height: 12px; border-radius: 3px; background-color: #f46a6a; display: inline-block;"></span>
                                                <span class="small fw-bold text-secondary" x-text="parseFloat((results.ai.confidence.AI * 100).toFixed(2)) + '% AI viết'"></span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span style="width: 12px; height: 12px; border-radius: 3px; background-color: #34c38f; display: inline-block;"></span>
                                                <span class="small fw-bold text-secondary" x-text="parseFloat((results.ai.confidence.Original * 100).toFixed(2)) + '% người viết'"></span>
                                            </div>
                                        </div>
                                        
                                        <h6 class="fw-bold mb-2" :class="results.ai.confidence.AI >= 0.5 ? 'text-danger' : 'text-success'" x-text="'Có khả năng là ' + (results.ai.confidence.AI >= 0.5 ? 'AI' : 'Nguyên bản') + ' - Độ tin cậy ' + parseFloat((Math.max(results.ai.confidence.AI, results.ai.confidence.Original) * 100).toFixed(2)) + '%.'"></h6>
                                        <p class="small text-secondary mb-3" x-text="'Chúng tôi tự tin rằng văn bản được quét là ' + (results.ai.confidence.AI >= 0.5 ? 'do AI tạo ra' : 'do con người viết') + ', nhưng điều đó KHÔNG có nghĩa là ' + parseFloat((Math.max(results.ai.confidence.AI, results.ai.confidence.Original) * 100).toFixed(2)) + '% văn bản được tạo ra đều là ' + (results.ai.confidence.AI >= 0.5 ? 'do AI' : 'do con người') + ' tạo ra.'"></p>
            
                                        <div class="text-start small text-muted border-top pt-3">
                                            <div><strong>Mô hình:</strong> <span x-text="results.ai.aiModel || params.aiModelVersion || 'Đa ngôn ngữ'"></span></div>
                                            <div><strong>Ngày:</strong> <?= date('d \t\h\á\n\g m \n\ă\m Y') ?></div>
                                        </div>
                                    </div>
                                </div>
                            </template>
            
                            <template x-if="params.check_ai && results.ai">
                                <div class="card border-0 shadow-sm rounded-4 bg-white">
                                    <div class="card-body p-4 small text-secondary">
                                        <div class="mb-3">
                                            <h6 class="fw-bold text-dark mb-1"><i data-lucide="info" class="me-1 text-primary" style="width:16px;height:16px;"></i> Làm thế nào để đọc điểm số?</h6>
                                            <p class="mb-0 lh-base">
                                                Điểm số này phản ánh mức độ tin cậy của chúng tôi rằng văn bản được quét đã được tạo ra bởi công cụ AI.
                                            </p>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1"><i data-lucide="highlighter" class="me-1 text-primary" style="width:16px;height:16px;"></i> Ý nghĩa phần tô sáng</h6>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <div style="width:16px;height:16px;background:#f87171;border-radius:4px;"></div>
                                                <span>Rất có thể là AI viết (&ge; 70%)</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <div style="width:16px;height:16px;background:#fbbf24;border-radius:4px;"></div>
                                                <span>Có thể là AI viết (40% - 69%)</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <div style="width:16px;height:16px;background:#4ade80;border-radius:4px;"></div>
                                                <span>Người viết (&lt; 40%)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
            
                            <template x-if="(params.check_plagiarism && results.plagiarism) || (params.check_grammar && results.grammarSpelling)">
                                <div class="row g-2">
                                    <template x-if="params.check_plagiarism && results.plagiarism">
                                        <div class="col-6">
                                            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
                                                <span class="small fw-bold text-warning-emphasis mb-1">
                                                    <i data-lucide="copy" class="size-4 me-1"></i> Đạo văn
                                                </span>
                                                <h3 class="fw-black mb-0" x-text="(results.plagiarism ? results.plagiarism.score : 0) + '%'"></h3>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="params.check_grammar && results.grammarSpelling">
                                        <div class="col-6">
                                            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
                                                <span class="small fw-bold text-danger mb-1">
                                                    <i data-lucide="spell-check" class="size-4 me-1"></i> Ngữ pháp
                                                </span>
                                                <h3 class="fw-black mb-0 text-danger" x-text="(results.grammarSpelling ? results.grammarSpelling.matches.length : 0) + ' Lỗi'"></h3>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
            
                            <template x-if="params.check_facts && results.facts && results.facts.length > 0">
                                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                                    <div class="px-3 py-2 small fw-bold d-flex justify-content-between"
                                         :class="falseFacts.length > 0 ? 'bg-danger text-white' : 'bg-success text-white'">
                                        <span>
                                            <i data-lucide="shield-check" class="size-4 me-1" x-show="falseFacts.length === 0"></i>
                                            <i data-lucide="alert-triangle" class="size-4 me-1" x-show="falseFacts.length > 0"></i>
                                            KIỂM CHỨNG SỰ THẬT
                                        </span>
                                        <span class="badge bg-white"
                                              :class="falseFacts.length > 0 ? 'text-danger' : 'text-success'"
                                              x-text="falseFacts.length > 0 ? falseFacts.length + ' vấn đề' : '100% Chính xác'"></span>
                                    </div>
                                    <div class="card-body p-0">
                                        <template x-for="(fact, index) in results.facts">
                                            <div class="border-bottom" x-data=\"{ open: false }\">
                                                <button @click="open = !open"
                                                        class="w-100 d-flex justify-content-between align-items-start bg-transparent border-0 p-3 text-start hover-light">
                                                    <div class="d-flex gap-3" style="min-width:0;flex:1;">
                                                        <div class="fs-4 fw-black text-secondary opacity-50 mt-1" x-text="index + 1"></div>
                                                        <div style="min-width:0;flex:1;">
                                                            <div class="fw-bold text-dark mb-2 lh-base fs-6" :class="open ? '' : 'text-truncate'" x-text="fact.fact"></div>
                                                            <span class="badge" :class="factBadgeClass(fact)" x-text="fact.truthfulness + ' Độ chính xác'"></span>
                                                        </div>
                                                    </div>
                                                    <i data-lucide="chevron-down" class="size-4 text-muted transition-transform mt-1 flex-shrink-0" :style="open ? 'transform:rotate(180deg)' : ''"></i>
                                                </button>
                                                <div x-show="open" x-collapse>
                                                    <div class="p-3 pt-0 ms-5 ps-2 border-start mb-3">
                                                        <div class="small text-muted mb-3" x-text="fact.explanation"></div>
                                                        <template x-if="fact.links && fact.links.length > 0">
                                                            <div class="mt-2 pt-2 border-top border-secondary border-opacity-25">
                                                                <div class="extra-small fw-bold text-muted mb-2" x-text="'Nguồn tham khảo (' + fact.links.length + ')'"></div>
                                                                <div class="d-flex flex-column gap-2">
                                                                    <template x-for="(link, li) in fact.links">
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <i data-lucide="link" class="size-3 text-primary flex-shrink-0" style="min-width:14px;"></i>
                                                                            <a :href="link" target="_blank" class="extra-small text-primary text-decoration-none fw-medium text-truncate" x-text="link"></a>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
            
                            <template x-if="params.check_plagiarism && results.plagiarism && results.plagiarism.results && results.plagiarism.results.length > 0">
                                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4"
                                     x-data="{
                                        get uniqueSources() {
                                            const sources = {};
                                            (results.plagiarism?.results ?? []).forEach(phraseResult => {
                                                const phrase = phraseResult.phrase ?? '';
                                                const srcs = phraseResult.results ?? phraseResult.sources ?? [];
                                                srcs.forEach(s => {
                                                    const link  = s.link ?? s.url ?? '';
                                                    const title = s.title ?? s.name ?? link;
                                                    const score = s.scores?.[0]?.score ?? 0;
                                                    if (!link) return;
                                                    if (!sources[link]) {
                                                        sources[link] = {
                                                            link, title,
                                                            score,
                                                            timestamps: s.timestamps ?? {},
                                                            phrases: [],
                                                        };
                                                    } else if (score > sources[link].score) {
                                                        sources[link].score = score;
                                                    }
                                                    if (phrase) sources[link].phrases.push(phrase);
                                                });
                                            });
                                            return Object.values(sources).sort((a,b) => b.score - a.score);
                                        }
                                     }">
                                    <div class="px-3 py-2 small fw-bold d-flex justify-content-between bg-warning text-dark">
                                        <span><i data-lucide="copy" class="size-4 me-1"></i>NGUỒN ĐẠO VĂN</span>
                                        <span class="badge bg-white text-dark" x-text="results.plagiarism.score + '% Trùng lặp'"></span>
                                    </div>
                                    <div class="card-body p-0">
                            
                                        <div class="px-3 py-2 bg-light border-bottom fw-bold text-secondary text-uppercase small d-flex align-items-center gap-2">
                                            <i data-lucide="globe" class="size-4"></i> Nguồn trùng lặp (<span x-text="uniqueSources.length"></span>)
                                        </div>
                                        <div class="d-flex flex-column">
                                            <template x-for="(source, index) in uniqueSources">
                                                <div class="border-bottom" x-data="{ open: false }">
                                                    <button @click="open = !open" class="w-100 d-flex justify-content-between align-items-start bg-transparent border-0 p-3 text-start hover-light">
                                                        <div class="d-flex gap-3" style="min-width:0;flex:1;">
                                                            <div class="fs-4 fw-black text-secondary opacity-50 mt-1" x-text="index + 1"></div>
                                                            <div style="min-width:0;flex:1;">
                                                                <div class="fw-bold text-dark mb-1 text-truncate" x-text="source.title"></div>
                                                                <a :href="source.link" target="_blank" rel="noopener noreferrer" class="extra-small text-primary text-decoration-none d-block text-truncate mb-1" x-text="source.link" @click.stop></a>
                                                                <span class="badge bg-danger" x-text="(source.score * 100).toFixed(0) + '%'"></span>
                                                            </div>
                                                        </div>
                                                        <i data-lucide="chevron-down" class="size-4 text-muted transition-transform mt-1 flex-shrink-0" :style="open ? 'transform:rotate(180deg)' : ''"></i>
                                                    </button>
                                                    <div x-show="open" x-collapse>
                                                        <div class="p-3 pt-0 ms-5 ps-2 border-start mb-3">
                                                            <template x-if="source.timestamps?.date_modified || source.timestamps?.date_published">
                                                                <div class="mb-3 p-2 bg-light rounded-3 small">
                                                                    <div class="fw-bold mb-1">Chi tiết trang</div>
                                                                    <template x-if="source.timestamps?.date_modified">
                                                                        <div class="text-secondary">Cập nhật: <span class="text-dark" x-text="source.timestamps.date_modified.split('T')[0]"></span></div>
                                                                    </template>
                                                                    <template x-if="source.timestamps?.date_published">
                                                                        <div class="text-secondary">Xuất bản: <span class="text-dark" x-text="source.timestamps.date_published.split('T')[0]"></span></div>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                            <div>
                                                                <span class="extra-small fw-bold text-uppercase text-secondary" x-text="'Cụm từ trùng (' + source.phrases.length + ')'"></span>
                                                                <div class="d-flex flex-column gap-2 mt-1">
                                                                    <template x-for="(phrase, pi) in source.phrases">
                                                                        <div class="p-2 bg-light rounded-3 border small text-secondary">
                                                                            <span class="fw-bold text-dark me-1" x-text="(pi+1) + '.'"></span>
                                                                            <span x-text="phrase"></span>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                            
                                        <div class="px-3 py-2 bg-light border-bottom fw-bold text-secondary text-uppercase small d-flex align-items-center gap-2">
                                            <i data-lucide="align-left" class="size-4"></i> Cụm từ trùng lặp (<span x-text="results.plagiarism.results.length"></span>)
                                        </div>
                                        <template x-for="(phraseResult, index) in results.plagiarism.results">
                                            <div class="border-bottom" x-data="{ open: false }">
                                                <button @click="open = !open" class="w-100 d-flex justify-content-between align-items-start bg-transparent border-0 p-3 text-start hover-light">
                                                    <div class="d-flex gap-3" style="min-width:0;flex:1;">
                                                        <div class="fs-4 fw-black text-secondary opacity-50 mt-1" x-text="index + 1"></div>
                                                        <div style="min-width:0;flex:1;">
                                                            <div class="fw-bold text-dark mb-2 lh-base fs-6" :class="open ? '' : 'text-truncate'" x-text="phraseResult.phrase"></div>
                                                            <span class="badge bg-warning text-dark" x-text="(phraseResult.results?.length ?? phraseResult.sources?.length ?? 0) + ' nguồn trùng'"></span>
                                                        </div>
                                                    </div>
                                                    <i data-lucide="chevron-down" class="size-4 text-muted transition-transform mt-1 flex-shrink-0" :style="open ? 'transform:rotate(180deg)' : ''"></i>
                                                </button>
                                                <div x-show="open" x-collapse>
                                                    <div class="p-3 pt-0 ms-5 ps-2 border-start mb-3">
                                                        <div class="d-flex flex-column gap-3 mt-2">
                                                            <template x-for="src in (phraseResult.results ?? phraseResult.sources ?? [])">
                                                                <div>
                                                                    <div class="d-flex align-items-center gap-2 mb-1">
                                                                        <span class="badge bg-danger" x-text="((src.scores?.[0]?.score ?? 0) * 100).toFixed(0) + '%'"></span>
                                                                        <a :href="src.link ?? src.url ?? '#'" target="_blank" rel="noopener noreferrer" class="fw-bold text-primary text-decoration-none text-truncate small" :title="src.title ?? src.link ?? ''" x-text="src.title ?? src.name ?? src.link ?? src.url ?? 'Xem nguồn'"></a>
                                                                    </div>
                                                                    <a :href="src.link ?? src.url ?? '#'" target="_blank" rel="noopener noreferrer" class="extra-small text-muted text-truncate d-block mb-1 text-decoration-none hover-primary" x-text="src.link ?? src.url ?? ''"></a>
                                                                    <template x-if="src.scores?.[0]?.sentence">
                                                                        <div class="p-2 bg-light rounded-3 small text-secondary fst-italic border" x-text="'&quot;' + src.scores[0].sentence + '&quot;'"></div>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
            
                            <template x-if="params.check_contentOptimizer && (results.contentOptimizer || results.content_optimizer)">
                                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white" x-data="{ seoData: results.contentOptimizer || results.content_optimizer }">
                                    <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0 text-primary"><i data-lucide="search" class="size-4 me-2"></i>TỐI ƯU SEO</h6>
                                        <span class="badge bg-primary" x-text="'Điểm: ' + (parseFloat(seoData.score ?? seoData.content_score ?? 0).toFixed(1))"></span>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="d-flex flex-column gap-2">
                                            <div class="border rounded-3 overflow-hidden" x-data="{ open: true }">
                                                <button @click="open = !open" class="w-100 d-flex justify-content-between align-items-center bg-light border-0 p-3 text-start">
                                                    <span class="fw-bold small text-muted text-uppercase">Cấu trúc nội dung</span>
                                                    <i data-lucide="chevron-down" class="size-4 transition-transform" :style="open ? 'transform: rotate(180deg)' : ''"></i>
                                                </button>
                                                <div x-show="open" x-collapse>
                                                    <div class="p-3 border-top bg-white">
                                                        <div class="row g-2">
                                                            <div class="col-4">
                                                                <div class="p-2 bg-light rounded-3 text-center border">
                                                                    <div class="extra-small text-muted mb-1">Từ</div>
                                                                    <div class="fw-bold text-dark" x-text="seoData.word_count_range?.current ?? 0"></div>
                                                                    <div class="extra-small text-secondary" x-text="(seoData.word_count_range?.min ?? 0) + ' - ' + (seoData.word_count_range?.max ?? 0)"></div>
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="p-2 bg-light rounded-3 text-center border">
                                                                    <div class="extra-small text-muted mb-1">Tiêu đề</div>
                                                                    <div class="fw-bold text-dark" x-text="seoData.heading_count_range?.current ?? 0"></div>
                                                                    <div class="extra-small text-secondary" x-text="(seoData.heading_count_range?.min ?? 0) + ' - ' + (seoData.heading_count_range?.max ?? 0)"></div>
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="p-2 bg-light rounded-3 text-center border">
                                                                    <div class="extra-small text-muted mb-1">Đoạn văn</div>
                                                                    <div class="fw-bold text-dark" x-text="seoData.paragraph_count_range?.current ?? 0"></div>
                                                                    <div class="extra-small text-secondary" x-text="(seoData.paragraph_count_range?.min ?? 0) + ' - ' + (seoData.paragraph_count_range?.max ?? 0)"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
            
                            <template x-if="params.check_readability && results.readability">
                                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                        <h6 class="small fw-bold m-0">ĐỌC HIỂU (READABILITY)</h6>
                                        <span class="badge bg-info text-white" x-text="'Điểm: ' + results.readability.readability.fleschReadingEase"></span>
                                    </div>
                            
                                    <h6 class="extra-small fw-bold text-muted mb-2">Thống kê văn bản</h6>
                                    <div class="row g-2 text-center mb-3">
                                        <div class="col-4">
                                            <div class="bg-light p-2 rounded-3">
                                                <div class="extra-small text-muted">Từ độc nhất</div>
                                                <div class="h6 fw-black text-dark m-0" x-text="results.readability.text_stats.uniqueWordCount"></div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="bg-light p-2 rounded-3">
                                                <div class="extra-small text-muted">Số câu</div>
                                                <div class="h6 fw-black text-dark m-0" x-text="results.readability.text_stats.sentenceCount"></div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="bg-light p-2 rounded-3">
                                                <div class="extra-small text-muted">Số đoạn</div>
                                                <div class="h6 fw-black text-dark m-0" x-text="results.readability.text_stats.paragraphCount"></div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="bg-light p-2 rounded-3">
                                                <div class="extra-small text-muted">Đọc (phút)</div>
                                                <div class="h6 fw-black text-dark m-0" x-text="Math.ceil(results.readability.text_stats.averageReadingTime) + 'p'"></div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="bg-light p-2 rounded-3">
                                                <div class="extra-small text-muted">Nói (phút)</div>
                                                <div class="h6 fw-black text-dark m-0" x-text="Math.ceil(results.readability.text_stats.averageSpeakingTime) + 'p'"></div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="bg-light p-2 rounded-3">
                                                <div class="extra-small text-muted">Viết (phút)</div>
                                                <div class="h6 fw-black text-dark m-0" x-text="Math.ceil(results.readability.text_stats.averageWritingTime) + 'p'"></div>
                                            </div>
                                        </div>
                                    </div>
                            
                                    <h6 class="extra-small fw-bold text-muted mb-2">Chỉ số đánh giá</h6>
                                    <div class="d-flex flex-column gap-1">
                                        <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded-3">
                                            <span class="extra-small fw-bold">Flesch-Kincaid Reading Ease</span>
                                            <span class="badge bg-primary" x-text="results.readability.readability.fleschReadingEase"></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded-3">
                                            <span class="extra-small fw-bold">Flesch-Kincaid Grade Level</span>
                                            <span class="badge bg-secondary" x-text="'Grade ' + results.readability.readability.fleschGradeLevel"></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded-3">
                                            <span class="extra-small fw-bold">Gunning Fog Index</span>
                                            <span class="badge bg-secondary" x-text="results.readability.readability.gunningFoxIndex"></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded-3">
                                            <span class="extra-small fw-bold">Dale-Chall Readability</span>
                                            <span class="badge bg-secondary" x-text="'Grade ' + results.readability.readability.daleChallReadabilityGrade"></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded-3">
                                            <span class="extra-small fw-bold">Smog Index</span>
                                            <span class="badge bg-secondary" x-text="results.readability.readability.smogIndex"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
            
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal xác nhận chi phí -->
    <div class="modal fade" id="costConfirmModal" tabindex="-1" aria-hidden="true" x-cloak>
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                            <i data-lucide="receipt" class="size-5"></i>
                        </div>
                        <h6 class="fw-black m-0 text-dark">Xác nhận kiểm tra</h6>
                    </div>
                </div>
                <div class="modal-body px-4 pt-3 pb-2" x-show="costPreview">
                    <!-- Chi phí -->
                    <div class="bg-light rounded-3 p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted fw-medium">Số từ</span>
                            <span class="fw-bold text-dark" 
                                  x-text="costPreview?.wordCount > 0 
                                    ? costPreview.wordCount.toLocaleString('vi-VN') + ' từ' 
                                    : 'File PDF'">
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted fw-medium">Chi phí</span>
                            <span class="fw-black fs-5"
                                  :class="costPreview?.enough ? 'text-primary' : 'text-danger'"
                                  x-text="(costPreview?.cost ?? 0).toLocaleString('vi-VN') + 'đ'">
                            </span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted fw-medium">Số dư hiện tại</span>
                            <span class="fw-bold"
                                  :class="costPreview?.enough ? 'text-success' : 'text-danger'"
                                  x-text="(costPreview?.balance ?? 0).toLocaleString('vi-VN') + 'đ'">
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2" 
                             x-show="costPreview?.enough">
                            <span class="small text-muted fw-medium">Số dư sau khi quét</span>
                            <span class="fw-bold text-secondary"
                                  x-text="((costPreview?.balance ?? 0) - (costPreview?.cost ?? 0)).toLocaleString('vi-VN') + 'đ'">
                            </span>
                        </div>
                    </div>
    
                    <!-- Không đủ tiền -->
                    <template x-if="costPreview && !costPreview.enough">
                        <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3">
                            <i data-lucide="alert-triangle" class="size-4 me-1"></i>
                            Số dư không đủ. Vui lòng nạp thêm điểm.
                        </div>
                    </template>
    
                    <!-- Ghi chú ước tính -->
                    <template x-if="costPreview?.note === 'Ước tính từ dữ liệu PDF'">
                        <p class="extra-small text-muted mb-3">
                            * Chi phí PDF là ước tính, có thể thay đổi sau khi quét xong.
                        </p>
                    </template>
    
                    <!-- Danh sách checks đang bật -->
<div class="d-flex flex-wrap gap-1 mb-3">
    <template x-for="c in checkList.filter(c => params[c.key])">
        <span class="badge rounded-pill border px-2 py-1 fw-medium small"
              :class="`text-bg-${c.color}`"
              x-text="c.label">
        </span>
    </template>
</div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2">
                    <button type="button" class="btn btn-light border rounded-pill px-3 fw-medium flex-grow-1"
                            data-bs-dismiss="modal">
                        Huỷ
                    </button>
                    <button type="button"
                            @click="confirmAndRun()"
                            :disabled="!costPreview?.enough"
                            class="btn btn-primary rounded-pill px-3 fw-bold flex-grow-1 d-flex align-items-center justify-content-center gap-2">
                        <i data-lucide="zap" class="size-4"></i> Xác nhận
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal chi tiết block -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-light border-0">
                    <h5 class="fw-bold m-0 text-dark">
                        <i data-lucide="zoom-in" class="text-primary me-2"></i>Chi tiết Vấn đề
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" x-show="modalData">
                    <div class="p-3 bg-light rounded-3 mb-4 text-dark fst-italic border fs-6">
                        "<span x-text="modalData?.text"></span>"
                    </div>
                    <template x-if="modalData?.plagiarism">
                        <div>
                            <h6 class="fw-black text-warning mb-2">
                                <i data-lucide="copy" class="size-4"></i> TRÙNG LẶP NỘI DUNG
                            </h6>
                            <a :href="modalData?.plagiarism?.results[0]?.link" target="_blank" 
                               class="fw-bold text-primary"
                               x-text="modalData?.plagiarism?.results[0]?.title"></a>
                            <span class="badge bg-warning text-dark ms-2"
                                  x-text="(modalData?.plagiarism?.results[0]?.scores[0]?.score * 100).toFixed(0) + '% Match'"></span>
                        </div>
                    </template>
                    <template x-if="modalData?.grammarErrors && modalData.grammarErrors.length > 0">
                        <div class="mt-3">
                            <h6 class="fw-black text-danger mb-2">
                                <i data-lucide="spell-check" class="size-4"></i> LỖI NGỮ PHÁP / CHÍNH TẢ (<span x-text="modalData.grammarErrors.length"></span>)
                            </h6>
                            <div class="d-flex flex-column gap-2">
                                <template x-for="err in modalData.grammarErrors">
                                    <div class="p-3 bg-danger bg-opacity-10 border border-danger rounded-3">
                                        <div class="fw-bold text-danger mb-1" x-text="err.message || err.shortMessage"></div>
                                        <template x-if="err.replacements && err.replacements.length > 0">
                                            <div class="mt-2 text-dark small">💡 Gợi ý thay thế: 
                                                <template x-for="rep in err.replacements.slice(0,3)">
                                                    <span class="badge bg-success bg-opacity-25 border border-success text-success me-1 fs-6" x-text="rep.value"></span>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="/js/ai.js"></script>
<style>
.transition-transform {
    transition: transform 0.3s ease;
}
.hover-light:hover {
    background-color: var(--bs-light) !important;
}
</style>
<?php $this->endSection() ?>