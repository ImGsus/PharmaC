@extends('admin.layouts.app')

<x-assets.tabulator />

@push('page-css')
<style>
	#addSaleModal .modal-dialog { height: calc(100vh - 56px); margin: 28px auto; max-width: 1240px; width: calc(100vw - 2rem); }
	#addSaleModal .modal-content { height: 100%; }
	#addSaleModal .modal-body { flex: 1 1 auto; min-height: 0; overflow: hidden; padding: 0; }
	#addSaleModal iframe { border: 0; display: block; height: 100%; width: 100%; }
	@media (max-width: 767.98px) {
		#addSaleModal .modal-dialog { height: 100dvh; margin: 0; max-width: none; width: 100%; }
		#addSaleModal .modal-content { border-radius: 0; height: 100dvh; }
	}
	#saleEditModal .modal-dialog { max-width: 560px; }
	#saleEditModal .modal-content { border: 1px solid #b8c4d2; border-radius: 8px; box-shadow: 0 16px 36px rgba(31, 45, 61, .2); }
	#saleEditModal .modal-header { padding: 20px 24px; border-bottom: 1px solid #e5eaf1; }
	#saleEditModal .modal-body { padding: 24px; }
	#saleEditModal .form-control { min-height: 44px; border-color: #cfd7e3; border-radius: 5px; }
	#saleEditModal .modal-footer .btn-primary { min-width: 150px; border-radius: 22px; }
	body.dark-mode #saleEditModal .modal-content { background: #1c2025; border-color: #475569; color: #f8fafc; }
	body.dark-mode #saleEditModal .modal-header { border-bottom-color: #475569; }
	body.dark-mode #saleEditModal label, body.dark-mode #saleEditModal .modal-title { color: #e2e8f0; }
	body.dark-mode #saleEditModal .form-control { background: #111827; border-color: #475569; color: #f8fafc; }
	body.dark-mode #saleEditModal .form-control:focus { border-color: #69d9aa; box-shadow: 0 0 0 2px rgba(105, 217, 170, .18); }
	#saleEditModal .modal-footer .btn-light { color: #344054; background: #f8fafc; border-color: #cbd5e1; }
	body.dark-mode #saleEditModal .modal-footer .btn-light { color: #10231d; background: #f8fafc; border-color: #cbd5e1; }
	#saleProductDetailsModal .modal-dialog { max-width: 760px; }
	#saleProductDetailsModal .sale-product-details-layout { align-items: stretch; display: grid; gap: 24px; grid-template-columns: minmax(220px, .9fr) minmax(0, 1.35fr); }
	#saleProductDetailsModal .sale-product-details-image { align-items: center; background: #f5f7fb; border: 1px solid #e5eaf1; border-radius: 8px; display: flex; justify-content: center; min-height: 300px; padding: 18px; }
	#saleProductDetailsModal .sale-product-details-image img { border-radius: 6px; max-height: 300px; object-fit: contain; width: 100%; }
	#saleProductDetailsModal .sale-product-details-info { align-content: center; display: grid; gap: 12px 18px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
	#saleProductDetailsModal .sale-product-details-info > div { border-bottom: 1px solid #edf0f4; min-width: 0; padding-bottom: 8px; }
	#saleProductDetailsModal .sale-product-details-info strong { color: #7a8795; display: block; font-size: 12px; font-weight: 600; margin-bottom: 3px; text-transform: uppercase; }
	#saleProductDetailsModal .sale-product-details-info span { color: #263238; overflow-wrap: anywhere; word-break: break-word; }
	#saleProductDetailsModal .sale-product-detail-value-wrap { align-items: center; display: flex; gap: 6px; justify-content: space-between; min-width: 0; position: relative; }
	#saleProductDetailsModal .sale-product-detail.product { background: transparent !important; border: 0 !important; border-radius: 0 !important; box-shadow: none !important; display: block; flex: 1 1 auto; margin: 0 !important; min-width: 0; overflow: hidden; padding: 0 !important; text-overflow: ellipsis; transition: none !important; white-space: nowrap; }
	#saleProductDetailsModal .sale-product-info-btn { align-items: center; background: transparent; border: 0; color: #0f766e; cursor: help; display: inline-flex; flex: 0 0 auto; font-size: 15px; height: 1.5rem; justify-content: center; padding: 0; position: relative; width: 1.5rem; }
	#saleProductDetailsModal .sale-product-info-btn:hover,
	#saleProductDetailsModal .sale-product-info-btn:focus { color: #0d9488; }
	#saleProductDetailsModal .sale-product-info-btn::after { background: #1f2937; border-radius: 5px; bottom: calc(100% + 8px); box-shadow: 0 4px 14px rgba(0, 0, 0, .25); color: #fff; content: attr(data-tooltip); font-size: 12px; left: auto; line-height: 1.35; max-height: 180px; opacity: 0; overflow-wrap: anywhere; overflow-y: auto; padding: 8px 10px; pointer-events: none; position: absolute; right: 0; text-align: left; visibility: hidden; white-space: pre-wrap; width: 220px; word-break: break-all; z-index: 1060; }
	#saleProductDetailsModal .sale-product-info-btn:hover::after,
	#saleProductDetailsModal .sale-product-info-btn:focus::after { opacity: 1; visibility: visible; }
	body.dark-mode #saleProductDetailsModal .sale-product-details-image { background: #111827; border-color: #334155; }
	body.dark-mode #saleProductDetailsModal .sale-product-details-info > div { border-bottom-color: #334155; }
	body.dark-mode #saleProductDetailsModal .sale-product-details-info strong { color: #94a3b8; }
	body.dark-mode #saleProductDetailsModal .sale-product-details-info span { color: #f8fafc; }
	body.dark-mode #saleProductDetailsModal .sale-product-info-btn { color: #14b8a6; }
	body.dark-mode #saleProductDetailsModal .sale-product-info-btn:hover,
	body.dark-mode #saleProductDetailsModal .sale-product-info-btn:focus { color: #2dd4bf; }
	body.dark-mode #saleProductDetailsModal .sale-product-info-btn::after { background: #0f172a; border: 1px solid #334155; box-shadow: 0 4px 14px rgba(0, 0, 0, .5); color: #f8fafc; }
	@media (max-width: 576px) {
		#saleProductDetailsModal .sale-product-details-layout,
		#saleProductDetailsModal .sale-product-details-info { grid-template-columns: 1fr; }
		#saleProductDetailsModal .sale-product-details-image { min-height: 220px; }
	}
