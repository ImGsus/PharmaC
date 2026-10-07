@extends('admin.layouts.app')

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Edit Purchase</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Edit Purchase</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
			
			<!-- Edit Supplier -->
			<form method="post" enctype="multipart/form-data" autocomplete="off" action="{{route('purchases.update',$purchase)}}">
				@csrf
				@method("PUT")
				<div class="service-fields mb-3">
					<div class="row">
						<div class="col-lg-4">
							<div class="form-group">
								<label>Medicine Name<span class="text-danger">*</span></label>
								<input class="form-control" type="text" value="{{$purchase->product}}" name="product" >
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Category <span class="text-danger">*</span></label>
								<select class="select2 form-select form-control" name="category"> 
									@foreach ($categories as $category)
										<option {{($purchase->category->id == $category->id) ? 'selected': ''}} value="{{$category->id}}">{{$category->name}}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>Supplier <span class="text-danger">*</span></label>
								<select class="select2 form-select form-control" name="supplier"> 
									@foreach ($suppliers as $supplier)
										<option @if($purchase->supplier->id == $supplier->id) selected @endif value="{{$supplier->id}}">{{$supplier->name}}</option>
									@endforeach
								</select>
							</div>
						</div>
					</div>
				</div>

				<div class="service-fields mb-3">
					<div class="row">
						<div class="col-lg-3"><div class="form-group"><label>Batch / Lot Number</label><input class="form-control" type="text" name="batch_number" value="{{ $purchase->batch_number }}" maxlength="100"></div></div>
						<div class="col-lg-3"><div class="form-group"><label>Manufacture Date</label><input class="form-control" type="date" name="manufacture_date" value="{{ $purchase->manufacture_date }}"></div></div>
						<div class="col-lg-3"><div class="form-group"><label>Reorder Level</label><input class="form-control" type="number" min="0" name="reorder_level" value="{{ $purchase->reorder_level ?? 10 }}"></div></div>
						<div class="col-lg-3"><div class="form-group"><label>Purchase Order Number</label><input class="form-control" type="text" name="order_number" value="{{ $purchase->order_number }}" maxlength="100"></div></div>
					</div>
				</div>
				
					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group">
									<label>Cost Price<span class="text-danger">*</span></label>
									<input class="form-control" value="{{$purchase->cost_price}}" type="text" name="cost_price">
								</div>
							</div>
						</div>
					</div>
				
					<div class="service-fields mb-3">
					<div class="row">
							<div class="col-lg-3">
								<div class="form-group">
									<label>Item (1)</label>
									<input class="form-control" type="number" min="0" name="item_quantity" value="{{$purchase->item_quantity}}" placeholder="0">
								</div>
							</div>
							<div class="col-lg-3">
								<div class="form-group">
									<label>Packaging box (1)</label>
									<input class="form-control" type="number" min="0" name="packaging_box" value="{{$purchase->packaging_box}}" placeholder="0">
								</div>
							</div>
							<div class="col-lg-3">
								<div class="form-group">
									<label>Total Quantity Per (1 - Box)</label>
									<input class="form-control" type="number" min="0" name="quantity_per_box" value="{{$purchase->quantity_per_box}}" placeholder="0">
								</div>
							</div>
							<div class="col-lg-3">
								<div class="form-group">
									<label>Total<span class="text-danger">*</span></label>
									<input class="form-control" type="number" min="0" name="total_quantity" value="{{$purchase->total_quantity}}" readonly>
									</div>
								</div>
				</div>

				<div class="service-fields mb-3">
					<div class="row">
						<div class="col-lg-6">
							<div class="form-group">
								<label>Expire Date<span class="text-danger">*</span></label>
								<input class="form-control" value="{{$purchase->expiry_date}}" type="date" name="expiry_date">
							</div>
						</div>
						<div class="col-lg-6">
							<div class="form-group">
								<label>Product Image</label>
								{{-- Image preview — shows the currently-saved image.
									 When the user picks a new file, JS swaps it for a live
									 preview of the new selection. The actual save happens
									 server-side: the controller deletes the old file and
									 stores the new one. --}}
								<div class="purchase-image-preview mb-2" id="purchase-image-preview"
									data-fallback="{{ asset('assets/img/productnoimage.png') }}"
									data-current="{{ $purchase->image ? url('storage/system/purchases/'.$purchase->image) : asset('assets/img/productnoimage.png') }}">
									<img id="purchase-image-preview-img"
										 src="{{ $purchase->image ? url('storage/system/purchases/'.$purchase->image) : asset('assets/img/productnoimage.png') }}"
										alt="Product image">
									@if($purchase->image)
										<small class="text-muted d-block mt-1">Current image: <code>{{ $purchase->image }}</code></small>
									@else
										<small class="text-muted d-block mt-1">No image uploaded yet.</small>
									@endif
								</div>
								<div class="purchase-image-input-row">
									<input type="file" name="image" id="purchase-image-input" class="purchase-image-file" accept="image/*">
									<div class="purchase-image-source dropdown">
										<button type="button" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-image mr-1"></i> Add photo</button>
										<div class="dropdown-menu source-menu"><label for="purchase-image-input"><i class="fa fa-folder-open-o mr-1"></i> Choose file</label><button type="button" class="purchase-camera-button" id="purchase-camera-button"><i class="fa fa-camera mr-1"></i> Use camera</button></div>
									</div>
									<span class="purchase-image-file-name" id="purchase-image-file-name">{{ $purchase->image ?: 'No file chosen' }}</span>
								</div>
								@include('components.purchase-camera')
								<small class="form-text text-muted">Allowed: JPG, JPEG, PNG, GIF. Leave empty to keep the current image.</small>
							</div>
						</div>
					</div>
				</div>
				
				
				<div class="submit-section">
					<button class="btn btn-primary submit-btn" type="submit" >Submit</button>
				</div>
			</form>
			<!-- /Edit Supplier -->

			</div>
		</div>
	</div>			
