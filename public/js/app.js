/**
 * Neo Framework - Core UI & SPA Helpers
 * Phụ thuộc: HTMX, Alpine.js, Bootstrap 5, Lucide Icons
 */

if (window.htmx) {
    window.htmx.config.globalViewTransitions = false;
    window.htmx.config.selfRequestsOnly = false;
}

window.NeoUI = {
    /**
     * 1. XỬ LÝ PHẢN HỒI JSON TOÀN CỤC
     */
    processResponse: function(data) {
        if (!data || typeof data !== 'object') return;

        // Ưu tiên 1: ALERT (Chặn luồng)
        if (data.alert) {
            this.alert(data.alert, data.status || 'info', data.title || 'Thông báo', () => {
                if (data.redirect) this.redirect(data.redirect, data);
            });
            return;
        }

        // Ưu tiên 2: REDIRECT (Chuyển trang + Toast sau đó)
        if (data.redirect) {
            this.redirect(data.redirect, data);
            return;
        }
        // Ưu tiên 3: TOAST (Chỉ hiện thông báo)
        if (data.toast) {
            this.toast(data.toast, data.status || 'success');
        }
    },

    /**
     * 2. ĐIỀU HƯỚNG THÔNG MINH (SPA REDIRECT)
     */
    redirect: function(url, options = {}) {
        if (!url) return;

        // Fallback: Reload cứng nếu không dùng HTMX
        if (options.htmx === false || !window.htmx) {
            window.location.href = url;
            return;
        }

        if (options.close_modal !== false) this.closeAll();

        const appContent = document.querySelector('#app-content');
        const isToLogin = url.includes('/login');
        
        const targetSelector = options.target || (appContent && !isToLogin ? '#app-content' : 'body');
        const selectSelector = options.select || (targetSelector !== 'body' ? targetSelector : undefined);

        try {
            const promise = window.htmx.ajax('GET', url, {
                target: targetSelector,
                select: selectSelector, 
                swap: options.swap || 'innerHTML transition:true',
                headers: { 
                    'HX-Boosted': (targetSelector === 'body') ? 'true' : 'false' 
                }
            });

            if (promise && typeof promise.then === 'function') {
                promise.then(() => this.afterRedirect(url, options));
            } else {
                setTimeout(() => this.afterRedirect(url, options), 100);
            }
        } catch (e) {
            window.location.href = url;
        }
    },

    afterRedirect: function(url, options) {
        if (options.push !== false && window.location.pathname !== url) {
            window.history.pushState({}, '', url);
        }
        if (window.lucide) lucide.createIcons();
        if (options.toast) {
            setTimeout(() => {
                this.toast(options.toast, options.status || 'success');
            }, 100);
        }
    },

    /**
     * 3. QUẢN LÝ POPUP (MODAL & OFFCANVAS)
     */
    open: function(html) {
        if (!html || typeof html !== 'string') return;

        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const target = doc.querySelector('.modal-load, .offcanvas-load');

        if (!target) return;

        if (target.id) {
            const old = document.getElementById(target.id);
            if (old) {
                const oldInst = bootstrap.Modal.getInstance(old) || bootstrap.Offcanvas.getInstance(old);
                oldInst?.hide();
                old.remove();
            }
        }

        const element = document.importNode(target, true);
        document.body.appendChild(element);

        if (window.htmx) window.htmx.process(element);
        if (window.Alpine) window.Alpine.initTree(element);
        if (window.lucide) lucide.createIcons();

        if (element.classList.contains('modal')) {
            const modalInst = new bootstrap.Modal(element);
            modalInst.show();
            element.addEventListener('hidden.bs.modal', () => { modalInst.dispose(); element.remove(); });
        } else if (element.classList.contains('offcanvas')) {
            const offcanvasInst = new bootstrap.Offcanvas(element);
            offcanvasInst.show();
            element.addEventListener('hidden.bs.offcanvas', () => { offcanvasInst.dispose(); element.remove(); });
        }
    },

    /**
     * 4. THÔNG BÁO (TOAST)
     */
    toast: function(message, type = 'success') {
        if (!document.body) return;

        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }

        const colorClass = { 
            'success': 'text-bg-success', 
            'error': 'text-bg-danger', 
            'warning': 'text-bg-warning' 
        }[type] || 'text-bg-info';

        const id = 't-' + Date.now();
        const iconName = type === 'success' ? 'check-circle' : (type === 'error' ? 'alert-circle' : 'info');
        const iconSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-${iconName}"><circle cx="12" cy="12" r="10"/><path d="${type==='success'?'M9 12l2 2 4-4':'M12 8v4m0 4h.01'}"/></svg>`;

        container.insertAdjacentHTML('beforeend', `
            <div id="${id}" class="toast align-items-center ${colorClass} border-0 shadow-lg fade" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        ${iconSvg}
                        <span>${message}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>`);

        const el = document.getElementById(id);
        if (window.bootstrap && window.bootstrap.Toast) {
            const bToast = new bootstrap.Toast(el, { delay: 4000 });
            bToast.show();
        }
        el.addEventListener('hidden.bs.toast', () => el.remove());
    },

    /**
     * 5. ALERT (SWEETALERT2)
     */
    alert: function(message, type = 'info', title = 'Thông báo', callback = null) {
        if (window.Swal) {
            Swal.fire({
                title, text: message, icon: type === 'error' ? 'error' : type,
                confirmButtonText: 'Đồng ý', heightAuto: false, scrollbarPadding: false,
                customClass: { confirmButton: 'btn btn-primary shadow-sm px-4' }, buttonsStyling: false
            }).then(() => { 
                document.body.classList.remove('swal2-shown', 'swal2-height-auto');
                document.body.style.overflow = '';
                if (callback) callback(); 
            });
        } else {
            window.alert(message); 
            if (callback) callback();
        }
    },

    confirm: function(message, callback, type = 'question', title = 'Xác nhận') {
        if (window.Swal) {
            Swal.fire({
                title, text: message, icon: type, showCancelButton: true, confirmButtonText: 'Xác nhận', cancelButtonText: 'Hủy',
                heightAuto: false, scrollbarPadding: false,
                customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-light' }, buttonsStyling: false
            }).then((r) => { if (r.isConfirmed && callback) callback(); });
        } else {
            if (window.confirm(message)) callback();
        }
    },

    closeAll: function() {
        document.querySelectorAll('.modal.show, .offcanvas.show').forEach(el => {
            const inst = (bootstrap.Modal.getInstance(el) || bootstrap.Offcanvas.getInstance(el));
            if (inst) inst.hide();
        });
    }
};

/**
 * GLOBAL HELPERS
 */
window.modal = (url) => fetchUI(url);
window.offcanvas = (url) => fetchUI(url);
window.toast = (msg, type) => NeoUI.toast(msg, type);
window.alert = (msg, type, title, callback) => NeoUI.alert(msg, type, title, callback);
window.confirmAction = (msg, callback, type, title) => NeoUI.confirm(msg, callback, type, title);


// ==========================================================
// ===== MODAL NẠP TIỀN =====
// ==========================================================
window.NapTien = (function () {

    const METHOD_NAMES = { qr: 'Chuyển khoản', momo: 'Ví MoMo', card: 'Thẻ quốc tế' };

    let _amount = 50000;
    let _method = 'qr';

    // ── Helpers ───────────────────────────────────────────
    function fmt(v) {
        return new Intl.NumberFormat('vi-VN').format(v);
    }

    function updateSummary() {
        const map = {
            'modal-label-amount': fmt(_amount) + ' ₫',
            'modal-label-total':  fmt(_amount) + ' ₫',
            'modal-label-vmied':  fmt(_amount) + ' V',
            'modal-label-method': METHOD_NAMES[_method] || _method,
        };
        Object.entries(map).forEach(([id, val]) => {
            const el = document.getElementById(id);
            if (el) el.innerText = val;
        });
    }

    function setActiveBtn(selector, activeBtn) {
        document.querySelectorAll(selector).forEach(el => {
            el.classList.remove('active');
            el.style.borderColor     = '';
            el.style.backgroundColor = '';
            el.style.color           = '';
        });
        if (activeBtn) {
            activeBtn.classList.add('active');
            activeBtn.style.borderColor     = '#0d6efd';
            activeBtn.style.backgroundColor = '#e7f1ff';
            activeBtn.style.color           = '#0d6efd';
        }
    }

    // ── Public API ────────────────────────────────────────
    return {

        selectAmount: function (val, btn) {
            _amount = val;
            const inp = document.getElementById('modal-custom-amount');
            if (inp) inp.value = '';
            setActiveBtn('.btn-pkg', btn);
            updateSummary();
        },

        customAmount: function (val) {
            _amount = parseInt(val) || 0;
            setActiveBtn('.btn-pkg', null);
            updateSummary();
        },

        selectMethod: function (method, btn) {
            _method = method;
            setActiveBtn('.btn-method', btn);
            ['qr', 'momo', 'card'].forEach(m => {
                document.getElementById('modal-detail-' + m)
                    ?.classList.toggle('d-none', m !== method);
            });
            updateSummary();
            if (window.lucide) lucide.createIcons();
        },

        submit: function () {
            if (!_amount || _amount < 10000) {
                return NeoUI.toast('Số tiền nạp tối thiểu là 10.000 VNĐ', 'warning');
            }

            fetch('/app/account/deposit', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ amount: _amount, method: _method })
            })
                .then(res => res.json())
                .then(data => {
                    NeoUI.closeAll();
                    NeoUI.processResponse(data);
                    // ✅ Cập nhật số dư ví ngay lập tức
                    if (data.newBalance !== undefined) {
                        document.querySelectorAll('.user-point-display')
                            .forEach(el => el.textContent = Number(data.newBalance).toLocaleString('vi-VN'));
                    }
                })
                .catch(() => NeoUI.toast('Mất kết nối, vui lòng thử lại.', 'error'));
        },

        init: function () {
            const modalEl = document.getElementById('modalNapTien');
            if (!modalEl) return;

            _amount = 50000;
            _method = 'qr';

            modalEl.querySelectorAll('.btn-pkg').forEach(btn => {
                const fresh = btn.cloneNode(true);
                btn.parentNode.replaceChild(fresh, btn);
                fresh.addEventListener('click', function () {
                    NapTien.selectAmount(parseInt(this.dataset.value), this);
                });
            });

            const customInp = modalEl.querySelector('#modal-custom-amount');
            if (customInp) {
                const fresh = customInp.cloneNode(true);
                customInp.parentNode.replaceChild(fresh, customInp);
                fresh.addEventListener('input', function () {
                    NapTien.customAmount(this.value);
                });
            }

            const defaultBtn = modalEl.querySelector('.btn-pkg[data-value="50000"]');
            if (defaultBtn) setActiveBtn('.btn-pkg', defaultBtn);

            updateSummary();

            modalEl.addEventListener('shown.bs.modal', function () {
                if (window.lucide) lucide.createIcons();
                updateSummary();
            });

            console.log('[NapTien] initialized');
        }
    };
})();