</style>
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Sales</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Sales</li>
	</ul>
</div>
@can('create-sale')
<div class="col-sm-5 col">
	<button type="button" class="btn btn-primary float-right mt-2" data-toggle="modal" data-target="#addSaleModal">Add Sale</button>
</div>
@endcan
@endpush

@section('content')
<div class="row">
	<div class="col-md-12">
	
		<!--  Sales -->
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<div id="sales-table" class="tabulator-table-wrap"></div>
				</div>
			</div>
		</div>
		<!-- / sales -->
		
	</div>
</div>

<div class="modal fade" id="saleEditModal" tabindex="-1" role="dialog" aria-labelledby="saleEditModalTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header"><h5 class="modal-title" id="saleEditModalTitle">Edit Sale</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button></div>
			<form method="POST" id="saleEditForm">
				@csrf
				@method('PUT')
				<div class="modal-body">
					<div class="form-group"><label for="saleEditProduct">Product</label><select name="product" id="saleEditProduct" class="form-control" required>@foreach ($products as $product) @if (!empty($product->purchase))<option value="{{ $product->id }}">{{ $product->purchase->product }}</option>@endif @endforeach</select></div>
					<div class="form-group mb-0"><label for="saleEditQuantity">Quantity</label><input type="number" min="1" name="quantity" id="saleEditQuantity" class="form-control" required></div>
				</div>
				<div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Changes</button></div>
			</form>
		</div>
	</div>
</div>

@can('create-sale')
<div class="modal fade" id="addSaleModal" tabindex="-1" role="dialog" aria-labelledby="addSaleModalTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="addSaleModalTitle">Add Sale</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<iframe id="addSaleFrame" title="Add Sale" data-src="{{ route('pos.orders', ['embed' => 1]) }}"></iframe>
			</div>
		</div>
	</div>
</div>
@endcan

