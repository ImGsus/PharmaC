@extends('admin.layouts.app')

@push('page-css')
<style>
    /* Main card wrapper: adjust width, spacing, and position here */
    .custom-card {
        max-width: 980px;
        margin: 20px auto;
        padding: 25px;
    }

    /* Description box: change width, height, and spacing from here */
    .custom-description {
        min-height: 220px;
        width: 100%;
        margin-top: 10px;
    }

    /* Submit button: adjust size, alignment, and vertical spacing here */
    .custom-submit {
        width: 170px;
        height: 52px;
        margin-top: 20px;
    }
</style>
@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Edit Product</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Edit Product</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
			<div class="card custom-card">

			<!-- Edit Product -->
				<form method="post" enctype="multipart/form-data" id="update_service" action="{{route('products.update',$product)}}" data-base-url="{{ url('products') }}">
					@csrf
                    @method("PUT")
					<div class="service-fields mb-3">
						<div class="row">
							
							<div class="col-lg-12">
								<div class="form-group">
									<label>Product <span class="text-danger">*</span></label>
									<select class="select2 form-select form-control" name="product"> 
										@foreach ($purchases as $purchase)
											@if(!empty($product->purchase))
											<option id="purchase_{{$purchase->id}}" {{($product->purchase->id == $purchase->id) ? 'selected': ''}} value="{{$purchase->id}}">{{$purchase->product}}</option>
											@endif
										@endforeach
									</select>
								</div>
							</div>
						</div>
					</div>
					
					<div class="service-fields mb-3">
						<div class="row">
								<div class="col-lg-4">
									<div class="form-group">
										<label>Selling Price<span class="text-danger">*</span></label>
										<input class="form-control" type="text" name="price" value="{{$product->price}}">
									</div>
								</div>

								<div class="col-lg-4">
									<div class="form-group">
										<label>SKU Num</label>
										<input class="form-control" type="text" name="barcode" value="{{$product->barcode}}" placeholder="Enter or scan SKU">
									</div>
								</div>
							</div>
						</div>

						<!-- Description box: move/resize using custom-description class -->
						<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-12">
								<div class="form-group">
									<label>Descriptions <span class="text-danger">*</span></label>
								<textarea class="form-control custom-description" value="{{$product->description}}" name="description">{{$product->description}}</textarea>
								</div>
							</div>
							
						</div>
					</div>					
					
					<div class="submit-section">
						<button class="btn btn-primary submit-btn" type="submit" name="form_submit" value="submit">Submit</button>
					</div>
				</form>
			<!-- /Edit Product -->
			</div>
		</div>
	</div>			
</div>
@endsection


@push('page-js')
	<script>
		var purchaseProductMap = @json($purchaseMap ?? []);
		(function(){
			var productSelect = document.querySelector('select[name="product"]');
			var barcodeInput = document.querySelector('input[name="barcode"]');
			var priceInput = document.querySelector('input[name="price"]');
			var descInput = document.querySelector('textarea[name="description"]');

			function setProductDetails(productData) {
				if (!productData) {
					if (barcodeInput) barcodeInput.value = '';
					if (priceInput) priceInput.value = '';
					if (descInput) descInput.value = '';
					return;
				}
				if (barcodeInput) barcodeInput.value = productData.barcode || '';
				if (priceInput) priceInput.value = productData.price || '';
				if (descInput) descInput.value = productData.description || '';
			}

			if (productSelect) {
				var form = document.getElementById('update_service');
				var originalAction = form ? form.action : null;
				var baseUrl = form ? form.dataset.baseUrl : null;
				var currentProductId = parseInt(@json($product->id));
				var methodInput = form ? form.querySelector('input[name="_method"]') : null;

				function applyFormTargetForData(data) {
					// data === null -> no existing product for selected purchase -> POST to store
					if (data && data.id && baseUrl) {
						form.action = baseUrl + '/' + data.id;
						// ensure _method is PUT
						if (!methodInput) {
							methodInput = document.createElement('input');
							methodInput.type = 'hidden';
							methodInput.name = '_method';
							form.appendChild(methodInput);
						}
						methodInput.value = 'PUT';
						currentProductId = parseInt(data.id);
						console.log('Switched form to update product', data.id);
					} else {
						// No existing product: restore to store (POST)
						if (originalAction) form.action = originalAction;
						if (methodInput) {
							methodInput.parentNode.removeChild(methodInput);
							methodInput = null;
						}
						currentProductId = null;
						console.log('Switched form to create (store)');
					}
				}

				productSelect.addEventListener('change', function(){
					var data = purchaseProductMap[this.value] || null;
					setProductDetails(data);
					applyFormTargetForData(data);
				});

				if (window.jQuery && jQuery(productSelect).data('select2')) {
					jQuery(productSelect).on('select2:select', function(e){
						var id = e.params.data.id;
						var data = purchaseProductMap[id] || null;
						setProductDetails(data);
						applyFormTargetForData(data);
					});
				}

				// Initial populate
				var initVal = productSelect.value;
				if (initVal) {
					var initData = purchaseProductMap[initVal] || null;
					setProductDetails(initData);
					// Ensure form target matches initial selection
					applyFormTargetForData(initData);
				}

				// Polling fallback
				var last = productSelect.value;
				setInterval(function(){
					if (!productSelect) return;
					if (productSelect.value !== last) {
						last = productSelect.value;
						var data = purchaseProductMap[last] || null;
						setProductDetails(data);
						applyFormTargetForData(data);
					}
				}, 300);
			}
		})();
	</script>
@endpush