// window.AIChecker = function () {
//     return {
//         STORAGE_KEY: 'aiCheckerConfig',

//         // ── Tham số gửi lên API ──────────────────────────────
//         params: {
//             content:               '',
//             title:                 'Bản quét',
//             aiModelVersion:        'multilang',
//             check_ai:               true,
//             check_plagiarism:       true,
//             check_grammar:          true,
//             check_readability:      true,
//             check_facts:            false,
//             check_contentOptimizer: false,
//             optimizerQuery:         '',
//             file_url:               '',
//         },

//         // ── Trạng thái UI ────────────────────────────────────
//         isLoading:    false,
//         isAnalyzed:   false,
//         results:      null,
//         modalData:    null,
//         loadingLabel: 'Đang phân tích...',
//         scanId:       null,

//         // ── Upload ───────────────────────────────────────────
//         isUploadLoading:  false,
//         uploadFileName:   '',
//         uploadProgress:   'Đang tải...',
//         _uploadedFileUrl: null,
//         _scanMode:        'text',   // 'text' | 'url'

//         // ── Danh sách 6 loại check để render toggle ──────────
//         checkList: [
//             { key: 'check_ai',               label: 'Phát hiện AI',        icon: 'bot',          color: 'primary'   },
//             { key: 'check_plagiarism',        label: 'Đạo văn',             icon: 'copy',         color: 'warning'   },
//             { key: 'check_grammar',           label: 'Ngữ pháp & Chính tả', icon: 'spell-check',  color: 'danger'    },
//             { key: 'check_readability',       label: 'Độ đọc hiểu',         icon: 'book-open',    color: 'info'      },
//             { key: 'check_facts',             label: 'Kiểm chứng sự thật',  icon: 'shield-check', color: 'success'   },
//             { key: 'check_contentOptimizer',  label: 'Tối ưu SEO',          icon: 'trending-up',  color: 'secondary' },
//         ],

//         // ============================================================
//         // INIT
//         // ============================================================
//         init() {
//             this._loadConfig();

//             this.$watch('params', () => this._saveConfig(), { deep: true });

//             window.addEventListener('ai-config-updated', () => {
//                 this._loadConfig();
//             });

//             this.$watch('results', () => {
//                 this.$nextTick(() => {
//                     if (window.lucide) lucide.createIcons();
//                     setTimeout(() => {
//                         document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
//                             const instance = bootstrap.Tooltip.getInstance(el);
//                             if (instance) instance.dispose();
//                             new bootstrap.Tooltip(el, { html: true });
//                         });
//                     }, 200);
//                 });
//             });
//         },

//         _loadConfig() {
//             try {
//                 const saved = localStorage.getItem(this.STORAGE_KEY);
//                 if (!saved) return;
//                 const cfg  = JSON.parse(saved);
//                 const keys = [
//                     'check_ai', 'check_plagiarism', 'check_grammar',
//                     'check_readability', 'check_facts', 'check_contentOptimizer',
//                     'aiModelVersion',
//                 ];
//                 keys.forEach(k => { if (k in cfg) this.params[k] = cfg[k]; });
//             } catch (e) {
//                 console.warn('[AIChecker] Lỗi load config:', e);
//             }
//         },

//         _saveConfig() {
//             try {
//                 const toSave = {
//                     check_ai:               this.params.check_ai,
//                     check_plagiarism:       this.params.check_plagiarism,
//                     check_grammar:          this.params.check_grammar,
//                     check_readability:      this.params.check_readability,
//                     check_facts:            this.params.check_facts,
//                     check_contentOptimizer: this.params.check_contentOptimizer,
//                     aiModelVersion:         this.params.aiModelVersion,
//                 };
//                 localStorage.setItem(this.STORAGE_KEY, JSON.stringify(toSave));
//             } catch (e) { /* quota exceeded */ }
//         },

//         // ============================================================
//         // FILE HANDLING
//         // ============================================================

//         handleFileChange(event) {
//             const file = event.target.files?.[0];
//             if (!file) return;
//             event.target.value = '';

//             if (file.name.toLowerCase().endsWith('.docx')) {
//                 this._readDocx(file);
//             } else if (file.name.toLowerCase().endsWith('.pdf') || file.type === 'application/pdf') {
//                 this._readPdf(file);
//             } else {
//                 NeoUI.toast('Chỉ hỗ trợ file .docx hoặc .pdf', 'warning');
//             }
//         },

//         async _readDocx(file) {
//             this.isUploadLoading = true;
//             this.uploadProgress  = 'Đang đọc file...';
//             this.uploadFileName  = file.name;
//             this._scanMode       = 'text';

//             try {
//                 await this._loadScript(
//                     'mammoth',
//                     'https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.6.0/mammoth.browser.min.js'
//                 );
//                 const buf    = await file.arrayBuffer();
//                 const result = await mammoth.extractRawText({ arrayBuffer: buf });
//                 const text   = result.value?.trim() ?? '';

//                 if (!text) throw new Error('File rỗng hoặc không đọc được nội dung.');

//                 this.params.content = text;
//                 this.params.title   = file.name.replace(/\.docx$/i, '');
//                 NeoUI.toast(`Đã tải: ${file.name}`, 'success');
//             } catch (err) {
//                 NeoUI.toast(err.message || 'Không thể đọc file .docx', 'error');
//                 this.clearFile();
//             } finally {
//                 this.isUploadLoading = false;
//             }
//         },

//         // ── _readPdf: đọc text + upload song song ─────────────────────
//         async _readPdf(file) {
//             this.isUploadLoading = true;
//             this.uploadProgress  = 'Đang đọc PDF và tải lên máy chủ...';
//             this.uploadFileName  = file.name;
//             this._scanMode       = 'text'; // mặc định, đổi sang 'url' nếu upload OK

//             try {
//                 // Chạy song song: extract text và upload file lên server
//                 const [textResult, uploadResult] = await Promise.allSettled([
//                     this._extractPdfText(file),
//                     this._uploadFileToServer(file),
//                 ]);

//                 // ── Xử lý kết quả đọc text ──────────────────────────
//                 if (textResult.status === 'fulfilled') {
//                     this.params.content = textResult.value;
//                     this.params.title   = file.name.replace(/\.pdf$/i, '');
//                     NeoUI.toast(`Đã đọc PDF: ${file.name}`, 'success');
//                 } else {
//                     // Đọc text thất bại → không tiếp tục được
//                     throw new Error(textResult.reason?.message || 'Không thể đọc nội dung file PDF.');
//                 }

//                 // ── Xử lý kết quả upload ────────────────────────────
//                 if (uploadResult.status === 'fulfilled' && uploadResult.value) {
//                     this._uploadedFileUrl = uploadResult.value;
//                     this._scanMode        = 'text';
//                 } else {
//                     // Upload thất bại → vẫn dùng được text mode, chỉ warn
//                     console.warn('[AIChecker] Upload thất bại, giữ text mode:', uploadResult.reason?.message);
//                     NeoUI.toast('Lưu file lên server thất bại, sẽ dùng nội dung văn bản trực tiếp.', 'warning');
//                 }

//             } catch (err) {
//                 NeoUI.toast(err.message || 'Không thể đọc file PDF', 'error');
//                 this.clearFile();
//             } finally {
//                 this.isUploadLoading = false;
//             }
//         },

//         // ── Tách riêng: chỉ extract text từ PDF, trả về Promise<string> ──
//         // async _extractPdfText(file) {
//         //     await this._loadScript(
//         //         'pdfjsLib',
//         //         'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js'
//         //     );
//         //     pdfjsLib.GlobalWorkerOptions.workerSrc =
//         //         'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

//         //     const buf = await file.arrayBuffer();
//         //     const pdf = await pdfjsLib.getDocument({ data: buf }).promise;
//         //     let fullText = '';

//         //     for (let i = 1; i <= pdf.numPages; i++) {
//         //         const page     = await pdf.getPage(i);
//         //         const content  = await page.getTextContent({ normalizeWhitespace: true });
//         //         const viewport = page.getViewport({ scale: 1 });

//         //         // 1. Tập hợp tất cả text items với tọa độ thực
//         //         const items = content.items
//         //             .filter(item => item.str && item.str.trim() !== '')
//         //             .map(item => ({
//         //                 str:    item.str,
//         //                 x:      item.transform[4],
//         //                 y:      Math.round(viewport.height - item.transform[5]), // flip Y
//         //                 width:  item.width,
//         //                 height: item.height,
//         //                 fontSz: Math.abs(item.transform[3]),
//         //             }));

//         //         if (!items.length) continue;

//         //         // 2. Group theo dòng (tolerance = nửa font size)
//         //         const lines = [];
//         //         for (const item of items) {
//         //             const tolerance = (item.fontSz || 12) * 0.5;
//         //             const line = lines.find(l => Math.abs(l.y - item.y) <= tolerance);
//         //             if (line) {
//         //                 line.items.push(item);
//         //             } else {
//         //                 lines.push({ y: item.y, items: [item] });
//         //             }
//         //         }

//         //         // 3. Sort dòng từ trên xuống, item từ trái sang phải
//         //         lines.sort((a, b) => a.y - b.y);

//         //         for (const line of lines) {
//         //             line.items.sort((a, b) => a.x - b.x);

//         //             let lineText  = '';
//         //             let prevRight = null;

//         //             for (const item of line.items) {
//         //                 if (prevRight !== null) {
//         //                     const gap          = item.x - prevRight;
//         //                     const avgCharWidth = item.width / (item.str.length || 1);
//         //                     // Nếu gap > 30% width ký tự → thêm space
//         //                     if (gap > avgCharWidth * 0.3) lineText += ' ';
//         //                 }
//         //                 lineText  += item.str;
//         //                 prevRight  = item.x + item.width;
//         //             }

//         //             const trimmed = lineText.trim();
//         //             if (trimmed) fullText += trimmed + '\n';
//         //         }

//         //         // Ngăn cách trang
//         //         fullText += '\n';
//         //     }

//         //     fullText = fullText.trim();
//         //     if (!fullText) {
//         //         throw new Error('PDF rỗng hoặc chỉ chứa hình ảnh, không đọc được text.');
//         //     }

//         //     return fullText;
//         // },
        
//         async _extractPdfText(file) {
//             await this._loadScript(
//                 'pdfjsLib',
//                 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js'
//             );
//             pdfjsLib.GlobalWorkerOptions.workerSrc =
//                 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

