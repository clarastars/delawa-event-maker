import QRCode from 'qrcode';

const qrOptions = {
    errorCorrectionLevel: 'M',
    margin: 2,
    width: 180,
    color: {
        dark: '#0f172a',
        light: '#ffffff',
    },
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-voucher-qr]').forEach((element) => {
        const value = element.dataset.voucherQr ?? '';

        if (value === '') {
            return;
        }

        QRCode.toCanvas(element, value, qrOptions).catch((error) => {
            console.error('Failed to render voucher QR code:', error);
        });
    });
});
