@extends('admin.layouts.app')

<x-assets.tabulator />

@push('page-header')
<div class="col">
	<h3 class="page-title">Profile</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Profile</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-md-12">
		<div class="profile-header">
			<div class="row align-items-center">
				<div class="col-auto profile-image">
					<a href="#">
						<div class="profile-image-wrapper {{ auth()->user()->hasRole('super-admin') && auth()->user()->email_verified_at ? 'super-admin-wrapper' : '' }}">
							<img class="rounded-circle {{ auth()->user()->email_verified_at ? 'verified-account' : '' }} {{ auth()->user()->hasRole('super-admin') && auth()->user()->email_verified_at ? 'super-admin-verified' : '' }}" alt="User Image" src="{{ auth()->user()->hasRole('super-admin') ? asset('assets/img/ADMIN.jpg') : (!empty(auth()->user()->avatar) ? (in_array(auth()->user()->avatar, ['femaleperson.jpg', 'maleperson.jpg'], true) ? asset('assets/img/'.auth()->user()->avatar) : url('storage/system/profiles/'.auth()->user()->avatar)) : (in_array(strtolower((string) auth()->user()->gender), ['male', 'female'], true) ? asset('assets/img/'.strtolower(auth()->user()->gender).'person.jpg') : asset('assets/img/avatar.png'))) }}">
							@if(auth()->user()->email_verified_at)
								<span class="verification-badge {{ auth()->user()->hasRole('super-admin') ? 'super-admin-badge' : '' }}">
									@if(auth()->user()->hasRole('super-admin'))
										<i class="fas fa-hammer"></i>
									@else
										<i class="fas fa-check"></i>
									@endif
								</span>
							@endif
						</div>
					</a>
				</div>
				<div class="col ml-md-n2 profile-user-info">
					<h4 class="user-name mb-0">{{auth()->user()->name}}</h4>
					<h6 class="text-muted">{{auth()->user()->email}}</h6>
				</div>

			</div>
		</div>
		<div class="profile-menu">
			<ul class="nav nav-tabs nav-tabs-solid">
				<li class="nav-item">
					<a class="nav-link active" data-toggle="tab" href="#per_details_tab">About</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" data-toggle="tab" href="#security_tab">Security</a>
				</li>
			</ul>
		</div>
		<div class="tab-content profile-tab-cont">

			<!-- Personal Details Tab -->
			<div class="tab-pane fade show active" id="per_details_tab">

				<!-- Personal Details -->
				<div class="row">
					<div class="col-lg-12">
						<div class="card">
							<div class="card-body">
								<h5 class="card-title d-flex justify-content-between">
									<span>Personal Details</span>
									<a class="edit-link" data-toggle="modal" href="#edit_personal_details"><i class="fa fa-edit mr-1"></i>Edit</a>
								</h5>
								<div class="profile-details-list">
									<p class="mb-2"><span class="text-muted">Name:</span> {{auth()->user()->name}}</p>
									<p class="mb-2"><span class="text-muted">Email ID:</span> {{auth()->user()->email}}</p>
									<p class="mb-0"><span class="text-muted">User Role:</span>
										@foreach (auth()->user()->getRoleNames() as $role)
										{{$role}}
										@endforeach
									</p>
								</div>

							</div>
						</div>

						<!-- Edit Details Modal -->
						<div class="modal fade" id="edit_personal_details" aria-hidden="true" role="dialog">
							<div class="modal-dialog modal-dialog-centered" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title">Personal Details</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
										<form method="POST" enctype="multipart/form-data" action="{{route('profile.update',auth()->user())}}">
											@csrf
											<div class="row form-row">
												<div class="col-12">
													<div class="form-group">
														<label>Full Name</label>
														<input class="form-control" name="name" type="text" value="{{auth()->user()->name}}" placeholder="Full Name">
													</div>
												</div>
												<div class="col-12">
													<div class="form-group">
														<label>email</label>
														<input class="form-control" name="email" type="text" value="{{auth()->user()->email}}" placeholder="Email">
													</div>
												</div>
												@can('edit-role')
												<div class="col-12">
													<div class="form-group">
														<label>Role</label>
														<select class="form-control select edit_role" name="role">
															@foreach ($roles as $role)
																<option value="{{$role->name}}">{{$role->name}}</option>
															@endforeach
														</select>
													</div>
												</div>
												@endcan
												<div class="col-12">
													<div class="form-group">
														<label>User Avatar</label>
															<div class="profile-image-source dropdown">
																<input type="file" value="{{auth()->user()->avatar}}" class="profile-avatar-file" id="profile-avatar-input" name="avatar" accept="image/*">
																<button type="button" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-image mr-1"></i> Add photo</button>
																<div class="dropdown-menu profile-source-menu"><label for="profile-avatar-input"><i class="fa fa-folder-open-o mr-1"></i> Choose file</label><button type="button" id="profile-camera-button"><i class="fa fa-camera mr-1"></i> Use camera</button></div>
															</div>
															<span class="profile-avatar-file-name" id="profile-avatar-file-name">No file chosen</span>
													</div>
												</div>

											</div>
											<button type="submit" class="btn btn-primary btn-block">Save Changes</button>
										</form>
									</div>
								</div>
							</div>
						</div>
						<div class="profile-camera-modal" id="profile-camera-modal" aria-hidden="true"><div class="profile-camera-dialog" role="dialog" aria-modal="true" aria-labelledby="profile-camera-title"><div class="profile-camera-header"><h5 id="profile-camera-title" class="mb-0">Take profile photo</h5><button type="button" class="profile-camera-close" id="profile-camera-close" aria-label="Close camera">&times;</button></div><div class="profile-camera-body"><video id="profile-camera-video" autoplay playsinline></video><canvas id="profile-camera-canvas" hidden></canvas><img id="profile-camera-preview" alt="Captured profile preview"><p id="profile-camera-message"></p></div><div class="profile-camera-footer"><button type="button" class="btn btn-light" id="profile-camera-cancel">Close</button><button type="button" class="btn btn-primary" id="profile-camera-capture">Capture</button><button type="button" class="btn btn-secondary" id="profile-camera-retake">Retake</button><button type="button" class="btn btn-success" id="profile-camera-use">Use photo</button></div></div></div>
						<!-- /Edit Details Modal -->

					</div>


				</div>
				<!-- /Personal Details -->

			</div>
			<!-- /Personal Details Tab -->

			<!-- Change Password Tab -->
			<div id="security_tab" class="tab-pane fade">

				<!-- Security Table -->
				<div class="card">
					<div class="card-body">
						<h5 class="card-title">Security Settings</h5>
						<div class="table-responsive">
							<div class="pharma-table-toolbar" data-table-key="security-table">
								<div class="dataTables_filter">
									<label><input type="search" id="security-table-search" class="form-control form-control-sm" placeholder="Search..." aria-label="Search"></label>
								</div>
							</div>
							<table id="security-table" class="table table-hover mb-0">
								<thead>
									<tr>
										<th>Security Option</th>
										<th class="text-right">Action</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
											<strong>Password</strong>
										</td>
										<td class="text-right">
											<div class="btn-group">
												<button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
													<i class="fa fa-ellipsis-v"></i>
												</button>
												<div class="dropdown-menu dropdown-menu-right">
													<p class="dropdown-item-text px-3 py-2">Change your account password</p>
													<div class="dropdown-divider"></div>
													<button type="button" class="dropdown-item" data-toggle="modal" data-target="#change_password_modal">
														<i class="fa fa-edit mr-2"></i>Change Password
													</button>
												</div>
											</div>
										</td>
									</tr>
									@if(!auth()->user()->email_verified_at)
									<tr>
										<td>
											<strong>Account Verification</strong>
										</td>
										<td class="text-right">
											<div class="btn-group">
												<button type="button" class="btn btn-sm btn-secondary dropdown-toggle security-action-button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
													<i class="fa fa-ellipsis-v"></i>
												</button>
												<div class="dropdown-menu dropdown-menu-right">
													<p class="dropdown-item-text px-3 py-2">Verify your account email address</p>
													<div class="dropdown-divider"></div>
													<button type="button" class="dropdown-item" data-toggle="modal" data-target="#verify_account_modal">
														<i class="fa fa-check-circle mr-2"></i>Verify Account
													</button>
												</div>
											</div>
										</td>
									</tr>
									@endif
									@if(auth()->user()->email_verified_at)
									<tr>
										<td><strong>Step Two Verification</strong></td>
										<td class="text-right">
											<div class="btn-group">
												<button type="button" class="btn btn-sm btn-secondary dropdown-toggle security-action-button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Two-step verification actions"><i class="fa fa-ellipsis-v"></i></button>
												<div class="dropdown-menu dropdown-menu-right">
													<p class="dropdown-item-text px-3 py-2">Use a code sent to your verified email when logging in.</p>
													<div class="dropdown-divider"></div>
													@if(auth()->user()->two_factor_enabled)
													<form method="POST" action="{{ route('two-factor.disable') }}">
														@csrf
														<button type="submit" class="dropdown-item text-danger"><i class="fa fa-toggle-off mr-2"></i>Turn Off</button>
													</form>
													@else
													<button type="button" class="dropdown-item" data-toggle="modal" data-target="#two_factor_modal"><i class="fa fa-toggle-on mr-2"></i>Turn On</button>
													@endif
												</div>
											</div>
										</td>
									</tr>
									@endif
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<!-- /Change Password Tab -->

			<!-- Change Password Modal -->
			<div class="modal fade" id="change_password_modal" aria-hidden="true" role="dialog">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">Change Password</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<form method="POST" action="{{route('update-password',auth()->user())}}" id="change_password_form">
								@csrf
								@method("PUT")
								<div class="form-group">
									<label>Current Password</label>
									<input type="password" name="current_password" class="form-control" placeholder="enter your current password">
								</div>
								<div class="form-group">
									<label>New Password</label>
									<input type="password" name="password" class="form-control" placeholder="enter your new password">
								</div>
								<div class="form-group">
									<label>Confirm Password</label>
									<input type="password" name="password_confirmation" class="form-control" placeholder="repeat your new password">
								</div>
							</form>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
							<button type="submit" form="change_password_form" class="btn btn-primary">Save Changes</button>
						</div>
					</div>
				</div>
			</div>
			<!-- /Change Password Modal -->

			<!-- Verify Account Modal -->
			<div class="modal fade" id="verify_account_modal" aria-hidden="true" role="dialog">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">Verify Account</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<div id="verification_prompt">
								<p class="mb-2">Are you ready to get verified now?</p>
								<p class="text-muted mb-0">Click the button below to start.</p>
							</div>
							<div id="verification_code_step" class="d-none">
								<p class="mb-3">Enter the 6-digit code sent to {{ auth()->user()->email }}</p>
								<div class="verification-code-inputs" aria-label="Verification code">
									@for ($index = 0; $index < 6; $index++)
										<input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" required aria-label="Digit {{ $index + 1 }}">
									@endfor
								</div>
								<div id="verification_error" class="alert alert-danger d-none mb-0"></div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
							<form method="POST" action="{{ route('verification.send') }}" id="send_verification_form">
								@csrf
								<button type="submit" class="btn btn-primary"><i class="fa fa-check-circle mr-1"></i>Verify Account</button>
							</form>
							<form method="POST" action="{{ route('verification.verify') }}" id="verify_code_form" class="d-none">
								@csrf
								<input type="hidden" name="code" id="verification_code">
								<button type="submit" class="btn btn-primary">Verify</button>
							</form>
						</div>
					</div>
				</div>
			</div>
			<!-- /Verify Account Modal -->

			@if(auth()->user()->email_verified_at && !auth()->user()->two_factor_enabled)
			<div class="modal fade" id="two_factor_modal" aria-hidden="true" role="dialog">
				<div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content">
					<div class="modal-header"><h5 class="modal-title">Enable Step Two Verification</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
					<div class="modal-body">
						<p id="two_factor_message">Send a six-digit code to {{ auth()->user()->email }} to enable login verification.</p>
						<div id="two_factor_code_step" class="d-none"><input id="two_factor_code" class="form-control" inputmode="numeric" maxlength="6" placeholder="Enter the 6-digit code"></div>
						<div id="two_factor_error" class="alert alert-danger d-none mt-3 mb-0"></div>
					</div>
					<div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary" id="send_two_factor_code">Send Code</button><button type="button" class="btn btn-primary d-none" id="enable_two_factor">Enable</button></div>
				</div></div>
			</div>
			@endif

		</div>
	</div>
