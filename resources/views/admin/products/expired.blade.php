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
	.product-row-action-modal .row-action-heading { min-width: 0; }
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
	#expired-product-edit-modal .modal-dialog {
		max-width: 760px;
		width: min(760px, calc(100vw - 32px));
	}
	#expired-product-edit-modal .modal-body {
		max-height: min(75vh, 680px);
		overflow-y: auto;
	}
	#expired-product-edit-modal .custom-card {
		max-width: none;
		margin: 0;
		padding: 0;
		box-shadow: none;
	}
	#expired-product-edit-modal .custom-description { min-height: 140px; }
	#expired-product-edit-modal .custom-submit { height: auto; }
	body.dark-mode #expired-product-edit-modal .modal-content {
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
	body.dark-mode .product-row-action-modal .modal-header { border-color: #475569; }
	body.dark-mode .product-row-action-modal .close {
		color: #e2e8f0;
		text-shadow: none;
	}
	body.dark-mode .product-row-action-modal .row-action-meta { color: #a8b3c4; }
	body.dark-mode .product-row-action-modal .row-action-expiry { color: #f87171; }

	#expiredDetailsModal .modal-dialog {
		max-width: 760px;
	}
	#expiredDetailsModal .product-details-layout {
		display: grid;
		grid-template-columns: minmax(220px, .9fr) minmax(0, 1.35fr);
		gap: 24px;
		align-items: stretch;
	}
	#expiredDetailsModal .product-details-image {
		display: flex;
		align-items: center;
		justify-content: center;
		min-height: 300px;
		padding: 18px;
		background: #f5f7fb;
		border: 1px solid #e5eaf1;
		border-radius: 8px;
	}
	#expiredDetailsModal .product-details-image img {
		width: 100%;
		max-height: 300px;
		object-fit: contain;
		border-radius: 6px;
	}
	#expiredDetailsModal .product-details-info {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: 12px 18px;
		align-content: center;
	}
	#expiredDetailsModal .product-detail-item {
		margin: 0;
		padding-bottom: 8px;
		border-bottom: 1px solid #edf0f4;
		min-width: 0;
		position: relative;
	}
	#expiredDetailsModal .product-detail-item strong {
		display: block;
		margin-bottom: 3px;
		font-size: 12px;
		font-weight: 600;
		color: #7a8795;
		text-transform: uppercase;
	}
	#expiredDetailsModal .product-detail-item span {
		color: #263238;
		word-break: break-word;
		overflow-wrap: anywhere;
	}
	#expiredDetailsModal .product-detail-value-wrap {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 6px;
		min-width: 0;
		position: relative;
	}
	#expiredDetailsModal .product-detail-value-wrap .detail-product {
		min-width: 0;
		flex: 1 1 auto;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		display: block;
	}
	#expiredDetailsModal .product-info-btn {
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
	#expiredDetailsModal .product-info-btn:hover,
	#expiredDetailsModal .product-info-btn:focus {
		color: #0d9488;
	}
	#expiredDetailsModal .product-info-btn::after {
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
	#expiredDetailsModal .product-info-btn:hover::after,
	#expiredDetailsModal .product-info-btn:focus::after {
		opacity: 1;
		visibility: visible;
	}
	@media (max-width: 576px) {
		#expiredDetailsModal .product-details-layout,
		#expiredDetailsModal .product-details-info {
			grid-template-columns: 1fr;
		}
		#expiredDetailsModal .product-details-image {
			min-height: 220px;
		}
	}
	body.dark-mode #expiredDetailsModal .product-details-image {
		background: #1e293b;
		border-color: #334155;
	}
	body.dark-mode #expiredDetailsModal .product-detail-item {
		border-bottom-color: #334155;
	}
	body.dark-mode #expiredDetailsModal .product-detail-item strong {
		color: #94a3b8;
	}
	body.dark-mode #expiredDetailsModal .product-detail-item span {
		color: #e2e8f0;
	}
	body.dark-mode #expiredDetailsModal .product-info-btn {
		color: #14b8a6;
	}
	body.dark-mode #expiredDetailsModal .product-info-btn:hover,
	body.dark-mode #expiredDetailsModal .product-info-btn:focus {
		color: #2dd4bf;
	}
	body.dark-mode #expiredDetailsModal .product-info-btn::after {
		background: #0f172a;
		border: 1px solid #334155;
		color: #f8fafc;
		box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5);
	}
</style>
@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Expired</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('products.index')}}">Products</a></li>
		<li class="breadcrumb-item active">Expired</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-md-12">
	
		<!-- Expired Products -->
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<div id="expired-product" class="tabulator-table-wrap"></div>
				</div>
			</div>
		</div>
		<!-- /Expired Products -->
		
	</div>