//             const buf = await file.arrayBuffer();
//             const pdf = await pdfjsLib.getDocument({ data: buf }).promise;
//             let fullText = '';

//             for (let i = 1; i <= pdf.numPages; i++) {
//                 const page     = await pdf.getPage(i);
//                 const content  = await page.getTextContent({ normalizeWhitespace: true });
//                 const viewport = page.getViewport({ scale: 1 });

//                 // 1. Tập hợp tất cả text items với tọa độ thực
//                 const items = content.items
//                     .filter(item => item.str && item.str.trim() !== '')
//                     .map(item => ({
//                         str:    item.str,
//                         x:      item.transform[4],
//                         y:      Math.round(viewport.height - item.transform[5]), // flip Y
//                         width:  item.width,
//                         height: item.height,
//                         fontSz: Math.abs(item.transform[3]),
//                     }));

//                 if (!items.length) continue;

//                 // 2. Group theo dòng (tolerance = nửa font size)
//                 const lines = [];
//                 for (const item of items) {
//                     const tolerance = (item.fontSz || 12) * 0.5;
//                     const line = lines.find(l => Math.abs(l.y - item.y) <= tolerance);
//                     if (line) {
//                         line.items.push(item);
//                     } else {
//                         lines.push({ y: item.y, items: [item] });
//                     }
//                 }

//                 // 3. Sort dòng từ trên xuống, item từ trái sang phải
//                 lines.sort((a, b) => a.y - b.y);

//                 let lastLineTrimmed = '';
//                 for (const line of lines) {
//                     line.items.sort((a, b) => a.x - b.x);

//                     let lineText  = '';
//                     let prevRight = null;

//                     for (const item of line.items) {
//                         if (prevRight !== null) {
//                             const gap          = item.x - prevRight;
//                             const avgCharWidth = item.width / (item.str.length || 1);
//                             if (gap > avgCharWidth * 0.3) lineText += ' ';
//                         }
//                         lineText  += item.str;
//                         prevRight  = item.x + item.width;
//                     }

//                     const trimmed = lineText.trim();
//                     if (trimmed) {
//                         // Bỏ qua các dòng chỉ chứa số trang (ví dụ: "26", "27")
//                         if (/^\s*\d+\s*$/.test(trimmed)) continue;

//                         if (fullText === '') {
//                             fullText = trimmed;
//                         } else {
//                             // LOGIC THÔNG MINH:
//                             // Nếu dòng trước kết thúc bằng dấu kết thúc câu (., !, ?, :) 
//                             // Hoặc dòng hiện tại bắt đầu bằng ký tự danh sách, đề mục (1., 1.5.2, -, *, ()
//                             // Thì mới cho xuống dòng (\n). Ngược lại thì nối dòng bằng khoảng trắng.
//                             const isEndOfSentence = /[.\!?:]$/.test(lastLineTrimmed);
//                             const isNewParagraph = /^\s*([\d\.\-\*\(]+|[A-ZĐ])/u.test(trimmed);

//                             if (isEndOfSentence || isNewParagraph) {
//                                 fullText += '\n' + trimmed;
//                             } else {
//                                 fullText += ' ' + trimmed;
//                             }
//                         }
//                         lastLineTrimmed = trimmed;
//                     }
//                 }

//                 // Ngăn cách trang (giữ một khoảng xuống dòng rõ ràng giữa các trang)
//                 fullText += '\n';
//             }

//             fullText = fullText.trim();
//             if (!fullText) {
//                 throw new Error('PDF rỗng hoặc chỉ chứa hình ảnh, không đọc được text.');
//             }

//             return fullText;
//         },

//         async _uploadFileToServer(file) {
//             const formData = new FormData();
//             formData.append('file', file);

//             const uploadRes  = await fetch('/app/uploads/scans', { method: 'POST', body: formData });
//             const uploadData = await uploadRes.json();

//             if (!uploadRes.ok) {
//                 throw new Error(uploadData.message || `HTTP ${uploadRes.status}`);
//             }

//             if (!uploadData.url) {
//                 throw new Error('Server không trả về URL file.');
//             }

//             return uploadData.url;
//         },

//         clearFile() {
//             this.uploadFileName   = '';
//             this._uploadedFileUrl = null;
//             this._scanMode        = 'text';
//             this.params.content   = '';
//         },

//         // ============================================================
//         // COMPUTED
//         // ============================================================

//         get wordCount() {
//             const text = this.params.content.trim();
//             return text ? text.split(/\s+/).length : 0;
//         },

//         get falseFacts() {
//             return (this.results?.facts ?? []).filter(f =>
//                 ['false', 'unverified'].includes((f.classification ?? '').toLowerCase())
//             );
//         },

//         // ============================================================
//         // RUN ANALYSIS
//         // ============================================================

//         async runAnalysis() {
//             this._loadConfig();

//             // Validate
//             if (this._scanMode === 'url') {
//                 if (!this._uploadedFileUrl) {
//                     return NeoUI.toast('Vui lòng upload file PDF trước.', 'warning');
//                 }
//             } else {
//                 if (this.wordCount < 10) {
//                     return NeoUI.toast('Vui lòng nhập ít nhất 10 từ.', 'warning');
//                 }
//             }

//             const anyCheck = this.checkList.some(c => this.params[c.key]);
//             if (!anyCheck) {
//                 return NeoUI.toast('Vui lòng bật ít nhất 1 loại kiểm tra.', 'warning');
//             }

//             this.isLoading    = true;
//             this.scanId       = null;
//             this.loadingLabel = this._scanMode === 'url'
//                 ? 'Đang phân tích PDF...'
//                 : 'Đang phân tích văn bản...';

//             try {
//                 let endpoint, payload;

//                 if (this._scanMode === 'url') {
//                     endpoint = '/mock/originality/scan/url';
//                     payload  = {
//                         url:                    this._uploadedFileUrl,
//                         title:                  this.params.title || this.uploadFileName || 'PDF Scan',
//                         aiModelVersion:         this.params.aiModelVersion,
//                         check_ai:               this.params.check_ai,
//                         check_plagiarism:       this.params.check_plagiarism,
//                         check_grammar:          this.params.check_grammar,
//                         check_readability:      this.params.check_readability,
//                         check_facts:            this.params.check_facts,
//                         check_contentOptimizer: this.params.check_contentOptimizer,
//                         optimizerQuery:         this.params.optimizerQuery,
//                     };
//                 } else {
//                     endpoint = '/mock/originality/scan';
//                     payload  = {
//                         ...this.params,
//                         file_url: this._uploadedFileUrl ?? null,
//                     };
//                 }
//                 console.log('[DEBUG payload]', JSON.stringify(payload, null, 2)); // ← thêm dòng này

//                 const res  = await fetch(endpoint, {
//                     method:  'POST',
//                     headers: { 'Content-Type': 'application/json' },
//                     body:    JSON.stringify(payload),
//                 });

//                 const data = await res.json();

//                 if (!res.ok) {
//                     NeoUI.toast(data.message || `Lỗi server (${res.status})`, 'error');
//                     return;
//                 }

//                 // Normalize facts nếu là object thay vì array
//                 if (data.facts && !Array.isArray(data.facts)) {
//                     data.facts = Object.values(data.facts);
//                 }

//                 this.scanId     = data.scanId ?? null;
//                 this.results    = this._mapErrorsToBlocks(data);
//                 this.isAnalyzed = true;

//             } catch (e) {
//                 console.error('[AIChecker] runAnalysis error:', e);
//                 NeoUI.toast('Mất kết nối máy chủ. Vui lòng thử lại.', 'error');
//             } finally {
//                 this.isLoading = false;
//             }
//         },

//         editAgain() {
//             this.isAnalyzed = false;
//             this.results    = null;
//             this.scanId     = null;
//             this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
//         },

//         // ============================================================
//         // BLOCK MAPPING
//         // ============================================================

//         _mapErrorsToBlocks(data) {
//             let blocks = data.ai?.blocks ?? [];

//             if (blocks.length === 0 && this.params.content) {
//                 const sentences = this.params.content.match(/[^.!?\n]+[.!?\n]*/g) || [this.params.content];
//                 blocks = sentences.map(s => ({ text: s }));
//                 if (!data.ai) data.ai = {};
//                 data.ai.blocks = blocks;
//             }

//             blocks.forEach(b => {
//                 b.htmlText      = b.text;
//                 b.grammarErrors = [];
//                 b.highlights    = [];
//             });

//             const matches     = data.grammarSpelling?.matches ?? [];
//             const plagResults = data.plagiarism?.results      ?? [];

//             matches.forEach(match => {
//                 const sentence = (match.sentence || '').toLowerCase().trim();
//                 if (!sentence) return;

//                 const idx = blocks.findIndex(b => {
//                     const bText = (b.text || '').toLowerCase().trim();
//                     return bText.includes(sentence) || sentence.includes(bText) || bText === sentence;
//                 });

//                 if (idx !== -1) {
//                     blocks[idx].grammarErrors.push(match);

//                     let errWord = '';
//                     if (match.context && match.context.text && match.context.offset !== undefined) {
//                         errWord = match.context.text.substr(match.context.offset, match.length);
//                     }

//                     if (errWord && this.params.check_grammar) {
//                         blocks[idx].highlights.push(errWord);
//                     }
//                 }
//             });

//             blocks.forEach(b => {
//                 if (b.highlights && b.highlights.length > 0) {
//                     let uniqueWords = [...new Set(b.highlights)].sort((a, b) => b.length - a.length);

//                     const escapeRegExp = (string) => string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

//                     uniqueWords.forEach(w => {
//                         const regex = new RegExp(`(<[^>]+>)|(${escapeRegExp(w)})`, 'gi');
//                         b.htmlText = b.htmlText.replace(regex, (match, tag, word) => {
//                             if (tag)  return tag;
//                             if (word) {
//                                 return `<span class="text-danger fw-bold text-decoration-underline" style="text-decoration-style: wavy; background-color: rgba(220,53,69,0.1); padding: 0 2px; border-radius: 3px;">${word}</span>`;
//                             }
//                             return match;
//                         });
//                     });
//                 }
//             });

//             if (plagResults.length > 0 && blocks.length > 0) {
//                 let mappedCount = 0;
//                 plagResults.forEach(pr => {
//                     const phrase = (pr.phrase || pr.match || pr.text || '').toLowerCase().trim();
//                     if (!phrase) return;

//                     const idx = blocks.findIndex(b => {
//                         const bText      = (b.text || '').toLowerCase().replace(/\s+/g, ' ').trim();
//                         const cleanPhrase = phrase.replace(/\s+/g, ' ');
//                         return bText.includes(cleanPhrase.slice(0, 40)) || cleanPhrase.includes(bText.slice(0, 40));
//                     });

