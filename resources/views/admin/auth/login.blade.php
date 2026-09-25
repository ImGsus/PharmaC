@extends('admin.layouts.plain')

@section('content')
<h1>Login</h1>
<p class="account-subtitle">Access to our dashboard</p>
@if (session('two_factor_prompt') || session('two_factor_error'))
<div class="login-validation-alerts" style="position: static; width: 100%; margin-bottom: 1rem;">
    @if (session('two_factor_prompt'))<div class="alert alert-info">{{ session('two_factor_prompt') }}</div>@endif
    @if (session('two_factor_error'))<div class="alert alert-danger">{{ session('two_factor_error') }}</div>@endif
</div>
<form action="{{ route('login.two-factor') }}" method="POST" class="animate__animated animate__fadeInUp">
    @csrf
    <div class="form-group login-field">
        <i class="fas fa-shield-alt login-field-icon" aria-hidden="true"></i>
        <input class="form-control" name="code" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="6-digit email code" autocomplete="one-time-code" required>
    </div>
    <button class="btn btn-primary btn-block" type="submit">Verify and Login</button>
</form>
@else
@if (session('login_error'))
<div id="loginServerValidationAlert" class="login-validation-alerts">
    <x-alerts.danger :error="session('login_error')" />
</div>
@endif
<div id="loginValidationAlerts" class="login-validation-alerts" aria-live="polite" aria-atomic="true"></div>
<!-- Form -->
<form id="loginForm" class="animate__animated animate__fadeInUp animate__delay-1s" action="{{route('login')}}" method="post">
	@csrf
    <div class="form-group login-field">
        <i class="fas fa-envelope login-field-icon" aria-hidden="true"></i>
        <input id="loginEmail" class="form-control" name="email" type="text" placeholder="Email" autocomplete="username" aria-invalid="false" value="{{ old('email') }}">
	</div>
    <div class="form-group password-group login-field">
        <i class="fas fa-lock login-field-icon" aria-hidden="true"></i>
		<input id="loginPassword" class="form-control" name="password" type="password" placeholder="Password" autocomplete="current-password" aria-invalid="false" value="{{ old('password') }}">
        <button id="passwordVisibilityToggle" type="button" class="password-toggle-btn" aria-label="Show password" aria-pressed="false">
            <i class="fas fa-eye" aria-hidden="true"></i>
        </button>
	</div>
	<div class="form-group">
		<button class="btn btn-primary btn-block" type="submit">Login</button>
	</div>
</form>
@endif
<!-- /Form -->

<div class="text-center forgotpass"><a href="{{route('password.request')}}">Forgot Password?</a></div>
<div class="text-center dont-have">Dont have an account? <a href="{{route('register')}}">Register</a></div>
@endsection

