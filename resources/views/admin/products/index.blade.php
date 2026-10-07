@extends('admin.layouts.app')

<x-assets.tabulator />

@push('page-css')
<style>
	.product-row-action-modal .modal-dialog {
		max-width: 420px;
		width: min(420px, calc(100vw - 32px));
	}
	.product-row-action-modal .modal-body {
		max-height: min(65vh, 480px);
		overflow-y: auto;
		padding: 8px 0;
	}
	.product-row-action-modal .row-action-heading {
		min-width: 0;
	}
	.product-row-action-modal .row-action-meta {
		color: #64748b;
		display: block;
		font-size: 13px;
		font-weight: 500;
		margin-top: 4px;
		overflow-wrap: anywhere;
	}
	.product-row-action-modal .row-action-expiry {
		color: #dc2626;
		display: block;
		font-size: 13px;
		font-weight: 600;
		margin-top: 4px;
	}
	#product-edit-modal .modal-dialog {
		max-width: 760px;
		width: min(760px, calc(100vw - 32px));
	}
	#product-edit-modal .modal-body {
		max-height: min(75vh, 680px);
		overflow-y: auto;
	}
	#product-edit-modal .custom-card {
		max-width: none;
		margin: 0;
		padding: 0;
		box-shadow: none;
	}
	#product-edit-modal .custom-description {
		min-height: 140px;
	}
	#product-edit-modal .custom-submit {
		height: auto;
	}
	body.dark-mode #product-edit-modal .modal-content {
		background: #252b33;
		color: #e2e8f0;
	}
	.product-row-action-modal .dropdown-item {
		padding: 10px 20px;
		white-space: normal;
	}
	.product-row-action-modal .dropdown-item:hover,
	.product-row-action-modal .dropdown-item:focus {
		background-color: #dbeafe !important;
		color: #1e3a8a !important;
	}
	.product-row-action-modal .dropdown-item.text-danger:hover,
	.product-row-action-modal .dropdown-item.text-danger:focus {
		color: #b91c1c !important;
	}
	.product-row-action-modal .product-status-menu-item:hover,
	.product-row-action-modal .product-status-menu-item:focus {
		background-color: #86efac !important;
	}
	.product-row-action-modal .product-status-menu-text {
		margin-left: 6px;
	}
	.product-row-action-modal .product-status-menu-text.is-active,
	.product-row-action-modal .product-status-menu-item:hover .product-status-menu-text.is-active,
	.product-row-action-modal .product-status-menu-item:focus .product-status-menu-text.is-active {
		color: #15803d !important;
	}
	.product-row-action-modal .product-status-menu-text.is-inactive,
	.product-row-action-modal .product-status-menu-item:hover .product-status-menu-text.is-inactive,
	.product-row-action-modal .product-status-menu-item:focus .product-status-menu-text.is-inactive {
		color: #dc2626 !important;
	}
	body.dark-mode .product-row-action-modal .modal-content {
		background: #252b33;
		color: #e2e8f0;
	}
	body.dark-mode .product-row-action-modal .dropdown-item:hover,
	body.dark-mode .product-row-action-modal .dropdown-item:focus {
		background-color: #bbf7d0 !important;
		color: #14532d !important;
	}
	body.dark-mode .product-row-action-modal .dropdown-item.text-danger:hover,
	body.dark-mode .product-row-action-modal .dropdown-item.text-danger:focus {
		color: #b91c1c !important;
	}
	body.dark-mode .product-row-action-modal .product-status-menu-item:hover,
	body.dark-mode .product-row-action-modal .product-status-menu-item:focus {
		background-color: #86efac !important;
	}
	body.dark-mode .product-row-action-modal .product-status-menu-text.is-active,
	body.dark-mode .product-row-action-modal .product-status-menu-item:hover .product-status-menu-text.is-active,
	body.dark-mode .product-row-action-modal .product-status-menu-item:focus .product-status-menu-text.is-active {
		color: #166534 !important;
	}
	body.dark-mode .product-row-action-modal .product-status-menu-text.is-inactive,
	body.dark-mode .product-row-action-modal .product-status-menu-item:hover .product-status-menu-text.is-inactive,
	body.dark-mode .product-row-action-modal .product-status-menu-item:focus .product-status-menu-text.is-inactive {
		color: #b91c1c !important;
	}
	body.dark-mode .product-row-action-modal .modal-header {
		border-color: #475569;
	}
	body.dark-mode .product-row-action-modal .close {
		color: #e2e8f0;
		text-shadow: none;
	}
	body.dark-mode .product-row-action-modal .row-action-meta {
		color: #a8b3c4;
	}
	body.dark-mode .product-row-action-modal .row-action-expiry {
		color: #f87171;
	}

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
						<div class="product-detail-item detail-box-expiries-wrap" style="grid-column: 1 / -1; display: none;">
							<strong>Packaging Box Expiries</strong>
							<div class="detail-box-expiries-list d-flex flex-wrap mt-1" style="gap: 6px;"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade product-row-action-modal" id="product-row-action-modal" tabindex="-1" role="dialog" aria-labelledby="product-row-action-modal-title" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<div class="row-action-heading">
					<h5 class="modal-title" id="product-row-action-modal-title">Products Actions</h5>
					<span class="row-action-meta">Name &rarr; <span id="product-row-action-name"></span> &rarr; <span id="product-row-action-category"></span></span>
				</div>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body"><div id="product-row-action-content"></div></div>
		</div>
	</div>
