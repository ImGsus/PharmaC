<?php
require_once __DIR__ . '/function.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webby Camera Scanner</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
        }
        .scanner-shell {
            width: 100%;
            max-width: 520px;
            background: white;
            border-radius: 18px;
            box-shadow: 0 18px 50px rgba(23, 43, 77, 0.12);
            padding: 28px;
            text-align: center;
        }
        h1 {
            margin-bottom: 12px;
            font-size: 1.6rem;
            color: #192a4e;
        }
        p.lead {
            margin: 0 0 20px;
            color: #556987;
        }
        .camera-box {
            position: relative;
            width: 100%;
            padding-top: 56.25%;
            border-radius: 16px;
            border: 2px solid #dde6f1;
            background: #111f34;
            overflow: hidden;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.04);
            margin-bottom: 20px;
        }
        #camera-preview {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: #111f34;
        }
        .camera-overlay {
            position: absolute;
            inset: 0;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .scan-border {
            width: calc(100% - 40px);
            height: calc(100% - 40px);
            border: 2px dashed rgba(255,255,255,0.35);
            border-radius: 16px;
            box-shadow: 0 0 0 1px rgba(255,255,255,0.08);
        }
        .scan-text {
            position: absolute;
            bottom: 18px;
            left: 0;
            right: 0;
            color: #e8f1ff;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 0.9rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 0 28px;
            border: none;
            border-radius: 999px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: transform .18s ease, box-shadow .18s ease, opacity .18s ease;
        }
        .btn-primary {
            background: #2f7df6;
            color: white;
            box-shadow: 0 14px 30px rgba(47, 125, 246, 0.22);
        }
        .btn-secondary {
            background: #edf2f8;
            color: #324563;
        }
        .btn:hover { transform: translateY(-1px); }
        .status {
            margin-top: 18px;
            font-size: 0.95rem;
            color: #354258;
        }
        .status.error { color: #c34a4a; }
        .status.success { color: #2a8c57; }
        input[type="text"] {
            width: 100%;
            margin-top: 24px;
            border: 1px solid #d9e2ec;
            border-radius: 14px;
            padding: 14px 18px;
            font-size: 1rem;
            color: #172b4d;
            outline: none;
        }
        input[type="text"]:focus {
            border-color: #2f7df6;
            box-shadow: 0 0 0 4px rgba(47, 125, 246, 0.12);
        }
    </style>
</head>
<body>
    <div class="scanner-shell">
        <h1>Webby Camera Scanner</h1>
        <p class="lead">Point your camera at a barcode and it will be inserted here automatically.</p>

        <div class="camera-box">
            <video id="camera-preview" autoplay muted playsinline></video>
            <div class="camera-overlay">
                <div class="scan-border"></div>
                <div class="scan-text">Align barcode inside frame</div>
            </div>
        </div>

        <input id="barcode-value" type="text" name="sku" placeholder="Detected SKU/barcode appears here" autocomplete="off">
        <div id="status-message" class="status">Press Start camera to begin scanning.</div>

        <div style="margin-top:18px; display:flex; gap:12px; flex-wrap:wrap; justify-content:center;">
            <button id="lookup-btn" class="btn btn-secondary" type="button">Lookup SKU manually</button>
        </div>

        <div style="margin-top:16px; display:flex; gap:12px; flex-wrap:wrap; justify-content:center;">
            <button id="start-camera" class="btn btn-primary">Start camera</button>
            <button id="stop-camera" class="btn btn-secondary" type="button">Stop camera</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@zxing/library@0.19.1/umd/index.min.js"></script>
    <script>
        const startButton = document.getElementById('start-camera');
        const stopButton = document.getElementById('stop-camera');
        const lookupButton = document.getElementById('lookup-btn');
        const preview = document.getElementById('camera-preview');
        const barcodeInput = document.getElementById('barcode-value');
        const statusMessage = document.getElementById('status-message');

        let stream = null;
        let codeReader = null;

        async function startCamera() {
            try {
                statusMessage.textContent = 'Starting camera and scanning SKU...';
                statusMessage.classList.remove('error');

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    statusMessage.textContent = 'Camera access is unavailable. Use Chrome on Android with HTTPS or localhost access to enable the camera.';
                    statusMessage.classList.add('error');
                    return;
                }

                codeReader = new ZXing.BrowserMultiFormatReader();

                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                preview.srcObject = stream;
                await preview.play();

                startButton.disabled = true;
                stopButton.disabled = false;

                codeReader.decodeFromVideoDevice(null, preview, (result, err) => {
                    if (result) {
                        const sku = result.text.trim();
                        if (sku) {
                            barcodeInput.value = sku;
                            statusMessage.textContent = 'SKU detected! Redirecting...';
                            stopCamera();
                            redirectToApp(sku);
                        }
                    }

                    if (err && !(err instanceof ZXing.NotFoundException) && !(err instanceof ZXing.ChecksumException) && !(err instanceof ZXing.FormatException)) {
                        console.debug('ZXing scan error', err);
                    }
                });
            } catch (error) {
                console.error('Camera start failed', error);
                statusMessage.textContent = error.message || 'Unable to access camera. Please allow permission and use HTTPS.';
            }
        }

        function redirectToApp(sku) {
            var params = new URLSearchParams(window.location.search);
            var origin = params.get('origin');
            if (window.opener && origin === 'product_create') {
                window.opener.postMessage({ type: 'scanned_barcode', barcode: sku }, '*');
                window.close();
                return;
            }
            const url = new URL('/products/scan/result', window.location.origin);
            url.searchParams.set('sku', sku);
            if (origin) {
                url.searchParams.set('origin', origin);
            }
            window.location.href = url.toString();
        }

        function lookupProduct() {
            const sku = barcodeInput.value.trim();
            if (!sku) {
                statusMessage.textContent = 'Enter a SKU or scan a barcode first.';
                statusMessage.classList.add('error');
                return;
            }
            statusMessage.textContent = 'Looking up SKU...';
            statusMessage.classList.remove('error');
            redirectToApp(sku);
        }

        function stopCamera() {
            if (codeReader) {
                codeReader.reset();
                codeReader = null;
            }
            if (stream) {
                stream.getTracks().forEach((track) => track.stop());
                stream = null;
            }
            preview.srcObject = null;
            startButton.disabled = false;
            stopButton.disabled = true;
            if (!barcodeInput.value) {
                statusMessage.textContent = 'Camera stopped. Press Start camera to try again.';
            }
        }

        startButton.addEventListener('click', (event) => {
            event.preventDefault();
            startCamera();
        });

        stopButton.addEventListener('click', (event) => {
            event.preventDefault();
            stopCamera();
        });

        lookupButton.addEventListener('click', (event) => {
            event.preventDefault();
            lookupProduct();
        });

        barcodeInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                lookupProduct();
            }
        });

        stopButton.disabled = true;
    </script>
</body>
</html>