//                     if (idx !== -1 && !blocks[idx].plagiarism) {
//                         blocks[idx].plagiarism = pr;
//                         mappedCount++;
//                     }
//                 });

//                 if (mappedCount === 0 && this.params.check_plagiarism) {
//                     const target = blocks.length > 1 ? 1 : 0;
//                     blocks[target].plagiarism = plagResults[0];
//                 }
//             }

//             return data;
//         },

//         // ============================================================
//         // BLOCK STYLING
//         // ============================================================

//         getBlockClass(block) {
//             const classes = ['hl-block', 'p-1', 'rounded', 'd-inline', 'lh-lg'];

//             if (this.params.check_ai && block.result !== undefined) {
//                 const fake = block.result.fake ?? 0;
//                 if (fake > 0.8)      classes.push('bg-danger bg-opacity-25');
//                 else if (fake > 0.5) classes.push('bg-warning bg-opacity-25');
//                 else if (fake > 0.3) classes.push('bg-warning bg-opacity-10');
//                 else                 classes.push('bg-success bg-opacity-10');
//             }

//             if (block.plagiarism && this.params.check_plagiarism)
//                 classes.push('border-bottom border-3 border-warning fw-medium');

//             return classes.join(' ');
//         },

//         getTooltip(block) {
//             let html = '';

//             if (this.params.check_ai && block.result !== undefined) {
//                 const fake = ((block.result.fake ?? 0) * 100).toFixed(1);
//                 html += `<strong>AI: ${fake}%</strong>`;
//             }

//             if (block.grammarErrors && block.grammarErrors.length > 0 && this.params.check_grammar) {
//                 html += (html ? '<br>' : '') + `<span class="text-danger">✗ ${block.grammarErrors.length} lỗi chính tả/ngữ pháp</span>`;
//             }
//             if (block.plagiarism && this.params.check_plagiarism) {
//                 html += (html ? '<br>' : '') + `<span class="text-warning">⚠ Nghi ngờ đạo văn</span>`;
//             }
//             return html || 'Nội dung nguyên bản';
//         },

//         openDetailModal(block) {
//             if (!block.plagiarism && (!block.grammarErrors || block.grammarErrors.length === 0)) {
//                 NeoUI.toast('Câu này không có lỗi chi tiết.', 'info');
//                 return;
//             }
//             this.modalData = block;
//             this.$nextTick(() => {
//                 if (window.lucide) lucide.createIcons();
//                 const el = document.getElementById('detailModal');
//                 if (el) {
//                     (bootstrap.Modal.getInstance(el) ?? new bootstrap.Modal(el)).show();
//                 }
//             });
//         },

//         // ============================================================
//         // LAZY SCRIPT LOADER
//         // ============================================================

//         _loadScript(globalName, src) {
//             return new Promise((resolve, reject) => {
//                 if (window[globalName]) return resolve();
//                 const s   = document.createElement('script');
//                 s.src     = src;
//                 s.onload  = resolve;
//                 s.onerror = () => reject(new Error(`Không load được: ${src}`));
//                 document.head.appendChild(s);
//             });
//         },
//     };
// };

// window.AIChecker = function () {
//     return {
//         STORAGE_KEY: 'aiCheckerConfig',

//         // ── Tham số gửi lên API ──────────────────────────────
//         params: {
//             content:               '',
//             title:                 'Bản quét',
//             aiModelVersion:        'multilang',
//             check_ai:               true,
//             check_plagiarism:       true,
//             check_grammar:          true,
//             check_readability:      true,
//             check_facts:            false,
//             check_contentOptimizer: false,
//             optimizerQuery:         '',
//             file_url:               '',
//         },

//         // ── Trạng thái UI ────────────────────────────────────
//         isLoading:    false,
//         isAnalyzed:   false,
//         results:      null,
//         modalData:    null,
//         loadingLabel: 'Đang phân tích...',
//         scanId:       null,

//         // ── Upload ───────────────────────────────────────────
//         isUploadLoading:  false,
//         uploadFileName:   '',
//         uploadProgress:   'Đang tải...',
//         _uploadedFileUrl: null,
//         _scanMode:        'text',   // 'text' | 'url'

//         // ── Danh sách 6 loại check để render toggle ──────────
//         checkList: [
//             { key: 'check_ai',               label: 'Phát hiện AI',        icon: 'bot',          color: 'primary'   },
//             { key: 'check_plagiarism',        label: 'Đạo văn',             icon: 'copy',         color: 'warning'   },
//             { key: 'check_grammar',           label: 'Ngữ pháp & Chính tả', icon: 'spell-check',  color: 'danger'    },
//             { key: 'check_readability',       label: 'Độ đọc hiểu',         icon: 'book-open',    color: 'info'      },
//             { key: 'check_facts',             label: 'Kiểm chứng sự thật',  icon: 'shield-check', color: 'success'   },
//             { key: 'check_contentOptimizer',  label: 'Tối ưu SEO',          icon: 'trending-up',  color: 'secondary' },
//         ],

//         // ============================================================
//         // INIT
//         // ============================================================
//         init() {
//             this._loadConfig();

//             this.$watch('params', () => this._saveConfig(), { deep: true });

//             window.addEventListener('ai-config-updated', () => {
//                 this._loadConfig();
//             });

//             this.$watch('results', () => {
//                 this.$nextTick(() => {
//                     if (window.lucide) lucide.createIcons();
//                     setTimeout(() => {
//                         document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
//                             const instance = bootstrap.Tooltip.getInstance(el);
//                             if (instance) instance.dispose();
//                             new bootstrap.Tooltip(el, { html: true });
//                         });
//                     }, 200);
//                 });
//             });
//         },

//         _loadConfig() {
//             try {
//                 const saved = localStorage.getItem(this.STORAGE_KEY);
//                 if (!saved) return;
//                 const cfg  = JSON.parse(saved);
//                 const keys = [
//                     'check_ai', 'check_plagiarism', 'check_grammar',
//                     'check_readability', 'check_facts', 'check_contentOptimizer',
//                     'aiModelVersion',
//                 ];
//                 keys.forEach(k => { if (k in cfg) this.params[k] = cfg[k]; });
//             } catch (e) {
//                 console.warn('[AIChecker] Lỗi load config:', e);
//             }
//         },

//         _saveConfig() {
//             try {
//                 const toSave = {
//                     check_ai:               this.params.check_ai,
//                     check_plagiarism:       this.params.check_plagiarism,
//                     check_grammar:          this.params.check_grammar,
//                     check_readability:      this.params.check_readability,
//                     check_facts:            this.params.check_facts,
//                     check_contentOptimizer: this.params.check_contentOptimizer,
//                     aiModelVersion:         this.params.aiModelVersion,
//                 };
//                 localStorage.setItem(this.STORAGE_KEY, JSON.stringify(toSave));
//             } catch (e) { /* quota exceeded */ }
//         },

//         // ============================================================
//         // FILE HANDLING
//         // ============================================================

//         handleFileChange(event) {
//             const file = event.target.files?.[0];
//             if (!file) return;
//             event.target.value = '';

//             if (file.name.toLowerCase().endsWith('.docx')) {
//                 this._readDocx(file);
//             } else if (file.name.toLowerCase().endsWith('.pdf') || file.type === 'application/pdf') {
//                 this._readPdf(file);
//             } else {
//                 NeoUI.toast('Chỉ hỗ trợ file .docx hoặc .pdf', 'warning');
//             }
//         },

//         async _readDocx(file) {
//             this.isUploadLoading = true;
//             this.uploadProgress  = 'Đang đọc file...';
//             this.uploadFileName  = file.name;
//             this._scanMode       = 'text';

//             try {
//                 await this._loadScript(
//                     'mammoth',
//                     'https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.6.0/mammoth.browser.min.js'
//                 );
//                 const buf    = await file.arrayBuffer();
//                 const result = await mammoth.extractRawText({ arrayBuffer: buf });
//                 const text   = result.value?.trim() ?? '';

//                 if (!text) throw new Error('File rỗng hoặc không đọc được nội dung.');

//                 this.params.content   = text;
//                 this.params.title     = file.name.replace(/\.docx$/i, '');
//                 this._uploadedFileUrl = null;   // docx không có file_url server
//                 NeoUI.toast(`Đã tải: ${file.name}`, 'success');
//             } catch (err) {
//                 NeoUI.toast(err.message || 'Không thể đọc file .docx', 'error');
//                 this.clearFile();
//             } finally {
//                 this.isUploadLoading = false;
//             }
//         },

//         // ── _readPdf: upload lên server để lấy URL, dùng cho OpenAI Files API ──
//         async _readPdf(file) {
//             this.isUploadLoading = true;
//             this.uploadProgress  = 'Đang tải PDF lên máy chủ...';
//             this.uploadFileName  = file.name;
//             this._scanMode       = 'text';
//             this._uploadedFileUrl = null;
//             this.params.content  = '';  // xóa content cũ

//             try {
//                 // Upload file lên server trước — backend sẽ gửi file này cho GPT
//                 const uploadedUrl = await this._uploadFileToServer(file);

//                 // Upload thành công → lưu URL, không cần extract text nữa
//                 this._uploadedFileUrl = uploadedUrl;
//                 this._scanMode        = 'pdf-file';  // mode mới: gửi file_url cho backend
//                 this.params.title     = file.name.replace(/\.pdf$/i, '');
//                 this.params.content   = '';  // không cần content

//                 NeoUI.toast(`Đã tải lên: ${file.name}`, 'success');

//             } catch (err) {
//                 // Upload thất bại → fallback: extract text phía client
//                 console.warn('[AIChecker] Server upload thất bại, fallback sang extract text:', err.message);
//                 NeoUI.toast('Không upload được file, đang đọc nội dung trực tiếp...', 'warning');

//                 try {
//                     const text = await this._extractPdfText(file);
//                     this.params.content   = text;
//                     this.params.title     = file.name.replace(/\.pdf$/i, '');
//                     this._uploadedFileUrl = null;
//                     this._scanMode        = 'text';
//                     NeoUI.toast(`Đã đọc PDF (text mode): ${file.name}`, 'success');
//                 } catch (extractErr) {
//                     NeoUI.toast(extractErr.message || 'Không thể đọc file PDF', 'error');
//                     this.clearFile();
//                 }
//             } finally {
//                 this.isUploadLoading = false;
//             }
//         },

//         async _extractPdfText(file) {
//             await this._loadScript(
//                 'pdfjsLib',
//                 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js'
//             );
//             pdfjsLib.GlobalWorkerOptions.workerSrc =
//                 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

//             const buf = await file.arrayBuffer();
//             const pdf = await pdfjsLib.getDocument({ data: buf }).promise;
//             let fullText = '';

