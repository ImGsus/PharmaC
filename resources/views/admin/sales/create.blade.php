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
                <form method="POST" action="{{route('sales.store')}}">
					@csrf
				<div class="row form-row">
					<div class="col-12">
						<div class="form-group">
							<label>Product <span class="text-danger">*</span></label>
							<select class="select2 form-select form-control" name="product"> 
								@foreach ($products as $product)
									@if (!empty($product->purchase))
										@if (!($product->purchase->quantity <= 0))
											<option value="{{$product->id}}">{{$product->purchase->product}}</option>
										@endif
									@endif
								@endforeach
							</select>
						</div>
					</div>
					<div class="col-12">
						<div class="form-group">
							<label>Quantity</label>
							<input type="number" value="1" class="form-control" name="quantity">
						</div>
					</div>
				</div>
				@if(!empty($selectedProducts) && count($selectedProducts))
					<hr />
					<h5>Scanned Items</h5>
					<div id="scanned-list" class="mb-3">
						@foreach($selectedProducts as $idx => $p)
							<div class="d-flex justify-content-between align-items-center p-2 border mb-1">
								<div>
									<strong>{{ $p->purchase->product ?? 'N/A' }}</strong>
									<div class="text-muted">SKU: {{ $p->barcode }}</div>
								</div>
								<div>
									<input type="hidden" name="items[{{ $idx }}][product]" value="{{ $p->id }}">
									<input type="number" name="items[{{ $idx }}][quantity]" value="1" min="1" class="form-control" style="width:90px; display:inline-block;">
								</div>
							</div>
						@endforeach
					</div>
					<button type="submit" class="btn btn-success btn-block">Add Sale for Scanned Items</button>
				@else
					<button type="submit" class="btn btn-primary btn-block">Save Changes</button>
				@endif
				</form>
                <!--/ Create Sale -->
			</div>
		</div>
	</div>			
</div>
@endsection	


@push('page-js')
    
@endpush