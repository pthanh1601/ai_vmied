<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - AI Vmied</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Be Vietnam Pro"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9', // Sky blue
                            600: '#0284c7',
                            700: '#0369a1',
                            900: '#0c4a6e',
                        },
                        accent: {
                            500: '#8b5cf6', // Violet
                            600: '#7c3aed',
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out forwards',
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .hidden-section {
            display: none;
        }
    </style>
</head>
<body class="font-sans antialiased text-slate-900 bg-white h-screen overflow-hidden flex">

    <!-- LEFT SIDE: BRANDING (Hidden on mobile) -->
    <div class="hidden lg:flex lg:w-1/2 bg-slate-900 relative overflow-hidden items-center justify-center p-12 text-white">
        <!-- Background Decor -->
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-brand-500/20 rounded-full blur-[100px] -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-accent-500/20 rounded-full blur-[100px] translate-y-1/2 -translate-x-1/2"></div>
        <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150"></div>

        <div class="relative z-10 max-w-lg">
            <!-- Logo Area -->
            <div class="flex items-center gap-3 mb-12">
                <div class="w-12 h-12 bg-gradient-to-br from-brand-500 to-accent-600 rounded-xl flex items-center justify-center text-white font-bold text-2xl shadow-lg shadow-brand-500/30">
                    V
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-2xl tracking-tight leading-none">AI Vmied</span>
                    <span class="text-[10px] text-slate-400 font-medium tracking-wider uppercase mt-1">Viện Nghiên Cứu PTGD Việt Mỹ</span>
                </div>
            </div>

            <div class="space-y-8 animate-fade-in">
                <h1 class="text-5xl font-extrabold leading-tight">
                    Nâng tầm <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-400 to-accent-400">Trí Thức Việt.</span>
                </h1>
                <p class="text-slate-300 text-lg leading-relaxed">
                    Truy cập bộ công cụ AI toàn diện giúp bạn kiểm tra đạo văn, chuẩn hóa ngữ pháp và tối ưu nội dung học thuật chỉ trong vài giây.
                </p>

                <!-- Testimonial Mini Card -->
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/10 mt-12">
                    <div class="flex gap-1 text-yellow-400 mb-3">
                        <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                        <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                        <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                        <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                        <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                    </div>
                    <p class="text-sm italic text-slate-200 mb-4">"Công cụ không thể thiếu cho luận văn của mình. Check AI cực kỳ chính xác!"</p>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-brand-500 flex items-center justify-center font-bold text-xs">MA</div>
                        <div>
                            <p class="font-bold text-sm">Minh Anh</p>
                            <p class="text-xs text-slate-400">Sinh viên Đại học Quốc Gia</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Floating Elements -->
        <div class="absolute top-1/4 left-10 opacity-20 animate-float">
            <i data-lucide="shield-check" class="w-24 h-24"></i>
        </div>
        <div class="absolute bottom-1/4 right-10 opacity-20 animate-float" style="animation-delay: 2s;">
            <i data-lucide="cpu" class="w-24 h-24"></i>
        </div>
    </div>

    <!-- RIGHT SIDE: FORMS -->
    <div class="w-full lg:w-1/2 h-full overflow-y-auto bg-slate-50 flex items-center justify-center p-6 md:p-12 relative">
        <!-- Mobile Logo (Visible only on mobile) -->
        <div class="lg:hidden absolute top-8 left-8 flex items-center gap-2">
            <div class="w-8 h-8 bg-brand-600 rounded-lg flex items-center justify-center text-white font-bold">V</div>
            <span class="font-bold text-slate-900">AI Vmied</span>
        </div>

        <div class="w-full max-w-md bg-white p-8 md:p-10 rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100">
            
            <!-- 1. LOGIN FORM -->
            <div id="login-view" class="animate-fade-in">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-slate-900">Chào mừng trở lại!</h2>
                    <p class="text-slate-500 mt-2 text-sm">Vui lòng đăng nhập để tiếp tục.</p>
                </div>

                <!-- Social Login -->
                <div class="space-y-3 mb-6">
                    <button class="w-full flex items-center justify-center gap-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-medium py-3 rounded-xl transition-all group">
                        <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5 group-hover:scale-110 transition-transform" alt="Google">
                        Tiếp tục với Google
                    </button>
                    <button class="w-full flex items-center justify-center gap-3 bg-black hover:bg-slate-800 text-white font-medium py-3 rounded-xl transition-all group">
                        <svg class="w-5 h-5 fill-current group-hover:scale-110 transition-transform" viewBox="0 0 24 24">
                            <path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.74 1.18 0 2.21-.93 3.69-.74.63.03 2.12.28 3.12 1.74-.16.12-1.92 1.11-1.92 3.29 0 2.51 2.21 3.48 2.3 3.52-.02.09-.34 1.16-1.12 2.31zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
                        </svg>
                        Tiếp tục với Apple
                    </button>
                </div>

                <div class="relative flex py-2 items-center mb-6">
                    <div class="flex-grow border-t border-slate-200"></div>
                    <span class="flex-shrink-0 mx-4 text-xs font-bold text-slate-400 uppercase">Hoặc đăng nhập bằng Email</span>
                    <div class="flex-grow border-t border-slate-200"></div>
                </div>

                <form id="loginForm" onsubmit="return handleLogin(event);" class="space-y-5">
                    <div class="space-y-1.5">
                        <label class="text-sm font-semibold text-slate-700 ml-1">Email</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i data-lucide="mail" class="w-5 h-5 text-slate-400 group-focus-within:text-brand-500 transition-colors"></i>
                            </div>
                            <input type="email" name="email" class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none transition-all" placeholder="name@example.com" required>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex justify-between items-center ml-1">
                            <label class="text-sm font-semibold text-slate-700">Mật khẩu</label>
                            <a href="#" onclick="switchView('forgot')" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Quên mật khẩu?</a>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i data-lucide="lock" class="w-5 h-5 text-slate-400 group-focus-within:text-brand-500 transition-colors"></i>
                            </div>
                            <input type="password" name="password" class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none transition-all" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3.5 rounded-xl hover:bg-brand-600 hover:shadow-lg hover:shadow-brand-500/30 transition-all duration-300 flex items-center justify-center gap-2 group">
                        Đăng nhập
                        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </form>

                <p class="text-center text-sm text-slate-600 mt-8">
                    Chưa có tài khoản? 
                    <button onclick="switchView('register')" class="font-bold text-brand-600 hover:text-brand-700 hover:underline">Đăng ký ngay</button>
                </p>
            </div>

            <!-- 2. REGISTER FORM -->
            <div id="register-view" class="hidden-section animate-fade-in">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-slate-900">Tạo tài khoản mới</h2>
                    <p class="text-slate-500 mt-2 text-sm">Bắt đầu hành trình học thuật chuyên nghiệp.</p>
                </div>

                <form id="registerForm" onsubmit="return handleRegister(event);" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-sm font-semibold text-slate-700 ml-1">Họ và tên</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i data-lucide="user" class="w-5 h-5 text-slate-400 group-focus-within:text-brand-500 transition-colors"></i>
                            </div>
                            <input type="text" name="name" class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none transition-all" placeholder="Nguyễn Văn A" required>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-semibold text-slate-700 ml-1">Email</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i data-lucide="mail" class="w-5 h-5 text-slate-400 group-focus-within:text-brand-500 transition-colors"></i>
                            </div>
                            <input type="email" name="email" class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none transition-all" placeholder="name@example.com" required>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-semibold text-slate-700 ml-1">Mật khẩu</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i data-lucide="lock" class="w-5 h-5 text-slate-400 group-focus-within:text-brand-500 transition-colors"></i>
                            </div>
                            <input type="password" name="password" class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none transition-all" placeholder="Tối thiểu 8 ký tự" required>
                        </div>
                    </div>

                    <div class="flex items-start gap-2 mt-2">
                        <input type="checkbox" id="terms" class="mt-1 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <label for="terms" class="text-xs text-slate-500">Tôi đồng ý với <a href="#" class="text-brand-600 hover:underline">Điều khoản dịch vụ</a> và <a href="#" class="text-brand-600 hover:underline">Chính sách bảo mật</a> của AI Vmied.</label>
                    </div>

                    <button type="submit" class="w-full bg-brand-600 text-white font-bold py-3.5 rounded-xl hover:bg-brand-700 hover:shadow-lg hover:shadow-brand-500/30 transition-all duration-300 mt-2">
                        Đăng ký tài khoản
                    </button>
                </form>

                <p class="text-center text-sm text-slate-600 mt-8">
                    Đã có tài khoản? 
                    <button onclick="switchView('login')" class="font-bold text-slate-900 hover:text-brand-600 hover:underline">Đăng nhập</button>
                </p>
            </div>

            <!-- 3. FORGOT PASSWORD VIEW -->
            <div id="forgot-view" class="hidden-section animate-fade-in">
                <button onclick="switchView('login')" class="flex items-center gap-2 text-sm text-slate-500 hover:text-slate-800 mb-6 transition">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Quay lại
                </button>

                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-brand-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="key-round" class="w-8 h-8 text-brand-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900">Quên mật khẩu?</h2>
                    <p class="text-slate-500 mt-2 text-sm">Đừng lo! Nhập email của bạn và chúng tôi sẽ gửi hướng dẫn khôi phục.</p>
                </div>

                <form onsubmit="event.preventDefault(); alert('Đã gửi link khôi phục!'); switchView('login');" class="space-y-6">
                    <div class="space-y-1.5">
                        <label class="text-sm font-semibold text-slate-700 ml-1">Email đăng ký</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i data-lucide="mail" class="w-5 h-5 text-slate-400 group-focus-within:text-brand-500 transition-colors"></i>
                            </div>
                            <input type="email" class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 outline-none transition-all" placeholder="name@example.com" required>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3.5 rounded-xl hover:bg-brand-600 hover:shadow-lg hover:shadow-brand-500/30 transition-all duration-300">
                        Gửi link khôi phục
                    </button>
                </form>
            </div>

        </div>

        <!-- Footer Copyright -->
        <div class="absolute bottom-6 text-center w-full text-xs text-slate-400">
            &copy; 2025 VMIED Institute. All rights reserved.
        </div>
    </div>

    <!-- Script to handle switching views -->
    <script>
        lucide.createIcons();

        function switchView(viewName) {
            document.getElementById('login-view').classList.add('hidden-section');
            document.getElementById('register-view').classList.add('hidden-section');
            document.getElementById('forgot-view').classList.add('hidden-section');

            const selected = document.getElementById(viewName + '-view');
            selected.classList.remove('hidden-section');
            
            selected.classList.remove('animate-fade-in');
            void selected.offsetWidth; 
            selected.classList.add('animate-fade-in');
        }

        async function handleLogin(e) {
            e.preventDefault();
            const form = e.target;
            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Đang xử lý...';
            btn.disabled = true;

            try {
                const res = await fetch('/login', {
                    method: 'POST',
                    body: new FormData(form)
                });
                const data = await res.json();
                
                if (data.status === 'success') {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        alert(data.alert || "Đăng nhập thành công!");
                    }
                } else {
                    alert(data.alert || "Có lỗi xảy ra, vui lòng thử lại.");
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (err) {
                alert("Đã xảy ra lỗi kết nối. Vui lòng thử lại.");
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
            return false;
        }

        async function handleRegister(e) {
            e.preventDefault();
            const form = e.target;
            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Đang xử lý...';
            btn.disabled = true;

            try {
                const res = await fetch('/register', {
                    method: 'POST',
                    body: new FormData(form)
                });
                const data = await res.json();
                
                if (data.status === 'success') {
                    alert(data.alert || "Đăng ký thành công!");
                    switchView('login');
                    form.reset();
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                } else {
                    alert(data.alert || "Có lỗi xảy ra, vui lòng thử lại.");
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (err) {
                alert("Đã xảy ra lỗi kết nối. Vui lòng thử lại.");
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
            return false;
        }
    </script>
</body>
</html>