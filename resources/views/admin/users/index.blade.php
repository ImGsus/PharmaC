@extends('admin.layouts.app')

<x-assets.tabulator />  

@push('page-css')
	<style>
		#userDetailsModal .modal-dialog,
		#userEditModal .modal-dialog {
			max-width: 760px;
		}
		#userDetailsModal .user-details-layout {
			display: grid;
			grid-template-columns: 180px minmax(0, 1fr);
			gap: 24px;
		}
		#userDetailsModal .user-details-picture {
			display: flex;
			align-items: center;
			justify-content: center;
			min-height: 180px;
			padding: 16px;
			background: #f5f7fb;
			border: 1px solid #e5eaf1;
			border-radius: 8px;
		}
		#userDetailsModal .user-details-picture img {
			width: 140px;
			height: 140px;
			object-fit: cover;
			border-radius: 50%;
		}
		#userDetailsModal .user-details-info {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 12px 18px;
		}
		#userDetailsModal .user-detail-item {
			padding-bottom: 8px;
			border-bottom: 1px solid #edf0f4;
		}
		#userDetailsModal .user-detail-item strong {
			display: block;
			margin-bottom: 3px;
			font-size: 12px;
			font-weight: 600;
			color: #7a8795;
			text-transform: uppercase;
		}
		#userDetailsModal .user-detail-item span {
			color: #263238;
			word-break: break-word;
		}
		#userEditModal .modal-body {
			padding: 24px;
		}
		#userCreateModal .modal-dialog {
			max-width: 980px;
		}
		#userCreateModal .modal-content {
			border: 1px solid #b8c4d2;
			border-radius: 8px;
			box-shadow: 0 16px 36px rgba(31, 45, 61, .2);
		}
		#userCreateModal .modal-header {
			padding: 20px 28px;
			border-bottom: 1px solid #e5eaf1;
		}
		#userCreateModal .modal-body {
			padding: 26px 32px 16px;
		}
		#userCreateModal .user-create-layout {
			display: grid;
			grid-template-columns: minmax(260px, 38%) minmax(0, 1fr);
			gap: 34px;
			align-items: start;
		}
		#userCreateModal .user-create-picture {
			display: flex;
			align-items: center;
			justify-content: center;
			min-height: 380px;
			padding: 24px;
			background: #f5f7fb;
			border: 1px solid #e1e7ef;
			border-radius: 8px;
		}
		#userCreateModal .user-create-picture img {
			width: 100%;
			height: 330px;
			object-fit: cover;
			border-radius: 6px;
		}
		#userCreateModal .user-create-picture img.is-changing {
			opacity: 0;
			transition: opacity .2s ease;
		}
		#userCreateModal .user-create-picture img.is-ready {
			opacity: 1;
			transition: opacity .2s ease;
		}
		#userCreateModal .user-create-fields {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			column-gap: 18px;
		}
		#userCreateModal .user-create-fields .form-group {
			margin-bottom: 20px;
		}
		#userCreateModal .user-create-confirm-password {
			grid-column: 1 / -1;
			width: 100%;
		}
		#userCreateModal .form-group {
			margin-bottom: 20px;
		}
		#userCreateModal label {
			color: #344054;
			font-weight: 500;
		}
		#userCreateModal .form-control {
			min-height: 44px;
			border-color: #cfd7e3;
			border-radius: 5px;
		}
		#userCreateModal .create-user-submit {
			min-width: 160px;
			border-radius: 22px;
		}
		#userCreateModal .user-avatar-file { display: none; }
		#userCreateModal .user-image-file-name {
			display: inline-flex;
			align-items: center;
			max-width: 180px;
			margin-left: 8px;
			color: #64748b;
			font-size: 13px;
			vertical-align: middle;
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
		}
		#userCreateModal .image-source-picker { display: inline-flex; }
		#userCreateModal .source-menu { min-width: 180px; padding: 6px; }
		#userCreateModal .source-menu button,
		#userCreateModal .source-menu label {
			display: block;
			width: 100%;
			margin: 0;
			padding: 9px 12px;
			border: 0;
			background: transparent;
			color: #344054;
			text-align: left;
			cursor: pointer;
			font-weight: 400;
		}
		#userCreateModal .source-menu button:hover,
		#userCreateModal .source-menu label:hover { background: #f1f5f9; }
		body.dark-mode #userCreateModal .modal-content {
			background: #1c2025;
			border-color: #475569;
			color: #f8fafc;
		}
		body.dark-mode #userCreateModal .modal-header {
			border-bottom-color: #475569;
		}
		body.dark-mode #userCreateModal .modal-title,
		body.dark-mode #userCreateModal label {
			color: #e2e8f0;
		}
		body.dark-mode #userCreateModal .close {
			color: #cbd5e1;
			text-shadow: none;
		}
		body.dark-mode #userCreateModal .user-create-picture {
			background: #252b33;
			border-color: #475569;
		}
		body.dark-mode #userCreateModal .form-control {
			background-color: #111827;
			border-color: #475569 !important;
			color: #f8fafc;
		}
		body.dark-mode #userCreateModal .form-control::placeholder {
			color: #94a3b8;
			opacity: 1;
		}
		body.dark-mode #userCreateModal .user-create-fields input.form-control:hover,
		body.dark-mode #userCreateModal .user-create-fields input.form-control:focus,
		body.dark-mode #userCreateModal .user-create-fields select.form-control:hover,
		body.dark-mode #userCreateModal .user-create-fields select.form-control:focus {
			border-color: #69d9aa !important;
			box-shadow: 0 0 0 2px rgba(105, 217, 170, .18) !important;
		}
		body.dark-mode #userCreateModal .image-source-picker > .btn {
			border-color: #69d9aa;
			color: #69d9aa;
		}
		body.dark-mode #userCreateModal .image-source-picker > .btn:hover,
		body.dark-mode #userCreateModal .image-source-picker > .btn:focus {
			background: #69d9aa;
			border-color: #69d9aa;
			color: #10231d;
		}
		body.dark-mode #userCreateModal .user-image-file-name {
			color: #cbd5e1;
		}
		body.dark-mode #userCreateModal .source-menu {
			background: #252b33;
			border-color: #475569;
		}
		body.dark-mode #userCreateModal .source-menu button,
		body.dark-mode #userCreateModal .source-menu label {
			color: #e2e8f0;
		}
		body.dark-mode #userCreateModal .source-menu button:hover,
		body.dark-mode #userCreateModal .source-menu label:hover {
			background: #334155;
			color: #69d9aa;
		}
		.user-create-camera-modal {
			display: none;
			position: fixed;
			inset: 0;
			z-index: 1070;
			padding: 20px;
			background: rgba(15, 23, 42, .78);
			align-items: center;
			justify-content: center;
		}
		.user-create-camera-modal.is-open { display: flex; }
		.user-create-camera-dialog {
			width: min(560px, 100%);
			overflow: hidden;
			background: #fff;
			border-radius: 8px;
			box-shadow: 0 12px 40px rgba(15, 23, 42, .3);
		}
		.user-create-camera-header,
		.user-create-camera-footer { display: flex; align-items: center; padding: 14px 18px; }
		.user-create-camera-header { justify-content: space-between; border-bottom: 1px solid #e5e7eb; }
		.user-create-camera-footer { justify-content: flex-end; gap: 8px; border-top: 1px solid #e5e7eb; }
		.user-create-camera-close { padding: 0; border: 0; background: transparent; color: #64748b; font-size: 28px; line-height: 1; cursor: pointer; }
		.user-create-camera-body { padding: 16px; text-align: center; }
		.user-create-camera-body video,
		.user-create-camera-body img { display: block; width: 100%; max-height: 60vh; border-radius: 6px; background: #111827; object-fit: contain; }
		.user-create-camera-body img { display: none; }
		.user-create-camera-message { margin: 12px 0 0; color: #64748b; }
		#user-create-camera-retake,
		#user-create-camera-use { display: none; }
		@media (max-width: 576px) {
			#userCreateModal .user-create-layout,
			#userCreateModal .user-create-fields { grid-template-columns: 1fr; }
			#userCreateModal .user-create-picture { min-height: 240px; }
			#userCreateModal .user-create-picture img { height: 210px; }
			#userDetailsModal .user-details-layout,
			#userDetailsModal .user-details-info {
				grid-template-columns: 1fr;
			}
		}
	</style>
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">User</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Users</li>
	</ul>
</div>
<div class="col-sm-5 col">
	<button type="button" class="btn btn-primary float-right mt-2" data-toggle="modal" data-target="#userCreateModal">Add User</button>
</div>

@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<div id="user-table" class="tabulator-table-wrap"></div>
					<div class="modal fade" id="userCreateModal" tabindex="-1" role="dialog" aria-labelledby="userCreateModalTitle" aria-hidden="true">
						<div class="modal-dialog modal-dialog-centered" role="document">
							<div class="modal-content">
								<div class="modal-header"><h5 class="modal-title" id="userCreateModalTitle">Add User</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button></div>
								<div class="modal-body">
									<form method="POST" enctype="multipart/form-data" action="{{ route('users.store') }}" id="userCreateForm">
										@csrf
										<div class="user-create-layout">
											<div class="user-create-picture"><img id="createUserAvatarPreview" src="{{ asset('assets/img/ADMIN.jpg') }}" alt="User picture preview" class="is-ready"></div>
											<div class="user-create-fields">
												<div class="form-group"><label for="createUserFirstName">First Name</label><input type="text" name="first_name" id="createUserFirstName" class="form-control" placeholder="First Name" required></div>
												<div class="form-group"><label for="createUserLastName">Last Name</label><input type="text" name="last_name" id="createUserLastName" class="form-control" placeholder="Last Name" required></div>
												<div class="form-group"><label for="createUserUsername">Username</label><input type="text" name="username" id="createUserUsername" class="form-control" placeholder="Username" required></div>
												<div class="form-group"><label for="createUserGender">Gender</label><select name="gender" id="createUserGender" class="form-control" required><option value="" selected disabled>Gender</option><option value="Male">Male</option><option value="Female">Female</option></select></div>
												<div class="form-group"><label for="createUserEmail">Email</label><input type="email" name="email" id="createUserEmail" class="form-control" placeholder="example@gmail.com" required></div>
												<div class="form-group"><label for="createUserRole">Role</label><select name="role" id="createUserRole" class="form-control" required>@foreach ($roles as $role)<option value="{{ $role->name }}">{{ $role->name }}</option>@endforeach</select></div>
														<div class="form-group user-create-picture-field"><div class="image-source-picker dropdown"><input type="file" name="avatar" id="createUserAvatar" class="user-avatar-file" accept="image/*"><button type="button" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-image mr-1"></i> Add photo</button><div class="dropdown-menu source-menu"><label for="createUserAvatar"><i class="fa fa-folder-open-o mr-1"></i> Choose file</label><button type="button" id="user-create-camera-button"><i class="fa fa-camera mr-1"></i> Use camera</button></div></div><span class="user-image-file-name" id="createUserAvatarFileName">No file chosen</span></div>
												<div class="form-group"><label for="createUserPassword">Password</label><input type="password" name="password" id="createUserPassword" class="form-control" required></div>
												<div class="form-group user-create-confirm-password"><label for="createUserPasswordConfirmation">Confirm Password</label><input type="password" name="password_confirmation" id="createUserPasswordConfirmation" class="form-control" required></div>
												<div class="text-center pt-2" style="grid-column: 1 / -1;"><button type="submit" class="btn btn-primary create-user-submit">Save Changes</button></div>
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
					<div class="user-create-camera-modal" id="user-create-camera-modal" aria-hidden="true">
						<div class="user-create-camera-dialog" role="dialog" aria-modal="true" aria-labelledby="user-create-camera-title">
							<div class="user-create-camera-header"><h5 id="user-create-camera-title" class="mb-0">Take user photo</h5><button type="button" class="user-create-camera-close" id="user-create-camera-close" aria-label="Close camera">&times;</button></div>
							<div class="user-create-camera-body"><video id="user-create-camera-video" autoplay playsinline></video><canvas id="user-create-camera-canvas" hidden></canvas><img id="user-create-camera-preview" alt="Captured user preview"><p class="user-create-camera-message" id="user-create-camera-message"></p></div>
							<div class="user-create-camera-footer"><button type="button" class="btn btn-light" id="user-create-camera-cancel">Close</button><button type="button" class="btn btn-primary" id="user-create-camera-capture">Capture</button><button type="button" class="btn btn-secondary" id="user-create-camera-retake">Retake</button><button type="button" class="btn btn-success" id="user-create-camera-use">Use photo</button></div>
						</div>
					</div>
					<div class="modal fade" id="userDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
						<div class="modal-dialog modal-dialog-centered" role="document">
							<div class="modal-content">
								<div class="modal-header"><h5 class="modal-title">User Details</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button></div>
								<div class="modal-body">
									<div class="user-details-layout">
										<div class="user-details-picture"><img class="user-detail detail-avatar" src="{{ asset('assets/img/avatar.png') }}" alt="User picture"></div>
										<div class="user-details-info">
											<div class="user-detail-item"><strong>Name</strong><span class="user-detail detail-name"></span></div>
											<div class="user-detail-item"><strong>Email</strong><span class="user-detail detail-email"></span></div>
											<div class="user-detail-item"><strong>Role</strong><span class="user-detail detail-role"></span></div>
											<div class="user-detail-item"><strong>Gender</strong><span class="user-detail detail-gender"></span></div>
											<div class="user-detail-item"><strong>Username</strong><span class="user-detail detail-username"></span></div>
											<div class="user-detail-item"><strong>Created Date</strong><span class="user-detail detail-created_at"></span></div>
											<div class="user-detail-item"><strong>Email Verified</strong><span class="user-detail detail-email_verified_at"></span></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal fade" id="userEditModal" tabindex="-1" role="dialog" aria-hidden="true">
						<div class="modal-dialog modal-dialog-centered" role="document">
							<div class="modal-content">
								<div class="modal-header"><h5 class="modal-title">Edit User</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button></div>
								<div class="modal-body">
									<form method="POST" enctype="multipart/form-data" id="userEditForm">
										@csrf
										@method('PUT')
										<div class="form-group"><label for="editUserName">Full Name</label><input type="text" name="name" id="editUserName" class="form-control" required></div>
										<div class="form-group"><label for="editUserEmail">Email</label><input type="email" name="email" id="editUserEmail" class="form-control" required></div>
										<div class="form-group"><label for="editUserRole">Role</label><select name="role" id="editUserRole" class="form-control" required>@foreach ($roles as $role)<option value="{{ $role->name }}">{{ $role->name }}</option>@endforeach</select></div>
										<div class="form-group"><label for="editUserAvatar">Picture</label><input type="file" name="avatar" id="editUserAvatar" class="form-control" accept="image/*"></div>
										<div class="row"><div class="col-md-6"><div class="form-group"><label for="editUserPassword">Password</label><input type="password" name="password" id="editUserPassword" class="form-control"></div></div><div class="col-md-6"><div class="form-group"><label for="editUserPasswordConfirmation">Confirm Password</label><input type="password" name="password_confirmation" id="editUserPasswordConfirmation" class="form-control"></div></div></div>
										<button type="submit" class="btn btn-primary btn-block">Save Changes</button>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@push('page-js')
<script>
window.pharmacyUsersInit = function() {
	if (!document.getElementById('user-table')) {
		return;
	}

	if (!window.PharmaTabulator) {
		return;
	}

    window.PharmaTabulator.server({
        el: 'user-table',
        url: "{{route('users.index')}}",
        columns: [
            {title: 'Name', field: 'name'},
            {title: 'Email', field: 'email'},
            {title: 'Role', field: 'role', formatter: 'html'},
            {title: 'Created date', field: 'created_at'},
            {title: 'Actions', field: 'action', formatter: 'html', headerSort: false, searchable: false, width: 110, hozAlign: 'center'},
        ]
	});

	$(document).off('click.userDetails', '.user-detail-btn').on('click.userDetails', '.user-detail-btn', function () {
		var details = JSON.parse($(this).attr('data-details') || '{}');
		Object.keys(details).forEach(function (key) {
			if (key === 'avatar') {
				$('#userDetailsModal .detail-avatar').attr('src', details[key] || '{{ asset('assets/img/avatar.png') }}');
				return;
			}
			$('#userDetailsModal .detail-' + key).text(details[key] || 'Not provided');
		});
		$('#userDetailsModal').modal('show');
	});

	$(document).off('click.userEdit', '.user-edit-btn').on('click.userEdit', '.user-edit-btn', function () {
		var details = JSON.parse($(this).attr('data-details') || '{}');
		$('#userEditForm').attr('action', '{{ url('users') }}/' + details.id);
		$('#editUserName').val(details.name || '');
		$('#editUserEmail').val(details.email || '');
		$('#editUserRole').val(details.role || '');
		$('#editUserPassword, #editUserPasswordConfirmation, #editUserAvatar').val('');
		$('#userEditModal').modal('show');
	});

	var createCameraButton = document.getElementById('user-create-camera-button');
	var createCameraModal = document.getElementById('user-create-camera-modal');
	var createCameraVideo = document.getElementById('user-create-camera-video');
	var createCameraCanvas = document.getElementById('user-create-camera-canvas');
	var createCameraPreview = document.getElementById('user-create-camera-preview');
	var createCameraInput = document.getElementById('createUserAvatar');
	var createAvatarFileName = document.getElementById('createUserAvatarFileName');
	var createAvatarPreview = document.getElementById('createUserAvatarPreview');
	var createGenderInput = document.getElementById('createUserGender');
	var createFirstNameInput = document.getElementById('createUserFirstName');
	var createCameraMessage = document.getElementById('user-create-camera-message');
	var createCameraCapture = document.getElementById('user-create-camera-capture');
	var createCameraRetake = document.getElementById('user-create-camera-retake');
	var createCameraUse = document.getElementById('user-create-camera-use');
	var createCameraStream = null;
	var createCameraPhoto = null;

	if (createCameraButton && createCameraModal) {
		function notifyFirstNameRequired() {
			var message = 'Please enter the First Name before using the camera.';
			if (window.Snackbar) {
				Snackbar.show({ text: message, duration: 4000, pos: 'top-right', backgroundColor: '#e8483f', textColor: '#ffffff' });
			} else {
				createCameraMessage.textContent = message;
			}
			if (createFirstNameInput) createFirstNameInput.focus();
		}

		function cameraFileName() {
			var firstName = createFirstNameInput ? createFirstNameInput.value.trim() : '';
			return (firstName.replace(/[^a-z0-9]+/gi, '-').replace(/^-+|-+$/g, '') || 'user') + '.jpg';
		}

		function changeDefaultAvatar(gender) {
			var avatar = gender === 'Male' ? '{{ asset('assets/img/maleperson.jpg') }}' : gender === 'Female' ? '{{ asset('assets/img/femaleperson.jpg') }}' : '{{ asset('assets/img/ADMIN.jpg') }}';
			createAvatarPreview.classList.remove('is-ready');
			createAvatarPreview.classList.add('is-changing');
			createAvatarPreview.src = avatar;
			setTimeout(function () {
				createAvatarPreview.classList.remove('is-changing');
				createAvatarPreview.classList.add('is-ready');
			}, 180);
		}

		createGenderInput.addEventListener('change', function () {
			if (!createCameraInput.files || !createCameraInput.files.length) changeDefaultAvatar(createGenderInput.value);
		});

		createCameraInput.addEventListener('change', function () {
			createAvatarFileName.textContent = createCameraInput.files && createCameraInput.files[0] ? createCameraInput.files[0].name : 'No file chosen';
			if (createCameraInput.files && createCameraInput.files[0]) {
				createAvatarPreview.src = URL.createObjectURL(createCameraInput.files[0]);
			}
		});

		function stopCreateCamera() {
			if (createCameraStream) {
				createCameraStream.getTracks().forEach(function (track) { track.stop(); });
				createCameraStream = null;
			}
			createCameraVideo.srcObject = null;
		}
		function resetCreateCamera() {
			createCameraVideo.style.display = 'block';
			createCameraPreview.style.display = 'none';
			createCameraCapture.style.display = 'inline-block';
			createCameraRetake.style.display = 'none';
			createCameraUse.style.display = 'none';
			createCameraPhoto = null;
		}
		function closeCreateCamera() {
			stopCreateCamera();
			resetCreateCamera();
			createCameraMessage.textContent = '';
			createCameraModal.classList.remove('is-open');
			createCameraModal.setAttribute('aria-hidden', 'true');
		}
		createCameraButton.addEventListener('click', async function () {
			if (!createFirstNameInput || !createFirstNameInput.value.trim()) {
				notifyFirstNameRequired();
				return;
			}
			if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
				createCameraMessage.textContent = 'Camera capture is not supported by this browser.';
				return;
			}
			createCameraModal.classList.add('is-open');
			createCameraModal.setAttribute('aria-hidden', 'false');
			resetCreateCamera();
			createCameraMessage.textContent = 'Allow camera access when your browser asks.';
			try {
				createCameraStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'user' } }, audio: false });
				createCameraVideo.srcObject = createCameraStream;
				createCameraMessage.textContent = '';
			} catch (error) {
				console.error('Unable to access camera:', error);
				createCameraMessage.textContent = 'Camera access was unavailable. Check browser permission and try again.';
				createCameraCapture.style.display = 'none';
			}
		});
		createCameraCapture.addEventListener('click', function () {
			if (!createCameraVideo.videoWidth) {
				createCameraMessage.textContent = 'The camera is still starting. Please try again.';
				return;
			}
			createCameraCanvas.width = createCameraVideo.videoWidth;
			createCameraCanvas.height = createCameraVideo.videoHeight;
			createCameraCanvas.getContext('2d').drawImage(createCameraVideo, 0, 0, createCameraCanvas.width, createCameraCanvas.height);
			createCameraCanvas.toBlob(function (blob) {
				if (!blob) return;
				createCameraPhoto = blob;
				createCameraPreview.src = URL.createObjectURL(blob);
				createCameraVideo.style.display = 'none';
				createCameraPreview.style.display = 'block';
				createCameraCapture.style.display = 'none';
				createCameraRetake.style.display = 'inline-block';
				createCameraUse.style.display = 'inline-block';
			}, 'image/jpeg', .9);
		});
		createCameraRetake.addEventListener('click', resetCreateCamera);
		createCameraUse.addEventListener('click', function () {
			if (!createCameraPhoto) return;
			var transfer = new DataTransfer();
			var file = new File([createCameraPhoto], cameraFileName(), { type: 'image/jpeg' });
			transfer.items.add(file);
			createCameraInput.files = transfer.files;
			createAvatarFileName.textContent = file.name;
			createAvatarPreview.src = URL.createObjectURL(createCameraPhoto);
			createCameraInput.dispatchEvent(new Event('change', { bubbles: true }));
			closeCreateCamera();
		});
		document.getElementById('user-create-camera-close').addEventListener('click', closeCreateCamera);
		document.getElementById('user-create-camera-cancel').addEventListener('click', closeCreateCamera);
		createCameraModal.addEventListener('click', function (event) { if (event.target === createCameraModal) closeCreateCamera(); });
		document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && createCameraModal.classList.contains('is-open')) closeCreateCamera(); });
	}
};

document.addEventListener('turbo:load', window.pharmacyUsersInit);
window.pharmacyUsersInit();
</script>
@endpush