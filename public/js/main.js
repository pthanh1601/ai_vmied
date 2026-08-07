// =============================================
// INIT ICONS
// =============================================
lucide.createIcons();

// =============================================
// DEMO LOGIC (giữ nguyên)
// =============================================
const textarea    = document.getElementById('demo-input');
const countDisplay = document.getElementById('word-count');
const overlay     = document.getElementById('loading-overlay');
const resultEmpty = document.getElementById('result-empty');
const resultFilled = document.getElementById('result-filled');

if (textarea) {
    textarea.addEventListener('input', function() {
        const text  = this.value.trim();
        const words = text ? text.split(/\s+/).length : 0;
        countDisplay.innerText = words;
        if (words > 200) countDisplay.classList.add('text-danger');
        else countDisplay.classList.remove('text-danger');
    });
}

function clearText() {
    textarea.value = '';
    countDisplay.innerText = '0';
    textarea.focus();
    resultFilled.classList.remove('d-flex');
    resultFilled.classList.add('d-none');
    resultEmpty.classList.remove('d-none');
}

function simulateProcess(type) {
    const text = textarea.value.trim();
    if (!text) {
        NeoUI.alert('Vui lòng nhập văn bản để kiểm tra!', 'warning');
        textarea.focus();
        return;
    }

    overlay.classList.remove('d-none');
    resultFilled.classList.remove('d-flex');
    resultFilled.classList.add('d-none');

    const chartRing    = document.getElementById('chart-ring');
    const chartScore   = document.getElementById('chart-score');
    const chartLabel   = document.getElementById('chart-label');
    const humanPercent = document.getElementById('human-percent');
    const aiPercent    = document.getElementById('ai-percent');
    const resultMsg    = document.getElementById('result-message');

    setTimeout(() => {
        overlay.classList.add('d-none');
        resultEmpty.classList.add('d-none');
        resultFilled.classList.remove('d-none');
        resultFilled.classList.add('d-flex');

        if (type === 'ai') {
            chartRing.style.background = 'conic-gradient(#dc3545 85%, #f1f5f9 0)';
            chartScore.innerText   = '85%';
            chartScore.className   = 'h3 fw-bold mb-0 text-danger';
            chartLabel.innerText   = 'AI PROB';
            humanPercent.innerText = '15%';
            aiPercent.innerText    = '85%';
            resultMsg.innerHTML    = '<i data-lucide="alert-triangle" style="width:16px;"></i> Khả năng cao do AI viết!';
            resultMsg.className    = 'text-danger fw-bold mb-0 d-flex align-items-center justify-content-center gap-2';
        } else {
            chartRing.style.background = 'conic-gradient(#22c55e 100%, #f1f5f9 0)';
            chartScore.innerText   = '100%';
            chartScore.className   = 'h3 fw-bold mb-0 text-success';
            chartLabel.innerText   = 'UNIQUE';
            humanPercent.innerText = '100%';
            aiPercent.innerText    = '0%';
            resultMsg.innerHTML    = '<i data-lucide="check-circle-2" style="width:16px;"></i> Văn bản hoàn toàn sạch!';
            resultMsg.className    = 'text-success fw-bold mb-0 d-flex align-items-center justify-content-center gap-2';
        }

        lucide.createIcons();
    }, 1500);
}

