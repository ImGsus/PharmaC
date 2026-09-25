@extends('admin.layouts.plain')

@section('content')
<h1>Register</h1>
<p class="account-subtitle">Access to our dashboard</p>

<div id="registerValidationAlerts" class="login-validation-alerts" aria-live="polite" aria-atomic="true"></div>

<!-- Form -->
<form id="registerForm" action="{{route('register')}}" method="POST">
	@csrf
    <div class="register-fields">
        <div class="form-group form-field">
            <input class="form-control" name="first_name" type="text" value="{{ old('first_name') }}" placeholder="First Name" required>
        </div>
        <div class="form-group form-field">
            <input class="form-control" name="last_name" type="text" value="{{ old('last_name') }}" placeholder="Last Name" required>
        </div>
        <div class="form-group form-field">
            <input class="form-control" name="username" type="text" value="{{ old('username') }}" placeholder="Username" required>
        </div>
        <div class="form-group form-field">
            <select class="form-control" name="gender" required>
                <option value="" disabled {{ old('gender') ? '' : 'selected' }}>Gender</option>
                <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
            </select>
        </div>
        <div class="form-group form-field">
            <input class="form-control email-input" name="email" type="email" value="{{ old('email') }}" placeholder="Email" required>
            <button type="button" class="email-info-btn" aria-label="Email information" data-tooltip="Enter your email address.">
                <i class="fas fa-info-circle" aria-hidden="true"></i>
            </button>
        </div>
        <div class="form-group form-field password-field">
            <input id="registerPassword" class="form-control" name="password" type="password" placeholder="Password" value="{{ old('password') }}" required>
        <button class="password-toggle-btn" type="button" data-target="registerPassword" aria-label="Show password" aria-pressed="false">
            <i class="fas fa-eye" aria-hidden="true"></i>
        </button>
        </div>
        <div class="form-group form-field password-field register-confirm-password">
            <input id="registerPasswordConfirmation" class="form-control" name="password_confirmation" type="password" placeholder="Confirm Password" value="{{ old('password_confirmation') }}" required>
        <button class="password-toggle-btn" type="button" data-target="registerPasswordConfirmation" aria-label="Show password" aria-pressed="false">
            <i class="fas fa-eye" aria-hidden="true"></i>
        </button>
        </div>
	</div>
	<div class="form-group mb-0">
		<button class="btn btn-primary btn-block" type="submit">Register</button>
	</div>
</form>
<!-- /Form -->
								
<div class="text-center dont-have">Already have an account? <a href="{{route('login')}}">Login</a></div>
@endsection

