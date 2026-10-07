<div class="purchase-camera-modal" id="purchase-camera-modal" aria-hidden="true">
	<div class="purchase-camera-dialog" role="dialog" aria-modal="true" aria-labelledby="purchase-camera-title">
		<div class="purchase-camera-header">
			<h5 id="purchase-camera-title" class="mb-0">Take product photo</h5>
			<button type="button" class="purchase-camera-close" id="purchase-camera-close" aria-label="Close camera">&times;</button>
		</div>
		<div class="purchase-camera-body">
			<video id="purchase-camera-video" autoplay playsinline></video>
			<canvas id="purchase-camera-canvas"></canvas>
			<img id="purchase-camera-preview" alt="Captured product preview">
			<p class="purchase-camera-message" id="purchase-camera-message"></p>
		</div>
		<div class="purchase-camera-footer">
			<button type="button" class="btn btn-light" id="purchase-camera-cancel">Close</button>
			<button type="button" class="btn btn-primary" id="purchase-camera-capture">Capture</button>
			<button type="button" class="btn btn-secondary" id="purchase-camera-retake">Retake</button>
			<button type="button" class="btn btn-success" id="purchase-camera-use">Use photo</button>
		</div>
	</div>
</div>

@push('page-css')
<style>
	.purchase-image-input-row {
		display: flex;
		gap: 8px;
		align-items: stretch;
	}
	.purchase-image-file { display: none; }
	.purchase-image-source { display: inline-flex; }
	.purchase-image-source .source-menu { min-width: 180px; padding: 6px; }
	.purchase-image-source .source-menu button,
	.purchase-image-source .source-menu label { display: block; width: 100%; margin: 0; padding: 9px 12px; border: 0; background: transparent; color: #344054; text-align: left; cursor: pointer; font-weight: 400; }
	.purchase-image-source .source-menu button:hover,
	.purchase-image-source .source-menu label:hover { background: #f1f5f9; }
	.purchase-camera-button { white-space: nowrap; }
	body.dark-mode .purchase-image-source > .btn,
	body.dark-mode .purchase-image-source > .btn:hover,
	body.dark-mode .purchase-image-source > .btn:focus {
		border-color: #69d9aa;
		color: #69d9aa;
	}
	body.dark-mode .purchase-image-source > .btn:hover,
	body.dark-mode .purchase-image-source > .btn:focus {
		background-color: #69d9aa;
		color: #10231d;
		box-shadow: 0 0 0 2px rgba(105, 217, 170, .18);
	}
	body.dark-mode .purchase-image-file-name { color: #cbd5e1; }
	.purchase-image-file-name {
		display: inline-flex;
		align-items: center;
		min-width: 0;
		max-width: 260px;
		padding: 0 8px;
		color: #64748b;
		font-size: 13px;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}
	body.dark-mode .purchase-camera-button:hover,
	body.dark-mode .purchase-camera-button:focus,
	body.dark-mode .purchase-camera-button:active {
		background-color: rgb(105, 217, 170);
		border-color: rgb(105, 217, 170);
		color: #fff;
	}
	.purchase-camera-modal {
		display: none;
		position: fixed;
		inset: 0;
		z-index: 1060;
		padding: 20px;
		background: rgba(15, 23, 42, .78);
		align-items: center;
		justify-content: center;
	}
	.purchase-camera-modal.is-open {
		display: flex;
	}
	.purchase-camera-dialog {
		width: min(640px, 100%);
		max-height: 100%;
		overflow: auto;
		background: #fff;
		border-radius: 8px;
		box-shadow: 0 12px 40px rgba(15, 23, 42, .3);
	}
	.purchase-camera-header,
	.purchase-camera-footer {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 14px 18px;
	}
	.purchase-camera-header {
		justify-content: space-between;
		border-bottom: 1px solid #e5e7eb;
	}
	.purchase-camera-footer {
		justify-content: flex-end;
		border-top: 1px solid #e5e7eb;
	}
	.purchase-camera-close {
		padding: 0;
		border: 0;
		background: transparent;
		color: #64748b;
		font-size: 28px;
		line-height: 1;
		cursor: pointer;
	}
	.purchase-camera-body {
		padding: 18px;
		text-align: center;
	}
	.purchase-camera-body video,
	.purchase-camera-body img {
		display: block;
		width: 100%;
		max-height: 60vh;
		border-radius: 6px;
		background: #111827;
		object-fit: contain;
	}
	.purchase-camera-body img,
	.purchase-camera-body canvas {
		display: none;
	}
	.purchase-camera-message {
		margin: 12px 0 0;
		color: #64748b;
	}
	#purchase-camera-retake,
	#purchase-camera-use {
		display: none;
	}
	@media (max-width: 576px) {
		.purchase-image-input-row {
			flex-direction: column;
		}
		.purchase-camera-footer {
			flex-wrap: wrap;
		}
	}
	body.dark-mode .purchase-camera-dialog {
		background: #1c2025;
		color: #e2e8f0;
		border: 1px solid #334155;
	}
	body.dark-mode .purchase-camera-header,
	body.dark-mode .purchase-camera-footer {
		border-color: #334155;
	}
	body.dark-mode .purchase-camera-close {
		color: #94a3b8;
	}
	body.dark-mode .purchase-camera-close:hover {
		color: #ffffff;
	}
	body.dark-mode #purchase-camera-cancel {
		background-color: #242c38 !important;
		border-color: #475569 !important;
		color: #e2e8f0 !important;
	}
	body.dark-mode #purchase-camera-cancel:hover,
	body.dark-mode #purchase-camera-cancel:focus {
		background-color: #334155 !important;
		border-color: #64748b !important;
		color: #ffffff !important;
	}
