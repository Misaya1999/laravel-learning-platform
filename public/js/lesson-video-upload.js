(function () {
    'use strict';

    function initLessonVideoUpload() {
        const config = document.getElementById('lesson-video-upload-config');
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (!config || !csrfMeta) return;

        const chunkUrl = config.dataset.chunkUrl;
        const completeUrl = config.dataset.completeUrl;
        const csrfToken = csrfMeta.content;
        // 50MB giúp giảm số request nhưng vẫn nằm an toàn dưới giới hạn 100MB
        // của proxy sau khi cộng thêm multipart overhead.
        const chunkSize = 50 * 1024 * 1024;
        const maxFileSize = 600 * 1024 * 1024;
        const forms = new Set(
            Array.from(document.querySelectorAll('.video-file-input'))
                .map(function (input) { return input.closest('form'); })
                .filter(Boolean)
        );

        function formatSize(bytes) {
            if (bytes >= 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
            return Math.max(0, bytes / 1024).toFixed(0) + ' KB';
        }

        function responseMessage(xhr, fallback) {
            try {
                const data = JSON.parse(xhr.responseText);
                return data.message || Object.values(data.errors || {}).flat()[0] || fallback;
            } catch (_) {
                return fallback;
            }
        }

        function sendChunk(payload, onProgress, retries) {
            return new Promise(function (resolve, reject) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', chunkUrl, true);
                xhr.timeout = 120000;
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.upload.addEventListener('progress', onProgress);
                xhr.addEventListener('load', function () {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        resolve();
                        return;
                    }
                    reject(new Error(responseMessage(xhr, 'Máy chủ từ chối một phần video.')));
                });

                function retryOrReject() {
                    if (retries > 0) {
                        window.setTimeout(function () {
                            sendChunk(payload, onProgress, retries - 1).then(resolve).catch(reject);
                        }, 1000);
                        return;
                    }
                    reject(new Error('Mất kết nối khi tải video. Vui lòng kiểm tra mạng và thử lại.'));
                }

                xhr.addEventListener('error', retryOrReject);
                xhr.addEventListener('timeout', retryOrReject);
                xhr.send(payload);
            });
        }

        forms.forEach(function (form) {
            if (form.dataset.videoUploadReady === 'true') return;
            form.dataset.videoUploadReady = 'true';

            form.addEventListener('submit', async function (event) {
                const fileInput = form.querySelector('.video-file-input');
                const file = fileInput && fileInput.files && fileInput.files[0];
                if (!file || form.dataset.uploading === 'true') return;

                event.preventDefault();
                event.stopPropagation();

                if (file.size > maxFileSize) {
                    window.alert('Video vượt quá giới hạn 600MB.');
                    return;
                }

                const footer = form.querySelector('.modal-footer');
                if (!footer) {
                    window.alert('Không thể khởi tạo vùng hiển thị tiến trình upload.');
                    return;
                }

                form.dataset.uploading = 'true';
                form.querySelector('.video-upload-progress')?.remove();

                const progress = document.createElement('div');
                progress.className = 'video-upload-progress';
                progress.innerHTML = '<div class="video-upload-progress-heading"><strong></strong><b>0%</b></div>' +
                    '<div class="video-upload-progress-track"><i></i></div>' +
                    '<div class="video-upload-progress-meta"><span>Đang chuẩn bị tải video...</span><span></span></div>';
                footer.parentNode.insertBefore(progress, footer);

                const fileName = progress.querySelector('strong');
                const percentLabel = progress.querySelector('b');
                const bar = progress.querySelector('i');
                const status = progress.querySelector('.video-upload-progress-meta span:first-child');
                const size = progress.querySelector('.video-upload-progress-meta span:last-child');
                fileName.textContent = file.name;
                size.textContent = '0 / ' + formatSize(file.size);

                const buttons = form.querySelectorAll('button[type="submit"]');
                buttons.forEach(function (button) {
                    button.disabled = true;
                    button.dataset.originalText = button.textContent;
                    button.textContent = 'Đang tải video...';
                });

                const warnBeforeLeave = function (leaveEvent) {
                    leaveEvent.preventDefault();
                    leaveEvent.returnValue = '';
                };
                window.addEventListener('beforeunload', warnBeforeLeave);

                function finishWithError(message) {
                    window.removeEventListener('beforeunload', warnBeforeLeave);
                    form.dataset.uploading = 'false';
                    progress.classList.remove('is-processing');
                    progress.classList.add('is-error');
                    status.textContent = message;
                    buttons.forEach(function (button) {
                        button.disabled = false;
                        button.textContent = button.dataset.originalText || 'Lưu';
                    });
                }

                try {
                    const uploadId = window.crypto && window.crypto.randomUUID
                        ? window.crypto.randomUUID()
                        : Date.now() + '-' + Math.random().toString(36).slice(2) + '-' + Math.random().toString(36).slice(2);
                    const totalChunks = Math.ceil(file.size / chunkSize);

                    for (let index = 0; index < totalChunks; index++) {
                        const start = index * chunkSize;
                        const end = Math.min(start + chunkSize, file.size);
                        const payload = new FormData();
                        payload.append('_token', csrfToken);
                        payload.append('upload_id', uploadId);
                        payload.append('chunk_index', String(index));
                        payload.append('total_chunks', String(totalChunks));
                        payload.append('video_chunk', file.slice(start, end), 'video.part');
                        status.textContent = 'Đang tải phần ' + (index + 1) + '/' + totalChunks + '...';

                        await sendChunk(payload, function (uploadEvent) {
                            if (!uploadEvent.lengthComputable) return;
                            const loaded = Math.min(file.size, start + uploadEvent.loaded);
                            const percent = Math.min(99, Math.round(loaded / file.size * 100));
                            bar.style.width = percent + '%';
                            percentLabel.textContent = percent + '%';
                            size.textContent = formatSize(loaded) + ' / ' + formatSize(file.size);
                        }, 2);
                    }

                    progress.classList.add('is-processing');
                    status.textContent = 'Đã tải đủ các phần, đang ghép video...';
                    const completePayload = new FormData();
                    completePayload.append('_token', csrfToken);
                    completePayload.append('upload_id', uploadId);
                    completePayload.append('total_chunks', String(totalChunks));
                    completePayload.append('expected_size', String(file.size));

                    const completeResponse = await fetch(completeUrl, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        body: completePayload
                    });
                    const completeData = await completeResponse.json().catch(function () { return {}; });
                    if (!completeResponse.ok || !completeData.upload_token) {
                        throw new Error(completeData.message || 'Không thể ghép các phần video.');
                    }

                    let tokenInput = form.querySelector('input[name="video_upload_token"]');
                    if (!tokenInput) {
                        tokenInput = document.createElement('input');
                        tokenInput.type = 'hidden';
                        tokenInput.name = 'video_upload_token';
                        form.appendChild(tokenInput);
                    }
                    tokenInput.value = completeData.upload_token;
                    fileInput.value = '';
                    bar.style.width = '100%';
                    percentLabel.textContent = '100%';
                    size.textContent = formatSize(file.size) + ' / ' + formatSize(file.size);
                    status.textContent = 'Video đã tải xong, đang lưu bài học...';
                    window.removeEventListener('beforeunload', warnBeforeLeave);
                    HTMLFormElement.prototype.submit.call(form);
                } catch (error) {
                    finishWithError(error.message || 'Upload không thành công. Vui lòng thử lại.');
                }
            });
        });
    }

    // Script được đặt sau toàn bộ form lesson nên khởi tạo ngay, không phụ thuộc
    // DOMContentLoaded (sự kiện này có thể đã chạy khi trang được nạp động).
    initLessonVideoUpload();
})();