</div>
@endsection	



@push('page-js')
	<!-- Select2 JS -->
	<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
	<script>
		(function(){
			// Live preview when picking a new image. The server replaces
			// the file on submit; this is just to give the cashier a
			// visual confirmation of what they selected.
			var input = document.getElementById('purchase-image-input');
			var img   = document.getElementById('purchase-image-preview-img');
			var wrap  = document.getElementById('purchase-image-preview');
			if (!input || !img || !wrap) return;
			var fallback = wrap.getAttribute('data-fallback');

			input.addEventListener('change', function () {
				var file = input.files && input.files[0];
				if (!file) {
					img.src = wrap.getAttribute('data-current') || fallback;
					return;
				}
				if (!file.type || file.type.indexOf('image/') !== 0) {
					if (window.Snackbar) {
						Snackbar.show({
							text: 'Please choose an image file (JPG, PNG, GIF).',
							duration: 4000, pos: 'top-right',
							backgroundColor: '#e8483f', textColor: '#ffffff'
						});
					}
					input.value = '';
					return;
				}
				// Use a Blob URL for an instant preview without uploading.
				var url = URL.createObjectURL(file);
				img.src = url;
			});
		})();

		$(function () {
			function computeEditTotal() {
				var item = parseInt($('input[name="item_quantity"]').val() || 0, 10);
				var boxes = parseInt($('input[name="packaging_box"]').val() || 0, 10);
				var perBox = parseInt($('input[name="quantity_per_box"]').val() || 0, 10);
				if (isNaN(item) || item < 0) item = 0;
				if (isNaN(boxes) || boxes < 0) boxes = 0;
				if (isNaN(perBox) || perBox < 0) perBox = 0;
				var total = item + (boxes * perBox);
				$('input[name="total_quantity"]').val(total);
			}

			$('input[name="item_quantity"], input[name="packaging_box"], input[name="quantity_per_box"]').on('input change', computeEditTotal);
			computeEditTotal();
		});
	</script>
@endpush

@push('page-css')
<style>
	.purchase-image-preview {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		padding: 10px;
		background: #f7f9fc;
		border: 1px dashed #cdd5e0;
		border-radius: 8px;
		min-height: 160px;
		text-align: center;
	}
	.purchase-image-preview img {
		max-width: 100%;
		max-height: 220px;
		width: auto;
		height: auto;
		object-fit: contain;
		border-radius: 6px;
		background: #fff;
	}
	.purchase-image-preview code {
		user-select: all;
	}
</style>
@endpush




