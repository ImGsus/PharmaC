@extends('admin.layouts.app')

<x-assets.tabulator />

@push('page-css')
<style>
	#outstockDetailsModal .modal-dialog {
		max-width: 760px;
	}
	#outstockDetailsModal .product-details-layout {
		display: grid;
		grid-template-columns: minmax(220px, .9fr) minmax(0, 1.35fr);
		gap: 24px;
		align-items: stretch;
	}
	#outstockDetailsModal .product-details-image {
		display: flex;
		align-items: center;
		justify-content: center;
		min-height: 300px;
		padding: 18px;
		background: #f5f7fb;
		border: 1px solid #e5eaf1;
		border-radius: 8px;
	}
	#outstockDetailsModal .product-details-image img {
		width: 100%;
		max-height: 300px;
		object-fit: contain;
		border-radius: 6px;
	}
	#outstockDetailsModal .product-details-info {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: 12px 18px;
		align-content: center;
	}
	#outstockDetailsModal .product-detail-item {
		margin: 0;
		padding-bottom: 8px;
		border-bottom: 1px solid #edf0f4;
		min-width: 0;
		position: relative;
	}
	#outstockDetailsModal .product-detail-item strong {
		display: block;
		margin-bottom: 3px;
		font-size: 12px;
		font-weight: 600;
		color: #7a8795;
		text-transform: uppercase;
	}
	#outstockDetailsModal .product-detail-item span {
		color: #263238;
		word-break: break-word;
		overflow-wrap: anywhere;
	}
	#outstockDetailsModal .product-detail-value-wrap {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 6px;
		min-width: 0;
		position: relative;
	}
	#outstockDetailsModal .product-detail-value-wrap .detail-product {
		min-width: 0;
		flex: 1 1 auto;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		display: block;
	}
	#outstockDetailsModal .product-info-btn {
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
	#outstockDetailsModal .product-info-btn:hover,
	#outstockDetailsModal .product-info-btn:focus {
		color: #0d9488;
	}
	#outstockDetailsModal .product-info-btn::after {
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
	#outstockDetailsModal .product-info-btn:hover::after,
	#outstockDetailsModal .product-info-btn:focus::after {
		opacity: 1;
		visibility: visible;
	}
	@media (max-width: 576px) {
		#outstockDetailsModal .product-details-layout,
		#outstockDetailsModal .product-details-info {
			grid-template-columns: 1fr;
		}
		#outstockDetailsModal .product-details-image {
			min-height: 220px;
		}
	}
	body.dark-mode #outstockDetailsModal .product-details-image {
		background: #1e293b;
		border-color: #334155;
	}
	body.dark-mode #outstockDetailsModal .product-detail-item {
		border-bottom-color: #334155;
	}
	body.dark-mode #outstockDetailsModal .product-detail-item strong {
		color: #94a3b8;
	}
	body.dark-mode #outstockDetailsModal .product-detail-item span {
		color: #e2e8f0;
	}
	body.dark-mode #outstockDetailsModal .product-info-btn {
		color: #14b8a6;
	}
	body.dark-mode #outstockDetailsModal .product-info-btn:hover,
	body.dark-mode #outstockDetailsModal .product-info-btn:focus {
		color: #2dd4bf;
	}
	body.dark-mode #outstockDetailsModal .product-info-btn::after {
		background: #0f172a;
		border: 1px solid #334155;
		color: #f8fafc;
		box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5);
	}
</style>
@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Outstock</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('products.index')}}">Products</a></li>
		<li class="breadcrumb-item active">Outstock</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-md-12">
	
		<!-- Outstock Products -->
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<div id="outstock-product" class="tabulator-table-wrap"></div>
				</div>
			</div>
		</div>
		<!-- /Outstock Products-->
		
	</div>
</div>

<div class="modal fade" id="outstockDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header"><h5 class="modal-title">Product Details</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
			<div class="modal-body">
				<div class="product-details-layout">
					<div class="product-details-image">
						<img class="outstock-detail detail-image" src="{{ asset('assets/img/productnoimage.png') }}" onerror="this.onerror=null;this.src='{{ asset('assets/img/productnoimage.png') }}';" alt="Product image">
					</div>
					<div class="product-details-info">
						<div class="product-detail-item">
							<strong>Product</strong>
							<div class="product-detail-value-wrap">
								<span class="outstock-detail detail-product product-name"></span>
								<button type="button" class="product-info-btn" aria-label="Product details" data-tooltip="Product: ">
									<i class="fas fa-info-circle" aria-hidden="true"></i>
								</button>
							</div>
						</div>
						<div class="product-detail-item"><strong>Category</strong><span class="outstock-detail detail-category"></span></div>
						<div class="product-detail-item"><strong>Supplier</strong><span class="outstock-detail detail-supplier"></span></div>
						<div class="product-detail-item"><strong>Price</strong><span class="outstock-detail detail-price"></span></div>
						<div class="product-detail-item"><strong>Quantity</strong><span class="outstock-detail detail-quantity"></span></div>
						<div class="product-detail-item"><strong>Item Quantity</strong><span class="outstock-detail detail-item_quantity"></span></div>
						<div class="product-detail-item"><strong>Packaging Box</strong><span class="outstock-detail detail-packaging_box"></span></div>
						<div class="product-detail-item"><strong>Quantity per Box</strong><span class="outstock-detail detail-quantity_per_box"></span></div>
						<div class="product-detail-item"><strong>Expire Date</strong><span class="outstock-detail detail-expiry"></span></div>
						<div class="product-detail-item"><strong>Date of Purchase</strong><span class="outstock-detail detail-purchased"></span></div>
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
        if (!window.PharmaTabulator) return;
        window.PharmaTabulator.server({
            el: 'outstock-product',
            url: "{{route('outstock')}}",
            columns: [
                {title: 'Brand Name', field: 'product', formatter: 'html'},
                {title: 'Category', field: 'category'},
                {title: 'Price', field: 'price'},
                {title: 'Quantity', field: 'quantity'},
                {title: 'Action', field: 'action', formatter: 'html', headerSort: false, searchable: false, width: 110, hozAlign: 'center'},
            ]
        });

		$(document).on('click', '.outstock-detail-btn', function () {
			var details = JSON.parse($(this).attr('data-details') || '{}');
			var defaultImage = '{{ asset('assets/img/productnoimage.png') }}';
			$('#outstockDetailsModal .detail-image').attr('src', details.image || defaultImage);

			var productName = details.product || '';
			var productTooltip = productName ? 'Product:\n' + productName : 'No product name';
			$('#outstockDetailsModal .product-info-btn')
				.attr('data-tooltip', productTooltip)
				.attr('aria-label', productTooltip);
			$('#outstockDetailsModal .detail-product').attr('title', productName).text(productName);

			Object.keys(details).forEach(function (key) {
				if (key === 'image' || key === 'product') return;
				$('#outstockDetailsModal .detail-' + key).text(details[key] || '');
			});
			var expiryVal = details.expiry || details.expiry_date || '';
			$('#outstockDetailsModal .detail-expiry').text(expiryVal && expiryVal !== '-' ? expiryVal : 'No expiry');
			$('#outstockDetailsModal').modal('show');
		});
        
    });
</script> 
@endpush