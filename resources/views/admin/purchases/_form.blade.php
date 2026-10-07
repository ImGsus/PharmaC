<style>
	.purchase-form-step {
		transition: opacity 0.2s ease, transform 0.2s ease;
	}
	.purchase-form-step.step-animating-out {
		opacity: 0;
		transform: translateY(-6px);
	}
	.purchase-form-step.step-animating-in {
		opacity: 1;
		transform: translateY(0);
	}
	.purchase-step2-summary {
		background: #f8fafc;
		border: 1px solid #e2e8f0;
	}
	.box-expiry-card {
		background: #ffffff;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		transition: border-color 0.2s, box-shadow 0.2s;
	}
	.box-expiry-card:hover {
		border-color: #cbd5e1;
		box-shadow: 0 2px 8px rgba(0,0,0,0.04);
	}
	.box-expiry-card .badge {
		font-size: 12px;
	}
	.earliest-expiry-alert {
		background: #f1f5f9;
		border-radius: 6px;
	}
	body.dark-mode .purchase-step2-summary {
		background: #1e252e !important;
		border-color: #334155 !important;
		color: #e2e8f0 !important;
	}
	body.dark-mode .purchase-step2-summary strong,
	body.dark-mode .purchase-step2-summary span.text-dark {
		color: #f1f5f9 !important;
	}
	body.dark-mode .box-expiry-card {
		background: #242c38 !important;
		border-color: #3b4554 !important;
	}
	body.dark-mode .box-expiry-card label {
		color: #94a3b8 !important;
	}
	body.dark-mode .earliest-expiry-alert {
		background: #1e252e !important;
		border-color: #334155 !important;
		color: #e2e8f0 !important;
	}
	body.dark-mode .card.bg-light {
		background: #1e252e !important;
		border-color: #334155 !important;
	}
	body.dark-mode .card.bg-light label,
	body.dark-mode .card.bg-light span.text-dark {
		color: #f1f5f9 !important;
	}
</style>

