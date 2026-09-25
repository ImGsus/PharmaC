@extends('admin.layouts.app')

<x-assets.tabulator />

@push('page-css')
<style>
	#productDetailsModal .modal-dialog {
		max-width: 760px;
	}
	#productDetailsModal .product-details-layout {
		display: grid;
		grid-template-columns: minmax(220px, .9fr) minmax(0, 1.35fr);
		gap: 24px;
		align-items: stretch;
	}
	#productDetailsModal .product-details-image {
		display: flex;
		align-items: center;
		justify-content: center;
		min-height: 300px;
		padding: 18px;
		background: #f5f7fb;
		border: 1px solid #e5eaf1;
		border-radius: 8px;
	}
	#productDetailsModal .product-details-image img {
		width: 100%;
		max-height: 300px;
		object-fit: contain;
		border-radius: 6px;
	}
	#productDetailsModal .product-details-info {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: 12px 18px;
		align-content: center;
	}
	#productDetailsModal .product-detail-item {
		margin: 0;
		padding-bottom: 8px;
		border-bottom: 1px solid #edf0f4;
		min-width: 0;
		position: relative;
	}
	#productDetailsModal .product-detail-item strong {
		display: block;
		margin-bottom: 3px;
		font-size: 12px;
		font-weight: 600;
		color: #7a8795;
		text-transform: uppercase;
	}
	#productDetailsModal .product-detail-item span {
		color: #263238;
		word-break: break-word;
		overflow-wrap: anywhere;
	}
	#productDetailsModal .product-detail-value-wrap {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 6px;
		min-width: 0;
		position: relative;
	}
	#productDetailsModal .product-detail-value-wrap .detail-product {
		min-width: 0;
		flex: 1 1 auto;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		display: block;
		border: 0 !important;
		border-radius: 0 !important;
		padding: 0 !important;
		background: transparent !important;
		background-color: transparent !important;
		margin: 0 !important;
		box-shadow: none !important;
		transition: none !important;
	}
	#productDetailsModal .product-info-btn {
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
	#productDetailsModal .product-info-btn:hover,
	#productDetailsModal .product-info-btn:focus {
		color: #0d9488;
	}
	#productDetailsModal .product-info-btn::after {
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
	#productDetailsModal .product-info-btn:hover::after,
	#productDetailsModal .product-info-btn:focus::after {
		opacity: 1;
		visibility: visible;
	}
	@media (max-width: 576px) {
		#productDetailsModal .product-details-layout,
		#productDetailsModal .product-details-info {
			grid-template-columns: 1fr;
		}
		#productDetailsModal .product-details-image {
			min-height: 220px;
		}
	}
	body.dark-mode #productDetailsModal .product-details-image {
		background: #111827;
		border-color: #334155;
	}
	body.dark-mode #productDetailsModal .product-detail-item {
		border-bottom-color: #334155;
	}
	body.dark-mode #productDetailsModal .product-detail-item strong {
		color: #94a3b8;
	}
	body.dark-mode #productDetailsModal .product-detail-item span {
		color: #f8fafc;
	}
	body.dark-mode #productDetailsModal .detail-product {
		background: transparent !important;
		background-color: transparent !important;
		border: 0 !important;
	}
	body.dark-mode #productDetailsModal .product-info-btn {
		color: #14b8a6;
	}
	body.dark-mode #productDetailsModal .product-info-btn:hover,
	body.dark-mode #productDetailsModal .product-info-btn:focus {
		color: #2dd4bf;
	}
	body.dark-mode #productDetailsModal .product-info-btn::after {
		background: #0f172a;
		border: 1px solid #334155;
		color: #f8fafc;
		box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5);
	}

	/* ==== Add Product modal (styled after the purchases "Add Purchase" modal) ==== */
	#addProductModal .modal-content {
		--animate-duration: .35s;
	}
	#addProductModal .modal-dialog {
		max-width: 1240px !important;
		width: 95vw !important;
		margin: 1.75rem auto;
	}
	#addProductModal .modal-content {
		border: 0;
		border-radius: 10px;
		box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
		max-width: 100%;
		overflow: hidden;
	}
	#addProductModal .modal-header {
		padding: 18px 26px;
		border-bottom: 1px solid #e9ecef;
		background: #fff;
		display: flex;
		align-items: center;
		justify-content: space-between;
	}
	#addProductModal .modal-title {
		font-size: 1.25rem;
		font-weight: 600;
		color: #1f2937;
	}
	#addProductModal .modal-body {
		max-height: calc(100vh - 150px);
		overflow-y: auto;
		overflow-x: hidden;
		padding: 26px 30px !important;
		background: #fff;
	}
	#addProductModal .service-fields {
		margin-bottom: 1.25rem;
	}
	#addProductModal .form-group {
		margin-bottom: 1.25rem;
		position: relative;
	}
	#addProductModal label:not(.purchase-expiry-switch) {
		display: block !important;
		width: 100% !important;
		margin-bottom: 0.5rem;
		font-weight: 500;
		color: #374151;
		float: none !important;
		clear: both !important;
	}
	#addProductModal .form-control {
		display: block !important;
		width: 100% !important;
		height: 42px;
		border: 1px solid #dcdcdc;
		border-radius: 5px;
		box-sizing: border-box;
	}
	#addProductModal .select2-container {
		display: block !important;
		width: 100% !important;
		max-width: 100% !important;
		box-sizing: border-box;
		float: none !important;
		clear: both !important;
	}
	#addProductModal .select2-container .select2-selection--single {
		display: flex !important;
		align-items: center;
		height: 42px !important;
		border: 1px solid #dcdcdc !important;
		border-radius: 5px !important;
		width: 100% !important;
		box-sizing: border-box;
		background-color: #fff;
	}
	#addProductModal .select2-container .select2-selection--single .select2-selection__rendered {
		line-height: 40px !important;
		padding-left: 12px !important;
		padding-right: 32px !important;
		color: #495057;
		width: 100%;
		display: block !important;
	}
	#addProductModal .select2-container .select2-selection--single .select2-selection__arrow {
		height: 40px !important;
		right: 8px !important;
		top: 1px !important;
	}
	#addProductModal .select2-container--open {
		z-index: 1065 !important;
	}
	.select2-dropdown {
		z-index: 1065 !important;
	}
	.purchase-camera-modal {
		z-index: 1070 !important;
	}
	#addProductModal .submit-section {
		padding-top: 12px;
		padding-bottom: 6px;
		text-align: center;
	}
	#addProductModal .submit-btn {
		min-width: 150px;
		border-radius: 25px;
		padding: 10px 36px;
		font-weight: 500;
		font-size: 1rem;
	}
	#addProductModal .purchase-expiry-switch {
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
	#addProductModal .purchase-expiry-switch input {
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
	#addProductModal .purchase-expiry-switch input::after {
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
	#addProductModal .purchase-expiry-switch input:checked { background: #76a7f7; }
	#addProductModal .purchase-expiry-switch input:checked::after { transform: translateX(1.14rem); }
	@media (max-width: 767px) {
		#addProductModal .modal-dialog {
			width: calc(100% - 1rem) !important;
			margin: .5rem auto;
		}
		#addProductModal .modal-body {
			padding: 16px !important;
			max-height: calc(100vh - 120px);
		}
	}
	body.dark-mode #addProductModal .modal-content {
		background: #1c2025;
		color: #e2e8f0;
		border: 1px solid #334155;
	}
	body.dark-mode #addProductModal .modal-header {
		background: #1c2025;
		border-bottom-color: #334155;
	}
	body.dark-mode #addProductModal .modal-title {
		color: #f8fafc;
	}
	body.dark-mode #addProductModal .modal-header .close {
		color: #cbd5e1;
	}
	body.dark-mode #addProductModal .modal-body {
		background: #1c2025;
	}
	body.dark-mode #addProductModal label:not(.purchase-expiry-switch) {
		color: #cbd5e1;
	}
	body.dark-mode #addProductModal .form-control {
		background-color: #12161a;
		border-color: #334155;
		color: #e2e8f0;
	}
	body.dark-mode #addProductModal .select2-container .select2-selection--single {
		background-color: #12161a !important;
		border-color: #334155 !important;
		color: #e2e8f0 !important;
	}
	body.dark-mode #addProductModal .select2-container .select2-selection--single .select2-selection__rendered {
		color: #e2e8f0 !important;
	}

	/* ==== Product status toggle (Active / Not Active) ==== */
	.product-status-text.active {
		color: #198754;
		font-weight: 600;
		white-space: nowrap;
	}
	.product-status-text.inactive {
		color: #e7515a;
		font-weight: 600;
		white-space: nowrap;
	}
	.product-action-cell {
		display: inline-flex;
		align-items: center;
		gap: 6px;
	}
	.product-action-button.is-active,
	.product-action-button.is-active:hover,
	.product-action-button.is-active:focus,
	.product-action-button.is-active.show {
		background: #6c757d !important;
		background-image: none !important;
		border-color: #6c757d !important;
		color: #fff !important;
		box-shadow: 0 3px 2px -2px rgba(40, 167, 69, .8), 0 4px 3px -2px rgba(40, 167, 69, .8) !important;
	}
	.product-action-button.is-active:hover,
	.product-action-button.is-active:focus,
	.product-action-button.is-active.show {
		background: #5a6268 !important;
		border-color: #5a6268 !important;
		box-shadow: 0 3px 2px -2px rgba(40, 167, 69, .9), 0 4px 3px -2px rgba(40, 167, 69, .9) !important;
	}
	.product-action-button.is-inactive,
	.product-action-button.is-inactive:hover,
	.product-action-button.is-inactive:focus,
	.product-action-button.is-inactive.show {
		background: #6c757d !important;
		background-image: none !important;
		border-color: #6c757d !important;
		color: #fff !important;
		box-shadow: 0 3px 2px -2px rgba(220, 53, 69, .8), 0 4px 3px -2px rgba(220, 53, 69, .8) !important;
	}
	.product-action-button.is-inactive:hover,
	.product-action-button.is-inactive:focus,
	.product-action-button.is-inactive.show {
		background: #5a6268 !important;
		border-color: #5a6268 !important;
		box-shadow: 0 3px 2px -2px rgba(220, 53, 69, .9), 0 4px 3px -2px rgba(220, 53, 69, .9) !important;
	}
	.dropdown-menu .product-status-menu-item {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 10px;
		margin: 0;
		cursor: pointer;
		font-weight: 500;
	}
	.product-status-menu-text {
		font-size: 13px;
		color: #3b4752;
		white-space: nowrap;
	}
	body.dark-mode .product-status-menu-text {
		color: #e2e8f0;
	}
	body.dark-mode .dropdown-menu .product-status-menu-item:hover .product-status-menu-text {
		color: #0f172a;
	}
	.product-active-switch {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		margin: 0;
		padding: 0;
		line-height: 1;
	}
	.product-active-switch input {
		appearance: none;
		-webkit-appearance: none;
		position: relative;
		width: 2.1rem;
		height: 1.15rem;
		margin: 0;
		border: 0;
		border-radius: 999px;
		background: #e7515a;
		box-shadow: inset 0 0 0 1px rgba(147, 34, 40, .25);
		cursor: pointer;
		transition: background-color .2s ease;
	}
	.product-active-switch input::after {
		content: '';
		position: absolute;
		width: .85rem;
		height: .85rem;
		left: .16rem;
		top: .14rem;
		border-radius: 50%;
		background: #fff;
		box-shadow: 0 1px 3px rgba(15, 23, 42, .3);
		transition: transform .2s ease;
	}
	.product-active-switch input:checked {
		background: #198754;
	}
	.product-active-switch input:checked::after {
		transform: translateX(.95rem);
	}
	.product-active-switch input:disabled {
		opacity: .55;
		cursor: not-allowed;
	}
	.product-active-switch.is-busy input {
		pointer-events: none;
	}
	body.dark-mode .product-active-switch input {
		box-shadow: inset 0 0 0 1px rgba(248, 113, 113, .35);
	}
	body.dark-mode .product-status-text.active {
		color: #4ade80;
	}
	body.dark-mode .product-status-text.inactive {
		color: #f87171;
	}

