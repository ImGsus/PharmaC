@extends('admin.layouts.app')

@push('page-css')
	<!-- Datetimepicker CSS -->
	<link rel="stylesheet" href="{{asset('assets/css/bootstrap-datetimepicker.min.css')}}">
@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Add Purchase</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Add Purchase</li>
	</ul>
</div>
@endpush


@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
				
				<!-- Add Medicine -->
				<form method="post" enctype="multipart/form-data" autocomplete="off" action="{{route('purchases.store')}}">
					@csrf
					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-4">
								<div class="form-group">
									<label>Product Name<span class="text-danger">*</span></label>
									<input class="form-control" type="text" name="product" value="{{ old('product') }}" >
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
									<label>Category <span class="text-danger">*</span></label>
									<select class="select2 form-select form-control" name="category"> 
										@foreach ($categories as $category)
											<option value="{{$category->id}}" {{ old('category') == $category->id ? 'selected' : '' }}>{{$category->name}}</option>
										@endforeach
									</select>
								</div>
							</div>
							<div class="col-lg-4">
								<div class="form-group">
									<label>Supplier <span class="text-danger">*</span></label>
									<select class="select2 form-select form-control" name="supplier"> 
										@foreach ($suppliers as $supplier)
											<option value="{{$supplier->id}}" data-product="{{ $supplier->product }}" {{ old('supplier') == $supplier->id ? 'selected' : '' }}>{{$supplier->name}}</option>
										@endforeach
									</select>
								</div>
							</div>
						</div>
					</div>

						<!-- Price row: keep cost_price field (preserve old value) -->
						<div class="service-fields mb-3">
							<div class="row">
								<div class="col-lg-4">
									<div class="form-group">
										<label>Price per (1) Item<span class="text-danger">*</span></label>
										<input class="form-control" type="text" name="cost_price" value="{{ old('cost_price') }}">
									</div>
								</div>
							</div>
						</div>
					
					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-3">
								<div class="form-group">
									<label>Item (1)<span class="text-danger">*</span></label>
									<input class="form-control" type="number" min="0" name="item_quantity" value="{{ old('item_quantity', 1) }}">
								</div>
							</div>
							<div class="col-lg-3">
								<div class="form-group">
									<label>Packaging box (1)<span class="text-danger">*</span></label>
									<input class="form-control" type="number" min="0" name="packaging_box" value="{{ old('packaging_box') }}">
								</div>
							</div>
							<div class="col-lg-3">
								<div class="form-group">
									<label>Total Quantity Per (1 - Box)<span class="text-danger">*</span></label>
									<input class="form-control" type="number" min="0" name="quantity_per_box" value="{{ old('quantity_per_box') }}">
								</div>
							</div>
							<div class="col-lg-3">
								<div class="form-group">
									<label>Total<span class="text-danger">*</span></label>
									<input class="form-control" type="number" min="0" name="total_quantity" value="{{ old('total_quantity') }}" readonly>
								</div>
							</div>
						</div>
					</div>

					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group">
									<label>Expire Date<span class="text-danger">*</span></label>
									<input class="form-control" type="date" name="expiry_date" value="{{ old('expiry_date') }}">
								</div>
							</div>
							<div class="col-lg-6">
								<div class="form-group">
									<label>Medicine Image</label>
									<input type="file" name="image" class="form-control">
								</div>
							</div>
						</div>
					</div>
					
					
					<div class="submit-section">
						<button class="btn btn-primary submit-btn" type="submit" >Submit</button>
					</div>
				</form>
				<!-- /Add Medicine -->

			</div>
		</div>
	</div>			
</div>
@endsection

@push('page-js')
	<!-- Datetimepicker JS -->
	<script src="{{asset('assets/js/moment.min.js')}}"></script>
	<script src="{{asset('assets/js/bootstrap-datetimepicker.min.js')}}"></script>	
	<script>
		// auto-calc total_quantity = item_quantity + (packaging_box * quantity_per_box)
		function computeTotal() {
			var item = parseInt($('input[name="item_quantity"]').val() || 0, 10);
			var boxes = parseInt($('input[name="packaging_box"]').val() || 0, 10);
			var perBox = parseInt($('input[name="quantity_per_box"]').val() || 0, 10);
			var total = item + (boxes * perBox);
			$('input[name="total_quantity"]').val(total);
		}
		$(function(){
			// compute on change
			$('input[name="item_quantity"], input[name="packaging_box"], input[name="quantity_per_box"]').on('input', computeTotal);
			// compute on page load (use old values if present)
			computeTotal();

							// auto-fill product name when supplier is selected
							$('select[name="supplier"]').on('change', function(){
								var prod = $(this).find('option:selected').data('product') || '';
								$('input[name="product"]').val(prod);
							});
							// trigger on load if selection exists
							$('select[name="supplier"]').trigger('change');
		});
	</script>
@endpush

