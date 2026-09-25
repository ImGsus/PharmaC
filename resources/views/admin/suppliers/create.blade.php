@extends('admin.layouts.app')

@push('page-css')
	<!-- Datetimepicker CSS -->
	<link rel="stylesheet" href="{{asset('assets/css/bootstrap-datetimepicker.min.css')}}">
@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Add Supplier</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Add Supplier</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
				
		
			<!-- Add Supplier -->
			<form method="post" enctype="multipart/form-data" action="{{route('suppliers.store')}}">
				@csrf
				<input type="hidden" name="form_submit" value="next">
				@include('admin.suppliers._form')

				<div class="submit-section">
					<button class="btn btn-primary submit-btn" type="submit" id="next-button">Next</button>
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
		(function () {
			var supplierForm = document.querySelector('form[action="{{ route('suppliers.store') }}"]');
			if (!supplierForm) return;
			var hiddenField = supplierForm.querySelector('input[name="form_submit"]');
			var nextButton = document.getElementById('next-button');
			if (!hiddenField) {
				hiddenField = document.createElement('input');
				hiddenField.type = 'hidden';
				hiddenField.name = 'form_submit';
				hiddenField.value = 'next';
				supplierForm.prepend(hiddenField);
			}
			if (nextButton) {
				nextButton.addEventListener('click', function () {
					hiddenField.value = 'next';
				});
			}
		})();
	</script>
@endpush

