<?php $this->extend('layouts/master') ?>
<?php $this->section('content') ?>
<!-- NAVIGATION -->
    <nav id="navbar" class="navbar navbar-expand-lg fixed-top glass-nav py-3">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand d-flex align-items-center gap-3" href="/#">
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
                    <li class="nav-item"><a class="nav-link text-secondary hover-primary" href="/#about">Về chúng tôi</a></li>
                    <li class="nav-item"><a class="nav-link text-secondary hover-primary" href="/#demo">Dùng thử</a></li>
                    <li class="nav-item"><a class="nav-link text-secondary hover-primary" href="/#features">Tính năng</a></li>
                    <li class="nav-item"><a class="nav-link text-secondary hover-primary" href="/#pricing">Bảng giá</a></li>
                    <li class="nav-item"><a class="nav-link text-secondary hover-primary" href="/#contact-section">Liên hệ</a></li>
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

<div class="container pt-5 mt-5 pb-5 mb-5" style="min-height: 50vh;">
    <div class="row pt-5 mt-3">
        <div class="col-lg-10 mx-auto">
            <h1 class="fw-bold mb-4 text-dark">Hướng dẫn sử dụng chi tiết AI Vmied</h1>
            <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border">
                
                <!-- Section 1 -->
                <div class="mb-5">
                    <h4 class="fw-bold mb-3 text-primary border-bottom pb-2">1. Tổng quan về Tài khoản</h4>
                    <ul class="text-secondary lh-lg mb-0">
                        <li><strong>Đăng ký/Đăng nhập:</strong> Bạn có thể tạo tài khoản miễn phí bằng địa chỉ Email cá nhân. Hệ thống cũng hỗ trợ đăng nhập nhanh nếu thông tin hợp lệ.</li>
                        <li><strong>Tài khoản Thường vs VIP:</strong> Tài khoản thường được trải nghiệm các tính năng cơ bản. Đối với khách hàng là tổ chức, trường học (hoặc Giảng viên), hệ thống cung cấp tài khoản <strong>VIP</strong> với giao diện quản lý riêng và hạn mức sử dụng cao hơn. Để nâng cấp VIP, vui lòng liên hệ Ban quản trị.</li>
                    </ul>
                </div>

                <!-- Section 2 -->
                <div class="mb-5">
                    <h4 class="fw-bold mb-3 text-primary border-bottom pb-2">2. Cách Nạp Điểm (V)</h4>
                    <p class="text-secondary lh-lg mb-3">AI Vmied sử dụng <strong>đồng "V"</strong> làm đơn vị thanh toán cho mỗi lần quét hoặc xử lý văn bản.</p>
                    <ol class="text-secondary lh-lg mb-0">
                        <li>Đăng nhập vào hệ thống, nhấp vào <strong>Avatar (Hình đại diện)</strong> của bạn ở góc trên bên phải để mở thanh menu Tài khoản (Sidebar).</li>
                        <li>Tại mục <strong>Số dư hiện tại</strong>, nhấn vào biểu tượng dấu <strong>(+)</strong>.</li>
                        <li>Một cửa sổ (Modal) sẽ hiện ra với <strong>Mã QR thanh toán</strong>.</li>
                        <li>Mở ứng dụng ngân hàng, quét mã QR và điền đúng nội dung chuyển khoản theo yêu cầu. Số V sẽ được tự động cộng vào tài khoản của bạn ngay sau khi giao dịch thành công.</li>
                    </ol>
                </div>

                <!-- Section 3 -->
                <div class="mb-5">
                    <h4 class="fw-bold mb-3 text-primary border-bottom pb-2">3. Hướng dẫn sử dụng các công cụ AI</h4>
                    
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark"><i data-lucide="scan-text" width="18" class="text-primary me-2"></i>A. Kiểm tra Đạo văn (Plagiarism)</h6>
                        <p class="text-secondary lh-lg mb-2">Công cụ đối chiếu văn bản của bạn với hơn 10 triệu nguồn dữ liệu học thuật và internet để phát hiện trùng lặp.</p>
                        <ul class="text-secondary lh-lg mb-0">
                            <li><strong>Cách dùng:</strong> Chọn tính năng "Check Đạo văn". Dán nội dung trực tiếp vào ô văn bản hoặc chọn tính năng <strong>Tải lên PDF/Word</strong>.</li>
                            <li><strong>Kết quả:</strong> Hệ thống sẽ trả về tỷ lệ % đạo văn, đồng thời đánh dấu màu đỏ các đoạn văn trùng lặp kèm theo nguồn (link) gốc để bạn đối chiếu.</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold text-dark"><i data-lucide="bot" width="18" class="text-primary me-2"></i>B. Phát hiện AI (AI Detection)</h6>
                        <p class="text-secondary lh-lg mb-2">Phân tích văn bản để xem nội dung có được tạo ra bởi các mô hình AI như ChatGPT, Gemini, hay Claude hay không.</p>
                        <ul class="text-secondary lh-lg mb-0">
                            <li><strong>Cách dùng:</strong> Dán đoạn văn bản cần kiểm tra. Quá trình xử lý diễn ra rất nhanh.</li>
                            <li><strong>Kết quả:</strong> Trả về xác suất % văn bản do AI viết và % do người thật viết, kèm theo phân tích chi tiết từng câu.</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold text-dark"><i data-lucide="spell-check-2" width="18" class="text-primary me-2"></i>C. Kiểm tra Ngữ pháp (Grammar Check)</h6>
                        <p class="text-secondary lh-lg mb-2">Tự động phát hiện lỗi chính tả, sai cấu trúc ngữ pháp và cách hành văn thiếu tự nhiên.</p>
                        <ul class="text-secondary lh-lg mb-0">
                            <li><strong>Gợi ý sửa đổi:</strong> Nhấp vào các từ bị gạch chân để xem các đề xuất thay thế từ AI, giúp đoạn văn mượt mà và chuyên nghiệp hơn.</li>
                        </ul>
                    </div>

                    <div class="mb-0">
                        <h6 class="fw-bold text-dark"><i data-lucide="user-check" width="18" class="text-primary me-2"></i>D. Tối ưu hóa văn bản (Humanizer)</h6>
                        <p class="text-secondary lh-lg mb-0">Tính năng này giúp biên tập lại các văn bản có vẻ "máy móc" (do AI viết) thành giọng văn tự nhiên, mang đậm tính con người (Humanized), dễ đọc và thân thiện hơn.</p>
                    </div>
                </div>

                <!-- Section 4 -->
                <div class="mb-0">
                    <h4 class="fw-bold mb-3 text-primary border-bottom pb-2">4. Lịch sử Giao dịch và Hoạt động</h4>
                    <ul class="text-secondary lh-lg mb-0">
                        <li><strong>Lịch sử Giao dịch:</strong> Xem lại chi tiết các lần nạp V, tiêu hao V từ mục <strong>"Lịch sử giao dịch"</strong> trong menu Tài khoản.</li>
                        <li><strong>Lịch sử Quét (Hoạt động):</strong> Truy cập mục <strong>"Lịch sử"</strong> trên Navbar hoặc bảng <strong>Hoạt động gần đây</strong> ở màn hình Dashboard để xem lại các kết quả Check đạo văn / AI Detection cũ mà không phải tốn V quét lại.</li>
                    </ul>
                </div>

            </div>
        </div>
    </div>
</div>

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
                        <li><a href="/huong-dan-su-dung" class="text-decoration-none text-secondary hover-text-white transition"><i data-lucide="chevron-right" width="12"></i> Hướng dẫn sử dụng</a></li>
                        <li><a href="/chinh-sach-bao-mat" class="text-decoration-none text-secondary hover-text-white transition"><i data-lucide="chevron-right" width="12"></i> Chính sách bảo mật</a></li>
                        <li><a href="/dieu-khoan-dich-vu" class="text-decoration-none text-secondary hover-text-white transition"><i data-lucide="chevron-right" width="12"></i> Điều khoản dịch vụ</a></li>
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