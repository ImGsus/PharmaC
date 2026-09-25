@extends('admin.layouts.app')

<x-assets.tabulator />

@push('page-css')
<style>
	.purchase-products-list .purchase-product-option {
		display: flex;
		align-items: flex-start;
		min-width: 0;
		position: relative;
		padding-right: 10px;
	}

	.purchase-products-list {
		display: grid;
		grid-template-columns: repeat(5, minmax(0, 1fr));
		gap: 6px 8px;
		width: 100%;
		max-width: 100%;
	}

	.purchase-products-list .purchase-product-option:not(:last-child)::after {
		content: ',';
		position: absolute;
		right: 1px;
		top: 2px;
	}

	.purchase-products-list .purchase-product-link {
		min-width: 0;
		max-width: 100%;
		padding: 0;
		text-align: left;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	.purchase-products-list .purchase-product-checkbox {
		flex: 0 0 auto;
		margin-top: 4px;
	}

	#purchaseDetailsModal .modal-content,
	#purchaseProductDetailsModal .modal-content {
		--animate-duration: .35s;
	}

	#purchaseProductDetailsModal .modal-dialog {
		max-width: 760px;
	}
	#addPurchaseModal .modal-dialog {
		max-width: 1240px !important;
		width: 95vw !important;
		margin: 1.75rem auto;
	}
	#addPurchaseModal .modal-content {
		border: 0;
		border-radius: 10px;
		box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
		max-width: 100%;
		overflow: hidden;
	}
	#addPurchaseModal .modal-header {
		padding: 18px 26px;
		border-bottom: 1px solid #e9ecef;
		background: #fff;
		display: flex;
		align-items: center;
		justify-content: space-between;
	}
	#addPurchaseModal .modal-title {
		font-size: 1.25rem;
		font-weight: 600;
		color: #1f2937;
	}
	#addPurchaseModal .modal-body {
		max-height: calc(100vh - 150px);
		overflow-y: auto;
		overflow-x: hidden;
		padding: 26px 30px !important;
		background: #fff;
	}
	#addPurchaseModal .service-fields {
		margin-bottom: 1.25rem;
	}
	#addPurchaseModal .form-group {
		margin-bottom: 1.25rem;
		position: relative;
	}
	#addPurchaseModal label:not(.purchase-expiry-switch) {
		display: block !important;
		width: 100% !important;
		margin-bottom: 0.5rem;
		font-weight: 500;
		color: #374151;
		float: none !important;
		clear: both !important;
	}
	#addPurchaseModal .form-control {
		display: block !important;
		width: 100% !important;
		height: 42px;
		border: 1px solid #dcdcdc;
		border-radius: 5px;
		box-sizing: border-box;
	}
	#addPurchaseModal .select2-container {
		display: block !important;
		width: 100% !important;
		max-width: 100% !important;
		box-sizing: border-box;
		float: none !important;
		clear: both !important;
	}
	#addPurchaseModal .select2-container .select2-selection--single {
		display: flex !important;
		align-items: center;
		height: 42px !important;
		border: 1px solid #dcdcdc !important;
		border-radius: 5px !important;
		width: 100% !important;
		box-sizing: border-box;
		background-color: #fff;
	}
	#addPurchaseModal .select2-container .select2-selection--single .select2-selection__rendered {
		line-height: 40px !important;
		padding-left: 12px !important;
		padding-right: 32px !important;
		color: #495057;
		width: 100%;
		display: block !important;
	}
	#addPurchaseModal .select2-container .select2-selection--single .select2-selection__arrow {
		height: 40px !important;
		right: 8px !important;
		top: 1px !important;
	}
	#addPurchaseModal .select2-container--open {
		z-index: 1065 !important;
	}
	.select2-dropdown {
		z-index: 1065 !important;
	}
	.purchase-camera-modal {
		z-index: 1070 !important;
	}
	#addPurchaseModal .submit-section {
		padding-top: 12px;
		padding-bottom: 6px;
		text-align: center;
	}
	#addPurchaseModal .submit-btn {
		min-width: 150px;
		border-radius: 25px;
		padding: 10px 36px;
		font-weight: 500;
		font-size: 1rem;
	}
	#addPurchaseModal .purchase-expiry-switch {
		display: flex !important;
		align-items: center;
		justify-content: space-between;
		gap: 1rem;
		margin-top: .75rem;
		font-size: .875rem;
		font-weight: 400;
		color: #64748b;
		cursor: pointer;
		width: 100% !important;
	}
	#addPurchaseModal .purchase-expiry-switch input {
		appearance: none;
		-webkit-appearance: none;
		position: relative;
		width: 2.5rem;
		height: 1.35rem;
		margin: 0;
		border: 0;
		border-radius: 999px;
		background: #cbd5e1;
		cursor: pointer;
		flex-shrink: 0;
	}
	#addPurchaseModal .purchase-expiry-switch input::after {
		content: '';
		position: absolute;
		width: 1rem;
		height: 1rem;
		left: .18rem;
		top: .175rem;
		border-radius: 50%;
		background: #fff;
		transition: transform .2s ease;
	}
	#addPurchaseModal .purchase-expiry-switch input:checked { background: #76a7f7; }
	#addPurchaseModal .purchase-expiry-switch input:checked::after { transform: translateX(1.14rem); }
	@media (max-width: 767px) {
		#addPurchaseModal .modal-dialog {
			width: calc(100% - 1rem) !important;
			margin: .5rem auto;
		}
		#addPurchaseModal .modal-body {
			padding: 16px !important;
			max-height: calc(100vh - 120px);
		}
	}
	body.dark-mode #addPurchaseModal .modal-content {
		background: #1c2025;
		color: #e2e8f0;
		border: 1px solid #334155;
	}
	body.dark-mode #addPurchaseModal .modal-header {
		background: #1c2025;
		border-bottom-color: #334155;
	}
	body.dark-mode #addPurchaseModal .modal-title {
		color: #f8fafc;
	}
	body.dark-mode #addPurchaseModal .modal-header .close {
		color: #cbd5e1;
	}
	body.dark-mode #addPurchaseModal .modal-body {
		background: #1c2025;
	}
	body.dark-mode #addPurchaseModal label:not(.purchase-expiry-switch) {
		color: #cbd5e1;
	}
	body.dark-mode #addPurchaseModal .form-control {
		background-color: #12161a;
		border-color: #334155;
		color: #e2e8f0;
	}
	body.dark-mode #addPurchaseModal .select2-container .select2-selection--single {
		background-color: #12161a !important;
		border-color: #334155 !important;
		color: #e2e8f0 !important;
	}
	body.dark-mode #addPurchaseModal .select2-container .select2-selection--single .select2-selection__rendered {
		color: #e2e8f0 !important;
	}
	#purchaseProductDetailsModal .dashboard-product-details-layout {
		align-items: stretch;
		display: grid;
		gap: 24px;
		grid-template-columns: minmax(220px, .9fr) minmax(0, 1.35fr);
	}
	#purchaseProductDetailsModal .dashboard-product-details-image {
		align-items: center;
		background: #f5f7fb;
		border: 1px solid #e5eaf1;
		border-radius: 8px;
		display: flex;
		justify-content: center;
		min-height: 300px;
		padding: 18px;
	}
	#purchaseProductDetailsModal .dashboard-product-details-image img {
		border-radius: 6px;
		max-height: 300px;
		object-fit: contain;
		width: 100%;
	}
	#purchaseProductDetailsModal .dashboard-product-details-info {
		align-content: center;
		display: grid;
		gap: 12px 18px;
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}
	#purchaseProductDetailsModal .dashboard-product-details-info > div {
		border-bottom: 1px solid #edf0f4;
		padding-bottom: 8px;
		min-width: 0;
		position: relative;
	}
	#purchaseProductDetailsModal .dashboard-product-details-info strong {
		color: #7a8795;
		display: block;
		font-size: 12px;
		font-weight: 600;
		margin-bottom: 3px;
		text-transform: uppercase;
	}
	#purchaseProductDetailsModal .dashboard-product-details-info span {
		color: #263238;
		word-break: break-word;
		overflow-wrap: anywhere;
	}
	#purchaseProductDetailsModal .product-detail-value-wrap {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 6px;
		min-width: 0;
		position: relative;
	}
	#purchaseProductDetailsModal .product-detail-value-wrap .detail-product {
		min-width: 0;
		flex: 1 1 auto;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		display: block;
	}
	#purchaseProductDetailsModal .product-info-btn {
		flex: 0 0 auto;
		position: relative;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 1.5rem;
		height: 1.5rem;
		padding: 0;
		border: 0;
		background: transparent;
		color: #0f766e;
		cursor: help;
		font-size: 15px;
	}
	#purchaseProductDetailsModal .product-info-btn:hover,
	#purchaseProductDetailsModal .product-info-btn:focus {
		color: #0d9488;
	}
	#purchaseProductDetailsModal .product-info-btn::after {
		content: attr(data-tooltip);
		position: absolute;
		right: 0;
		bottom: calc(100% + 8px);
		width: 220px;
		max-height: 180px;
		overflow-y: auto;
		padding: 8px 10px;
		border-radius: 5px;
		background: #1f2937;
		color: #fff;
		font-size: 12px;
		line-height: 1.35;
		text-align: left;
		white-space: pre-wrap;
		word-break: break-all;
		overflow-wrap: anywhere;
		box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
		z-index: 1060;
		opacity: 0;
		visibility: hidden;
		pointer-events: none;
		transition: opacity .15s ease, visibility .15s ease;
	}
	#purchaseProductDetailsModal .product-info-btn:hover::after,
	#purchaseProductDetailsModal .product-info-btn:focus::after {
		opacity: 1;
		visibility: visible;
	}
	@media (max-width: 576px) {
		#purchaseProductDetailsModal .dashboard-product-details-layout,
		#purchaseProductDetailsModal .dashboard-product-details-info {
			grid-template-columns: 1fr;
		}
		#purchaseProductDetailsModal .dashboard-product-details-image {
			min-height: 220px;
		}
	}
	body.dark-mode #purchaseProductDetailsModal .dashboard-product-details-image {
		background: #1e293b;
		border-color: #334155;
	}
	body.dark-mode #purchaseProductDetailsModal .dashboard-product-details-info > div {
		border-bottom-color: #334155;
	}
	body.dark-mode #purchaseProductDetailsModal .dashboard-product-details-info strong {
		color: #94a3b8;
	}
	body.dark-mode #purchaseProductDetailsModal .dashboard-product-details-info span {
		color: #e2e8f0;
	}
	body.dark-mode #purchaseProductDetailsModal .product-info-btn {
		color: #14b8a6;
	}
	body.dark-mode #purchaseProductDetailsModal .product-info-btn:hover,
	body.dark-mode #purchaseProductDetailsModal .product-info-btn:focus {
		color: #2dd4bf;
	}
	body.dark-mode #purchaseProductDetailsModal .product-info-btn::after {
		background: #0f172a;
		border: 1px solid #334155;
		color: #f8fafc;
		box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5);
	}