// =============================================
// PAYMENTS
// =============================================
window.Payments = function () {
    return {
        currentAmount: 50000,
        currentMethod: 'qr',

        format(amount) {
            return new Intl.NumberFormat('vi-VN').format(amount);
        },

        selectAmount(amount, btn) {
            this.currentAmount = amount;
            document.querySelectorAll('.btn-check-custom[data-value]').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');
            const input = document.getElementById('custom-amount');
            if (input) input.value = amount;
            this.updateSummary(amount);
        },

        onCustomInput(e) {
            document.querySelectorAll('.btn-check-custom[data-value]').forEach(b => b.classList.remove('active'));
            const val = parseInt(e.target.value) || 0;
            this.currentAmount = val;
            this.updateSummary(val);
        },

        updateSummary(amount) {
            const fmt = this.format(amount) + ' ₫';
            const el1 = document.getElementById('vmied-receive');
            const el2 = document.getElementById('summary-amount');
            const el3 = document.getElementById('summary-total');
            if (el1) el1.innerText = this.format(amount) + ' V';
            if (el2) el2.innerText = fmt;
            if (el3) el3.innerText = fmt;
        },

        selectMethod(method, btn) {
            this.currentMethod = method;
            document.querySelectorAll('.btn-check-custom:not([data-value])').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');
            document.querySelectorAll('.payment-detail').forEach(el => el.classList.add('d-none'));
            const detail = document.getElementById('detail-' + method);
            if (detail) detail.classList.remove('d-none');
        },

        copyText(text, btn) {
            navigator.clipboard.writeText(text).then(() => {
                const icon = btn.querySelector('[data-lucide]');
                if (!icon) return;
                icon.setAttribute('data-lucide', 'check');
                lucide.createIcons();
                btn.classList.add('text-success');
                setTimeout(() => {
                    icon.setAttribute('data-lucide', 'copy');
                    lucide.createIcons();
                    btn.classList.remove('text-success');
                }, 1500);
            });
        },

        confirmPayment() {
            if (!this.currentAmount || this.currentAmount < 10000) {
                NeoUI.alert('Vui lòng chọn hoặc nhập số tiền tối thiểu 10.000 ₫', 'warning');
                return;
            }
            // TODO: Tích hợp VNPay
            NeoUI.toast('Chức năng thanh toán đang được tích hợp.', 'info');
        },

        async loadBalance() {
            try {
                const res  = await fetch('/app/api/payment/balance');
                const data = await res.json();
                if (data.status !== 'success') return;
                const el = document.getElementById('wallet-balance');
                if (el) el.innerText = this.format(data.points) + ' V';
            } catch (e) {
                console.error('Lỗi load số dư:', e);
            }
        },

        async loadTransactions() {
            const tbody = document.getElementById('transaction-tbody');
            if (!tbody) return;

            try {
                const res  = await fetch('/app/api/payment/transactions?limit=10');
                const data = await res.json();

                if (!data.transactions || data.transactions.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="px-4 py-5 text-center text-secondary small">
                                <i data-lucide="inbox" width="32" class="d-block mx-auto mb-2 opacity-25"></i>
                                Chưa có giao dịch nào
                            </td>
                        </tr>`;
                    lucide.createIcons();
                    return;
                }

                const colorMap = { success: 'success', warning: 'warning', danger: 'danger' };

                tbody.innerHTML = data.transactions.map(tx => {
                    const d     = new Date(tx.created_at);
                    const date  = `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${d.getFullYear()} ${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`;
                    const amt   = this.format(tx.amount) + ' ₫';
                    const color = colorMap[tx.color] ?? 'secondary';
                    return `
                        <tr>
                            <td class="px-4 py-3 text-secondary font-monospace small">#${tx.uuid.slice(-6).toUpperCase()}</td>
                            <td class="px-4 py-3 fw-bold text-dark">${amt}</td>
                            <td class="px-4 py-3">
                                <span class="badge bg-${color} bg-opacity-10 text-${color} border border-${color}-subtle rounded-pill px-3">
                                    ${tx.status}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-end text-secondary small">${date}</td>
                        </tr>`;
                }).join('');

            } catch (e) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="px-4 py-4 text-center text-danger small">
                            <i data-lucide="wifi-off" width="18" class="me-1"></i>
                            Không thể tải dữ liệu, vui lòng thử lại.
                        </td>
                    </tr>`;
                lucide.createIcons();
            }
        },

        init() {
            this.updateSummary(this.currentAmount);
            this.loadBalance();
            this.loadTransactions();
            const customInput = document.getElementById('custom-amount');
            if (customInput) customInput.addEventListener('input', (e) => this.onCustomInput(e));
        }
    };
};

// Khởi tạo nếu đang ở trang payments
if (document.getElementById('transaction-tbody')) {
    document.addEventListener('DOMContentLoaded', () => {
        window._pay = Payments();
        window._pay.init();
    });
}