@push('page-css')
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
.form-field {
    position: relative;
}
.register-fields {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0 12px;
}
.register-fields .form-field,
.register-fields .form-control {
    min-width: 0;
}
.register-fields select.form-control {
    color: #64748b;
}
.register-confirm-password {
    grid-column: 1 / -1;
    width: 100%;
}
.form-field.has-error {
    border-radius: 0.5rem;
}
.form-field.has-error .form-control,
.form-field.has-error .form-control:focus {
    border-color: #ef4444 !important;
    box-shadow: 0 0 0 0.2rem rgba(239, 68, 68, 0.12) !important;
}
.password-field {
    position: relative;
}
.password-field .form-control {
    padding-right: 3.5rem;
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
.email-input {
    padding-right: 3rem;
}
.email-info-btn {
    position: absolute;
    top: 50%;
    right: 12px;
    width: 2rem;
    height: 2rem;
    padding: 0;
    border: 0;
    background: transparent;
    color: #94a3b8;
    cursor: help;
    transform: translateY(-50%);
}
.email-info-btn:hover,
.email-info-btn:focus { color: #0f766e; }
.email-info-btn::after {
    content: attr(data-tooltip);
    position: absolute;
    right: 0;
    bottom: calc(100% + 8px);
    width: 220px;
    padding: 8px 10px;
    border-radius: 5px;
    background: #1f2937;
    color: #fff;
    font-size: 12px;
    line-height: 1.35;
    text-align: left;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity .15s ease, visibility .15s ease;
}
.email-info-btn:hover::after,
.email-info-btn:focus::after {
    opacity: 1;
    visibility: visible;
}
@media (max-width: 767px) {
    .register-fields {
        grid-template-columns: 1fr;
    }
    .register-confirm-password {
        grid-column: auto;
        width: auto;
    }
    .login-validation-alerts {
        position: static;
        width: 100%;
        padding: 1rem 1rem 0;
    }
}
</style>
@endpush

@push('page-js')
<script>
$(function() {
    function syncEmailTooltip() {
        var email = $.trim($('input[name="email"]').val());
        var tooltip = email ? 'Your email: ' + email : 'Enter your email address.';
        $('.email-info-btn').attr('data-tooltip', tooltip).attr('aria-label', tooltip);
    }

    $('input[name="email"]').on('input', syncEmailTooltip);
    syncEmailTooltip();

    function showFieldError($field, message) {
        if (window.AuthValidation && typeof window.AuthValidation.showFieldError === 'function') {
            window.AuthValidation.showFieldError('#registerValidationAlerts', $field, message);
            return;
        }

        $field.addClass('has-error');
        $field.find('input, select').attr('aria-invalid', 'true').addClass('is-invalid');

        $('#registerValidationAlerts').html(
            '<div class="alert alert-danger" role="alert">' +
            '<button type="button" class="close" aria-label="Close">&times;</button>' +
            '<span>Oh snap! ' + message + '</span>' +
            '</div>'
        );

        $('#registerValidationAlerts .close').on('click', function() {
            $('#registerValidationAlerts').empty();
            $field.removeClass('has-error');
            $field.find('input, select').removeAttr('aria-invalid').removeClass('is-invalid');
        });
    }

    function syncPasswordField($field) {
        var $input = $field.find('input');
        var hasText = $.trim($input.val()).length > 0;
        var hasError = $field.hasClass('has-error');

        $field.toggleClass('has-text', !hasError && (hasText || $input.is(':focus')));
        $field.toggleClass('has-focus', !hasError && $input.is(':focus'));
    }

    $('#registerForm input, #registerForm select').on('input change', function() {
        var $field = $(this).closest('.form-field');
        if ($field.hasClass('has-error')) {
            $field.removeClass('has-error');
            $(this).removeAttr('aria-invalid').removeClass('is-invalid');
            $('#registerValidationAlerts').empty();
        }
        if ($(this).closest('.password-field').length) {
            syncPasswordField($(this).closest('.password-field'));
        }
    });

    $('#registerForm input, #registerForm select').on('focusin', function() {
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

    $('#registerForm').on('submit', function(event) {
        var isValid = true;
        $('#registerValidationAlerts').empty();
        $('#registerForm .form-field').removeClass('has-error');
        $('#registerForm input, #registerForm select').removeAttr('aria-invalid').removeClass('is-invalid');

        $('#registerForm input, #registerForm select').each(function() {
            var $input = $(this);
            var $field = $input.closest('.form-field');
            var value = $.trim($input.val());

            if (($input.attr('name') === 'first_name' || $input.attr('name') === 'last_name' || $input.attr('name') === 'username') && !value) {
                event.preventDefault();
                isValid = false;
                showFieldError($field, 'This field is required.');
                return false;
            }

			if ($input.attr('name') === 'gender' && !$input.val()) {
				event.preventDefault();
				isValid = false;
				showFieldError($field, 'Please select a gender.');
				return false;
			}

            if ($input.attr('name') === 'email' && !value) {
                event.preventDefault();
                isValid = false;
                showFieldError($field, 'The email field is required.');
                return false;
            }

            if ($input.attr('name') === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                event.preventDefault();
                isValid = false;
                showFieldError($field, 'The email must be a valid email address.');
                return false;
            }

            if ($input.attr('name') === 'password' && !value) {
                event.preventDefault();
                isValid = false;
                showFieldError($field, 'The password field is required.');
                return false;
            }

            if ($input.attr('name') === 'password_confirmation' && !value) {
                event.preventDefault();
                isValid = false;
                showFieldError($field, 'The confirm password field is required.');
                return false;
            }
        });

        if (!isValid) {
            return false;
        }

        var $password = $('#registerPassword');
        var $confirm = $('#registerPasswordConfirmation');
        if ($password.val() && $confirm.val() && $password.val() !== $confirm.val()) {
            event.preventDefault();
            showFieldError($confirm.closest('.form-field'), 'The password confirmation does not match.');
            return false;
        }
    });

    $('.password-toggle-btn').on('click', function(event) {
        var targetId = $(this).data('target');
        var $input = $('#' + targetId);
        var $field = $input.closest('.password-field');

        if ($field.hasClass('has-error') || (!$field.hasClass('has-focus') && !($input.is(':focus')) && $.trim($input.val()).length === 0)) {
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
@endpush