//             for (let i = 1; i <= pdf.numPages; i++) {
//                 const page     = await pdf.getPage(i);
//                 const content  = await page.getTextContent({ normalizeWhitespace: true });
//                 const viewport = page.getViewport({ scale: 1 });

//                 const items = content.items
//                     .filter(item => item.str && item.str.trim() !== '')
//                     .map(item => ({
//                         str:    item.str,
//                         x:      item.transform[4],
//                         y:      Math.round(viewport.height - item.transform[5]),
//                         width:  item.width,
//                         height: item.height,
//                         fontSz: Math.abs(item.transform[3]),
//                     }));

//                 if (!items.length) continue;

//                 const lines = [];
//                 for (const item of items) {
//                     const tolerance = (item.fontSz || 12) * 0.5;
//                     const line = lines.find(l => Math.abs(l.y - item.y) <= tolerance);
//                     if (line) {
//                         line.items.push(item);
//                     } else {
//                         lines.push({ y: item.y, items: [item] });
//                     }
//                 }

//                 lines.sort((a, b) => a.y - b.y);

//                 let lastLineTrimmed = '';
//                 for (const line of lines) {
//                     line.items.sort((a, b) => a.x - b.x);

//                     let lineText  = '';
//                     let prevRight = null;

//                     for (const item of line.items) {
//                         if (prevRight !== null) {
//                             const gap          = item.x - prevRight;
//                             const avgCharWidth = item.width / (item.str.length || 1);
//                             if (gap > avgCharWidth * 0.3) lineText += ' ';
//                         }
//                         lineText  += item.str;
//                         prevRight  = item.x + item.width;
//                     }

//                     const trimmed = lineText.trim();
//                     if (trimmed) {
//                         if (/^\s*\d+\s*$/.test(trimmed)) continue;

//                         if (fullText === '') {
//                             fullText = trimmed;
//                         } else {
//                             const isEndOfSentence = /[.\!?:]$/.test(lastLineTrimmed);
//                             const isNewParagraph  = /^\s*([\d\.\-\*\(]+|[A-ZĐ])/u.test(trimmed);

//                             if (isEndOfSentence || isNewParagraph) {
//                                 fullText += '\n' + trimmed;
//                             } else {
//                                 fullText += ' ' + trimmed;
//                             }
//                         }
//                         lastLineTrimmed = trimmed;
//                     }
//                 }

//                 fullText += '\n';
//             }

//             fullText = fullText.trim();
//             if (!fullText) {
//                 throw new Error('PDF rỗng hoặc chỉ chứa hình ảnh, không đọc được text.');
//             }

//             return fullText;
//         },

//         async _uploadFileToServer(file) {
//             const formData = new FormData();
//             formData.append('file', file);

//             const uploadRes  = await fetch('/app/uploads/scans', { method: 'POST', body: formData });
//             const uploadData = await uploadRes.json();

//             if (!uploadRes.ok) {
//                 throw new Error(uploadData.message || `HTTP ${uploadRes.status}`);
//             }

//             if (!uploadData.url) {
//                 throw new Error('Server không trả về URL file.');
//             }

//             return uploadData.url;
//         },

//         clearFile() {
//             this.uploadFileName   = '';
//             this._uploadedFileUrl = null;
//             this._scanMode        = 'text';
//             this.params.content   = '';
//         },

//         // ============================================================
//         // COMPUTED
//         // ============================================================

//         get wordCount() {
//             // Nếu đang ở pdf-file mode, hiển thị tên file thay vì đếm từ
//             if (this._scanMode === 'pdf-file' && this._uploadedFileUrl) {
//                 return '—';
//             }
//             const text = this.params.content.trim();
//             return text ? text.split(/\s+/).length : 0;
//         },

//         get falseFacts() {
//             return (this.results?.facts ?? []).filter(f =>
//                 ['false', 'unverified'].includes((f.classification ?? '').toLowerCase())
//             );
//         },

//         // ============================================================
//         // RUN ANALYSIS
//         // ============================================================

//         async runAnalysis() {
//             this._loadConfig();

//             // Validate theo mode
//             if (this._scanMode === 'pdf-file') {
//                 // PDF file mode: chỉ cần file_url, không cần content
//                 if (!this._uploadedFileUrl) {
//                     return NeoUI.toast('Vui lòng upload file PDF trước.', 'warning');
//                 }
//             } else {
//                 // Text mode: cần content
//                 const wc = this.params.content.trim().split(/\s+/).filter(Boolean).length;
//                 if (wc < 10) {
//                     return NeoUI.toast('Vui lòng nhập ít nhất 10 từ.', 'warning');
//                 }
//             }

//             const anyCheck = this.checkList.some(c => this.params[c.key]);
//             if (!anyCheck) {
//                 return NeoUI.toast('Vui lòng bật ít nhất 1 loại kiểm tra.', 'warning');
//             }

//             this.isLoading = true;
//             this.scanId    = null;

//             if (this._scanMode === 'pdf-file') {
//                 this.loadingLabel = 'Đang đọc PDF và phân tích...';
//             } else {
//                 this.loadingLabel = 'Đang phân tích văn bản...';
//             }

//             try {
//                 let endpoint, payload;

//                 if (this._scanMode === 'pdf-file') {
//                     // ── PDF file mode: gửi file_url, backend tự upload lên OpenAI ──
//                     // Dùng endpoint /scan thống nhất, truyền file_url thay content
//                     endpoint = '/mock/originality/scan';
//                     payload  = {
//                         file_url:               this._uploadedFileUrl,
//                         content:                '',   // rỗng — backend bỏ qua khi có file_url hợp lệ
//                         title:                  this.params.title || this.uploadFileName || 'PDF Scan',
//                         aiModelVersion:         this.params.aiModelVersion,
//                         check_ai:               this.params.check_ai,
//                         check_plagiarism:       this.params.check_plagiarism,
//                         check_grammar:          this.params.check_grammar,
//                         check_readability:      this.params.check_readability,
//                         check_facts:            this.params.check_facts,
//                         check_contentOptimizer: this.params.check_contentOptimizer,
//                         optimizerQuery:         this.params.optimizerQuery,
//                     };
//                 } else {
//                     // ── Text mode: giữ nguyên như cũ ──
//                     endpoint = '/mock/originality/scan';
//                     payload  = {
//                         ...this.params,
//                         file_url: null,
//                     };
//                 }

//                 console.log('[DEBUG payload]', JSON.stringify({ ...payload, content: payload.content?.slice(0, 100) + '...' }, null, 2));

//                 const res  = await fetch(endpoint, {
//                     method:  'POST',
//                     headers: { 'Content-Type': 'application/json' },
//                     body:    JSON.stringify(payload),
//                 });

//                 const data = await res.json();

//                 if (!res.ok) {
//                     NeoUI.toast(data.message || `Lỗi server (${res.status})`, 'error');
//                     return;
//                 }

//                 // Normalize facts nếu là object thay vì array
//                 if (data.facts && !Array.isArray(data.facts)) {
//                     data.facts = Object.values(data.facts);
//                 }

//                 this.scanId     = data.scanId ?? null;
//                 this.results    = this._mapErrorsToBlocks(data);
//                 this.isAnalyzed = true;

//             } catch (e) {
//                 console.error('[AIChecker] runAnalysis error:', e);
//                 NeoUI.toast('Mất kết nối máy chủ. Vui lòng thử lại.', 'error');
//             } finally {
//                 this.isLoading = false;
//             }
//         },

//         editAgain() {
//             this.isAnalyzed = false;
//             this.results    = null;
//             this.scanId     = null;
//             this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
//         },

//         // ============================================================
//         // BLOCK MAPPING
//         // ============================================================

//         _mapErrorsToBlocks(data) {
//             let blocks = data.ai?.blocks ?? [];

//             if (blocks.length === 0 && this.params.content) {
//                 const sentences = this.params.content.match(/[^.!?\n]+[.!?\n]*/g) || [this.params.content];
//                 blocks = sentences.map(s => ({ text: s }));
//                 if (!data.ai) data.ai = {};
//                 data.ai.blocks = blocks;
//             }

//             blocks.forEach(b => {
//                 b.htmlText      = b.text;
//                 b.grammarErrors = [];
//                 b.highlights    = [];
//             });

//             const matches     = data.grammarSpelling?.matches ?? [];
//             const plagResults = data.plagiarism?.results      ?? [];

//             matches.forEach(match => {
//                 const sentence = (match.sentence || '').toLowerCase().trim();
//                 if (!sentence) return;

//                 const idx = blocks.findIndex(b => {
//                     const bText = (b.text || '').toLowerCase().trim();
//                     return bText.includes(sentence) || sentence.includes(bText) || bText === sentence;
//                 });

//                 if (idx !== -1) {
//                     blocks[idx].grammarErrors.push(match);

//                     let errWord = '';
//                     if (match.context && match.context.text && match.context.offset !== undefined) {
//                         errWord = match.context.text.substr(match.context.offset, match.length);
//                     }

//                     if (errWord && this.params.check_grammar) {
//                         blocks[idx].highlights.push(errWord);
//                     }
//                 }
//             });

//             blocks.forEach(b => {
//                 if (b.highlights && b.highlights.length > 0) {
//                     let uniqueWords = [...new Set(b.highlights)].sort((a, b) => b.length - a.length);

//                     const escapeRegExp = (string) => string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

//                     uniqueWords.forEach(w => {
//                         const regex = new RegExp(`(<[^>]+>)|(${escapeRegExp(w)})`, 'gi');
//                         b.htmlText = b.htmlText.replace(regex, (match, tag, word) => {
//                             if (tag)  return tag;
//                             if (word) {
//                                 return `<span class="text-danger fw-bold text-decoration-underline" style="text-decoration-style: wavy; background-color: rgba(220,53,69,0.1); padding: 0 2px; border-radius: 3px;">${word}</span>`;
//                             }
//                             return match;
//                         });
//                     });
//                 }
//             });

//             if (plagResults.length > 0 && blocks.length > 0) {
//                 let mappedCount = 0;
//                 plagResults.forEach(pr => {
//                     const phrase = (pr.phrase || pr.match || pr.text || '').toLowerCase().trim();
//                     if (!phrase) return;

//                     const idx = blocks.findIndex(b => {
//                         const bText      = (b.text || '').toLowerCase().replace(/\s+/g, ' ').trim();
//                         const cleanPhrase = phrase.replace(/\s+/g, ' ');
//                         return bText.includes(cleanPhrase.slice(0, 40)) || cleanPhrase.includes(bText.slice(0, 40));
//                     });

//                     if (idx !== -1 && !blocks[idx].plagiarism) {
//                         blocks[idx].plagiarism = pr;
//                         mappedCount++;
//                     }
//                 });

