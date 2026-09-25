@extends('admin.layouts.plain')

@section('content')
<h1>Forgot Password?</h1>
<p class="account-subtitle">Enter your email to get a password reset link</p>

<div id="resetPasswordValidationAlerts" class="form-validation-alerts" aria-live="polite" aria-atomic="true"></div>

<!-- Form -->
<form id="resetPasswordForm" action="{{route('password.request')}}" method="post">
	@csrf
    <input type="hidden" name="token" value="{{request()->token}}">
	<div class="form-group form-field">
		<input class="form-control" name="email" type="text" placeholder="Email" value="{{ old('email') }}">
	</div>
    <div class="form-group form-field password-field">
		<input id="resetPasswordInput" class="form-control" name="password" type="password" placeholder="Enter new password" value="{{ old('password') }}">
        <button class="password-toggle-btn" type="button" data-target="resetPasswordInput" aria-label="Show password" aria-pressed="false">
            <i class="fas fa-eye" aria-hidden="true"></i>
        </button>
	</div>
    <div class="form-group form-field password-field">
		<input id="resetPasswordConfirmInput" class="form-control" name="password_confirmation" type="password" placeholder="Repeat new password" value="{{ old('password_confirmation') }}">
        <button class="password-toggle-btn" type="button" data-target="resetPasswordConfirmInput" aria-label="Show password" aria-pressed="false">
            <i class="fas fa-eye" aria-hidden="true"></i>
        </button>
	</div>
	<div class="form-group mb-0">
		<button class="btn btn-primary btn-block" type="submit">Reset Password</button>
	</div>
</form>
<!-- /Form -->

<div class="text-center dont-have">Remember your password? <a href="{{route('login')}}">Login</a></div>

<style>
.form-validation-alerts {
    position: absolute;
    left: calc(100% + 20px);
    top: 20px;
    width: 240px;
    z-index: 3;
}
.form-validation-alerts .alert {
    background: #ef4444;
    border: 1px solid #dc2626;
    border-radius: 10px;
    color: #fff;
    padding: 0.9rem 2.6rem 0.9rem 2.8rem;
    box-shadow: 0 10px 18px rgba(239, 68, 68, 0.18);
    margin: 0 0 0.75rem;
    position: relative;
    line-height: 1.4;
}
.form-validation-alerts .alert::before {
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
.form-validation-alerts .alert .close {
    position: absolute;
    top: 0.55rem;
    right: 0.8rem;
    color: #fff;
    background: transparent;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    opacity: 1;
}
.form-field {
    position: relative;
}
.form-field.has-error .form-control,
.form-field.has-error .form-control:focus {
    border-color: #ef4444 !important;
    box-shadow: 0 0 0 0.15rem rgba(239, 68, 68, 0.12) !important;
}
.password-field {
    position: relative;
}
.password-toggle-btn {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 2.25rem;
    height: 2.25rem;
    border: none;
    background: transparent;
    color: #64748b;
    font-size: 1rem;
    cursor: pointer;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity 0.2s ease, visibility 0.2s ease;
}
.password-field.has-focus .password-toggle-btn,
.password-field.has-text .password-toggle-btn,
.password-field:focus-within .password-toggle-btn {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
}
.password-field.has-error .password-toggle-btn {
    opacity: 0 !important;
    visibility: hidden !important;
    pointer-events: none !important;
}
@media (max-width: 767px) {
    .form-validation-alerts {
        position: static;
        width: 100%;
        margin-bottom: 1rem;
    }
}
</style>

<script>
$(function() {
    function showFieldError($field, message, alertContainer) {
        $field.addClass('has-error');
        $field.find('input').attr('aria-invalid', 'true').addClass('is-invalid');
        $(alertContainer).html(
            '<div class="alert alert-danger" role="alert">' +
            '<button type="button" class="close" aria-label="Close">&times;</button>' +
            '<span>Oh snap! ' + message + '</span>' +
            '</div>'
        );
        $(alertContainer + ' .close').on('click', function() {
            $(alertContainer).empty();
            $field.removeClass('has-error');
            $field.find('input').removeAttr('aria-invalid').removeClass('is-invalid');
        });
    }

    function syncPasswordField($field) {
        var $input = $field.find('input');
        var hasText = $.trim($input.val()).length > 0;
        var hasError = $field.hasClass('has-error');

        $field.toggleClass('has-text', !hasError && (hasText || $input.is(':focus')));
        $field.toggleClass('has-focus', !hasError && $input.is(':focus'));
    }

    $('#resetPasswordForm input').on('input', function() {
        var $field = $(this).closest('.form-field');
        if ($field.hasClass('has-error')) {
            $field.removeClass('has-error');
            $(this).removeAttr('aria-invalid').removeClass('is-invalid');
            $('#resetPasswordValidationAlerts').empty();
        }
        if ($(this).closest('.password-field').length) {
            syncPasswordField($(this).closest('.password-field'));
        }
    });

    $('#resetPasswordForm input').on('focusin', function() {
        if ($(this).closest('.password-field').length) {
            syncPasswordField($(this).closest('.password-field'));
        }
    }).on('focusout', function() {
        if ($(this).closest('.password-field').length) {
            var $field = $(this).closest('.password-field');
            $field.removeClass('has-focus');
            if ($.trim($(this).val()).length === 0) {
                $field.removeClass('has-text');
            }
        }
    });

    $('#resetPasswordForm').on('submit', function(event) {
        var isValid = true;
        var alertSelector = '#resetPasswordValidationAlerts';
        $(alertSelector).empty();
        $('#resetPasswordForm .form-field').removeClass('has-error');
        $('#resetPasswordForm input').removeAttr('aria-invalid').removeClass('is-invalid');

        $('#resetPasswordForm input').each(function() {
            var $input = $(this);
            var $field = $input.closest('.form-field');
            var value = $.trim($input.val());

            if ($input.attr('name') === 'token') {
                return;
            }

            if (!value) {
                event.preventDefault();
                isValid = false;
                showFieldError($field, $input.attr('name') === 'email' ? 'The email field is required.' : 'The password field is required.', alertSelector);
                return false;
            }

            if ($input.attr('name') === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                event.preventDefault();
                isValid = false;
                showFieldError($field, 'The email must be a valid email address.', alertSelector);
                return false;
            }
        });

        var $password = $('#resetPasswordInput');
        var $confirm = $('#resetPasswordConfirmInput');
        if ($password.val() && $confirm.val() && $password.val() !== $confirm.val()) {
            event.preventDefault();
            isValid = false;
            showFieldError($confirm.closest('.form-field'), 'The password confirmation does not match.', alertSelector);
        }

        if (!isValid) {
            event.preventDefault();
            return false;
        }
    });

    $('.password-toggle-btn').on('click', function(event) {
        var targetId = $(this).data('target');
        var $input = $('#' + targetId);
        var $field = $input.closest('.password-field');

        if ($field.hasClass('has-error') || (!$field.hasClass('has-focus') && !($input.is(':focus')))) {
            event.preventDefault();
            return;
        }

        var type = $input.attr('type') === 'password' ? 'text' : 'password';
        $input.attr('type', type);
        var isVisible = type === 'text';
        $(this).attr('aria-label', isVisible ? 'Hide password' : 'Show password');
        $(this).attr('aria-pressed', isVisible ? 'true' : 'false');
        $(this).find('i').toggleClass('fa-eye', !isVisible).toggleClass('fa-eye-slash', isVisible);
    });
});
</script>
@endsection