<div class="modal fade" id="saleProductDetailsModal" tabindex="-1" role="dialog" aria-labelledby="saleProductDetailsModalTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header"><h5 class="modal-title" id="saleProductDetailsModalTitle">Product Details</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button></div>
			<div class="modal-body">
				<div class="sale-product-details-layout">
					<div class="sale-product-details-image"><img class="sale-product-detail-image" src="{{ asset('assets/img/productnoimage.png') }}" alt="Product image"></div>
					<div class="sale-product-details-info">
						<div><strong>Product</strong><div class="sale-product-detail-value-wrap"><span class="sale-product-detail product" data-detail="product"></span><button type="button" class="sale-product-info-btn" aria-label="Product details" data-tooltip="Product: "><i class="fas fa-info-circle" aria-hidden="true"></i></button></div></div>
						<div><strong>Category</strong><span class="sale-product-detail" data-detail="category"></span></div>
						<div><strong>Supplier</strong><span class="sale-product-detail" data-detail="supplier"></span></div>
						<div><strong>Price</strong><span class="sale-product-detail" data-detail="price"></span></div>
						<div><strong>Quantity</strong><span class="sale-product-detail" data-detail="quantity"></span></div>
						<div><strong>Item Quantity</strong><span class="sale-product-detail" data-detail="item_quantity"></span></div>
						<div><strong>Packaging Box</strong><span class="sale-product-detail" data-detail="packaging_box"></span></div>
						<div><strong>Quantity per Box</strong><span class="sale-product-detail" data-detail="quantity_per_box"></span></div>
						<div><strong>Expiry Date</strong><span class="sale-product-detail" data-detail="expiry"></span></div>
						<div><strong>Date of Purchase</strong><span class="sale-product-detail" data-detail="purchased"></span></div>
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
		var addSaleFrame = document.getElementById('addSaleFrame');
		var addSaleModalShown = false;
		function focusAddSaleScannerFrame() {
			if (!addSaleFrame || !addSaleModalShown) return;
			var frameWindow = addSaleFrame.contentWindow;
			var frameDocument = addSaleFrame.contentDocument;
			if (!frameDocument || !frameDocument.body || !frameDocument.querySelector('#pos-v2-scan-status')) return;
			frameDocument.body.setAttribute('tabindex', '-1');
			frameWindow.focus();
			frameDocument.body.focus();
		}
		$(document).off('.salesAddSale')
			.on('show.bs.modal.salesAddSale', '#addSaleModal', function () {
				addSaleModalShown = false;
				var frameSrc = addSaleFrame && addSaleFrame.getAttribute('src');
				if (addSaleFrame && (!frameSrc || frameSrc === 'about:blank')) {
					addSaleFrame.src = addSaleFrame.dataset.src;
				}
			})
			.on('shown.bs.modal.salesAddSale', '#addSaleModal', function () {
				addSaleModalShown = true;
				focusAddSaleScannerFrame();
			})
			.on('hidden.bs.modal.salesAddSale', '#addSaleModal', function (event) {
				addSaleModalShown = false;
				if (addSaleFrame && !event.currentTarget.classList.contains('show')) {
					addSaleFrame.removeAttribute('src');
				}
			});
		if (addSaleFrame) $(addSaleFrame).off('load.salesAddSale').on('load.salesAddSale', function () {
				try {
					var frameWindow = this.contentWindow;
					var framePath = frameWindow.location.pathname;
					if (framePath === @json(parse_url(route('pos.orders'), PHP_URL_PATH))) {
						focusAddSaleScannerFrame();
					}
					if (framePath === @json(parse_url(route('sales.index'), PHP_URL_PATH))) {
						$('#addSaleModal').modal('hide');
						if (window.PharmaTabulator) window.PharmaTabulator.reload('sales-table');
					}
				} catch (error) {
					// Ignore cross-origin navigations; the POS frame should stay on this app.
				}
			});

        if (window.PharmaTabulator) {
            window.PharmaTabulator.server({
                el: 'sales-table',
                url: "{{route('sales.index')}}",
                columns: [
                    {title: 'Medicine Name', field: 'product', formatter: 'html'},
                    {title: 'Quantity', field: 'quantity'},
                    {title: 'Total Price', field: 'total_price'},
                    {title: 'Date', field: 'date'},
                    {title: 'Action', field: 'action', formatter: 'html', headerSort: false, searchable: false, width: 110, hozAlign: 'center'}
                ]
            });
        }
		$(document).off('click.saleProductDetails', '.sale-product-detail-link').on('click.saleProductDetails', '.sale-product-detail-link', function (event) {
			event.preventDefault();
			var details;
			try {
				details = JSON.parse($(this).attr('data-details') || '{}');
			} catch (error) {
				return;
			}
			$('#saleProductDetailsModal .sale-product-detail').each(function () {
				var key = $(this).data('detail');
				$(this).text(details[key] || '');
			});
			$('#saleProductDetailsModal .sale-product-detail-image').attr('src', details.image || '{{ asset('assets/img/productnoimage.png') }}');
			var productName = details.product || '';
			$('#saleProductDetailsModal .sale-product-info-btn')
				.attr('data-tooltip', productName ? 'Product:\n' + productName : 'No product name')
				.attr('aria-label', productName ? 'Product: ' + productName : 'No product name');
			$('#saleProductDetailsModal').modal('show');
		});
		$(document).off('click.saleEdit', '.sale-edit-btn').on('click.saleEdit', '.sale-edit-btn', function () {
			var button = $(this);
			$('#saleEditForm').attr('action', '{{ url('sales') }}/' + button.data('sale-id'));
			$('#saleEditProduct').val(String(button.data('product-id')));
			$('#saleEditQuantity').val(button.data('quantity'));
			$('#saleEditModal').modal('show');
		});
    });
</script>
@endpush