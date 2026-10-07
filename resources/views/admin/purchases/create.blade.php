@extends('admin.layouts.app')

@push('page-css')
	<!-- Datetimepicker CSS -->
	<link rel="stylesheet" href="{{asset('assets/css/bootstrap-datetimepicker.min.css')}}">
	<style>
		.purchase-expiry-switch {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 1rem;
			margin-top: .75rem;
			font-size: .875rem;
			color: #64748b;
			cursor: pointer;
		}
		.purchase-expiry-switch input {
			appearance: none;
			-webkit-appearance: none;
			position: relative;
			width: 2.5rem;
			height: 1.35rem;
			margin: 0;
			border: 0;
			border-radius: 999px;
			background: #cbd5e1;
			box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .12);
			cursor: pointer;
			transition: background-color .2s ease;
		}
		.purchase-expiry-switch input::after {
			content: '';
			position: absolute;
			width: 1rem;
			height: 1rem;
			left: .18rem;
			top: .175rem;
			border-radius: 50%;
			background: #fff;
			box-shadow: 0 1px 3px rgba(15, 23, 42, .3);
			transition: transform .2s ease;
		}
		.purchase-expiry-switch input:checked {
			background: #76a7f7;
		}
		.purchase-expiry-switch input:checked::after {
			transform: translateX(1.14rem);
		}
		.purchase-expiry-switch input:disabled {
			opacity: .8;
			cursor: not-allowed;
		}
	</style>
@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Add Purchase</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Add Purchase</li>
	</ul>
</div>
@endpush


@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
				
				@include('admin.purchases._form')

			</div>
		</div>
	</div>			
</div>
@endsection

@push('page-js')
	<!-- Datetimepicker JS -->
	<script src="{{asset('assets/js/moment.min.js')}}"></script>
	<script src="{{asset('assets/js/bootstrap-datetimepicker.min.js')}}"></script>	
	<script src="{{asset('assets/js/purchase-multi-step.js')}}"></script>
	<script>
		(function () {
			var purchaseForm = document.querySelector('form[action="{{ route('purchases.store') }}"]');
			if (!purchaseForm) return;
			var validationNoticeShown = false;

			purchaseForm.addEventListener('invalid', function (event) {
				event.preventDefault();
				var firstInvalid = purchaseForm.querySelector(':invalid');
				if (firstInvalid) firstInvalid.focus();
				if (!validationNoticeShown && window.Snackbar) {
					validationNoticeShown = true;
					Snackbar.show({
						text: 'Please complete all required fields before submitting.',
						pos: 'top-right',
						actionTextColor: '#fff',
						backgroundColor: '#e7515a'
					});
					setTimeout(function () {
						validationNoticeShown = false;
					}, 500);
				}
			}, true);

			purchaseForm.addEventListener('submit', function () {
				var submitButton = purchaseForm.querySelector('button[type="submit"]');
				if (submitButton) submitButton.setAttribute('data-original-submit-text', submitButton.innerHTML);
			});
		})();

		$(function(){
			var clearPrefill = {{ $clearPrefill ? 'true' : 'false' }};
			var blockedSupplier = "{{ $blockedSupplier ?? '' }}";
			var blockedProduct = {!! json_encode($blockedProduct ?? '') !!};

			if (clearPrefill) {
				// disable any supplier options that match the blocked supplier or blocked product
				if (blockedSupplier) {
					$('select[name="supplier"]').find('option[value="'+blockedSupplier+'"]').prop('disabled', true);
				}
				if (blockedProduct) {
					$('select[name="supplier"]').find('option[data-product="'+blockedProduct+'"]').prop('disabled', true);
				}
				// clear visible selections and product input when duplicate warning is active
				$('select[name="supplier"]').val('').trigger('change.select2');
				$('select[name="category"]').val('').trigger('change.select2');
				$('input[name="product"]').val('');
			}
		});
    </script>
@endpush

