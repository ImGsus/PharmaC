@extends('admin.layouts.plain')

@section('content')
<h1>Verify Account</h1>
<p class="account-subtitle">Enter the 6-digit code sent to {{ $email }}</p>

<div id="verificationNotifications" class="login-validation-alerts" aria-live="polite" aria-atomic="true">
    @if ($errors->any())
        <div class="alert alert-danger" role="alert"><button type="button" class="close" aria-label="Close">&times;</button>{{ $errors->first('code') }}</div>
    @elseif (session('message'))
        <div class="alert alert-success" role="alert"><button type="button" class="close" aria-label="Close">&times;</button>{{ session('message') }}</div>
    @endif
</div>

<form method="POST" action="{{ route('verification.verify') }}" id="verificationForm">
    @csrf
    <div class="verification-code-inputs" aria-label="Verification code">
        @for ($index = 0; $index < 6; $index++)
            <input type="text" name="code_digit[]" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" required aria-label="Digit {{ $index + 1 }}">
        @endfor
    </div>
    <input type="hidden" name="code" id="verificationCode">
    <button class="btn btn-primary btn-block" type="submit">Verify</button>
</form>

<button type="button" class="btn btn-link btn-block" id="resendVerificationCode">Send another code</button>
@endsection

@push('page-css')
<style>
.verification-code-inputs { display: flex; gap: .5rem; margin-bottom: 1.25rem; }
.verification-code-inputs input { width: 100%; min-width: 0; height: 3rem; text-align: center; font-size: 1.25rem; border: 1px solid #cbd5e1; border-radius: .35rem; }
.verification-code-inputs input:focus { border-color: #669df6; outline: 0; box-shadow: 0 0 0 .2rem rgba(102,157,246,.2); }
.btn-link { color: #64748b; }
#verificationNotifications { position: absolute; left: calc(100% + 20px); top: 20px; width: 240px; z-index: 4; }
#verificationNotifications .alert { margin-bottom: .5rem; width: 100%; color: #fff; border: 0; border-radius: 10px; box-shadow: 0 10px 18px rgba(15, 23, 42, .18); position: relative; padding: .8rem 2.5rem .8rem 1rem; }
#verificationNotifications .alert { animation: verificationNotificationIn .28s ease-out both; }
#verificationNotifications .alert.is-hiding { animation: verificationNotificationOut .28s ease-in both; }
#verificationNotifications .alert-success { background: #22c55e; }
#verificationNotifications .alert-danger { background: #ef4444; }
#verificationNotifications .alert .close { position: absolute; right: .75rem; top: .35rem; color: #fff; opacity: 1; }
@media (max-width: 767px) { #verificationNotifications { position: static; width: 100%; margin-bottom: 1rem; } }
@keyframes verificationNotificationIn { from { opacity: 0; transform: translateX(18px); } to { opacity: 1; transform: translateX(0); } }
@keyframes verificationNotificationOut { from { opacity: 1; transform: translateX(0); } to { opacity: 0; transform: translateX(18px); } }
</style>
@endpush

@push('page-js')
<script>
$(function () {
    var inputs = $('#verificationForm .verification-code-inputs input');
    var notifications = $('#verificationNotifications');
    var notificationTimer;

    function showNotification(message, type) {
        clearTimeout(notificationTimer);
        notifications.empty().append(
            $('<div class="alert" role="alert"></div>')
                .addClass('alert-' + type)
                .text(message)
                .prepend('<button type="button" class="close" aria-label="Close">&times;</button>')
        );
        notificationTimer = setTimeout(hideNotification, 5000);
    }

    function hideNotification() {
        var alert = notifications.find('.alert');
        if (!alert.length) return;
        alert.addClass('is-hiding').one('animationend', function () { $(this).remove(); });
    }

    notifications.on('click', '.close', function () {
        clearTimeout(notificationTimer);
        hideNotification();
    });
    if (notifications.find('.alert').length) {
        notificationTimer = setTimeout(hideNotification, 5000);
    }
    inputs.on('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 1);
        if (this.value) inputs.eq(inputs.index(this) + 1).trigger('focus');
    }).on('keydown', function (event) {
        if (event.key === 'Backspace' && !this.value) inputs.eq(inputs.index(this) - 1).trigger('focus');
    });
    inputs.on('paste', function (event) {
        event.preventDefault();
        var pastedCode = (event.originalEvent.clipboardData || window.clipboardData).getData('text')
            .replace(/\D/g, '')
            .slice(0, inputs.length);
        inputs.val('');
        pastedCode.split('').forEach(function (digit, index) {
            inputs.eq(index).val(digit);
        });
        inputs.eq(Math.max(0, pastedCode.length - 1)).trigger('focus');
    });
    $('#verificationForm').on('submit', function () {
        $('#verificationCode').val(inputs.map(function () { return this.value; }).get().join(''));
    });

    $('#resendVerificationCode').on('click', function () {
        var button = $(this);
        button.prop('disabled', true).text('Sending...');
        $.ajax({
            url: '{{ route('verification.send') }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            headers: { Accept: 'application/json' }
        }).done(function (response) {
            showNotification(response.message || 'A verification code was sent to your email.', 'success');
        }).fail(function (response) {
            showNotification(response.responseJSON && response.responseJSON.message ? response.responseJSON.message : 'Unable to send another code.', 'danger');
        }).always(function () {
            button.prop('disabled', false).text('Send another code');
        });
    });

    inputs.first().trigger('focus');
});
</script>
@endpush
