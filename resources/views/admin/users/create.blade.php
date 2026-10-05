@extends('admin.layouts.app')

@push('page-css')
<style>
    .user-create-card { border: 1px solid #d9e1eb; border-radius: 8px; box-shadow: 0 12px 28px rgba(31, 45, 61, .08); }
    .user-create-card .card-header { padding: 22px 28px; background: #fff; border-bottom: 1px solid #edf0f4; }
    .user-create-card .card-body { padding: 28px; }
    .user-create-card .form-group { margin-bottom: 22px; }
    .user-create-card label { color: #344054; font-weight: 500; }
    .user-create-card .form-control { min-height: 44px; border-color: #cfd7e3; border-radius: 5px; }
    .user-create-card .submit-btn { min-width: 150px; border-radius: 22px; }
    .image-source-picker { position: relative; display: inline-flex; }
    .image-source-picker .source-menu { min-width: 180px; padding: 6px; }
    .image-source-picker .source-menu button, .image-source-picker .source-menu label { display: block; width: 100%; margin: 0; padding: 9px 12px; border: 0; background: transparent; color: #344054; text-align: left; cursor: pointer; font-weight: 400; }
    .image-source-picker .source-menu button:hover, .image-source-picker .source-menu label:hover { background: #f1f5f9; }
    .user-avatar-file { display: none; }
    .user-image-file-name { display: inline-flex; align-items: center; max-width: 220px; margin-left: 8px; color: #64748b; font-size: 13px; vertical-align: middle; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .user-camera-modal { display: none; position: fixed; inset: 0; z-index: 1060; padding: 20px; background: rgba(15, 23, 42, .78); align-items: center; justify-content: center; }
    .user-camera-modal.is-open { display: flex; }
    .user-camera-dialog { width: min(560px, 100%); overflow: hidden; background: #fff; border-radius: 8px; box-shadow: 0 12px 40px rgba(15, 23, 42, .3); }
    .user-camera-header, .user-camera-footer { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; }
    .user-camera-header { border-bottom: 1px solid #e5e7eb; }
    .user-camera-footer { justify-content: flex-end; gap: 8px; border-top: 1px solid #e5e7eb; }
    .user-camera-close { padding: 0; border: 0; background: transparent; color: #64748b; font-size: 28px; line-height: 1; cursor: pointer; }
    .user-camera-body { padding: 16px; text-align: center; }
    .user-camera-body video, .user-camera-body img { display: block; width: 100%; max-height: 60vh; border-radius: 6px; background: #111827; object-fit: contain; }
    .user-camera-body img { display: none; }
    .user-camera-message { margin: 12px 0 0; color: #64748b; }
    #user-camera-retake, #user-camera-use { display: none; }
    @media (max-width: 576px) { .user-create-card .card-body { padding: 20px; } .image-source-picker { width: 100%; } .image-source-picker > .btn { width: 100%; } }
</style>
@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Create User</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item active">Dashboard</li>
	</ul>
</div>
@endpush

@section('content')

<div class="row">
    <div class="col-md-12 col-lg-12">
    
        <div class="card card-table user-create-card">
            <div class="card-header">
                <h4 class="card-title mb-0">Add User</h4>
            </div>
            <div class="card-body">
                <div>
                    <form method="POST" enctype="multipart/form-data" action="{{route('users.store')}}">
                        @csrf
                        <div class="row form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" name="name" class="form-control" placeholder="John Doe">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control" placeholder="example@gmail.com">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Role</label>
                                    <div class="form-group">
                                        <select class="select2 form-select form-control" name="role">
                                            @foreach ($roles as $role)
                                                <option value="{{$role->name}}">{{$role->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Picture</label>
                                    <div class="image-source-picker dropdown">
                                        <input type="file" name="avatar" id="user-avatar-input" class="user-avatar-file" accept="image/*">
                                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-image mr-1"></i> Add photo</button>
                                        <div class="dropdown-menu source-menu">
                                            <label for="user-avatar-input"><i class="fa fa-folder-open-o mr-1"></i> Choose file</label>
                                            <button type="button" id="user-camera-button"><i class="fa fa-camera mr-1"></i> Use camera</button>
                                        </div>
                                    </div>
                                    <span class="user-image-file-name" id="user-avatar-file-name">No file chosen</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Password</label>
                                            <input type="password" name="password" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Confirm Password</label>
                                            <input type="password" name="password_confirmation" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center pt-2"><button type="submit" class="btn btn-primary submit-btn">Save Changes</button></div>
                    </form>
                </div>
            </div>
        </div>
        
    </div>

    
</div>

<div class="user-camera-modal" id="user-camera-modal" aria-hidden="true">
    <div class="user-camera-dialog" role="dialog" aria-modal="true" aria-labelledby="user-camera-title">
        <div class="user-camera-header"><h5 id="user-camera-title" class="mb-0">Take user photo</h5><button type="button" class="user-camera-close" id="user-camera-close" aria-label="Close camera">&times;</button></div>
        <div class="user-camera-body"><video id="user-camera-video" autoplay playsinline></video><canvas id="user-camera-canvas" hidden></canvas><img id="user-camera-preview" alt="Captured user preview"><p class="user-camera-message" id="user-camera-message"></p></div>
        <div class="user-camera-footer"><button type="button" class="btn btn-light" id="user-camera-cancel">Close</button><button type="button" class="btn btn-primary" id="user-camera-capture">Capture</button><button type="button" class="btn btn-secondary" id="user-camera-retake">Retake</button><button type="button" class="btn btn-success" id="user-camera-use">Use photo</button></div>
    </div>
</div>

@endsection

@push('page-js')
<script>
(function () {
    var button = document.getElementById('user-camera-button'), modal = document.getElementById('user-camera-modal'), video = document.getElementById('user-camera-video'), canvas = document.getElementById('user-camera-canvas'), preview = document.getElementById('user-camera-preview'), input = document.getElementById('user-avatar-input'), fileName = document.getElementById('user-avatar-file-name'), message = document.getElementById('user-camera-message'), capture = document.getElementById('user-camera-capture'), retake = document.getElementById('user-camera-retake'), usePhotoButton = document.getElementById('user-camera-use'), stream = null, photo = null;
    if (!button || !modal) return;
    input.addEventListener('change', function () { fileName.textContent = input.files && input.files[0] ? input.files[0].name : 'No file chosen'; });
    function stop() { if (stream) { stream.getTracks().forEach(function (track) { track.stop(); }); stream = null; } video.srcObject = null; }
    function reset() { video.style.display = 'block'; preview.style.display = 'none'; capture.style.display = 'inline-block'; retake.style.display = 'none'; usePhotoButton.style.display = 'none'; photo = null; }
    function close() { stop(); reset(); message.textContent = ''; modal.classList.remove('is-open'); modal.setAttribute('aria-hidden', 'true'); }
    function cameraFileName() { var firstNameInput = document.querySelector('input[name="first_name"], input[name="name"]'); var firstName = firstNameInput ? firstNameInput.value.trim() : ''; return (firstName.replace(/[^a-z0-9]+/gi, '-').replace(/^-+|-+$/g, '') || 'user') + '.jpg'; }
    async function open() { var firstNameInput = document.querySelector('input[name="first_name"], input[name="name"]'); if (!firstNameInput || !firstNameInput.value.trim()) { var requiredMessage = 'Please enter the First Name before using the camera.'; if (window.Snackbar) { Snackbar.show({ text: requiredMessage, duration: 4000, pos: 'top-right', backgroundColor: '#e8483f', textColor: '#ffffff' }); } else { message.textContent = requiredMessage; } if (firstNameInput) firstNameInput.focus(); return; } if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) { window.openUserPhotoPickerFallback(input); return; } modal.classList.add('is-open'); modal.setAttribute('aria-hidden', 'false'); reset(); message.textContent = 'Allow camera access when your browser asks.'; try { stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'user' } }, audio: false }); video.srcObject = stream; message.textContent = ''; } catch (error) { console.error('Unable to access camera:', error); message.textContent = 'Camera access was unavailable. Check browser permission and try again.'; capture.style.display = 'none'; } }
    function takePhoto() { if (!video.videoWidth) { message.textContent = 'The camera is still starting. Please try again.'; return; } canvas.width = video.videoWidth; canvas.height = video.videoHeight; canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height); canvas.toBlob(function (blob) { if (!blob) return; photo = blob; preview.src = URL.createObjectURL(blob); video.style.display = 'none'; preview.style.display = 'block'; capture.style.display = 'none'; retake.style.display = 'inline-block'; usePhotoButton.style.display = 'inline-block'; }, 'image/jpeg', .9); }
    button.addEventListener('click', open); capture.addEventListener('click', takePhoto); retake.addEventListener('click', reset);
    usePhotoButton.addEventListener('click', function () { if (!photo) return; var file = new File([photo], cameraFileName(), { type: 'image/jpeg' }); var transfer = new DataTransfer(); transfer.items.add(file); input.files = transfer.files; fileName.textContent = file.name; input.dispatchEvent(new Event('change', { bubbles: true })); close(); });
    document.getElementById('user-camera-close').addEventListener('click', close); document.getElementById('user-camera-cancel').addEventListener('click', close); modal.addEventListener('click', function (event) { if (event.target === modal) close(); }); document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && modal.classList.contains('is-open')) close(); });
})();
</script>
@endpush