</div>

<div class="modal fade" id="expiredDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header"><h5 class="modal-title">Product Details</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
			<div class="modal-body">
				<div class="product-details-layout">
					<div class="product-details-image">
						<img class="expired-detail detail-image" src="{{ asset('assets/img/productnoimage.png') }}" onerror="this.onerror=null;this.src='{{ asset('assets/img/productnoimage.png') }}';" alt="Product image">
					</div>
					<div class="product-details-info">
						<div class="product-detail-item">
							<strong>Product</strong>
							<div class="product-detail-value-wrap">
								<span class="expired-detail detail-product product-name"></span>
								<button type="button" class="product-info-btn" aria-label="Product details" data-tooltip="Product: ">
									<i class="fas fa-info-circle" aria-hidden="true"></i>
								</button>
							</div>
						</div>
						<div class="product-detail-item"><strong>Category</strong><span class="expired-detail detail-category"></span></div>
						<div class="product-detail-item"><strong>Supplier</strong><span class="expired-detail detail-supplier"></span></div>
						<div class="product-detail-item"><strong>Price</strong><span class="expired-detail detail-price"></span></div>
						<div class="product-detail-item"><strong>Quantity</strong><span class="expired-detail detail-quantity"></span></div>
						<div class="product-detail-item"><strong>Item Quantity</strong><span class="expired-detail detail-item_quantity"></span></div>
						<div class="product-detail-item"><strong>Packaging Box</strong><span class="expired-detail detail-packaging_box"></span></div>
						<div class="product-detail-item"><strong>Quantity per Box</strong><span class="expired-detail detail-quantity_per_box"></span></div>
						<div class="product-detail-item"><strong>Expire Date</strong><span class="expired-detail detail-expiry"></span></div>
						<div class="product-detail-item"><strong>Date of Purchase</strong><span class="expired-detail detail-purchased"></span></div>
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
					<h5 class="modal-title" id="product-row-action-modal-title">Expired Actions</h5>
					<span class="row-action-meta">Name &rarr; <span id="product-row-action-name"></span> &rarr; <span id="product-row-action-category"></span></span>
				</div>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body"><div id="product-row-action-content"></div></div>
		</div>
	</div>
</div>

<div class="modal fade" id="expired-product-edit-modal" tabindex="-1" role="dialog" aria-labelledby="expired-product-edit-modal-title" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="expired-product-edit-modal-title">Edit Product</h5>
				<button type="button" class="close" data-edit-modal-close aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body" id="expired-product-edit-modal-content"></div>
		</div>
	</div>
</div>
@endsection