</style>
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Products</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Products</li>
	</ul>
</div>
<div class="col-sm-5 col">
	<button type="button" class="btn btn-primary float-right mt-2" data-toggle="modal" data-target="#addProductModal">Add Product</button>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-md-12">
	
		<!-- Products -->
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<div id="product-table" class="tabulator-table-wrap"></div>
				</div>
			</div>
		</div>
		<!-- /Products -->
		
	</div>
</div>

<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="addProductModalLabel">Add Purchase <span aria-hidden="true">-&gt;</span> <span class="text-primary">New Product</span></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body custom-edit-service">
				@include('admin.purchases._form')
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="productDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header"><h5 class="modal-title">Product Details</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
			<div class="modal-body">
				<div class="product-details-layout">
					<div class="product-details-image">
						<img class="product-detail detail-image" src="{{ asset('assets/img/productnoimage.png') }}" onerror="this.onerror=null;this.src='{{ asset('assets/img/productnoimage.png') }}';" alt="Product image">
					</div>
					<div class="product-details-info">
						<div class="product-detail-item">
							<strong>Product</strong>
							<div class="product-detail-value-wrap">
								<span class="product-detail detail-product product-name"></span>
								<button type="button" class="product-info-btn" aria-label="Product details" data-tooltip="Product: ">
									<i class="fas fa-info-circle" aria-hidden="true"></i>
								</button>
							</div>
						</div>
						<div class="product-detail-item"><strong>Category</strong><span class="product-detail detail-category"></span></div>
						<div class="product-detail-item"><strong>Supplier</strong><span class="product-detail detail-supplier"></span></div>
						<div class="product-detail-item"><strong>Price</strong><span class="product-detail detail-price"></span></div>
						<div class="product-detail-item"><strong>Quantity</strong><span class="product-detail detail-quantity"></span></div>
						<div class="product-detail-item"><strong>Item Quantity</strong><span class="product-detail detail-item_quantity"></span></div>
						<div class="product-detail-item"><strong>Packaging Box</strong><span class="product-detail detail-packaging_box"></span></div>
						<div class="product-detail-item"><strong>Quantity per Box</strong><span class="product-detail detail-quantity_per_box"></span></div>
						<div class="product-detail-item"><strong>Expiry Date</strong><span class="product-detail detail-expiry"></span></div>
						<div class="product-detail-item"><strong>Date of Purchase</strong><span class="product-detail detail-purchased"></span></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@push('page-js')
