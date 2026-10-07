@extends('admin.layouts.app')

<x-assets.tabulator />

@push('page-css')
<style>
	.supplier-products-list {
		display: grid;
		grid-template-columns: repeat(3, minmax(0, 1fr));
		gap: 8px;
		width: 100%;
	}

	.supplier-products-list .supplier-product-option {
		display: flex;
		align-items: center;
		gap: 9px;
		min-width: 0;
		min-height: 40px;
		margin: 0;
		padding: 7px 9px;
		border: 1px solid #e2e8f0;
		border-radius: 6px;
		cursor: default;
		font-weight: 400;
		line-height: 1.35;
	}

	.supplier-product-checkbox {
		flex: 0 0 auto;
		width: 16px;
		height: 16px;
		margin: 0 !important;
	}

	.supplier-products-list .supplier-product-link {
		display: block;
		flex: 1 1 auto;
		min-width: 0;
		margin: 0;
		padding: 0;
		overflow: hidden;
		text-align: left;
		text-overflow: ellipsis;
		white-space: nowrap;
		color: #2563eb !important;
		text-decoration: none;
		cursor: pointer;
	}

	.supplier-products-list .supplier-product-link:hover {
		color: #1d4ed8 !important;
		text-decoration: underline;
	}

	body.dark-mode .supplier-products-list .supplier-product-link {
		color: #60a5fa !important;
	}

	body.dark-mode .supplier-products-list .supplier-product-link:hover {
		color: #93c5fd !important;
		text-decoration: underline;
	}

	.supplier-detail-toolbar,
	.supplier-detail-pagination {
		display: flex;
		align-items: center;
		gap: 12px;
	}

	#supplierDetailsModal .purchase-select-all-option {
		display: flex;
		align-items: center;
		gap: 9px;
		margin: 0 0 12px;
		line-height: 1.35;
		cursor: pointer;
	}

	.supplier-detail-toolbar {
		justify-content: space-between;
		margin-bottom: 12px;
	}

	.supplier-detail-search {
		flex: 1 1 220px;
		max-width: 280px;
	}

	.supplier-detail-pagination {
		justify-content: flex-end;
		gap: 6px;
		margin-top: 12px;
	}

	.supplier-detail-pagination .supplier-detail-page-size {
		display: flex;
		align-items: center;
		gap: 6px;
		margin-right: auto;
		white-space: nowrap;
	}

	.supplier-detail-page-size select {
		width: 64px;
		padding: 3px 6px;
	}

	.supplier-detail-pagination .btn {
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

	.supplier-detail-pagination .btn:disabled {
		background-color: #f1f5f9 !important;
		color: #64748b !important;
		cursor: not-allowed;
	}

	body.dark-mode #supplierDetailsModal .supplier-product-option {
		border-color: #374151;
	}

	body.dark-mode .supplier-detail-pagination .btn {
		border-color: #475569 !important;
		background-color: #111827 !important;
		color: #e2e8f0 !important;
	}

	body.dark-mode .supplier-detail-pagination .btn:disabled {
		background-color: #1f2937 !important;
		color: #94a3b8 !important;
	}

	#supplierDetailsModal .supplier-detail-page-status {
		min-width: 34px;
		text-align: center;
	}

	#supplierDetailsModal .modal-content,
	#supplierProductDetailsModal .modal-content {
		--animate-duration: .35s;
	}

	#supplierProductDetailsModal .modal-dialog {
		max-width: 760px;
	}

	#supplierProductDetailsModal .dashboard-product-details-layout {
		display: grid;
		grid-template-columns: minmax(220px, .9fr) minmax(0, 1.35fr);
		align-items: stretch;
		gap: 24px;
	}

	#supplierProductDetailsModal .dashboard-product-details-image {
		display: flex;
		align-items: center;
		justify-content: center;
		min-height: 300px;
		padding: 18px;
		border: 1px solid #e5eaf1;
		border-radius: 8px;
		background: #f5f7fb;
	}

	#supplierProductDetailsModal .dashboard-product-details-image img {
		width: 100%;
		max-height: 300px;
		border-radius: 6px;
		object-fit: contain;
	}

	#supplierProductDetailsModal .dashboard-product-details-info {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		align-content: center;
		gap: 12px 18px;
	}

	#supplierProductDetailsModal .dashboard-product-details-info > div {
		position: relative;
		min-width: 0;
		padding-bottom: 8px;
		border-bottom: 1px solid #edf0f4;
	}

	#supplierProductDetailsModal .dashboard-product-details-info strong {
		display: block;
		margin-bottom: 3px;
		color: #7a8795;
		font-size: 12px;
		font-weight: 600;
		text-transform: uppercase;
	}

	#supplierProductDetailsModal .dashboard-product-details-info span {
		color: #263238;
		word-break: break-word;
		overflow-wrap: anywhere;
	}

	#supplierProductDetailsModal .product-detail-value-wrap {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 6px;
		min-width: 0;
	}

	#supplierProductDetailsModal .detail-product {
		display: block;
		flex: 1 1 auto;
		min-width: 0;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}

	#supplierProductDetailsModal .product-info-btn {
		position: relative;
		display: inline-flex;
		flex: 0 0 auto;
		align-items: center;
		justify-content: center;
		width: 1.5rem;
		height: 1.5rem;
		padding: 0;
		border: 0;
		background: transparent;
		color: #0f766e;
		cursor: help;
	}

	#supplierProductDetailsModal .product-info-btn::after {
		position: absolute;
		right: 0;
		bottom: calc(100% + 8px);
		z-index: 1060;
		width: 220px;
		max-height: 180px;
		padding: 8px 10px;
		overflow-y: auto;
		border-radius: 5px;
		background: #1f2937;
		color: #fff;
		content: attr(data-tooltip);
		font-size: 12px;
		line-height: 1.35;
		text-align: left;
		white-space: pre-wrap;
		word-break: break-all;
		overflow-wrap: anywhere;
		box-shadow: 0 4px 14px rgba(0, 0, 0, .25);
		opacity: 0;
		visibility: hidden;
		pointer-events: none;
		transition: opacity .15s ease, visibility .15s ease;
	}

	#supplierProductDetailsModal .product-info-btn:hover::after,
	#supplierProductDetailsModal .product-info-btn:focus::after {
		opacity: 1;
		visibility: visible;
	}

	body.dark-mode #supplierProductDetailsModal .dashboard-product-details-image {
		border-color: #334155;
		background: #1e293b;
	}

	body.dark-mode #supplierProductDetailsModal .dashboard-product-details-info > div {
		border-bottom-color: #334155;
	}

	body.dark-mode #supplierProductDetailsModal .dashboard-product-details-info strong {
		color: #94a3b8;
	}

	body.dark-mode #supplierProductDetailsModal .dashboard-product-details-info span {
		color: #e2e8f0;
	}

	body.dark-mode #supplierProductDetailsModal .product-info-btn {
		color: #14b8a6;
	}

	body.dark-mode #supplierProductDetailsModal .product-info-btn::after {
		border: 1px solid #334155;
		background: #0f172a;
		color: #f8fafc;
	}

	@media (max-width: 575.98px) {
		.supplier-products-list { grid-template-columns: 1fr; }
		.supplier-detail-toolbar { align-items: stretch; flex-direction: column; }
		.supplier-detail-search { flex: 0 0 auto; max-width: none; }
		.supplier-detail-pagination { flex-wrap: wrap; }
	}

	@media (max-width: 576px) {
		#supplierProductDetailsModal .dashboard-product-details-layout,
		#supplierProductDetailsModal .dashboard-product-details-info {
			grid-template-columns: 1fr;
		}
		#supplierProductDetailsModal .dashboard-product-details-image {
			min-height: 220px;
		}
	}