</div>

<div class="modal fade" id="product-edit-modal" tabindex="-1" role="dialog" aria-labelledby="product-edit-modal-title" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="product-edit-modal-title">Edit Product</h5>
				<button type="button" class="close" data-edit-modal-close aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body" id="product-edit-modal-content"></div>
		</div>
	</div>
</div>
@endsection

@push('page-js')
<script src="{{asset('assets/js/purchase-multi-step.js')}}"></script>
<script>
    $(document).ready(function() {
		$(document)
			.off('.productRowActions')
			.off('.productEdit')
			.off('.outstockProductEdit')
			.off('.expiredProductEdit')
			.off('.productStatus');

		var activeProductActionButton = null;
		var returnToProductActionsAfterEdit = false;
		var returnToProductActionsAfterDetails = false;

		function runAfterProductActionsReady(callback) {
			var actionModal = $('#product-row-action-modal');
			var modalInstance = actionModal.data('bs.modal');
			if (modalInstance && modalInstance._isTransitioning) {
				actionModal.one('shown.bs.modal.productActionReady', callback);
				return;
			}
			callback();
		}

		function switchFromProductActions(targetModal, eventNamespace) {
			runAfterProductActionsReady(function () {
				var actionModal = $('#product-row-action-modal');
				actionModal.one('hidden.bs.modal.' + eventNamespace, function () {
					window.setTimeout(function () {
						targetModal.modal('show');
					}, 50);
				}).modal('hide');
			});
		}

		$(document).off('click.productRowActions', '#product-table .product-row-action-button')
			.on('click.productRowActions', '#product-table .product-row-action-button', function (event) {
				event.preventDefault();
				event.stopPropagation();
				var menu = this.nextElementSibling;
				if (!menu || !menu.classList.contains('dropdown-menu')) return;

				activeProductActionButton = this;
				this.setAttribute('aria-expanded', 'true');
				$('#product-row-action-name').text(this.getAttribute('data-action-name') || '');
				$('#product-row-action-category').text(this.getAttribute('data-action-category') || '');
				$('#product-row-action-content').html(menu.innerHTML);
				$('#product-row-action-modal').modal('show');
			});
		$('#product-row-action-modal').off('hidden.bs.modal.productRowActions').on('hidden.bs.modal.productRowActions', function () {
			if (returnToProductActionsAfterEdit || returnToProductActionsAfterDetails) return;
			if (activeProductActionButton) activeProductActionButton.setAttribute('aria-expanded', 'false');
			activeProductActionButton = null;
			$('#product-row-action-content').empty();
		});

	window.pharmacyProductsInit = function() {
		var tableEl = document.getElementById('product-table');
		if (!tableEl) return;
		if (!window.PharmaTabulator) return;

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
	};

	if (!window.pharmacyProductsTurboHandler) {
		window.pharmacyProductsTurboHandler = function() {
			if (window.pharmacyProductsInit) {
				window.pharmacyProductsInit();
			}
		};
		document.addEventListener('turbo:load', window.pharmacyProductsTurboHandler);
	}

	window.pharmacyProductsInit();

		$(document).off('click.productRowActions', '#product-table .product-detail-btn, #product-row-action-content .product-detail-btn')
			.on('click.productRowActions', '#product-table .product-detail-btn, #product-row-action-content .product-detail-btn', function (event) {
			event.preventDefault();
			event.stopPropagation();
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

			var boxExpiries = details.box_expiries || [];
			var $boxExpWrap = $('#productDetailsModal .detail-box-expiries-wrap');
			var $boxExpList = $('#productDetailsModal .detail-box-expiries-list');
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

			var showDetails = function () { $('#productDetailsModal').modal('show'); };
			var actionModal = $('#product-row-action-modal');
			if (actionModal.hasClass('show')) {
				returnToProductActionsAfterDetails = true;
				switchFromProductActions($('#productDetailsModal'), 'productDetails');
			} else {
				showDetails();
			}
		});

		$('#productDetailsModal').off('hidden.bs.modal.productDetailsReturn').on('hidden.bs.modal.productDetailsReturn', function () {
			if (!returnToProductActionsAfterDetails) return;
			returnToProductActionsAfterDetails = false;
			window.setTimeout(function () {
				$('#product-row-action-modal').modal('show');
			}, 50);
		});

		var productEditModal = $('#product-edit-modal');
		var productEditContent = $('#product-edit-modal-content');
		var productEditClosePending = false;

		productEditModal.find('[data-edit-modal-close]').off('click.productEditClose').on('click.productEditClose', function (event) {
			event.preventDefault();
			event.stopImmediatePropagation();
			var modalInstance = productEditModal.data('bs.modal');
			if (modalInstance && modalInstance._isTransitioning) {
				if (productEditModal.hasClass('show') && !productEditClosePending) {
					productEditClosePending = true;
					productEditModal.one('shown.bs.modal.productEditClose', function () {
						productEditClosePending = false;
						productEditModal.modal('hide');
					});
				}
				return;
			}
			productEditModal.modal('hide');
		});

		function showProductActionsAfterEditClose() {
			if (!returnToProductActionsAfterEdit) return;
			returnToProductActionsAfterEdit = false;
			window.setTimeout(function () {
				$('#product-row-action-modal').modal('show');
			}, 50);
		}

		productEditModal.off('hidden.bs.modal.productEdit').on('hidden.bs.modal.productEdit', function () {
			productEditClosePending = false;
			productEditContent.empty();
			if (returnToProductActionsAfterEdit) {
				showProductActionsAfterEditClose();
				return;
			}
			if (activeProductActionButton) activeProductActionButton.setAttribute('aria-expanded', 'false');
			activeProductActionButton = null;
			$('#product-row-action-content').empty();
			if (window.PharmaTabulator) window.PharmaTabulator.reload('product-table');
		});

		function initializeProductEditForm(form) {
			var $form = $(form);
			var purchaseMap = JSON.parse($form.attr('data-purchase-map') || '{}');
			var originalAction = form.action;
			var baseUrl = form.dataset.baseUrl;
			var methodInput = form.querySelector('input[name="_method"]');
			var productSelect = form.querySelector('select[name="product"]');
			var barcodeInput = form.querySelector('input[name="barcode"]');
			var priceInput = form.querySelector('input[name="price"]');
			var descriptionInput = form.querySelector('textarea[name="description"]');

			function setProductDetails(data) {
				if (!data) {
					barcodeInput.value = '';
					priceInput.value = '';
					descriptionInput.value = '';
					return;
				}
				barcodeInput.value = data.barcode || '';
				priceInput.value = data.price || '';
				descriptionInput.value = data.description || '';
			}

			function updateProductTarget(data) {
				if (data && data.id && baseUrl) {
					form.action = baseUrl + '/' + data.id;
					if (!methodInput) {
						methodInput = document.createElement('input');
						methodInput.type = 'hidden';
						methodInput.name = '_method';
						form.appendChild(methodInput);
					}
					methodInput.value = 'PUT';
				} else {
					form.action = originalAction;
					if (methodInput) {
						methodInput.remove();
						methodInput = null;
					}
				}
			}

			if ($.fn.select2) {
				$(productSelect).select2({
					dropdownParent: productEditModal,
					width: '100%'
				});
			}

			$(productSelect).off('change.productEdit').on('change.productEdit', function () {
				var data = purchaseMap[this.value] || null;
				setProductDetails(data);
				updateProductTarget(data);
			});

			$form.off('submit.productEdit').on('submit.productEdit', function (event) {
				event.preventDefault();
				$form.find('.edit-form-errors').remove();
				$form.find('.is-invalid').removeClass('is-invalid');

				$.ajax({
					url: form.action,
					type: 'POST',
					data: new FormData(form),
					processData: false,
					contentType: false,
					headers: {
						'X-Requested-With': 'XMLHttpRequest',
						'Accept': 'application/json'
					},
					success: function (response) {
						returnToProductActionsAfterEdit = false;
						if (window.Snackbar) {
							Snackbar.show({
								text: response.message || 'Product has been updated.',
								pos: 'top-right',
								actionTextColor: '#fff',
								backgroundColor: '#8dbf42'
							});
						}
						productEditModal.modal('hide');
					},
					error: function (xhr) {
						var errors = xhr.responseJSON && xhr.responseJSON.errors;
						if (!errors) {
							if (window.Snackbar) {
								Snackbar.show({
									text: (xhr.responseJSON && xhr.responseJSON.message) || 'Could not update the product.',
									pos: 'top-right',
									actionTextColor: '#fff',
									backgroundColor: '#e7515a'
								});
							}
							return;
						}

						var messages = [];
						$.each(errors, function (name, fieldErrors) {
							var input = form.querySelector('[name="' + name + '"]');
							if (input) $(input).addClass('is-invalid');
							$.each(fieldErrors, function (_, message) {
								messages.push($('<div>').text(message).html());
							});
						});
						$form.prepend('<div class="alert alert-danger edit-form-errors" role="alert">' + messages.join('<br>') + '</div>');
					}
				});
			});
		}

		$(document).off('click.productEdit', '#product-row-action-content .editbtn')
			.on('click.productEdit', '#product-row-action-content .editbtn', function (event) {
				event.preventDefault();
				var editUrl = this.getAttribute('data-edit-url');
				$.ajax({
					url: editUrl,
					type: 'GET',
					dataType: 'html',
					success: function (html) {
						var editDocument = new DOMParser().parseFromString(html, 'text/html');
						var form = editDocument.querySelector('#update_service');
						if (!form) {
							if (window.Snackbar) {
								Snackbar.show({
									text: 'The product edit form could not be loaded.',
									pos: 'top-right',
									actionTextColor: '#fff',
									backgroundColor: '#e7515a'
								});
							}
							return;
						}

						productEditContent.empty().append(form);
						initializeProductEditForm(form);
						returnToProductActionsAfterEdit = true;
						switchFromProductActions(productEditModal, 'productEdit');
					},
					error: function () {
						if (window.Snackbar) {
							Snackbar.show({
								text: 'Could not load the product edit form.',
								pos: 'top-right',
								actionTextColor: '#fff',
								backgroundColor: '#e7515a'
							});
						}
					}
				});
			});

		/* ---- Add Product modal (multi-step purchase form) ---- */
		var addProductModal = $('#addProductModal');
		var addProductForm = addProductModal.find('form');

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
			});

			if (window.initPurchaseMultiStep) {
				window.initPurchaseMultiStep(addProductForm, addProductModal);
			}

			// Re-open the modal if the page reloaded with server-side validation errors
			if (addProductForm.find('.purchase-validation-alert').length || addProductForm.find('.alert-danger').length) {
				addProductModal.modal('show');
			}
		}
