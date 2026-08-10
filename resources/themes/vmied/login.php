<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - AI Vmied</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="/css/app.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid p-0 h-100">
        <div class="row g-0 h-100" x-data="{ view: 'login' }" x-cloak>
            
            <!-- LEFT SIDE: BRANDING (Hidden on mobile) -->
            <div class="col-lg-6 d-none d-lg-flex bg-dark position-relative overflow-hidden align-items-center justify-content-center text-white">
                <!-- Background Decor -->
                <div class="blob bg-info" style="top: 0; right: 0; width: 500px; height: 500px; transform: translate(50%, -50%);"></div>
                <div class="blob bg-primary" style="bottom: 0; left: 0; width: 500px; height: 500px; transform: translate(-50%, 50%);"></div>
                <!-- Noise Texture Overlay -->
                <!--<div class="position-absolute top-0 start-0 w-100 h-100" style="background-image: url('https://grainy-gradients.vercel.app/noise.svg'); opacity: 0.2; mix-blend-mode: overlay; pointer-events: none;"></div>-->

                <div class="position-relative z-1 p-5" style="max-width: 600px;">
                    <!-- Logo Area -->
                    <div class="d-flex align-items-center gap-3 mb-5">
                        <div class="d-flex align-items-center justify-content-center rounded-3 text-white fw-bold fs-4 shadow" 
                             style="width: 48px; height: 48px; background: linear-gradient(135deg, var(--brand-500), var(--accent-600));">
                            V
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-bold fs-4 lh-1">AI Vmied</span>
                            <span class="text-white-50 fw-medium text-uppercase mt-1" style="font-size: 10px; letter-spacing: 1px;">Viện Nghiên Cứu PTGD Việt Mỹ</span>
                        </div>
                    </div>

                    <div class="fade-in">
                        <h1 class="display-4 fw-bold lh-sm mb-4">
                            Nâng tầm <br>
                            <span class="text-gradient">Trí Thức Việt.</span>
                        </h1>
                        <p class="text-white-50 fs-5 mb-5 lh-base">
                            Truy cập bộ công cụ AI toàn diện giúp bạn kiểm tra đạo văn, chuẩn hóa ngữ pháp và tối ưu nội dung học thuật chỉ trong vài giây.
                        </p>

                        <!-- Testimonial Mini Card -->
                        <div class="glass-card p-4 rounded-4 mt-5">
                            <div class="d-flex gap-1 text-warning mb-3">
                                <i data-lucide="star" class="fill-current" style="width:16px;"></i>
                                <i data-lucide="star" class="fill-current" style="width:16px;"></i>
                                <i data-lucide="star" class="fill-current" style="width:16px;"></i>
                                <i data-lucide="star" class="fill-current" style="width:16px;"></i>
                                <i data-lucide="star" class="fill-current" style="width:16px;"></i>
                            </div>
                            <p class="fst-italic text-light mb-4 small opacity-75">"Công cụ không thể thiếu cho luận văn của mình. Check AI cực kỳ chính xác!"</p>
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold small text-white" 
                                     style="width: 32px; height: 32px; background-color: var(--brand-500);">MA</div>
                                <div>
                                    <p class="fw-bold mb-0 small">Minh Anh</p>
                                    <p class="text-white-50 mb-0" style="font-size: 0.75rem;">Sinh viên Đại học Quốc Gia</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Floating Elements -->
                <div class="position-absolute opacity-25 animate-float" style="top: 25%; left: 10%;">
                    <i data-lucide="shield-check" style="width: 96px; height: 96px;"></i>
                </div>
                <div class="position-absolute opacity-25 animate-float" style="bottom: 25%; right: 10%; animation-delay: 2s;">
                    <i data-lucide="cpu" style="width: 96px; height: 96px;"></i>
                </div>
            </div>

            <!-- RIGHT SIDE: FORMS -->
            <div class="col-lg-6 h-100 bg-white d-flex align-items-center justify-content-center position-relative">
                <!-- Mobile Logo -->
                <div class="d-lg-none position-absolute top-0 start-0 p-4 d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded bg-primary text-white fw-bold" style="width: 32px; height: 32px;">V</div>
                    <span class="fw-bold text-dark">AI Vmied</span>
                </div>

                <div class="w-100 p-4" style="max-width: 480px;">
                    
                    <!-- 1. LOGIN FORM -->
                    <div x-show="view === 'login'" 
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="opacity-0 translate-y-3" 
                         x-transition:enter-end="opacity-100 translate-y-0">
                        
                        <div class="text-center mb-5">
                            <h2 class="fw-bold text-dark mb-2">Chào mừng trở lại!</h2>
                            <p class="text-secondary small">Vui lòng đăng nhập để tiếp tục.</p>
                        </div>

                        <!-- Social Login -->
                        <div class="d-grid gap-3 mb-4">
                            <button class="btn btn-social d-flex align-items-center justify-content-center gap-3 shadow-sm">
                                <img src="https://www.svgrepo.com/show/475656/google-color.svg" width="20" alt="Google">
                                Tiếp tục với Google
                            </button>
                            <button class="btn btn-dark d-flex align-items-center justify-content-center gap-3 py-2 rounded-3 fw-medium shadow-sm">
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.74 1.18 0 2.21-.93 3.69-.74.63.03 2.12.28 3.12 1.74-.16.12-1.92 1.11-1.92 3.29 0 2.51 2.21 3.48 2.3 3.52-.02.09-.34 1.16-1.12 2.31zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
                                </svg>
                                Tiếp tục với Apple
                            </button>
                        </div>

                        <div class="d-flex align-items-center mb-4">
                            <hr class="flex-grow-1 text-secondary opacity-25">
                            <span class="mx-3 text-secondary small fw-bold text-uppercase" style="font-size: 0.7rem;">Hoặc đăng nhập bằng Email</span>
                            <hr class="flex-grow-1 text-secondary opacity-25">
                        </div>

                        <!-- Alpine Data for Login -->
                        <div x-data="Form()">
                            <form 
                                hx-post="/login" 
                                hx-swap="none"
                                @htmx:before-request="startRequest()"
                                @htmx:after-request="handleResponse($event)"
                                class="d-grid gap-3"
                            >
                                <!-- Error Alert -->
                                <div x-show="errorMessage" style="display: none;">
                                    <div class="alert alert-danger d-flex align-items-center small py-2 rounded-3" role="alert">
                                        <i data-lucide="alert-circle" style="width: 16px; margin-right: 8px;"></i>
                                        <div><span class="fw-bold">Lỗi:</span> <span x-text="errorMessage"></span></div>
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label small fw-bold text-secondary ps-1">Email</label>
                                    <div class="input-group-custom">
                                        <input type="text" name="email" class="form-control form-control-custom" placeholder="Email" required>
                                    </div>
                                </div>

                                <div>
                                    <div class="d-flex justify-content-between">
                                        <label class="form-label small fw-bold text-secondary ps-1">Mật khẩu</label>
                                        <button type="button" @click="view = 'forgot'" class="btn btn-link p-0 text-decoration-none small text-primary fw-bold">Quên mật khẩu?</button>
                                    </div>
                                    <div class="input-group-custom">
                                        <input type="password" name="password" class="form-control form-control-custom" placeholder="••••••••" required>
                                    </div>
                                </div>

                                <button type="submit" 
                                        :disabled="isLoading"
                                        class="btn btn-primary w-100 py-3 rounded-3 fw-bold text-white shadow mt-2"
                                        style="transition: all 0.3s;">
                                    <div x-show="isLoading" class="spinner-border spinner-border-sm text-light" role="status"></div>
                                    <span x-text="isLoading ? 'Đang xử lý...' : 'Đăng nhập'"></span>
                                    <i x-show="!isLoading" data-lucide="arrow-right" style="width: 16px;"></i>
                                </button>
                            </form>
                        </div>
                        <div class="text-center text-secondary mt-4 align-items-center">
                            Chưa có tài khoản? 
                            <a @click="view = 'register'" href="#" class="link-primary text-decoration-none p-0 fw-bold">Đăng ký ngay</a>
                        </div>
                    </div>

                    <!-- 2. REGISTER FORM -->
                    <div x-show="view === 'register'" 
                         x-transition:enter="node-transition" 
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         class="fade show"
                         style="display: none;">
                        
                        <div class="text-center mb-5">
                            <h2 class="fw-bold text-dark mb-2">Tạo tài khoản mới</h2>
                            <p class="text-secondary small">Bắt đầu hành trình học thuật chuyên nghiệp.</p>
                        </div>

                        <div x-data="Form()">
                            <!-- Error Alert -->
                            <div x-show="errorMessage" style="display: none;">
                                <div class="alert alert-danger d-flex align-items-center small py-2 rounded-3" role="alert">
                                    <i data-lucide="alert-circle" style="width: 16px; margin-right: 8px;"></i>
                                    <div><span class="fw-bold">Lỗi:</span> <span x-text="errorMessage"></span></div>
                                </div>
                            </div>

                            <form hx-post="/register" hx-swap="none"
                                @htmx:before-request="startRequest()"
                                @htmx:after-request="handleResponse($event)"
                                class="d-grid gap-3">

                                <div>
                                    <label class="form-label small fw-bold text-secondary ps-1">Họ và tên</label>
                                    <div class="input-group-custom">
                                        <i data-lucide="user" class="input-icon" style="width: 18px;"></i>
                                        <input type="text" name="name" class="form-control form-control-custom has-icon" placeholder="Nguyễn Văn A">
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label small fw-bold text-secondary ps-1">Email</label>
                                    <div class="input-group-custom">
                                        <i data-lucide="mail" class="input-icon" style="width: 18px;"></i>
                                        <input type="email" name="email" class="form-control form-control-custom has-icon" placeholder="Email" required>
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label small fw-bold text-secondary ps-1">Mật khẩu</label>
                                    <div class="input-group-custom">
                                        <i data-lucide="lock" class="input-icon" style="width: 18px;"></i>
                                        <input type="password" name="password" class="form-control form-control-custom has-icon" placeholder="Tối thiểu 6 ký tự" required>
                                    </div>
                                </div>

                                <!-- ===== REF HIDDEN INPUT ===== -->
                                <!-- Tự động lấy ?ref= từ URL, ví dụ: /login?ref=73539373 -->
                                <input type="hidden" name="ref" x-bind:value="new URLSearchParams(window.location.search).get('ref') ?? ''">
                                <!-- ============================= -->

                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label small text-secondary" for="terms">
                                        Tôi đồng ý với <a href="#" class="text-primary text-decoration-none">Điều khoản dịch vụ</a> và <a href="#" class="text-primary text-decoration-none">Chính sách bảo mật</a> của AI Vmied.
                                    </label>
                                </div>

                                <button type="submit" 
                                        :disabled="isLoading"
                                        class="btn btn-primary w-100 py-3 rounded-3 fw-bold text-white shadow mt-2"
                                        style="transition: all 0.3s;">
                                    <div x-show="isLoading" class="spinner-border spinner-border-sm text-light" role="status"></div>
                                    <span x-text="isLoading ? 'Đang xử lý...' : 'Đăng ký tài khoản'"></span>
                                    <i x-show="!isLoading" data-lucide="arrow-right" style="width: 16px;"></i>
                                </button>
                            </form>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a @click="view = 'register-vip'" href="#" class="btn btn-sm btn-outline-warning rounded-pill px-4 fw-bold text-dark">
                                <i data-lucide="building" style="width: 14px;"></i> Đăng ký cho Trường học / Đơn vị
                            </a>
                        </div>

                        <div class="text-center text-secondary mt-4 align-items-center">
                            Đã có tài khoản? 
                            <a @click="view = 'login'" href="#" class="link-primary text-decoration-none p-0 fw-bold">Đăng nhập</a>
                        </div>
                    </div>
                    
                    <!-- 2.5 REGISTER VIP FORM -->
                    <div x-show="view === 'register-vip'" 
                         x-transition:enter="node-transition" 
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         class="fade show"
                         style="display: none;">
                        
                        <div class="text-center mb-4">
                            <span class="badge bg-warning text-dark mb-2 px-3 py-1 rounded-pill">Dành cho Đối tác</span>
                            <h3 class="fw-bold text-dark mb-2">Đăng ký Đơn vị Liên kết</h3>
                            <p class="text-secondary small">Trường Đại học, Cao đẳng & Đơn vị giáo dục.</p>
                        </div>
                    
                        <div x-data="Form()">
                            <div x-show="errorMessage" style="display: none;">
                                <div class="alert alert-danger d-flex align-items-center small py-2 rounded-3" role="alert">
                                    <i data-lucide="alert-circle" style="width: 16px; margin-right: 8px;"></i>
                                    <span x-text="errorMessage"></span>
                                </div>
                            </div>
                    
                            <!-- Lưu ý: Form đăng ký VIP cần enctype="multipart/form-data" để upload logo -->
                            <form hx-post="/register-vip" hx-swap="none" enctype="multipart/form-data"
                                @htmx:before-request="startRequest()"
                                @htmx:after-request="handleResponse($event)"
                                class="d-grid gap-3">
                    
                                <div>
                                    <label class="form-label small fw-bold text-secondary ps-1">Tên Đơn vị / Trường học *</label>
                                    <div class="input-group-custom">
                                        <i data-lucide="building" class="input-icon" style="width: 18px;"></i>
                                        <input type="text" name="organization" class="form-control form-control-custom has-icon" placeholder="Vd: Đại học Tôn Đức Thắng" required>
                                    </div>
                                </div>
                    
                                <div>
                                    <label class="form-label small fw-bold text-secondary ps-1">Logo Đơn vị</label>
                                    <div class="input-group-custom">
                                        <input type="file" name="avatar" accept="image/*" class="form-control form-control-custom" style="padding-left: 1rem;">
                                    </div>
                                </div>
                    
                                <hr class="text-secondary opacity-25 my-1">
                    
                                <div>
                                    <label class="form-label small fw-bold text-secondary ps-1">Họ tên người đại diện *</label>
                                    <div class="input-group-custom">
                                        <i data-lucide="user" class="input-icon" style="width: 18px;"></i>
                                        <input type="text" name="name" class="form-control form-control-custom has-icon" required>
                                    </div>
                                </div>
                    
                                <div>
                                    <label class="form-label small fw-bold text-secondary ps-1">Email làm việc *</label>
                                    <div class="input-group-custom">
                                        <i data-lucide="mail" class="input-icon" style="width: 18px;"></i>
                                        <input type="email" name="email" class="form-control form-control-custom has-icon" required>
                                    </div>
                                </div>
                    
                                <div>
                                    <label class="form-label small fw-bold text-secondary ps-1">Mật khẩu *</label>
                                    <div class="input-group-custom">
                                        <i data-lucide="lock" class="input-icon" style="width: 18px;"></i>
                                        <input type="password" name="password" class="form-control form-control-custom has-icon" required>
                                    </div>
                                </div>
                    
                                <button type="submit" 
                                        :disabled="isLoading"
                                        class="btn btn-warning w-100 py-3 rounded-3 fw-bold text-dark shadow mt-2">
                                    <div x-show="isLoading" class="spinner-border spinner-border-sm text-dark" role="status"></div>
                                    <span x-text="isLoading ? 'Đang xử lý...' : 'Gửi yêu cầu đăng ký'"></span>
                                </button>
                            </form>
                        </div>
                    
                        <div class="text-center text-secondary mt-4 align-items-center">
                            <a @click="view = 'login'" href="#" class="link-secondary text-decoration-none p-0 fw-medium small"><i data-lucide="arrow-left" style="width: 14px;"></i> Quay lại đăng nhập</a>
                        </div>
                    </div>

                    <!-- 3. FORGOT PASSWORD VIEW -->
                    <div x-show="view === 'forgot'" 
                         x-transition:enter="node-transition" 
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         class="fade show"
                         style="display: none;">
                        
                        <button @click="view = 'login'" class="btn btn-link text-decoration-none text-secondary p-0 d-flex align-items-center gap-2 mb-4 small hover-dark">
                            <i data-lucide="arrow-left" style="width: 16px;"></i> Quay lại
                        </button>

                        <div class="text-center mb-5">
                            <div class="d-inline-flex align-items-center justify-content-center bg-info bg-opacity-10 rounded-circle mb-3 text-primary" style="width: 64px; height: 64px;">
                                <i data-lucide="key-round" style="width: 32px;"></i>
                            </div>
                            <h2 class="fw-bold text-dark mb-2">Quên mật khẩu?</h2>
                            <p class="text-secondary small px-4">Đừng lo! Nhập email của bạn và chúng tôi sẽ gửi hướng dẫn khôi phục.</p>
                        </div>

                        <form hx-post="/forgot-password" hx-swap="afterend" class="d-grid gap-4">
                            <div>
                                <label class="form-label small fw-bold text-secondary ps-1">Email đăng ký</label>
                                <div class="input-group-custom">
                                    <i data-lucide="mail" class="input-icon" style="width: 18px;"></i>
                                    <input type="email" name="email" class="form-control form-control-custom has-icon" placeholder="Email đăng ký" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-dark w-100 py-3 rounded-3 fw-bold shadow">
                                Gửi link khôi phục
                            </button>
                        </form>
                    </div>

                </div>

                <!-- Footer Copyright -->
                <div class="position-absolute bottom-0 w-100 text-center pb-3 text-secondary" style="font-size: 0.75rem;">
                    &copy; 2025 VMIED Institute. All rights reserved.
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="/js/app.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>