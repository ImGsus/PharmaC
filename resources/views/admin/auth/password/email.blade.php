@extends('admin.layouts.plain')

@section('content')
@php
    $emailError = $errors->has('email');
@endphp
<h1>Forgot Password?</h1>
<p class="account-subtitle">Enter your email to get a password reset link</p>

@if ($emailError)
<div id="forgotPasswordValidationAlerts" class="login-validation-alerts" aria-live="polite" aria-atomic="true">
    <div class="alert alert-danger" role="alert">
        <button type="button" class="close" aria-label="Close">&times;</button>
        <span>Oh snap! {{ $errors->first('email') }}</span>
    </div>
</div>
@else
<div id="forgotPasswordValidationAlerts" class="login-validation-alerts" aria-live="polite" aria-atomic="true"></div>
@endif

<!-- Form -->
<form id="forgotPasswordForm" action="{{route('password.request')}}" method="post">
	@csrf
	<div class="form-group login-field{{ $emailError ? ' has-error' : '' }}">
		<i class="fas fa-envelope login-field-icon" aria-hidden="true"></i>
		<input id="forgotPasswordEmail" class="form-control{{ $emailError ? ' is-invalid' : '' }}" name="email" type="text" placeholder="Email" value="{{ old('email') }}" aria-invalid="{{ $emailError ? 'true' : 'false' }}">
	</div>
	<div class="form-group mb-0">
		<button class="btn btn-primary btn-block" type="submit">Submit</button>
	</div>
</form>
<!-- /Form -->

<div class="text-center dont-have">Remember your password? <a href="{{route('login')}}">Login</a></div>

<style>
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
.login-field {
    position: relative;
}
.login-field .form-control {
    padding-left: 2.5rem;
    border-color: #8bc9d8;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.login-field .form-control:focus {
    border-color: #2dc4e7 !important;
    box-shadow: 0 0 0 0.2rem rgba(45, 196, 231, 0.18) !important;
    outline: none;
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
.login-field-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    z-index: 1;
    pointer-events: none;
}
@media (max-width: 767px) {
    .login-validation-alerts {
        position: static;
        width: 100%;
        padding: 1rem 1rem 0;
    }
}
</style>
@endsection

@push('page-js')
<script>
$(function() {
    function clearForgotPasswordErrors() {
        $('#forgotPasswordValidationAlerts').empty();
        $('.login-field').removeClass('has-error');
        $('#forgotPasswordEmail').removeAttr('aria-invalid').removeClass('is-invalid');
    }

    function markForgotPasswordError($input, message) {
        var $field = $input.closest('.login-field');
        if (!$field.length) {
            return;
        }

        clearForgotPasswordErrors();
        $field.addClass('has-error');
        $input.attr('aria-invalid', 'true').addClass('is-invalid');

        var $alert = $(
            '<div class="alert alert-danger" role="alert">' +
            '<button type="button" class="close" aria-label="Close">&times;</button>' +
            '<span>Oh snap! ' + message + '</span>' +
            '</div>'
        );

        $('#forgotPasswordValidationAlerts').append($alert);

        $alert.find('.close').on('click', function() {
            $alert.stop(true, true).fadeOut(200, function() {
                $(this).remove();
            });
            $field.removeClass('has-error');
            $input.removeAttr('aria-invalid').removeClass('is-invalid');
        });

        $input.focus();
        var inputValue = $input.val() || '';
        if ($input[0].setSelectionRange) {
            $input[0].setSelectionRange(inputValue.length, inputValue.length);
        }
    }

    $('#forgotPasswordEmail').on('input', function() {
        var $input = $(this);
        var $field = $input.closest('.login-field');
        if ($field.hasClass('has-error')) {
            $field.removeClass('has-error');
            $input.removeAttr('aria-invalid').removeClass('is-invalid');
        }
        if ($('#forgotPasswordValidationAlerts .alert').length) {
            $('#forgotPasswordValidationAlerts .alert').stop(true, true).fadeOut(200, function() {
                $(this).remove();
            });
        }
    });

    $('#forgotPasswordForm').on('submit', function(event) {
        var $email = $('#forgotPasswordEmail');
        var email = $.trim($email.val());

        clearForgotPasswordErrors();

        if (!email) {
            event.preventDefault();
            markForgotPasswordError($email, 'The email field is required.');
            return false;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            event.preventDefault();
            markForgotPasswordError($email, 'The email must be a valid email address.');
            return false;
        }
    });
});
</script>
@endpush