</style>
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Supplier</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Supplier</li>
	</ul>
</div>
<div class="col-sm-5 col">
	@can('create-supplier')
	<button type="button" class="btn btn-primary float-right mt-2" data-toggle="modal" data-target="#addSupplierModal">Add New</button>
	@endcan
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-md-12">
	
		<!-- Suppliers -->
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<div id="supplier-table" class="tabulator-table-wrap"></div>
				</div>
				<div class="modal fade" id="supplierDetailsModal" tabindex="-1" role="dialog" aria-labelledby="supplierDetailsModalLabel" aria-hidden="true">
					<div class="modal-dialog modal-dialog-centered" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="supplierDetailsModalLabel">Supplier Details</h5>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							</div>
							<div class="modal-body">
								<div class="mb-3"><strong>Supplier:</strong> <span class="supplier-detail-name"></span></div>
								<div class="mb-3"><strong>Products:</strong></div>
								<label class="purchase-select-all-option"><input type="checkbox" class="supplier-select-all">Select all</label>
								<div class="supplier-detail-toolbar">
									<input type="search" class="form-control form-control-sm supplier-detail-search" placeholder="Search products..." aria-label="Search products">
									<div class="supplier-detail-count text-muted small" aria-live="polite"></div>
								</div>
								<div class="supplier-products-list"></div>
								<nav class="supplier-detail-pagination" aria-label="Supplier product pages">
									<div class="supplier-detail-page-size"><span>Page Size</span><select class="form-control form-control-sm" aria-label="Products per page" disabled><option>12</option></select></div>
									<button type="button" class="btn btn-sm btn-outline-secondary supplier-detail-first">First</button>
									<button type="button" class="btn btn-sm btn-outline-secondary supplier-detail-prev">Prev</button>
									<span class="supplier-detail-page-status" aria-live="polite" aria-label="Page 1">1</span>
									<button type="button" class="btn btn-sm btn-outline-secondary supplier-detail-next">Next</button>
									<button type="button" class="btn btn-sm btn-outline-secondary supplier-detail-last">Last</button>
								</nav>
							</div>
							<div class="modal-footer">
								@if(auth()->user()->hasPermissionTo('destroy-purchase'))
									<button type="button" class="btn btn-danger supplier-bulk-delete" disabled>Delete selected</button>
								@endif
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
							</div>
						</div>
					</div>
				</div>
				<div class="modal fade" id="supplierProductDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog modal-dialog-centered" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">Product Details</h5>
								<button type="button" class="close supplier-product-details-close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							</div>
							<div class="modal-body">
								<div class="dashboard-product-details-layout">
									<div class="dashboard-product-details-image">
										<img class="supplier-product-detail-image" src="{{ asset('assets/img/productnoimage.png') }}" alt="Product image">
									</div>
									<div class="dashboard-product-details-info">
										<div><strong>Product</strong><div class="product-detail-value-wrap"><span class="detail-product"></span><button type="button" class="product-info-btn" aria-label="Product details" data-tooltip="Product: "><i class="fas fa-info-circle" aria-hidden="true"></i></button></div></div>
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
			</div>
		</div>
		<!-- /Suppliers-->
		
	</div>