@push('page-js')
<script>
	window.pharmacyExpiredProductsInit = function() {
		var tableEl = document.getElementById('expired-product');
		if (!tableEl) return;
		if (!window.PharmaTabulator) return;

		window.PharmaTabulator.server({
			el: 'expired-product',
			url: "{{route('expired')}}",
			columns: [
				{title: 'Brand Name', field: 'product', formatter: 'html'},
				{title: 'Category', field: 'category'},
				{title: 'Price', field: 'price'},
				{title: 'Quantity', field: 'quantity'},
				{title: 'Action', field: 'action', formatter: 'html', headerSort: false, searchable: false, width: 110, hozAlign: 'center'},
			]
		});
	};

	if (!window.pharmacyExpiredProductsTurboHandler) {
		window.pharmacyExpiredProductsTurboHandler = function() {
			if (window.pharmacyExpiredProductsInit) {
				window.pharmacyExpiredProductsInit();
			}
		};
		document.addEventListener('turbo:load', window.pharmacyExpiredProductsTurboHandler);
	}

    $(document).ready(function() {
		$(document)
			.off('.productRowActions')
			.off('.productEdit')
			.off('.outstockProductEdit')
			.off('.expiredProductEdit')
			.off('.productStatus');

		window.pharmacyExpiredProductsInit();

		var activeProductActionButton = null;
		var returnToProductActionsAfterEdit = false;
		var returnToProductActionsAfterDetails = false;

		function runAfterProductActionsReady(callback) {
			var actionModal = $('#product-row-action-modal');
			var modalInstance = actionModal.data('bs.modal');
			if (modalInstance && modalInstance._isTransitioning) {
				actionModal.one('shown.bs.modal.expiredActionReady', callback);
				return;
			}
			callback();
		}

		function switchFromProductActions(targetModal) {
			runAfterProductActionsReady(function () {
				var actionModal = $('#product-row-action-modal');
				actionModal.one('hidden.bs.modal.expiredProductEdit', function () {
					window.setTimeout(function () {
						targetModal.modal('show');
					}, 50);
				}).modal('hide');
			});
		}

		$(document).off('click.productRowActions', '#expired-product .product-row-action-button')
			.on('click.productRowActions', '#expired-product .product-row-action-button', function (event) {
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

		$(document).off('click.expiredProductDetails', '#expired-product .expired-detail-btn, #product-row-action-content .expired-detail-btn')
			.on('click.expiredProductDetails', '#expired-product .expired-detail-btn, #product-row-action-content .expired-detail-btn', function (event) {
			event.preventDefault();
			event.stopPropagation();
			var details = JSON.parse($(this).attr('data-details') || '{}');
			var defaultImage = '{{ asset('assets/img/productnoimage.png') }}';
			$('#expiredDetailsModal .detail-image').attr('src', details.image || defaultImage);

			var productName = details.product || '';
			var productTooltip = productName ? 'Product:\n' + productName : 'No product name';
			$('#expiredDetailsModal .product-info-btn')
				.attr('data-tooltip', productTooltip)
				.attr('aria-label', productTooltip);
			$('#expiredDetailsModal .detail-product').attr('title', productName).text(productName);

			Object.keys(details).forEach(function (key) {
				if (key === 'image' || key === 'product') return;
				$('#expiredDetailsModal .detail-' + key).text(details[key] || '');
			});
			var expiryVal = details.expiry || details.expiry_date || '';
			$('#expiredDetailsModal .detail-expiry').text(expiryVal && expiryVal !== '-' ? expiryVal : 'No expiry');

			var boxExpiries = details.box_expiries || [];
			var $boxExpWrap = $('#expiredDetailsModal .detail-box-expiries-wrap');
			var $boxExpList = $('#expiredDetailsModal .detail-box-expiries-list');
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

			var actionModal = $('#product-row-action-modal');
			if (actionModal.hasClass('show')) {
				returnToProductActionsAfterDetails = true;
				runAfterProductActionsReady(function () {
					actionModal.one('hidden.bs.modal.expiredProductDetails', function () {
						window.setTimeout(function () {
							$('#expiredDetailsModal').modal('show');
						}, 50);
					}).modal('hide');
				});
			} else {
				$('#expiredDetailsModal').modal('show');
			}
		});

		$('#expiredDetailsModal').off('hidden.bs.modal.expiredProductDetailsReturn').on('hidden.bs.modal.expiredProductDetailsReturn', function () {
			if (!returnToProductActionsAfterDetails) return;
			returnToProductActionsAfterDetails = false;
			window.setTimeout(function () {
				$('#product-row-action-modal').modal('show');
			}, 50);
		});

		var productEditModal = $('#expired-product-edit-modal');
		var productEditContent = $('#expired-product-edit-modal-content');
		var productEditClosePending = false;

		productEditModal.find('[data-edit-modal-close]').off('click.expiredProductEditClose').on('click.expiredProductEditClose', function (event) {
			event.preventDefault();
			event.stopImmediatePropagation();
			var modalInstance = productEditModal.data('bs.modal');
			if (modalInstance && modalInstance._isTransitioning) {
				if (productEditModal.hasClass('show') && !productEditClosePending) {
					productEditClosePending = true;
					productEditModal.one('shown.bs.modal.expiredProductEditClose', function () {
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

		productEditModal.off('hidden.bs.modal.expiredProductEdit').on('hidden.bs.modal.expiredProductEdit', function () {
			productEditClosePending = false;
			productEditContent.empty();
			if (returnToProductActionsAfterEdit) {
				showProductActionsAfterEditClose();
				return;
			}
			if (activeProductActionButton) activeProductActionButton.setAttribute('aria-expanded', 'false');
			activeProductActionButton = null;
			$('#product-row-action-content').empty();
			if (window.PharmaTabulator) window.PharmaTabulator.reload('expired-product');
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

			$(productSelect).off('change.expiredProductEdit').on('change.expiredProductEdit', function () {
				var data = purchaseMap[this.value] || null;
				setProductDetails(data);
				updateProductTarget(data);
			});

			$form.off('submit.expiredProductEdit').on('submit.expiredProductEdit', function (event) {
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

		$(document).off('click.expiredProductEdit', '#product-row-action-content .editbtn')
			.on('click.expiredProductEdit', '#product-row-action-content .editbtn', function (event) {
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
						switchFromProductActions(productEditModal);
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
        
    });
</script> 
@endpush