
function loadMammoth() {
    return new Promise((resolve, reject) => {
        if (window.mammoth) return resolve();
        const script    = document.createElement('script');
        script.src      = 'https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.6.0/mammoth.browser.min.js';
        script.onload   = resolve;
        script.onerror  = () => reject(new Error('Không load được mammoth.js'));
        document.head.appendChild(script);
    });
}

// window.AIChecker = function () {
//     return {
//         STORAGE_KEY: 'aiConfig',

//         params: {
//             content: '',
//             title: 'Bản quét',
//             aiModelVersion: 'turbo',
//             check_ai: true,
//             check_plagiarism: true,
//             check_facts: false,
//             check_grammar: true,
//             check_contentOptimizer: false,
//             check_readability: false,
//         },

//         isLoading: false,
//         isAnalyzed: false,
//         results: null,
//         modalData: null,
//         isUploadLoading: false,
//         uploadFileName: '',
//         _uploadedFileUrl: null,
//         _scanMode: 'text',

//         // ==================== LOAD & SAVE CONFIG ====================
//         init() {
//             this._loadConfig();
//             this.$watch('params', () => this._saveConfig(), { deep: true });
//         },

//         _loadConfig() {
//             try {
//                 const saved = localStorage.getItem(this.STORAGE_KEY);
//                 if (saved) {
//                     const config = JSON.parse(saved);
//                     this.params.check_ai = config.check_ai ?? true;
//                     this.params.check_plagiarism = config.check_plagiarism ?? true;
//                     this.params.check_grammar = config.check_grammar ?? true;
//                     this.params.check_readability = config.check_readability ?? false;
//                     this.params.check_facts = config.check_facts ?? false;
//                     this.params.check_contentOptimizer = config.check_contentOptimizer ?? false;
//                     this.params.aiModelVersion = config.aiModelVersion ?? 'turbo';
//                 }
//             } catch (e) {
//                 console.warn('Load aiConfig error', e);
//             }
//         },

//         _saveConfig() {
//             try {
//                 const saveData = {
//                     check_ai: this.params.check_ai,
//                     check_plagiarism: this.params.check_plagiarism,
//                     check_grammar: this.params.check_grammar,
//                     check_readability: this.params.check_readability,
//                     check_facts: this.params.check_facts,
//                     check_contentOptimizer: this.params.check_contentOptimizer,
//                     aiModelVersion: this.params.aiModelVersion,
//                 };
//                 localStorage.setItem(this.STORAGE_KEY, JSON.stringify(saveData));
//             } catch (e) {
//                 console.warn('Save aiConfig error', e);
//             }
//         },

//         // Toggle dùng chung cho menu
//         toggleCheck(key) {
//             if (key in this.params) {
//                 this.params[key] = !this.params[key];
//             }
//         },

//         // ==================== FILE HANDLING ====================
//         handleFileChange(event) {
//             const file = event.target.files?.[0];
//             if (!file) return;
//             const ext = file.name.toLowerCase();
//             if (ext.endsWith('.docx')) {
//                 this.readDocxFile(file);
//             } else if (ext.endsWith('.pdf')) {
//                 this.uploadPdfFile(file);
//             } else {
//                 NeoUI.toast('Vui lòng chọn file .docx hoặc .pdf', 'warning');
//             }
//             event.target.value = '';
//         },

//         async readDocxFile(file) {
//             this.isUploadLoading = true;
//             this.uploadFileName = file.name;
//             this._scanMode = 'text';
//             this._uploadedFileUrl = null;
//             try {
//                 await this._loadMammoth();
//                 const arrayBuffer = await file.arrayBuffer();
//                 const result = await mammoth.extractRawText({ arrayBuffer });
//                 const text = result.value?.trim();
//                 if (!text) {
//                     NeoUI.toast('File rỗng hoặc không đọc được', 'error');
//                     this.uploadFileName = '';
//                     return;
//                 }
//                 this.params.content = text;
//                 NeoUI.toast(`Đã tải: ${file.name}`, 'success');
//             } catch (err) {
//                 console.error('Lỗi đọc .docx:', err);
//                 NeoUI.toast('Không thể đọc file, vui lòng thử lại', 'error');
//                 this.uploadFileName = '';
//             } finally {
//                 this.isUploadLoading = false;
//             }
//         },

//         async uploadPdfFile(file) {
//             this.isUploadLoading = true;
//             this.uploadFileName = file.name;
//             this._scanMode = 'url';
//             this._uploadedFileUrl = null;
//             this.params.content = '';
//             try {
//                 const formData = new FormData();
//                 formData.append('file', file);
//                 const res = await fetch('/mock/originality/upload-pdf', {
//                     method: 'POST',
//                     body: formData,
//                 });
//                 const data = await res.json();
//                 if (!res.ok || data.status !== 'ok') {
//                     throw new Error(data.message || 'Upload thất bại');
//                 }
//                 this._uploadedFileUrl = data.url;
//                 NeoUI.toast(`Đã tải PDF lên máy chủ: ${file.name}`, 'success');
//             } catch (err) {
//                 console.error('Lỗi upload PDF:', err);
//                 NeoUI.toast(err.message || 'Không thể upload file PDF', 'error');
//                 this.uploadFileName = '';
//                 this._scanMode = 'text';
//             } finally {
//                 this.isUploadLoading = false;
//             }
//         },

//         clearFile() {
//             this.params.content = '';
//             this.uploadFileName = '';
//             this._uploadedFileUrl = null;
//             this._scanMode = 'text';
//         },

