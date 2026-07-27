import JsBarcode from 'jsbarcode';

const barcodeOptions = {
    format: 'CODE128',
    displayValue: false,
    margin: 8,
    width: 2,
    height: 44,
    lineColor: '#0f172a',
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-voucher-barcode]').forEach((element) => {
        const value = element.dataset.voucherBarcode ?? '';

        if (value !== '') {
            JsBarcode(element, value, barcodeOptions);
        }
    });
});