</style>
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Purchase</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Purchase</li>
	</ul>
</div>
<div class="col-sm-5 col">
	<button type="button" class="btn btn-primary float-right mt-2" data-toggle="modal" data-target="#addPurchaseModal">Add New</button>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-md-12">
		<!-- Recent Orders -->
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<div id="purchase-table" class="tabulator-table-wrap"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="addPurchaseModal" tabindex="-1" role="dialog" aria-labelledby="addPurchaseModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="addPurchaseModalLabel">Add Purchase</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body custom-edit-service">
				@include('admin.purchases._form')
			</div>
		</div>
	</div>
</div>

<div class="modal" id="purchaseDetailsModal" tabindex="-1" role="dialog" aria-labelledby="purchaseDetailsModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="purchaseDetailsModalLabel">Purchase Details</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="mb-3"><strong>Supplier:</strong> <span class="purchase-detail detail-supplier"></span></div>
				<div class="mb-3"><strong>Products:</strong></div>
				<label class="d-block mb-2"><input type="checkbox" class="purchase-select-all mr-2">Select all</label>
				<div class="purchase-products-list"></div>
			</div>
			<div class="modal-footer">
				@if(auth()->user()->hasPermissionTo('destroy-purchase'))
				<button type="button" class="btn btn-danger purchase-bulk-delete" disabled>Delete selected</button>
				@endif
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal" id="purchaseProductDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Product Details</h5>
				<button type="button" class="close product-details-close" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="dashboard-product-details-layout">
					<div class="dashboard-product-details-image">
						<img class="purchase-product-detail-image" src="{{ asset('assets/img/productnoimage.png') }}" onerror="this.onerror=null;this.src='{{ asset('assets/img/productnoimage.png') }}';" alt="Product image">
					</div>
					<div class="dashboard-product-details-info">
						<div>
							<strong>Product</strong>
							<div class="product-detail-value-wrap">
								<span class="detail-product"></span>
								<button type="button" class="product-info-btn" aria-label="Product details" data-tooltip="Product: ">
									<i class="fas fa-info-circle" aria-hidden="true"></i>
								</button>
							</div>
						</div>
						<div><strong>Category</strong><span class="detail-category"></span></div>
						<div><strong>Supplier</strong><span class="detail-supplier"></span></div>
						<div><strong>Price</strong><span class="detail-price"></span></div>
						<div><strong>Quantity</strong><span class="detail-quantity"></span></div>
						<div><strong>Item Quantity</strong><span class="detail-item_quantity"></span></div>
						<div><strong>Packaging Box</strong><span class="detail-packaging_box"></span></div>
						<div><strong>Quantity per Box</strong><span class="detail-quantity_per_box"></span></div>
						<div><strong>Expire Date</strong><span class="detail-expiry"></span></div>
						<div><strong>Date of Purchase</strong><span class="detail-purchased"></span></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@if(request()->boolean('purchase_created'))
	<div id="purchase-created-notification" data-message="Successfully added a new purchase and it is now available in Products."></div>