</style>
@endpush

@push('page-js')
<script>
	(function () {
		var cameraButton = document.getElementById('purchase-camera-button');
		var modal = document.getElementById('purchase-camera-modal');
		var video = document.getElementById('purchase-camera-video');
		var canvas = document.getElementById('purchase-camera-canvas');
		var preview = document.getElementById('purchase-camera-preview');
		var message = document.getElementById('purchase-camera-message');
		var captureButton = document.getElementById('purchase-camera-capture');
		var retakeButton = document.getElementById('purchase-camera-retake');
		var useButton = document.getElementById('purchase-camera-use');
		var input = document.getElementById('purchase-image-input');
		var fileName = document.getElementById('purchase-image-file-name');
		var productInput = document.querySelector('input[name="product"]');
		var stream = null;
		var capturedBlob = null;

		if (!cameraButton || !modal || !video || !canvas || !input) return;

		input.addEventListener('change', function () {
			fileName.textContent = input.files && input.files[0] ? input.files[0].name : 'No file chosen';
		});

		function setMessage(text) {
			message.textContent = text || '';
		}

		function stopCamera() {
			if (stream) {
				stream.getTracks().forEach(function (track) { track.stop(); });
				stream = null;
			}
			video.srcObject = null;
		}

		function resetCameraView() {
			video.style.display = 'block';
			preview.style.display = 'none';
			captureButton.style.display = 'inline-block';
			retakeButton.style.display = 'none';
			useButton.style.display = 'none';
			capturedBlob = null;
		}

		function closeCamera() {
			stopCamera();
			modal.classList.remove('is-open');
			modal.setAttribute('aria-hidden', 'true');
			resetCameraView();
			setMessage('');
		}

		function showCameraError(error) {
			console.error('Unable to access camera:', error);
			setMessage('Camera access was unavailable. Check browser permission and try again.');
			captureButton.style.display = 'none';
		}

		function notifyProductNameRequired() {
			if (window.Snackbar) {
				Snackbar.show({
					text: 'Please enter the Product Name before using the camera.',
					duration: 4000,
					pos: 'top-right',
					backgroundColor: '#e8483f',
					textColor: '#ffffff'
				});
			} else {
				setMessage('Please enter the Product Name before using the camera.');
			}
			if (productInput) productInput.focus();
		}

		async function openCamera() {
			if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
				setMessage('Camera capture is not supported by this browser.');
				return;
			}
			modal.classList.add('is-open');
			modal.setAttribute('aria-hidden', 'false');
			resetCameraView();
			setMessage('Allow camera access when your browser asks.');
			try {
				stream = await navigator.mediaDevices.getUserMedia({
					video: { facingMode: { ideal: 'environment' } },
					audio: false
				});
				video.srcObject = stream;
				setMessage('');
			} catch (error) {
				showCameraError(error);
			}
		}

		function capturePhoto() {
			if (!video.videoWidth || !video.videoHeight) {
				setMessage('The camera is still starting. Please try again.');
				return;
			}
			canvas.width = video.videoWidth;
			canvas.height = video.videoHeight;
			canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
			canvas.toBlob(function (blob) {
				if (!blob) {
					setMessage('The photo could not be captured. Please try again.');
					return;
				}
				capturedBlob = blob;
				preview.src = URL.createObjectURL(blob);
				video.style.display = 'none';
				preview.style.display = 'block';
				captureButton.style.display = 'none';
				retakeButton.style.display = 'inline-block';
				useButton.style.display = 'inline-block';
			}, 'image/jpeg', 0.9);
		}

		function usePhoto() {
			if (!capturedBlob) return;
			var productName = productInput ? productInput.value.trim() : '';
			var safeProductName = productName
				.replace(/[^a-z0-9]+/gi, '-')
				.replace(/^-+|-+$/g, '') || 'product';
			var file = new File([capturedBlob], safeProductName + '-camera.jpg', { type: 'image/jpeg' });
			var transfer = new DataTransfer();
			transfer.items.add(file);
			input.files = transfer.files;
			fileName.textContent = file.name;
			input.dispatchEvent(new Event('change', { bubbles: true }));
			closeCamera();
		}

		cameraButton.addEventListener('click', function () {
			if (!productInput || !productInput.value.trim()) {
				notifyProductNameRequired();
				return;
			}
			openCamera();
		});
		captureButton.addEventListener('click', capturePhoto);
		retakeButton.addEventListener('click', function () {
			resetCameraView();
			setMessage('');
		});
		useButton.addEventListener('click', usePhoto);
		document.getElementById('purchase-camera-close').addEventListener('click', closeCamera);
		document.getElementById('purchase-camera-cancel').addEventListener('click', closeCamera);
		modal.addEventListener('click', function (event) {
			if (event.target === modal) closeCamera();
		});
		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && modal.classList.contains('is-open')) closeCamera();
		});
	})();
</script>
@endpush