//                 if (mappedCount === 0 && this.params.check_plagiarism) {
//                     const target = blocks.length > 1 ? 1 : 0;
//                     blocks[target].plagiarism = plagResults[0];
//                 }
//             }

//             return data;
//         },

//         // ============================================================
//         // BLOCK STYLING
//         // ============================================================

//         getBlockClass(block) {
//             const classes = ['hl-block', 'p-1', 'rounded', 'd-inline', 'lh-lg'];

//             if (this.params.check_ai && block.result !== undefined) {
//                 const fake = block.result.fake ?? 0;
//                 if (fake > 0.8)      classes.push('bg-danger bg-opacity-25');
//                 else if (fake > 0.5) classes.push('bg-warning bg-opacity-25');
//                 else if (fake > 0.3) classes.push('bg-warning bg-opacity-10');
//                 else                 classes.push('bg-success bg-opacity-10');
//             }

//             if (block.plagiarism && this.params.check_plagiarism)
//                 classes.push('border-bottom border-3 border-warning fw-medium');

//             return classes.join(' ');
//         },

//         getTooltip(block) {
//             let html = '';

//             if (this.params.check_ai && block.result !== undefined) {
//                 const fake = ((block.result.fake ?? 0) * 100).toFixed(1);
//                 html += `<strong>AI: ${fake}%</strong>`;
//             }

//             if (block.grammarErrors && block.grammarErrors.length > 0 && this.params.check_grammar) {
//                 html += (html ? '<br>' : '') + `<span class="text-danger">✗ ${block.grammarErrors.length} lỗi chính tả/ngữ pháp</span>`;
//             }
//             if (block.plagiarism && this.params.check_plagiarism) {
//                 html += (html ? '<br>' : '') + `<span class="text-warning">⚠ Nghi ngờ đạo văn</span>`;
//             }
//             return html || 'Nội dung nguyên bản';
//         },

//         openDetailModal(block) {
//             if (!block.plagiarism && (!block.grammarErrors || block.grammarErrors.length === 0)) {
//                 NeoUI.toast('Câu này không có lỗi chi tiết.', 'info');
//                 return;
//             }
//             this.modalData = block;
//             this.$nextTick(() => {
//                 if (window.lucide) lucide.createIcons();
//                 const el = document.getElementById('detailModal');
//                 if (el) {
//                     (bootstrap.Modal.getInstance(el) ?? new bootstrap.Modal(el)).show();
//                 }
//             });
//         },

//         // ============================================================
//         // LAZY SCRIPT LOADER
//         // ============================================================

//         _loadScript(globalName, src) {
//             return new Promise((resolve, reject) => {
//                 if (window[globalName]) return resolve();
//                 const s   = document.createElement('script');
//                 s.src     = src;
//                 s.onload  = resolve;
//                 s.onerror = () => reject(new Error(`Không load được: ${src}`));
//                 document.head.appendChild(s);
//             });
//         },
//     };
// };

