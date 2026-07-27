import JsBarcode from 'jsbarcode';

const OUTPUT_WIDTH = 1200;
const BARCODE_WIDTH_RATIO = 0.5;

const barcodeOptions = {
    format: 'CODE128',
    displayValue: false,
    margin: 8,
    width: 2,
    height: 44,
    lineColor: '#0f172a',
};

function loadImage(src) {
    return new Promise((resolve, reject) => {
        const image = new Image();
        image.onload = () => resolve(image);
        image.onerror = () => reject(new Error('Failed to load voucher image.'));
        image.src = src;
    });
}

function renderBarcodeCanvas(voucherId, targetWidth) {
    const canvas = document.createElement('canvas');
    JsBarcode(canvas, voucherId, barcodeOptions);

    if (canvas.width >= targetWidth) {
        return canvas;
    }

    const scaled = document.createElement('canvas');
    scaled.width = targetWidth;
    scaled.height = Math.round((canvas.height / canvas.width) * targetWidth);

    const context = scaled.getContext('2d');

    if (context) {
        context.drawImage(canvas, 0, 0, scaled.width, scaled.height);
    }

    return scaled;
}

function triggerDownload(dataUrl, filename) {
    const link = document.createElement('a');
    link.download = filename;
    link.href = dataUrl;
    document.body.appendChild(link);
    link.click();
    link.remove();
}

document.addEventListener('DOMContentLoaded', () => {
    const card = document.getElementById('voucher-card');

    if (! card) {
        return;
    }

    const voucherId = card.dataset.voucherId ?? '';
    const barcode = document.getElementById('voucher-barcode');

    if (barcode && voucherId !== '') {
        JsBarcode(barcode, voucherId, barcodeOptions);
    }

    const downloadButton = document.getElementById('download-voucher');
    const voucherImage = card.querySelector('img');
    const voucherIdLabel = card.querySelector('[data-voucher-id-label]');
    const oneTimeLabel = card.querySelector('[data-one-time-label]');

    downloadButton?.addEventListener('click', async () => {
        if (! voucherImage?.src || voucherId === '') {
            return;
        }

        downloadButton.disabled = true;

        try {
            const image = await loadImage(voucherImage.src);
            const width = OUTPUT_WIDTH;
            const imageHeight = Math.round(image.naturalHeight * (width / image.naturalWidth));
            const horizontalPadding = Math.round(width * 0.06);
            const topPadding = Math.round(width * 0.05);
            const barcodeGap = Math.round(width * 0.03);
            const labelGap = Math.round(width * 0.02);
            const bottomPadding = Math.round(width * 0.05);
            const barcodeTargetWidth = Math.round(width * BARCODE_WIDTH_RATIO);
            const barcodeCanvas = renderBarcodeCanvas(voucherId, barcodeTargetWidth);
            const barcodeHeight = barcodeCanvas.height;
            const labelLineHeight = Math.round(width * 0.03);
            const oneTimeLineHeight = oneTimeLabel ? Math.round(width * 0.018) : 0;
            const footerHeight = topPadding
                + barcodeHeight
                + barcodeGap
                + labelLineHeight
                + (oneTimeLabel ? labelGap + oneTimeLineHeight : 0)
                + bottomPadding;
            const height = imageHeight + footerHeight;
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;

            const context = canvas.getContext('2d');

            if (! context) {
                throw new Error('Could not prepare voucher download.');
            }

            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, width, height);
            context.drawImage(image, 0, 0, width, imageHeight);

            const footerTop = imageHeight;
            context.strokeStyle = '#f1f5f9';
            context.lineWidth = 1;
            context.beginPath();
            context.moveTo(0, footerTop);
            context.lineTo(width, footerTop);
            context.stroke();

            const barcodeX = (width - barcodeTargetWidth) / 2;
            const barcodeY = footerTop + topPadding;
            context.drawImage(barcodeCanvas, barcodeX, barcodeY, barcodeTargetWidth, barcodeHeight);

            const label = voucherIdLabel?.textContent?.trim() || voucherId;
            const labelFontSize = Math.round(width * 0.013);
            context.fillStyle = '#020617';
            context.font = `600 ${labelFontSize}px ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace`;
            context.textAlign = 'center';
            context.fillText(label, width / 2, barcodeY + barcodeHeight + barcodeGap + labelFontSize);

            if (oneTimeLabel) {
                const oneTimeFontSize = Math.round(width * 0.012);
                context.fillStyle = '#02777a';
                context.font = `600 ${oneTimeFontSize}px ui-sans-serif, system-ui, sans-serif`;
                context.fillText(
                    oneTimeLabel.textContent.trim(),
                    width / 2,
                    barcodeY + barcodeHeight + barcodeGap + labelLineHeight + labelGap + oneTimeFontSize,
                );
            }

            triggerDownload(canvas.toDataURL('image/png'), `tsepass-voucher-${voucherId}.png`);
        } catch (error) {
            console.error('Failed to download voucher:', error);
        } finally {
            downloadButton.disabled = false;
        }
    });
});