@push('page-css')
<style>
.login-actions {
    position: fixed;
    top: 1.25rem;
    right: 1.25rem;
    margin-bottom: 0;
    z-index: 3;
}
.login-wrapper .loginbox {
    position: relative;
}
.login-validation-alerts {
    position: absolute;
    left: calc(100% + 20px);
    top: 20px;
    width: 240px;
    z-index: 2;
}
.login-validation-alerts .alert {
    margin-bottom: 0.5rem;
    width: 100%;
    background: #ef4444;
    border: 1px solid #dc2626;
    color: #fff;
    border-radius: 10px;
    box-shadow: 0 10px 18px rgba(239, 68, 68, 0.18);
    position: relative;
    padding: 0.8rem 2.5rem 0.8rem 2.8rem;
}
.login-validation-alerts .alert::before {
    content: '!';
    position: absolute;
    left: 0.9rem;
    top: 50%;
    transform: translateY(-50%);
    width: 1.3rem;
    height: 1.3rem;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.9rem;
    line-height: 1;
}
.login-validation-alerts .alert .close {
    position: absolute;
    right: 0.9rem;
    top: 0.45rem;
    color: #fff;
    opacity: 1;
    font-size: 1.2rem;
    line-height: 1;
    background: transparent;
    border: 0;
    cursor: pointer;
}
.login-validation-alerts .alert strong {
    color: #fff;
}
.login-field.has-error {
    border-radius: 0.5rem;
}
.login-field.has-error .form-control,
.login-field.has-error .form-control:focus {
    border-color: #ef4444 !important;
    box-shadow: 0 0 0 0.2rem rgba(239, 68, 68, 0.12) !important;
}
.login-field.has-error .login-field-icon {
    color: #ef4444;
}
@media (max-width: 767px) {
    .login-validation-alerts {
        position: static;
        width: 100%;
        padding: 1rem 1rem 0;
    }
}
.night-mode-toggle {
    display: inline-flex;
    /* Adjust width and height together to change the icon button size. */
    width: 2.75rem;
    height: 2.75rem;
    align-items: center;
    justify-content: center;
    padding: 0;
    border: 0;
    border-radius: 50%;
    background: transparent;
    color: #f59e0b;
    cursor: pointer;
    transition: color 0.35s ease, transform 0.2s ease;
}
.night-mode-toggle:hover {
    transform: scale(1.08);
}
.theme-switch-icon {
    width: 1.75rem;
    height: 1.75rem;
    overflow: visible;
    transform: rotate(90deg);
    transition: transform 0.55s cubic-bezier(0.4, 1.6, 0.6, 1);
}
.theme-switch-sun {
    fill: currentColor;
    transition: r 0.55s cubic-bezier(0.4, 1.6, 0.6, 1), fill 0.35s ease;
}
.theme-switch-mask {
    transition: cx 0.55s cubic-bezier(0.4, 1.6, 0.6, 1), cy 0.55s cubic-bezier(0.4, 1.6, 0.6, 1);
}
.theme-switch-rays {
    fill: none;
    stroke-width: 2;
    stroke-linecap: round;
    opacity: 1;
    transition: opacity 0.35s ease;
}
.night-mode-toggle.is-dark {
    color: #f8fafc;
}
.night-mode-toggle.is-dark .theme-switch-icon {
    transform: rotate(40deg);
}
.night-mode-toggle.is-dark .theme-switch-sun {
    r: 9;
}
.night-mode-toggle.is-dark .theme-switch-mask {
    cx: 12px;
    cy: 5.52px;
}
.night-mode-toggle.is-dark .theme-switch-rays {
    opacity: 0;
}
.password-group {
    position: relative;
}
.password-group .form-control {
    padding-right: 3.5rem;
}
.login-field {
    position: relative;
}
.login-field .form-control {
    padding-left: 2.5rem;
    border-color: #8bc9d8;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.login-field .form-control:focus {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.18) !important;
    outline: none;
}
.login-field-icon {
    position: absolute;
    left: 1rem;
    pointer-events: none;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    z-index: 1;
}
.password-toggle-btn {
    position: absolute;
    top: 50%;
    right: 15px;
    transform: translateY(-50%);
    border: none;
    background: transparent;
    width: 2.25rem;
    height: 2.25rem;
    color: #64748b;
    font-size: 1rem;
    padding: 0;
    cursor: pointer;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity 0.2s ease, visibility 0.2s ease, color 0.2s ease;
}
.password-group.has-focus .password-toggle-btn,
.password-group.has-text .password-toggle-btn,
.password-group:focus-within .password-toggle-btn {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
}
.password-group.has-error .password-toggle-btn {
    opacity: 0 !important;
    visibility: hidden !important;
    pointer-events: none !important;
}
.password-toggle-btn:hover {
    color: #0f766e;
}
body:not(.dark-mode) .login-body {
    background-color: #f8fafc;
    background-image: linear-gradient(rgba(15, 23, 42, .035) 1px, transparent 1px), linear-gradient(90deg, rgba(15, 23, 42, .035) 1px, transparent 1px);
    background-size: 80px 80px;
}
body.dark-mode {
    background-color: #0f1418;
    background-image: linear-gradient(rgba(148, 163, 184, .055) 1px, transparent 1px), linear-gradient(90deg, rgba(148, 163, 184, .055) 1px, transparent 1px);
    background-size: 80px 80px;
    color: #f1f5f9;
    transition: background-color 0.4s ease, color 0.4s ease;
}
body.dark-mode .login-body {
    background-color: #0f1418 !important;
    background-image: linear-gradient(rgba(148, 163, 184, .055) 1px, transparent 1px), linear-gradient(90deg, rgba(148, 163, 184, .055) 1px, transparent 1px) !important;
    background-size: 80px 80px;
}
body.dark-mode .login-wrapper .loginbox {
    background-color: #1c2025;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.55);
    border-color: rgba(148, 163, 184, 0.2);
}
body.dark-mode .login-wrapper .loginbox .login-left {
    background: linear-gradient(rgba(8, 74, 58, .82), rgba(20, 151, 105, .72)), url('{{ asset('assets/img/img-01.jpg') }}') center/cover no-repeat;
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
    background: #111827;
    border-color: #334155;
    color: #f8fafc;
}
body.dark-mode .form-control::placeholder {
    color: #94a3b8;
}
body.dark-mode .login-field-icon,
body.dark-mode .password-toggle-btn {
    color: #a8b2bd;
}
body.dark-mode .password-toggle-btn:hover {
    color: #69d9aa;
}
body.dark-mode .btn-primary {
    background-color: #3db985;
    border-color: #3db985;
}
@media (prefers-reduced-motion: reduce) {
    .animate__animated {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
</style>
@endpush

@push('page-js')
<script>
$(function() {
    function clearLoginErrors() {
        $('#loginValidationAlerts').empty();
        $('#loginServerValidationAlert').remove();
        $('.login-field').removeClass('has-error');
        $('#loginEmail, #loginPassword').removeAttr('aria-invalid').removeClass('is-invalid');
    }

    function markFieldError($input, message) {
        var $field = $input.closest('.login-field');
        if (!$field.length) {
            return;
        }

        clearLoginErrors();
        $field.addClass('has-error');
        $input.attr('aria-invalid', 'true').addClass('is-invalid');

        var $alert = $(
            '<div class="alert alert-danger" role="alert">' +
            '<button type="button" class="close" aria-label="Close">&times;</button>' +
            '<span>Oh snap! ' + message + '</span>' +
            '</div>'
        );

        $('#loginValidationAlerts').append($alert);

        $alert.find('.close').on('click', function() {
            $alert.remove();
            $field.removeClass('has-error');
            $input.removeAttr('aria-invalid').removeClass('is-invalid');
        });

        $input.focus();
        var inputValue = $input.val() || '';
        if ($input[0].setSelectionRange) {
            $input[0].setSelectionRange(inputValue.length, inputValue.length);
        }
    }

    function syncPasswordToggleState() {
        var $password = $('#loginPassword');
        var $group = $password.closest('.password-group');
        var hasText = $.trim($password.val()).length > 0;
        var hasError = $group.hasClass('has-error');

        $group.toggleClass('has-text', !hasError && (hasText || $password.is(':focus')));
        $group.toggleClass('has-focus', !hasError && $password.is(':focus'));
    }

    $('#loginEmail, #loginPassword').on('input', function() {
        var $input = $(this);
        var $field = $input.closest('.login-field');
        if ($field.hasClass('has-error')) {
            $field.removeClass('has-error');
            $input.removeAttr('aria-invalid').removeClass('is-invalid');
        }
        if ($('#loginValidationAlerts .alert').length) {
            $('#loginValidationAlerts .alert').fadeOut(200, function() {
                $(this).remove();
            });
        }
        $('#loginServerValidationAlert').remove();

        if ($input.attr('id') === 'loginPassword') {
            syncPasswordToggleState();
        }
    });

    $('#loginPassword').on('focusin', syncPasswordToggleState).on('focusout', function() {
        $(this).closest('.password-group').removeClass('has-focus');
        if ($.trim($(this).val()).length === 0) {
            $(this).closest('.password-group').removeClass('has-text');
        }
    });

    $('#loginForm').on('submit', function(event) {
        event.preventDefault();

        var $form = $(this);
        var $email = $('#loginEmail');
        var $password = $('#loginPassword');
        var email = $.trim($email.val());
        var password = $password.val();
        var $submit = $form.find('button[type="submit"]');

        clearLoginErrors();

        if (!email) {
            markFieldError($email, 'The email field is required.');
            return false;
        }

        if (!password) {
            markFieldError($password, 'The password field is required.');
            return false;
        }

        $submit.prop('disabled', true).attr('aria-busy', 'true').text('Logging in...');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            headers: {
                Accept: 'application/json'
            }
        }).done(function(response) {
            window.location.href = response.redirect;
        }).fail(function(xhr) {
            var response = xhr.responseJSON || {};
            var message = response.message || 'Unable to log in. Please check your details and try again.';

            if (response.errors) {
                var firstError = Object.keys(response.errors)[0];
                message = response.errors[firstError][0];
                markFieldError(firstError === 'password' ? $password : $email, message);
            } else {
                markFieldError($email, message);
            }

            $submit.prop('disabled', false).removeAttr('aria-busy').text('Login');
        });

        return false;
    });

    $('#passwordVisibilityToggle').on('click', function() {
        var $password = $('#loginPassword');
        var type = $password.attr('type') === 'password' ? 'text' : 'password';
        $password.attr('type', type);
        var isVisible = type === 'text';
        $(this).attr('aria-label', isVisible ? 'Hide password' : 'Show password');
        $(this).attr('aria-pressed', isVisible ? 'true' : 'false');
        $(this).find('i').toggleClass('fa-eye', !isVisible).toggleClass('fa-eye-slash', isVisible);
    });
});
</script>
@endpush