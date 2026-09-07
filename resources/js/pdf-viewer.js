import * as pdfjsLib from 'pdfjs-dist/build/pdf.mjs';
import pdfWorkerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?url';

pdfjsLib.GlobalWorkerOptions.workerSrc = pdfWorkerUrl;

document.querySelectorAll('[data-pdf-viewer]').forEach((viewer) => {
    const pagesContainer = viewer.querySelector('[data-pdf-pages-container]');
    const status = viewer.querySelector('[data-pdf-status]');
    const pagesLabel = viewer.querySelector('[data-pdf-pages]');
    const zoomLabel = viewer.querySelector('[data-pdf-zoom]');
    let zoom = 1;

    const drawWatermark = (context, canvas) => {
        const text = viewer.dataset.watermark || 'KhoaHocPlus';
        const ratio = canvas.width / Math.max(Number(canvas.dataset.baseWidth), 1);
        context.save();
        context.globalAlpha = 0.16;
        context.fillStyle = '#3156b8';
        context.font = `600 ${Math.round(15 * ratio)}px "Segoe UI", sans-serif`;
        context.textAlign = 'center';
        context.textBaseline = 'middle';
        context.translate(canvas.width / 2, canvas.height / 2);
        context.rotate(-Math.PI / 7);
        const stepX = Math.max(320 * ratio, context.measureText(text).width + 90 * ratio);
        const stepY = 170 * ratio;
        for (let y = -canvas.height; y <= canvas.height; y += stepY) {
            for (let x = -canvas.width; x <= canvas.width; x += stepX) context.fillText(text, x, y);
        }
        context.restore();
    };

    const applyZoom = () => {
        pagesContainer.querySelectorAll('canvas').forEach((canvas) => {
            canvas.style.width = `${Number(canvas.dataset.baseWidth) * zoom}px`;
            canvas.style.height = `${Number(canvas.dataset.baseHeight) * zoom}px`;
        });
        zoomLabel.textContent = `${Math.round(zoom * 100)}%`;
    };

    const renderDocument = async () => {
        try {
            const pdf = await pdfjsLib.getDocument({
                url: viewer.dataset.pdfUrl,
                withCredentials: true,
                disableAutoFetch: true,
                disableStream: false,
            }).promise;
            pagesLabel.textContent = pdf.numPages;
            pagesContainer.replaceChildren();

            for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber += 1) {
                status.hidden = false;
                status.textContent = `Đang hiển thị trang ${pageNumber}/${pdf.numPages}...`;
                const page = await pdf.getPage(pageNumber);
                const viewport = page.getViewport({ scale: 1.2 });
                const outputScale = Math.min(window.devicePixelRatio || 1, 1.5);
                const pageElement = document.createElement('section');
                pageElement.className = 'lesson-pdf-page';
                pageElement.innerHTML = `<small>Trang ${pageNumber}</small>`;
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.dataset.baseWidth = String(Math.floor(viewport.width));
                canvas.dataset.baseHeight = String(Math.floor(viewport.height));
                canvas.width = Math.floor(viewport.width * outputScale);
                canvas.height = Math.floor(viewport.height * outputScale);
                canvas.style.width = `${Math.floor(viewport.width) * zoom}px`;
                canvas.style.height = `${Math.floor(viewport.height) * zoom}px`;
                pageElement.appendChild(canvas);
                pagesContainer.appendChild(pageElement);
                await page.render({
                    canvasContext: context,
                    viewport,
                    transform: outputScale === 1 ? null : [outputScale, 0, 0, outputScale, 0, 0],
                }).promise;
                drawWatermark(context, canvas);
            }
            status.hidden = true;
        } catch (error) {
            status.hidden = false;
            status.textContent = error?.name === 'UnexpectedResponseException'
                ? 'Liên kết tài liệu đã hết hạn. Vui lòng tải lại trang học.'
                : 'Không thể mở tài liệu. Vui lòng tải lại trang học.';
        }
    };

    viewer.querySelector('[data-pdf-zoom-out]').addEventListener('click', () => {
        zoom = Math.max(0.65, zoom - 0.1);
        applyZoom();
    });
    viewer.querySelector('[data-pdf-zoom-in]').addEventListener('click', () => {
        zoom = Math.min(1.7, zoom + 0.1);
        applyZoom();
    });
    viewer.querySelector('[data-pdf-fullscreen]').addEventListener('click', () => {
        if (!document.fullscreenElement) viewer.requestFullscreen?.();
        else document.exitFullscreen?.();
    });
    viewer.addEventListener('contextmenu', (event) => event.preventDefault());
    viewer.addEventListener('dragstart', (event) => event.preventDefault());

    renderDocument();
});
