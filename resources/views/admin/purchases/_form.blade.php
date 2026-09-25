<form method="post" enctype="multipart/form-data" autocomplete="off" action="{{route('purchases.store')}}">
	@csrf
	@if ($errors->any())
		<div class="alert alert-danger purchase-validation-alert" role="alert">
			<strong>Please complete the required fields before submitting.</strong>
			<ul class="mb-0 mt-2">
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif
	@if(session('duplicate_purchase_warning'))
		<div class="alert alert-warning">{{ session('duplicate_purchase_warning') }}</div>
	@endif
	<div class="service-fields mb-3">
		<div class="row">
			<div class="col-lg-4">
				<div class="form-group">
					<label class="d-block">Product Name<span class="text-danger">*</span></label>
					<input class="form-control" type="text" name="product" value="{{ old('product', $clearPrefill ? '' : ($preselectedProduct ?? '')) }}" required>
				</div>
			</div>
			<div class="col-lg-4">
				<div class="form-group">
					<label class="d-block">Category <span class="text-danger">*</span></label>
					<select class="select2 form-select form-control" name="category" data-placeholder="Select category" data-allow-clear="true" required>
						<option value="" {{ old('category', $clearPrefill ? '' : ($preselectedCategory ?? '')) == '' ? 'selected' : '' }}>Select category</option>
						@foreach ($categories as $category)
							<option value="{{$category->id}}" data-no-expiry="{{ $category->no_expiry ? '1' : '0' }}" {{ old('category', $clearPrefill ? '' : ($preselectedCategory ?? '')) == $category->id ? 'selected' : '' }}>{{$category->name}}</option>
						@endforeach
					</select>
					<label class="purchase-expiry-switch">
						<span>No expiry for this purchase</span>
						<input type="checkbox" name="no_expiry" value="1" {{ old('no_expiry') ? 'checked' : '' }}>
					</label>
				</div>
			</div>
			<div class="col-lg-4">
				<div class="form-group">
					<label class="d-block">Supplier <span class="text-danger">*</span></label>
					<select class="select2 form-select form-control" name="supplier" data-placeholder="Select supplier" data-allow-clear="true" required>
						<option value="" {{ old('supplier', $clearPrefill ? '' : ($preselectedSupplier ?? '')) == '' ? 'selected' : '' }}>Select supplier</option>
						@if (!empty($pendingSupplier))
							<option value="pending" selected>{{$pendingSupplier['name']}}</option>
						@endif
						@foreach ($suppliers as $supplier)
							@php
								$sp = $supplierPurchaseMap[$supplier->id] ?? null;
								$isSupplierInUse = in_array((string) $supplier->id, $usedSupplierIds ?? [], true);
							@endphp
							<option value="{{$supplier->id}}"
								data-product="{{ $sp['product'] ?? '' }}"
								data-category="{{ $sp['category_id'] ?? '' }}"
								data-cost="{{ $sp['cost_price'] ?? '' }}"
								data-expiry="{{ $sp['expiry_date'] ?? '' }}"
								{{ old('supplier', $clearPrefill ? '' : ($preselectedSupplier ?? '')) == $supplier->id ? 'selected' : '' }}
							>{{$supplier->name}}{{ $isSupplierInUse ? ' (Already in use)' : '' }}</option>
						@endforeach
					</select>
				</div>
			</div>
		</div>
	</div>
	<div class="service-fields mb-3">
		<div class="row">
			<div class="col-lg-4"><div class="form-group"><label>Selling Price per (1) item<span class="text-danger">*</span></label><input class="form-control" type="number" min="1" step="0.01" name="cost_price" value="{{ old('cost_price') }}" required></div></div>
			<div class="col-lg-4"><div class="form-group"><label>SKU Num</label><input class="form-control" type="text" name="barcode" value="{{ old('barcode') }}" placeholder="Enter or scan SKU" maxlength="100"></div></div>
		</div>
	</div>
	<div class="service-fields mb-3"><div class="row">
		<div class="col-lg-3"><div class="form-group"><label>Batch / Lot Number</label><input class="form-control" type="text" name="batch_number" value="{{ old('batch_number') }}" maxlength="100"></div></div>
		<div class="col-lg-3"><div class="form-group"><label>Manufacture Date</label><input class="form-control" type="date" name="manufacture_date" value="{{ old('manufacture_date') }}"></div></div>
		<div class="col-lg-3"><div class="form-group"><label>Reorder Level</label><input class="form-control" type="number" min="0" name="reorder_level" value="{{ old('reorder_level', 10) }}"></div></div>
		<div class="col-lg-3"><div class="form-group"><label>Purchase Order Number</label><input class="form-control" type="text" name="order_number" value="{{ old('order_number') }}" maxlength="100"></div></div>
	</div></div>
	<div class="service-fields mb-3"><div class="row">
		<div class="col-lg-3"><div class="form-group"><label>Item (1)<span class="text-danger">*</span></label><input class="form-control" type="number" min="0" name="item_quantity" value="{{ old('item_quantity', 1) }}" required></div></div>
		<div class="col-lg-3"><div class="form-group"><label>Packaging box (1)<span class="text-danger">*</span></label><input class="form-control" type="number" min="0" name="packaging_box" value="{{ old('packaging_box') }}" required></div></div>
		<div class="col-lg-3"><div class="form-group"><label>Total Quantity inside Per (1 - Box)<span class="text-danger">*</span></label><input class="form-control" type="number" min="0" name="quantity_per_box" value="{{ old('quantity_per_box') }}" required></div></div>
		<div class="col-lg-3"><div class="form-group"><label>Total<span class="text-danger">*</span></label><input class="form-control" type="number" min="0" name="total_quantity" value="{{ old('total_quantity') }}" readonly></div></div>
	</div></div>
	<div class="service-fields mb-3"><div class="row">
		<div class="col-lg-6 expiry-field"><div class="form-group"><label>Expire Date<span class="text-danger">*</span></label><input class="form-control" type="date" name="expiry_date" value="{{ old('expiry_date') }}" required></div></div>
		<div class="col-lg-6"><div class="form-group"><label>Product Image</label><div class="purchase-image-input-row">
			<input type="file" name="image" id="purchase-image-input" class="purchase-image-file" accept="image/*">
			<div class="purchase-image-source dropdown"><button type="button" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-image mr-1"></i> Add photo</button><div class="dropdown-menu source-menu"><label for="purchase-image-input"><i class="fa fa-folder-open-o mr-1"></i> Choose file</label><button type="button" class="purchase-camera-button" id="purchase-camera-button"><i class="fa fa-camera mr-1"></i> Use camera</button></div></div>
			<span class="purchase-image-file-name" id="purchase-image-file-name">No file chosen</span>
		</div>@include('components.purchase-camera')</div></div>
	</div></div>
	<div class="submit-section"><button class="btn btn-primary submit-btn" type="submit">Submit</button></div>
</form>