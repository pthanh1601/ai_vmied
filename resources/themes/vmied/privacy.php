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
            <h1 class="fw-bold mb-4 text-dark">Chính sách bảo mật (Privacy Policy)</h1>
            <p class="text-secondary mb-4">Cập nhật lần cuối: Tháng 8/2026. Xin vui lòng đọc kỹ chính sách bảo mật này để hiểu rõ cách chúng tôi thu thập, sử dụng và bảo vệ thông tin của bạn.</p>
            
            <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border">
                
                <div class="mb-4">
                    <h4 class="fw-bold mb-3 text-primary border-bottom pb-2">1. Thông tin chúng tôi thu thập</h4>
                    <ul class="text-secondary lh-lg mb-0">
                        <li><strong>Thông tin cá nhân:</strong> Họ tên, địa chỉ email, số điện thoại, và tên tổ chức/trường học (nếu có) khi bạn đăng ký tài khoản.</li>
                        <li><strong>Dữ liệu tải lên:</strong> Nội dung các đoạn văn bản, tài liệu (PDF, Word) bạn tải lên hệ thống để sử dụng các tính năng kiểm tra đạo văn, ngữ pháp, hay AI Detection.</li>
                        <li><strong>Dữ liệu kỹ thuật:</strong> Địa chỉ IP, loại trình duyệt, hệ điều hành, và lịch sử truy cập (cookie) để tối ưu hóa trải nghiệm.</li>
                    </ul>
                </div>

                <div class="mb-4">
                    <h4 class="fw-bold mb-3 text-primary border-bottom pb-2">2. Mục đích sử dụng thông tin</h4>
                    <p class="text-secondary lh-lg mb-2">Thông tin của bạn được thu thập nhằm mục đích:</p>
                    <ul class="text-secondary lh-lg mb-0">
                        <li>Cung cấp và duy trì dịch vụ ổn định.</li>
                        <li>Cá nhân hóa trải nghiệm người dùng và hỗ trợ khách hàng nhanh chóng.</li>
                        <li>Phát hiện, ngăn chặn các hành vi gian lận hoặc tấn công hệ thống.</li>
                        <li><em>Lưu ý:</em> Dữ liệu văn bản của bạn chỉ được dùng để phân tích trong phiên làm việc và <strong>KHÔNG</strong> được sử dụng để huấn luyện (train) các mô hình AI công khai.</li>
                    </ul>
                </div>

                <div class="mb-4">
                    <h4 class="fw-bold mb-3 text-primary border-bottom pb-2">3. Cam kết bảo vệ dữ liệu</h4>
                    <p class="text-secondary lh-lg mb-0">Viện Nghiên Cứu Phát Triển Giáo Dục Việt Mỹ (AI Vmied) áp dụng các biện pháp mã hóa tiêu chuẩn (SSL/TLS) trong quá trình truyền tải dữ liệu. Chúng tôi cam kết <strong>không bán, cho thuê hay trao đổi</strong> dữ liệu cá nhân của người dùng cho bất kỳ bên thứ ba nào vì mục đích thương mại.</p>
                </div>

                <div class="mb-0">
                    <h4 class="fw-bold mb-3 text-primary border-bottom pb-2">4. Quyền của Người dùng</h4>
                    <p class="text-secondary lh-lg mb-0">Bạn có toàn quyền truy cập, chỉnh sửa hoặc yêu cầu hệ thống xóa vĩnh viễn tài khoản và toàn bộ lịch sử dữ liệu của mình. Mọi yêu cầu vui lòng gửi qua tính năng "Trợ giúp & Hỗ trợ" trong tài khoản.</p>
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