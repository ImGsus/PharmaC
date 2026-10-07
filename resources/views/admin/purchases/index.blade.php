@extends('admin.layouts.app')

<x-assets.tabulator />

@push('page-css')
<style>
	.purchase-products-list .purchase-product-option {
		display: flex;
		align-items: center;
		gap: 9px;
		min-width: 0;
		width: 100%;
		min-height: 40px;
		margin: 0;
		padding: 7px 9px;
		border: 1px solid #e2e8f0;
		border-radius: 6px;
		box-sizing: border-box;
		cursor: default;
		font-weight: 400;
		line-height: 1.35;
	}

	.purchase-products-list {
		display: grid;
		grid-template-columns: repeat(3, minmax(0, 1fr));
		gap: 8px;
		width: 100%;
		max-width: 100%;
	}

	.purchase-detail-toolbar {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		margin: 0 0 12px;
	}

	.purchase-detail-search {
		flex: 1 1 220px;
		max-width: 280px;
	}

	.purchase-detail-pagination {
		display: flex;
		align-items: center;
		justify-content: flex-end;
		gap: 6px;
		margin-top: 12px;
	}

	.purchase-detail-page-size {
		display: flex;
		align-items: center;
		gap: 6px;
		margin-right: auto;
		white-space: nowrap;
	}

	.purchase-detail-page-size select {
		width: 64px;
		padding: 3px 6px;
	}

	.purchase-detail-pagination .btn {
		flex: 0 0 auto;
		min-width: 34px;
		padding: 4px 8px;
		border: 1px solid #cbd5e1 !important;
		border-radius: 6px;
		background-color: #f8fafc !important;
		color: #334155 !important;
		font-weight: 500;
		line-height: 1.35;
		opacity: 1;
	}

	.purchase-detail-pagination .btn:disabled {
		background-color: #f1f5f9 !important;
		color: #64748b !important;
		cursor: not-allowed;
		opacity: 1;
	}

	body.dark-mode .purchase-detail-pagination .btn {
		border-color: #475569 !important;
		background-color: #111827 !important;
		color: #e2e8f0 !important;
	}

	body.dark-mode .purchase-detail-pagination .btn:disabled {
		background-color: #1f2937 !important;
		color: #94a3b8 !important;
	}

	.purchase-detail-pagination .purchase-detail-page-status {
		min-width: 34px;
		text-align: center;
	}

	.purchase-select-all-option {
		display: flex;
		align-items: center;
		gap: 9px;
		margin: 0 0 12px;
		line-height: 1.35;
		cursor: pointer;
	}

	.purchase-products-list .purchase-product-link {
		display: block;
		flex: 1 1 auto;
		min-width: 0;
		max-width: 100%;
		margin: 0;
		padding: 0;
		line-height: 1.35;
		text-align: left;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		color: #2563eb !important;
		text-decoration: none;
		cursor: pointer;
	}

	.purchase-products-list .purchase-product-link:hover {
		color: #1d4ed8 !important;
		text-decoration: underline;
	}

	body.dark-mode .purchase-products-list .purchase-product-link {
		color: #60a5fa !important;
	}

	body.dark-mode .purchase-products-list .purchase-product-link:hover {
		color: #93c5fd !important;
		text-decoration: underline;
	}

	.purchase-products-list .purchase-product-checkbox {
		flex: 0 0 auto;
		width: 16px;
		height: 16px;
		margin: 0 !important;
	}

	body.dark-mode #purchaseDetailsModal .purchase-product-option { border-color: #374151; }

	#rowActionPopup .row-action-popup-menu > .purchase-detail-btn,
	#rowActionPopup .row-action-popup-menu > .purchase-detail-btn:hover,
	#rowActionPopup .row-action-popup-menu > .purchase-detail-btn:focus {
		color: #334155 !important;
	}

	body.dark-mode #rowActionPopup .row-action-popup-menu > .purchase-detail-btn,
	body.dark-mode #rowActionPopup .row-action-popup-menu > .purchase-detail-btn:hover,
	body.dark-mode #rowActionPopup .row-action-popup-menu > .purchase-detail-btn:focus {
		color: #e2e8f0 !important;
	}

	@media (max-width: 575.98px) {
		.purchase-products-list { grid-template-columns: 1fr; }
		.purchase-detail-toolbar { align-items: stretch; flex-direction: column; }
		.purchase-detail-search { flex: 0 0 auto; max-width: none; }
		.purchase-detail-pagination { flex-wrap: wrap; }
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

<div class="modal fade" id="purchaseDetailsModal" tabindex="-1" role="dialog" aria-labelledby="purchaseDetailsModalLabel" aria-hidden="true">
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
				<label class="purchase-select-all-option"><input type="checkbox" class="purchase-select-all">Select all</label>
				<div class="purchase-detail-toolbar">
					<input type="search" class="form-control form-control-sm purchase-detail-search" placeholder="Search products..." aria-label="Search products">
					<div class="purchase-detail-count text-muted small" aria-live="polite"></div>
				</div>
				<div class="purchase-products-list"></div>
				<nav class="purchase-detail-pagination" aria-label="Purchase product pages">
					<div class="purchase-detail-page-size">
						<span>Page Size</span>
						<select class="form-control form-control-sm" aria-label="Products per page" disabled><option>12</option></select>
					</div>
					<button type="button" class="btn btn-sm btn-outline-secondary purchase-detail-first">First</button>
					<button type="button" class="btn btn-sm btn-outline-secondary purchase-detail-prev">Prev</button>
					<span class="purchase-detail-page-status" aria-live="polite" aria-label="Page 1">1</span>
					<button type="button" class="btn btn-sm btn-outline-secondary purchase-detail-next">Next</button>
					<button type="button" class="btn btn-sm btn-outline-secondary purchase-detail-last">Last</button>
				</nav>
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

<div class="modal fade" id="purchaseProductDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Product Details</h5>
				<button type="button" class="close product-details-close" data-dismiss="modal" aria-label="Close">
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
						<div class="detail-box-expiries-wrap" style="grid-column: 1 / -1; display: none;">
							<strong>Packaging Box Expiries</strong>
							<div class="detail-box-expiries-list d-flex flex-wrap mt-1" style="gap: 6px;"></div>
						</div>
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
<script src="{{asset('assets/js/purchase-multi-step.js')}}"></script>
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

		if (addPurchaseModal.length) {
			addPurchaseModal.on('shown.bs.modal', function () {
				initAddPurchaseSelect2();
			});

			if (window.initPurchaseMultiStep) {
				window.initPurchaseMultiStep(purchaseForm, addPurchaseModal);
			}

			if (purchaseForm.find('.purchase-validation-alert').length || purchaseForm.find('.alert-danger').length) {
				addPurchaseModal.modal('show');
			}
		}

		var purchaseDetailProducts = [];
		var purchaseDetailSelectedIds = {};
		var purchaseDetailPage = 1;
		var purchaseDetailPageSize = 12;

		function renderPurchaseDetailProducts() {
			var modal = $('#purchaseDetailsModal');
			var query = (modal.find('.purchase-detail-search').val() || '').trim().toLowerCase();
			var filteredProducts = purchaseDetailProducts.filter(function (product) {
				return (product.product || '').toLowerCase().indexOf(query) !== -1;
			});
			var totalPages = Math.max(1, Math.ceil(filteredProducts.length / purchaseDetailPageSize));
			purchaseDetailPage = Math.min(Math.max(purchaseDetailPage, 1), totalPages);
			var start = (purchaseDetailPage - 1) * purchaseDetailPageSize;
			var pageProducts = filteredProducts.slice(start, start + purchaseDetailPageSize);
			var list = modal.find('.purchase-products-list').empty();

			pageProducts.forEach(function (product) {
				var rawName = (product.product || '').trim();
				var item = $('<div class="purchase-product-option"></div>');
				var checkbox = $('<input type="checkbox" class="purchase-product-checkbox">')
					.val(product.id)
					.prop('checked', Boolean(purchaseDetailSelectedIds[String(product.id)]))
					.attr('data-product-details', JSON.stringify(product));
				var link = $('<button type="button" class="btn btn-link purchase-product-link"></button>')
					.text(rawName)
					.attr('title', rawName)
					.attr('data-product-details', JSON.stringify(product));
				item.append(checkbox).append(link);
				list.append(item);
			});

			var selectedCount = Object.keys(purchaseDetailSelectedIds).length;
			modal.find('.purchase-detail-count').text(filteredProducts.length
				? 'Showing ' + (start + 1) + '-' + Math.min(start + purchaseDetailPageSize, filteredProducts.length) + ' of ' + filteredProducts.length
				: 'No products found');
			modal.find('.purchase-detail-page-status').text(purchaseDetailPage).attr('aria-label', 'Page ' + purchaseDetailPage + ' of ' + totalPages);
			modal.find('.purchase-detail-first, .purchase-detail-prev').prop('disabled', purchaseDetailPage <= 1);
			modal.find('.purchase-detail-next, .purchase-detail-last').prop('disabled', purchaseDetailPage >= totalPages);
			modal.find('.purchase-select-all').prop('checked', filteredProducts.length > 0 && filteredProducts.every(function (product) {
				return Boolean(purchaseDetailSelectedIds[String(product.id)]);
			}));
			modal.find('.purchase-bulk-delete').prop('disabled', selectedCount === 0);
		}

		$(document).on('click', '.purchase-detail-btn', function () {
            var button = $(this);
			purchaseDetailProducts = JSON.parse(button.attr('data-products') || '[]');
			purchaseDetailSelectedIds = {};
			purchaseDetailPage = 1;
            $('#purchaseDetailsModal .detail-supplier').text(button.data('supplier'));
			$('#purchaseDetailsModal .purchase-detail-search').val('');
			renderPurchaseDetailProducts();
			$('#purchaseDetailsModal').modal('show');
        });

		$('#purchaseDetailsModal').on('change', '.purchase-product-checkbox', function () {
			var id = String($(this).val());
			if ($(this).prop('checked')) {
				purchaseDetailSelectedIds[id] = true;
			} else {
				delete purchaseDetailSelectedIds[id];
			}
			renderPurchaseDetailProducts();
		});

		$('#purchaseDetailsModal').on('change', '.purchase-select-all', function () {
			var checked = $(this).prop('checked');
			var query = ($('#purchaseDetailsModal .purchase-detail-search').val() || '').trim().toLowerCase();
			purchaseDetailProducts.forEach(function (product) {
				if ((product.product || '').toLowerCase().indexOf(query) !== -1) {
					var id = String(product.id);
					if (checked) {
						purchaseDetailSelectedIds[id] = true;
					} else {
						delete purchaseDetailSelectedIds[id];
					}
				}
			});
			renderPurchaseDetailProducts();
		});

		$('#purchaseDetailsModal')
			.on('input', '.purchase-detail-search', function () {
				purchaseDetailPage = 1;
				renderPurchaseDetailProducts();
			})
			.on('click', '.purchase-detail-first', function () {
				purchaseDetailPage = 1;
				renderPurchaseDetailProducts();
			})
			.on('click', '.purchase-detail-prev', function () {
				purchaseDetailPage--;
				renderPurchaseDetailProducts();
			})
			.on('click', '.purchase-detail-next', function () {
				purchaseDetailPage++;
				renderPurchaseDetailProducts();
			})
			.on('click', '.purchase-detail-last', function () {
				var query = ($('#purchaseDetailsModal .purchase-detail-search').val() || '').trim().toLowerCase();
				var count = purchaseDetailProducts.filter(function (product) {
					return (product.product || '').toLowerCase().indexOf(query) !== -1;
				}).length;
				purchaseDetailPage = Math.max(1, Math.ceil(count / purchaseDetailPageSize));
				renderPurchaseDetailProducts();
			});

		$('#purchaseDetailsModal').on('click', '.purchase-bulk-delete', function () {
			var ids = Object.keys(purchaseDetailSelectedIds);

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

		var returnToPurchaseDetails = false;

		$(document)
			.off('click.purchaseProductLink', '#purchaseDetailsModal .purchase-product-link')
			.on('click.purchaseProductLink', '#purchaseDetailsModal .purchase-product-link', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var details = JSON.parse($(this).attr('data-product-details') || '{}');
				var defaultImage = "{{ asset('assets/img/productnoimage.png') }}";
				var $productModal = $('#purchaseProductDetailsModal');
				var $detailsModal = $('#purchaseDetailsModal');

				$productModal.find('.purchase-product-detail-image').attr('src', details.image || defaultImage);
				if (!details.price && details.cost) {
					details.price = details.cost;
				}
				var productName = details.product || '';
				var productTooltip = productName ? 'Product:\n' + productName : 'No product name';
				$productModal.find('.product-info-btn')
					.attr('data-tooltip', productTooltip)
					.attr('aria-label', productTooltip);
				$productModal.find('.detail-product').attr('title', productName);

				Object.keys(details).forEach(function (key) {
					var value = details[key];
					if (key === 'image') return;
					$productModal.find('.detail-' + key).text(value || '');
				});
				var expiryVal = details.expiry || details.expiry_date || '';
				$productModal.find('.detail-expiry').text(expiryVal && expiryVal !== '-' ? expiryVal : 'No expiry');

				var boxExpiries = details.box_expiries || [];
				var $boxExpWrap = $productModal.find('.detail-box-expiries-wrap');
				var $boxExpList = $productModal.find('.detail-box-expiries-list');
				$boxExpList.empty();

				if (Array.isArray(boxExpiries) && boxExpiries.length > 0) {
					boxExpiries.forEach(function (b) {
						var boxLabel = b.box ? (typeof b.box === 'number' ? 'Box #' + b.box : b.box) : 'Box';
						var dateStr = b.expiry_date || 'No expiry';
						var badgeClass = 'badge badge-light border text-dark';
						if (b.expiry_date) {
							var expD = new Date(b.expiry_date + 'T00:00:00');
							var today = new Date();
							today.setHours(0,0,0,0);
							if (expD < today) {
								badgeClass = 'badge badge-danger text-white';
							}
						}
						$boxExpList.append('<span class="' + badgeClass + ' px-2 py-1"><i class="fas fa-box mr-1"></i> ' + boxLabel + ': <strong>' + dateStr + '</strong></span>');
					});
					$boxExpWrap.show();
				} else {
					$boxExpWrap.hide();
				}

				returnToPurchaseDetails = true;
				$detailsModal.data('row-action-switching', true);
				$detailsModal.one('hidden.bs.modal.switchProduct', function () {
					window.setTimeout(function () {
						$productModal.modal('show');
					}, 50);
				});
				$detailsModal.modal('hide');
			});

		$('#purchaseProductDetailsModal')
			.off('hide.bs.modal.returnPurchase')
			.on('hide.bs.modal.returnPurchase', function () {
				if (returnToPurchaseDetails) {
					$(this).data('row-action-switching', true);
				}
			})
			.off('hidden.bs.modal.returnPurchase')
			.on('hidden.bs.modal.returnPurchase', function () {
				if (returnToPurchaseDetails) {
					returnToPurchaseDetails = false;
					window.setTimeout(function () {
						$('#purchaseDetailsModal').modal('show');
					}, 50);
				}
			});
        
    });
</script> 
@endpush