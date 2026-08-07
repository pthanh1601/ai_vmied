// ===== HISTORY PAGE FUNCTIONS =====

window.switchTab = function (type) {
    const usage = document.getElementById('content-usage');
    const trans = document.getElementById('content-transaction');
    const btnUsage = document.getElementById('tab-usage');
    const btnTrans = document.getElementById('tab-transaction');

    if (type === 'usage') {
        usage.classList.remove('d-none');
        trans.classList.add('d-none');
        btnUsage.className = 'btn btn-dark rounded-3 px-4 py-2 fw-bold btn-sm shadow-sm';
        btnTrans.className = 'btn btn-link text-secondary text-decoration-none rounded-3 px-4 py-2 fw-bold btn-sm';
    } else {
        trans.classList.remove('d-none');
        usage.classList.add('d-none');
        btnTrans.className = 'btn btn-dark rounded-3 px-4 py-2 fw-bold btn-sm shadow-sm';
        btnUsage.className = 'btn btn-link text-secondary text-decoration-none rounded-3 px-4 py-2 fw-bold btn-sm';
    }
};

function initChart() {
    if (typeof Chart === 'undefined') { setTimeout(initChart, 100); return; }

    const canvas = document.getElementById('usageChartBootstrap');
    if (!canvas) { setTimeout(initChart, 100); return; }

    const existing = Chart.getChart(canvas);
    if (existing) existing.destroy();

    // Đọc data từ PHP truyền xuống qua data attribute
    const labels = JSON.parse(canvas.dataset.labels || '[]');
    const data   = JSON.parse(canvas.dataset.values || '[]');

    const ctx = canvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(13, 110, 253, 0.2)');
    gradient.addColorStop(1, 'rgba(13, 110, 253, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'VMIED tiêu (V)',
                data: data,
                borderColor: '#0d6efd',
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 3,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.parsed.y.toLocaleString('vi-VN') + ' V'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f0f0f0' },
                    ticks: {
                        callback: val => val.toLocaleString('vi-VN') + ' V'
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        maxTicksLimit: 10
                    }
                }
            }
        }
    });
}

function initDetailModal() {
    const modalEl = document.getElementById('detailModal');
    // Đảm bảo chỉ khởi tạo 1 lần duy nhất dù HTMX có load lại trang
    if (!modalEl || modalEl.dataset.initialized) return;
    modalEl.dataset.initialized = 'true';
    
    modalEl.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        if (!button) return;
        
        const id = button.getAttribute('data-id');
        const bodyEl = document.getElementById('modal-body-content');

        bodyEl.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary mb-3"></div>
                <p class="text-muted mt-3 fw-medium">Đang tải chi tiết báo cáo #${id}...</p>
            </div>
        `;

        // Fetch dữ liệu kèm Timestamp chống bộ nhớ đệm
        fetch(`/app/historys/detail/${id}?t=${Date.now()}`, {
            headers: { 'HX-Request': 'true', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.text())
        .then(html => {
            bodyEl.innerHTML = html;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        })
        .catch(err => {
            bodyEl.innerHTML = `<div class="text-center py-5 text-danger"><p>${err.message}</p></div>`;
        });
    });

    // Sửa chữ hide thành hidden để đảm bảo xóa sạch dữ liệu sau khi Modal đóng
    modalEl.addEventListener('hidden.bs.modal', () => {
        document.getElementById('modal-body-content').innerHTML = '';
    });
}

window.__pageInit__ = function () {
    if (typeof lucide !== 'undefined') lucide.createIcons();

    initChart();
    initDetailModal(); 

    const params = new URLSearchParams(window.location.search);
    if (params.get('tab') === 'transaction') {
        switchTab('transaction');
    }
};