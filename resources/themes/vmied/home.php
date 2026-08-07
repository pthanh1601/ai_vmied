    <?php $this->extend('layouts/master') ?>

    <?php $this->section('content') ?>
    <!-- NAVIGATION -->
    <nav id="navbar" class="navbar navbar-expand-lg fixed-top glass-nav py-3">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand d-flex align-items-center gap-3" href="#">
                <div class="navbar-brand-box">V</div>
                <div class="d-flex flex-column">
                    <span class="fw-bold fs-5 lh-1 text-dark" style="letter-spacing: -0.5px;">AI Vmied</span>
                    <span class="text-secondary fw-semibold text-uppercase mt-1" style="font-size: 10px; letter-spacing: 1px;">Viện Nghiên Cứu PTGD Việt Mỹ</span>
                </div>
            </a>

            <!-- Mobile Toggle -->
            <button class="navbar-toggler border-0 p-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i data-lucide="menu" class="text-dark" width="24" height="24"></i>
            </button>

            <!-- Desktop Menu -->
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center gap-lg-4 text-sm fw-bold">
                    <li class="nav-item"><a class="nav-link text-secondary hover-primary" href="#about">Về chúng tôi</a></li>
                    <li class="nav-item"><a class="nav-link text-secondary hover-primary" href="#demo">Dùng thử</a></li>
                    <li class="nav-item"><a class="nav-link text-secondary hover-primary" href="#features">Tính năng</a></li>
                    <li class="nav-item"><a class="nav-link text-secondary hover-primary" href="#pricing">Bảng giá</a></li>
                    <li class="nav-item"><a class="nav-link text-secondary hover-primary" href="#contact-section">Liên hệ</a></li>
                    <li class="nav-item mt-3 mt-lg-0">
                        <?php if($user) { ?>
                            <a href="/app" class="btn btn-primary fw-bold btn-rounded px-4 py-2 d-flex align-items-center gap-2 shadow-sm hover-lift">
                                Ứng dụng <i data-lucide="arrow-right" width="16"></i>
                            </a>
                        <?php } else {?>
                            <a href="/login" class="btn btn-dark btn-rounded px-4 py-2 d-flex align-items-center gap-2 shadow-sm hover-lift">
                                Đăng nhập <i data-lucide="arrow-right" width="16"></i>
                            </a>
                        <?php } ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="pt-5 pb-5 mt-5 position-relative overflow-hidden">
        <!-- Background Elements -->
        <div class="blob bg-info" style="top: -10%; right: -10%; width: 500px; height: 500px;"></div>
        <div class="blob bg-primary" style="bottom: -10%; left: -10%; width: 400px; height: 400px; animation-delay: 2s;"></div>

        <div class="container pt-5 text-center position-relative z-1">
            <!-- Trust Badge -->
            <div class="d-inline-flex align-items-center gap-2 px-3 py-2 bg-white border rounded-pill shadow-sm mb-4 cursor-default animate-float">
                <span class="position-relative d-flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-100 w-100 rounded-circle bg-success opacity-75"></span>
                  <span class="relative inline-flex rounded-circle h-2 w-2 bg-success" style="width: 8px; height: 8px;"></span>
                </span>
                <span class="text-secondary fw-bold" style="font-size: 12px; letter-spacing: 0.5px;">GIẤY PHÉP BKH&CN SỐ A-1953</span>
            </div>

            <h1 class="display-3 fw-bolder mb-4 text-dark lh-sm tracking-tight">
                Chuẩn Hóa Học Thuật <br class="d-none d-md-block" />
                Với Sức Mạnh <span class="text-gradient">Trí Tuệ Nhân Tạo</span>
            </h1>

            <p class="lead text-secondary mx-auto mb-5 fw-normal" style="max-width: 700px; font-size: 1.25rem;">
                Giải pháp toàn diện từ <strong>Viện Nghiên Cứu Phát Triển Giáo Dục Việt Mỹ</strong>. Kiểm tra đạo văn, phát hiện AI, và tối ưu hóa văn bản tiếng Việt/Anh chuyên sâu.
            </p>

            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                <a href="#demo" class="btn btn-dark btn-rounded px-5 py-3 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-lg hover-translate-up" style="transition: all 0.3s;">
                    <i data-lucide="sparkles" width="20"></i> Trải nghiệm miễn phí
                </a>
                <a href="#about" class="btn btn-white bg-white border btn-rounded px-5 py-3 fw-bold d-flex align-items-center justify-content-center gap-2 hover-shadow">
                    <i data-lucide="info" width="20"></i> Về VMIED
                </a>
            </div>
        </div>
    </section>

    <!-- PARTNERS SLIDE -->
    <section class="py-4 border-top border-bottom bg-white overflow-hidden">
        <div class="container text-center mb-4">
            <small class="fw-bold text-secondary text-opacity-50 text-uppercase" style="letter-spacing: 2px; font-size: 0.75rem;">Được tin dùng bởi các tổ chức giáo dục hàng đầu</small>
        </div>
        <div class="scroll-wrapper">
            <div class="scroll-container">
                <!-- Content Block (Repeating) -->
                <div class="d-flex gap-5 px-5 opacity-50 grayscale" style="filter: grayscale(100%); transition: filter 0.3s;">
                    <div class="d-flex align-items-center gap-2 h4 fw-bold font-monospace text-dark mb-0"><i data-lucide="graduation-cap"></i> UNI HCMC</div>
                    <div class="d-flex align-items-center gap-2 h4 fw-bold font-monospace text-dark mb-0"><i data-lucide="library"></i> EDU CENTER</div>
                    <div class="d-flex align-items-center gap-2 h4 fw-bold font-monospace text-dark mb-0"><i data-lucide="globe-2"></i> GLOBAL LANG</div>
                    <div class="d-flex align-items-center gap-2 h4 fw-bold font-monospace text-dark mb-0"><i data-lucide="microscope"></i> VAYSE LAB</div>
                    <div class="d-flex align-items-center gap-2 h4 fw-bold font-monospace text-dark mb-0"><i data-lucide="book-open-check"></i> EDU TECH</div>
                    <!-- Duplicate 1 -->
                    <div class="d-flex align-items-center gap-2 h4 fw-bold font-monospace text-dark mb-0"><i data-lucide="graduation-cap"></i> UNI HCMC</div>
                    <div class="d-flex align-items-center gap-2 h4 fw-bold font-monospace text-dark mb-0"><i data-lucide="library"></i> EDU CENTER</div>
                    <div class="d-flex align-items-center gap-2 h4 fw-bold font-monospace text-dark mb-0"><i data-lucide="globe-2"></i> GLOBAL LANG</div>
                    <div class="d-flex align-items-center gap-2 h4 fw-bold font-monospace text-dark mb-0"><i data-lucide="microscope"></i> VAYSE LAB</div>
                    <div class="d-flex align-items-center gap-2 h4 fw-bold font-monospace text-dark mb-0"><i data-lucide="book-open-check"></i> EDU TECH</div>
                     <!-- Duplicate 2 -->
                     <div class="d-flex align-items-center gap-2 h4 fw-bold font-monospace text-dark mb-0"><i data-lucide="graduation-cap"></i> UNI HCMC</div>
                     <div class="d-flex align-items-center gap-2 h4 fw-bold font-monospace text-dark mb-0"><i data-lucide="library"></i> EDU CENTER</div>
                </div>
            </div>
        </div>
    </section>

    <!-- DEMO SECTION -->
    <section id="demo" class="py-5 bg-light position-relative">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-dark display-6">Trải nghiệm sức mạnh AI</h2>
                <p class="text-secondary">Phân tích văn bản miễn phí (Giới hạn 200 từ/lần)</p>
            </div>

            <div class="card rounded-3xl shadow-lg border-0 overflow-hidden bg-white ring-1 ring-black ring-opacity-5">
                <div class="row g-0" style="min-height: 600px;">
                    <!-- LEFT: INPUT -->
                    <div class="col-lg-7 d-flex flex-column border-end">
                        <div class="p-4 border-bottom bg-white d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark d-flex align-items-center gap-2">
                                <i data-lucide="pen-line" class="text-primary" width="18"></i> Nhập nội dung
                            </span>
                            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill fw-normal">
                                <span id="word-count">0</span>/200 từ
                            </span>
                        </div>
                        
                        <div class="position-relative flex-grow-1 bg-white">
                            <textarea id="demo-input" class="form-control demo-textarea w-100 h-100 p-4 fs-5 text-dark" placeholder="Dán văn bản của bạn vào đây (tiếng Việt hoặc tiếng Anh)... &#10;Ví dụ: Trí tuệ nhân tạo đang thay đổi cách chúng ta học tập và làm việc..."></textarea>
                            
                            <!-- Loading Overlay -->
                            <div id="loading-overlay" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-90 d-flex flex-column align-items-center justify-content-center d-none" style="backdrop-filter: blur(4px); z-index: 10;">
                                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem; border-width: 4px;"></div>
                                <p class="text-primary fw-bold animate-pulse">Đang phân tích dữ liệu...</p>
                                <small class="text-secondary">Đang đối chiếu 10 triệu nguồn dữ liệu</small>
                            </div>
                        </div>

                        <div class="p-4 bg-white border-top">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                <div class="d-flex gap-2">
                                    <button onclick="simulateProcess('plagiarism')" class="btn btn-dark rounded-3 fw-bold d-flex align-items-center gap-2 py-2 px-3 shadow-sm">
                                        <i data-lucide="scan-search" width="16"></i> Check Đạo văn
                                    </button>
                                    <button onclick="simulateProcess('ai')" class="btn btn-outline-secondary rounded-3 fw-bold d-flex align-items-center gap-2 py-2 px-3">
                                        <i data-lucide="bot" width="16"></i> Check AI
                                    </button>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button onclick="alert('Tính năng dành cho thành viên!')" class="btn btn-light text-secondary p-2 rounded-3 hover-primary"><i data-lucide="upload-cloud" width="20"></i></button>
                                    <div class="vr mx-1 text-secondary opacity-25"></div>
                                    <button onclick="clearText()" class="btn btn-light text-secondary p-2 rounded-3 hover-danger"><i data-lucide="trash-2" width="20"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: RESULTS -->
                    <div class="col-lg-5 bg-light p-4 p-md-5 d-flex flex-column position-relative">
                        <div class="position-absolute top-0 end-0 p-5 pe-none">
                            <div class="blob bg-primary opacity-10" style="width: 200px; height: 200px;"></div>
                        </div>

                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-4 position-relative z-1">
                            <h5 class="fw-bold text-dark d-flex align-items-center gap-2 mb-0">
                                <i data-lucide="bar-chart-3" class="text-primary"></i> Kết quả
                            </h5>
                            <span class="badge bg-white text-secondary border text-uppercase" style="font-size: 10px; letter-spacing: 1px;">Demo Mode</span>
                        </div>

                        <!-- Empty State -->
                        <div id="result-empty" class="flex-grow-1 d-flex flex-column align-items-center justify-content-center text-center opacity-50">
                            <div class="bg-white p-4 rounded-circle mb-3 shadow-sm">
                                <i data-lucide="search" class="text-secondary opacity-50" width="40" height="40"></i>
                            </div>
                            <p class="fw-medium text-secondary">Vui lòng nhập văn bản và chọn chế độ kiểm tra bên trái.</p>
                        </div>

                        <!-- Filled State -->
                        <div id="result-filled" class="d-none flex-column h-100 position-relative z-1 fade-in">
                            <!-- Chart Card -->
                            <div class="card border-0 shadow-sm p-4 mb-4 rounded-4 bg-white">
                                <div class="d-flex align-items-center justify-content-center gap-4">
                                    <div class="position-relative" style="width: 120px; height: 120px; border-radius: 50%; background: conic-gradient(#22c55e 98%, #f1f5f9 0);" id="chart-ring">
                                        <div class="position-absolute bg-white rounded-circle d-flex flex-column align-items-center justify-content-center shadow-sm" style="top: 10px; left: 10px; right: 10px; bottom: 10px;">
                                            <span class="h3 fw-bold mb-0 text-dark" id="chart-score">98%</span>
                                            <span class="text-secondary fw-bold" style="font-size: 10px;" id="chart-label">HUMAN</span>
                                        </div>
                                    </div>
                                    <div class="small">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="bg-success rounded-circle" style="width: 10px; height: 10px;"></span>
                                            <span class="text-secondary fw-medium">Con người</span>
                                            <span class="fw-bold ms-auto text-dark" id="human-percent">98%</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="bg-danger rounded-circle" style="width: 10px; height: 10px;"></span>
                                            <span class="text-secondary fw-medium">AI / Trùng lặp</span>
                                            <span class="fw-bold ms-auto text-dark" id="ai-percent">2%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 pt-3 border-top text-center">
                                    <p class="text-success fw-bold mb-0 d-flex align-items-center justify-content-center gap-2" id="result-message">
                                        <i data-lucide="check-circle-2" width="16"></i> Văn bản rất tự nhiên!
                                    </p>
                                </div>
                            </div>

                            <!-- CTA Box -->
                            <div class="mt-auto">
                                <div class="bg-gradient-brand text-white p-4 rounded-4 shadow-lg position-relative overflow-hidden">
                                    <div class="position-relative z-1">
                                        <h6 class="fw-bold mb-2 d-flex align-items-center gap-2"><i data-lucide="lock" width="16" class="text-info"></i> Fix & Share Results</h6>
                                        <p class="small text-white-50 mb-3">Tải báo cáo chi tiết (PDF) hoặc chia sẻ liên kết kết quả cho giảng viên/bạn bè.</p>
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <button class="btn btn-outline-light btn-sm w-100 disabled border-opacity-25 text-white-50"><i data-lucide="download" width="14"></i> PDF</button>
                                            </div>
                                            <div class="col-6">
                                                <button class="btn btn-outline-light btn-sm w-100 disabled border-opacity-25 text-white-50"><i data-lucide="share-2" width="14"></i> Share</button>
                                            </div>
                                        </div>
                                        <button class="btn btn-primary w-100 fw-bold shadow-sm border-0">Đăng nhập để mở khóa <i data-lucide="arrow-right" width="16"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ABOUT SECTION -->
    <section id="about" class="py-5 bg-white">
        <div class="container py-5">
            <div class="row align-items-center gy-5">
                <div class="col-lg-6 order-2 order-lg-1">
                    <div class="position-relative p-5 rounded-3xl bg-gradient-brand text-white overflow-hidden shadow-2xl hover-scale transition-transform">
                        <!-- Decorative BGs -->
                        <div class="blob bg-info opacity-25" style="top: -50px; right: -50px; width: 300px; height: 300px; filter: blur(60px);"></div>
                        <div class="blob bg-primary opacity-25" style="bottom: -50px; left: -50px; width: 300px; height: 300px; filter: blur(60px);"></div>
                        
                        <div class="position-relative z-1">
                            <div class="d-inline-flex align-items-center gap-2 px-3 py-1 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-pill mb-4 shadow-sm backdrop-blur-sm">
                                <span class="badge bg-primary rounded-circle p-1 me-1"> </span> <span class="fw-bold small tracking-wider">VIỆN VMIED</span>
                            </div>
                            <h2 class="fw-bold mb-4 display-6">Uy Tín & <br><span class="text-info">Chất Lượng Hàng Đầu</span></h2>
                            <p class="text-blue-100 mb-4 lead opacity-90 fw-light">Viện Nghiên Cứu Phát Triển Giáo Dục Việt Mỹ (VMIED) hoạt động với sứ mệnh nâng tầm trí thức Việt. Cam kết minh bạch và liêm chính.</p>
                            
                            <ul class="list-unstyled d-grid gap-3">
                                <li class="d-flex align-items-start gap-3 bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-10 hover-bg-opacity-20 transition">
                                    <div class="bg-primary bg-opacity-25 p-2 rounded text-info"><i data-lucide="award" width="20"></i></div>
                                    <div>
                                        <strong class="d-block text-white">Pháp lý vững chắc</strong>
                                        <small class="text-white-50">Cấp phép số A-1953 bởi Bộ KH&CN.</small>
                                    </div>
                                </li>
                                <li class="d-flex align-items-start gap-3 bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-10 hover-bg-opacity-20 transition">
                                    <div class="bg-success bg-opacity-25 p-2 rounded text-success"><i data-lucide="lock" width="20"></i></div>
                                    <div>
                                        <strong class="d-block text-white">Bảo mật tuyệt đối</strong>
                                        <small class="text-white-50">Không lưu trữ dữ liệu người dùng.</small>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 order-1 order-lg-2 ps-lg-5">
                    <span class="text-primary fw-bold text-uppercase small" style="letter-spacing: 1px;">Tại sao chọn chúng tôi?</span>
                    <h2 class="fw-bold text-dark mt-2 mb-4 display-6">Sự khác biệt của AI Vmied</h2>
                    <p class="text-secondary fs-5 mb-5 lh-base">Trong kỷ nguyên số, sự chính trực học thuật là vô giá. Chúng tôi cung cấp công cụ không chỉ để "bắt lỗi" mà để "nâng tầm" chất lượng bài viết.</p>
                    
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <div class="d-flex gap-3">
                                <div class="p-2 rounded-3 text-primary h-auto d-flex align-items-start justify-content-center" style="width: 48px; height: 48px;"><i data-lucide="zap"></i></div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-1">Tốc độ siêu tốc</h5>
                                    <p class="text-secondary small mb-0">Xử lý 10.000 từ chỉ trong vài giây.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex gap-3">
                                <div class="p-2 rounded-3 text-primary h-auto d-flex align-items-start justify-content-center" style="width: 48px; height: 48px;"><i data-lucide="database"></i></div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-1">Dữ liệu Việt hóa</h5>
                                    <p class="text-secondary small mb-0">Tối ưu đặc biệt cho tiếng Việt.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex gap-3">
                                <div class="p-2 rounded-3 text-primary h-auto d-flex align-items-start justify-content-center" style="width: 48px; height: 48px;"><i data-lucide="lock"></i></div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-1">Bảo mật 100%</h5>
                                    <p class="text-secondary small mb-0">Văn bản của bạn là của bạn.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex gap-3">
                                <div class="p-2 rounded-3 text-primary h-auto d-flex align-items-start justify-content-center" style="width: 48px; height: 48px;"><i data-lucide="coins"></i></div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-1">Chi phí hợp lý</h5>
                                    <p class="text-secondary small mb-0">Mô hình nạp Credit linh hoạt.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURES SECTION -->
    <section id="features" class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5">
                <span class="text-primary fw-bold text-uppercase small tracking-wide">Công nghệ cốt lõi</span>
                <h2 class="fw-bold text-dark mt-2 display-6">6 Công cụ Mạnh mẽ</h2>
                <p class="text-secondary mx-auto" style="max-width: 600px;">Kết hợp giữa thuật toán NLP tiên tiến và cơ sở dữ liệu học thuật khổng lồ.</p>
            </div>

            <div class="row g-4">
                <!-- Card 1 -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 bg-white border-0 shadow-sm p-4 tool-card rounded-4">
                        <div class="feature-icon-box icon-red">
                            <i data-lucide="file-warning" width="28" height="28"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">Kiểm tra Đạo văn</h4>
                        <p class="text-secondary small mb-0 lh-sm">Quét trùng lặp từ hàng tỷ nguồn internet, tạp chí khoa học và luận văn.</p>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 bg-white border-0 shadow-sm p-4 tool-card rounded-4">
                        <div class="feature-icon-box icon-purple">
                            <i data-lucide="cpu" width="28" height="28"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">Kiểm tra AI Viết</h4>
                        <p class="text-secondary small mb-0 lh-sm">Phát hiện văn bản tạo bởi ChatGPT, Claude, Gemini... chính xác cao.</p>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 bg-white border-0 shadow-sm p-4 tool-card rounded-4">
                        <div class="feature-icon-box icon-blue">
                            <i data-lucide="book-check" width="28" height="28"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">Kiểm tra Ngữ pháp</h4>
                        <p class="text-secondary small mb-0 lh-sm">Phân tích cú pháp chuyên sâu cho Tiếng Việt và Tiếng Anh.</p>
                    </div>
                </div>
                <!-- Card 4 -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 bg-white border-0 shadow-sm p-4 tool-card rounded-4">
                        <div class="feature-icon-box icon-green">
                            <i data-lucide="type" width="28" height="28"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">Kiểm tra Chính tả</h4>
                        <p class="text-secondary small mb-0 lh-sm">Tự động phát hiện và sửa lỗi chính tả, lỗi đánh máy.</p>
                    </div>
                </div>
                <!-- Card 5 -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 bg-white border-0 shadow-sm p-4 tool-card rounded-4">
                        <div class="feature-icon-box icon-orange">
                            <i data-lucide="glasses" width="28" height="28"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">Đánh giá Đọc hiểu</h4>
                        <p class="text-secondary small mb-0 lh-sm">Chấm điểm độ dễ đọc (Readability score), tối ưu cho người đọc.</p>
                    </div>
                </div>
                <!-- Card 6 -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 bg-white border-0 shadow-sm p-4 tool-card rounded-4">
                        <div class="feature-icon-box icon-indigo">
                            <i data-lucide="pen-tool" width="28" height="28"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">Humanizer (Viết lại)</h4>
                        <p class="text-secondary small mb-0 lh-sm">Biến văn bản khô khan hoặc văn bản AI thành văn phong tự nhiên.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS (Simplified Scroll) -->
    <section class="py-5 bg-white border-top border-bottom overflow-hidden">
        <div class="container text-center mb-5">
            <h2 class="fw-bold text-dark">Cộng đồng nói gì về chúng tôi?</h2>
            <p class="text-secondary">Hàng ngàn sinh viên và giảng viên đã tin tưởng lựa chọn VMIED.</p>
        </div>
        <div class="scroll-wrapper">
            <div class="scroll-container gap-4 px-4">
                <!-- Testimonial Items -->
                <div class="card border p-4 rounded-4 bg-light flex-shrink-0" style="width: 350px;">
                    <div class="text-warning mb-3 d-flex"><i data-lucide="star" class="fill-current" style="width:16px;"></i><i data-lucide="star" class="fill-current" style="width:16px;"></i><i data-lucide="star" class="fill-current" style="width:16px;"></i><i data-lucide="star" class="fill-current" style="width:16px;"></i><i data-lucide="star" class="fill-current" style="width:16px;"></i></div>
                    <p class="text-secondary fst-italic small mb-4" style="height: 60px;">"Là giảng viên hướng dẫn, tôi luôn khuyến khích sinh viên dùng AI Vmied để tự kiểm tra trước khi nộp bài."</p>
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center fw-bold text-secondary" style="width: 40px; height: 40px;">TH</div>
                        <div><h6 class="fw-bold mb-0">TS. Trần Văn Hùng</h6><small class="text-secondary">Giảng viên ĐH</small></div>
                    </div>
                </div>

                <div class="card border p-4 rounded-4 bg-light flex-shrink-0" style="width: 350px;">
                    <div class="text-warning mb-3 d-flex"><i data-lucide="star" class="fill-current" style="width:16px;"></i><i data-lucide="star" class="fill-current" style="width:16px;"></i><i data-lucide="star" class="fill-current" style="width:16px;"></i><i data-lucide="star" class="fill-current" style="width:16px;"></i><i data-lucide="star" class="fill-current" style="width:16px;"></i></div>
                    <p class="text-secondary fst-italic small mb-4" style="height: 60px;">"Tính năng Humanizer thực sự cứu cánh! Giúp câu văn tự nhiên hơn hẳn. Giá lại rất rẻ cho sinh viên."</p>
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center fw-bold text-primary" style="width: 40px; height: 40px;">MA</div>
                        <div><h6 class="fw-bold mb-0">Minh Anh</h6><small class="text-secondary">Sinh viên năm cuối</small></div>
                    </div>
                </div>

                <div class="card border p-4 rounded-4 bg-light flex-shrink-0" style="width: 350px;">
                    <div class="text-warning mb-3 d-flex"><i data-lucide="star" class="fill-current" style="width:16px;"></i><i data-lucide="star" class="fill-current" style="width:16px;"></i><i data-lucide="star" class="fill-current" style="width:16px;"></i><i data-lucide="star" class="fill-current" style="width:16px;"></i><i data-lucide="star" style="width:16px;"></i></div>
                    <p class="text-secondary fst-italic small mb-4" style="height: 60px;">"Check đạo văn rất sâu, tìm được cả những nguồn từ tài liệu tiếng Việt cũ. Rất đáng tiền."</p>
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center fw-bold text-success" style="width: 40px; height: 40px;">TV</div>
                        <div><h6 class="fw-bold mb-0">Trần Văn</h6><small class="text-secondary">Nghiên cứu sinh</small></div>
                    </div>
                </div>
                
                 <!-- Duplicates for scroll -->
                 <div class="card border p-4 rounded-4 bg-light flex-shrink-0" style="width: 350px;">
                    <div class="text-warning mb-3 d-flex"><i data-lucide="star" class="fill-current" style="width:16px;"></i><i data-lucide="star" class="fill-current" style="width:16px;"></i><i data-lucide="star" class="fill-current" style="width:16px;"></i><i data-lucide="star" class="fill-current" style="width:16px;"></i><i data-lucide="star" class="fill-current" style="width:16px;"></i></div>
                    <p class="text-secondary fst-italic small mb-4" style="height: 60px;">"Là giảng viên hướng dẫn, tôi luôn khuyến khích sinh viên dùng AI Vmied để tự kiểm tra trước khi nộp bài."</p>
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center fw-bold text-secondary" style="width: 40px; height: 40px;">TH</div>
                        <div><h6 class="fw-bold mb-0">TS. Trần Văn Hùng</h6><small class="text-secondary">Giảng viên ĐH</small></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PRICING SECTION -->
    <section id="pricing" class="py-5 bg-light">
        <div class="container py-5">
            <div class="row gx-5 gy-5 align-items-start">
                <div class="col-lg-6">
                    <h2 class="fw-bold text-dark display-6 mb-3">Chi phí linh hoạt <br> <span class="text-primary">Pay As You Go</span></h2>
                    <p class="lead text-secondary mb-4">Hệ thống sử dụng ví <strong>VMIED</strong> với tỷ giá 1:1 so với VNĐ. Bạn nạp bao nhiêu dùng bấy nhiêu, minh bạch tuyệt đối.</p>
                    
                    <div class="d-flex gap-3 mb-3">
                        <div class="bg-white p-2 rounded-circle shadow-sm text-primary d-flex align-items-center justify-content-center" style="width:40px; height:40px;"><i data-lucide="wallet" width="20"></i></div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Nạp bao nhiêu dùng bấy nhiêu</h5>
                            <p class="text-secondary small mb-0">Không gia hạn tự động. Không phí ẩn.</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3 mb-5">
                        <div class="bg-white p-2 rounded-circle shadow-sm text-primary d-flex align-items-center justify-content-center" style="width:40px; height:40px;"><i data-lucide="infinity" width="20"></i></div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Số dư không hết hạn</h5>
                            <p class="text-secondary small mb-0">Tiền trong ví được bảo lưu mãi mãi.</p>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
                        <h6 class="fw-bold text-secondary text-uppercase mb-3 border-bottom pb-2" style="font-size: 12px; letter-spacing: 1px;">Bảng giá niêm yết</h6>
                        <ul class="list-unstyled d-flex flex-column gap-3">
                            <li class="d-flex justify-content-between align-items-center small">
                                <span class="d-flex align-items-center gap-3 text-dark fw-bold"><i data-lucide="scan-search" class="text-primary" style="width:16px;"></i> Check Đạo văn</span>
                                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-bold">500 VMIED <span class="text-secondary fw-normal">/100 từ</span></span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center small">
                                <span class="d-flex align-items-center gap-3 text-dark fw-bold"><i data-lucide="bot" class="text-secondary" style="width:16px;"></i> Check AI Viết</span>
                                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-bold">500 VMIED <span class="text-secondary fw-normal">/100 từ</span></span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center small">
                                <span class="d-flex align-items-center gap-3 text-dark fw-bold"><i data-lucide="wand-2" class="text-info" style="width:16px;"></i> Humanize</span>
                                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-bold">500 VMIED <span class="text-secondary fw-normal">/100 từ</span></span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card border-0 shadow-lg p-4 p-md-5 rounded-4 position-relative overflow-hidden bg-white">
                        <div class="blob bg-primary opacity-25" style="top:-30px; right:-30px; width:200px; height:200px;"></div>
                        <div class="blob bg-info opacity-25" style="bottom:-30px; left:-30px; width:150px; height:150px;"></div>
                        
                        <div class="text-center position-relative z-1">
                            <div class="badge bg-dark text-white px-3 py-2 rounded-pill mb-4 text-uppercase shadow fw-bold tracking-wide">Tỷ giá quy đổi 1:1</div>
                            <h3 class="fw-bold text-dark">Nạp Ví VMIED</h3>
                            
                            <div class="my-4 py-4 bg-light rounded-4 border border-dashed border-secondary border-opacity-25">
                                <div class="d-flex align-items-center justify-content-center gap-3 text-dark">
                                    <span class="h2 fw-bolder mb-0">1.000₫</span>
                                    <i data-lucide="arrow-right-left" class="text-secondary opacity-50"></i>
                                    <span class="h2 fw-bolder mb-0 text-primary">1.000</span>
                                </div>
                                <div class="d-flex justify-content-center gap-5 mt-1 small fw-bold text-secondary text-uppercase tracking-wider">
                                    <span>VNĐ</span>
                                    <span>VMIED</span>
                                </div>
                            </div>

                            <p class="text-start fw-bold text-dark small mb-2">Chọn mệnh giá nạp nhanh:</p>
                            <div class="row g-2 mb-4">
                                <div class="col-4"><button onclick="selectAmount(20000)" class="btn btn-outline-secondary border w-100 btn-sm deposit-btn py-3 rounded-4 fw-semibold">20k</button></div>
                                <div class="col-4"><button onclick="selectAmount(50000)" class="btn btn-outline-secondary border w-100 btn-sm deposit-btn py-3 rounded-4 fw-semibold">50k</button></div>
                                <div class="col-4"><button onclick="selectAmount(100000)" class="btn btn-outline-secondary border w-100 btn-sm deposit-btn py-3 rounded-4 fw-semibold">100k</button></div>
                                <div class="col-4"><button onclick="selectAmount(200000)" class="btn btn-outline-secondary border w-100 btn-sm deposit-btn py-3 rounded-4 fw-semibold">200k</button></div>
                                <div class="col-4"><button onclick="selectAmount(500000)" class="btn btn-outline-secondary border w-100 btn-sm deposit-btn py-3 rounded-4 fw-semibold">500k</button></div>
                                <div class="col-4"><button onclick="selectAmount(1000000)" class="btn btn-outline-secondary border w-100 btn-sm deposit-btn py-3 rounded-4 fw-semibold">1M</button></div>
                            </div>

                            <div class="mb-4">
                                <label class="d-block text-start fw-bold text-dark small mb-2">Hoặc nhập số khác:</label>
                                <div class="input-group">
                                    <input type="number" id="deposit-amount" class="form-control form-control-lg fw-bold border-end-0 rounded-start-4 py-3" placeholder="0">
                                    <span class="input-group-text fw-bold text-secondary bg-white border-start-0 rounded-end-4">VNĐ</span>
                                </div>
                            </div>

                            <button onclick="processDeposit()" class="btn btn-dark w-100 py-3 rounded-3 fw-bold shadow-lg hover-scale">
                                Thanh toán ngay <i data-lucide="arrow-right" style="width:16px; display:inline;"></i>
                            </button>
                            
                            <div class="mt-4 d-flex justify-content-center gap-3 opacity-50 grayscale">
                                <div class="bg-primary rounded px-2 py-1 text-white fw-bold" style="font-size: 10px;">VISA</div>
                                <div class="bg-danger rounded px-2 py-1 text-white fw-bold" style="font-size: 10px;">MOMO</div>
                                <div class="bg-success rounded px-2 py-1 text-white fw-bold" style="font-size: 10px;">QR</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ SECTION -->
    <section class="py-5 bg-white border-top">
        <div class="container py-5">
            <h2 class="fw-bold text-center mb-5">Câu hỏi thường gặp</h2>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion accordion-flush d-grid gap-3" id="faqAccordion">
                        <div class="accordion-item border rounded-3 overflow-hidden bg-light">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Văn bản của tôi có bị lưu lại không?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-secondary small lh-lg">
                                    Tuyệt đối không. AI Vmied tôn trọng quyền sở hữu trí tuệ. Văn bản của bạn chỉ được xử lý tạm thời để đưa ra kết quả và sẽ bị xóa ngay sau đó.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border rounded-3 overflow-hidden bg-light">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    VMIED có hết hạn sử dụng không?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-secondary small lh-lg">
                                    Không. Số lượng VMIED bạn đã nạp sẽ được bảo lưu vĩnh viễn trong tài khoản cho đến khi bạn sử dụng hết.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border rounded-3 overflow-hidden bg-light">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Làm sao để nạp tiền?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-secondary small lh-lg">
                                    Bạn có thể nạp tiền thông qua chuyển khoản ngân hàng (VietQR), ví Momo hoặc thẻ Visa/Mastercard ngay trong trang quản lý tài khoản.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTACT SECTION -->
    <section id="contact-section" class="py-5 position-relative overflow-hidden">
        <div class="blob bg-primary opacity-10" style="left: -100px; top: 20%; width: 500px; height: 500px; filter: blur(100px);"></div>
        <div class="blob bg-info opacity-10" style="right: -100px; bottom: 10%; width: 400px; height: 400px; filter: blur(80px);"></div>
        
        <div class="container py-5 position-relative z-1">
            <div class="row align-items-center gy-5">
                <div class="col-lg-6">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 mb-3 px-3 py-2 text-uppercase fw-bold">Hỗ trợ 24/7</span>
                    <h2 class="display-4 fw-bolder text-dark mb-4 lh-sm">Hãy để chúng tôi <br> <span class="text-primary">lắng nghe bạn.</span></h2>
                    <p class="lead text-secondary mb-5 fw-normal">Dù bạn là sinh viên, giảng viên hay đối tác doanh nghiệp, VMIED luôn sẵn sàng giải đáp mọi thắc mắc.</p>
                    
                    <div class="d-flex align-items-center gap-4 pt-4 border-top">
                        <div>
                            <p class="h3 fw-bold text-dark mb-0">~15p</p>
                            <small class="text-secondary">Thời gian phản hồi</small>
                        </div>
                        <div class="vr opacity-25"></div>
                        <div class="d-flex gap-3">
                            <a href="#" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center border-opacity-25 hover-primary" style="width:48px; height:48px;"><i data-lucide="facebook" width="20"></i></a>
                            <a href="#" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center border-opacity-25 hover-primary" style="width:48px; height:48px;"><i data-lucide="mail" width="20"></i></a>
                            <a href="#" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center border-opacity-25 hover-primary" style="width:48px; height:48px;"><i data-lucide="linkedin" width="20"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="glass-card p-4 p-md-5 rounded-4 shadow-xl">
                        <form onsubmit="event.preventDefault(); alert('Cảm ơn bạn đã liên hệ!');">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-dark">Họ tên</label>
                                    <input type="text" class="form-control form-control-lg-custom" placeholder="Tên của bạn">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-dark">Email</label>
                                    <input type="email" class="form-control form-control-lg-custom" placeholder="email@domain.com">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-dark">Vấn đề cần hỗ trợ</label>
                                <select class="form-select form-control-lg-custom">
                                    <option>Tư vấn về gói VMIED</option>
                                    <option>Báo lỗi kỹ thuật</option>
                                    <option>Hợp tác doanh nghiệp</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-dark">Nội dung</label>
                                <textarea rows="4" class="form-control form-control-lg-custom" placeholder="Chia sẻ với chúng tôi..."></textarea>
                            </div>
                            <button class="btn btn-dark w-100 py-3 rounded-3 fw-bold shadow-lg hover-scale">
                                Gửi tin nhắn <i data-lucide="send" width="16" class="ms-2"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-dark text-light pt-5 pb-4 border-top border-secondary border-opacity-25">
        <div class="container">
            <div class="row border-bottom border-secondary border-opacity-25 pb-5 mb-4 gy-4">
                <div class="col-lg-5">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <div class="navbar-brand-box" style="width:32px; height:32px; font-size: 1rem;">V</div>
                        <span class="h5 fw-bold mb-0">AI Vmied</span>
                    </div>
                    <p class="text-secondary small mb-4 lh-lg">VIỆN NGHIÊN CỨU PHÁT TRIỂN GIÁO DỤC VIỆT MỸ<br>Đơn vị tiên phong ứng dụng công nghệ trong giáo dục.</p>
                    <div class="bg-secondary bg-opacity-10 p-3 rounded-3 small text-secondary">
                        <p class="mb-2 d-flex gap-2"><i data-lucide="file-check" width="16"></i> QĐ thành lập số: 18-51/QĐ/VAYSE-VPNB</p>
                        <p class="mb-0 d-flex gap-2"><i data-lucide="award" width="16"></i> Giấy CNĐKHĐ: A-1953 - Bộ KH&CN</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <h6 class="fw-bold mb-4 text-white">Liên hệ</h6>
                    <ul class="list-unstyled text-secondary small d-grid gap-3">
                        <li class="d-flex gap-3"><span class="bg-secondary bg-opacity-25 p-1 rounded-circle"><i data-lucide="map-pin" width="14"></i></span> 94/44 Lưu Chí Hiếu, P. Tây Thạnh, TP. HCM</li>
                        <li class="d-flex gap-3"><span class="bg-secondary bg-opacity-25 p-1 rounded-circle"><i data-lucide="phone" width="14"></i></span> 0903328995</li>
                        <li class="d-flex gap-3"><span class="bg-secondary bg-opacity-25 p-1 rounded-circle"><i data-lucide="mail" width="14"></i></span> info@vmied.edu.vn</li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h6 class="fw-bold mb-4 text-white">Hỗ trợ</h6>
                    <ul class="list-unstyled text-secondary small d-grid gap-2">
                        <li><a href="#" class="text-decoration-none text-secondary hover-text-white transition"><i data-lucide="chevron-right" width="12"></i> Hướng dẫn sử dụng</a></li>
                        <li><a href="#" class="text-decoration-none text-secondary hover-text-white transition"><i data-lucide="chevron-right" width="12"></i> Chính sách bảo mật</a></li>
                        <li><a href="#" class="text-decoration-none text-secondary hover-text-white transition"><i data-lucide="chevron-right" width="12"></i> Điều khoản dịch vụ</a></li>
                    </ul>
                </div>
            </div>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center text-secondary small opacity-75">
                <p class="mb-0">&copy; 2025 VMIED Institute. All rights reserved.</p>
                <div class="d-flex gap-3 mt-3 mt-md-0">
                    <a href="#" class="text-secondary hover-text-white transition"><i data-lucide="facebook" width="20"></i></a>
                    <a href="#" class="text-secondary hover-text-white transition"><i data-lucide="linkedin" width="20"></i></a>
                    <a href="#" class="text-secondary hover-text-white transition"><i data-lucide="youtube" width="20"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <?php $this->endSection() ?>