<script>
    $(document).ready(function() {
        if (window.PharmaTabulator) {
            window.PharmaTabulator.server({
                el: 'product-table',
                url: "{{route('products.index')}}",
                columns: [
                    {title: 'Product Name', field: 'product', formatter: 'html'},
                    {title: 'Category', field: 'category'},
                    {title: 'Status', field: 'status', formatter: 'html'},
                    {title: 'Quantity', field: 'quantity'},
                    {title: 'Action', field: 'action', formatter: 'html', headerSort: false, searchable: false, width: 150, hozAlign: 'center'},
                ]
            });
        }

		$(document).on('click', '.product-detail-btn', function () {
			var details = JSON.parse($(this).attr('data-details') || '{}');
			var defaultImage = '{{ asset('assets/img/productnoimage.png') }}';
			$('#productDetailsModal .detail-image').attr('src', details.image || defaultImage);

			var productName = details.product || '';
			var productTooltip = productName ? 'Product:\n' + productName : 'No product name';
			$('#productDetailsModal .product-info-btn')
				.attr('data-tooltip', productTooltip)
				.attr('aria-label', productTooltip);
			$('#productDetailsModal .detail-product').attr('title', productName).text(productName);

			Object.keys(details).forEach(function (key) {
				if (key === 'image' || key === 'product') return;
				$('#productDetailsModal .detail-' + key).text(details[key] || '');
			});
			var expiryVal = details.expiry || details.expiry_date || '';
			$('#productDetailsModal .detail-expiry').text(expiryVal && expiryVal !== '-' ? expiryVal : 'No expiry');
			$('#productDetailsModal').modal('show');
		});

		/* ---- Add Product modal (reuses the purchase form, styled like "Add Purchase") ---- */
		var addProductModal = $('#addProductModal');
		var addProductForm = addProductModal.find('form');
		var addProductCategory = addProductForm.find('select[name="category"]');
		var addProductSupplier = addProductForm.find('select[name="supplier"]');
		var addProductExpiry = addProductForm.find('input[name="expiry_date"]');
		var addProductNoExpiry = addProductForm.find('input[name="no_expiry"]');

		function computeAddProductTotal() {
			var item = parseInt(addProductForm.find('input[name="item_quantity"]').val() || 0, 10);
			var boxes = parseInt(addProductForm.find('input[name="packaging_box"]').val() || 0, 10);
			var perBox = parseInt(addProductForm.find('input[name="quantity_per_box"]').val() || 0, 10);
			addProductForm.find('input[name="total_quantity"]').val(item + (boxes * perBox));
		}

		function syncAddProductExpiry() {
			var categoryNoExpiry = addProductCategory.find('option:selected').data('no-expiry') === 1 || addProductCategory.find('option:selected').data('no-expiry') === '1';
			var noExpiry = categoryNoExpiry || addProductNoExpiry.is(':checked');
			addProductNoExpiry.prop('disabled', categoryNoExpiry);
			addProductExpiry.prop('disabled', noExpiry).prop('required', !noExpiry);
		}

		function initAddProductSelect2() {
			if (!$.fn.select2) return;
			addProductForm.find('select.select2').each(function () {
				var $sel = $(this);
				if ($sel.hasClass('select2-hidden-accessible')) {
					$sel.select2('destroy');
				}
				$sel.select2({
					dropdownParent: addProductModal,
					width: '100%',
					placeholder: $sel.attr('data-placeholder') || 'Select an option',
					allowClear: Boolean($sel.data('allow-clear'))
				});
			});
		}

		if (addProductModal.length) {
			addProductModal.on('shown.bs.modal', function () {
				initAddProductSelect2();
				computeAddProductTotal();
				syncAddProductExpiry();
			});

			addProductForm.find('input[name="item_quantity"], input[name="packaging_box"], input[name="quantity_per_box"]').on('input', computeAddProductTotal);

			addProductCategory.on('change', function () {
				var categoryNoExpiry = addProductCategory.find('option:selected').data('no-expiry') === 1 || addProductCategory.find('option:selected').data('no-expiry') === '1';
				addProductNoExpiry.prop('checked', categoryNoExpiry).prop('disabled', categoryNoExpiry);
				syncAddProductExpiry();
			});

			addProductNoExpiry.on('change', syncAddProductExpiry);

			addProductSupplier.on('change', function () {
				var option = $(this).find('option:selected');
				var product = option.data('product') || '';
				var category = option.data('category') || '';
				var cost = option.data('cost') || '';
				var expiry = option.data('expiry') || '';
				if (product) addProductForm.find('input[name="product"]').val(product);
				if (category) addProductCategory.val(category).trigger('change.select2').trigger('change');
				if (cost) addProductForm.find('input[name="cost_price"]').val(cost);
				if (expiry) addProductExpiry.val(expiry);
				computeAddProductTotal();
			});

			var addProductValidationNoticeShown = false;
			if (addProductForm.length) {
				addProductForm[0].addEventListener('invalid', function (event) {
					event.preventDefault();
					var firstInvalid = addProductForm[0].querySelector(':invalid');
					if (firstInvalid) firstInvalid.focus();
					if (!addProductValidationNoticeShown && window.Snackbar) {
						addProductValidationNoticeShown = true;
						Snackbar.show({
							text: 'Please complete all required fields before submitting.',
							pos: 'top-right',
							actionTextColor: '#fff',
							backgroundColor: '#e7515a'
						});
						setTimeout(function () {
							addProductValidationNoticeShown = false;
						}, 500);
					}
				}, true);
			}

			// Re-open the modal if the page reloaded with server-side validation errors
			if (addProductForm.find('.purchase-validation-alert').length || addProductForm.find('.alert-danger').length) {
				addProductModal.modal('show');
			}
		}
/* ---- Product Active / Not Active toggle (inside Action dropdown) ---- */
		// Clicking the switch inside the dropdown must not close the menu.
		$(document).on('click', '.product-status-menu-item, .product-active-toggle, .product-active-switch', function (event) {
			event.stopPropagation();
		});
		$(document).on('change', '.product-active-toggle', function () {
			var $toggle = $(this);
			var $switch = $toggle.closest('.product-active-switch');
			var $cell = $toggle.closest('.product-action-cell');
			var $button = $cell.find('.product-action-button');
			var $menuText = $toggle.closest('.product-status-menu-item').find('.product-status-menu-text');
			var isActive = $toggle.prop('checked');
			var url = $toggle.data('url');

			$switch.addClass('is-busy');
			$.ajax({
				url: url,
				type: 'POST',
				data: {
					is_active: isActive ? 1 : 0,
					_token: $('meta[name="csrf-token"]').attr('content')
				},
				success: function (response) {
					$switch.removeClass('is-busy');
					// Update the Action button border + menu label instantly (before table reload).
					$cell.attr('data-active', isActive ? '1' : '0');
					$button.removeClass('is-active is-inactive').addClass(isActive ? 'is-active' : 'is-inactive');
					$menuText.text(isActive ? 'Active' : 'Not Active');
					if (window.Snackbar) {
						Snackbar.show({
							text: response.message,
							pos: 'top-right',
							actionTextColor: '#fff',
							backgroundColor: isActive ? '#8dbf42' : '#e7515a'
						});
					}
					if (window.PharmaTabulator) {
						window.PharmaTabulator.reload('product-table');
					}
				},
				error: function (xhr) {
					$toggle.prop('checked', !isActive);
					$switch.removeClass('is-busy');
					if (window.Snackbar) {
						var serverMessage = (xhr && xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Could not update the product status.';
						Snackbar.show({
							text: serverMessage,
							pos: 'top-right',
							actionTextColor: '#fff',
							backgroundColor: '#e7515a'
						});
					}
				}
			});
		});
        
    });
</script> 
@endpush