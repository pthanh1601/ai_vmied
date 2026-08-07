// ===== PAYMENTS PAGE FUNCTIONS =====

window.selectMethod = function (method, btn) {
    document.querySelectorAll('.payment-detail').forEach(el => el.classList.add('d-none'));

    const detail = document.getElementById('detail-' + method);
    if (detail) detail.classList.remove('d-none');

    document.querySelectorAll('[onclick^="selectMethod"]').forEach(el => {
        el.classList.remove('active');
        el.style.borderColor = '';
        el.style.backgroundColor = '';
    });

    btn.classList.add('active');
    btn.style.borderColor = '#0d6efd';
    btn.style.backgroundColor = '#e7f1ff';

    window._paymentCurrentMethod = method;
};

window.processDeposit = function () {
    const currentAmount = window._paymentCurrentAmount || 0;
    const currentMethod = window._paymentCurrentMethod || 'qr';

    if (!currentAmount || currentAmount < 10000) {
        return Swal.fire('Lỗi', 'Số tiền nạp tối thiểu là 10.000 VNĐ', 'error');
    }

    fetch('/app/account/deposit', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ amount: currentAmount, method: currentMethod })
    })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire('Thành công', data.alert, 'success').then(() => {
                    htmx.ajax('GET', data.redirect, {
                        target: '#app-content',
                        swap: 'outerHTML show:window:top'
                    });
                });
            } else {
                Swal.fire('Lỗi', data.alert, 'error');
            }
        })
        .catch(() => Swal.fire('Lỗi', 'Mất kết nối', 'error'));
};

function initPaymentPage() {
    const btns = document.querySelectorAll('.btn-check-custom[data-value]');
    if (btns.length === 0) return;

    window._paymentCurrentAmount = 50000;
    window._paymentCurrentMethod = 'qr';

    function updateSummary() {
        const amount = window._paymentCurrentAmount || 0;
        const formatted = new Intl.NumberFormat('vi-VN').format(amount);
        const vmiedEl = document.getElementById('vmied-receive');
        const summaryAmountEl = document.getElementById('summary-amount');
        const summaryTotalEl = document.getElementById('summary-total');
        if (vmiedEl) vmiedEl.innerText = formatted + ' V';
        if (summaryAmountEl) summaryAmountEl.innerText = formatted + ' ₫';
        if (summaryTotalEl) summaryTotalEl.innerText = formatted + ' ₫';
    }

    function setAmount(amount, activeBtn) {
        window._paymentCurrentAmount = amount;
        document.querySelectorAll('.btn-check-custom[data-value]').forEach(el => {
            el.classList.remove('active');
            el.style.borderColor = '';
            el.style.backgroundColor = '';
            el.style.color = '';
        });
        if (activeBtn) {
            activeBtn.classList.add('active');
            activeBtn.style.borderColor = '#0d6efd';
            activeBtn.style.backgroundColor = '#e7f1ff';
            activeBtn.style.color = '#0d6efd';
        }
        const customInput = document.getElementById('custom-amount');
        if (customInput) customInput.value = '';
        updateSummary();
    }

    // Clone để tránh duplicate listener khi init lại
    btns.forEach(btn => {
        const fresh = btn.cloneNode(true);
        btn.parentNode.replaceChild(fresh, btn);
        fresh.addEventListener('click', function () {
            setAmount(parseInt(this.dataset.value), this);
        });
    });

    const customInput = document.getElementById('custom-amount');
    if (customInput) {
        const freshInput = customInput.cloneNode(true);
        customInput.parentNode.replaceChild(freshInput, customInput);
        freshInput.addEventListener('input', function () {
            const val = parseInt(this.value);
            if (val > 0) {
                window._paymentCurrentAmount = val;
                document.querySelectorAll('.btn-check-custom[data-value]').forEach(el => {
                    el.classList.remove('active');
                    el.style.borderColor = '';
                    el.style.backgroundColor = '';
                    el.style.color = '';
                });
                updateSummary();
            }
        });
    }

    const defaultBtn = document.querySelector('.btn-check-custom[data-value="50000"]');
    if (defaultBtn) setAmount(50000, defaultBtn);

    console.log('Payment page initialized');
}

window.__pageInit__ = function () {
    initPaymentPage();
};