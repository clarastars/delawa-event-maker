import { Html5Qrcode } from "html5-qrcode";

document.addEventListener('DOMContentLoaded', function() {
    const startCameraButton = document.getElementById('start-camera');
    const stopCameraButton = document.getElementById('stop-camera');
    const readerContainer = document.getElementById('reader-container');
    const inputField = document.getElementById('voucher_id');
    const form = document.getElementById('scan-form');
    
    if (!startCameraButton) return;

    let html5QrCode;

    startCameraButton.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation(); // prevent form submission or focus steal
        
        readerContainer.classList.remove('hidden');
        startCameraButton.classList.add('hidden');
        stopCameraButton.classList.remove('hidden');
        
        html5QrCode = new Html5Qrcode("reader");

        const qrCodeSuccessCallback = (decodedText, decodedResult) => {
            // Found a barcode
            inputField.value = decodedText;
            
            // Stop scanning and submit
            html5QrCode.stop().then((ignore) => {
                form.submit();
            }).catch((err) => {
                // if stop fails, still submit
                form.submit();
            });
        };
        
        // Let's use environment facing camera
        const config = { fps: 10, qrbox: { width: 250, height: 100 } };
        
        html5QrCode.start(
            { facingMode: "environment" }, 
            config, 
            qrCodeSuccessCallback
        ).catch((err) => {
            console.error("Camera start failed", err);
            alert("Could not start camera. Please ensure you have given camera permissions.");
            stopScanner();
        });
    });

    stopCameraButton.addEventListener('click', function(e) {
        e.preventDefault();
        stopScanner();
    });

    function stopScanner() {
        if (html5QrCode) {
            html5QrCode.stop().then((ignore) => {
                hideScannerUI();
            }).catch((err) => {
                hideScannerUI();
            });
        } else {
            hideScannerUI();
        }
    }
    
    function hideScannerUI() {
        readerContainer.classList.add('hidden');
        startCameraButton.classList.remove('hidden');
        stopCameraButton.classList.add('hidden');
        inputField.focus();
    }
});