/* ---- Product Active / Not Active toggle (inside Action dropdown) ---- */
		// Clicking the switch inside the dropdown must not close the menu.
		var productStatusMenuSelector = '.product-status-menu-item, .product-active-toggle, .product-active-switch';
		$(document).off('click', productStatusMenuSelector).on('click.productStatus', productStatusMenuSelector, function (event) {
			event.stopPropagation();
		});
		$(document).off('change', '.product-active-toggle').on('change.productStatus', '.product-active-toggle', function () {
			var $toggle = $(this);
			var $switch = $toggle.closest('.product-active-switch');
			var $cell = $toggle.closest('.product-action-cell');
			if (!$cell.length && activeProductActionButton) {
				$cell = $(activeProductActionButton).closest('.product-action-cell');
			}
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
					$cell.find('.product-status-menu-text').text(isActive ? 'Active' : 'Not Active');
					$menuText.removeClass('is-active is-inactive').addClass(isActive ? 'is-active' : 'is-inactive');
					$cell.find('.product-status-menu-text').removeClass('is-active is-inactive').addClass(isActive ? 'is-active' : 'is-inactive');
					$button.attr('data-action-status', isActive ? 'Active' : 'Not Active');
					$('#product-row-action-status').text(isActive ? 'Active' : 'Not Active');
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