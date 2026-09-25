@extends('admin.layouts.app')


@push('page-css')
    
@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Edit Sale</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Edit Sale</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
                <!-- Create Sale -->
                @php
                    $scannedIds = [];
                    if (!empty($selectedProducts) && count($selectedProducts)) {
                        foreach ($selectedProducts as $item) {
                            if (!empty($item['product']->id)) {
                                $scannedIds[] = $item['product']->id;
                            }
                        }
                    }
                    $itemIndex = !empty($selectedProducts) ? count($selectedProducts) : 0;
                @endphp
                <form method="POST" action="{{route('sales.store')}}" id="sales-create-form">
					@csrf
				<div class="row form-row">
					<div class="col-12">
						<div class="form-group">
							<label>Add Product <span class="text-danger">*</span></label>
							<select class="select2 form-select form-control" id="add-product-select" name="add_product">
						<option value="">Select product to add</option>
						@foreach ($products as $product)
							@if (!empty($product->purchase) && !in_array($product->id, $scannedIds))
								@if (!($product->purchase->quantity <= 0))
									<option value="{{$product->id}}" data-name="{{ $product->purchase->product }}" data-barcode="{{ $product->barcode }}">{{$product->purchase->product}}</option>
								@endif
							@endif
						@endforeach
					</select>
						</div>
					</div>
					<div class="col-12 text-end mb-3">
						<button type="button" class="btn btn-primary" id="add-selected-product">Add Product</button>
					</div>
				</div>
				<hr />
				<h5>Scanned Items</h5>
				<div id="scanned-list" class="mb-3">
				@if(!empty($selectedProducts) && count($selectedProducts))
					@foreach($selectedProducts as $idx => $item)
						@php $product = $item['product']; $quantity = $item['quantity']; @endphp
						<div class="d-flex justify-content-between align-items-center p-2 border mb-1">
							<div>
								<strong>{{ $product->purchase->product ?? 'N/A' }} x{{ $quantity }}</strong>
								<div class="text-muted">SKU: {{ $product->barcode }}</div>
							</div>
							<div>
								<input type="hidden" name="items[{{ $idx }}][product]" value="{{ $product->id }}">
								<input type="number" name="items[{{ $idx }}][quantity]" value="{{ $quantity }}" min="1" class="form-control" style="width:90px; display:inline-block;">
							</div>
						</div>
					@endforeach
					<button type="submit" class="btn btn-success btn-block">Add Sale for Scanned Items</button>
				@else
					<button type="submit" class="btn btn-primary btn-block">Save Changes</button>
				@endif
				</form>
				@if ($errors->any())
					<div class="alert alert-danger mt-2">{{ $errors->first() }}</div>
				@endif
                <!--/ Create Sale -->
			</div>
		</div>
	</div>			
</div>
@endsection	



@push('page-js')
	<script>
	(function () {
		var addBtn = document.getElementById('add-selected-product');
		var select = document.getElementById('add-product-select');
		var scannedList = document.getElementById('scanned-list');
		var itemIndex = {{ $itemIndex ?? 0 }};

		function escapeHtml(text){
			if(!text) return '';
			return String(text).replace(/[&<>"']/g, function(m){
				return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]);
			});
		}

		if (addBtn && select && scannedList) {
			addBtn.addEventListener('click', function () {
				var val = select.value;
				if (!val) {
					alert('Please select a product to add.');
					return;
				}
				var opt = select.options[select.selectedIndex];
				var name = opt.getAttribute('data-name') || opt.text;
				var barcode = opt.getAttribute('data-barcode') || '';

				var wrapper = document.createElement('div');
				wrapper.className = 'd-flex justify-content-between align-items-center p-2 border mb-1';
				wrapper.innerHTML = '<div><strong>' + escapeHtml(name) + ' x1</strong><div class="text-muted">SKU: ' + escapeHtml(barcode) + '</div></div>' +
					'<div><input type="hidden" name="items[' + itemIndex + '][product]" value="' + escapeHtml(val) + '">' +
					'<input type="number" name="items[' + itemIndex + '][quantity]" value="1" min="1" class="form-control" style="width:90px; display:inline-block;"></div>';

				// insert at top of list
				scannedList.insertBefore(wrapper, scannedList.firstChild);

				// remove option to prevent re-adding
				opt.remove();
				select.value = '';
				if (window.jQuery && $(select).hasClass('select2-hidden-accessible')) {
					$(select).trigger('change.select2');
				}
				itemIndex++;
			});
		}
	})();
	</script>
@endpush