window.AIChecker = function () {
    return {
        STORAGE_KEY: 'aiCheckerConfig',

        params: {
            content:               '',
            title:                 'Bản quét',
            aiModelVersion:        'multilang',
            check_ai:               true,
            check_plagiarism:       true,
            check_grammar:          true,
            check_readability:      true,
            check_facts:            false,
            check_contentOptimizer: false,
            optimizerQuery:         '',
            file_url:               '',
        },

        isLoading:    false,
        isAnalyzed:   false,
        results:      null,
        modalData:    null,
        loadingLabel: 'Đang phân tích...',
        scanId:       null,

        isUploadLoading:  false,
        uploadFileName:   '',
        uploadProgress:   'Đang tải...',
        _uploadedFileUrl: null,
        _scanMode:        'text',
        
        // Cost preview
        costPreview:     null,   // { cost, balance, enough, wordCount, note }
        isPreviewLoading: false,
        _confirmModal:   null,

        checkList: [
            { key: 'check_ai',               label: 'Phát hiện AI',        icon: 'bot',          color: 'primary'   },
            { key: 'check_plagiarism',        label: 'Đạo văn',             icon: 'copy',         color: 'warning'   },
            { key: 'check_grammar',           label: 'Ngữ pháp & Chính tả', icon: 'spell-check',  color: 'danger'    },
            { key: 'check_readability',       label: 'Độ đọc hiểu',         icon: 'book-open',    color: 'info'      },
            { key: 'check_facts',             label: 'Kiểm chứng sự thật',  icon: 'shield-check', color: 'success'   },
            { key: 'check_contentOptimizer',  label: 'Tối ưu SEO',          icon: 'trending-up',  color: 'secondary' },
        ],

        // ============================================================
        // INIT
        // ============================================================
        init() {
            this._loadConfig();
            this.$watch('params', () => this._saveConfig(), { deep: true });
            window.addEventListener('ai-config-updated', () => { this._loadConfig(); });
            this.$watch('results', () => {
                this.$nextTick(() => {
                    if (window.lucide) lucide.createIcons();
                    setTimeout(() => {
                        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                            const instance = bootstrap.Tooltip.getInstance(el);
                            if (instance) instance.dispose();
                            new bootstrap.Tooltip(el, { html: true });
                        });
                    }, 200);
                });
            });
        },

        _loadConfig() {
            try {
                const saved = localStorage.getItem(this.STORAGE_KEY);
                if (!saved) return;
                const cfg  = JSON.parse(saved);
                const keys = ['check_ai','check_plagiarism','check_grammar','check_readability','check_facts','check_contentOptimizer','aiModelVersion'];
                keys.forEach(k => { if (k in cfg) this.params[k] = cfg[k]; });
            } catch (e) { console.warn('[AIChecker] Lỗi load config:', e); }
        },

        _saveConfig() {
            try {
                localStorage.setItem(this.STORAGE_KEY, JSON.stringify({
                    check_ai:               this.params.check_ai,
                    check_plagiarism:       this.params.check_plagiarism,
                    check_grammar:          this.params.check_grammar,
                    check_readability:      this.params.check_readability,
                    check_facts:            this.params.check_facts,
                    check_contentOptimizer: this.params.check_contentOptimizer,
                    aiModelVersion:         this.params.aiModelVersion,
                }));
            } catch (e) {}
        },

        // ============================================================
        // FILE HANDLING
        // ============================================================
        handleFileChange(event) {
            const file = event.target.files?.[0];
            if (!file) return;
            event.target.value = '';
        
            // ─── Giới hạn kích thước file ───────────────────────────
            const MAX_SIZE_MB = 20; // chỉnh số này theo nhu cầu
            const maxBytes = MAX_SIZE_MB * 1024 * 1024;
            if (file.size > maxBytes) {
                const sizeMB = (file.size / 1024 / 1024).toFixed(1);
                return NeoUI.toast(
                    `File quá lớn (${sizeMB}MB). Vui lòng chọn file dưới ${MAX_SIZE_MB}MB.`,
                    'warning'
                );
            }
            // ──────────────────────────────────────────────────────
        
            if (file.name.toLowerCase().endsWith('.docx')) {
                this._readDocx(file);
            } else if (file.name.toLowerCase().endsWith('.pdf') || file.type === 'application/pdf') {
                this._readPdf(file);
            } else {
                NeoUI.toast('Chỉ hỗ trợ file .docx hoặc .pdf', 'warning');
            }
        },

        async _readDocx(file) {
            this.isUploadLoading = true;
            this.uploadFileName  = file.name;
            this._scanMode       = 'text';
            this._uploadedFileUrl = null;
            try {
                await this._loadScript('mammoth','https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.6.0/mammoth.browser.min.js');
                const buf    = await file.arrayBuffer();
                const result = await mammoth.extractRawText({ arrayBuffer: buf });
                const text   = result.value?.trim() ?? '';
                if (!text) throw new Error('File rỗng hoặc không đọc được nội dung.');
                this.params.content = text;
                this.params.title   = file.name.replace(/\.docx$/i, '');
                NeoUI.toast(`Đã tải: ${file.name}`, 'success');
            } catch (err) {
                NeoUI.toast(err.message || 'Không thể đọc file .docx', 'error');
                this.clearFile();
            } finally {
                this.isUploadLoading = false;
            }
        },

        async _readPdf(file) {
            this.isUploadLoading = true;
            this.uploadProgress  = 'Đang tải PDF lên máy chủ...';
            this.uploadFileName  = file.name;
            this._scanMode       = 'text';
            this._uploadedFileUrl = null;
            this.params.content  = '';
            try {
                const uploadedUrl     = await this._uploadFileToServer(file);
                this._uploadedFileUrl = uploadedUrl;
                this._scanMode        = 'pdf-file';
                this.params.title     = file.name.replace(/\.pdf$/i, '');
                this.params.content   = '';
                NeoUI.toast(`Đã tải lên: ${file.name}`, 'success');
            } catch (err) {
                console.warn('[AIChecker] Upload PDF thất bại, fallback đọc text:', err.message);
                NeoUI.toast('Không upload được file, đang đọc nội dung trực tiếp...', 'warning');
                try {
                    const text = await this._extractPdfText(file);
                    this.params.content   = text;
                    this.params.title     = file.name.replace(/\.pdf$/i, '');
                    this._uploadedFileUrl = null;
                    this._scanMode        = 'text';
                    NeoUI.toast(`Đã đọc PDF: ${file.name}`, 'success');
                } catch (extractErr) {
                    NeoUI.toast(extractErr.message || 'Không thể đọc file PDF', 'error');
                    this.clearFile();
                }
            } finally {
                this.isUploadLoading = false;
            }
        },

        async _extractPdfText(file) {
            await this._loadScript('pdfjsLib','https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js');
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
            const buf = await file.arrayBuffer();
            const pdf = await pdfjsLib.getDocument({ data: buf }).promise;
            let fullText = '';
            for (let i = 1; i <= pdf.numPages; i++) {
                const page    = await pdf.getPage(i);
                const content = await page.getTextContent({ normalizeWhitespace: true });
                const vp      = page.getViewport({ scale: 1 });
                const items   = content.items.filter(it => it.str?.trim()).map(it => ({
                    str: it.str, x: it.transform[4],
                    y: Math.round(vp.height - it.transform[5]),
                    width: it.width, fontSz: Math.abs(it.transform[3]),
                }));
                if (!items.length) continue;
                const lines = [];
                for (const item of items) {
                    const tol  = (item.fontSz || 12) * 0.5;
                    const line = lines.find(l => Math.abs(l.y - item.y) <= tol);
                    if (line) line.items.push(item);
                    else lines.push({ y: item.y, items: [item] });
                }
                lines.sort((a, b) => a.y - b.y);
                let lastTrimmed = '';
                for (const line of lines) {
                    line.items.sort((a, b) => a.x - b.x);
                    let lineText = ''; let prevRight = null;
                    for (const item of line.items) {
                        if (prevRight !== null) {
                            const gap = item.x - prevRight;
                            const acw = item.width / (item.str.length || 1);
                            if (gap > acw * 0.3) lineText += ' ';
                        }
                        lineText += item.str;
                        prevRight = item.x + item.width;
                    }
                    const trimmed = lineText.trim();
                    if (!fullText) {
                        fullText = trimmed;
                    } else {
                        // BỘ LỌC TỪ VIẾT TẮT: Tránh ngắt dòng sai với "et al.", "e.g.", v.v.
                        const isAbbr = /\b(et al|e\.g|i\.e|etc|vs|fig|vol|ed|dr|prof|mr|mrs|ms|inc|ltd)\.$/i.test(lastTrimmed);
                        
                        const isEnd = /[.\!?:]$/.test(lastTrimmed) && !isAbbr;
                        const isNew = /^\s*([\d\.\-\*\(]+|[A-ZĐ])/u.test(trimmed);
                        
                        // Nếu dòng trước kết thúc bằng từ viết tắt (vd: "et al.") -> Bắt buộc nối bằng khoảng trắng
                        fullText += (isEnd || (isNew && !isAbbr)) ? '\n' + trimmed : ' ' + trimmed;
                    }
                    lastTrimmed = trimmed;
                }
                fullText += '\n';
            }
            fullText = fullText.trim();
            if (!fullText) throw new Error('PDF rỗng hoặc chỉ chứa hình ảnh.');
            return fullText;
        },

        async _uploadFileToServer(file) {
            const formData = new FormData();
            formData.append('file', file);
            const uploadRes  = await fetch('/app/uploads/scans', { method: 'POST', body: formData });
            const uploadData = await uploadRes.json();
            if (!uploadRes.ok) throw new Error(uploadData.message || `HTTP ${uploadRes.status}`);
            if (!uploadData.url) throw new Error('Server không trả về URL file.');
            return uploadData.url;
        },

        clearFile() {
            this.uploadFileName   = '';
            this._uploadedFileUrl = null;
            this._scanMode        = 'text';
            this.params.content   = '';
        },

        // ============================================================
        // COMPUTED
        // ============================================================
        get wordCount() {
            if (this._scanMode === 'pdf-file') return '—';
            const text = this.params.content.trim();
            return text ? text.split(/\s+/).length : 0;
        },

        /**
         * Facts: cấu trúc thực { fact, truthfulness: "85%", explanation, links: [] }
         * "Lỗi" = truthfulness < 70%
         */
        get falseFacts() {
            const facts = this.results?.facts ?? [];
            const arr   = Array.isArray(facts) ? facts : Object.values(facts);
            return arr.filter(f => {
                const pct = parseInt(f.truthfulness ?? '0', 10);
                return pct < 70;
            });
        },

        // Tiện ích: lấy màu badge theo truthfulness
        factBadgeClass(fact) {
            const pct = parseInt(fact.truthfulness ?? '0', 10);
            if (pct >= 70) return 'bg-success';
            if (pct >= 40) return 'bg-warning text-dark';
            return 'bg-danger';
        },

        // Lấy link an toàn từ plagiarism result item
        plagLink(src) {
            return src.link ?? src.url ?? '#';
        },
        plagTitle(src) {
            return src.title ?? src.name ?? src.link ?? src.url ?? 'Nguồn không rõ';
        },

        // ============================================================
        // RUN ANALYSIS
        // ============================================================
        async runAnalysis() {
            this._loadConfig();

            if (this._scanMode === 'pdf-file') {
                if (!this._uploadedFileUrl) return NeoUI.toast('Vui lòng upload file PDF trước.', 'warning');
            } else {
                const wc = this.params.content.trim().split(/\s+/).filter(Boolean).length;
                if (wc < 10) return NeoUI.toast('Vui lòng nhập ít nhất 10 từ.', 'warning');
            }

            if (!this.checkList.some(c => this.params[c.key])) {
                return NeoUI.toast('Vui lòng bật ít nhất 1 loại kiểm tra.', 'warning');
            }

            this.isLoading    = true;
            this.scanId       = null;
            this.plagiarismPending = false;  // ← THÊM
            this._plagPollTimer    = null;   // ← THÊM
            this.loadingLabel = this._scanMode === 'pdf-file' ? 'Đang đọc PDF và phân tích...' : 'Đang phân tích văn bản...';

            try {
                const payload = this._scanMode === 'pdf-file'
                    ? {
                        file_url:               this._uploadedFileUrl,
                        content:                '',
                        title:                  this.params.title || this.uploadFileName || 'PDF Scan',
                        aiModelVersion:         this.params.aiModelVersion,
                        check_ai:               this.params.check_ai,
                        check_plagiarism:       this.params.check_plagiarism,
                        check_grammar:          this.params.check_grammar,
                        check_readability:      this.params.check_readability,
                        check_facts:            this.params.check_facts,
                        check_contentOptimizer: this.params.check_contentOptimizer,
                        optimizerQuery:         this.params.optimizerQuery,
                    }
                    : { ...this.params, file_url: null };

                console.log('[DEBUG payload]', JSON.stringify(payload, null, 2));

                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 180000); // 3 phút
                
                const res = await fetch('/mock/originality/scan', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                    signal: controller.signal,
                });
                clearTimeout(timeoutId);
                
                const data = await res.json();

                if (!res.ok) {
                    NeoUI.toast(data.message || `Lỗi server (${res.status})`, 'error');
                    return;
                }

                // ── Normalize facts → đảm bảo đúng cấu trúc {fact, truthfulness, explanation, links:[]}
                if (data.facts) {
                    const arr = Array.isArray(data.facts) ? data.facts : Object.values(data.facts);
                    data.facts = arr.map(f => ({
                        fact:         f.fact         ?? f.claim ?? '',
                        truthfulness: f.truthfulness ?? '0%',
                        explanation:  f.explanation  ?? '',
                        links: Array.isArray(f.links)
                            ? f.links.filter(l => typeof l === 'string' && l.startsWith('http'))
                            : (Array.isArray(f.sources)
                                ? f.sources.map(s => s.url ?? s.link ?? '').filter(Boolean)
                                : []),
                    }));
                }

                // ── Normalize plagiarism → đảm bảo link/title luôn có giá trị
                if (data.plagiarism?.results) {
                    data.plagiarism.results = data.plagiarism.results.map(pr => ({
                        ...pr,
                        results: (pr.results ?? pr.sources ?? []).map(src => ({
                            link:   src.link  ?? src.url  ?? '',
                            title:  src.title ?? src.name ?? src.link ?? src.url ?? 'Nguồn không rõ',
                            scores: Array.isArray(src.scores) && src.scores.length
                                ? src.scores
                                : [{ score: 0.5, sentence: '' }],
                            timestamps: src.timestamps ?? {},
                        })),
                    }));
                }

                this.scanId     = data.scanId ?? null;
                this.results    = this._mapErrorsToBlocks(data);
                this.isAnalyzed = true;
                
                // Nếu server trả pending=true → bắt đầu poll
                if (data.plagiarism?.pending && this.scanId) {
                    this.plagiarismPending = true;
                    this._startPlagiarismPoll(this.scanId);
                }
                
                // ✅ THÊM VÀO ĐÂY — cập nhật số dư ví trên header
                if (data.newBalance !== undefined) {
                    document.querySelectorAll('.user-point-display')
                        .forEach(el => el.textContent = Number(data.newBalance).toLocaleString('vi-VN'));
                }

            } catch (e) {
                console.error('[AIChecker] runAnalysis error:', e);
                NeoUI.toast('Mất kết nối máy chủ. Vui lòng thử lại.', 'error');
            } finally {
                this.isLoading = false;
            }
        },

        // ============================================================
        // PLAGIARISM POLLING — chờ Copyleaks webhook hoàn thành
        // ============================================================
        _startPlagiarismPoll(scanId) {
            let attempts = 0;
            const maxAttempts = 30; // 30 × 4s = 2 phút
        
            this._plagPollTimer = setInterval(async () => {
                attempts++;
                try {
                    const res  = await fetch(`/mock/originality/scan/${scanId}/plagiarism`);
                    const data = await res.json();
        
                    if (!data.pending) {
                        clearInterval(this._plagPollTimer);
                        this._plagPollTimer    = null;
                        this.plagiarismPending = false;
        
                        // Cập nhật kết quả plagiarism vào results hiện tại
                        if (this.results) {
                            this.results.plagiarism = data.plagiarism;
                            // Re-map để gán plagiarism vào đúng block
                            this.results = this._mapErrorsToBlocks(this.results);
                        }
                        NeoUI.toast('Kết quả kiểm tra đạo văn đã cập nhật!', 'success');
                    }
                } catch (e) {
                    console.warn('[PlagPoll] lỗi:', e);
                }
        
                if (attempts >= maxAttempts) {
                    clearInterval(this._plagPollTimer);
                    this._plagPollTimer    = null;
                    this.plagiarismPending = false;
                    NeoUI.toast('Kiểm tra đạo văn mất nhiều thời gian hơn dự kiến.', 'warning');
                }
            }, 4000);
        },
        
        editAgain() {
            this.isAnalyzed = false;
            this.results    = null;
            this.scanId     = null;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },
        
        // Bước 1: tính chi phí và hiện modal xác nhận
        async previewCost() {
            if (this._scanMode === 'pdf-file') {
                if (!this._uploadedFileUrl) return NeoUI.toast('Vui lòng upload file PDF trước.', 'warning');
            } else {
                const wc = this.params.content.trim().split(/\s+/).filter(Boolean).length;
                if (wc < 10) return NeoUI.toast('Vui lòng nhập ít nhất 10 từ.', 'warning');
            }
            if (!this.checkList.some(c => this.params[c.key])) {
                return NeoUI.toast('Vui lòng bật ít nhất 1 loại kiểm tra.', 'warning');
            }
        
            this.isPreviewLoading = true;
            try {
                const body = this._scanMode === 'pdf-file'
                    ? { ...this.params, file_url: this._uploadedFileUrl, content: '' }
                    : { ...this.params, content: this.params.content, file_url: null };
        
                const res  = await fetch('/mock/originality/cost', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body),
                });
                const data = await res.json();
                if (!res.ok) return NeoUI.toast(data.message || 'Lỗi tính chi phí.', 'error');
        
                this.costPreview = data;
                // Hiện modal
                if (!this._confirmModal) {
                    this._confirmModal = new bootstrap.Modal(
                        document.getElementById('costConfirmModal')
                    );
                }
                this._confirmModal.show();
            } catch (e) {
                NeoUI.toast('Không kết nối được máy chủ.', 'error');
            } finally {
                this.isPreviewLoading = false;
            }
        },
        
        // Bước 2: user bấm xác nhận trong modal → chạy scan thật
        async confirmAndRun() {
            if (this._confirmModal) this._confirmModal.hide();
            await this.runAnalysis();
        },

        // ============================================================
        // BLOCK MAPPING
        // ============================================================
        _mapErrorsToBlocks(data) {
            let blocks = data.ai?.blocks ?? [];

            // 1. Tự động tạo blocks nếu AI model không trả về (Dùng sourceText)
            const sourceText = this.params.content || data.properties?.content || '';
            if (blocks.length === 0 && sourceText) {
                const sentences = sourceText.match(/[^.!?\n]+[.!?\n]*/g) || [sourceText];
                blocks = sentences.map(s => ({ text: s }));
                if (!data.ai) data.ai = {};
                data.ai.blocks = blocks;
            }

            blocks.forEach(b => { b.htmlText = b.text; b.grammarErrors = []; b.highlights = []; });

            const matches     = data.grammarSpelling?.matches ?? [];
            const plagResults = data.plagiarism?.results      ?? [];

            // 2. MAPPING NGỮ PHÁP (Thuật toán linh hoạt)
            matches.forEach(match => {
                const sentence = (match.sentence || '').toLowerCase().replace(/\s+/g, ' ').trim();
                
                // ĐÃ SỬA: Lấy trực tiếp error_text từ GPT
                const errWordRaw = match.error_text || '';
                const errWord = errWordRaw.toLowerCase().trim();
                
                if (!errWord) return;

                // Tìm block chứa từ sai
                let idx = blocks.findIndex(b => {
                    const bText = (b.text || '').toLowerCase().replace(/\s+/g, ' ').trim();
                    
                    // Phải chứa từ bị sai
                    if (!bText.includes(errWord)) return false; 
                    
                    // So khớp linh hoạt: Câu của GPT nằm trong block, hoặc ngược lại
                    if (sentence && (bText.includes(sentence) || sentence.includes(bText))) return true;
                    
                    // Fallback: Chấp nhận nếu block chứa từ sai (khi câu bị ngắt)
                    return true;
                });

                if (idx !== -1) {
                    blocks[idx].grammarErrors.push(match);
                    if (this.params.check_grammar && errWordRaw) {
                        blocks[idx].highlights.push(errWordRaw);
                    }
                }
            });

            // 3. RENDER BÔI ĐỎ LÊN HTML
            blocks.forEach(b => {
                if (!b.highlights?.length) return;
                
                // Tránh lỗi regex đè nhau bằng cách xếp từ dài xuống ngắn
                const uniqueWords = [...new Set(b.highlights)].sort((a, b) => b.length - a.length);
                const esc = s => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                
                uniqueWords.forEach(w => {
                    const regex = new RegExp(`(<[^>]+>)|(${esc(w)})`, 'gi');
                    b.htmlText = b.htmlText.replace(regex, (m, tag, word) => {
                        if (tag)  return tag;
                        // Gạch chân lượn sóng màu đỏ
                        if (word) return `<span class="text-danger fw-bold" style="text-decoration: underline wavy; background:rgba(220,53,69,0.1);padding:0 2px;border-radius:3px;cursor:pointer;">${word}</span>`;
                        return m;
                    });
                });
            });

            // 4. MAPPING ĐẠO VĂN
            if (plagResults.length > 0 && blocks.length > 0) {
                let mapped = 0;
                plagResults.forEach(pr => {
                    const phrase = (pr.phrase || '').toLowerCase().trim();
                    if (!phrase) return;
                    const idx = blocks.findIndex(b => {
                        const bText = (b.text || '').toLowerCase().replace(/\s+/g, ' ').trim();
                        const cp    = phrase.replace(/\s+/g, ' ');
                        return bText.includes(cp.slice(0, 40)) || cp.includes(bText.slice(0, 40));
                    });
                    if (idx !== -1 && !blocks[idx].plagiarism) { 
                        blocks[idx].plagiarism = pr; 
                        mapped++; 
                    }
                });
                
                if (mapped === 0 && this.params.check_plagiarism) {
                    blocks[blocks.length > 1 ? 1 : 0].plagiarism = plagResults[0];
                }
            }

            return data;
        },

        // ============================================================
        // BLOCK STYLING
        // ============================================================
        getBlockClass(block) {
            const classes = ['hl-block', 'p-1', 'rounded', 'd-inline', 'lh-lg'];
            if (this.params.check_ai && block.result !== undefined) {
                const fake = block.result.fake ?? 0;
                if (fake > 0.8)      classes.push('bg-danger bg-opacity-25');
                else if (fake > 0.5) classes.push('bg-warning bg-opacity-25');
                else if (fake > 0.3) classes.push('bg-warning bg-opacity-10');
                else                 classes.push('bg-success bg-opacity-10');
            }
            if (block.plagiarism && this.params.check_plagiarism)
                classes.push('border-bottom border-3 border-warning fw-medium');
            return classes.join(' ');
        },

        getTooltip(block) {
            let html = '';
            if (this.params.check_ai && block.result !== undefined) {
                const fake = ((block.result.fake ?? 0) * 100).toFixed(1);
                html += `<strong>AI: ${fake}%</strong>`;
            }
            if (block.grammarErrors?.length && this.params.check_grammar)
                html += (html ? '<br>' : '') + `<span class="text-danger">✗ ${block.grammarErrors.length} lỗi ngữ pháp</span>`;
            if (block.plagiarism && this.params.check_plagiarism)
                html += (html ? '<br>' : '') + `<span class="text-warning">⚠ Nghi ngờ đạo văn</span>`;
            return html || 'Nội dung nguyên bản';
        },

        openDetailModal(block) {
            if (!block.plagiarism && !block.grammarErrors?.length) {
                NeoUI.toast('Câu này không có lỗi chi tiết.', 'info');
                return;
            }
            this.modalData = block;
            this.$nextTick(() => {
                if (window.lucide) lucide.createIcons();
                const el = document.getElementById('detailModal');
                if (el) (bootstrap.Modal.getInstance(el) ?? new bootstrap.Modal(el)).show();
            });
        },

        // ============================================================
        // LAZY SCRIPT LOADER
        // ============================================================
        _loadScript(globalName, src) {
            return new Promise((resolve, reject) => {
                if (window[globalName]) return resolve();
                const s = document.createElement('script');
                s.src = src;
                s.onload  = resolve;
                s.onerror = () => reject(new Error(`Không load được: ${src}`));
                document.head.appendChild(s);
            });
        },
    };
};