//         _loadMammoth() {
//             return new Promise((resolve, reject) => {
//                 if (window.mammoth) return resolve();
//                 const s = document.createElement('script');
//                 s.src = 'https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.6.0/mammoth.browser.min.js';
//                 s.onload = resolve;
//                 s.onerror = () => reject(new Error('Không load được mammoth.js'));
//                 document.head.appendChild(s);
//             });
//         },

//         get wordCount() {
//             return this.params.content.trim()
//                 ? this.params.content.trim().split(/\s+/).length
//                 : 0;
//         },

//         // ==================== RUN ANALYSIS ====================
//         async runAnalysis() {
//             if (this._scanMode === 'url' && this._uploadedFileUrl) {
//                 // OK
//             } else if (this.wordCount < 10) {
//                 return NeoUI.toast('Vui lòng nhập ít nhất 10 từ!', 'warning');
//             }

//             this.isLoading = true;
//             try {
//                 const payload = {
//                     title: this.params.title || this.uploadFileName || 'Bản quét',
//                     aiModelVersion: this.params.aiModelVersion,
//                     check_ai: this.params.check_ai,
//                     check_plagiarism: this.params.check_plagiarism,
//                     check_readability: this.params.check_readability,
//                     check_grammar: this.params.check_grammar,
//                     check_facts: this.params.check_facts,
//                     check_contentOptimizer: this.params.check_contentOptimizer,
//                 };

//                 let response;
//                 if (this._scanMode === 'url' && this._uploadedFileUrl) {
//                     payload.url = this._uploadedFileUrl;
//                     response = await fetch('/mock/originality/scan-url', {
//                         method: 'POST',
//                         headers: { 'Content-Type': 'application/json' },
//                         body: JSON.stringify(payload)
//                     });
//                 } else {
//                     payload.content = this.params.content;
//                     response = await fetch('/mock/originality/scan', {
//                         method: 'POST',
//                         headers: { 'Content-Type': 'application/json' },
//                         body: JSON.stringify(payload)
//                     });
//                 }

//                 const data = await response.json();
//                 if (response.ok) {
//                     if (data.facts && !Array.isArray(data.facts)) {
//                         data.facts = Object.values(data.facts);
//                     }
//                     this.results = this.forceMapErrors(data);
//                     this.isAnalyzed = true;
//                     this.$nextTick(() => {
//                         lucide.createIcons();
//                         [...document.querySelectorAll('[data-bs-toggle="tooltip"]')]
//                             .map(el => new bootstrap.Tooltip(el));
//                     });
//                 } else {
//                     NeoUI.toast(data.message || 'Lỗi không xác định', 'error');
//                 }
//             } catch (e) {
//                 console.error(e);
//                 NeoUI.toast('Lỗi kết nối hoặc server', 'error');
//             } finally {
//                 this.isLoading = false;
//             }
//         },

//         editAgain() {
//             this.isAnalyzed = false;
//             this.$nextTick(() => lucide.createIcons());
//         },

//         forceMapErrors(data) {
//             if (data.ai?.blocks?.length > 0) {
//                 if (this.params.check_grammar && data.grammarSpelling?.matches) {
//                     data.ai.blocks[0].grammar = data.grammarSpelling.matches[0];
//                 }
//                 if (this.params.check_plagiarism && data.plagiarism?.results) {
//                     const target = data.ai.blocks.length > 1 ? 1 : 0;
//                     data.ai.blocks[target].plagiarism = data.plagiarism.results[0];
//                 }
//             }
//             return data;
//         },

//         getBlockClass(block) {
//             let classes = ['hl-block', 'p-1', 'mx-0.5', 'rounded'];
//             if (block.result.fake > 0.8) classes.push('bg-ai-high');
//             else if (block.result.fake > 0.4) classes.push('bg-ai-med');
//             else classes.push('bg-ai-low');
//             if (block.plagiarism && this.params.check_plagiarism) classes.push('err-plagiarism');
//             if (block.grammar && this.params.check_grammar) classes.push('err-grammar');
//             return classes.join(' ');
//         },

//         getTooltip(block) {
//             let html = `<strong>Điểm AI: ${(block.result.fake * 100).toFixed(1)}%</strong>`;
//             if (block.grammar && this.params.check_grammar) html += `<br><span class="text-danger">Lỗi: Sai chính tả</span>`;
//             if (block.plagiarism && this.params.check_plagiarism) html += `<br><span class="text-warning">Lỗi: Đạo văn</span>`;
//             return html;
//         },

//         openDetailModal(block) {
//             if (!block.plagiarism && !block.grammar)
//                 return NeoUI.toast('Câu này không có lỗi chi tiết.', 'info');
//             this.modalData = block;
//             new bootstrap.Modal(document.getElementById('detailModal')).show();
//             this.$nextTick(() => lucide.createIcons());
//         },
//     };
// };

// window.AIConfig = function() {
//     return {
//         config: {
//             check_ai: true,
//             check_plagiarism: true,
//             check_grammar: true,
//             check_readability: false,
//             check_facts: false,
//             check_contentOptimizer: false
//         },

//         init() {
//             const saved = localStorage.getItem('aiConfig');
//             if (saved) {
//                 try {
//                     Object.assign(this.config, JSON.parse(saved));
//                 } catch(e){}
//             }
//             this.$watch('config', (val) => {
//                 localStorage.setItem('aiConfig', JSON.stringify(val));
//             }, { deep: true });
//         },

//         toggle(key) {
//             this.config[key] = !this.config[key];
//         },

//         isChecked(key) {
//             return this.config[key] === true;
//         }
//     }
// };