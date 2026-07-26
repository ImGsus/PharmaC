@extends('admin.layouts.plain')

@section('content')
<h1>Login</h1>
<p class="account-subtitle">Access to our dashboard</p>
<div class="login-actions text-center">
    <button type="button" id="loginThemeToggle" class="night-mode-toggle" aria-label="Toggle night mode">
        <span class="toggle-icon">🌙</span>
        <span class="toggle-text">Night Mode</span>
    </button>
</div>
@if (session('login_error'))
<x-alerts.danger :error="session('login_error')" />
@endif
<!-- Form -->
<form action="{{route('login')}}" method="post">
	@csrf
	<div class="form-group">
		<input class="form-control" name="email" type="text" placeholder="Email" autocomplete="username">
	</div>
	<div class="form-group password-group">
		<input id="loginPassword" class="form-control" name="password" type="password" placeholder="Password" autocomplete="current-password">
		<button id="passwordVisibilityToggle" type="button" class="password-toggle-btn" aria-label="Toggle password visibility">Show</button>
	</div>
	<div class="form-group">
		<button class="btn btn-primary btn-block" type="submit">Login</button>
	</div>
</form>
<!-- /Form -->

<div class="text-center forgotpass"><a href="{{route('password.request')}}">Forgot Password?</a></div>
<div class="text-center dont-have">Dont have an account? <a href="{{route('register')}}">Register</a></div>
@endsection

@push('page-css')
<style>
:root {
    --night-toggle-bg: #4b5563;
    --night-toggle-color: #ffffff;
    --night-toggle-hover-bg: #111827;
}
.login-actions {
    margin-bottom: 1rem;
}
.night-mode-toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.45rem 0.9rem;
    border: 0;
    border-radius: 999px;
    background: var(--night-toggle-bg);
    color: var(--night-toggle-color);
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 6px 16px rgba(17, 24, 39, 0.18);
    transition: all 0.35s ease;
}
.night-mode-toggle:hover {
    background: var(--night-toggle-hover-bg);
    transform: translateY(-1px);
}
.night-mode-toggle .toggle-icon {
    font-size: 1rem;
    line-height: 1;
}
.password-group {
    position: relative;
}
.password-toggle-btn {
    position: absolute;
    top: 50%;
    right: 15px;
    transform: translateY(-50%);
    border: none;
    background: transparent;
    color: #4c4c4c;
    font-weight: 600;
    padding: 0;
    cursor: pointer;
}
.password-toggle-btn:hover {
    color: #000;
}
body.dark-mode {
    background: #0f1720;
    color: #f1f5f9;
    transition: background-color 0.4s ease, color 0.4s ease;
}
body.dark-mode .login-wrapper .loginbox {
    background-color: #111827;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.55);
    border-color: rgba(148, 163, 184, 0.2);
}
body.dark-mode .login-wrapper .loginbox .login-left {
    background: linear-gradient(rgba(12, 74, 110, 0.9), rgba(2, 81, 115, 0.9)), url('../img/img-01.jpg') center/cover no-repeat;
}
body.dark-mode .login-wrapper .loginbox .login-right h1,
body.dark-mode .account-subtitle,
body.dark-mode .login-wrapper .loginbox .forgotpass a,
body.dark-mode .login-wrapper .loginbox .dont-have,
body.dark-mode .login-wrapper .loginbox .dont-have a,
body.dark-mode .login-wrapper .loginbox .password-toggle-btn {
    color: #f8fafc;
}
body.dark-mode .form-control {
    background: #1f2937;
    border-color: #334155;
    color: #f8fafc;
}
body.dark-mode .form-control::placeholder {
    color: #cbd5e1;
}
body.dark-mode .btn-primary {
    background-color: #2563eb;
    border-color: #2563eb;
}
body.dark-mode .night-mode-toggle {
    background: #f8fafc;
    color: #111827;
}
body.dark-mode .night-mode-toggle:hover {
    background: #ffffff;
}
</style>
@endpush

@push('page-js')
<script>
$(function() {
    $('#passwordVisibilityToggle').on('click', function() {
        var $password = $('#loginPassword');
        var type = $password.attr('type') === 'password' ? 'text' : 'password';
        $password.attr('type', type);
        $(this).text(type === 'password' ? 'Show' : 'Hide');
    });
});
</script>
@endpush