@endif
@endsection	

@push('page-js')
<script>
    $(document).ready(function() {
        if (!window.PharmaTabulator) return;
        window.PharmaTabulator.server({
            el: 'purchase-table',
            url: "{{route('purchases.index')}}",
            columns: [
                {title: 'Supplier', field: 'supplier', formatter: 'html'},
                {title: 'Date Submitted', field: 'submitted_at'},
                {title: 'Action', field: 'action', formatter: 'html', headerSort: false, searchable: false, width: 110, hozAlign: 'center'},
            ]
        });

		var addPurchaseModal = $('#addPurchaseModal');
		var purchaseForm = addPurchaseModal.find('form');
		var categorySelect = purchaseForm.find('select[name="category"]');
		var supplierSelect = purchaseForm.find('select[name="supplier"]');
		var expiryInput = purchaseForm.find('input[name="expiry_date"]');
		var noExpiryInput = purchaseForm.find('input[name="no_expiry"]');

		function computePurchaseTotal() {
			var item = parseInt(purchaseForm.find('input[name="item_quantity"]').val() || 0, 10);
			var boxes = parseInt(purchaseForm.find('input[name="packaging_box"]').val() || 0, 10);
			var perBox = parseInt(purchaseForm.find('input[name="quantity_per_box"]').val() || 0, 10);
			purchaseForm.find('input[name="total_quantity"]').val(item + (boxes * perBox));
		}

		function syncPurchaseExpiry() {
			var categoryNoExpiry = categorySelect.find('option:selected').data('no-expiry') === 1 || categorySelect.find('option:selected').data('no-expiry') === '1';
			var noExpiry = categoryNoExpiry || noExpiryInput.is(':checked');
			noExpiryInput.prop('disabled', categoryNoExpiry);
			expiryInput.prop('disabled', noExpiry).prop('required', !noExpiry);
		}

		function initAddPurchaseSelect2() {
			if (!$.fn.select2) return;
			purchaseForm.find('select.select2').each(function () {
				var $sel = $(this);
				if ($sel.hasClass('select2-hidden-accessible')) {
					$sel.select2('destroy');
				}
				$sel.select2({
					dropdownParent: addPurchaseModal,
					width: '100%',
					placeholder: $sel.attr('data-placeholder') || 'Select an option',
					allowClear: Boolean($sel.data('allow-clear'))
				});
			});
		}

		addPurchaseModal.on('shown.bs.modal', function () {
			initAddPurchaseSelect2();
			computePurchaseTotal();
			syncPurchaseExpiry();
		});

		purchaseForm.find('input[name="item_quantity"], input[name="packaging_box"], input[name="quantity_per_box"]').on('input', computePurchaseTotal);

		categorySelect.on('change', function () {
			var categoryNoExpiry = categorySelect.find('option:selected').data('no-expiry') === 1 || categorySelect.find('option:selected').data('no-expiry') === '1';
			noExpiryInput.prop('checked', categoryNoExpiry).prop('disabled', categoryNoExpiry);
			syncPurchaseExpiry();
		});

		noExpiryInput.on('change', syncPurchaseExpiry);

		supplierSelect.on('change', function () {
			var option = $(this).find('option:selected');
			var product = option.data('product') || '';
			var category = option.data('category') || '';
			var cost = option.data('cost') || '';
			var expiry = option.data('expiry') || '';
			if (product) purchaseForm.find('input[name="product"]').val(product);
			if (category) categorySelect.val(category).trigger('change.select2').trigger('change');
			if (cost) purchaseForm.find('input[name="cost_price"]').val(cost);
			if (expiry) expiryInput.val(expiry);
			computePurchaseTotal();
		});

		var validationNoticeShown = false;
		if (purchaseForm.length) {
			purchaseForm[0].addEventListener('invalid', function (event) {
				event.preventDefault();
				var firstInvalid = purchaseForm[0].querySelector(':invalid');
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
		}

		var pendingModal = null;

		function animateModal(modalSelector, animationClass) {
			var content = $(modalSelector + ' .modal-content');
			content.removeClass('animate__animated animate__fadeIn animate__fadeOut');
			if (animationClass) {
				content.addClass('animate__animated ' + animationClass);
			}
		}

		function hideModalAfterFade(modalSelector) {
			var modal = $(modalSelector);
			var content = modal.find('.modal-content');
			var finished = false;
			var finish = function () {
				if (finished) return;
				finished = true;
				content.off('animationend.purchaseFade');
				modal.modal('hide');
			};

			content.one('animationend.purchaseFade', finish);
			setTimeout(finish, 400);
		}

		$('#purchaseDetailsModal').on('shown.bs.modal', function () {
			animateModal('#purchaseDetailsModal', 'animate__fadeIn');
		}).on('hidden.bs.modal', function () {
			animateModal('#purchaseDetailsModal', null);
			if (pendingModal === 'product') {
				pendingModal = null;
				$('#purchaseProductDetailsModal').modal('show');
			}
		});

		$('#purchaseProductDetailsModal').on('shown.bs.modal', function () {
			animateModal('#purchaseProductDetailsModal', 'animate__fadeIn');
		}).on('hidden.bs.modal', function () {
			animateModal('#purchaseProductDetailsModal', null);
			if (pendingModal === 'purchase') {
				pendingModal = null;
				$('#purchaseDetailsModal').modal('show');
			}
		});

		$(document).on('click', '.purchase-detail-btn', function () {
            var button = $(this);
			var products = JSON.parse(button.attr('data-products') || '[]');
            $('#purchaseDetailsModal .detail-supplier').text(button.data('supplier'));
			var list = $('#purchaseDetailsModal .purchase-products-list').empty();
			products.forEach(function (product, index) {
				var rawName = (product.product || '').trim();
				var limit = 8;
				var displayName = rawName.length > limit ? rawName.slice(0, limit).trimEnd() + '..' : rawName;
				var item = $('<label class="purchase-product-option"></label>');
				var checkbox = $('<input type="checkbox" class="purchase-product-checkbox mr-2">')
					.val(product.id)
					.attr('data-product-details', JSON.stringify(product));
				var link = $('<button type="button" class="btn btn-link purchase-product-link p-0"></button>')
					.text(displayName)
					.attr('title', rawName)
					.attr('data-product-details', JSON.stringify(product));
				item.append(checkbox).append(link);
				list.append(item);
			});
			$('#purchaseDetailsModal .purchase-select-all').prop('checked', false);
			$('#purchaseDetailsModal .purchase-bulk-delete').prop('disabled', true);
			$('#purchaseDetailsModal').modal('show');
        });

		$('#purchaseDetailsModal').on('change', '.purchase-product-checkbox', function () {
			var selected = $('#purchaseDetailsModal .purchase-product-checkbox:checked').length;
			var total = $('#purchaseDetailsModal .purchase-product-checkbox').length;
			$('#purchaseDetailsModal .purchase-select-all').prop('checked', total > 0 && selected === total);
			$('#purchaseDetailsModal .purchase-bulk-delete').prop('disabled', selected === 0);
		});

		$('#purchaseDetailsModal').on('change', '.purchase-select-all', function () {
			var checked = $(this).prop('checked');
			$('#purchaseDetailsModal .purchase-product-checkbox').prop('checked', checked).trigger('change');
		});

		$('#purchaseDetailsModal').on('click', '.purchase-bulk-delete', function () {
			var ids = $('#purchaseDetailsModal .purchase-product-checkbox:checked').map(function () {
				return $(this).val();
			}).get();

			if (!ids.length || !window.confirm('Delete the selected purchase(s)?')) return;

			$.ajax({
				url: "{{ route('purchases.bulk-destroy') }}",
				type: 'DELETE',
				data: {ids: ids, _token: $('meta[name="csrf-token"]').attr('content')},
				success: function (response) {
					$('#purchaseDetailsModal').modal('hide');
					if (window.PharmaTabulator) {
						window.PharmaTabulator.reload('purchase-table');
					}
					if (window.Snackbar) {
						Snackbar.show({text: response.message, pos: 'top-right', actionTextColor: '#fff', backgroundColor: '#8dbf42'});
					}
				},
				error: function () {
					if (window.Snackbar) {
						Snackbar.show({text: 'The selected purchases could not be deleted.', pos: 'top-right', actionTextColor: '#fff', backgroundColor: '#e7515a'});
					}
				}
			});
		});

		$('#purchaseDetailsModal').on('click', '.purchase-product-link', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var details = JSON.parse($(this).attr('data-product-details') || '{}');
			var defaultImage = "{{ asset('assets/img/productnoimage.png') }}";
			$('#purchaseProductDetailsModal .purchase-product-detail-image').attr('src', details.image || defaultImage);
			if (!details.price && details.cost) {
				details.price = details.cost;
			}
			var productName = details.product || '';
			var productTooltip = productName ? 'Product:\n' + productName : 'No product name';
			$('#purchaseProductDetailsModal .product-info-btn')
				.attr('data-tooltip', productTooltip)
				.attr('aria-label', productTooltip);
			$('#purchaseProductDetailsModal .detail-product').attr('title', productName);

			Object.keys(details).forEach(function (key) {
				var value = details[key];
				if (key === 'image') return;
				$('#purchaseProductDetailsModal .detail-' + key).text(value || '');
			});
			var expiryVal = details.expiry || details.expiry_date || '';
			$('#purchaseProductDetailsModal .detail-expiry').text(expiryVal && expiryVal !== '-' ? expiryVal : 'No expiry');
			pendingModal = 'product';
			animateModal('#purchaseDetailsModal', 'animate__fadeOut');
			hideModalAfterFade('#purchaseDetailsModal');
		});

		$('#purchaseProductDetailsModal').on('click', '.product-details-close', function () {
			pendingModal = 'purchase';
			animateModal('#purchaseProductDetailsModal', 'animate__fadeOut');
			hideModalAfterFade('#purchaseProductDetailsModal');
		});
        
    });
</script> 
@endpush