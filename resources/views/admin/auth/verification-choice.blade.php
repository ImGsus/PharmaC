@extends('admin.layouts.plain')

@section('content')
<h1>Verify Account</h1>
@if($existingAccount)
<p class="account-subtitle">Are you ready to get verified now?</p>
@else
<p class="account-subtitle">Your account was created successfully.</p>
@endif

<div class="verification-choice">
    @if($existingAccount)
    <p>Click the button below to start.</p>
    @else
    <p>Would you like to verify your email now?</p>
    @endif
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button class="btn btn-primary btn-block" type="submit">Verify account</button>
    </form>
    <a class="btn btn-link btn-block" href="{{ route('dashboard') }}">I'll do it later</a>
</div>
@endsection

@push('page-css')
<style>
.verification-choice p { color: #64748b; text-align: center; margin-bottom: 1.25rem; }
.verification-choice .btn-link { color: #64748b; margin-top: .5rem; }
</style>
@endpush