<form method="post" enctype="multipart/form-data" autocomplete="off" action="{{route('purchases.store')}}" class="purchase-multi-step-form">
	@csrf
	<input type="hidden" name="redirect_to" value="{{ request()->routeIs('products.*') || request()->is('products*') ? 'products.index' : '' }}">
	<input type="hidden" name="expiry_date" class="primary-expiry-date-hidden" value="{{ old('expiry_date') }}">

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

	<!-- STEP 1: Product & Purchase Details -->
	<div class="purchase-form-step purchase-step-1" data-step="1">
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
						<label class="purchase-expiry-switch mt-2">
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
			<div class="col-lg-3"><div class="form-group"><label>Item (1)</label><input class="form-control" type="number" min="0" name="item_quantity" value="{{ old('item_quantity', 1) }}" placeholder="0"></div></div>
			<div class="col-lg-3"><div class="form-group"><label>Packaging box (1)</label><input class="form-control" type="number" min="0" name="packaging_box" value="{{ old('packaging_box') }}" placeholder="0"></div></div>
			<div class="col-lg-3"><div class="form-group"><label>Total Quantity inside Per (1 - Box)</label><input class="form-control" type="number" min="0" name="quantity_per_box" value="{{ old('quantity_per_box') }}" placeholder="0"></div></div>
			<div class="col-lg-3"><div class="form-group"><label>Total<span class="text-danger">*</span></label><input class="form-control" type="number" min="0" name="total_quantity" value="{{ old('total_quantity') }}" readonly></div></div>
		</div></div>
		<div class="service-fields mb-3"><div class="row">
			<div class="col-lg-6"><div class="form-group"><label>Product Image</label><div class="purchase-image-input-row">
				<input type="file" name="image" id="purchase-image-input" class="purchase-image-file" accept="image/*">
				<div class="purchase-image-source dropdown"><button type="button" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-image mr-1"></i> Add photo</button><div class="dropdown-menu source-menu"><label for="purchase-image-input"><i class="fa fa-folder-open-o mr-1"></i> Choose file</label><button type="button" class="purchase-camera-button" id="purchase-camera-button"><i class="fa fa-camera mr-1"></i> Use camera</button></div></div>
				<span class="purchase-image-file-name" id="purchase-image-file-name">No file chosen</span>
			</div>@include('components.purchase-camera')</div></div>
		</div></div>
		<div class="submit-section d-flex justify-content-end mt-4">
			<button class="btn btn-primary submit-btn purchase-step-next-btn px-4" type="button">Next <i class="fas fa-arrow-right ml-1"></i></button>
		</div>
	</div>

	<!-- STEP 2: Expiry Declaration by Packaging Box -->
	<div class="purchase-form-step purchase-step-2" data-step="2" style="display: none;">
		<!-- Summary Preview -->
		<div class="purchase-step2-summary mb-3 p-3 rounded">
			<div class="row text-center text-md-left align-items-center">
				<div class="col-md-4 mb-2 mb-md-0">
					<span class="text-muted d-block small font-weight-bold text-uppercase">Product</span>
					<strong class="step2-summary-product text-dark">--</strong>
				</div>
				<div class="col-md-3 mb-2 mb-md-0">
					<span class="text-muted d-block small font-weight-bold text-uppercase">Category</span>
					<span class="step2-summary-category text-dark font-weight-600">--</span>
				</div>
				<div class="col-md-3 mb-2 mb-md-0">
					<span class="text-muted d-block small font-weight-bold text-uppercase">Packaging Boxes</span>
					<span class="badge badge-primary px-2 py-1"><span class="step2-summary-boxes">0</span> Box(es)</span>
					<span class="small text-muted d-block mt-1">(<span class="step2-summary-per-box">0</span> qty/box)</span>
				</div>
				<div class="col-md-2">
					<span class="text-muted d-block small font-weight-bold text-uppercase">Total Stock</span>
					<span class="badge badge-success px-2 py-1 font-weight-bold"><span class="step2-summary-total">0</span></span>
				</div>
			</div>
		</div>

		<!-- When No Expiry is Active -->
		<div class="step2-no-expiry-wrap d-none">
			<div class="alert alert-info d-flex align-items-center mb-4">
				<i class="fas fa-info-circle fa-2x mr-3 text-info"></i>
				<div>
					<strong>No Expiry Date Required</strong>
					<p class="mb-0 small">This purchase or category is marked with "No Expiry". You can review the details and submit directly.</p>
				</div>
			</div>
		</div>

		<!-- When Expiry is Required -->
		<div class="step2-expiry-content">
			<!-- Quick Bulk Set Bar -->
			<div class="card mb-3 border bg-light shadow-none">
				<div class="card-body p-3">
					<div class="row align-items-center">
						<div class="col-md-5">
							<label class="font-weight-600 mb-1 small text-dark d-flex align-items-center">
								<i class="fas fa-calendar-alt text-primary mr-1"></i> Quick Action: Set All Expiries
							</label>
							<input type="date" class="form-control form-control-sm master-expiry-input">
						</div>
						<div class="col-md-7 mt-2 mt-md-0 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between">
							<button type="button" class="btn btn-sm btn-outline-primary btn-apply-all-expiry mb-2 mb-sm-0">
								<i class="fas fa-clone mr-1"></i> Apply to all boxes
							</button>
							<span class="text-muted small ml-sm-2">Sets every box to this expiry date. You can still adjust individual boxes below.</span>
						</div>
					</div>
				</div>
			</div>

			<!-- Dynamic Box Expiration Dates Grid -->
			<div class="mb-3">
				<h6 class="font-weight-bold text-dark mb-2 d-flex align-items-center justify-content-between">
					<span><i class="fas fa-boxes text-secondary mr-1"></i> Packaging Box Expiration Breakdown</span>
					<span class="badge badge-secondary font-weight-normal"><span class="step2-box-count-badge">0</span> box(es) declared</span>
				</h6>
				<div class="box-expiries-container row">
					<!-- Dynamically rendered box cards -->
				</div>
			</div>

			<!-- Loose Items Expiration (if item_quantity > 0) -->
			<div class="loose-items-expiry-wrap mb-3 d-none">
				<h6 class="font-weight-bold text-dark mb-2"><i class="fas fa-pills text-info mr-1"></i> Loose Items Expiry</h6>
				<div class="card border mb-2 shadow-none box-expiry-card">
					<div class="card-body p-3 d-flex align-items-center justify-content-between">
						<div>
							<span class="badge badge-info mr-2"><i class="fas fa-pills mr-1"></i> Loose Items</span>
							<span class="small text-muted"><span class="loose-items-qty">0</span> unit(s)</span>
						</div>
						<div style="max-width: 220px; flex: 1;">
							<input type="date" name="loose_expiry" class="form-control form-control-sm loose-expiry-input">
						</div>
					</div>
				</div>
			</div>

			<!-- Earliest Expiry Summary Display -->
			<div class="earliest-expiry-alert alert alert-light border d-flex align-items-center justify-content-between py-2 px-3 mb-3">
				<div>
					<i class="fas fa-clock text-warning mr-1"></i>
					<span class="small text-muted">Earliest Expiry Date:</span>
					<strong class="earliest-expiry-display ml-1 text-primary">Not set</strong>
				</div>
				<span class="badge badge-light border text-muted small">Primary Shelf-Life</span>
			</div>
		</div>

		<!-- Step 2 Navigation & Submission -->
		<div class="submit-section d-flex justify-content-between align-items-center mt-4">
			<button type="button" class="btn btn-secondary purchase-step-prev-btn px-4">
				<i class="fas fa-arrow-left mr-1"></i> Back
			</button>
			<button type="submit" class="btn btn-primary submit-btn purchase-step-submit-btn px-4">
				<i class="fas fa-check mr-1"></i> Submit
			</button>
		</div>
	</div>
</form>