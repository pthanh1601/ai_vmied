// ===== PAYMENTS PAGE FUNCTIONS =====

window.selectMethod = function (method, btn) {
    if (method !== 'vnpay') {
        return Swal.fire('Thông báo', 'Phương thức thanh toán này hiện đang bảo trì hoặc chưa được hỗ trợ. Vui lòng chọn VNPAY!', 'info');
    }

    document.querySelectorAll('.payment-detail').forEach(el => el.classList.add('d-none'));

    const detail = document.getElementById('detail-' + method);
    if (detail) detail.classList.remove('d-none');

    // Reset toàn bộ nút về mặc định
    document.querySelectorAll('.btn-method').forEach(el => {
        el.classList.remove('active');
    });

    // Thêm hiệu ứng cho nút đang được chọn
    if (btn) {
        btn.classList.add('active');
    }

    window._paymentCurrentMethod = method;
};

window.setAmount = function (amount, activeBtn) {
    window._paymentCurrentAmount = amount;
    document.querySelectorAll('.btn-pkg').forEach(el => {
        el.classList.remove('active');
    });
    if (activeBtn) {
        activeBtn.classList.add('active');
    }
    const customInput = document.getElementById('custom-amount');
    if (customInput && customInput !== activeBtn) {
        customInput.value = '';
    }
    
    // Update summary
    const formatted = new Intl.NumberFormat('vi-VN').format(amount);
    const vmiedEl = document.getElementById('vmied-receive');
    const summaryAmountEl = document.getElementById('summary-amount');
    const summaryTotalEl = document.getElementById('summary-total');
    if (vmiedEl) vmiedEl.innerText = formatted + ' V';
    if (summaryAmountEl) summaryAmountEl.innerText = formatted + ' ₫';
    if (summaryTotalEl) summaryTotalEl.innerText = formatted + ' ₫';
};

window.processDeposit = function () {
    const currentAmount = window._paymentCurrentAmount || 0;
    const currentMethod = window._paymentCurrentMethod || 'vnpay';

    if (!currentAmount || currentAmount < 10000) {
        return NeoUI.toast('Số tiền nạp tối thiểu là 10.000 VNĐ', 'warning');
    }

    fetch('/app/account/deposit', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ amount: currentAmount, method: currentMethod })
    })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (data.redirectUrl) {
                    Swal.fire({
                        title: 'Đang chuyển hướng',
                        text: data.alert,
                        icon: 'info',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = data.redirectUrl;
                    });
                } else {
                    Swal.fire('Thành công', data.alert, 'success').then(() => {
                        htmx.ajax('GET', data.redirect, {
                            target: '#app-content',
                            swap: 'outerHTML show:window:top'
                        });
                    });
                }
            } else {
                Swal.fire('Lỗi', data.alert, 'error');
            }
        })
        .catch(() => Swal.fire('Lỗi', 'Mất kết nối', 'error'));
};

function initPaymentPage() {
    window._paymentCurrentAmount = 50000;
    window._paymentCurrentMethod = 'vnpay';

    const defaultAmountBtn = document.querySelector('.btn-pkg[data-value="50000"]');
    if (defaultAmountBtn) window.setAmount(50000, defaultAmountBtn);

    const defaultMethodBtn = document.querySelector('.btn-method[onclick*="vnpay"]');
    if (defaultMethodBtn) window.selectMethod('vnpay', defaultMethodBtn);

    console.log('Payment page initialized');
}

window.__pageInit__ = function () {
    initPaymentPage();
};