window.AIConfig = function () {
    return {
        STORAGE_KEY: 'aiCheckerConfig',
        config: {
            check_ai:               true,
            check_plagiarism:       true,
            check_grammar:          true,
            check_readability:      true,
            check_facts:            false,
            check_contentOptimizer: false,
            aiModelVersion:         'multilang',
        },
        init() {
            try {
                const saved = localStorage.getItem(this.STORAGE_KEY);
                if (saved) Object.assign(this.config, JSON.parse(saved));
            } catch (e) {}
            this.$watch('config', val => {
                localStorage.setItem(this.STORAGE_KEY, JSON.stringify(val));
                window.dispatchEvent(new CustomEvent('ai-config-updated'));
            }, { deep: true });
        },
        toggle(key) { this.config[key] = !this.config[key]; },
        isChecked(key) { return this.config[key] === true; },
    };
};

window.AIConfig = function () {
    return {
        STORAGE_KEY: 'aiCheckerConfig',

        config: {
            check_ai:               true,
            check_plagiarism:       true,
            check_grammar:          true,
            check_readability:      true,
            check_facts:            false,
            check_contentOptimizer: false,
            aiModelVersion:         'multilang',
        },

        init() {
            try {
                const saved = localStorage.getItem(this.STORAGE_KEY);
                if (saved) Object.assign(this.config, JSON.parse(saved));
            } catch (e) { /* ignore */ }

            this.$watch('config', val => {
                localStorage.setItem(this.STORAGE_KEY, JSON.stringify(val));
                window.dispatchEvent(new CustomEvent('ai-config-updated'));
            }, { deep: true });
        },

        toggle(key) {
            this.config[key] = !this.config[key];
        },

        isChecked(key) {
            return this.config[key] === true;
        },
    };
};



async function fetchUI(url) {
    try {
        const r = await fetch(url, { headers: { 'HX-Request': 'true' } });
        NeoUI.open(await r.text());
    } catch (e) { console.error(e); }
}

/**
 * ALPINE COMPONENTS
 */
window.Form = function() {
    return {
        errorMessage: null, isLoading: false,
        startRequest() { this.errorMessage = null; this.isLoading = true; },
        handleResponse(event) {
            this.isLoading = false;
            try {
                const data = JSON.parse(event.detail.xhr.responseText);
                if (data.status === 'error' && !data.alert && !data.redirect && !data.toast) {
                    this.errorMessage = data.content;
                }
            } catch (e) { }
        },
        handleError() { this.isLoading = false; NeoUI.toast('Lỗi kết nối máy chủ', 'error'); }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) lucide.createIcons();

    NapTien.init();

    document.body.addEventListener('htmx:beforeSwap', (e) => {
        const xhr = e.detail.xhr;
        const contentType = xhr.getResponseHeader('Content-Type') || '';
        const text = xhr.responseText.trim();

        if (contentType.includes('application/json') || text.startsWith('{')) {
            e.detail.shouldSwap = false;
            try {
                const data = JSON.parse(text);
                NeoUI.processResponse(data);
            } catch (err) {}
        }
    });

    document.body.addEventListener('htmx:afterRequest', (e) => {
        const xhr = e.detail.xhr;
        if (xhr.status >= 200 && xhr.status < 300) {
            const contentType = xhr.getResponseHeader('Content-Type') || '';
            const text = xhr.responseText.trim();

            if (!contentType.includes('application/json') && !text.startsWith('{')) {
                if (text.includes('class="modal') || text.includes('class="offcanvas') || 
                    text.includes("class='modal") || text.includes("class='offcanvas")) {
                    NeoUI.open(text);
                }
            }
        }
    });

    document.body.addEventListener('htmx:afterSwap', () => {
        NapTien.init();
    });

    document.body.addEventListener('htmx:responseError', function(evt) {
        const xhr = evt.detail.xhr;
        const status = xhr.status;
        if (status === 404) {
            NeoUI.toast('Không tìm thấy dữ liệu hoặc trang yêu cầu!', 'error');
        } else if (status === 500) {
            NeoUI.toast('Lỗi hệ thống! Vui lòng thử lại sau.', 'error');
        } else if (status === 403) {
            NeoUI.toast('Bạn không có quyền thực hiện hành động này.', 'warning');
        }
    });

    document.body.addEventListener('htmx:load', function() {
        if (window.lucide) lucide.createIcons();
    });
});