</div>
@endsection

@push('page-css')
<style>
#verify_account_modal .verification-code-inputs { display: flex; gap: .5rem; }
#verify_account_modal .verification-code-inputs input { width: 100%; min-width: 0; height: 3rem; text-align: center; font-size: 1.25rem; border: 1px solid #cbd5e1; border-radius: .35rem; }
#verify_account_modal .verification-code-inputs input:focus { border-color: #669df6; outline: 0; box-shadow: 0 0 0 .2rem rgba(102,157,246,.2); }
.profile-avatar-file { display: none; }
.profile-image-source { display: inline-flex; }
.profile-avatar-file-name { display: inline-flex; align-items: center; max-width: 240px; margin-left: 8px; color: #64748b; font-size: 13px; vertical-align: middle; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.profile-source-menu { min-width: 180px; padding: 6px; }
.profile-source-menu button, .profile-source-menu label { display: block; width: 100%; margin: 0; padding: 9px 12px; border: 0; background: transparent; color: #344054; text-align: left; cursor: pointer; font-weight: 400; }
.profile-source-menu button:hover, .profile-source-menu label:hover { background: #f1f5f9; }
.profile-camera-modal { display: none; position: fixed; inset: 0; z-index: 1070; padding: 20px; background: rgba(15, 23, 42, .78); align-items: center; justify-content: center; }
.profile-camera-modal.is-open { display: flex; }
.profile-camera-dialog { width: min(600px, 100%); overflow: hidden; background: #fff; border-radius: 8px; box-shadow: 0 12px 40px rgba(15, 23, 42, .3); }
.profile-camera-header, .profile-camera-footer { display: flex; align-items: center; padding: 14px 18px; }
.profile-camera-header { justify-content: space-between; border-bottom: 1px solid #e5e7eb; }
.profile-camera-footer { justify-content: flex-end; gap: 8px; border-top: 1px solid #e5e7eb; }
.profile-camera-close { padding: 0; border: 0; background: transparent; color: #64748b; font-size: 28px; line-height: 1; cursor: pointer; }
.profile-camera-body { padding: 18px; text-align: center; }
.profile-camera-body video, .profile-camera-body img { display: block; width: 100%; max-height: 60vh; border-radius: 6px; background: #111827; object-fit: contain; }
.profile-camera-body img { display: none; }
#profile-camera-retake, #profile-camera-use { display: none; }
body.dark-mode #edit_personal_details .modal-content { background: #1c2025; border-color: #475569; color: #f8fafc; }
body.dark-mode #edit_personal_details .modal-header { border-bottom-color: #475569; }
body.dark-mode #edit_personal_details label, body.dark-mode #edit_personal_details .modal-title { color: #e2e8f0; }
body.dark-mode #edit_personal_details .form-control { background: #111827; border-color: #475569; color: #f8fafc; }
body.dark-mode #edit_personal_details .form-control::placeholder { color: #94a3b8; }
body.dark-mode #edit_personal_details .profile-image-source > .btn { border-color: #69d9aa; color: #69d9aa; }
body.dark-mode #edit_personal_details .profile-image-source > .btn:hover, body.dark-mode #edit_personal_details .profile-image-source > .btn:focus { background: #69d9aa; border-color: #69d9aa; color: #10231d; }
body.dark-mode #edit_personal_details .profile-avatar-file-name { color: #cbd5e1; }
body.dark-mode #edit_personal_details .profile-source-menu { background: #252b33; border-color: #475569; }
body.dark-mode #edit_personal_details .profile-source-menu button, body.dark-mode #edit_personal_details .profile-source-menu label { color: #e2e8f0; }
body.dark-mode #edit_personal_details .profile-source-menu button:hover, body.dark-mode #edit_personal_details .profile-source-menu label:hover { background: #334155; color: #69d9aa; }
</style>
@endpush

@push('page-js')
<script>
$(function () {
	// Security Settings grid — the same Tabulator table used on the Purchase page.
	var securityTable = null;
	$('a[href="#security_tab"]').on('shown.bs.tab', function () {
		if (!window.PharmaTabulator) return;

		if (securityTable) {
			securityTable.redraw(true);
			return;
		}

		securityTable = window.PharmaTabulator.fromDom({
			el: 'security-table',
			key: 'security-table',
			preserveHtml: true,
			pageLength: 10,
			fields: ['option', 'action']
		});
		if (!securityTable) return;

		// Match the Purchase table's Action column (not sortable, fixed width, centered).
		securityTable.updateColumnDefinition('action', {
			headerSort: false,
			width: 110,
			hozAlign: 'center'
		});

		var securitySearchTimer = null;
		$('#security-table-search').on('input', function () {
			var value = $.trim(this.value).toLowerCase();
			clearTimeout(securitySearchTimer);
			securitySearchTimer = setTimeout(function () {
				securityTable.setFilter(function (row) {
					if (!value) return true;
					return String(row.option || '').replace(/<[^>]+>/g, ' ').toLowerCase().indexOf(value) !== -1;
				});
			}, 350);
		});
	});

	var profileCameraButton = document.getElementById('profile-camera-button');
	var profileCameraModal = document.getElementById('profile-camera-modal');
	var profileCameraInput = document.getElementById('profile-avatar-input');
	var profileCameraFileName = document.getElementById('profile-avatar-file-name');
	var profileCameraVideo = document.getElementById('profile-camera-video');
	var profileCameraCanvas = document.getElementById('profile-camera-canvas');
	var profileCameraPreview = document.getElementById('profile-camera-preview');
	var profileCameraMessage = document.getElementById('profile-camera-message');
	var profileCameraCapture = document.getElementById('profile-camera-capture');
	var profileCameraRetake = document.getElementById('profile-camera-retake');
	var profileCameraUse = document.getElementById('profile-camera-use');
	var profileCameraStream = null;
	var profileCameraPhoto = null;
	function profilePhotoName() { var fullName = '{{ addslashes(auth()->user()->name) }}'.trim(); var firstName = fullName.split(/\s+/)[0] || 'user'; return firstName.replace(/[^a-z0-9_-]/gi, '') + '.jpg'; }
	function resetProfileCamera() { profileCameraVideo.style.display = 'block'; profileCameraPreview.style.display = 'none'; profileCameraCapture.style.display = 'inline-block'; profileCameraRetake.style.display = 'none'; profileCameraUse.style.display = 'none'; profileCameraPhoto = null; }
	function stopProfileCamera() { if (profileCameraStream) { profileCameraStream.getTracks().forEach(function (track) { track.stop(); }); profileCameraStream = null; } profileCameraVideo.srcObject = null; }
	function closeProfileCamera() { stopProfileCamera(); resetProfileCamera(); profileCameraMessage.textContent = ''; profileCameraModal.classList.remove('is-open'); profileCameraModal.setAttribute('aria-hidden', 'true'); }
	if (profileCameraInput) profileCameraInput.addEventListener('change', function () { profileCameraFileName.textContent = profileCameraInput.files && profileCameraInput.files[0] ? profileCameraInput.files[0].name : 'No file chosen'; });
	if (profileCameraButton) profileCameraButton.addEventListener('click', async function () { if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) { profileCameraMessage.textContent = 'Camera capture is not supported by this browser.'; return; } profileCameraModal.classList.add('is-open'); profileCameraModal.setAttribute('aria-hidden', 'false'); resetProfileCamera(); profileCameraMessage.textContent = 'Allow camera access when your browser asks.'; try { profileCameraStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'user' } }, audio: false }); profileCameraVideo.srcObject = profileCameraStream; profileCameraMessage.textContent = ''; } catch (error) { profileCameraMessage.textContent = 'Camera access was unavailable. Check browser permission and try again.'; profileCameraCapture.style.display = 'none'; } });
	if (profileCameraCapture) profileCameraCapture.addEventListener('click', function () { if (!profileCameraVideo.videoWidth) { profileCameraMessage.textContent = 'The camera is still starting. Please try again.'; return; } profileCameraCanvas.width = profileCameraVideo.videoWidth; profileCameraCanvas.height = profileCameraVideo.videoHeight; profileCameraCanvas.getContext('2d').drawImage(profileCameraVideo, 0, 0, profileCameraCanvas.width, profileCameraCanvas.height); profileCameraCanvas.toBlob(function (blob) { if (!blob) return; profileCameraPhoto = blob; profileCameraPreview.src = URL.createObjectURL(blob); profileCameraVideo.style.display = 'none'; profileCameraPreview.style.display = 'block'; profileCameraCapture.style.display = 'none'; profileCameraRetake.style.display = 'inline-block'; profileCameraUse.style.display = 'inline-block'; }, 'image/jpeg', .9); });
	if (profileCameraRetake) profileCameraRetake.addEventListener('click', resetProfileCamera);
	if (profileCameraUse) profileCameraUse.addEventListener('click', function () { if (!profileCameraPhoto) return; var file = new File([profileCameraPhoto], profilePhotoName(), { type: 'image/jpeg' }); var transfer = new DataTransfer(); transfer.items.add(file); profileCameraInput.files = transfer.files; profileCameraFileName.textContent = file.name; profileCameraInput.dispatchEvent(new Event('change', { bubbles: true })); closeProfileCamera(); });
	if (profileCameraModal) { document.getElementById('profile-camera-close').addEventListener('click', closeProfileCamera); document.getElementById('profile-camera-cancel').addEventListener('click', closeProfileCamera); profileCameraModal.addEventListener('click', function (event) { if (event.target === profileCameraModal) closeProfileCamera(); }); document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && profileCameraModal.classList.contains('is-open')) closeProfileCamera(); }); }

	var modal = $('#verify_account_modal');
	var sendForm = $('#send_verification_form');
	var verifyForm = $('#verify_code_form');
	var codeStep = $('#verification_code_step');
	var codeInputs = codeStep.find('.verification-code-inputs input');
	var error = $('#verification_error');

	sendForm.on('submit', function (event) {
		event.preventDefault();
		event.stopPropagation();
		var button = sendForm.find('button[type="submit"]');
		button.prop('disabled', true).text('Sending...');

		$.ajax({
			url: sendForm.attr('action'),
			method: 'POST',
			data: sendForm.serialize(),
			headers: { Accept: 'application/json' }
		}).done(function () {
			$('#verification_prompt').addClass('d-none');
			codeStep.removeClass('d-none');
			sendForm.addClass('d-none');
			verifyForm.removeClass('d-none');
			codeInputs.first().trigger('focus');
		}).fail(function (response) {
			error.text(response.responseJSON && response.responseJSON.message ? response.responseJSON.message : 'Unable to send the verification code.').removeClass('d-none');
			button.prop('disabled', false).html('<i class="fa fa-check-circle mr-1"></i>Verify Account');
		});
	});

	verifyForm.on('submit', function (event) {
		event.preventDefault();
		event.stopPropagation();
		$('#verification_code').val(codeInputs.map(function () { return this.value; }).get().join(''));
		var button = verifyForm.find('button[type="submit"]');
		button.prop('disabled', true).text('Verifying...');

		$.ajax({
			url: verifyForm.attr('action'),
			method: 'POST',
			data: verifyForm.serialize(),
			headers: { Accept: 'application/json' }
		}).done(function () {
			window.location.href = '{{ route('profile') }}';
		}).fail(function (response) {
			error.text(response.responseJSON && response.responseJSON.message ? response.responseJSON.message : 'The verification code is invalid or expired.').removeClass('d-none');
			button.prop('disabled', false).text('Verify');
		});
	});

	codeInputs.on('input', function () {
		this.value = this.value.replace(/\D/g, '').slice(0, 1);
		if (this.value) codeInputs.eq(codeInputs.index(this) + 1).trigger('focus');
	}).on('keydown', function (event) {
		if (event.key === 'Backspace' && !this.value) codeInputs.eq(codeInputs.index(this) - 1).trigger('focus');
	}).on('paste', function (event) {
		event.preventDefault();
		var pastedCode = (event.originalEvent.clipboardData || window.clipboardData).getData('text')
			.replace(/\D/g, '')
			.slice(0, codeInputs.length);
		codeInputs.val('');
		pastedCode.split('').forEach(function (digit, index) {
			codeInputs.eq(index).val(digit);
		});
		codeInputs.eq(Math.max(0, pastedCode.length - 1)).trigger('focus');
	});

	modal.on('hidden.bs.modal', function () {
		$('#verification_prompt').removeClass('d-none');
		codeStep.addClass('d-none');
		sendForm.removeClass('d-none');
		verifyForm.addClass('d-none');
		error.addClass('d-none').text('');
		codeInputs.val('');
		sendForm.find('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-check-circle mr-1"></i>Verify Account');
	});

	var twoFactorModal = $('#two_factor_modal');
	$('#send_two_factor_code').on('click', function () {
		var button = $(this);
		button.prop('disabled', true).text('Sending...');
		$.post('{{ route('two-factor.send') }}', {_token: '{{ csrf_token() }}'})
			.done(function () {
				$('#two_factor_message').text('Enter the six-digit code sent to your verified email.');
				$('#two_factor_code_step').removeClass('d-none');
				button.addClass('d-none');
				$('#enable_two_factor').removeClass('d-none').trigger('focus');
			})
			.fail(function (response) {
				$('#two_factor_error').text(response.responseJSON && response.responseJSON.message ? response.responseJSON.message : 'Unable to send the code.').removeClass('d-none');
				button.prop('disabled', false).text('Send Code');
			});
	});

	$('#enable_two_factor').on('click', function () {
		var button = $(this);
		button.prop('disabled', true).text('Checking...');
		$.post('{{ route('two-factor.enable') }}', {_token: '{{ csrf_token() }}', code: $('#two_factor_code').val()})
			.done(function () { window.location.href = '{{ route('profile') }}'; })
			.fail(function (response) {
				$('#two_factor_error').text(response.responseJSON && response.responseJSON.message ? response.responseJSON.message : 'The code is invalid or expired.').removeClass('d-none');
				button.prop('disabled', false).text('Enable');
			});
	});

	twoFactorModal.on('hidden.bs.modal', function () {
		$('#two_factor_message').text('Send a six-digit code to {{ auth()->user()->email }} to enable login verification.');
		$('#two_factor_code').val('');
		$('#two_factor_code_step, #enable_two_factor, #two_factor_error').addClass('d-none');
		$('#send_two_factor_code').removeClass('d-none').prop('disabled', false).text('Send Code');
	});
});
</script>
@endpush