</div>

<div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="addSupplierModalLabel">Add Supplier</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body custom-edit-service">
				<form method="post" enctype="multipart/form-data" action="{{route('suppliers.store')}}" id="add-supplier-form">
					@csrf
					<input type="hidden" name="form_submit" value="next">
					<input type="hidden" name="from_suppliers_modal" value="1">
					@include('admin.suppliers._form')
					<div class="submit-section text-center">
						<button class="btn btn-primary submit-btn" type="submit" id="add-supplier-next">Next</button>
					</div>
				</form>
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
                el: 'supplier-table',
                url: "{{route('suppliers.index')}}",
                columns: [
                    {title: 'Name', field: 'name'},
                    {title: 'Phone', field: 'phone'},
                    {title: 'Email', field: 'email'},
                    {title: 'Address', field: 'address'},
                    {title: 'Company', field: 'company'},
                    {title: 'Action', field: 'action', formatter: 'html', headerSort: false, searchable: false, width: 110, hozAlign: 'center'},
                ]
            });
        }

		var supplierDetailProducts = [];
		var supplierDetailSelectedIds = {};
		var supplierDetailPage = 1;
		var supplierDetailPageSize = 12;
		var returnToSupplierDetails = false;

		function renderSupplierProducts() {
			var modal = $('#supplierDetailsModal');
			var query = (modal.find('.supplier-detail-search').val() || '').trim().toLowerCase();
			var filteredProducts = supplierDetailProducts.filter(function (product) {
				return (product.product || '').toLowerCase().indexOf(query) !== -1;
			});
			var totalPages = Math.max(1, Math.ceil(filteredProducts.length / supplierDetailPageSize));
			supplierDetailPage = Math.min(Math.max(supplierDetailPage, 1), totalPages);
			var start = (supplierDetailPage - 1) * supplierDetailPageSize;
			var pageProducts = filteredProducts.slice(start, start + supplierDetailPageSize);
			var list = modal.find('.supplier-products-list').empty();

			pageProducts.forEach(function (product) {
				var rawName = (product.product || '').trim();
				var item = $('<div class="supplier-product-option"></div>');
				var checkbox = $('<input type="checkbox" class="supplier-product-checkbox">')
					.val(product.id)
					.prop('checked', Boolean(supplierDetailSelectedIds[String(product.id)]));
				var link = $('<button type="button" class="btn btn-link supplier-product-link"></button>')
					.text(rawName)
					.attr('title', rawName)
					.attr('data-product-details', JSON.stringify(product));
				item.append(checkbox).append(link);
				list.append(item);
			});

			var selectedCount = Object.keys(supplierDetailSelectedIds).length;
			modal.find('.supplier-detail-count').text(filteredProducts.length
				? 'Showing ' + (start + 1) + '-' + Math.min(start + supplierDetailPageSize, filteredProducts.length) + ' of ' + filteredProducts.length
				: 'No products found');
			modal.find('.supplier-detail-page-status').text(supplierDetailPage).attr('aria-label', 'Page ' + supplierDetailPage + ' of ' + totalPages);
			modal.find('.supplier-detail-first, .supplier-detail-prev').prop('disabled', supplierDetailPage <= 1);
			modal.find('.supplier-detail-next, .supplier-detail-last').prop('disabled', supplierDetailPage >= totalPages);
			modal.find('.supplier-select-all').prop('checked', filteredProducts.length > 0 && filteredProducts.every(function (product) {
				return Boolean(supplierDetailSelectedIds[String(product.id)]);
			}));
			modal.find('.supplier-bulk-delete').prop('disabled', selectedCount === 0);
		}

		$(document)
			.off('.supplierDetails')
			.on('click.supplierDetails', '.supplier-detail-btn', function () {
				var button = $(this);
				supplierDetailProducts = JSON.parse(button.attr('data-products') || '[]');
				supplierDetailSelectedIds = {};
				supplierDetailPage = 1;
				$('#supplierDetailsModal .supplier-detail-name').text(button.attr('data-supplier'));
				$('#supplierDetailsModal .supplier-detail-search').val('');
				renderSupplierProducts();
				$('#supplierDetailsModal').modal('show');
			})
			.on('change.supplierDetails', '#supplierDetailsModal .supplier-product-checkbox', function () {
				var id = String($(this).val());
				if ($(this).prop('checked')) supplierDetailSelectedIds[id] = true;
				else delete supplierDetailSelectedIds[id];
				renderSupplierProducts();
			})
			.on('change.supplierDetails', '#supplierDetailsModal .supplier-select-all', function () {
				var checked = $(this).prop('checked');
				var query = ($('#supplierDetailsModal .supplier-detail-search').val() || '').trim().toLowerCase();
				supplierDetailProducts.forEach(function (product) {
					if ((product.product || '').toLowerCase().indexOf(query) === -1) return;
					var id = String(product.id);
					if (checked) supplierDetailSelectedIds[id] = true;
					else delete supplierDetailSelectedIds[id];
				});
				renderSupplierProducts();
			})
			.on('input.supplierDetails', '#supplierDetailsModal .supplier-detail-search', function () {
				supplierDetailPage = 1;
				renderSupplierProducts();
			})
			.on('click.supplierDetails', '#supplierDetailsModal .supplier-detail-first', function () {
				supplierDetailPage = 1;
				renderSupplierProducts();
			})
			.on('click.supplierDetails', '#supplierDetailsModal .supplier-detail-prev', function () {
				supplierDetailPage--;
				renderSupplierProducts();
			})
			.on('click.supplierDetails', '#supplierDetailsModal .supplier-detail-next', function () {
				supplierDetailPage++;
				renderSupplierProducts();
			})
			.on('click.supplierDetails', '#supplierDetailsModal .supplier-detail-last', function () {
				var query = ($('#supplierDetailsModal .supplier-detail-search').val() || '').trim().toLowerCase();
				var count = supplierDetailProducts.filter(function (product) {
					return (product.product || '').toLowerCase().indexOf(query) !== -1;
				}).length;
				supplierDetailPage = Math.max(1, Math.ceil(count / supplierDetailPageSize));
				renderSupplierProducts();
			})
			.on('click.supplierDetails', '#supplierDetailsModal .supplier-product-link', function (event) {
				event.preventDefault();
				event.stopPropagation();
				var details = JSON.parse($(this).attr('data-product-details') || '{}');
				var modal = $('#supplierProductDetailsModal');
				var defaultImage = "{{ asset('assets/img/productnoimage.png') }}";
				modal.find('.supplier-product-detail-image').attr('src', details.image || defaultImage);
				var productName = details.product || '';
				modal.find('.product-info-btn')
					.attr('data-tooltip', productName ? 'Product:\n' + productName : 'No product name')
					.attr('aria-label', productName ? 'Product: ' + productName : 'No product name');
				modal.find('.detail-product').attr('title', productName);
				modal.find('.detail-category').text(details.category || '');
				modal.find('.detail-supplier').text(details.supplier || '');
				modal.find('.detail-price').text(details.price || details.cost || '');
				modal.find('.detail-quantity').text(details.quantity || '');
				modal.find('.detail-item_quantity').text(details.item_quantity || '');
				modal.find('.detail-packaging_box').text(details.packaging_box || '');
				modal.find('.detail-quantity_per_box').text(details.quantity_per_box || '');
				modal.find('.detail-expiry').text(details.expiry && details.expiry !== '-' ? details.expiry : 'No expiry');
				modal.find('.detail-purchased').text(details.purchased || '');
				modal.find('.detail-product').text(productName);

				var $supplierModal = $('#supplierDetailsModal');
				returnToSupplierDetails = true;
				$supplierModal.data('row-action-switching', true);
				$supplierModal.one('hidden.bs.modal.switchSupplierProduct', function () {
					window.setTimeout(function () {
						modal.modal('show');
					}, 50);
				});
				$supplierModal.modal('hide');
			})
			.on('click.supplierDetails', '#supplierDetailsModal .supplier-bulk-delete', function () {
				var ids = Object.keys(supplierDetailSelectedIds);
				if (!ids.length || !window.confirm('Delete the selected purchase(s)?')) return;

				$.ajax({
					url: "{{ route('purchases.bulk-destroy') }}",
					type: 'DELETE',
					data: {ids: ids, _token: $('meta[name="csrf-token"]').attr('content')},
					success: function (response) {
						$('#supplierDetailsModal').modal('hide');
						if (window.PharmaTabulator) window.PharmaTabulator.reload('supplier-table');
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

		$('#supplierProductDetailsModal')
			.off('hide.bs.modal.returnSupplier')
			.on('hide.bs.modal.returnSupplier', function () {
				if (returnToSupplierDetails) {
					$(this).data('row-action-switching', true);
				}
			})
			.off('hidden.bs.modal.returnSupplier')
			.on('hidden.bs.modal.returnSupplier', function () {
				if (returnToSupplierDetails) {
					returnToSupplierDetails = false;
					window.setTimeout(function () {
						$('#supplierDetailsModal').modal('show');
					}, 50);
				}
			});

		// Re-open the Add Supplier modal if the last submit failed validation
		var addSupplierModal = $('#addSupplierModal');
		var addSupplierForm = $('#add-supplier-form');
		if (addSupplierForm.find('.supplier-validation-alert').length || {{ session('open_add_supplier_modal') ? 'true' : 'false' }}) {
			addSupplierModal.modal('show');
		}
		addSupplierModal.on('hidden.bs.modal', function () {
			// Clear stale validation errors / old input display state on close
			addSupplierForm.find('.supplier-validation-alert').remove();
		});
        
    });
</script> 
@endpush