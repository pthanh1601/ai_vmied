// ===== AFFILIATE PAGE FUNCTIONS =====

window.toggleAddBank = function () {
    const el = document.getElementById('form-add-bank');
    if (!el) return;
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
    if (typeof lucide !== 'undefined') lucide.createIcons();
};

window.updateBankName = function (select) {
    const selected = select.options[select.selectedIndex];
    const hidden = document.getElementById('bank_name_hidden');
    if (hidden) hidden.value = selected.dataset.name || '';
};

window.onBankAdded = function (event) {
    const xhr = event.detail.xhr;
    try {
        const res = JSON.parse(xhr.responseText);
        if (res.status === 'success') {
            const select = document.querySelector('select[name="bank_account_uuid"]');
            if (select && res.data) {
                const opt = document.createElement('option');
                opt.value = res.data.uuid;
                opt.textContent = `${res.data.bank_name} - **** ${res.data.account_number.slice(-4)} (${res.data.account_name})`;
                opt.selected = true;
                select.appendChild(opt);
            }
            const form = document.getElementById('form-bank-account');
            if (form) form.reset();
            window.toggleAddBank();
        }
    } catch (e) {}
};

// Handler payout — định nghĩa 1 lần, tái sử dụng để có thể remove được
function _payoutAfterRequest(e) {
    if (!e.detail.elt || e.detail.elt.id !== 'form-payout') return;
    try {
        const res = JSON.parse(e.detail.xhr.responseText);
        const alertEl = document.getElementById('payout-alert');
        if (!alertEl) return;

        if (res.status === 'success') {
            alertEl.innerHTML = `
                <div class="alert alert-success rounded-4 border-0 d-flex align-items-center gap-2">
                    <i data-lucide="check-circle-2" width="18"></i>
                    ${res.alert}
                </div>`;
            const form = document.getElementById('form-payout');
            if (form) form.reset();
        } else {
            alertEl.innerHTML = `
                <div class="alert alert-danger rounded-4 border-0 d-flex align-items-center gap-2">
                    <i data-lucide="alert-circle" width="18"></i>
                    ${res.alert}
                </div>`;
        }

        if (typeof lucide !== 'undefined') lucide.createIcons();
    } catch (e) {}
}

window.__pageInit__ = function () {
    document.removeEventListener('htmx:afterRequest', _payoutAfterRequest);
    document.addEventListener('htmx:afterRequest', _payoutAfterRequest);

    if (typeof lucide !== 'undefined') lucide.createIcons();

    console.log('Affiliate page initialized ✓');
};