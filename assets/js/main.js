// QWQ Texture Packs - Main JavaScript

document.addEventListener('DOMContentLoaded', () => {
    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 50);
        });
    }

    // Mobile nav toggle
    const toggleBtn = document.querySelector('.navbar-toggle');
    const navLinks = document.querySelector('.navbar-links');
    if (toggleBtn && navLinks) {
        toggleBtn.addEventListener('click', () => {
            navLinks.classList.toggle('open');
        });
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', (e) => {
            const target = document.querySelector(anchor.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Lightbox
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    if (lightbox && lightboxImg) {
        document.querySelectorAll('.gallery-item img').forEach(img => {
            img.addEventListener('click', () => {
                lightboxImg.src = img.src;
                lightbox.classList.add('active');
            });
        });

        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox || e.target === lightboxImg) {
                lightbox.classList.remove('active');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') lightbox.classList.remove('active');
        });
    }

    // Toast notifications
    window.showToast = (message, type = 'success') => {
        const existing = document.querySelector('.toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);

        requestAnimationFrame(() => toast.classList.add('show'));

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    };

    // File upload preview
    document.querySelectorAll('.file-upload-area input[type="file"]').forEach(input => {
        input.addEventListener('change', () => {
            const preview = input.closest('.file-upload-area').nextElementSibling;
            if (!preview || !preview.classList.contains('preview-list')) return;

            preview.innerHTML = '';
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    if (file.type.startsWith('video/')) {
                        div.innerHTML = `<video src="${e.target.result}"></video>`;
                    } else {
                        div.innerHTML = `<img src="${e.target.result}" alt="preview">`;
                    }
                    div.innerHTML += `<button type="button" class="remove-btn" onclick="this.parentElement.remove()">✕</button>`;
                    preview.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        });
    });

    // Form validation
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', (e) => {
            const required = form.querySelectorAll('[required]');
            let valid = true;
            required.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#ef4444';
                    valid = false;
                } else {
                    field.style.borderColor = '';
                }
            });
            if (!valid) {
                e.preventDefault();
                showToast('请填写所有必填字段', 'error');
            }
        });
    });

    // Batch file upload handler (images/videos) with progress modal
    let currentUploadXhr = null;
    let batchCancelled = false;

    document.querySelectorAll('.upload-input').forEach(input => {
        input.addEventListener('change', async (e) => {
            const files = Array.from(e.target.files);
            if (files.length === 0) return;

            const targetId = input.dataset.target;
            const textarea = document.getElementById(targetId);
            if (!textarea) return;

            const previewContainer = document.getElementById(targetId + '-preview');
            const modal = document.getElementById('upload-modal');
            const filenameEl = document.getElementById('upload-filename');
            const batchInfo = document.getElementById('upload-batch-info');
            const progressFill = document.getElementById('upload-progress-fill');
            const progressText = document.getElementById('upload-progress-text');
            const cancelBtn = document.getElementById('upload-cancel-btn');

            batchCancelled = false;
            let uploadedCount = 0;
            const total = files.length;

            // 1. 显示本地预览（所有文件）
            const localItems = [];
            if (previewContainer) {
                files.forEach(file => {
                    const localItem = document.createElement('div');
                    localItem.id = 'local-preview-' + Date.now() + '-' + Math.random().toString(36).slice(2, 6);
                    localItem.style.cssText = 'position:relative;width:100px;height:70px;border-radius:8px;overflow:hidden;border:1px solid rgba(255,255,255,0.1);background:#000;flex-shrink:0;';
                    localItem.style.animation = 'pulse 1.5s ease-in-out infinite';
                    
                    const reader = new FileReader();
                    reader.onload = (ev) => {
                        if (file.type.startsWith('video/')) {
                            localItem.innerHTML = '<video src="' + ev.target.result + '" style="width:100%;height:100%;object-fit:cover;"></video>';
                        } else {
                            localItem.innerHTML = '<img src="' + ev.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
                        }
                    };
                    reader.readAsDataURL(file);
                    previewContainer.prepend(localItem);
                    localItems.push(localItem);
                });
            }

            // 2. 显示进度弹窗
            if (modal) {
                modal.classList.add('open');
                filenameEl.textContent = '第 1/' + total + ' 个文件';
                if (batchInfo) batchInfo.textContent = '准备上传 ' + total + ' 个文件...';
                progressFill.style.width = '0%';
                progressText.textContent = '0%';
            }

            // 3. 依次上传每个文件
            const uploadNext = (index) => {
                if (batchCancelled) return;

                const file = files[index];
                if (!file) {
                    // 全部完成
                    modal.classList.remove('open');
                    // 移除所有本地预览
                    localItems.forEach(el => {
                        if (el.parentNode) el.parentNode.removeChild(el);
                    });
                    // 刷新预览
                    if (typeof previewMedia === 'function') {
                        previewMedia(targetId, textarea.value);
                    }
                    showToast('全部上传完成 (' + total + '/' + total + ')', 'success');
                    currentUploadXhr = null;
                    return;
                }

                // 更新弹窗信息
                filenameEl.textContent = file.name;
                if (batchInfo) batchInfo.textContent = '正在上传第 ' + (index + 1) + '/' + total + ' 个文件';
                progressFill.style.width = '0%';
                progressText.textContent = '0%';

                // 创建 XHR
                const xhr = new XMLHttpRequest();
                currentUploadXhr = xhr;

                // 取消按钮
                cancelBtn.onclick = () => {
                    batchCancelled = true;
                    xhr.abort();
                    modal.classList.remove('open');
                    localItems.forEach(el => {
                        if (el.parentNode) el.parentNode.removeChild(el);
                    });
                    // 已成功上传的仍然保留
                    if (typeof previewMedia === 'function') {
                        previewMedia(targetId, textarea.value);
                    }
                    showToast('已取消上传 (' + uploadedCount + '/' + total + ' 个完成)', 'error');
                    currentUploadXhr = null;
                };

                xhr.upload.onprogress = (ev) => {
                    if (ev.lengthComputable) {
                        const pct = Math.round((ev.loaded / ev.total) * 100);
                        // 全局进度 = 已完成的 + 当前的
                        const overall = Math.round((uploadedCount / total) * 100 + (pct / total));
                        if (progressFill) progressFill.style.width = Math.min(overall, 100) + '%';
                        if (progressText) progressText.textContent = Math.min(overall, 100) + '%';
                    }
                };

                xhr.onload = () => {
                    if (batchCancelled) return;
                    try {
                        const result = JSON.parse(xhr.responseText);
                        if (result.success) {
                            uploadedCount++;
                            const url = result.data.url;
                            if (textarea.value.trim()) {
                                textarea.value += '\n' + url;
                            } else {
                                textarea.value = url;
                            }
                            // 将这个文件的本地预览标记为完成
                            if (localItems[index]) {
                                localItems[index].style.animation = 'none';
                                localItems[index].style.borderColor = 'rgba(74, 222, 128, 0.4)';
                            }
                            // 上传下一个
                            uploadNext(index + 1);
                        } else {
                            showToast('上传失败: ' + (result.message || file.name), 'error');
                            // 继续上传下一个
                            uploadNext(index + 1);
                        }
                    } catch (e) {
                        showToast('上传失败: ' + (file.name || '格式错误'), 'error');
                        uploadNext(index + 1);
                    }
                };

                xhr.onerror = () => {
                    if (batchCancelled) return;
                    showToast('上传失败: ' + (file.name || '网络错误'), 'error');
                    uploadNext(index + 1);
                };

                xhr.onabort = () => {
                    currentUploadXhr = null;
                };

                const formData = new FormData();
                formData.append('file', file);
                xhr.open('POST', '/api/upload.php', true);
                xhr.send(formData);
            };

            // 开始上传第一个
            uploadNext(0);

            input.value = '';
        });
    });

    // 初始化预览（编辑页面）
    setTimeout(() => {
        document.querySelectorAll('.upload-preview').forEach(preview => {
            const id = preview.id.replace('-preview', '');
            const textarea = document.getElementById(id);
            if (textarea && typeof previewMedia === 'function') {
                previewMedia(id, textarea.value);
            }
        });
    }, 100);
});

// 预览函数（全局，供 oninput 调用）
function previewMedia(id, value) {
    const container = document.getElementById(id + '-preview');
    if (!container) return;
    container.innerHTML = '';
    const urls = value.split('\n').map(s => s.trim()).filter(s => s);
    urls.forEach(url => {
        const item = document.createElement('div');
        item.style.cssText = 'position:relative;width:100px;height:70px;border-radius:8px;overflow:hidden;border:1px solid rgba(255,255,255,0.1);background:#000;flex-shrink:0;';
        if (url.match(/\.(mp4|webm|ogg)(\?|$)/i) || url.indexOf('/assets/uploads/') !== -1 && !url.match(/\.(jpg|jpeg|png|gif|webp)(\?|$)/i)) {
            item.innerHTML = '<video src="' + encodeURI(url) + '" style="width:100%;height:100%;object-fit:cover;"></video>';
        } else {
            item.innerHTML = '<img src="' + encodeURI(url) + '" style="width:100%;height:100%;object-fit:cover;" onerror="this.parentElement.innerHTML=\'<div style=\\\'width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#64748b;font-size:10px;text-align:center;padding:4px;\\\'>加载失败</div>\'">';
        }
        container.appendChild(item);
    });
}