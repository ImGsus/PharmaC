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

		// auto-calc total_quantity = item_quantity + (packaging_box * quantity_per_box)
		function computeTotal() {
			var item = parseInt($('input[name="item_quantity"]').val() || 0, 10);
			var boxes = parseInt($('input[name="packaging_box"]').val() || 0, 10);
			var perBox = parseInt($('input[name="quantity_per_box"]').val() || 0, 10);
			var total = item + (boxes * perBox);
			$('input[name="total_quantity"]').val(total);
		}
		$(function(){
			// compute on change
			$('input[name="item_quantity"], input[name="packaging_box"], input[name="quantity_per_box"]').on('input', computeTotal);
			// compute on page load (use old values if present)
			computeTotal();

			var clearPrefill = {{ $clearPrefill ? 'true' : 'false' }};
			var blockedSupplier = "{{ $blockedSupplier ?? '' }}";
			var blockedProduct = {!! json_encode($blockedProduct ?? '') !!};
			var categorySelect = $('select[name="category"]');
			var expiryInput = $('input[name="expiry_date"]');
			var noExpiryInput = $('input[name="no_expiry"]');
			var expiryStateInitialized = false;

			function notifyExpiry(message) {
				if (!window.Snackbar) return;
				Snackbar.show({
					text: message,
					duration: 4500,
					pos: 'top-right',
					actionTextColor: '#fff',
					backgroundColor: '#f0ad4e',
					textColor: '#fff'
				});
			}

			function syncExpiryForCategory() {
				var categoryNoExpiry = categorySelect.find('option:selected').data('no-expiry') === 1 || categorySelect.find('option:selected').data('no-expiry') === '1';
				var noExpiry = categoryNoExpiry || noExpiryInput.is(':checked');
				noExpiryInput.prop('disabled', categoryNoExpiry);
				expiryInput.prop('disabled', noExpiry).prop('required', !noExpiry);
			}

			function setPurchaseFieldsLock(locked) {
				var productInput = $('input[name="product"]');
				var costInput = $('input[name="cost_price"]');
				var expiryInput = $('input[name="expiry_date"]');

				productInput.prop('readonly', locked);
				costInput.prop('readonly', locked);
				expiryInput.prop('readonly', locked);

				var select2Container = categorySelect.next('.select2-container');
				if (locked) {
					categorySelect.data('locked', true);
					select2Container.css('pointer-events', 'none');
					select2Container.css('background-color', '#e9ecef');
				} else {
					categorySelect.data('locked', false);
					select2Container.css('pointer-events', 'auto');
					select2Container.css('background-color', '');
				}
			}

			$('select[name="category"]').on('select2:opening', function(e) {
				if ($(this).data('locked')) {
					e.preventDefault();
				}
			});
			categorySelect.on('change', function () {
				var categoryNoExpiry = categorySelect.find('option:selected').data('no-expiry') === 1 || categorySelect.find('option:selected').data('no-expiry') === '1';
				noExpiryInput.prop('checked', categoryNoExpiry).prop('disabled', categoryNoExpiry);
				syncExpiryForCategory();
				if (categoryNoExpiry && expiryStateInitialized) {
					notifyExpiry('This category has no expiry enabled. Turn off No expiry to enter a committed expiry date.');
				}
			});
			noExpiryInput.on('change', function () {
				syncExpiryForCategory();
				if ($(this).is(':checked')) {
					notifyExpiry('No expiry is enabled. Turn it off to enter a committed expiry date for this product.');
				}
			});

			$('select[name="supplier"]').on('change', function(){
				var $opt = $(this).find('option:selected');
				var prod = $opt.data('product') || '';
				var cat = $opt.data('category') || '';
				var cost = $opt.data('cost') || '';
				var expiry = $opt.data('expiry') || '';
                var hasPurchaseMeta = $opt.data('last-purchase') === '1' || $opt.data('lastPurchase') === '1';

                var productInput = $('input[name="product"]');
                var categorySelect = $('select[name="category"]');
                var costInput = $('input[name="cost_price"]');
                var expiryInput = $('input[name="expiry_date"]');

                // Overwrite fields with supplier's latest purchase data when available
                if (prod) productInput.val(prod);
				if (cat) categorySelect.val(cat).trigger('change.select2');
				syncExpiryForCategory();
                if (cost) costInput.val(cost);
                if (expiry) expiryInput.val(expiry);

                // Only lock fields when the supplier has an existing purchase metadata record
                if (hasPurchaseMeta) {
                    setPurchaseFieldsLock(true);
                } else {
                    setPurchaseFieldsLock(false);
                }

                // recompute total after autofill
                computeTotal();
            });

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

            // trigger on load if selection exists
            $('select[name="supplier"]').trigger('change');
			syncExpiryForCategory();
			expiryStateInitialized = true;
        });
    </script>
@endpush

